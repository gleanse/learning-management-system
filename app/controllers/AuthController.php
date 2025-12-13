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
        $username_or_email = '';

        require __DIR__ . '/../views/login.php';
    }

    public function processLogin()
    {
        $username_or_email = $_POST['username_or_email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->authenticate($username_or_email, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_firstname'] = $user['first_name'];
            header('Location: index.php?page=dashboard');
            exit();
        } else {
            $error = 'Invalid login credentials';
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
        $first_name = trim($_POST['first_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        if ($first_name === '' || $last_name === '' || $username === '' || $password === '' || $role === null) {
            $error = 'All fields are required.';
            require __DIR__ . '/../views/register.php';
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
            require __DIR__ . '/../views/register.php';
            return;
        }

        $allowedRoles = ['student', 'teacher', 'admin', 'superadmin'];
        // validate if the usertype range is valid
        if (!in_array($role, $allowedRoles, true)) {
            $error = 'Please select a valid user type.';
            require __DIR__ . '/../views/register.php';
            return;
        }

        $result = $this->userModel->register($first_name, $middle_name, $last_name, $username, $email, $password, $role);

        if ($result === true) {
            header('Location: index.php?page=login&registered=1');
            exit();
        }
        
        if ($result === 'username_exists') {
            $error = 'Username already exists. Please use different username.';
        } else {
            $error = 'Registration failed. Please try again.';
        }
        
        if ($result === 'email_exists') {
            $error = 'Email already exists. Please use different email.';
        } else {
            $error = 'Registration failed. Please try again.';
        }

        require __DIR__ . '/../views/register.php';
    }
}
