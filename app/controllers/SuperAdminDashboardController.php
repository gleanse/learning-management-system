<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Student.php';

class SuperAdminDashboardController
{
    private $user_model;
    private $student_model;

    public function __construct()
    {
        $this->user_model    = new User();
        $this->student_model = new Student();
    }

    private function requireSuperAdmin()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
            header('Location: index.php?page=login');
            exit();
        }
    }

    public function showDashboard()
    {
        $this->requireSuperAdmin();

        // users by role counts
        $users_by_role = $this->user_model->getTotalUsersByRole();

        // student account stats
        $total_students          = $this->student_model->getTotalActiveStudents();
        $students_without_account = $this->student_model->getTotalStudentsWithoutUserAccountCount();
        $students_with_account   = $total_students - $students_without_account;

        // recent user creations
        $recent_users = $this->user_model->getRecentUsers(10);

        require __DIR__ . '/../views/superadmin/dashboard.php';
    }
}
