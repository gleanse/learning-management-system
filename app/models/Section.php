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

    public function getTotalSections()
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as total FROM sections");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getAllSections()
    {
        $stmt = $this->connection->prepare("
            SELECT section_id, section_name, education_level, year_level, strand_course, max_capacity, school_year
            FROM sections
            ORDER BY education_level DESC, year_level ASC, section_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllSectionsWithStudentCount($school_year = '')
    {
        $stmt = $this->connection->prepare("
            SELECT 
                sec.section_id, sec.section_name, sec.education_level, sec.year_level, 
                sec.strand_course, sec.max_capacity, sec.school_year,
                COUNT(s.student_id) as student_count
            FROM sections sec
            LEFT JOIN students s ON sec.section_id = s.section_id AND s.enrollment_status = 'active'
            WHERE sec.school_year = ?
            GROUP BY sec.section_id, sec.section_name, sec.education_level, sec.year_level, sec.strand_course, sec.max_capacity, sec.school_year
            ORDER BY sec.education_level DESC, sec.year_level ASC, sec.section_name ASC
        ");
        $stmt->execute([$school_year]);
        return $stmt->fetchAll();
    }

    public function getSectionsByYearLevel($year_level)
    {
        $stmt = $this->connection->prepare("
            SELECT section_id, section_name, education_level, year_level, strand_course, max_capacity, school_year
            FROM sections
            WHERE year_level = ?
            ORDER BY section_name ASC
        ");
        $stmt->execute([$year_level]);
        return $stmt->fetchAll();
    }

    public function getSectionsByEducationLevel($education_level, $school_year = '')
    {
        $stmt = $this->connection->prepare("
            SELECT section_id, section_name, education_level, year_level, strand_course, max_capacity, school_year
            FROM sections
            WHERE education_level = ? AND school_year = ?
            ORDER BY year_level ASC, section_name ASC
        ");
        $stmt->execute([$education_level, $school_year]);
        return $stmt->fetchAll();
    }

    public function getSectionById($section_id)
    {
        $stmt = $this->connection->prepare("
            SELECT section_id, section_name, education_level, year_level, strand_course, max_capacity, school_year
            FROM sections
            WHERE section_id = ?
        ");
        $stmt->execute([$section_id]);
        return $stmt->fetch();
    }

    public function getSectionWithStudentCount($section_id)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                sec.section_id, sec.section_name, sec.education_level, sec.year_level, 
                sec.strand_course, sec.max_capacity, sec.school_year,
                COUNT(s.student_id) as student_count
            FROM sections sec
            LEFT JOIN students s ON sec.section_id = s.section_id AND s.enrollment_status = 'active'
            WHERE sec.section_id = ?
            GROUP BY sec.section_id, sec.section_name, sec.education_level, sec.year_level, sec.strand_course, sec.max_capacity, sec.school_year
        ");
        $stmt->execute([$section_id]);
        return $stmt->fetch();
    }

    // returns all distinct school years that exist in sections table, newest first
    public function getDistinctSchoolYears()
    {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT school_year
            FROM sections
            ORDER BY school_year DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function create($section_name, $education_level, $year_level, $strand_course, $max_capacity, $school_year)
    {
        $stmt = $this->connection->prepare("
            INSERT INTO sections (section_name, education_level, year_level, strand_course, max_capacity, school_year)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$section_name, $education_level, $year_level, $strand_course, $max_capacity, $school_year]);
    }

    public function update($section_id, $section_name, $education_level, $year_level, $strand_course, $max_capacity, $school_year)
    {
        $stmt = $this->connection->prepare("
            UPDATE sections 
            SET section_name = ?, education_level = ?, year_level = ?, strand_course = ?, max_capacity = ?, school_year = ?
            WHERE section_id = ?
        ");
        return $stmt->execute([$section_name, $education_level, $year_level, $strand_course, $max_capacity, $school_year, $section_id]);
    }

    public function delete($section_id)
    {
        // check for enrolled students before deleting
        $check_stmt = $this->connection->prepare("SELECT COUNT(*) as student_count FROM students WHERE section_id = ?");
        $check_stmt->execute([$section_id]);
        $result = $check_stmt->fetch();

        if ($result['student_count'] > 0) {
            return false;
        }

        $stmt = $this->connection->prepare("DELETE FROM sections WHERE section_id = ?");
        return $stmt->execute([$section_id]);
    }

    public function sectionExists($section_name, $school_year, $exclude_id = null)
    {
        if ($exclude_id) {
            $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM sections WHERE section_name = ? AND school_year = ? AND section_id != ?");
            $stmt->execute([$section_name, $school_year, $exclude_id]);
        } else {
            $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM sections WHERE section_name = ? AND school_year = ?");
            $stmt->execute([$section_name, $school_year]);
        }
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function getWithPagination($limit, $offset, $search = '', $school_year = '')
    {
        $sql = "
            SELECT 
                sec.section_id, sec.section_name, sec.education_level, sec.year_level, 
                sec.strand_course, sec.max_capacity, sec.school_year,
                COUNT(s.student_id) as student_count
            FROM sections sec
            LEFT JOIN students s ON sec.section_id = s.section_id AND s.enrollment_status = 'active'
            WHERE sec.school_year = :school_year
        ";

        if (!empty($search)) {
            $sql .= " AND (sec.section_name LIKE :search OR sec.strand_course LIKE :search)";
        }

        $sql .= " GROUP BY sec.section_id, sec.section_name, sec.education_level, sec.year_level, sec.strand_course, sec.max_capacity, sec.school_year 
                  ORDER BY sec.education_level DESC, sec.year_level ASC, sec.section_name ASC 
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':school_year', $school_year);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        if (!empty($search)) {
            $stmt->bindValue(':search', "%{$search}%");
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalCount($search = '', $school_year = '')
    {
        $sql    = "SELECT COUNT(*) as count FROM sections WHERE school_year = ?";
        $params = [$school_year];

        if (!empty($search)) {
            $sql     .= " AND (section_name LIKE ? OR strand_course LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getSectionsWithAvailability($school_year = '', $year_level = null, $education_level = null)
    {
        $sql = "
            SELECT 
                sec.section_id, sec.section_name, sec.education_level, sec.year_level, 
                sec.strand_course, sec.max_capacity, sec.school_year,
                COUNT(s.student_id) as current_students,
                (sec.max_capacity - COUNT(s.student_id)) as available_slots
            FROM sections sec
            LEFT JOIN students s ON sec.section_id = s.section_id AND s.enrollment_status = 'active'
            WHERE sec.school_year = ?
        ";

        $params = [$school_year];

        if ($year_level !== null) {
            $sql     .= " AND sec.year_level = ?";
            $params[] = $year_level;
        }

        if ($education_level !== null) {
            $sql     .= " AND sec.education_level = ?";
            $params[] = $education_level;
        }

        $sql .= " 
            GROUP BY sec.section_id, sec.section_name, sec.education_level, sec.year_level, sec.strand_course, sec.max_capacity, sec.school_year
            HAVING available_slots > 0
            ORDER BY sec.education_level DESC, sec.year_level ASC, sec.section_name ASC
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function hasAvailableSlots($section_id)
    {
        $stmt = $this->connection->prepare("
            SELECT sec.max_capacity, COUNT(s.student_id) as current_students
            FROM sections sec
            LEFT JOIN students s ON sec.section_id = s.section_id AND s.enrollment_status = 'active'
            WHERE sec.section_id = ?
            GROUP BY sec.section_id, sec.max_capacity
        ");
        $stmt->execute([$section_id]);
        $result = $stmt->fetch();

        if (!$result) return false;
        return ($result['max_capacity'] - $result['current_students']) > 0;
    }

    public function getAvailableSlots($section_id)
    {
        $stmt = $this->connection->prepare("
            SELECT sec.max_capacity, COUNT(s.student_id) as current_students
            FROM sections sec
            LEFT JOIN students s ON sec.section_id = s.section_id AND s.enrollment_status = 'active'
            WHERE sec.section_id = ?
            GROUP BY sec.section_id, sec.max_capacity
        ");
        $stmt->execute([$section_id]);
        $result = $stmt->fetch();

        if (!$result) return 0;
        return max(0, $result['max_capacity'] - $result['current_students']);
    }

    // get historical students for a section from snapshot table
    public function getHistoricalStudents($section_id, $limit, $offset, $search = '')
    {
        $search_param = '%' . $search . '%';
        $stmt = $this->connection->prepare("
        SELECT 
            s.student_number,
            s.first_name,
            s.middle_name,
            s.last_name,
            sp.email,
            s.year_level,
            s.enrollment_status,
            h.school_year,
            h.semester
        FROM student_section_history h
        INNER JOIN students s ON s.student_id = h.student_id
        LEFT JOIN student_profiles sp ON sp.student_id = s.student_id
        WHERE h.section_id = :section_id
          AND (s.first_name LIKE :search1 OR s.last_name LIKE :search2 OR s.student_number LIKE :search3)
        ORDER BY s.last_name ASC
        LIMIT :limit OFFSET :offset
    ");
        $stmt->bindValue(':section_id', $section_id,   PDO::PARAM_INT);
        $stmt->bindValue(':search1',    $search_param);
        $stmt->bindValue(':search2',    $search_param);
        $stmt->bindValue(':search3',    $search_param);
        $stmt->bindValue(':limit',      (int) $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset',     (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // total count of historical students for a section
    public function getTotalHistoricalStudents($section_id, $search = '')
    {
        $search_param = '%' . $search . '%';
        $stmt = $this->connection->prepare("
        SELECT COUNT(*) as total
        FROM student_section_history h
        INNER JOIN students s ON s.student_id = h.student_id
        WHERE h.section_id = ?
          AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_number LIKE ?)
    ");
        $stmt->execute([$section_id, $search_param, $search_param, $search_param]);
        $result = $stmt->fetch();
        return (int) $result['total'];
    }
}
