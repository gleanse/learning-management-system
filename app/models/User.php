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

        if ($user && $user['password'] === $password) {
            return $user;
        }

        return false;
    }
}
