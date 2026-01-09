<?php

require_once __DIR__ . '/../../config/db_connection.php';

class Student
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // get all students enrolled in a specific subject and section
    public function getEnrolledStudentsInSubject($subject_id, $section_id, $school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                s.student_id,
                s.student_number,
                u.first_name,
                u.middle_name,
                u.last_name,
                s.year_level,
                sec.section_name
            FROM student_subject_enrollments sse
            INNER JOIN students s ON sse.student_id = s.student_id
            INNER JOIN users u ON s.user_id = u.id
            LEFT JOIN sections sec ON s.section_id = sec.section_id
            WHERE sse.subject_id = ? 
                AND s.section_id = ?
                AND sse.school_year = ? 
                AND sse.semester = ?
            ORDER BY u.last_name ASC, u.first_name ASC
        ");
        
        $stmt->execute([$subject_id, $section_id, $school_year, $semester]);
        
        return $stmt->fetchAll();
    }
    
    // get all sections for a specific subject and year level
    public function getSectionsBySubjectAndYearLevel($subject_id, $year_level, $school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT
                sec.section_id,
                sec.section_name,
                sec.year_level,
                COUNT(DISTINCT sse.student_id) as student_count
            FROM sections sec
            INNER JOIN students s ON sec.section_id = s.section_id
            INNER JOIN student_subject_enrollments sse ON s.student_id = sse.student_id
            WHERE sse.subject_id = ?
                AND sec.year_level = ?
                AND sse.school_year = ?
                AND sse.semester = ?
            GROUP BY sec.section_id, sec.section_name, sec.year_level
            ORDER BY sec.section_name ASC
        ");
        
        $stmt->execute([$subject_id, $year_level, $school_year, $semester]);
        
        return $stmt->fetchAll();
    }
}