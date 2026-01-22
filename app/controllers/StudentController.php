<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Subject.php';

class StudentController
{
    private $student_model;
    private $subject_model;

    public function __construct()
    {
        $this->student_model = new Student();
        $this->subject_model = new Subject();
    }

    public function showStudentDashboard()
    {
        // check if student is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            header('Location: index.php?page=login');
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $student_info = $this->student_model->getStudentInfoByUserId($user_id);

        if (!$student_info) {
            // if student info doesn't exist, logout and redirect to login with error
            session_destroy();
            header('Location: index.php?page=login');
            exit();
        }

        $school_year = '2025-2026'; // TODO: can be made dynamic later
        $year_levels = $this->student_model->getYearLevelsByStudentId($student_info['student_id'], $school_year);

        require __DIR__ . '/../views/student/student_dashboard.php';
    }

    public function showYearLevels()
    {
        // check if student is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            header('Location: index.php?page=login');
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $student_info = $this->student_model->getStudentInfoByUserId($user_id);

        if (!$student_info) {
            // if student info doesn't exist, logout and redirect to login with error
            session_destroy();
            header('Location: index.php?page=login');
            exit();
        }

        $school_year = '2025-2026'; // TODO: can be made dynamic later
        $year_levels = $this->student_model->getYearLevelsByStudentId($student_info['student_id'], $school_year);

        require __DIR__ . '/../views/student/year_levels.php';
    }

    public function showSemesters()
    {
        // check if student is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            header('Location: index.php?page=login');
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $student_info = $this->student_model->getStudentInfoByUserId($user_id);

        if (!$student_info) {
            // if student info doesn't exist, logout and redirect to login with error
            session_destroy();
            header('Location: index.php?page=login');
            exit();
        }

        $year_level = $_GET['year_level'] ?? null;

        if (empty($year_level)) {
            header('Location: index.php?page=student_dashboard');
            exit();
        }

        $school_year = '2025-2026'; // TODO: can be made dynamic later
        $semesters = $this->student_model->getSemestersByStudentIdAndYearLevel(
            $student_info['student_id'], 
            $year_level, 
            $school_year
        );

        require __DIR__ . '/../views/student/semesters.php';
    }

    public function showSubjects()
    {
        // check if student is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            header('Location: index.php?page=login');
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $student_info = $this->student_model->getStudentInfoByUserId($user_id);

        if (!$student_info) {
            // if student info doesn't exist, logout and redirect to login with error
            session_destroy();
            header('Location: index.php?page=login');
            exit();
        }

        $year_level = $_GET['year_level'] ?? null;
        $semester = $_GET['semester'] ?? null;

        if (empty($year_level) || empty($semester)) {
            header('Location: index.php?page=student_dashboard');
            exit();
        }

        $school_year = '2025-2026'; // TODO: can be made dynamic later
        $subjects = $this->student_model->getSubjectsByStudentIdYearLevelAndSemester(
            $student_info['student_id'], 
            $year_level, 
            $semester, 
            $school_year
        );

        require __DIR__ . '/../views/student/subjects.php';
    }

    public function showGrades()
    {
        // check if student is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            header('Location: index.php?page=login');
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $student_info = $this->student_model->getStudentInfoByUserId($user_id);

        if (!$student_info) {
            // if student info doesn't exist, logout and redirect to login with error
            session_destroy();
            header('Location: index.php?page=login');
            exit();
        }

        $subject_id = $_GET['subject_id'] ?? null;
        $year_level = $_GET['year_level'] ?? null;
        $semester = $_GET['semester'] ?? null;

        if (empty($subject_id) || empty($year_level) || empty($semester)) {
            header('Location: index.php?page=student_dashboard');
            exit();
        }

        $subject = $this->subject_model->getById($subject_id);
        if (!$subject) {
            header('Location: index.php?page=student_dashboard');
            exit();
        }

        $school_year = '2025-2026'; // TODO: can be made dynamic later
        $grades = $this->student_model->getGradesByStudentIdAndSubject(
            $student_info['student_id'], 
            $subject_id, 
            $semester, 
            $school_year
        );

        // organize grades by grading period for easy display
        $grades_by_period = [];
        foreach ($grades as $grade) {
            $grades_by_period[$grade['grading_period']] = $grade;
        }

        require __DIR__ . '/../views/student/grades.php';
    }
}
