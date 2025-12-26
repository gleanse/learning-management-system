<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/LoginLockout.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

class AuthController
{
    private $user_model;
    private $lockout_model;

    public function __construct()
    {
        $this->user_model = new User();
        $this->lockout_model = new LoginLockout();
    }

    public function showLoginForm()
    {
        // generate a csrf token to prevent csrf attacks
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $errors = [];
        $username_or_email = '';

        require __DIR__ . '/../views/login.php';
    }

    public function processLogin()
    {
        $user_ip = getUserIp();
        $ip_status = $this->lockout_model->checkLockout($user_ip);
        
        if ($ip_status['locked']) {
            $seconds = $ip_status['seconds_remaining'];
            $minutes = ceil($seconds / 60);
            
            if ($minutes < 1) {
                $errors['general'] = "Too many failed attempts. Try again in a few seconds.";
            } else {
                $plural = $minutes > 1 ? 's' : '';
                $errors['general'] = "Too many failed attempts. Try again in {$minutes} minute{$plural}.";
            }
    
            require __DIR__ . '/../views/login.php';
            return;
        }

        // check if csrf token exists stop script if missing
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            die('CSRF token missing. Possible attack detected.');
        }

        // compare csrf token from session and form submission
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die('CSRF token validation failed. Possible attack detected.');
        }

        $username_or_email = trim($_POST['username_or_email'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];

        if (empty($username_or_email)) {
            $errors['username_or_email'] = 'Please enter your username or email.';
        }

        if (empty($password)) {
            $errors['password'] = 'Please enter your password.';
        }

        if (!empty($errors)) {
            require __DIR__ . '/../views/login.php';
            return;
        }

        $user = $this->user_model->authenticate($username_or_email, $password);

        if ($user) {
            // on sucessful login
            $this->lockout_model->clearLockout($user_ip);
            // for session fixation security
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_firstname'] = $user['first_name'];
            $_SESSION['user_middlename'] = $user['middle_name'];
            $_SESSION['user_lastname'] = $user['last_name'];
            // regenerate new csrf token after login
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header('Location: index.php?page=dashboard');
            exit();
        } else {
            $this->lockout_model->recordFail($user_ip);
            // get fail count and show warning if close to lockout
            $fail_count = $this->lockout_model->getFailCount($user_ip);
            
            if ($fail_count >= 4 && $fail_count < 6) {
                // warning message for attempts 4-5
                $remaining = 6 - $fail_count;
                $errors['general'] = "Invalid credentials. Warning: {$remaining} attempts remaining before lockout.";
            } else {
                if (filter_var($username_or_email, FILTER_VALIDATE_EMAIL)) {
                    $errors['general'] = 'Invalid email or password.';
                } else {
                    $errors['general'] = 'Invalid username or password.';
                }
            }
            require __DIR__ . '/../views/login.php';
        }
    }

    public function logout()
    {
        session_destroy();
        header('Location: index.php?page=login');
        exit();
    }

    public function showRegisterForm()
    {
        $errors = [];

        require __DIR__ . '/../views/register.php';
    }

    public function processRegister()
    {
        // check if superadmin is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit();
        }

        $user_data = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => !empty(trim($_POST['email'])) ? trim($_POST['email']) : null,
            'password' => $_POST['password'] ?? '',
            'role' => $_POST['role'] ?? '',
            'first_name' => trim($_POST['first_name'] ?? ''),
            'middle_name' => trim($_POST['middle_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'created_by' => $_SESSION['user_id']
        ];

        // validate required fields
        $errors = [];
        if (empty($user_data['first_name'])) {
            $errors['first_name'] = 'First name is required.';
        }

        if (empty($user_data['last_name'])) {
            $errors['last_name'] = 'Last name is required.';
        }

        if (empty($user_data['username'])) {
            $errors['username'] = 'Username is required.';
        }

        if (empty($user_data['password'])) {
            $errors['password'] = 'Password is required.';
        }

        if (empty($user_data['role'])) {
            $errors['role'] = 'Role is required.';
        }

        // validate email format if provided
        if ($user_data['email'] !== null && !filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        }

        // validate if the role is valid
        $allowed_roles = ['student', 'teacher', 'admin', 'superadmin'];
        if (!empty($user_data['role']) && !in_array($user_data['role'], $allowed_roles, true)) {
            $errors['role'] = 'Please select a valid user role.';
        }

        // if there are validation errors, show form with errors
        if (!empty($errors)) {
            require __DIR__ . '/../views/register.php';
            return;
        }

        $result = $this->user_model->register($user_data);

        if (is_array($result)) {
            if (in_array('username_exists', $result)) {
                $errors['username'] = 'Username already exists.';
            }

            if (in_array('email_exists', $result)) {
                $errors['email'] = 'Email already exists.';
            }

            require __DIR__ . '/../views/register.php';
            return;
        } elseif ($result) {
            // success register
            header('Location: index.php?page=users');
            exit();
        } else {
            $errors['general'] = 'Registration failed. Please try again.';
            require __DIR__ . '/../views/register.php';
        }
    }
}
