<?php
require_once __DIR__ . '/../../config/db_connection.php';

class Payment
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // search students by name, student number, or lrn
    public function searchStudents($query)
    {
        $term = "%{$query}%";
        $stmt = $this->connection->prepare("
            SELECT
                s.student_id,
                s.student_number,
                s.first_name,
                s.middle_name,
                s.last_name,
                s.year_level,
                s.education_level,
                s.strand_course,
                s.enrollment_status,
                sec.section_name
            FROM students s
            LEFT JOIN sections sec ON s.section_id = sec.section_id
            WHERE s.enrollment_status = 'active'
                AND (
                    s.student_number LIKE ?
                    OR s.first_name LIKE ?
                    OR s.last_name LIKE ?
                    OR s.lrn LIKE ?
                    OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?
                    OR CONCAT(s.last_name, ' ', s.first_name) LIKE ?
                )
            ORDER BY s.last_name ASC, s.first_name ASC
            LIMIT 10
        ");

        $stmt->execute([$term, $term, $term, $term, $term, $term]);
        return $stmt->fetchAll();
    }

    // get student basic info for the payment page header
    public function getStudentById($student_id)
    {
        $stmt = $this->connection->prepare("
            SELECT
                s.student_id,
                s.student_number,
                s.first_name,
                s.middle_name,
                s.last_name,
                s.year_level,
                s.education_level,
                s.strand_course,
                s.enrollment_status,
                sec.section_name
            FROM students s
            LEFT JOIN sections sec ON s.section_id = sec.section_id
            WHERE s.student_id = ?
        ");

        $stmt->execute([$student_id]);
        return $stmt->fetch();
    }

    // get the active enrollment_payment record for this student
    public function getActiveEnrollmentPayment($student_id, $school_year, $semester)
    {
        $stmt = $this->connection->prepare("
        SELECT
            ep.payment_id, ep.student_id, ep.school_year, ep.semester,
            ep.total_amount, ep.net_amount, ep.status, ep.created_at,
            COALESCE(SUM(pt.amount_paid), 0) AS total_paid
        FROM enrollment_payments ep
        LEFT JOIN payment_transactions pt ON ep.payment_id = pt.payment_id
        WHERE ep.student_id = ?
            AND ep.school_year = ?
            AND ep.semester = ?
        GROUP BY ep.payment_id
    ");

        $stmt->execute([$student_id, $school_year, $semester]);
        $payment = $stmt->fetch();

        // auto-create payment record if none exists for current period
        if (!$payment) {
            $payment_id = $this->createEnrollmentPayment($student_id, $school_year, $semester);
            if ($payment_id) {
                $stmt->execute([$student_id, $school_year, $semester]);
                $payment = $stmt->fetch();
            }
        }

        return $payment;
    }

    // create enrollment_payment from fee_config based on student's year level
    private function createEnrollmentPayment($student_id, $school_year, $semester)
    {
        // get student year level, education level, strand
        $stmt = $this->connection->prepare("
        SELECT year_level, education_level, strand_course 
        FROM students WHERE student_id = ?
    ");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();

        if (!$student) {
            return false;
        }

        // lookup fee using year_level as the key (school_year column repurposed)
        $stmt = $this->connection->prepare("
        SELECT tuition_fee, miscellaneous, other_fees
        FROM fee_config
        WHERE education_level = ?
            AND school_year = ?
            AND strand_course = ?
    ");
        $stmt->execute([
            $student['education_level'],
            $student['year_level'],
            $student['strand_course']
        ]);
        $fee = $stmt->fetch();

        if (!$fee) {
            return false;
        }

        $total = $fee['tuition_fee'] + $fee['miscellaneous'] + $fee['other_fees'];

        $insert = $this->connection->prepare("
        INSERT INTO enrollment_payments 
            (student_id, school_year, semester, total_amount, discount_amount, net_amount, status, created_by)
        VALUES (?, ?, ?, ?, 0.00, ?, 'pending', 1)
    ");
        $insert->execute([$student_id, $school_year, $semester, $total, $total]);

        return $this->connection->lastInsertId();
    }


    // get all payment transactions for a given enrollment_payment
    public function getTransactionsByPaymentId($payment_id)
    {
        $stmt = $this->connection->prepare("
            SELECT
                pt.transaction_id,
                pt.amount_paid,
                pt.payment_date,
                pt.notes,
                pt.created_at,
                u.first_name AS received_by_first,
                u.last_name AS received_by_last
            FROM payment_transactions pt
            INNER JOIN users u ON pt.received_by = u.id
            WHERE pt.payment_id = ?
            ORDER BY pt.created_at DESC
        ");

        $stmt->execute([$payment_id]);
        return $stmt->fetchAll();
    }

    // record a cash payment and update enrollment_payment status
    public function processPayment($payment_id, $amount_paid, $notes, $received_by)
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare("
                INSERT INTO payment_transactions (payment_id, amount_paid, payment_date, notes, received_by)
                VALUES (?, ?, CURDATE(), ?, ?)
            ");
            $stmt->execute([$payment_id, $amount_paid, $notes ?: null, $received_by]);

            $transaction_id = $this->connection->lastInsertId();

            $this->updatePaymentStatus($payment_id);

            $this->connection->commit();
            return $transaction_id;
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[Payment::processPayment] ' . $e->getMessage());
            return false;
        }
    }

    // recalculates total paid and sets status to paid, partial, or pending
    private function updatePaymentStatus($payment_id)
    {
        $stmt = $this->connection->prepare("
            SELECT ep.net_amount, COALESCE(SUM(pt.amount_paid), 0) AS total_paid
            FROM enrollment_payments ep
            LEFT JOIN payment_transactions pt ON ep.payment_id = pt.payment_id
            WHERE ep.payment_id = ?
            GROUP BY ep.payment_id
        ");
        $stmt->execute([$payment_id]);
        $row = $stmt->fetch();

        if (!$row) return;

        $total_paid = (float) $row['total_paid'];
        $net_amount = (float) $row['net_amount'];

        if ($total_paid <= 0) {
            $status = 'pending';
        } elseif ($total_paid >= $net_amount) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        $update = $this->connection->prepare("
            UPDATE enrollment_payments SET status = ? WHERE payment_id = ?
        ");
        $update->execute([$status, $payment_id]);
    }

    // get a single transaction for receipt display
    public function getTransactionById($transaction_id)
    {
        $stmt = $this->connection->prepare("
            SELECT
                pt.transaction_id,
                pt.payment_id,
                pt.amount_paid,
                pt.payment_date,
                pt.notes,
                pt.created_at,
                u.first_name AS received_by_first,
                u.last_name AS received_by_last,
                u.role AS received_by_role,
                ep.school_year,
                ep.semester,
                ep.total_amount,
                ep.net_amount,
                ep.status,
                COALESCE(SUM(pt2.amount_paid), 0) AS total_paid_so_far
            FROM payment_transactions pt
            INNER JOIN users u ON pt.received_by = u.id
            INNER JOIN enrollment_payments ep ON pt.payment_id = ep.payment_id
            LEFT JOIN payment_transactions pt2 ON ep.payment_id = pt2.payment_id
            WHERE pt.transaction_id = ?
            GROUP BY pt.transaction_id
        ");

        $stmt->execute([$transaction_id]);
        return $stmt->fetch();
    }

    // get current active school year
    public function getCurrentSchoolYear()
    {
        $stmt = $this->connection->prepare("
        SELECT school_year FROM school_settings
        WHERE is_active = 1
        ORDER BY id DESC
        LIMIT 1
    ");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['school_year'] : date('Y') . '-' . (date('Y') + 1);
    }

    // get current semester based on most recent active enrollment_payment
    public function getCurrentSemester($student_id, $school_year)
    {
        $stmt = $this->connection->prepare("
            SELECT semester FROM enrollment_payments
            WHERE student_id = ? AND school_year = ?
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$student_id, $school_year]);
        $result = $stmt->fetch();
        return $result ? $result['semester'] : 'First';
    }

    // get recently enrolled students with their payment status
    public function getRecentEnrollments($limit = 8)
    {
        $stmt = $this->connection->prepare("
            SELECT
                s.student_id,
                s.student_number,
                s.first_name,
                s.last_name,
                s.year_level,
                s.strand_course,
                s.education_level,
                sec.section_name,
                ep.status AS payment_status,
                ep.net_amount,
                COALESCE(SUM(pt.amount_paid), 0) AS total_paid,
                s.created_at
            FROM students s
            LEFT JOIN sections sec ON s.section_id = sec.section_id
            LEFT JOIN enrollment_payments ep ON s.student_id = ep.student_id
            LEFT JOIN payment_transactions pt ON ep.payment_id = pt.payment_id
            WHERE s.enrollment_status = 'active'
            GROUP BY s.student_id, ep.payment_id
            ORDER BY s.created_at DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
