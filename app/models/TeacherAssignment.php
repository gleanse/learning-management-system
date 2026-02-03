<?php

require_once __DIR__ . '/../../config/db_connection.php';

class TeacherAssignment
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // assign teacher to subject and section
    public function assignTeacher($teacher_id, $subject_id, $section_id, $year_level, $school_year)
    {
        $stmt = $this->connection->prepare("
            INSERT INTO teacher_subject_assignments 
            (teacher_id, subject_id, section_id, year_level, school_year, assigned_date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        return $stmt->execute([$teacher_id, $subject_id, $section_id, $year_level, $school_year]);
    }

    // assign teacher to multiple subjects for one section
    public function assignTeacherMultipleSubjects($teacher_id, $subject_ids, $section_id, $year_level, $school_year)
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare("
                INSERT INTO teacher_subject_assignments 
                (teacher_id, subject_id, section_id, year_level, school_year, assigned_date)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            foreach ($subject_ids as $subject_id) {
                $stmt->execute([$teacher_id, $subject_id, $section_id, $year_level, $school_year]);
            }

            $this->connection->commit();
            return true;
        } catch (Exception $e) {
            $this->connection->rollBack();
            return false;
        }
    }

    // get all assignments with teacher, subject, and section info
    public function getAllAssignments()
    {
        $stmt = $this->connection->prepare("
            SELECT 
                tsa.assignment_id,
                tsa.teacher_id,
                tsa.subject_id,
                tsa.section_id,
                tsa.year_level,
                tsa.school_year,
                tsa.assigned_date,
                CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                s.subject_code,
                s.subject_name,
                sec.section_name
            FROM teacher_subject_assignments tsa
            INNER JOIN users u ON tsa.teacher_id = u.id
            INNER JOIN subjects s ON tsa.subject_id = s.subject_id
            INNER JOIN sections sec ON tsa.section_id = sec.section_id
            ORDER BY u.last_name ASC, sec.section_name ASC, s.subject_name ASC
        ");

        $stmt->execute();
        return $stmt->fetchAll();
    }

    // get assignments grouped by teacher and section (for the table view)
    public function getAssignmentsGrouped()
    {
        $stmt = $this->connection->prepare("
            SELECT 
                tsa.teacher_id,
                tsa.section_id,
                tsa.year_level,
                tsa.school_year,
                CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                sec.section_name,
                GROUP_CONCAT(s.subject_name ORDER BY s.subject_name SEPARATOR ', ') as subjects,
                COUNT(tsa.subject_id) as subject_count
            FROM teacher_subject_assignments tsa
            INNER JOIN users u ON tsa.teacher_id = u.id
            INNER JOIN subjects s ON tsa.subject_id = s.subject_id
            INNER JOIN sections sec ON tsa.section_id = sec.section_id
            GROUP BY tsa.teacher_id, tsa.section_id, tsa.year_level, tsa.school_year
            ORDER BY u.last_name ASC, sec.section_name ASC
        ");

        $stmt->execute();
        return $stmt->fetchAll();
    }

    // check if assignment already exists
    public function assignmentExists($teacher_id, $subject_id, $section_id, $school_year)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count
            FROM teacher_subject_assignments
            WHERE teacher_id = ? AND subject_id = ? AND section_id = ? AND school_year = ?
        ");

        $stmt->execute([$teacher_id, $subject_id, $section_id, $school_year]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    // remove assignment
    public function removeAssignment($assignment_id)
    {
        $stmt = $this->connection->prepare("
            DELETE FROM teacher_subject_assignments
            WHERE assignment_id = ?
        ");

        return $stmt->execute([$assignment_id]);
    }

    // remove all assignments for a teacher-section combo
    public function removeTeacherSectionAssignments($teacher_id, $section_id, $school_year)
    {
        $stmt = $this->connection->prepare("
            DELETE FROM teacher_subject_assignments
            WHERE teacher_id = ? AND section_id = ? AND school_year = ?
        ");

        return $stmt->execute([$teacher_id, $section_id, $school_year]);
    }
}
