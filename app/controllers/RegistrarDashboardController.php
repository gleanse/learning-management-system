<?php

class RegistrarDashboardController
{
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
        require __DIR__ . '/../views/registrar/dashboard.php';
    }
}
