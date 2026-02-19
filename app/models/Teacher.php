<?php

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/AcademicPeriod.php';

class Teacher
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // get all subjects assigned to this teacher for a specific year level (with sections)
    public function getAssignedSubjects($teacher_id, $year_level, $school_year = null)
    {
        $sql = "
            SELECT 
                s.subject_id,
                s.subject_code,
                s.subject_name,
                tsa.school_year,
                tsa.semester,
                sec.section_id,
                sec.section_name,
                sec.education_level,
                sec.strand_course
            FROM teacher_subject_assignments tsa
            INNER JOIN subjects s ON tsa.subject_id = s.subject_id
            INNER JOIN sections sec ON tsa.section_id = sec.section_id
            WHERE tsa.teacher_id = ? 
            AND tsa.year_level = ?
            AND tsa.status = 'active'
        ";

        $params = [$teacher_id, $year_level];

        if (!empty($school_year)) {
            $sql .= " AND tsa.school_year = ?";
            $params[] = $school_year;
        }

        $sql .= " ORDER BY s.subject_name ASC, sec.section_name ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // get subjects assigned to teacher grouped by subject for a specific year level and school year
    public function getAssignedSubjectsGrouped($teacher_id, $year_level, $school_year = null)
    {
        $sql = "
            SELECT 
                s.subject_id,
                s.subject_code,
                s.subject_name,
                tsa.school_year,
                tsa.semester,
                COUNT(DISTINCT tsa.section_id) as section_count
            FROM teacher_subject_assignments tsa
            INNER JOIN subjects s ON tsa.subject_id = s.subject_id
            WHERE tsa.teacher_id = ? 
            AND tsa.year_level = ?
            AND tsa.status = 'active'
        ";

        $params = [$teacher_id, $year_level];

        if (!empty($school_year)) {
            $sql .= " AND tsa.school_year = ?";
            $params[] = $school_year;
        }

        $sql .= " GROUP BY s.subject_id, s.subject_code, s.subject_name, tsa.school_year, tsa.semester
                  ORDER BY s.subject_name ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // get sections assigned to teacher for a specific subject
    // counts from student_section_history so past school years show correct numbers
    public function getSectionsBySubject($teacher_id, $subject_id, $year_level, $school_year)
    {
        $current = (new AcademicPeriod())->getCurrentPeriod();
        $is_history = $current && $school_year !== $current['school_year'];

        if ($is_history) {
            $count_sql = "
            SELECT COUNT(DISTINCT ssh.student_id)
            FROM student_section_history ssh
            INNER JOIN student_subject_enrollments sse
                ON sse.student_id = ssh.student_id
                AND sse.subject_id = ?
                AND sse.school_year = ?
                AND sse.semester = tsa.semester
            WHERE ssh.section_id = sec.section_id
                AND ssh.school_year = ?
                AND ssh.semester = tsa.semester
        ";
            $subparams = [$subject_id, $school_year, $school_year];
        } else {
            $count_sql = "
            SELECT COUNT(DISTINCT s.student_id)
            FROM students s
            INNER JOIN student_subject_enrollments sse
                ON sse.student_id = s.student_id
                AND sse.subject_id = ?
                AND sse.school_year = ?
                AND sse.semester = tsa.semester
            WHERE s.section_id = sec.section_id
        ";
            $subparams = [$subject_id, $school_year];
        }

        $stmt = $this->connection->prepare("
        SELECT 
            sec.section_id, sec.section_name, sec.education_level, sec.year_level,
            sec.strand_course, sec.max_capacity, sec.school_year,
            ($count_sql) as student_count
        FROM teacher_subject_assignments tsa
        INNER JOIN sections sec ON tsa.section_id = sec.section_id
        WHERE tsa.teacher_id = ? 
            AND tsa.subject_id = ? 
            AND tsa.year_level = ? 
            AND tsa.school_year = ?
            AND tsa.status = 'active'
        GROUP BY sec.section_id, sec.section_name, sec.education_level, sec.year_level, sec.strand_course, sec.max_capacity, sec.school_year
        ORDER BY sec.section_name ASC
    ");

        $stmt->execute(array_merge($subparams, [$teacher_id, $subject_id, $year_level, $school_year]));
        return $stmt->fetchAll();
    }

    // get all year levels where teacher has assigned subjects, filtered by school year
    public function getAssignedYearLevels($teacher_id, $school_year = null)
    {
        $sql = "
            SELECT DISTINCT year_level
            FROM teacher_subject_assignments
            WHERE teacher_id = ? 
            AND status = 'active'
        ";

        $params = [$teacher_id];

        if (!empty($school_year)) {
            $sql .= " AND school_year = ?";
            $params[] = $school_year;
        }

        $sql .= " ORDER BY year_level ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // get all distinct school years teacher has assignments in
    public function getAssignedSchoolYears($teacher_id)
    {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT school_year
            FROM teacher_subject_assignments
            WHERE teacher_id = ? AND status = 'active'
            ORDER BY school_year DESC
        ");
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
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

    // get single teacher details by id
    public function getTeacherById($teacher_id)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                id,
                username,
                email,
                first_name,
                middle_name,
                last_name,
                status,
                CONCAT(first_name, ' ', last_name) as full_name
            FROM users
            WHERE id = ? AND role = 'teacher'
        ");
        $stmt->execute([$teacher_id]);
        return $stmt->fetch();
    }

    // get teachers with pagination
    public function getWithPagination($limit, $offset, $search = '')
    {
        if (!empty($search)) {
            $stmt = $this->connection->prepare("
                SELECT 
                    id,
                    CONCAT(first_name, ' ', last_name) as full_name,
                    email,
                    first_name,
                    last_name
                FROM users
                WHERE role = 'teacher' 
                AND status = 'active'
                AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search)
                ORDER BY last_name ASC, first_name ASC
                LIMIT :limit OFFSET :offset
            ");
            $search_term = "%{$search}%";
            $stmt->bindValue(':search', $search_term, PDO::PARAM_STR);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->connection->prepare("
                SELECT 
                    id,
                    CONCAT(first_name, ' ', last_name) as full_name,
                    email,
                    first_name,
                    last_name
                FROM users
                WHERE role = 'teacher' AND status = 'active'
                ORDER BY last_name ASC, first_name ASC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // get total count for pagination
    public function getTotalCount($search = '')
    {
        if (!empty($search)) {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count
                FROM users
                WHERE role = 'teacher' 
                AND status = 'active'
                AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)
            ");
            $search_term = "%{$search}%";
            $stmt->execute([$search_term, $search_term, $search_term]);
        } else {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count
                FROM users
                WHERE role = 'teacher' AND status = 'active'
            ");
            $stmt->execute();
        }

        $result = $stmt->fetch();
        return $result['count'];
    }
}
