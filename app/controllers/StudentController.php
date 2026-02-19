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

        $school_year     = $this->resolveSchoolYear();
        $available_years = $this->getStudentSchoolYears($student_info['student_id']);
        $year_levels     = $this->student_model->getYearLevelsByStudentId($student_info['student_id'], $school_year);

        require __DIR__ . '/../views/student/year_levels.php';
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
}
