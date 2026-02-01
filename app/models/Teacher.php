<?php

require_once __DIR__ . '/../../config/db_connection.php';

class Teacher
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // get all subjects assigned to this teacher// get all subjects assigned to this teacher for a specific year level
    public function getAssignedSubjects($teacher_id, $year_level)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                s.subject_id,
                s.subject_code,
                s.subject_name,
                tsa.school_year
            FROM teacher_subject_assignments tsa
            INNER JOIN subjects s ON tsa.subject_id = s.subject_id
            WHERE tsa.teacher_id = ? AND tsa.year_level = ?
            ORDER BY s.subject_name ASC
        ");

        $stmt->execute([$teacher_id, $year_level]);

        return $stmt->fetchAll();
    }

    // get all year levels where teacher has assigned subjects
    public function getAssignedYearLevels($teacher_id)
    {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT year_level
            FROM teacher_subject_assignments
            WHERE teacher_id = ?
            ORDER BY year_level ASC
        ");

        $stmt->execute([$teacher_id]);

        return $stmt->fetchAll();
    }

    public function getTotalActiveTeachers()
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total 
            FROM users 
            WHERE role = 'teacher' 
            AND status = 'active'
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    // get count of teachers with subject assignments
    public function getAssignedTeachersCount()
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(DISTINCT teacher_id) as total 
            FROM teacher_subject_assignments
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
