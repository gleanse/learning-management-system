<?php
require_once __DIR__ . '/../../config/db_connection.php';

class SchoolSettings
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // get the current active school year and semester
    public function getCurrentPeriod()
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM school_settings
            WHERE is_active = TRUE
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch();
    }

    // check if any period has been initialized
    public function hasPeriod()
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as total FROM school_settings");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['total'] > 0;
    }
}
