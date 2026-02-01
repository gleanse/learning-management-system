<?php

require_once __DIR__ . '/../../config/db_connection.php';

class Section
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // get total sections count for dashboard
    public function getTotalSections()
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total 
            FROM sections
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
