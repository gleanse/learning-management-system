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

    // get students by section with pagination
    public function getStudentsBySectionWithPagination($section_id, $limit, $offset, $search = '')
    {
        $sql = "
            SELECT 
                s.student_id, s.student_number, s.year_level, s.enrollment_status,
                u.first_name, u.middle_name, u.last_name, u.email
            FROM students s
            INNER JOIN users u ON s.user_id = u.id
            WHERE s.section_id = :section_id
        ";

        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE :search OR u.last_name LIKE :search OR u.first_name LIKE :search)";
        }

        $sql .= " ORDER BY u.last_name ASC, u.first_name ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':section_id', $section_id);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        if (!empty($search)) {
            $stmt->bindValue(':search', "%{$search}%");
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    // get total students in section count for pagination
    public function getTotalStudentsInSectionCount($section_id, $search = '')
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM students s
            INNER JOIN users u ON s.user_id = u.id
            WHERE s.section_id = ?
        ";
        $params = [$section_id];

        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE ? OR u.last_name LIKE ? OR u.first_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    // get unassigned students (students without section)
    public function getUnassignedStudents($limit = null, $offset = null, $search = '')
    {
        $sql = "
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
            WHERE s.section_id IS NULL
            AND s.enrollment_status = 'active'
        ";

        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE :search OR u.last_name LIKE :search OR u.first_name LIKE :search OR u.email LIKE :search)";
        }

        $sql .= " ORDER BY s.year_level ASC, u.last_name ASC, u.first_name ASC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
        }

        $stmt = $this->connection->prepare($sql);

        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            }
        }

        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindValue(':search', $search_term);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    // get total unassigned students count
    public function getTotalUnassignedCount($search = '')
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM students s
            INNER JOIN users u ON s.user_id = u.id
            WHERE s.section_id IS NULL
            AND s.enrollment_status = 'active'
        ";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE ? OR u.last_name LIKE ? OR u.first_name LIKE ? OR u.email LIKE ?)";
            $search_term = "%{$search}%";
            $params = [$search_term, $search_term, $search_term, $search_term];
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }

    // get total students by year level count
    public function getTotalStudentsByYearLevelCount($year_level, $search = '')
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM students s
            INNER JOIN users u ON s.user_id = u.id
            WHERE s.year_level = ?
            AND s.enrollment_status = 'active'
        ";

        $params = [$year_level];

        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE ? OR u.last_name LIKE ? OR u.first_name LIKE ? OR u.email LIKE ?)";
            $search_term = "%{$search}%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }

    // get students by year level (for filtering in assignment)
    public function getStudentsByYearLevel($year_level, $limit = null, $offset = null, $search = '')
    {
        $sql = "
            SELECT 
                s.student_id,
                s.student_number,
                s.year_level,
                s.section_id,
                s.enrollment_status,
                u.first_name,
                u.middle_name,
                u.last_name,
                u.email,
                sec.section_name
            FROM students s
            INNER JOIN users u ON s.user_id = u.id
            LEFT JOIN sections sec ON s.section_id = sec.section_id
            WHERE s.year_level = :year_level
            AND s.enrollment_status = 'active'
        ";

        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE :search OR u.last_name LIKE :search OR u.first_name LIKE :search OR u.email LIKE :search)";
        }

        $sql .= " ORDER BY u.last_name ASC, u.first_name ASC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':year_level', $year_level);

        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            }
        }

        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindValue(':search', $search_term);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    // assign student to section
    public function assignToSection($student_id, $section_id)
    {
        $stmt = $this->connection->prepare("
            UPDATE students 
            SET section_id = ? 
            WHERE student_id = ?
        ");

        return $stmt->execute([$section_id, $student_id]);
    }

    // assign multiple students to section
    public function assignMultipleToSection($student_ids, $section_id)
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare("
                UPDATE students 
                SET section_id = ? 
                WHERE student_id = ?
            ");

            foreach ($student_ids as $student_id) {
                $stmt->execute([$section_id, $student_id]);
            }

            $this->connection->commit();
            return true;
        } catch (Exception $e) {
            $this->connection->rollBack();
            return false;
        }
    }

    // remove student from section
    public function removeFromSection($student_id)
    {
        $stmt = $this->connection->prepare("
            UPDATE students 
            SET section_id = NULL 
            WHERE student_id = ?
        ");

        return $stmt->execute([$student_id]);
    }

    // get student by id with full details
    public function getStudentById($student_id)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                s.student_id,
                s.student_number,
                s.year_level,
                s.section_id,
                s.enrollment_status,
                u.first_name,
                u.middle_name,
                u.last_name,
                u.email,
                sec.section_name,
                sec.education_level,
                sec.strand_course
            FROM students s
            INNER JOIN users u ON s.user_id = u.id
            LEFT JOIN sections sec ON s.section_id = sec.section_id
            WHERE s.student_id = ?
        ");

        $stmt->execute([$student_id]);
        return $stmt->fetch();
    }

    // check if student already has a section
    public function hasSection($student_id)
    {
        $stmt = $this->connection->prepare("
            SELECT section_id 
            FROM students 
            WHERE student_id = ?
        ");

        $stmt->execute([$student_id]);
        $result = $stmt->fetch();

        return $result && $result['section_id'] !== null;
    }

    // get recent assignments with admin info
    public function getRecentAssignments($limit = 10)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                sa.assignment_id,
                sa.assigned_at,
                s.student_number,
                u_student.first_name as student_first_name,
                u_student.last_name as student_last_name,
                s.year_level,
                sec.section_name,
                sec.education_level,
                sec.strand_course,
                u_admin.first_name as admin_first_name,
                u_admin.last_name as admin_last_name,
                u_admin.username as admin_username
            FROM student_assignments sa
            INNER JOIN students s ON sa.student_id = s.student_id
            INNER JOIN users u_student ON s.user_id = u_student.id
            INNER JOIN sections sec ON sa.section_id = sec.section_id
            INNER JOIN users u_admin ON sa.assigned_by = u_admin.id
            ORDER BY sa.assigned_at DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // log student assignment
    public function logAssignment($student_id, $section_id, $assigned_by_user_id)
    {
        try {
            $stmt = $this->connection->prepare("
                INSERT INTO student_assignments (student_id, section_id, assigned_by, assigned_at)
                VALUES (?, ?, ?, NOW())
            ");

            $stmt->execute([$student_id, $section_id, $assigned_by_user_id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
