<?php

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->authenticate($email, $password);

            if ($user) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['user_name'] = $user['name'];

                header('Location: dashboard.php');
                exit();
            } else {
                return "Invalid email or password";
            }
        }
        return null;
    }
}
