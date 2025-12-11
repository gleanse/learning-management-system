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

    public function authenticate($email, $password)
    {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public function register($name, $email, $password, $user_type)
    {
        $checkStmt = $this->connection->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);

        // checking if email that about to register already exist before attempting to insert
        if ($checkStmt->fetch()){
            return 'email_exists';
        }

        $stmt = $this->connection->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)");
        $hash = password_hash($password, PASSWORD_DEFAULT);

        return $stmt->execute([$name, $email, $hash, $user_type]);
    }
}
