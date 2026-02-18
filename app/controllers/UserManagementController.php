<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Student.php';

class UserManagementController
{
    private $user_model;
    private $student_model;

    public function __construct()
    {
        $this->user_model    = new User();
        $this->student_model = new Student();
    }

    private function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    private function jsonResponse($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    private function requireSuperAdmin()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
            header('Location: index.php?page=login');
            exit();
        }
    }

    private function validateCsrf()
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Session expired. Please refresh and try again.'], 403);
            }

            header('Location: index.php?page=user_management');
            exit();
        }
    }

    // checks password against strength requirements, returns array of unmet rules
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

    public function showUserManagementPage()
    {
        $this->requireSuperAdmin();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // pass only counts to view, actual lists are loaded via ajax
        $total_users            = $this->user_model->getTotalUsersCount();
        $total_without_accounts = $this->student_model->getTotalStudentsWithoutUserAccountCount();

        $errors          = $_SESSION['user_mgmt_errors'] ?? [];
        $success_message = $_SESSION['user_mgmt_success'] ?? null;
        unset($_SESSION['user_mgmt_errors'], $_SESSION['user_mgmt_success']);

        require __DIR__ . '/../views/superadmin/user_management.php';
    }

    // ajax: paginated + searchable user list with optional role filter
    public function ajaxGetUsers()
    {
        $this->requireSuperAdmin();

        $page   = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
        $search = trim($_GET['search'] ?? '');
        $role   = trim($_GET['role'] ?? '');
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        $allowed_roles = ['student', 'teacher', 'registrar', 'admin', 'superadmin', ''];
        if (!in_array($role, $allowed_roles, true)) $role = '';

        $users = $this->user_model->getAllUsers($limit, $offset, $search, $role);
        $total = $this->user_model->getTotalUsersCount($search, $role);

        $this->jsonResponse([
            'success'     => true,
            'users'       => $users,
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'total_pages' => (int)ceil($total / $limit),
        ]);
    }

    // ajax: paginated student list for students-without-account tab
    public function ajaxGetStudentsWithoutAccount()
    {
        $this->requireSuperAdmin();

        $page   = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
        $search = trim($_GET['search'] ?? '');
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        $students = $this->student_model->getStudentsWithoutUserAccount($limit, $offset, $search);
        $total    = $this->student_model->getTotalStudentsWithoutUserAccountCount($search);

        $this->jsonResponse([
            'success'     => true,
            'students'    => $students,
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'total_pages' => (int)ceil($total / $limit),
        ]);
    }

    // ajax: real-time username availability check
    public function ajaxCheckUsername()
    {
        $this->requireSuperAdmin();

        $username   = trim($_GET['username'] ?? '');
        $exclude_id = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : null;

        if (empty($username)) {
            $this->jsonResponse(['available' => false, 'message' => 'Username is required.']);
        }

        $exists = $this->user_model->usernameExists($username, $exclude_id);
        $this->jsonResponse([
            'available' => !$exists,
            'message'   => $exists ? 'Username is already taken.' : 'Username is available.',
        ]);
    }

    // ajax: real-time email availability check
    public function ajaxCheckEmail()
    {
        $this->requireSuperAdmin();

        $email      = trim($_GET['email'] ?? '');
        $exclude_id = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : null;

        // empty email is allowed (optional field)
        if (empty($email)) {
            $this->jsonResponse(['available' => true, 'message' => '']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['available' => false, 'message' => 'Invalid email format.']);
        }

        $exists = $this->user_model->emailExists($email, $exclude_id);
        $this->jsonResponse([
            'available' => !$exists,
            'message'   => $exists ? 'Email is already in use.' : 'Email is available.',
        ]);
    }

    // ajax: create non-student user (teacher, admin, registrar, superadmin)
    public function processCreateUser()
    {
        $this->requireSuperAdmin();
        $this->validateCsrf();

        $first_name  = trim($_POST['first_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name   = trim($_POST['last_name'] ?? '');
        $username    = trim($_POST['username'] ?? '');
        $email       = !empty(trim($_POST['email'])) ? trim($_POST['email']) : null;
        $password    = $_POST['password'] ?? '';
        $role        = $_POST['role'] ?? '';

        $errors = [];

        if (empty($first_name)) $errors['first_name'] = 'First name is required.';
        if (empty($last_name))  $errors['last_name']  = 'Last name is required.';
        if (empty($username))   $errors['username']   = 'Username is required.';
        if (empty($password))   $errors['password']   = 'Password is required.';

        $allowed_roles = ['teacher', 'registrar', 'admin', 'superadmin'];
        if (empty($role) || !in_array($role, $allowed_roles, true)) {
            $errors['role'] = 'Please select a valid role.';
        }

        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        }

        if (!empty($password)) {
            $strength_errors = $this->validatePasswordStrength($password);
            if (!empty($strength_errors)) {
                $errors['password'] = 'Password must contain: ' . implode(', ', $strength_errors) . '.';
            }
        }

        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors]);
        }

        $result = $this->user_model->register([
            'username'    => $username,
            'email'       => $email,
            'password'    => $password,
            'role'        => $role,
            'first_name'  => $first_name,
            'middle_name' => $middle_name ?: null,
            'last_name'   => $last_name,
            'created_by'  => $_SESSION['user_id'],
        ]);

        if (is_array($result)) {
            $errors = [];
            if (in_array('username_exists', $result)) $errors['username'] = 'Username already exists.';
            if (in_array('email_exists', $result))    $errors['email']    = 'Email already exists.';
            $this->jsonResponse(['success' => false, 'errors' => $errors]);
        }

        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'User account created successfully.', 'user_id' => $result]);
        }

        $this->jsonResponse(['success' => false, 'message' => 'Failed to create user. Please try again.']);
    }

    // ajax: create account for an existing student record, then links them
    public function processCreateStudentAccount()
    {
        $this->requireSuperAdmin();
        $this->validateCsrf();

        $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : null;
        $username   = trim($_POST['username'] ?? '');
        $email      = !empty(trim($_POST['email'])) ? trim($_POST['email']) : null;
        $password   = $_POST['password'] ?? '';

        $errors = [];

        if (empty($student_id)) $errors['student_id'] = 'Student ID is required.';
        if (empty($username))   $errors['username']   = 'Username is required.';
        if (empty($password))   $errors['password']   = 'Password is required.';

        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        }

        if (!empty($password)) {
            $strength_errors = $this->validatePasswordStrength($password);
            if (!empty($strength_errors)) {
                $errors['password'] = 'Password must contain: ' . implode(', ', $strength_errors) . '.';
            }
        }

        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors]);
        }

        $student = $this->student_model->getStudentById($student_id);

        if (!$student) {
            $this->jsonResponse(['success' => false, 'message' => 'Student record not found.']);
        }

        if ($this->student_model->hasUserAccount($student_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'This student already has an account.']);
        }

        // wrap user creation + student link in a transaction so both succeed or both rollback
        global $connection;

        try {
            $connection->beginTransaction();

            $new_user_id = $this->user_model->register([
                'username'    => $username,
                'email'       => $email,
                'password'    => $password,
                'role'        => 'student',
                'first_name'  => $student['first_name'],
                'middle_name' => $student['middle_name'],
                'last_name'   => $student['last_name'],
                'created_by'  => $_SESSION['user_id'],
            ]);

            if (is_array($new_user_id)) {
                $connection->rollBack();
                $errors = [];
                if (in_array('username_exists', $new_user_id)) $errors['username'] = 'Username already exists.';
                if (in_array('email_exists', $new_user_id))    $errors['email']    = 'Email already exists.';
                $this->jsonResponse(['success' => false, 'errors' => $errors]);
            }

            if (!$new_user_id) {
                $connection->rollBack();
                $this->jsonResponse(['success' => false, 'message' => 'Failed to create user account. Please try again.']);
            }

            $linked = $this->student_model->linkStudentToUser($student_id, $new_user_id);

            if (!$linked) {
                $connection->rollBack();
                $this->jsonResponse(['success' => false, 'message' => 'Failed to link account to student record. Please try again.']);
            }

            $connection->commit();

            $this->jsonResponse([
                'success' => true,
                'message' => "Account created and linked to {$student['first_name']} {$student['last_name']} successfully.",
                'user_id' => $new_user_id,
            ]);
        } catch (Exception $e) {
            $connection->rollBack();
            $this->jsonResponse(['success' => false, 'message' => 'An unexpected error occurred. Please try again.']);
        }
    }

    // ajax: update existing user details
    public function processUpdateUser()
    {
        $this->requireSuperAdmin();
        $this->validateCsrf();

        $user_id     = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $first_name  = trim($_POST['first_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name   = trim($_POST['last_name'] ?? '');
        $username    = trim($_POST['username'] ?? '');
        $email       = !empty(trim($_POST['email'])) ? trim($_POST['email']) : null;
        $password    = $_POST['password'] ?? '';
        $role        = $_POST['role'] ?? '';
        $status      = $_POST['status'] ?? '';

        $errors = [];

        if (empty($user_id))    $errors['user_id']    = 'User ID is required.';
        if (empty($first_name)) $errors['first_name'] = 'First name is required.';
        if (empty($last_name))  $errors['last_name']  = 'Last name is required.';
        if (empty($username))   $errors['username']   = 'Username is required.';

        $allowed_roles    = ['student', 'teacher', 'registrar', 'admin', 'superadmin'];
        $allowed_statuses = ['active', 'inactive', 'suspended'];

        if (empty($role) || !in_array($role, $allowed_roles, true)) {
            $errors['role'] = 'Please select a valid role.';
        }

        if (empty($status) || !in_array($status, $allowed_statuses, true)) {
            $errors['status'] = 'Please select a valid status.';
        }

        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        }

        // only validate strength if a new password is being set
        if (!empty($password)) {
            $strength_errors = $this->validatePasswordStrength($password);
            if (!empty($strength_errors)) {
                $errors['password'] = 'Password must contain: ' . implode(', ', $strength_errors) . '.';
            }
        }

        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors]);
        }

        if (!$this->user_model->getUserById($user_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'User not found.']);
        }

        $result = $this->user_model->updateUser($user_id, [
            'username'    => $username,
            'email'       => $email,
            'password'    => $password,
            'role'        => $role,
            'status'      => $status,
            'first_name'  => $first_name,
            'middle_name' => $middle_name ?: null,
            'last_name'   => $last_name,
        ]);

        if (is_array($result)) {
            $errors = [];
            if (in_array('username_exists', $result)) $errors['username'] = 'Username already exists.';
            if (in_array('email_exists', $result))    $errors['email']    = 'Email already exists.';
            $this->jsonResponse(['success' => false, 'errors' => $errors]);
        }

        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'User updated successfully.']);
        }

        $this->jsonResponse(['success' => false, 'message' => 'Failed to update user. Please try again.']);
    }

    // ajax: toggle user account status (active, inactive, suspended)
    public function processUpdateStatus()
    {
        $this->requireSuperAdmin();
        $this->validateCsrf();

        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $status  = $_POST['status'] ?? '';

        $allowed = ['active', 'inactive', 'suspended'];

        if (empty($user_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'User ID is required.']);
        }

        if (!in_array($status, $allowed, true)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid status value.']);
        }

        // prevent superadmin from locking themselves out
        if ((int)$_SESSION['user_id'] === $user_id && $status !== 'active') {
            $this->jsonResponse(['success' => false, 'message' => 'You cannot change your own account status.']);
        }

        if (!$this->user_model->getUserById($user_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'User not found.']);
        }

        $result = $this->user_model->updateStatus($user_id, $status);

        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => "User status updated to {$status} successfully."]);
        }

        $this->jsonResponse(['success' => false, 'message' => 'Failed to update status. Please try again.']);
    }
}
