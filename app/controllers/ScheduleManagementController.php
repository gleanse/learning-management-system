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
        $this->teacher_model  = new Teacher();
        $this->subject_model  = new Subject();
        $this->section_model  = new Section();
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
            header('Location: index.php?page=teacher_schedules');
            exit();
        }
    }

    private function buildConflictMessages($conflict_details, $day_of_week)
    {
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

        return 'schedule conflict detected: ' . implode(' ', $error_messages);
    }

    public function showTeacherSchedulePage()
    {
        $this->requireAdmin();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // only pass the total count to the view — teacher list is loaded via ajax
        $total_teachers = $this->teacher_model->getTotalCount();

        $errors          = $_SESSION['schedule_errors'] ?? [];
        $success_message = $_SESSION['schedule_success'] ?? null;
        unset($_SESSION['schedule_errors'], $_SESSION['schedule_success']);

        require __DIR__ . '/../views/admin/teacher_schedules.php';
    }

    // ajax: paginated + searchable teacher list for the teacher picker
    public function ajaxGetTeachers()
    {
        $this->requireAdmin();

        $page    = isset($_GET['page_num']) ? max(1, (int) $_GET['page_num']) : 1;
        $search  = trim($_GET['search'] ?? '');
        $limit   = 10;
        $offset  = ($page - 1) * $limit;

        $teachers = $this->teacher_model->getWithPagination($limit, $offset, $search);
        $total    = $this->teacher_model->getTotalCount($search);

        $this->jsonResponse([
            'success'      => true,
            'teachers'     => $teachers,
            'total'        => $total,
            'page'         => $page,
            'limit'        => $limit,
            'total_pages'  => (int) ceil($total / $limit),
        ]);
    }

    // ajax: assignments + schedules for a teacher
    public function ajaxGetTeacherAssignments()
    {
        $this->requireAdmin();

        $teacher_id  = isset($_GET['teacher_id']) ? (int) $_GET['teacher_id'] : null;
        $school_year = $_GET['school_year'] ?? '2025-2026';
        $semester    = $_GET['semester']    ?? 'First';

        if (empty($teacher_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'teacher id is required.']);
        }

        $teacher = $this->teacher_model->getTeacherById($teacher_id);
        if (!$teacher) {
            $this->jsonResponse(['success' => false, 'message' => 'teacher not found.']);
        }

        $assignments = $this->schedule_model->getAssignmentsWithSchedules($teacher_id, $school_year, $semester);

        $this->jsonResponse([
            'success'     => true,
            'assignments' => $assignments,
            'teacher'     => $teacher,
        ]);
    }

    // ajax: refresh schedules for one assignment row after add/edit/delete
    public function ajaxGetAssignmentSchedules()
    {
        $this->requireAdmin();

        $teacher_id  = isset($_GET['teacher_id']) ? (int) $_GET['teacher_id'] : null;
        $subject_id  = isset($_GET['subject_id']) ? (int) $_GET['subject_id'] : null;
        $section_id  = isset($_GET['section_id']) ? (int) $_GET['section_id'] : null;
        $school_year = $_GET['school_year'] ?? '2025-2026';
        $semester    = $_GET['semester']    ?? 'First';

        if (empty($teacher_id) || empty($subject_id) || empty($section_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'teacher, subject, and section are required.']);
        }

        $schedules = $this->schedule_model->getSchedulesByAssignment($teacher_id, $subject_id, $section_id, $school_year, $semester);

        $this->jsonResponse(['success' => true, 'schedules' => $schedules]);
    }

    public function processCreateSchedule()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $return_page = $_POST['return_page'] ?? 'teacher_schedules';

        $teacher_id  = isset($_POST['teacher_id'])  ? (int) $_POST['teacher_id']  : null;
        $subject_id  = isset($_POST['subject_id'])  ? (int) $_POST['subject_id']  : null;
        $section_id  = isset($_POST['section_id'])  ? (int) $_POST['section_id']  : null;
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time  = $_POST['start_time']  ?? '';
        $end_time    = $_POST['end_time']    ?? '';
        $room        = trim($_POST['room']   ?? '');
        $school_year = $_POST['school_year'] ?? '2025-2026';
        $semester    = $_POST['semester']    ?? 'First';
        $status      = $_POST['status']      ?? 'active';

        $errors = [];

        if (empty($teacher_id))  $errors['teacher_id']  = 'please select a teacher.';
        if (empty($subject_id))  $errors['subject_id']  = 'please select a subject.';
        if (empty($section_id))  $errors['section_id']  = 'please select a section.';
        if (empty($day_of_week)) $errors['day_of_week'] = 'please select a day.';
        if (empty($start_time))  $errors['start_time']  = 'start time is required.';
        if (empty($end_time))    $errors['end_time']    = 'end time is required.';
        if (empty($school_year)) $errors['school_year'] = 'school year is required.';
        if (empty($semester))    $errors['semester']    = 'semester is required.';

        if (!empty($start_time) && !empty($end_time) && strtotime($start_time) >= strtotime($end_time)) {
            $errors['time'] = 'end time must be after start time.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'errors' => $errors]);
            $_SESSION['schedule_errors'] = $errors;
            header('Location: index.php?page=' . $return_page);
            exit();
        }

        if (!$this->teacher_model->getTeacherById($teacher_id)) {
            $msg = ['teacher_id' => 'selected teacher not found.'];
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'errors' => $msg]);
            $_SESSION['schedule_errors'] = $msg;
            header('Location: index.php?page=' . $return_page);
            exit();
        }

        if (!$this->subject_model->getById($subject_id)) {
            $msg = ['subject_id' => 'selected subject not found.'];
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'errors' => $msg]);
            $_SESSION['schedule_errors'] = $msg;
            header('Location: index.php?page=' . $return_page);
            exit();
        }

        if (!$this->section_model->getSectionById($section_id)) {
            $msg = ['section_id' => 'selected section not found.'];
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'errors' => $msg]);
            $_SESSION['schedule_errors'] = $msg;
            header('Location: index.php?page=' . $return_page);
            exit();
        }

        $conflicts = $this->schedule_model->checkAllConflicts($teacher_id, $section_id, $room, $day_of_week, $start_time, $end_time, $school_year, $semester);

        if (!empty($conflicts)) {
            $conflict_details = $this->schedule_model->getConflictingSchedules($teacher_id, $section_id, $room, $day_of_week, $start_time, $end_time, $school_year, $semester);
            $message = $this->buildConflictMessages($conflict_details, $day_of_week);
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'errors' => ['conflict' => $message]]);
            $_SESSION['schedule_errors'] = ['conflict' => $message];
            header('Location: index.php?page=' . $return_page);
            exit();
        }

        $result = $this->schedule_model->create($teacher_id, $subject_id, $section_id, $day_of_week, $start_time, $end_time, $room, $school_year, $semester, $status);

        if ($result) {
            if ($this->isAjax()) {
                $schedules = $this->schedule_model->getSchedulesByAssignment($teacher_id, $subject_id, $section_id, $school_year, $semester);
                $this->jsonResponse(['success' => true, 'message' => 'schedule created successfully.', 'schedules' => $schedules, 'row_key' => "{$teacher_id}_{$subject_id}_{$section_id}"]);
            }
            $_SESSION['schedule_success'] = 'schedule created successfully.';
            header('Location: index.php?page=teacher_schedules');
            exit();
        }

        if ($this->isAjax()) $this->jsonResponse(['success' => false, 'message' => 'failed to create schedule. please try again.']);
        $_SESSION['schedule_errors'] = ['general' => 'failed to create schedule. please try again.'];
        header('Location: index.php?page=' . $return_page);
        exit();
    }

    public function processUpdateSchedule()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $return_page = $_POST['return_page'] ?? 'teacher_schedules';

        $schedule_id = isset($_POST['schedule_id']) ? (int) $_POST['schedule_id'] : null;
        $teacher_id  = isset($_POST['teacher_id'])  ? (int) $_POST['teacher_id']  : null;
        $subject_id  = isset($_POST['subject_id'])  ? (int) $_POST['subject_id']  : null;
        $section_id  = isset($_POST['section_id'])  ? (int) $_POST['section_id']  : null;
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time  = $_POST['start_time']  ?? '';
        $end_time    = $_POST['end_time']    ?? '';
        $room        = trim($_POST['room']   ?? '');
        $school_year = $_POST['school_year'] ?? '2025-2026';
        $semester    = $_POST['semester']    ?? 'First';
        $status      = $_POST['status']      ?? 'active';

        $errors = [];

        if (empty($schedule_id)) $errors['schedule_id'] = 'schedule id is required.';
        if (empty($teacher_id))  $errors['teacher_id']  = 'please select a teacher.';
        if (empty($subject_id))  $errors['subject_id']  = 'please select a subject.';
        if (empty($section_id))  $errors['section_id']  = 'please select a section.';
        if (empty($day_of_week)) $errors['day_of_week'] = 'please select a day.';
        if (empty($start_time))  $errors['start_time']  = 'start time is required.';
        if (empty($end_time))    $errors['end_time']    = 'end time is required.';
        if (empty($school_year)) $errors['school_year'] = 'school year is required.';
        if (empty($semester))    $errors['semester']    = 'semester is required.';

        if (!empty($start_time) && !empty($end_time) && strtotime($start_time) >= strtotime($end_time)) {
            $errors['time'] = 'end time must be after start time.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'errors' => $errors]);
            $_SESSION['schedule_errors'] = $errors;
            header('Location: index.php?page=' . $return_page);
            exit();
        }

        if (!$this->schedule_model->getScheduleById($schedule_id)) {
            $msg = 'schedule not found.';
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'message' => $msg]);
            $_SESSION['schedule_errors'] = ['schedule_id' => $msg];
            header('Location: index.php?page=' . $return_page);
            exit();
        }

        if (!$this->teacher_model->getTeacherById($teacher_id)) {
            $msg = ['teacher_id' => 'selected teacher not found.'];
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'errors' => $msg]);
            $_SESSION['schedule_errors'] = $msg;
            header('Location: index.php?page=' . $return_page);
            exit();
        }

        if (!$this->subject_model->getById($subject_id)) {
            $msg = ['subject_id' => 'selected subject not found.'];
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'errors' => $msg]);
            $_SESSION['schedule_errors'] = $msg;
            header('Location: index.php?page=' . $return_page);
            exit();
        }

        if (!$this->section_model->getSectionById($section_id)) {
            $msg = ['section_id' => 'selected section not found.'];
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'errors' => $msg]);
            $_SESSION['schedule_errors'] = $msg;
            header('Location: index.php?page=' . $return_page);
            exit();
        }

        $conflicts = $this->schedule_model->checkAllConflicts($teacher_id, $section_id, $room, $day_of_week, $start_time, $end_time, $school_year, $semester, $schedule_id);

        if (!empty($conflicts)) {
            $conflict_details = $this->schedule_model->getConflictingSchedules($teacher_id, $section_id, $room, $day_of_week, $start_time, $end_time, $school_year, $semester, $schedule_id);
            $message = $this->buildConflictMessages($conflict_details, $day_of_week);
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'errors' => ['conflict' => $message]]);
            $_SESSION['schedule_errors'] = ['conflict' => $message];
            header('Location: index.php?page=' . $return_page);
            exit();
        }

        $result = $this->schedule_model->update($schedule_id, $teacher_id, $subject_id, $section_id, $day_of_week, $start_time, $end_time, $room, $school_year, $semester, $status);

        if ($result) {
            if ($this->isAjax()) {
                $schedules = $this->schedule_model->getSchedulesByAssignment($teacher_id, $subject_id, $section_id, $school_year, $semester);
                $this->jsonResponse(['success' => true, 'message' => 'schedule updated successfully.', 'schedules' => $schedules, 'row_key' => "{$teacher_id}_{$subject_id}_{$section_id}"]);
            }
            $_SESSION['schedule_success'] = 'schedule updated successfully.';
            header('Location: index.php?page=teacher_schedules');
            exit();
        }

        if ($this->isAjax()) $this->jsonResponse(['success' => false, 'message' => 'failed to update schedule. please try again.']);
        $_SESSION['schedule_errors'] = ['general' => 'failed to update schedule. please try again.'];
        header('Location: index.php?page=' . $return_page);
        exit();
    }

    public function processDeleteSchedule()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $schedule_id = isset($_POST['schedule_id']) ? (int) $_POST['schedule_id'] : null;
        $teacher_id  = isset($_POST['teacher_id'])  ? (int) $_POST['teacher_id']  : null;
        $subject_id  = isset($_POST['subject_id'])  ? (int) $_POST['subject_id']  : null;
        $section_id  = isset($_POST['section_id'])  ? (int) $_POST['section_id']  : null;
        $school_year = $_POST['school_year'] ?? '2025-2026';
        $semester    = $_POST['semester']    ?? 'First';

        if (empty($schedule_id)) {
            $message = 'schedule id is required.';
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'message' => $message]);
            $_SESSION['schedule_errors'] = ['schedule_id' => $message];
            header('Location: index.php?page=teacher_schedules');
            exit();
        }

        if (!$this->schedule_model->getScheduleById($schedule_id)) {
            $message = 'schedule not found.';
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'message' => $message]);
            $_SESSION['schedule_errors'] = ['schedule_id' => $message];
            header('Location: index.php?page=teacher_schedules');
            exit();
        }

        $result = $this->schedule_model->delete($schedule_id);

        if ($result) {
            if ($this->isAjax()) {
                $schedules = ($teacher_id && $subject_id && $section_id)
                    ? $this->schedule_model->getSchedulesByAssignment($teacher_id, $subject_id, $section_id, $school_year, $semester)
                    : [];
                $this->jsonResponse(['success' => true, 'message' => 'schedule deleted successfully.', 'schedules' => $schedules, 'row_key' => "{$teacher_id}_{$subject_id}_{$section_id}"]);
            }
            $_SESSION['schedule_success'] = 'schedule deleted successfully.';
        } else {
            if ($this->isAjax()) $this->jsonResponse(['success' => false, 'message' => 'failed to delete schedule. please try again.']);
            $_SESSION['schedule_errors'] = ['general' => 'failed to delete schedule. please try again.'];
        }

        header('Location: index.php?page=teacher_schedules');
        exit();
    }
}
