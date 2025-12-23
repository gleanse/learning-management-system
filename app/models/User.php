<?php

require_once __DIR__ . '/../../config/db_connection.php';

class User
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    public function authenticate($username_or_email, $password)
    {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username_or_email, $username_or_email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public function register(array $user_data)
    {
        $errors = [];

        $check_username = $this->connection->prepare("SELECT id FROM users WHERE username = ?");
        $check_username->execute([$user_data['username']]);

        if ($check_username->fetch()) {
            $errors[] = 'username_exists';
        }

        if (!empty($user_data['email'])) {
            $check_email = $this->connection->prepare("SELECT id FROM users WHERE email = ?");
            $check_email->execute([$user_data['email']]);

            if ($check_email->fetch()) {
                $errors[] = 'email_exists';
            }
        }

        // return if theres any validation error
        if (!empty($errors)) {
            return $errors;
        }

        $stmt = $this->connection->prepare("
            INSERT INTO users (username, email, password, role, first_name, middle_name, last_name, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        // if insert execution succeed return the id of created user
        if ($stmt->execute([
            $user_data['username'],
            $user_data['email'],
            password_hash($user_data['password'], PASSWORD_DEFAULT),
            $user_data['role'],
            $user_data['first_name'],
            $user_data['middle_name'],
            $user_data['last_name'],
            $user_data['created_by']
        ])) {
            return $this->connection->lastInsertId();
        }

        return false;
    }
}
