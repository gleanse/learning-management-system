<?php

require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Grade.php';
require_once __DIR__ . '/../models/GradingPeriod.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/Section.php';
require_once __DIR__ . '/../models/AcademicPeriod.php';
require_once __DIR__ . '/../helpers/activity_logger.php';

class GradeController
{
    private $teacher_model;
    private $student_model;
    private $grade_model;
    private $grading_period_model;
    private $subject_model;
    private $section_model;
    private $academic_model;

    public function __construct()
    {
        $this->teacher_model        = new Teacher();
        $this->student_model        = new Student();
        $this->grade_model          = new Grade();
        $this->grading_period_model = new GradingPeriod();
        $this->subject_model        = new Subject();
        $this->section_model        = new Section();
        $this->academic_model       = new AcademicPeriod();
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

    private function requireTeacher()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Unauthorized.'], 403);
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
                $this->jsonResponse(['success' => false, 'message' => 'Your session expired. Please refresh and try again.'], 403);
            }

            $_SESSION['grading_errors'] = ['general' => 'Your session expired. Please try again.'];
            header('Location: index.php?page=teacher_dashboard');
            exit();
        }
    }

    public function showTeacherDashboard()
    {
        $this->requireTeacher();

        $teacher_id = $_SESSION['user_id'];

        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $year_levels = $this->teacher_model->getAssignedYearLevels($teacher_id, $school_year);

        require_once __DIR__ . '/../models/Schedule.php';
        $schedule_model = new Schedule();
        $current_date   = date('l, F j, Y');
        $today_schedule = $schedule_model->getTodaySchedule($teacher_id, $school_year, $semester);

        foreach ($today_schedule as $key => $schedule) {
            $today_schedule[$key]['time_range']   = date('g:i A', strtotime($schedule['start_time'])) . ' - ' .
                date('g:i A', strtotime($schedule['end_time']));
            $today_schedule[$key]['room_display'] = !empty($schedule['room']) ? $schedule['room'] : 'Not Assigned';
        }

        require __DIR__ . '/../views/teacher/teacher_dashboard.php';
    }

    public function showYearLevels()
    {
        $this->requireTeacher();

        $teacher_id      = $_SESSION['user_id'];
        $current         = $this->academic_model->getCurrentPeriod();
        $available_years = $this->teacher_model->getAssignedSchoolYears($teacher_id);

        $school_year = $_GET['school_year'] ?? ($current['school_year'] ?? '');
        $semester    = $_GET['semester']    ?? ($current['semester']    ?? 'First');

        $year_levels = $this->teacher_model->getAssignedYearLevels($teacher_id, $school_year);

        require __DIR__ . '/../views/teacher/year_levels.php';
    }

    public function showSubjects()
    {
        $this->requireTeacher();

        $teacher_id = $_SESSION['user_id'];
        $year_level = $_GET['year_level'] ?? null;

        if (empty($year_level)) {
            header('Location: index.php?page=teacher_dashboard');
            exit();
        }

        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $_GET['school_year'] ?? ($current['school_year'] ?? '');
        $semester    = $_GET['semester']    ?? ($current['semester']    ?? 'First');

        // pass school year so only subjects for the selected year are shown
        $subjects = $this->teacher_model->getAssignedSubjectsGrouped($teacher_id, $year_level, $school_year);

        require __DIR__ . '/../views/teacher/subjects.php';
    }

    public function showSections()
    {
        $this->requireTeacher();

        $year_level  = $_GET['year_level'] ?? null;
        $subject_id  = $_GET['subject_id'] ?? null;
        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $_GET['school_year'] ?? ($current['school_year'] ?? '');
        $semester    = $_GET['semester']    ?? ($current['semester']    ?? 'First');

        if (empty($year_level) || empty($subject_id)) {
            header('Location: index.php?page=teacher_dashboard');
            exit();
        }

        $subject_model = new Subject();
        $subject       = $subject_model->getById($subject_id);

        $teacher_id = $_SESSION['user_id'];
        $sections   = $this->teacher_model->getSectionsBySubject($teacher_id, $subject_id, $year_level, $school_year);

        require __DIR__ . '/../views/teacher/sections.php';
    }

    public function showStudentList()
    {
        $this->requireTeacher();

        $year_level     = $_GET['year_level']     ?? null;
        $subject_id     = $_GET['subject_id']     ?? null;
        $section_id     = $_GET['section_id']     ?? null;
        $current        = $this->academic_model->getCurrentPeriod();
        $school_year    = $_GET['school_year']    ?? ($current['school_year'] ?? '');
        $semester       = $_GET['semester']       ?? ($current['semester']    ?? 'First');
        $grading_period = $_GET['grading_period'] ?? 'Prelim';

        $errors = [];

        if (empty($year_level))      $errors['year_level']     = 'Year level is required.';
        if (empty($subject_id))      $errors['subject']        = 'Please select a subject.';
        if (empty($section_id))      $errors['section']        = 'Please select a section.';
        if (empty($school_year))     $errors['school_year']    = 'Please select a school year.';
        if (empty($semester))        $errors['semester']       = 'Please select a semester.';
        if (empty($grading_period))  $errors['grading_period'] = 'Please select a grading period.';

        if (!empty($errors)) {
            $_SESSION['grading_errors'] = $errors;
            header('Location: index.php?page=teacher_dashboard');
            exit();
        }

        $subject = $this->subject_model->getById($subject_id);
        if (!$subject) {
            $_SESSION['grading_errors'] = ['subject' => 'Subject not found.'];
            header('Location: index.php?page=teacher_dashboard');
            exit();
        }

        $section = $this->section_model->getSectionById($section_id);
        if (!$section) {
            $_SESSION['grading_errors'] = ['section' => 'Section not found.'];
            header('Location: index.php?page=teacher_dashboard');
            exit();
        }

        $is_locked = $this->grading_period_model->isLocked($school_year, $semester, $grading_period);
        $students  = $this->student_model->getEnrolledStudentsInSubject($subject_id, $section_id, $school_year, $semester);

        foreach ($students as &$student) {
            $existing_grade = $this->grade_model->getGrade(
                $student['student_id'],
                $subject_id,
                $grading_period,
                $semester,
                $school_year
            );

            $student['grade_value'] = $existing_grade['grade_value'] ?? '';
            $student['remarks']     = $existing_grade['remarks']     ?? '';

            if (!empty($student['grade_value'])) {
                $student['percentage_display'] = number_format($student['grade_value'], 2);
                $student['gpa_display']        = number_format($this->percentageToGPA($student['grade_value']), 2);
            } else {
                $student['percentage_display'] = null;
                $student['gpa_display']        = null;
            }
        }
        // break reference to prevent array corruption in the view
        unset($student);

        $success_message = $_SESSION['success_message'] ?? null;
        unset($_SESSION['success_message']);

        $teacher_id      = $_SESSION['user_id'];
        $available_years = $this->teacher_model->getAssignedSchoolYears($teacher_id);

        require __DIR__ . '/../views/teacher/student_list.php';
    }

    public function processSaveGrade()
    {
        $this->requireTeacher();
        $this->validateCsrf();

        $teacher_id     = $_SESSION['user_id'];
        $student_id     = $_POST['student_id']    ?? null;
        $subject_id     = $_POST['subject_id']    ?? null;
        $section_id     = $_POST['section_id']    ?? null;
        $year_level     = $_POST['year_level']    ?? null;
        $school_year    = $_POST['school_year']   ?? null;
        $semester       = $_POST['semester']      ?? null;
        $grading_period = $_POST['grading_period'] ?? null;
        $grade_value    = trim($_POST['grade_value'] ?? '');
        $grade_format   = $_POST['grade_format']  ?? 'percentage';
        $remarks        = trim($_POST['remarks']   ?? '');

        $errors = [];

        if (empty($student_id))     $errors['student_id']     = 'Student is required.';
        if (empty($subject_id))     $errors['subject_id']     = 'Subject is required.';
        if (empty($school_year))    $errors['school_year']    = 'School year is required.';
        if (empty($semester))       $errors['semester']       = 'Semester is required.';
        if (empty($grading_period)) $errors['grading_period'] = 'Grading period is required.';
        if (empty($grade_value))    $errors['grade_value']    = 'Grade value is required.';

        if (!empty($grade_value) && !is_numeric($grade_value)) {
            $errors['grade_value'] = 'Grade must be a number.';
        }

        // get student and subject for log description
        $student = $this->student_model->getStudentById($student_id);
        $subject = $this->subject_model->getById($subject_id);

        // convert gpa to percentage if needed then validate
        $percentage_value = $grade_value;
        if ($grade_format === 'gpa' && !empty($grade_value) && is_numeric($grade_value)) {
            $gpa_value = floatval($grade_value);

            if ($gpa_value < 1.0 || $gpa_value > 5.0) {
                $errors['grade_value'] = 'GPA must be between 1.0 and 5.0.';
            } else {
                $percentage_value = $this->gpaToPercentage($gpa_value);
                if ($percentage_value === false) {
                    $errors['grade_value'] = 'Invalid GPA value. Please use: 1.0, 1.25, 1.5, 1.75, 2.0, 2.25, 2.5, 2.75, 3.0, or 5.0.';
                }
            }
        } elseif ($grade_format === 'percentage' && !empty($grade_value) && is_numeric($grade_value)) {
            $percentage_num = floatval($grade_value);
            if ($percentage_num < 0 || $percentage_num > 100) {
                $errors['grade_value'] = 'Percentage must be between 0 and 100.';
            }
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'errors' => $errors]);
            }
            $_SESSION['grading_errors'] = $errors;
            header("Location: index.php?page=grading_students&year_level=$year_level&subject_id=$subject_id&section_id=$section_id&school_year=$school_year&semester=$semester&grading_period=$grading_period");
            exit();
        }

        $is_locked = $this->grading_period_model->isLocked($school_year, $semester, $grading_period);

        if ($is_locked) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Grading period is locked. You cannot submit grades at this time.']);
            }
            $_SESSION['grading_errors'] = ['general' => 'Grading period is locked. You cannot submit grades at this time.'];
            header("Location: index.php?page=grading_students&year_level=$year_level&subject_id=$subject_id&section_id=$section_id&school_year=$school_year&semester=$semester&grading_period=$grading_period");
            exit();
        }

        // check if grade already exists (for before/after)
        $existing_grade = $this->grade_model->getGrade(
            $student_id,
            $subject_id,
            $grading_period,
            $semester,
            $school_year
        );

        // store grades as percentage internally
        $grade_data = [
            'student_id'     => $student_id,
            'subject_id'     => $subject_id,
            'teacher_id'     => $teacher_id,
            'grading_period' => $grading_period,
            'semester'       => $semester,
            'grade_value'    => $percentage_value,
            'remarks'        => $remarks,
            'school_year'    => $school_year
        ];

        $result = $this->grade_model->saveGrade($grade_data);

        if ($result) {
            // LOG GRADE SAVE
            $action = $existing_grade ? 'update_grade' : 'create_grade';
            $action_text = $existing_grade ? 'Updated' : 'Added';
            
            logAction(
                $action,
                "{$action_text} grade for {$student['first_name']} {$student['last_name']} - {$subject['subject_code']} ({$grading_period})",
                'grades',
                $existing_grade ? $existing_grade['grade_id'] : null,
                $existing_grade ?: null,
                [
                    'student_id' => $student_id,
                    'subject_id' => $subject_id,
                    'grading_period' => $grading_period,
                    'semester' => $semester,
                    'school_year' => $school_year,
                    'grade_value' => $percentage_value,
                    'remarks' => $remarks
                ]
            );

            if ($this->isAjax()) {
                $this->jsonResponse(['success' => true, 'message' => 'Grade saved successfully.']);
            }
            $_SESSION['success_message'] = 'Grade saved successfully.';
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to save grade. Please try again.']);
            }
            $_SESSION['grading_errors'] = ['general' => 'Failed to save grade. Please try again.'];
        }

        header("Location: index.php?page=grading_students&year_level=$year_level&subject_id=$subject_id&section_id=$section_id&school_year=$school_year&semester=$semester&grading_period=$grading_period");
        exit();
    }

    private function gpaToPercentage($gpa)
    {
        $conversion = [
            '1.00' => 98,
            '1.25' => 95.5,
            '1.50' => 92.5,
            '1.75' => 90.5,
            '2.00' => 87.5,
            '2.25' => 84.5,
            '2.50' => 81.5,
            '2.75' => 78.5,
            '3.00' => 75,
            '5.00' => 50
        ];

        $gpa_key = number_format((float)$gpa, 2, '.', '');
        return $conversion[$gpa_key] ?? false;
    }

    public function percentageToGPA($percentage)
    {
        if ($percentage >= 97) return 1.0;
        if ($percentage >= 94) return 1.25;
        if ($percentage >= 91) return 1.5;
        if ($percentage >= 88) return 1.75;
        if ($percentage >= 85) return 2.0;
        if ($percentage >= 82) return 2.25;
        if ($percentage >= 79) return 2.5;
        if ($percentage >= 76) return 2.75;
        if ($percentage >= 75) return 3.0;
        return 5.0;
    }

    public function getGPARange($gpa)
    {
        $ranges = [
            '1.00' => '97-100',
            '1.25' => '94-96',
            '1.50' => '91-93',
            '1.75' => '88-90',
            '2.00' => '85-87',
            '2.25' => '82-84',
            '2.50' => '79-81',
            '2.75' => '76-78',
            '3.00' => '75',
            '5.00' => '0-74'
        ];

        $gpa_key = number_format((float)$gpa, 2, '.', '');
        return $ranges[$gpa_key] ?? 'N/A';
    }
}
