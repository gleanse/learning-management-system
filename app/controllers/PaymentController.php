<?php
require_once __DIR__ . '/../models/Payment.php';

class PaymentController
{
    private $payment_model;

    public function __construct()
    {
        $this->payment_model = new Payment();
    }

    private function requireRegistrar()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'registrar') {
            header('Location: index.php?page=login');
            exit();
        }
    }

    // show the main process payment page
    public function showPaymentPage()
    {
        $this->requireRegistrar();

        $student            = null;
        $payment            = null;
        $transactions       = [];
        $last_receipt       = null;
        $recent_enrollments = [];
        $errors             = $_SESSION['payment_errors'] ?? [];
        $student_id         = (int) ($_GET['student_id'] ?? 0);

        unset($_SESSION['payment_errors']);

        if ($student_id) {
            $student = $this->payment_model->getStudentById($student_id);

            if ($student) {
                $school_year = $this->payment_model->getCurrentSchoolYear();
                $semester    = $this->payment_model->getCurrentSemester($student_id, $school_year);
                $payment     = $this->payment_model->getActiveEnrollmentPayment($student_id, $school_year, $semester);

                if ($payment) {
                    $transactions = $this->payment_model->getTransactionsByPaymentId($payment['payment_id']);
                }
            }
        }

        // only load recent enrollments when no student is selected
        if (!$student) {
            $recent_enrollments = $this->payment_model->getRecentEnrollments(8);
        }

        // pull receipt from session if payment was just processed
        if (!empty($_SESSION['last_transaction_id'])) {
            $last_receipt = $this->payment_model->getTransactionById($_SESSION['last_transaction_id']);
            unset($_SESSION['last_transaction_id']);
        }

        require __DIR__ . '/../views/registrar/payment_process.php';
    }

    // ajax — search students
    public function searchStudents()
    {
        $this->requireRegistrar();

        header('Content-Type: application/json');

        $query = trim($_GET['q'] ?? '');

        if (strlen($query) < 2) {
            echo json_encode(['success' => false, 'message' => 'Search query too short']);
            return;
        }

        $students = $this->payment_model->searchStudents($query);
        echo json_encode(['success' => true, 'data' => $students]);
    }

    // ajax — get enrollment payment and transaction history for a student
    public function getPaymentDetails()
    {
        $this->requireRegistrar();

        header('Content-Type: application/json');

        $student_id = (int) ($_GET['student_id'] ?? 0);

        if (!$student_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid student']);
            return;
        }

        $student = $this->payment_model->getStudentById($student_id);

        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'Student not found']);
            return;
        }

        $school_year  = $this->payment_model->getCurrentSchoolYear();
        $semester     = $this->payment_model->getCurrentSemester($student_id, $school_year);
        $payment      = $this->payment_model->getActiveEnrollmentPayment($student_id, $school_year, $semester);
        $transactions = $payment ? $this->payment_model->getTransactionsByPaymentId($payment['payment_id']) : [];

        echo json_encode([
            'success'      => true,
            'student'      => $student,
            'payment'      => $payment,
            'transactions' => $transactions,
        ]);
    }

    // process a cash payment submission
    public function store()
    {
        $this->requireRegistrar();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=payment_process');
            exit();
        }

        $data       = $this->sanitize($_POST);
        $student_id = (int) ($data['student_id'] ?? 0);
        $payment_id = (int) ($data['payment_id'] ?? 0);
        $errors     = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['payment_errors'] = $errors;
            header("Location: index.php?page=payment_process&student_id={$student_id}");
            exit();
        }

        $transaction_id = $this->payment_model->processPayment(
            $payment_id,
            (float) $data['amount_paid'],
            $data['notes'] ?? '',
            $_SESSION['user_id']
        );

        if (!$transaction_id) {
            $_SESSION['payment_errors'] = ['general' => 'Payment processing failed. Please try again.'];
            header("Location: index.php?page=payment_process&student_id={$student_id}");
            exit();
        }

        $_SESSION['last_transaction_id'] = $transaction_id;

        header("Location: index.php?page=payment_process&student_id={$student_id}");
        exit();
    }

    // ajax — get receipt data for a transaction
    public function getReceipt()
    {
        $this->requireRegistrar();

        header('Content-Type: application/json');

        $transaction_id = (int) ($_GET['transaction_id'] ?? 0);

        if (!$transaction_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid transaction']);
            return;
        }

        $receipt = $this->payment_model->getTransactionById($transaction_id);

        if (!$receipt) {
            echo json_encode(['success' => false, 'message' => 'Transaction not found']);
            return;
        }

        echo json_encode(['success' => true, 'data' => $receipt]);
    }

    private function validate($data)
    {
        $errors = [];

        if (empty($data['student_id']) || (int) $data['student_id'] <= 0) {
            $errors['student_id'] = 'No student selected.';
        }

        if (empty($data['payment_id']) || (int) $data['payment_id'] <= 0) {
            $errors['payment_id'] = 'No enrollment payment record found for this student.';
        }

        if (empty($data['amount_paid']) || (float) $data['amount_paid'] <= 0) {
            $errors['amount_paid'] = 'Amount must be greater than zero.';
        }

        if (!empty($data['amount_paid']) && !empty($data['remaining_balance'])) {
            $amount    = (float) $data['amount_paid'];
            $remaining = (float) $data['remaining_balance'];

            if ($amount > $remaining) {
                $errors['amount_paid'] = 'Amount exceeds the remaining balance of ₱' . number_format($remaining, 2) . '.';
            }
        }

        return $errors;
    }

    private function sanitize($data)
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = $this->sanitize($value);
            } else {
                $clean[$key] = htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
            }
        }
        return $clean;
    }
}
