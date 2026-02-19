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

    // fetch enrolled students for the grade students page
    // uses student_section_history instead of students.section_id so historical school years still work
    public function getEnrolledStudentsInSubject($subject_id, $section_id, $school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                s.student_id,
                s.student_number,
                s.lrn,
                s.first_name,
                s.middle_name,
                s.last_name,
                s.year_level,
                s.education_level,
                sec.section_name,
                sec.strand_course
            FROM student_subject_enrollments sse
            INNER JOIN students s ON sse.student_id = s.student_id
            INNER JOIN student_section_history ssh
                ON ssh.student_id = s.student_id
                AND ssh.section_id = ?
                AND ssh.school_year = ?
                AND ssh.semester = ?
            INNER JOIN sections sec ON sec.section_id = ssh.section_id
            WHERE sse.subject_id = ?
                AND sse.school_year = ?
                AND sse.semester = ?
            GROUP BY s.student_id
            ORDER BY s.last_name ASC, s.first_name ASC
        ");

        $stmt->execute([$section_id, $school_year, $semester, $subject_id, $school_year, $semester]);

        return $stmt->fetchAll();
    }

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

    // get student_id by user_id
    public function getStudentIdByUserId($user_id)
    {
        $stmt = $this->connection->prepare("SELECT student_id FROM students WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result ? $result['student_id'] : null;
    }

    public function getYearLevelsByStudentId($student_id, $school_year = '2025-2026')
    {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT s.year_level
            FROM student_subject_enrollments sse
            INNER JOIN students s ON sse.student_id = s.student_id
            WHERE sse.student_id = ? AND sse.school_year = ?
            ORDER BY s.year_level ASC
        ");
        $stmt->execute([$student_id, $school_year]);
        return $stmt->fetchAll();
    }

    public function getSemestersByStudentIdAndYearLevel($student_id, $year_level, $school_year = '2025-2026')
    {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT sse.semester
            FROM student_subject_enrollments sse
            INNER JOIN students s ON sse.student_id = s.student_id
            WHERE sse.student_id = ? 
                AND s.year_level = ?
                AND sse.school_year = ?
            ORDER BY CASE sse.semester WHEN 'First' THEN 1 WHEN 'Second' THEN 2 END ASC
        ");
        $stmt->execute([$student_id, $year_level, $school_year]);
        return $stmt->fetchAll();
    }

    public function getSubjectsByStudentIdYearLevelAndSemester($student_id, $year_level, $semester, $school_year = '2025-2026')
    {
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

    public function getGradesByStudentIdAndSubject($student_id, $subject_id, $semester, $school_year = '2025-2026')
    {
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

    public function getStudentInfoByUserId($user_id)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                s.student_id,
                s.student_number,
                s.lrn,
                s.year_level,
                s.education_level,
                s.strand_course,
                s.section_id,
                sec.section_name,
                s.first_name,
                s.middle_name,
                s.last_name,
                u.email
            FROM students s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN sections sec ON s.section_id = sec.section_id
            WHERE s.user_id = ?
        ");

        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }

    public function getTotalActiveStudents()
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as total FROM students WHERE enrollment_status = 'active'");
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
                s.first_name,
                s.middle_name,
                s.last_name,
                sec.section_name,
                sec.education_level,
                sec.strand_course,
                s.year_level,
                s.created_at
            FROM students s
            LEFT JOIN sections sec ON s.section_id = sec.section_id
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
        $stmt = $this->connection->prepare("SELECT enrollment_status, COUNT(*) as count FROM students GROUP BY enrollment_status");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStudentsBySection($section_id)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                s.student_id, 
                s.student_number, 
                s.year_level, 
                s.enrollment_status,
                s.first_name, 
                s.middle_name, 
                s.last_name,
                u.email,
                s.user_id
            FROM students s
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.section_id = ?
            ORDER BY s.last_name ASC, s.first_name ASC
        ");
        $stmt->execute([$section_id]);
        return $stmt->fetchAll();
    }

    public function getStudentsBySectionWithPagination($section_id, $limit, $offset, $search = '')
    {
        $sql = "
            SELECT 
                s.student_id, 
                s.student_number, 
                s.year_level, 
                s.enrollment_status,
                s.first_name, 
                s.middle_name, 
                s.last_name,
                u.email,
                s.user_id
            FROM students s
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.section_id = :section_id
        ";

        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE :search OR s.last_name LIKE :search OR s.first_name LIKE :search)";
        }
        $sql .= " ORDER BY s.last_name ASC, s.first_name ASC LIMIT :limit OFFSET :offset";

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

    public function getTotalStudentsInSectionCount($section_id, $search = '')
    {
        $sql = "SELECT COUNT(*) as count FROM students s WHERE s.section_id = ?";
        $params = [$section_id];
        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE ? OR s.last_name LIKE ? OR s.first_name LIKE ?)";
            $params = array_merge($params, ["%{$search}%", "%{$search}%", "%{$search}%"]);
        }
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getUnassignedStudents($limit = null, $offset = null, $search = '')
    {
        $sql = "
            SELECT 
                s.student_id, 
                s.student_number, 
                s.year_level, 
                s.enrollment_status,
                s.first_name, 
                s.middle_name, 
                s.last_name,
                u.email,
                s.user_id
            FROM students s
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.section_id IS NULL AND s.enrollment_status = 'active'
        ";

        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE :search OR s.last_name LIKE :search OR s.first_name LIKE :search OR u.email LIKE :search)";
        }
        $sql .= " ORDER BY s.year_level ASC, s.last_name ASC, s.first_name ASC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) $sql .= " OFFSET :offset";
        }

        $stmt = $this->connection->prepare($sql);
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        if (!empty($search)) $stmt->bindValue(':search', "%{$search}%");

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalUnassignedCount($search = '')
    {
        $sql = "
            SELECT COUNT(*) as count FROM students s 
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.section_id IS NULL AND s.enrollment_status = 'active'
        ";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE ? OR s.last_name LIKE ? OR s.first_name LIKE ? OR u.email LIKE ?)";
            $term = "%{$search}%";
            $params = [$term, $term, $term, $term];
        }
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getTotalStudentsByYearLevelCount($year_level, $search = '')
    {
        $sql = "
            SELECT COUNT(*) as count FROM students s 
            WHERE s.year_level = ? AND s.enrollment_status = 'active'
        ";
        $params = [$year_level];
        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE ? OR s.last_name LIKE ? OR s.first_name LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term]);
        }
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getStudentsByYearLevel($year_level, $limit = null, $offset = null, $search = '')
    {
        $sql = "
            SELECT 
                s.student_id, 
                s.student_number, 
                s.year_level, 
                s.section_id, 
                s.enrollment_status,
                s.first_name, 
                s.middle_name, 
                s.last_name, 
                u.email, 
                sec.section_name,
                s.user_id
            FROM students s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN sections sec ON s.section_id = sec.section_id
            WHERE s.year_level = :year_level AND s.enrollment_status = 'active'
        ";

        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE :search OR s.last_name LIKE :search OR s.first_name LIKE :search OR u.email LIKE :search)";
        }
        $sql .= " ORDER BY s.last_name ASC, s.first_name ASC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) $sql .= " OFFSET :offset";
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':year_level', $year_level);

        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        if (!empty($search)) $stmt->bindValue(':search', "%{$search}%");

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function assignToSection($student_id, $section_id, $assigned_by_user_id)
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare("UPDATE students SET section_id = ? WHERE student_id = ?");
            $stmt->execute([$section_id, $student_id]);

            $logStmt = $this->connection->prepare("
                INSERT INTO student_assignments (student_id, section_id, assigned_by, assigned_at)
                VALUES (?, ?, ?, NOW())
            ");
            $logStmt->execute([$student_id, $section_id, $assigned_by_user_id]);

            $this->enrollStudentInSectionSubjects($student_id, $section_id);

            $this->connection->commit();
            return true;
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[Student::assignToSection] ' . $e->getMessage());
            return false;
        }
    }

    public function assignMultipleToSection($student_ids, $section_id, $assigned_by_user_id)
    {
        try {
            $this->connection->beginTransaction();

            $updateStmt = $this->connection->prepare("UPDATE students SET section_id = ? WHERE student_id = ?");
            $logStmt = $this->connection->prepare("
                INSERT INTO student_assignments (student_id, section_id, assigned_by, assigned_at)
                VALUES (?, ?, ?, NOW())
            ");

            foreach ($student_ids as $student_id) {
                $updateStmt->execute([$section_id, $student_id]);
                $logStmt->execute([$student_id, $section_id, $assigned_by_user_id]);
                $this->enrollStudentInSectionSubjects($student_id, $section_id);
            }

            $this->connection->commit();
            return true;
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[Student::assignMultipleToSection] ' . $e->getMessage());
            return false;
        }
    }

    // enroll a student into all subjects linked to a section
    private function enrollStudentInSectionSubjects($student_id, $section_id)
    {
        $stmt = $this->connection->prepare("
            SELECT ss.subject_id, sec.school_year
            FROM section_subjects ss
            INNER JOIN sections sec ON sec.section_id = ss.section_id
            WHERE ss.section_id = ?
        ");
        $stmt->execute([$section_id]);
        $subjects = $stmt->fetchAll();

        if (empty($subjects)) {
            return;
        }

        $school_year = $subjects[0]['school_year'];

        $semStmt = $this->connection->prepare("
            SELECT semester FROM enrollment_payments
            WHERE student_id = ? AND school_year = ?
            LIMIT 1
        ");
        $semStmt->execute([$student_id, $school_year]);
        $semResult = $semStmt->fetch();
        $semester = $semResult ? $semResult['semester'] : 'First';

        $checkStmt = $this->connection->prepare("
            SELECT 1 FROM student_subject_enrollments 
            WHERE student_id = ? AND subject_id = ? AND school_year = ? AND semester = ?
        ");

        $insertStmt = $this->connection->prepare("
            INSERT INTO student_subject_enrollments
                (student_id, subject_id, school_year, semester, enrolled_date)
            VALUES (?, ?, ?, ?, CURDATE())
        ");

        foreach ($subjects as $subject) {
            $checkStmt->execute([$student_id, $subject['subject_id'], $school_year, $semester]);
            if (!$checkStmt->fetch()) {
                $insertStmt->execute([$student_id, $subject['subject_id'], $school_year, $semester]);
            }
        }

        // write a section history snapshot so past school years remain queryable
        $this->writeSectionHistory($student_id, $section_id, $school_year, $semester);
    }

    // write or ignore a student_section_history row for the given period
    // called on assignment so every enrollment is snapshotted
    private function writeSectionHistory($student_id, $section_id, $school_year, $semester)
    {
        $secStmt = $this->connection->prepare("SELECT section_name FROM sections WHERE section_id = ?");
        $secStmt->execute([$section_id]);
        $section = $secStmt->fetch();

        if (!$section) {
            return;
        }

        // insert ignore so re-assignments don't cause duplicate key errors
        $stmt = $this->connection->prepare("
            INSERT IGNORE INTO student_section_history
                (student_id, section_id, section_name, school_year, semester)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$student_id, $section_id, $section['section_name'], $school_year, $semester]);
    }

    public function removeFromSection($student_id)
    {
        $stmt = $this->connection->prepare("UPDATE students SET section_id = NULL WHERE student_id = ?");
        return $stmt->execute([$student_id]);
    }

    public function getStudentById($student_id)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                s.student_id,
                s.student_number,
                s.lrn,
                s.year_level,
                s.education_level,
                s.strand_course,
                s.section_id,
                s.enrollment_status,
                s.first_name,
                s.middle_name,
                s.last_name,
                u.email,
                sec.section_name,
                sec.education_level as section_education_level,
                sec.strand_course as section_strand_course,
                s.user_id
            FROM students s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN sections sec ON s.section_id = sec.section_id
            WHERE s.student_id = ?
        ");

        $stmt->execute([$student_id]);
        return $stmt->fetch();
    }

    public function hasSection($student_id)
    {
        $stmt = $this->connection->prepare("SELECT section_id FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $result = $stmt->fetch();
        return $result && $result['section_id'] !== null;
    }

    public function getRecentAssignments($limit = 10)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                sa.assignment_id,
                sa.assigned_at,
                s.student_number,
                s.first_name as student_first_name,
                s.last_name as student_last_name,
                s.year_level,
                sec.section_name,
                sec.education_level,
                sec.strand_course,
                u_admin.first_name as admin_first_name,
                u_admin.last_name as admin_last_name,
                u_admin.username as admin_username
            FROM student_assignments sa
            INNER JOIN students s ON sa.student_id = s.student_id
            INNER JOIN sections sec ON sa.section_id = sec.section_id
            INNER JOIN users u_admin ON sa.assigned_by = u_admin.id
            ORDER BY sa.assigned_at DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEligibleStudentsForSection($section_id, $limit = null, $offset = null, $search = '')
    {
        $sql = "
            SELECT 
                s.student_id, 
                s.student_number, 
                s.lrn, 
                s.year_level, 
                s.education_level, 
                s.strand_course, 
                s.enrollment_status,
                s.first_name, 
                s.middle_name, 
                s.last_name, 
                u.email,
                s.user_id
            FROM students s
            LEFT JOIN users u ON s.user_id = u.id
            INNER JOIN sections sec ON 
                sec.section_id = :section_id
                AND s.education_level = sec.education_level
                AND s.year_level = sec.year_level
                AND s.strand_course = sec.strand_course
            WHERE s.section_id IS NULL 
                AND s.enrollment_status = 'active'
        ";

        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE :search OR s.first_name LIKE :search OR s.last_name LIKE :search OR u.email LIKE :search)";
        }

        $sql .= " ORDER BY s.last_name ASC, s.first_name ASC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) $sql .= " OFFSET :offset";
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':section_id', $section_id);

        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        if (!empty($search)) {
            $stmt->bindValue(':search', "%{$search}%");
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalEligibleStudentsCount($section_id, $search = '')
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM students s
            LEFT JOIN users u ON s.user_id = u.id
            INNER JOIN sections sec ON 
                sec.section_id = ?
                AND s.education_level = sec.education_level
                AND s.year_level = sec.year_level
                AND s.strand_course = sec.strand_course
            WHERE s.section_id IS NULL 
                AND s.enrollment_status = 'active'
        ";

        $params = [$section_id];

        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR u.email LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getStudentsWithoutUserAccount($limit = null, $offset = null, $search = '')
    {
        $sql = "
            SELECT 
                s.student_id,
                s.student_number,
                s.lrn,
                s.first_name,
                s.middle_name,
                s.last_name,
                s.year_level,
                s.education_level,
                s.strand_course,
                s.enrollment_status,
                sec.section_name,
                s.created_at
            FROM students s
            LEFT JOIN sections sec ON s.section_id = sec.section_id
            WHERE s.user_id IS NULL
        ";

        if (!empty($search)) {
            $sql .= " AND (s.student_number LIKE :search OR s.first_name LIKE :search OR s.last_name LIKE :search OR s.lrn LIKE :search)";
        }

        $sql .= " ORDER BY s.created_at DESC, s.last_name ASC, s.first_name ASC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) $sql .= " OFFSET :offset";
        }

        $stmt = $this->connection->prepare($sql);

        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        if (!empty($search)) {
            $stmt->bindValue(':search', "%{$search}%");
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalStudentsWithoutUserAccountCount($search = '')
    {
        $sql = "SELECT COUNT(*) as count FROM students WHERE user_id IS NULL";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (student_number LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR lrn LIKE ?)";
            $term = "%{$search}%";
            $params = [$term, $term, $term, $term];
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function linkStudentToUser($student_id, $user_id)
    {
        $stmt = $this->connection->prepare("UPDATE students SET user_id = ? WHERE student_id = ?");
        return $stmt->execute([$user_id, $student_id]);
    }

    public function createStudentRecord(array $student_data)
    {
        $stmt = $this->connection->prepare("
            INSERT INTO students (
                user_id, first_name, middle_name, last_name, student_number, lrn, 
                section_id, year_level, education_level, strand_course, 
                enrollment_status, guardian_contact, guardian
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $student_data['user_id'] ?? null,
            $student_data['first_name'],
            $student_data['middle_name'] ?? null,
            $student_data['last_name'],
            $student_data['student_number'],
            $student_data['lrn'] ?? null,
            $student_data['section_id'] ?? null,
            $student_data['year_level'],
            $student_data['education_level'],
            $student_data['strand_course'],
            $student_data['enrollment_status'] ?? 'active',
            $student_data['guardian_contact'] ?? null,
            $student_data['guardian'] ?? null
        ]);
    }

    public function hasUserAccount($student_id)
    {
        $stmt = $this->connection->prepare("SELECT user_id FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $result = $stmt->fetch();
        return $result && $result['user_id'] !== null;
    }
}
