<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/LoginLockout.php';
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../models/PasswordReset.php';
require_once __DIR__ . '/../helpers/activity_logger.php';

class AuthController
{
    private $user_model;
    private $lockout_model;
    private $reset_model;

    public function __construct()
    {
        $this->user_model = new User();
        $this->lockout_model = new LoginLockout();
        $this->reset_model   = new PasswordReset();
    }

    private function jsonResponse($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
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
            // check account status before allowing login — credentials are valid but account may be blocked
            $blocked_statuses = [
                'inactive'  => 'Your account is currently inactive. Please contact the administrator for assistance.',
                'suspended' => 'Your account has been suspended. Please contact the administrator for assistance.',
                'graduated' => 'Your account is no longer active as you have already graduated.',
            ];

            if (isset($blocked_statuses[$user['status']])) {
                // LOG FAILED LOGIN (BLOCKED STATUS)
                logAction(
                    'failed_login',
                    "Failed login attempt for {$user['username']} - account {$user['status']}",
                    'users',
                    $user['id'],
                    null,
                    ['status' => $user['status'], 'ip' => $user_ip]
                );
                
                $_SESSION['login_errors'] = ['general' => $blocked_statuses[$user['status']]];
                $_SESSION['old_input'] = ['username_or_email' => $username_or_email];
                header('Location: index.php?page=login');
                exit();
            }

            // on successful login
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
            
            // LOG SUCCESSFUL LOGIN
            logAction(
                'login',
                "User logged in: {$user['username']} ({$user['role']})",
                'users',
                $user['id'],
                null,
                ['ip' => $user_ip]
            );
            
            // set remember me cookie, pass model via dependency injection for helper function
            if (isset($_POST['remember_me'])) {
                setRememberMeCookie($user['id'], $this->user_model);
            }
            
            // redirect based on role
            if ($user['role'] === 'teacher') {
                header('Location: index.php?page=teacher_dashboard');
            } elseif ($user['role'] === 'student') {
                header('Location: index.php?page=student_dashboard');
            } elseif ($user['role'] === 'admin' || $user['role'] === 'superadmin') {
                header('Location: index.php?page=admin_dashboard');
            } else {
                header('Location: index.php?page=dashboard');
            }
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
    
            // LOG FAILED LOGIN (INVALID CREDENTIALS)
            logAction(
                'failed_login',
                "Failed login attempt for identifier: {$username_or_email}",
                null,
                null,
                null,
                ['ip' => $user_ip, 'attempts' => $fail_count]
            );
    
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
        // LOG LOGOUT before destroying session
        if (isset($_SESSION['user_id'])) {
            logAction(
                'logout',
                "User logged out: {$_SESSION['user_username']}",
                'users',
                $_SESSION['user_id']
            );
        }
    
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

    // shows the forgot password page (single page, multi-step via js)
    public function showForgotPassword()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?page=dashboard');
            exit();
        }

        require __DIR__ . '/../views/forgot_password.php';
    }

    // step 1 — lookup user by username or email, send otp if valid
    public function sendOtp()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request.'], 405);
        }

        $identifier = trim($_POST['identifier'] ?? '');

        if (empty($identifier)) {
            $this->jsonResponse(['success' => false, 'message' => 'Please enter your username or email.']);
        }

        $user = $this->reset_model->findUserByIdentifier($identifier);

        // vague message intentional — don't reveal if account exists
        if (!$user) {
            $this->jsonResponse(['success' => false, 'message' => 'No active account found with that username or email.']);
        }

        if (empty($user['email'])) {
            $this->jsonResponse(['success' => false, 'message' => 'No email address linked to this account. Please contact your administrator.']);
        }

        if ($this->reset_model->isWithinCooldown($user['id'])) {
            $remaining = $this->reset_model->getCooldownRemaining($user['id']);
            $this->jsonResponse([
                'success'  => false,
                'message'  => "Please wait {$remaining} second(s) before requesting a new OTP.",
                'cooldown' => $remaining,
            ]);
        }

        $otp  = $this->reset_model->createOrRenewOtp($user['id']);
        $sent = $this->reset_model->sendOtpEmail(
            $user['email'],
            $user['first_name'] . ' ' . $user['last_name'],
            $otp
        );

        if (!$sent) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to send OTP. Please try again later.']);
        }

        // store user_id in session to carry across steps — never expose to client
        $_SESSION['otp_user_id'] = $user['id'];

        $masked = $this->maskEmail($user['email']);

        $this->jsonResponse([
            'success'      => true,
            'message'      => "OTP sent to {$masked}. Valid for 15 minutes.",
            'masked_email' => $masked,
        ]);
    }

    // step 2 — verify the 6-digit otp
    public function verifyOtp()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request.'], 405);
        }

        $user_id  = $_SESSION['otp_user_id'] ?? null;
        $otp_code = trim($_POST['otp_code'] ?? '');

        if (!$user_id) {
            $this->jsonResponse(['success' => false, 'message' => 'Session expired. Please start over.']);
        }

        if (empty($otp_code) || !preg_match('/^\d{6}$/', $otp_code)) {
            $this->jsonResponse(['success' => false, 'message' => 'Please enter a valid 6-digit OTP.']);
        }

        $reset = $this->reset_model->verifyOtp($user_id, $otp_code);

        if (!$reset) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid or expired OTP. Please try again.']);
        }

        $this->reset_model->markOtpUsed($reset['id']);

        // flag session as otp verified then clear otp session
        $_SESSION['otp_verified']   = true;
        $_SESSION['otp_reset_user'] = $user_id;
        unset($_SESSION['otp_user_id']);

        $this->jsonResponse(['success' => true, 'message' => 'OTP verified. Please set your new password.']);
    }

    // step 3 — reset password after otp verified
    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request.'], 405);
        }

        if (empty($_SESSION['otp_verified']) || empty($_SESSION['otp_reset_user'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Session expired. Please start over.']);
        }

        $user_id          = (int) $_SESSION['otp_reset_user'];
        $new_password     = $_POST['new_password']     ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($new_password)) {
            $this->jsonResponse(['success' => false, 'message' => 'Password is required.']);
        }

        if ($new_password !== $confirm_password) {
            $this->jsonResponse(['success' => false, 'message' => 'Passwords do not match.']);
        }

        $strength_errors = $this->validatePasswordStrength($new_password);
        if (!empty($strength_errors)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Password must contain: ' . implode(', ', $strength_errors) . '.',
            ]);
        }

        $updated = $this->reset_model->resetPassword($user_id, $new_password);

        if (!$updated) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to reset password. Please try again.']);
        }

        unset($_SESSION['otp_verified'], $_SESSION['otp_reset_user']);

        $this->jsonResponse(['success' => true, 'message' => 'Password reset successfully. You can now log in.']);
    }

    // resend otp — respects 1 minute cooldown
    public function resendOtp()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request.'], 405);
        }

        $user_id = $_SESSION['otp_user_id'] ?? null;

        if (!$user_id) {
            $this->jsonResponse(['success' => false, 'message' => 'Session expired. Please start over.']);
        }

        if ($this->reset_model->isWithinCooldown($user_id)) {
            $remaining = $this->reset_model->getCooldownRemaining($user_id);
            $this->jsonResponse([
                'success'  => false,
                'message'  => "Please wait {$remaining} second(s) before resending.",
                'cooldown' => $remaining,
            ]);
        }

        global $connection;
        $stmt = $connection->prepare("SELECT id, email, first_name, last_name FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user || empty($user['email'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Unable to resend OTP. Please start over.']);
        }

        $otp  = $this->reset_model->createOrRenewOtp($user_id);
        $sent = $this->reset_model->sendOtpEmail(
            $user['email'],
            $user['first_name'] . ' ' . $user['last_name'],
            $otp
        );

        if (!$sent) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to resend OTP. Please try again later.']);
        }

        $masked = $this->maskEmail($user['email']);

        $this->jsonResponse([
            'success' => true,
            'message' => "OTP resent to {$masked}.",
        ]);
    }

    // masks email for display — jo***@gmail.com
    private function maskEmail($email)
    {
        [$local, $domain] = explode('@', $email);
        $visible = substr($local, 0, 2);
        return $visible . str_repeat('*', max(3, strlen($local) - 2)) . '@' . $domain;
    }

    private function validatePasswordStrength($password)
    {
        $errors = [];

        if (strlen($password) < 8)             $errors[] = 'At least 8 characters';
        if (!preg_match('/[A-Z]/', $password))  $errors[] = 'At least one uppercase letter';
        if (!preg_match('/[a-z]/', $password))  $errors[] = 'At least one lowercase letter';
        if (!preg_match('/[0-9]/', $password))  $errors[] = 'At least one number';
        if (!preg_match('/[\W_]/', $password))  $errors[] = 'At least one special character';

        return $errors;
    }
}
