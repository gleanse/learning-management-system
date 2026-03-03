<?php

require_once __DIR__ . '/../../config/db_connection.php';

class AuthSession
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    public function getUserRoleAndStatus($user_id)
    {
        $stmt = $this->connection->prepare("
            SELECT role, status 
            FROM users 
            WHERE id = ? 
            LIMIT 1
        ");

        $stmt->execute([$user_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
