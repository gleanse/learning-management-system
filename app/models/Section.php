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

    // get all sections (for admin dropdown)
    public function getAllSections()
    {
        $stmt = $this->connection->prepare("
            SELECT 
                section_id,
                section_name,
                year_level,
                school_year
            FROM sections
            ORDER BY year_level ASC, section_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // get sections by year level (for filtering)
    public function getSectionsByYearLevel($year_level)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                section_id,
                section_name,
                year_level,
                school_year
            FROM sections
            WHERE year_level = ?
            ORDER BY section_name ASC
        ");
        $stmt->execute([$year_level]);
        return $stmt->fetchAll();
    }
}
