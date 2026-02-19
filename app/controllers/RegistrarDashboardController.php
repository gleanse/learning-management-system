<?php

require_once __DIR__ . '/../models/RegistrarDashboard.php';
require_once __DIR__ . '/../models/AcademicPeriod.php';

class RegistrarDashboardController
{
    private $dashboard_model;
    private $academic_model;

    public function __construct()
    {
        $this->dashboard_model = new RegistrarDashboard();
        $this->academic_model  = new AcademicPeriod();
    }

    private function requireRegistrar()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'registrar') {
            header('Location: index.php?page=login');
            exit();
        }
    }

    public function showDashboard()
    {
        $this->requireRegistrar();

        // get active academic period
        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        // stats cards
        $total_active      = $this->dashboard_model->getTotalActiveStudents();
        $total_enrollments = $this->dashboard_model->getTotalEnrollments($school_year, $semester);
        $payment_counts    = $this->dashboard_model->getPaymentStatusCounts($school_year, $semester);
        $total_collected   = $this->dashboard_model->getTotalCollected($school_year, $semester);
        $total_outstanding = $this->dashboard_model->getTotalOutstanding($school_year, $semester);
        $by_level          = $this->dashboard_model->getEnrollmentByLevel($school_year, $semester);
        $no_payment_count  = $this->dashboard_model->getStudentsWithoutPayment($school_year, $semester);

        // recent activity
        $recent_enrollments  = $this->dashboard_model->getRecentEnrollments($school_year, $semester, 8);
        $recent_transactions = $this->dashboard_model->getRecentTransactions($school_year, $semester, 8);

        require __DIR__ . '/../views/registrar/registrar_dashboard.php';
    }
}
