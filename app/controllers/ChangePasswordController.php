<?php

class ChangePasswordController
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    private function requireLogin()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit();
        }
    }

    private function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    public function ajaxChangePassword()
    {
        $this->requireLogin();

        $user_id         = $_SESSION['user_id'];
        $old_password    = $_POST['old_password']     ?? '';
        $new_password    = $_POST['new_password']     ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // basic validation
        if (!$old_password || !$new_password || !$confirm_password) {
            $this->jsonResponse(['success' => false, 'message' => 'All fields are required.'], 422);
        }

        if ($new_password !== $confirm_password) {
            $this->jsonResponse(['success' => false, 'message' => 'New passwords do not match.'], 422);
        }

        $strength_errors = $this->validatePasswordStrength($new_password);
        if (!empty($strength_errors)) {
            $this->jsonResponse(['success' => false, 'message' => implode(', ', $strength_errors) . '.'], 422);
        }

        if ($old_password === $new_password) {
            $this->jsonResponse(['success' => false, 'message' => 'New password must be different from your current password.'], 422);
        }

        // fetch current hash
        $stmt = $this->connection->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->jsonResponse(['success' => false, 'message' => 'User not found.'], 404);
        }

        // verify old password
        if (!password_verify($old_password, $user['password'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        // update
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt     = $this->connection->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_hash, $user_id]);

        $this->jsonResponse(['success' => true, 'message' => 'Password changed successfully.']);
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
