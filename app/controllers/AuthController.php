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

        $errors = $_SESSION['login_errors'] ?? [];
        $old_input = $_SESSION['old_input'] ?? [];
        $ip_status = $_SESSION['ip_status'] ?? null;
        unset($_SESSION['login_errors'], $_SESSION['old_input'], $_SESSION['ip_status']);

        require __DIR__ . '/../views/login.php';
    }

    public function processLogin()
    {
        $user_ip = getUserIp();
        $ip_status = $this->lockout_model->checkLockout($user_ip);

        if ($ip_status['locked']) {
            $_SESSION['login_errors'] = ['general' => "Too many failed attempts. Try again in"];
            $_SESSION['ip_status'] = $ip_status;
            header('Location: index.php?page=login');
            exit();
        }

        // check if csrf token exists stop script if missing
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // regenerate token for next attempt
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['login_errors'] = ['general' => 'Your session expired. Please try again.'];
        $_SESSION['old_input'] = ['username_or_email' => trim($_POST['username_or_email'] ?? '')];
        header('Location: index.php?page=login');
        exit();
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
            $_SESSION['login_errors'] = $errors;
            $_SESSION['old_input'] = ['username_or_email' => $username_or_email];
            header('Location: index.php?page=login');
            exit();
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
            
            // set remember me cookie, pass model via dependency injection for helper function
            if (isset($_POST['remember_me'])) {
                setRememberMeCookie($user['id'], $this->user_model);
            }
            
            header('Location: index.php?page=dashboard');
            exit();
        } else {
            $this->lockout_model->recordFail($user_ip);
            $fail_count = $this->lockout_model->getFailCount($user_ip);

            // build base invalid message (dynamic based on email or username enter on input)
            if (filter_var($username_or_email, FILTER_VALIDATE_EMAIL)) {
                $base_message = 'Invalid email or password.';
            } else {
                $base_message = 'Invalid username or password.';
            }

            if ($fail_count == 3) {
                $errors['general'] = $base_message . ' Warning: 2 attempts remaining before lockout.';
            } elseif ($fail_count == 4) {
                $errors['general'] = $base_message . ' Warning: 1 last attempt before lockout.';
            } elseif ($fail_count >= 5) {
                // 5th+ failed attempt lockout triggered, check status and show message
                $ip_status_after_fail = $this->lockout_model->checkLockout($user_ip);
                if ($ip_status_after_fail['locked']) {
                    $errors['general'] = $base_message . ' Too many failed attempts. Try again in';
                    // pass ip_status to view for countdown
                    $ip_status = $ip_status_after_fail;
                } else {
                    $errors['general'] = $base_message;
                }
            } else {
                $errors['general'] = $base_message;
            }

            $_SESSION['login_errors'] = $errors;
            $_SESSION['old_input'] = ['username_or_email' => $username_or_email];
            if (isset($ip_status)) {
                $_SESSION['ip_status'] = $ip_status;
            }
            header('Location: index.php?page=login');
            exit();
        }
    }

    public function logout()
    {
        // delete remember token
        if (isset($_COOKIE['remember_token'])) {
        $tokenHash = hash('sha256', $_COOKIE['remember_token']);
        $this->user_model->deleteRememberToken($tokenHash);
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        // clear session data
        $_SESSION = [];
        
        // delete session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();
        header('Location: index.php?page=login');
        exit();
    }
    

    public function showRegisterForm()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
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
        
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $errors = ['general' => 'Your session expired. Please refresh and try again.'];
        require __DIR__ . '/../views/register.php';
        return;
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
