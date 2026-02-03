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

    // get all subjects assigned to this teacher for a specific year level (NOW WITH SECTIONS)
    public function getAssignedSubjects($teacher_id, $year_level)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                s.subject_id,
                s.subject_code,
                s.subject_name,
                tsa.school_year,
                sec.section_id,
                sec.section_name
            FROM teacher_subject_assignments tsa
            INNER JOIN subjects s ON tsa.subject_id = s.subject_id
            INNER JOIN sections sec ON tsa.section_id = sec.section_id
            WHERE tsa.teacher_id = ? 
            AND tsa.year_level = ?
            AND tsa.status = 'active'
            ORDER BY s.subject_name ASC, sec.section_name ASC
        ");

        $stmt->execute([$teacher_id, $year_level]);

        return $stmt->fetchAll();
    }

    // get all subjects assigned to this teacher grouped by subject (no duplicates)
    public function getAssignedSubjectsGrouped($teacher_id, $year_level)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                s.subject_id,
                s.subject_code,
                s.subject_name,
                tsa.school_year,
                COUNT(DISTINCT tsa.section_id) as section_count
            FROM teacher_subject_assignments tsa
            INNER JOIN subjects s ON tsa.subject_id = s.subject_id
            WHERE tsa.teacher_id = ? 
            AND tsa.year_level = ?
            AND tsa.status = 'active'
            GROUP BY s.subject_id, s.subject_code, s.subject_name, tsa.school_year
            ORDER BY s.subject_name ASC
        ");

        $stmt->execute([$teacher_id, $year_level]);

        return $stmt->fetchAll();
    }

    // get sections assigned to teacher for a specific subject
    public function getSectionsBySubject($teacher_id, $subject_id, $year_level, $school_year)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                sec.section_id,
                sec.section_name,
                sec.year_level,
                sec.school_year,
                COUNT(DISTINCT st.student_id) as student_count
            FROM teacher_subject_assignments tsa
            INNER JOIN sections sec ON tsa.section_id = sec.section_id
            LEFT JOIN students st ON st.section_id = sec.section_id AND st.enrollment_status = 'active'
            WHERE tsa.teacher_id = ? 
            AND tsa.subject_id = ? 
            AND tsa.year_level = ? 
            AND tsa.school_year = ?
            AND tsa.status = 'active'
            GROUP BY sec.section_id, sec.section_name, sec.year_level, sec.school_year
            ORDER BY sec.section_name ASC
        ");

        $stmt->execute([$teacher_id, $subject_id, $year_level, $school_year]);

        return $stmt->fetchAll();
    }

    // get all year levels where teacher has assigned subjects
    public function getAssignedYearLevels($teacher_id)
    {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT year_level
            FROM teacher_subject_assignments
            WHERE teacher_id = ? 
            AND status = 'active'
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

    // get all teachers (for admin dropdown)
    public function getAllActiveTeachers()
    {
        $stmt = $this->connection->prepare("
            SELECT 
                id,
                CONCAT(first_name, ' ', last_name) as full_name,
                email
            FROM users
            WHERE role = 'teacher' AND status = 'active'
            ORDER BY last_name ASC, first_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
