<?php

require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/Section.php';

class ScheduleManagementController
{
    private $schedule_model;
    private $teacher_model;
    private $subject_model;
    private $section_model;

    public function __construct()
    {
        $this->schedule_model = new Schedule();
        $this->teacher_model = new Teacher();
        $this->subject_model = new Subject();
        $this->section_model = new Section();
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

    private function requireAdmin()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'unauthorized access.'], 403);
            }
            header('Location: index.php?page=login');
            exit();
        }
    }

    private function validateCsrf()
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'session expired. please refresh and try again.'], 403);
            }

            $_SESSION['schedule_errors'] = ['general' => 'session expired. please try again.'];
            header('Location: index.php?page=manage_schedules');
            exit();
        }
    }

    // generate display schedule id (SCH-YEAR-ID)
    private function generateScheduleDisplayId($schedule_id, $school_year)
    {
        $year = explode('-', $school_year)[0];
        return 'SCH-' . $year . '-' . str_pad($schedule_id, 3, '0', STR_PAD_LEFT);
    }

    public function showScheduleManagement()
    {
        $this->requireAdmin();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $school_year = $_GET['school_year'] ?? '2025-2026';
        $semester = $_GET['semester'] ?? 'First';
        $status = $_GET['status'] ?? 'active';

        // get all schedules
        $schedules = $this->schedule_model->getAllSchedules($school_year, $semester, $status);

        // add display id to each schedule
        foreach ($schedules as &$schedule) {
            $schedule['display_id'] = $this->generateScheduleDisplayId($schedule['schedule_id'], $schedule['school_year']);
        }

        $errors = $_SESSION['schedule_errors'] ?? [];
        $success_message = $_SESSION['schedule_success'] ?? null;
        unset($_SESSION['schedule_errors'], $_SESSION['schedule_success']);

        require __DIR__ . '/../views/admin/manage_schedules.php';
    }

    public function showCreateSchedule()
    {
        $this->requireAdmin();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // get dropdown data
        $teachers = $this->teacher_model->getAllActiveTeachers();
        $subjects = $this->subject_model->getAll();
        $sections = $this->section_model->getAllSections();

        $errors = $_SESSION['schedule_errors'] ?? [];
        $old_input = $_SESSION['old_input'] ?? [];
        unset($_SESSION['schedule_errors'], $_SESSION['old_input']);

        require __DIR__ . '/../views/admin/create_schedule.php';
    }

    public function showEditSchedule()
    {
        $this->requireAdmin();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $schedule_id = isset($_GET['id']) ? (int) $_GET['id'] : null;

        if (empty($schedule_id)) {
            $_SESSION['schedule_errors'] = ['general' => 'schedule id is required.'];
            header('Location: index.php?page=manage_schedules');
            exit();
        }

        // get schedule data
        $schedule = $this->schedule_model->getScheduleById($schedule_id);

        if (!$schedule) {
            $_SESSION['schedule_errors'] = ['general' => 'schedule not found.'];
            header('Location: index.php?page=manage_schedules');
            exit();
        }

        // add display id
        $schedule['display_id'] = $this->generateScheduleDisplayId($schedule['schedule_id'], $schedule['school_year']);

        // get dropdown data
        $teachers = $this->teacher_model->getAllActiveTeachers();
        $subjects = $this->subject_model->getAll();
        $sections = $this->section_model->getAllSections();

        $errors = $_SESSION['schedule_errors'] ?? [];
        unset($_SESSION['schedule_errors']);

        require __DIR__ . '/../views/admin/edit_schedule.php';
    }

    public function processCreateSchedule()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $teacher_id = isset($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : null;
        $subject_id = isset($_POST['subject_id']) ? (int) $_POST['subject_id'] : null;
        $section_id = isset($_POST['section_id']) ? (int) $_POST['section_id'] : null;
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $room = trim($_POST['room'] ?? '');
        $school_year = $_POST['school_year'] ?? '2025-2026';
        $semester = $_POST['semester'] ?? 'First';
        $status = $_POST['status'] ?? 'active';

        $errors = [];

        // validate required fields
        if (empty($teacher_id)) {
            $errors['teacher_id'] = 'please select a teacher.';
        }

        if (empty($subject_id)) {
            $errors['subject_id'] = 'please select a subject.';
        }

        if (empty($section_id)) {
            $errors['section_id'] = 'please select a section.';
        }

        if (empty($day_of_week)) {
            $errors['day_of_week'] = 'please select a day.';
        }

        if (empty($start_time)) {
            $errors['start_time'] = 'start time is required.';
        }

        if (empty($end_time)) {
            $errors['end_time'] = 'end time is required.';
        }

        if (empty($school_year)) {
            $errors['school_year'] = 'school year is required.';
        }

        if (empty($semester)) {
            $errors['semester'] = 'semester is required.';
        }

        // validate time logic
        if (!empty($start_time) && !empty($end_time)) {
            if (strtotime($start_time) >= strtotime($end_time)) {
                $errors['time'] = 'end time must be after start time.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['schedule_errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: index.php?page=create_schedule');
            exit();
        }

        // validate entities exist
        $teacher = $this->teacher_model->getTeacherById($teacher_id);
        if (!$teacher) {
            $_SESSION['schedule_errors'] = ['teacher_id' => 'selected teacher not found.'];
            $_SESSION['old_input'] = $_POST;
            header('Location: index.php?page=create_schedule');
            exit();
        }

        $subject = $this->subject_model->getById($subject_id);
        if (!$subject) {
            $_SESSION['schedule_errors'] = ['subject_id' => 'selected subject not found.'];
            $_SESSION['old_input'] = $_POST;
            header('Location: index.php?page=create_schedule');
            exit();
        }

        $section = $this->section_model->getSectionById($section_id);
        if (!$section) {
            $_SESSION['schedule_errors'] = ['section_id' => 'selected section not found.'];
            $_SESSION['old_input'] = $_POST;
            header('Location: index.php?page=create_schedule');
            exit();
        }

        // check for conflicts
        $conflicts = $this->schedule_model->checkAllConflicts(
            $teacher_id,
            $section_id,
            $room,
            $day_of_week,
            $start_time,
            $end_time,
            $school_year,
            $semester
        );

        if (!empty($conflicts)) {
            $conflict_details = $this->schedule_model->getConflictingSchedules(
                $teacher_id,
                $section_id,
                $room,
                $day_of_week,
                $start_time,
                $end_time,
                $school_year,
                $semester
            );

            $error_messages = [];
            foreach ($conflict_details as $conflict) {
                $time_display = date('g:i A', strtotime($conflict['start_time'])) . ' - ' . date('g:i A', strtotime($conflict['end_time']));

                if ($conflict['conflict_type'] === 'teacher') {
                    $error_messages[] = "teacher {$conflict['teacher_name']} already has {$conflict['subject_name']} for {$conflict['section_name']} on {$day_of_week} at {$time_display}.";
                } elseif ($conflict['conflict_type'] === 'section') {
                    $error_messages[] = "section {$conflict['section_name']} already has {$conflict['subject_name']} with {$conflict['teacher_name']} on {$day_of_week} at {$time_display}.";
                } elseif ($conflict['conflict_type'] === 'room') {
                    $error_messages[] = "room {$conflict['room']} is already occupied by {$conflict['teacher_name']} teaching {$conflict['subject_name']} on {$day_of_week} at {$time_display}.";
                }
            }

            $message = 'schedule conflict detected: ' . implode(' ', $error_messages);
            $_SESSION['schedule_errors'] = ['conflict' => $message];
            $_SESSION['old_input'] = $_POST;
            header('Location: index.php?page=create_schedule');
            exit();
        }

        // create schedule
        $result = $this->schedule_model->create(
            $teacher_id,
            $subject_id,
            $section_id,
            $day_of_week,
            $start_time,
            $end_time,
            $room,
            $school_year,
            $semester,
            $status
        );

        if ($result) {
            $_SESSION['schedule_success'] = 'schedule created successfully.';
            header('Location: index.php?page=manage_schedules');
            exit();
        } else {
            $_SESSION['schedule_errors'] = ['general' => 'failed to create schedule. please try again.'];
            $_SESSION['old_input'] = $_POST;
            header('Location: index.php?page=create_schedule');
            exit();
        }
    }

    public function processUpdateSchedule()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $schedule_id = isset($_POST['schedule_id']) ? (int) $_POST['schedule_id'] : null;
        $teacher_id = isset($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : null;
        $subject_id = isset($_POST['subject_id']) ? (int) $_POST['subject_id'] : null;
        $section_id = isset($_POST['section_id']) ? (int) $_POST['section_id'] : null;
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $room = trim($_POST['room'] ?? '');
        $school_year = $_POST['school_year'] ?? '2025-2026';
        $semester = $_POST['semester'] ?? 'First';
        $status = $_POST['status'] ?? 'active';

        $errors = [];

        // validate required fields
        if (empty($schedule_id)) {
            $errors['schedule_id'] = 'schedule id is required.';
        }

        if (empty($teacher_id)) {
            $errors['teacher_id'] = 'please select a teacher.';
        }

        if (empty($subject_id)) {
            $errors['subject_id'] = 'please select a subject.';
        }

        if (empty($section_id)) {
            $errors['section_id'] = 'please select a section.';
        }

        if (empty($day_of_week)) {
            $errors['day_of_week'] = 'please select a day.';
        }

        if (empty($start_time)) {
            $errors['start_time'] = 'start time is required.';
        }

        if (empty($end_time)) {
            $errors['end_time'] = 'end time is required.';
        }

        if (empty($school_year)) {
            $errors['school_year'] = 'school year is required.';
        }

        if (empty($semester)) {
            $errors['semester'] = 'semester is required.';
        }

        // validate time logic
        if (!empty($start_time) && !empty($end_time)) {
            if (strtotime($start_time) >= strtotime($end_time)) {
                $errors['time'] = 'end time must be after start time.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['schedule_errors'] = $errors;
            header('Location: index.php?page=edit_schedule&id=' . $schedule_id);
            exit();
        }

        // validate schedule exists
        $existing_schedule = $this->schedule_model->getScheduleById($schedule_id);
        if (!$existing_schedule) {
            $_SESSION['schedule_errors'] = ['schedule_id' => 'schedule not found.'];
            header('Location: index.php?page=manage_schedules');
            exit();
        }

        // validate entities exist
        $teacher = $this->teacher_model->getTeacherById($teacher_id);
        if (!$teacher) {
            $_SESSION['schedule_errors'] = ['teacher_id' => 'selected teacher not found.'];
            header('Location: index.php?page=edit_schedule&id=' . $schedule_id);
            exit();
        }

        $subject = $this->subject_model->getById($subject_id);
        if (!$subject) {
            $_SESSION['schedule_errors'] = ['subject_id' => 'selected subject not found.'];
            header('Location: index.php?page=edit_schedule&id=' . $schedule_id);
            exit();
        }

        $section = $this->section_model->getSectionById($section_id);
        if (!$section) {
            $_SESSION['schedule_errors'] = ['section_id' => 'selected section not found.'];
            header('Location: index.php?page=edit_schedule&id=' . $schedule_id);
            exit();
        }

        // check for conflicts (exclude current schedule)
        $conflicts = $this->schedule_model->checkAllConflicts(
            $teacher_id,
            $section_id,
            $room,
            $day_of_week,
            $start_time,
            $end_time,
            $school_year,
            $semester,
            $schedule_id
        );

        if (!empty($conflicts)) {
            $conflict_details = $this->schedule_model->getConflictingSchedules(
                $teacher_id,
                $section_id,
                $room,
                $day_of_week,
                $start_time,
                $end_time,
                $school_year,
                $semester,
                $schedule_id
            );

            $error_messages = [];
            foreach ($conflict_details as $conflict) {
                $time_display = date('g:i A', strtotime($conflict['start_time'])) . ' - ' . date('g:i A', strtotime($conflict['end_time']));

                if ($conflict['conflict_type'] === 'teacher') {
                    $error_messages[] = "teacher {$conflict['teacher_name']} already has {$conflict['subject_name']} for {$conflict['section_name']} on {$day_of_week} at {$time_display}.";
                } elseif ($conflict['conflict_type'] === 'section') {
                    $error_messages[] = "section {$conflict['section_name']} already has {$conflict['subject_name']} with {$conflict['teacher_name']} on {$day_of_week} at {$time_display}.";
                } elseif ($conflict['conflict_type'] === 'room') {
                    $error_messages[] = "room {$conflict['room']} is already occupied by {$conflict['teacher_name']} teaching {$conflict['subject_name']} on {$day_of_week} at {$time_display}.";
                }
            }

            $message = 'schedule conflict detected: ' . implode(' ', $error_messages);
            $_SESSION['schedule_errors'] = ['conflict' => $message];
            header('Location: index.php?page=edit_schedule&id=' . $schedule_id);
            exit();
        }

        // update schedule
        $result = $this->schedule_model->update(
            $schedule_id,
            $teacher_id,
            $subject_id,
            $section_id,
            $day_of_week,
            $start_time,
            $end_time,
            $room,
            $school_year,
            $semester,
            $status
        );

        if ($result) {
            $_SESSION['schedule_success'] = 'schedule updated successfully.';
            header('Location: index.php?page=manage_schedules');
            exit();
        } else {
            $_SESSION['schedule_errors'] = ['general' => 'failed to update schedule. please try again.'];
            header('Location: index.php?page=edit_schedule&id=' . $schedule_id);
            exit();
        }
    }

    public function processDeleteSchedule()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $schedule_id = isset($_POST['schedule_id']) ? (int) $_POST['schedule_id'] : null;

        if (empty($schedule_id)) {
            $message = 'schedule id is required.';
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => $message]);
            }
            $_SESSION['schedule_errors'] = ['schedule_id' => $message];
            header('Location: index.php?page=manage_schedules');
            exit();
        }

        // validate schedule exists
        $schedule = $this->schedule_model->getScheduleById($schedule_id);
        if (!$schedule) {
            $message = 'schedule not found.';
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => $message]);
            }
            $_SESSION['schedule_errors'] = ['schedule_id' => $message];
            header('Location: index.php?page=manage_schedules');
            exit();
        }

        $result = $this->schedule_model->delete($schedule_id);

        if ($result) {
            $message = 'schedule deleted successfully.';

            if ($this->isAjax()) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => $message
                ]);
            }

            $_SESSION['schedule_success'] = $message;
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'failed to delete schedule. please try again.']);
            }
            $_SESSION['schedule_errors'] = ['general' => 'failed to delete schedule. please try again.'];
        }

        header('Location: index.php?page=manage_schedules');
        exit();
    }

    public function processToggleStatus()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $schedule_id = isset($_POST['schedule_id']) ? (int) $_POST['schedule_id'] : null;
        $new_status = $_POST['status'] ?? '';

        if (empty($schedule_id)) {
            $message = 'schedule id is required.';
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => $message]);
            }
            $_SESSION['schedule_errors'] = ['schedule_id' => $message];
            header('Location: index.php?page=manage_schedules');
            exit();
        }

        if (!in_array($new_status, ['active', 'inactive'])) {
            $message = 'invalid status value.';
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => $message]);
            }
            $_SESSION['schedule_errors'] = ['status' => $message];
            header('Location: index.php?page=manage_schedules');
            exit();
        }

        // validate schedule exists
        $schedule = $this->schedule_model->getScheduleById($schedule_id);
        if (!$schedule) {
            $message = 'schedule not found.';
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => $message]);
            }
            $_SESSION['schedule_errors'] = ['schedule_id' => $message];
            header('Location: index.php?page=manage_schedules');
            exit();
        }

        $result = $this->schedule_model->updateStatus($schedule_id, $new_status);

        if ($result) {
            $status_text = $new_status === 'active' ? 'activated' : 'deactivated';
            $message = "schedule {$status_text} successfully.";

            if ($this->isAjax()) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => $message,
                    'new_status' => $new_status
                ]);
            }

            $_SESSION['schedule_success'] = $message;
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'failed to update status. please try again.']);
            }
            $_SESSION['schedule_errors'] = ['general' => 'failed to update status. please try again.'];
        }

        header('Location: index.php?page=manage_schedules');
        exit();
    }
}
