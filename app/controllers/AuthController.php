<?php

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLoginForm()
    {
        $error = null;
        $email = '';

        require __DIR__ . '/../views/login.php';
    }

    public function processLogin()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->authenticate($email, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: index.php?page=dashboard');
            exit();
        } else {
            $error = 'Invalid email or password';
            require __DIR__ . '/../views/login.php';
        }
    }

    public function logout()
    {
        session_destroy();
        header('Location: index.php?page=login');
        exit();
    }

    public function showRegisterForm(){
        $error = null;

        require __DIR__ . '/../views/register.php';
    }

    public function processRegister()
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $userType = $_POST['user_type'] ?? null;

        if ($name === '' || $email === '' || $password === '' || $userType === null) {
            $error = 'All fields are required.';
            require __DIR__ . '/../views/register.php';
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
            require __DIR__ . '/../views/register.php';
            return;
        }

        $userType = (int)$userType;
        // validate if the usertype range is valid
        if ($userType < 1 || $userType > 4) {
            $error = 'Please select a valid user type.';
            require __DIR__ . '/../views/register.php';
            return;
        }

        $result = $this->userModel->register($name, $email, $password, $userType);

        if ($result === true) {
            header('Location: index.php?page=login&registered=1');
            exit();
        }

        if ($result === 'email_exists') {
            $error = 'Email already exists. Please use different email.';
        } else {
            $error = 'Registration failed. Please try again.';
        }

        require __DIR__ . '/../views/register.php';
    }
}
