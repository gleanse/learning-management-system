<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Section.php';

class DashboardController
{
    private $student_model;
    private $teacher_model;
    private $section_model;

    public function __construct()
    {
        $this->student_model = new Student();
        $this->teacher_model = new Teacher();
        $this->section_model = new Section();
    }

    public function showDashboard()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit();
        }

        // get dashboard statistics
        $total_students = $this->student_model->getTotalActiveStudents();
        $students_this_month = $this->student_model->getStudentsEnrolledThisMonth();
        $total_teachers = $this->teacher_model->getTotalActiveTeachers();
        $assigned_teachers = $this->teacher_model->getAssignedTeachersCount();
        $total_sections = $this->section_model->getTotalSections();
        $recent_enrollments = $this->student_model->getRecentEnrollments(5);

        require __DIR__ . '/../views/admin/dashboard.php';
    }
}
