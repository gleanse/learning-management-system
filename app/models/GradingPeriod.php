<?php

require_once __DIR__ . '/../../config/db_connection.php';

class GradingPeriod
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // check if grading period is locked
    public function isLocked($school_year, $semester, $grading_period)
    {
        $stmt = $this->connection->prepare("
            SELECT is_locked
            FROM grading_periods
            WHERE school_year = ? 
                AND semester = ? 
                AND grading_period = ?
        ");
        
        $stmt->execute([$school_year, $semester, $grading_period]);
        
        $result = $stmt->fetch();
        
        return $result ? (bool)$result['is_locked'] : true;
    }
}