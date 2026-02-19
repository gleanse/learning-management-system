<?php

require_once __DIR__ . '/../models/StudentProfile.php';
require_once __DIR__ . '/../models/AcademicPeriod.php';

class StudentProfileController
{
    private $profile_model;
    private $academic_model;

    public function __construct()
    {
        $this->profile_model  = new StudentProfile();
        $this->academic_model = new AcademicPeriod();
    }

    private function requireRegistrar()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'registrar') {
            header('Location: index.php?page=login');
            exit();
        }
    }

    private function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    private function jsonResponse($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    private function validateCsrf()
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Your session expired. Please refresh and try again.'], 403);
            }

            $_SESSION['profile_errors'] = ['general' => 'Your session expired. Please try again.'];
            header('Location: index.php?page=student_profiles');
            exit();
        }
    }

    // show main student profiles list
    public function showProfiles()
    {
        $this->requireRegistrar();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $page   = isset($_GET['p']) ? (int) $_GET['p'] : 1;
        $limit  = 15;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';

        $students    = $this->profile_model->getStudentsWithBalance($school_year, $semester, $limit, $offset, $search);
        $total       = $this->profile_model->getTotalCount($search);
        $total_pages = ceil($total / $limit);

        $errors          = $_SESSION['profile_errors']  ?? [];
        $success_message = $_SESSION['profile_success'] ?? null;
        unset($_SESSION['profile_errors'], $_SESSION['profile_success']);

        require __DIR__ . '/../views/registrar/student_profiles.php';
    }

    // show single student profile view
    public function showViewProfile()
    {
        $this->requireRegistrar();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $student_id = isset($_GET['student_id']) ? (int) $_GET['student_id'] : null;

        if (empty($student_id)) {
            $_SESSION['profile_errors'] = ['general' => 'Invalid student.'];
            header('Location: index.php?page=student_profiles');
            exit();
        }

        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $student = $this->profile_model->getStudentWithProfile($student_id, $school_year, $semester);

        if (!$student) {
            $_SESSION['profile_errors'] = ['general' => 'Student not found.'];
            header('Location: index.php?page=student_profiles');
            exit();
        }

        $payment_history = $this->profile_model->getPaymentHistory($student_id);

        $errors          = $_SESSION['profile_errors']  ?? [];
        $success_message = $_SESSION['profile_success'] ?? null;
        unset($_SESSION['profile_errors'], $_SESSION['profile_success']);

        require __DIR__ . '/../views/registrar/view_student_profile.php';
    }

    // show edit profile form
    public function showEditProfile()
    {
        $this->requireRegistrar();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $student_id = isset($_GET['student_id']) ? (int) $_GET['student_id'] : null;

        if (empty($student_id)) {
            $_SESSION['profile_errors'] = ['general' => 'Invalid student.'];
            header('Location: index.php?page=student_profiles');
            exit();
        }

        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $student = $this->profile_model->getStudentWithProfile($student_id, $school_year, $semester);

        if (!$student) {
            $_SESSION['profile_errors'] = ['general' => 'Student not found.'];
            header('Location: index.php?page=student_profiles');
            exit();
        }

        $errors          = $_SESSION['profile_errors']  ?? [];
        $success_message = $_SESSION['profile_success'] ?? null;
        unset($_SESSION['profile_errors'], $_SESSION['profile_success']);

        require __DIR__ . '/../views/registrar/edit_student_profile.php';
    }

    // process save profile
    public function processSaveProfile()
    {
        $this->requireRegistrar();
        $this->validateCsrf();

        $student_id = isset($_POST['student_id']) ? (int) $_POST['student_id'] : null;

        if (empty($student_id)) {
            $_SESSION['profile_errors'] = ['general' => 'Invalid student.'];
            header('Location: index.php?page=student_profiles');
            exit();
        }

        $first_name  = trim($_POST['first_name']  ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name   = trim($_POST['last_name']   ?? '');

        $data = [
            'email'           => trim($_POST['email']           ?? ''),
            'date_of_birth'   => trim($_POST['date_of_birth']   ?? ''),
            'gender'          => trim($_POST['gender']          ?? ''),
            'contact_number'  => trim($_POST['contact_number']  ?? ''),
            'home_address'    => trim($_POST['home_address']    ?? ''),
            'previous_school' => trim($_POST['previous_school'] ?? ''),
            'special_notes'   => trim($_POST['special_notes']   ?? ''),
        ];

        // convert empty strings to null
        foreach ($data as $key => $value) {
            if ($value === '') $data[$key] = null;
        }

        $errors = [];

        if (empty($first_name)) $errors['first_name'] = 'First name is required.';
        if (empty($last_name))  $errors['last_name']  = 'Last name is required.';

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email address.';
        }

        if (!empty($errors)) {
            $_SESSION['profile_errors'] = $errors;
            header('Location: index.php?page=edit_student_profile&student_id=' . $student_id);
            exit();
        }

        // update name in students table
        $this->profile_model->updateStudentName($student_id, $first_name, $middle_name ?: null, $last_name);

        $result = $this->profile_model->saveProfile($student_id, $data);

        if ($result) {
            $_SESSION['profile_success'] = 'Student profile updated successfully.';
        } else {
            $_SESSION['profile_errors'] = ['general' => 'Failed to update profile. Please try again.'];
        }

        header('Location: index.php?page=view_student_profile&student_id=' . $student_id);
        exit();
    }

    // export students to csv
    public function exportCsv()
    {
        $this->requireRegistrar();

        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $students = $this->profile_model->getAllForExport($school_year, $semester);

        $filename = 'student_profiles_' . $school_year . '_' . $semester . '_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');

        // csv headers
        fputcsv($out, [
            'Student Number',
            'Student Name',
            'Year Level',
            'Strand/Course',
            'Education Level',
            'Total Amount',
            'Payment Status',
            'Total Paid',
            'Remaining Balance',
        ]);

        foreach ($students as $row) {
            fputcsv($out, [
                $row['student_number'],
                $row['student_name'],
                $row['year_level'],
                $row['strand_course'],
                $row['education_level'] === 'senior_high' ? 'Senior High' : 'College',
                $row['net_amount']       ?? '0.00',
                $row['payment_status']   ?? 'No Record',
                $row['total_paid']       ?? '0.00',
                $row['remaining']        ?? '0.00',
            ]);
        }

        fclose($out);
        exit();
    }

    // ajax search
    public function ajaxSearch()
    {
        $this->requireRegistrar();

        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $page   = isset($_GET['p']) ? (int) $_GET['p'] : 1;
        $limit  = 15;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';

        $students    = $this->profile_model->getStudentsWithBalance($school_year, $semester, $limit, $offset, $search);
        $total       = $this->profile_model->getTotalCount($search);
        $total_pages = ceil($total / $limit);

        ob_start();
        foreach ($students as $s):
            $full_name    = htmlspecialchars($s['last_name'] . ', ' . $s['first_name'] . ($s['middle_name'] ? ' ' . $s['middle_name'] : ''));
            $grade_label  = htmlspecialchars($s['year_level'] . ' - ' . $s['strand_course']);
            $remaining    = $s['remaining'] ?? null;
            $pay_status   = $s['payment_status'] ?? null;

            $status_badge = match ($pay_status) {
                'paid'    => '<span class="badge bg-success">Paid</span>',
                'partial' => '<span class="badge bg-warning text-dark">Partial</span>',
                'pending' => '<span class="badge bg-danger">Pending</span>',
                default   => '<span class="badge bg-secondary">No Record</span>',
            };
?>
            <tr>
                <td><?= htmlspecialchars($s['student_number']) ?></td>
                <td><?= $full_name ?></td>
                <td><?= $grade_label ?></td>
                <td><?= $remaining !== null ? '₱' . number_format($remaining, 2) : '—' ?></td>
                <td><?= $status_badge ?></td>
                <td>
                    <a href="index.php?page=edit_student_profile&student_id=<?= $s['student_id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                        <i class="bi bi-pencil-fill"></i> Edit
                    </a>
                    <a href="index.php?page=view_student_profile&student_id=<?= $s['student_id'] ?>" class="btn btn-sm btn-outline-secondary me-1">
                        <i class="bi bi-eye-fill"></i> View
                    </a>
                    <a href="index.php?page=record_payment&student_id=<?= $s['student_id'] ?>" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-cash-coin"></i> Payment
                    </a>
                </td>
            </tr>
<?php endforeach;
        $table_html = ob_get_clean();

        $this->jsonResponse([
            'success'     => true,
            'html'        => $table_html,
            'total'       => $total,
            'total_pages' => $total_pages,
            'current_page' => $page,
        ]);
    }
}
