<?php
require_once __DIR__ . '/../models/AcademicPeriod.php';
require_once __DIR__ . '/../helpers/activity_logger.php';

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

        $current      = $this->academic_model->getCurrentPeriod();
        $has_period   = $this->academic_model->hasPeriod();
        $history      = $this->academic_model->getHistory(10);
        $active_count = $this->academic_model->getActiveStudentCount();
        $errors       = $_SESSION['academic_errors']  ?? [];
        $success      = $_SESSION['academic_success'] ?? null;

        unset($_SESSION['academic_errors'], $_SESSION['academic_success']);

        $period_count   = 0;
        $missing_config = 0;
        $grading_periods = [];
        $can_redo        = false;

        if ($current) {
            $period_count    = $this->academic_model->getPaidStudentCount($current['school_year'], $current['semester']);
            $missing_config  = $this->academic_model->getStudentsMissingFeeConfig($current['school_year']);
            $grading_periods = $this->academic_model->getGradingPeriodSummary($current['school_year'], $current['semester']);
            $can_redo        = $this->academic_model->canRedo();

            // auto lock any expired grading periods on dashboard load
            $this->academic_model->autoLockExpiredPeriods();
        }

        $next_period = null;
        if ($current) {
            $next_period = $this->resolveNextPeriodLabel($current['school_year'], $current['semester']);
        }

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

        if ($this->academic_model->hasPeriod()) {
            $this->jsonResponse(['success' => false, 'message' => 'A period is already initialized.'], 400);
        }

        $school_year = trim($_POST['school_year'] ?? '');
        $semester    = trim($_POST['semester']    ?? '');
        $errors      = [];

        if (empty($school_year)) $errors['school_year'] = 'School year is required.';
        if (empty($semester))    $errors['semester']    = 'Semester is required.';

        if (!empty($school_year) && !preg_match('/^\d{4}-\d{4}$/', $school_year)) {
            $errors['school_year'] = 'School year must be in YYYY-YYYY format.';
        }

        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 422);
        }

        $deadlines = $this->extractDeadlines();
        $result    = $this->academic_model->initializePeriod($school_year, $semester, $_SESSION['user_id'], $deadlines);

        if (!$result['success']) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to initialize period. Please try again.'], 500);
        }

        // LOG THIS ACTION
        logAction(
            'initialize_period',
            "Initialized academic period: {$semester} Semester {$school_year}",
            'school_settings',
            null,
            null,
            [
                'school_year' => $school_year,
                'semester' => $semester,
                'payment_records_created' => $result['created']
            ]
        );

        $this->jsonResponse(array_merge(
            ['success' => true, 'message' => "Period initialized: {$semester} Semester {$school_year}. {$result['created']} payment record(s) created."],
            $this->buildCurrentPeriodPayload($school_year, $semester)
        ));
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

        $current = $this->academic_model->getCurrentPeriod();

        // grading period lock check — warn before allowing advance
        if (!$this->academic_model->allGradingPeriodsLocked($current['school_year'], $current['semester'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'All grading periods must be locked before advancing to the next semester.',
            ], 422);
        }

        $deadlines = $this->extractDeadlines();
        $result    = $this->academic_model->advancePeriod($_SESSION['user_id'], $deadlines);

        if (!$result['success']) {
            $this->jsonResponse(['success' => false, 'message' => $result['error'] ?? 'Failed to advance period. Please try again.'], 500);
        }

        $next = $result['next'];

        // LOG THIS ACTION
        logAction(
            'advance_period',
            "Advanced academic period to: {$next['semester']} Semester {$next['school_year']}",
            'school_settings',
            null,
            [
                'previous' => [
                    'school_year' => $current['school_year'],
                    'semester' => $current['semester']
                ]
            ],
            [
                'current' => [
                    'school_year' => $next['school_year'],
                    'semester' => $next['semester']
                ],
                'payment_records_created' => $result['created']
            ]
        );

        $this->jsonResponse(array_merge(
            [
                'success' => true,
                'message' => "Advanced to {$next['semester']} Semester {$next['school_year']}. {$result['created']} payment record(s) created.",
                'created' => $result['created'],
            ],
            $this->buildCurrentPeriodPayload($next['school_year'], $next['semester'])
        ));
    }

    // ajax: undo the last period advancement
    public function ajaxUndo()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        $current = $this->academic_model->getCurrentPeriod();

        if (!$current) {
            $this->jsonResponse(['success' => false, 'message' => 'No active period to undo.'], 400);
        }

        // hard block if grades already exist — undo would cause data loss
        if ($this->academic_model->hasGradesForPeriod($current['school_year'], $current['semester'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Cannot undo: grades have already been submitted for the current period.',
            ], 422);
        }

        $result = $this->academic_model->undoPeriod($_SESSION['user_id']);

        if (!$result['success']) {
            $this->jsonResponse(['success' => false, 'message' => $result['error'] ?? 'Failed to undo period. Please try again.'], 500);
        }

        $prev = $result['previous'];

        // LOG THIS ACTION
        logAction(
            'undo_period',
            "Undid period advancement. Reverted to: {$prev['semester']} Semester {$prev['school_year']}",
            'school_settings',
            null,
            [
                'previous' => [
                    'school_year' => $current['school_year'],
                    'semester' => $current['semester']
                ]
            ],
            [
                'current' => [
                    'school_year' => $prev['school_year'],
                    'semester' => $prev['semester']
                ]
            ]
        );

        $this->jsonResponse(array_merge(
            [
                'success'  => true,
                'message'  => "Reverted to {$prev['semester']} Semester {$prev['school_year']}.",
                'can_redo' => true,
            ],
            $this->buildCurrentPeriodPayload($prev['school_year'], $prev['semester'])
        ));
    }

    // ajax: redo a previously undone advancement
    public function ajaxRedo()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        if (!$this->academic_model->canRedo()) {
            $this->jsonResponse(['success' => false, 'message' => 'Nothing to redo.'], 400);
        }

        $current = $this->academic_model->getCurrentPeriod();

        if (!$this->academic_model->allGradingPeriodsLocked($current['school_year'], $current['semester'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'All grading periods must be locked before redoing.',
            ], 422);
        }

        $deadlines = $this->extractDeadlines();
        $result    = $this->academic_model->redoPeriod($_SESSION['user_id'], $deadlines);

        if (!$result['success']) {
            $this->jsonResponse(['success' => false, 'message' => $result['error'] ?? 'Failed to redo period. Please try again.'], 500);
        }

        $target = $result['target'];

        // LOG THIS ACTION
        logAction(
            'redo_period',
            "Redid period advancement to: {$target['semester']} Semester {$target['school_year']}",
            'school_settings',
            null,
            [
                'previous' => [
                    'school_year' => $current['school_year'],
                    'semester' => $current['semester']
                ]
            ],
            [
                'current' => [
                    'school_year' => $target['school_year'],
                    'semester' => $target['semester']
                ],
                'payment_records_created' => $result['created']
            ]
        );

        $this->jsonResponse(array_merge(
            [
                'success'  => true,
                'message'  => "Redone to {$target['semester']} Semester {$target['school_year']}. {$result['created']} payment record(s) created.",
                'created'  => $result['created'],
                'can_redo' => $this->academic_model->canRedo(),
            ],
            $this->buildCurrentPeriodPayload($target['school_year'], $target['semester'])
        ));
    }

    // ajax: toggle lock status of a single grading period
    public function ajaxToggleGradingLock()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        $period_id = (int) ($_POST['period_id'] ?? 0);
        $is_locked = filter_var($_POST['is_locked'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$period_id) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid grading period.'], 422);
        }

        $result = $this->academic_model->toggleGradingPeriodLock($period_id, $is_locked);

        if (!$result) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update lock status.'], 500);
        }

        // GET PERIOD DETAILS FOR LOG
        $period = $this->academic_model->getGradingPeriodById($period_id);
        $action_text = $is_locked ? 'Locked' : 'Unlocked';
        $period_name = $period['grading_period'] ?? 'Unknown';

        // LOG THIS ACTION
        logAction(
            $is_locked ? 'lock_grading_period' : 'unlock_grading_period',
            $action_text . ' ' . $period_name . ' grading period',
            'grading_periods',
            $period_id,
            ['is_locked' => !$is_locked],
            ['is_locked' => $is_locked]
        );

        $current = $this->academic_model->getCurrentPeriod();
        $grading_periods = $current
            ? $this->academic_model->getGradingPeriodSummary($current['school_year'], $current['semester'])
            : [];

        $this->jsonResponse([
            'success'         => true,
            'message'         => $is_locked ? 'Grading period locked.' : 'Grading period unlocked.',
            'grading_periods' => $grading_periods,
            'all_locked'      => $current
                ? $this->academic_model->allGradingPeriodsLocked($current['school_year'], $current['semester'])
                : false,
        ]);
    }

    // ajax: lock all grading periods at once for the current period
    public function ajaxLockAllGrading()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        $current = $this->academic_model->getCurrentPeriod();

        if (!$current) {
            $this->jsonResponse(['success' => false, 'message' => 'No active period found.'], 400);
        }

        $periods = $this->academic_model->getGradingPeriods($current['school_year'], $current['semester']);

        foreach ($periods as $period) {
            $this->academic_model->lockGradingPeriod($period['period_id']);
        }

        // LOG THIS ACTION
        logAction(
            'lock_all_grading',
            "Locked all grading periods for {$current['semester']} Semester {$current['school_year']}",
            'grading_periods',
            null,
            null,
            [
                'school_year' => $current['school_year'],
                'semester' => $current['semester'],
                'periods_locked' => count($periods)
            ]
        );

        $grading_periods = $this->academic_model->getGradingPeriodSummary($current['school_year'], $current['semester']);

        $this->jsonResponse([
            'success'         => true,
            'message'         => 'All grading periods locked.',
            'grading_periods' => $grading_periods,
            'all_locked'      => true,
        ]);
    }

    // ajax: save grading period deadlines
    public function ajaxSaveGradingPeriods()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        $current = $this->academic_model->getCurrentPeriod();

        if (!$current) {
            $this->jsonResponse(['success' => false, 'message' => 'No active period found.'], 400);
        }

        $deadlines = $this->extractDeadlines();

        if (empty($deadlines)) {
            $this->jsonResponse(['success' => false, 'message' => 'No deadline data provided.'], 422);
        }

        $result = $this->academic_model->saveGradingPeriods($current['school_year'], $current['semester'], $deadlines);

        if (!$result) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to save grading periods.'], 500);
        }

        // LOG THIS ACTION
        logAction(
            'update_grading_deadlines',
            "Updated grading period deadlines for {$current['semester']} Semester {$current['school_year']}",
            'grading_periods',
            null,
            null,
            [
                'school_year' => $current['school_year'],
                'semester' => $current['semester'],
                'deadlines' => $deadlines
            ]
        );

        $grading_periods = $this->academic_model->getGradingPeriodSummary($current['school_year'], $current['semester']);

        $this->jsonResponse([
            'success'         => true,
            'message'         => 'Grading period deadlines saved.',
            'grading_periods' => $grading_periods,
        ]);
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

        // LOG THIS ACTION
        logAction(
            'graduate_students',
            "Graduated " . count($student_ids) . " student(s)",
            'students',
            null,
            null,
            ['student_ids' => $student_ids]
        );

        $active_count = $this->academic_model->getActiveStudentCount();
        $graduatable  = $this->academic_model->getGraduatableStudents();

        $this->jsonResponse([
            'success'      => true,
            'message'      => count($student_ids) . ' student(s) graduated successfully.',
            'active_count' => $active_count,
            'graduatable'  => $graduatable,
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

    // pull deadline fields from post — expects keys: deadline_prelim, deadline_midterm etc.
    private function extractDeadlines()
    {
        $keys = ['prelim', 'midterm', 'prefinal', 'final'];
        $deadlines = [];

        foreach ($keys as $key) {
            $value = trim($_POST["deadline_{$key}"] ?? '');
            if (!empty($value)) {
                $deadlines[$key] = $value;
            }
        }

        return $deadlines;
    }

    // build the common payload returned after any period mutation
    private function buildCurrentPeriodPayload($school_year, $semester)
    {
        $current         = $this->academic_model->getCurrentPeriod();
        $active_count    = $this->academic_model->getActiveStudentCount();
        $period_count    = $this->academic_model->getPaidStudentCount($school_year, $semester);
        $missing_config  = $this->academic_model->getStudentsMissingFeeConfig($school_year);
        $history         = $this->academic_model->getHistory(10);
        $grading_periods = $this->academic_model->getGradingPeriodSummary($school_year, $semester);
        $next_period     = $current ? $this->resolveNextPeriodLabel($school_year, $semester) : null;
        $can_redo        = $this->academic_model->canRedo();

        return compact(
            'current',
            'active_count',
            'period_count',
            'missing_config',
            'history',
            'grading_periods',
            'next_period',
            'can_redo'
        );
    }
}
