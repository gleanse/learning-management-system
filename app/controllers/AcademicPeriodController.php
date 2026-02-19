<?php
require_once __DIR__ . '/../models/AcademicPeriod.php';

class AcademicPeriodController
{
    private $academic_model;

    public function __construct()
    {
        $this->academic_model = new AcademicPeriod();
    }

    private function requireAdmin()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
            header('Location: index.php?page=login');
            exit();
        }
    }

    // json response helper
    private function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    // show the academic period management dashboard
    public function showDashboard()
    {
        $this->requireAdmin();

        $current        = $this->academic_model->getCurrentPeriod();
        $has_period     = $this->academic_model->hasPeriod();
        $history        = $this->academic_model->getHistory(10);
        $active_count   = $this->academic_model->getActiveStudentCount();
        $errors         = $_SESSION['academic_errors']  ?? [];
        $success        = $_SESSION['academic_success'] ?? null;

        unset($_SESSION['academic_errors'], $_SESSION['academic_success']);

        // stats for current period
        $period_count   = 0;
        $missing_config = 0;

        if ($current) {
            $period_count   = $this->academic_model->getPaidStudentCount($current['school_year'], $current['semester']);
            $missing_config = $this->academic_model->getStudentsMissingFeeConfig($current['school_year']);
        }

        // resolve what next period would be for the advance button label
        $next_period = null;
        if ($current) {
            $next_period = $this->resolveNextPeriodLabel($current['school_year'], $current['semester']);
        }

        // fetch graduatable students only during second semester
        $graduatable_students = [];
        if ($current && $current['semester'] === 'Second') {
            $graduatable_students = $this->academic_model->getGraduatableStudents();
        }

        require __DIR__ . '/../views/admin/academic_period.php';
    }

    // ajax: initialize the first period
    public function ajaxInitialize()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        // block if period already exists
        if ($this->academic_model->hasPeriod()) {
            $this->jsonResponse(['success' => false, 'message' => 'A period is already initialized.'], 400);
        }

        $school_year = trim($_POST['school_year'] ?? '');
        $semester    = trim($_POST['semester']    ?? '');
        $errors      = [];

        if (empty($school_year)) $errors['school_year'] = 'School year is required.';
        if (empty($semester))    $errors['semester']    = 'Semester is required.';

        // validate school year format YYYY-YYYY
        if (!empty($school_year) && !preg_match('/^\d{4}-\d{4}$/', $school_year)) {
            $errors['school_year'] = 'School year must be in YYYY-YYYY format.';
        }

        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 422);
        }

        $result = $this->academic_model->initializePeriod($school_year, $semester, $_SESSION['user_id']);

        if (!$result['success']) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to initialize period. Please try again.'], 500);
        }

        // build fresh data to return for ui update
        $current        = $this->academic_model->getCurrentPeriod();
        $active_count   = $this->academic_model->getActiveStudentCount();
        $period_count   = $this->academic_model->getPaidStudentCount($school_year, $semester);
        $missing_config = $this->academic_model->getStudentsMissingFeeConfig($school_year);
        $history        = $this->academic_model->getHistory(10);
        $next_period    = $this->resolveNextPeriodLabel($school_year, $semester);

        $this->jsonResponse([
            'success'        => true,
            'message'        => "Period initialized: {$semester} Semester {$school_year}. {$result['created']} payment record(s) created.",
            'created'        => $result['created'],
            'current'        => $current,
            'active_count'   => $active_count,
            'period_count'   => $period_count,
            'missing_config' => $missing_config,
            'next_period'    => $next_period,
            'history'        => $history,
        ]);
    }

    // ajax: advance to the next semester or school year
    public function ajaxAdvance()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        if (!$this->academic_model->hasPeriod()) {
            $this->jsonResponse(['success' => false, 'message' => 'No active period found. Please initialize first.'], 400);
        }

        $result = $this->academic_model->advancePeriod($_SESSION['user_id']);

        if (!$result['success']) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to advance period. Please try again.'], 500);
        }

        $next = $result['next'];

        // build fresh data to return for ui update
        $current        = $this->academic_model->getCurrentPeriod();
        $active_count   = $this->academic_model->getActiveStudentCount();
        $period_count   = $this->academic_model->getPaidStudentCount($next['school_year'], $next['semester']);
        $missing_config = $this->academic_model->getStudentsMissingFeeConfig($next['school_year']);
        $history        = $this->academic_model->getHistory(10);
        $next_period    = $this->resolveNextPeriodLabel($next['school_year'], $next['semester']);

        $this->jsonResponse([
            'success'        => true,
            'message'        => "Advanced to {$next['semester']} Semester {$next['school_year']}. {$result['created']} payment record(s) created.",
            'created'        => $result['created'],
            'current'        => $current,
            'active_count'   => $active_count,
            'period_count'   => $period_count,
            'missing_config' => $missing_config,
            'next_period'    => $next_period,
            'history'        => $history,
        ]);
    }

    // helper — returns human readable next period label for the advance button
    private function resolveNextPeriodLabel($school_year, $semester)
    {
        if ($semester === 'First') {
            return "Second Semester {$school_year}";
        }

        $parts      = explode('-', $school_year);
        $next_start = (int) ($parts[1] ?? date('Y'));
        $next_end   = $next_start + 1;

        return "First Semester {$next_start}-{$next_end}";
    }

    // ajax: graduate selected students
    public function ajaxGraduateStudents()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        $student_ids = isset($_POST['student_ids']) && is_array($_POST['student_ids'])
            ? array_map('intval', $_POST['student_ids'])
            : [];

        if (empty($student_ids)) {
            $this->jsonResponse(['success' => false, 'message' => 'Please select at least one student.'], 422);
        }

        $result = $this->academic_model->graduateStudents($student_ids);

        if (!$result) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to graduate students. Please try again.'], 500);
        }

        $active_count = $this->academic_model->getActiveStudentCount();
        $graduatable  = $this->academic_model->getGraduatableStudents();

        $this->jsonResponse([
            'success'      => true,
            'message'      => count($student_ids) . ' student(s) graduated successfully.',
            'active_count' => $active_count,
            'graduatable'  => $graduatable,
        ]);
    }
}
