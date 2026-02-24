<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/AcademicPeriod.php';

class StudentController
{
    private $student_model;
    private $subject_model;
    private $academic_model;

    public function __construct()
    {
        $this->student_model  = new Student();
        $this->subject_model  = new Subject();
        $this->academic_model = new AcademicPeriod();
    }

    // check if student is logged in and has a valid student record
    private function requireStudent()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            header('Location: index.php?page=login');
            exit();
        }

        $student_info = $this->student_model->getStudentInfoByUserId($_SESSION['user_id']);

        if (!$student_info) {
            session_destroy();
            header('Location: index.php?page=login');
            exit();
        }

        return $student_info;
    }

    // get all distinct school years a student has enrollments in
    private function getStudentSchoolYears($student_id)
    {
        return $this->student_model->getEnrolledSchoolYears($student_id);
    }

    // resolve selected school year from query string or fall back to current period
    private function resolveSchoolYear()
    {
        $current = $this->academic_model->getCurrentPeriod();
        return $_GET['school_year'] ?? ($current['school_year'] ?? '');
    }

    public function showStudentDashboard()
    {
        $student_info = $this->requireStudent();

        $current         = $this->academic_model->getCurrentPeriod();
        $school_year     = $this->resolveSchoolYear();
        $semester        = $current['semester'] ?? 'First';
        $available_years = $this->getStudentSchoolYears($student_info['student_id']);
        $year_levels     = $this->student_model->getYearLevelsByStudentId($student_info['student_id'], $school_year);

        // balance always uses current active period, not selected school year
        $current_school_year = $current['school_year'] ?? '';
        $balance = $this->student_model->getStudentBalance(
            $student_info['student_id'],
            $current_school_year,
            $semester
        );

        // today's schedule — based on student's current section_id
        $raw_schedule = [];
        if (!empty($student_info['section_id'])) {
            $raw_schedule = $this->student_model->getTodayScheduleBySection(
                $student_info['section_id'],
                $current['school_year'] ?? $school_year,
                $semester
            );
        }

        // format time_range and room display for the view
        $today_schedule = array_map(function ($class) {
            $class['time_range']    = date('g:i A', strtotime($class['start_time']))
                . ' – '
                . date('g:i A', strtotime($class['end_time']));
            $class['room_display']  = !empty($class['room']) ? $class['room'] : 'TBA';
            return $class;
        }, $raw_schedule);

        require __DIR__ . '/../views/student/student_dashboard.php';
    }

    public function showYearLevels()
    {
        $student_info = $this->requireStudent();

        // existing individual grades filter
        $school_year     = $this->resolveSchoolYear();
        $available_years = $this->getStudentSchoolYears($student_info['student_id']);
        $year_levels     = $this->student_model->getYearLevelsByStudentId($student_info['student_id'], $school_year);

        // overall grades filter — uses ov_ prefixed params to avoid collision
        $current             = $this->academic_model->getCurrentPeriod();
        $active_year         = $current['school_year'] ?? '';
        $ov_school_year      = in_array($active_year, $available_years) ? $active_year : ($available_years[0] ?? $active_year);

        if (!empty($_GET['ov_school_year']) && in_array($_GET['ov_school_year'], $available_years)) {
            $ov_school_year = $_GET['ov_school_year'];
        }

        $ov_year_levels   = $this->student_model->getYearLevelsByStudentId($student_info['student_id'], $ov_school_year);
        $first_year_level = !empty($ov_year_levels) ? $ov_year_levels[0]['year_level'] : null;
        $ov_year_level    = $_GET['ov_year_level'] ?? $first_year_level;

        $valid_year_levels = array_column($ov_year_levels, 'year_level');
        if (!in_array($ov_year_level, $valid_year_levels)) {
            $ov_year_level = $first_year_level;
        }

        $ov_semesters = [];
        if ($ov_year_level) {
            $ov_semesters = $this->student_model->getSemestersByStudentIdAndYearLevel(
                $student_info['student_id'],
                $ov_year_level,
                $ov_school_year
            );
        }

        $first_semester  = !empty($ov_semesters) ? $ov_semesters[0]['semester'] : null;
        $ov_semester     = $_GET['ov_semester'] ?? $first_semester;

        $valid_semesters = array_column($ov_semesters, 'semester');
        if (!in_array($ov_semester, $valid_semesters)) {
            $ov_semester = $first_semester;
        }

        // fetch overview only if all filters resolved
        $grades_overview = [];
        if ($ov_year_level && $ov_semester) {
            $grades_overview = $this->student_model->getAllGradesOverview(
                $student_info['student_id'],
                $ov_year_level,
                $ov_semester,
                $ov_school_year
            );
        }

        require __DIR__ . '/../views/student/year_levels.php';
    }

    public function ajaxGetOverallGrades()
    {
        $student_info = $this->requireStudent();

        $year_level  = $_GET['year_level']  ?? null;
        $semester    = $_GET['semester']    ?? null;
        $school_year = $this->resolveSchoolYear();

        if (empty($year_level) || empty($semester)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
            exit();
        }

        $grades_overview = $this->student_model->getAllGradesOverview(
            $student_info['student_id'],
            $year_level,
            $semester,
            $school_year
        );

        // compute per-subject average and overall average for the response
        $total_avg = 0;
        $avg_count = 0;

        foreach ($grades_overview as &$row) {
            $available = array_filter(
                [$row['prelim'], $row['midterm'], $row['prefinal'], $row['final']],
                fn($v) => $v !== null
            );

            $row['average']     = !empty($available)
                ? round(array_sum($available) / count($available), 2)
                : null;
            $row['is_complete'] = count($available) === 4;

            if ($row['average'] !== null) {
                $total_avg += $row['average'];
                $avg_count++;
            }
        }
        unset($row);

        $overall_average = $avg_count > 0 ? round($total_avg / $avg_count, 2) : null;

        header('Content-Type: application/json');
        echo json_encode([
            'success'         => true,
            'grades_overview' => $grades_overview,
            'overall_average' => $overall_average,
        ]);
        exit();
    }

    public function showSemesters()
    {
        $student_info = $this->requireStudent();

        $year_level = $_GET['year_level'] ?? null;

        if (empty($year_level)) {
            header('Location: index.php?page=student_dashboard');
            exit();
        }

        $school_year     = $this->resolveSchoolYear();
        $available_years = $this->getStudentSchoolYears($student_info['student_id']);
        $semesters       = $this->student_model->getSemestersByStudentIdAndYearLevel(
            $student_info['student_id'],
            $year_level,
            $school_year
        );

        require __DIR__ . '/../views/student/semesters.php';
    }

    public function showSubjects()
    {
        $student_info = $this->requireStudent();

        $year_level = $_GET['year_level'] ?? null;
        $semester   = $_GET['semester']   ?? null;

        if (empty($year_level) || empty($semester)) {
            header('Location: index.php?page=student_dashboard');
            exit();
        }

        $school_year     = $this->resolveSchoolYear();
        $available_years = $this->getStudentSchoolYears($student_info['student_id']);
        $subjects        = $this->student_model->getSubjectsByStudentIdYearLevelAndSemester(
            $student_info['student_id'],
            $year_level,
            $semester,
            $school_year
        );

        require __DIR__ . '/../views/student/subjects.php';
    }

    public function showGrades()
    {
        $student_info = $this->requireStudent();

        $subject_id = $_GET['subject_id'] ?? null;
        $year_level = $_GET['year_level'] ?? null;
        $semester   = $_GET['semester']   ?? null;

        if (empty($subject_id) || empty($year_level) || empty($semester)) {
            header('Location: index.php?page=student_dashboard');
            exit();
        }

        $subject = $this->subject_model->getById($subject_id);
        if (!$subject) {
            header('Location: index.php?page=student_dashboard');
            exit();
        }

        $school_year     = $this->resolveSchoolYear();
        $available_years = $this->getStudentSchoolYears($student_info['student_id']);
        $grades          = $this->student_model->getGradesByStudentIdAndSubject(
            $student_info['student_id'],
            $subject_id,
            $semester,
            $school_year
        );

        // organize by grading period for easy display
        $grades_by_period = [];
        foreach ($grades as $grade) {
            $grades_by_period[$grade['grading_period']] = $grade;
        }

        require __DIR__ . '/../views/student/grades.php';
    }

    public function ajaxGetGrades()
    {
        $student_info = $this->requireStudent();

        $subject_id = $_GET['subject_id'] ?? null;
        $year_level = $_GET['year_level'] ?? null;
        $semester   = $_GET['semester']   ?? null;
        $school_year = $this->resolveSchoolYear();

        if (empty($subject_id) || empty($year_level) || empty($semester)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false]);
            exit();
        }

        $grades = $this->student_model->getGradesByStudentIdAndSubject(
            $student_info['student_id'],
            $subject_id,
            $semester,
            $school_year
        );

        $grades_by_period = [];
        foreach ($grades as $grade) {
            $grades_by_period[$grade['grading_period']] = $grade;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'grades' => $grades_by_period]);
        exit();
    }
}
