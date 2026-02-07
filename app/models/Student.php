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
                sec.section_name,
                sec.education_level,
                sec.strand_course
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
                sec.education_level,
                sec.year_level,
                sec.strand_course,
                sec.max_capacity,
                COUNT(DISTINCT sse.student_id) as student_count
            FROM sections sec
            INNER JOIN students s ON sec.section_id = s.section_id
            INNER JOIN student_subject_enrollments sse ON s.student_id = sse.student_id
            WHERE sse.subject_id = ?
                AND sec.year_level = ?
                AND sse.school_year = ?
                AND sse.semester = ?
            GROUP BY sec.section_id, sec.section_name, sec.education_level, sec.year_level, sec.strand_course, sec.max_capacity
            ORDER BY sec.section_name ASC
        ");

        $stmt->execute([$subject_id, $year_level, $school_year, $semester]);

        return $stmt->fetchAll();
    }

    public function getSectionById($section_id)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                section_id,
                section_name,
                education_level,
                year_level,
                strand_course,
                max_capacity
            FROM sections
            WHERE section_id = ?
        ");

        $stmt->execute([$section_id]);

        return $stmt->fetch();
    }

    // get student_id by user_id
    public function getStudentIdByUserId($user_id)
    {
        $stmt = $this->connection->prepare("
            SELECT student_id
            FROM students
            WHERE user_id = ?
        ");

        $stmt->execute([$user_id]);
        $result = $stmt->fetch();

        return $result ? $result['student_id'] : null;
    }

    // get all year levels for a student
    public function getYearLevelsByStudentId($student_id, $school_year = null)
    {
        $school_year = $school_year ?? '2025-2026'; // TODO: can be made dynamic later

        $stmt = $this->connection->prepare("
            SELECT DISTINCT s.year_level
            FROM student_subject_enrollments sse
            INNER JOIN students s ON sse.student_id = s.student_id
            WHERE sse.student_id = ? 
                AND sse.school_year = ?
            ORDER BY s.year_level ASC
        ");

        $stmt->execute([$student_id, $school_year]);

        return $stmt->fetchAll();
    }

    // get all semesters for a student in a specific year level
    public function getSemestersByStudentIdAndYearLevel($student_id, $year_level, $school_year = null)
    {
        $school_year = $school_year ?? '2025-2026'; // TODO: can be made dynamic later

        $stmt = $this->connection->prepare("
            SELECT DISTINCT sse.semester
            FROM student_subject_enrollments sse
            INNER JOIN students s ON sse.student_id = s.student_id
            WHERE sse.student_id = ? 
                AND s.year_level = ?
                AND sse.school_year = ?
            ORDER BY 
                CASE sse.semester
                    WHEN 'First' THEN 1
                    WHEN 'Second' THEN 2
                END ASC
        ");

        $stmt->execute([$student_id, $year_level, $school_year]);

        return $stmt->fetchAll();
    }

    // get all subjects for a student in a specific year level and semester
    public function getSubjectsByStudentIdYearLevelAndSemester($student_id, $year_level, $semester, $school_year = null)
    {
        $school_year = $school_year ?? '2025-2026'; // TODO: can be made dynamic later

        $stmt = $this->connection->prepare("
            SELECT DISTINCT
                sub.subject_id,
                sub.subject_code,
                sub.subject_name,
                sub.description
            FROM student_subject_enrollments sse
            INNER JOIN students s ON sse.student_id = s.student_id
            INNER JOIN subjects sub ON sse.subject_id = sub.subject_id
            WHERE sse.student_id = ? 
                AND s.year_level = ?
                AND sse.semester = ?
                AND sse.school_year = ?
            ORDER BY sub.subject_name ASC
        ");

        $stmt->execute([$student_id, $year_level, $semester, $school_year]);

        return $stmt->fetchAll();
    }

    // get all grades for a student in a specific subject
    public function getGradesByStudentIdAndSubject($student_id, $subject_id, $semester, $school_year = null)
    {
        $school_year = $school_year ?? '2025-2026'; // TODO: can be made dynamic later

        $stmt = $this->connection->prepare("
            SELECT 
                g.grade_id,
                g.grading_period,
                g.grade_value,
                g.remarks,
                g.graded_date,
                gp.deadline_date,
                gp.is_locked,
                u.first_name as teacher_first_name,
                u.last_name as teacher_last_name
            FROM grades g
            INNER JOIN users u ON g.teacher_id = u.id
            LEFT JOIN grading_periods gp ON g.school_year = gp.school_year 
                AND g.semester = gp.semester 
                AND g.grading_period = gp.grading_period
            WHERE g.student_id = ? 
                AND g.subject_id = ?
                AND g.semester = ?
                AND g.school_year = ?
            ORDER BY 
                CASE g.grading_period
                    WHEN 'Prelim' THEN 1
                    WHEN 'Midterm' THEN 2
                    WHEN 'Prefinal' THEN 3
                    WHEN 'Final' THEN 4
                END ASC
        ");

        $stmt->execute([$student_id, $subject_id, $semester, $school_year]);

        return $stmt->fetchAll();
    }

    // get student info including section
    public function getStudentInfoByUserId($user_id)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                s.student_id,
                s.student_number,
                s.year_level,
                s.section_id,
                sec.section_name,
                sec.education_level,
                sec.strand_course,
                u.first_name,
                u.middle_name,
                u.last_name,
                u.email
            FROM students s
            INNER JOIN users u ON s.user_id = u.id
            LEFT JOIN sections sec ON s.section_id = sec.section_id
            WHERE s.user_id = ?
        ");

        $stmt->execute([$user_id]);

        return $stmt->fetch();
    }

    public function getTotalActiveStudents()
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total 
            FROM students 
            WHERE enrollment_status = 'active'
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getStudentsEnrolledThisMonth()
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total 
            FROM students 
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
            AND enrollment_status = 'active'
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getRecentEnrollments($limit = 5)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                s.student_number,
                u.first_name,
                u.middle_name,
                u.last_name,
                sec.section_name,
                sec.education_level,
                sec.strand_course,
                s.year_level,
                s.created_at
            FROM students s
            JOIN users u ON s.user_id = u.id
            JOIN sections sec ON s.section_id = sec.section_id
            WHERE s.enrollment_status = 'active'
            ORDER BY s.created_at DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getStudentCountByStatus()
    {
        $stmt = $this->connection->prepare("
            SELECT 
                enrollment_status,
                COUNT(*) as count
            FROM students
            GROUP BY enrollment_status
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // get students by section id with enrollment count
    public function getStudentsBySection($section_id)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                s.student_id,
                s.student_number,
                s.year_level,
                s.enrollment_status,
                u.first_name,
                u.middle_name,
                u.last_name,
                u.email
            FROM students s
            INNER JOIN users u ON s.user_id = u.id
            WHERE s.section_id = ?
            ORDER BY u.last_name ASC, u.first_name ASC
        ");

        $stmt->execute([$section_id]);

        return $stmt->fetchAll();
    }
}
