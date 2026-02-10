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
                education_level,
                year_level,
                strand_course,
                max_capacity,
                school_year
            FROM sections
            ORDER BY education_level DESC, year_level ASC, section_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // get all sections with student count (kept for non-paginated needs)
    public function getAllSectionsWithStudentCount($school_year = '2025-2026')
    {
        $stmt = $this->connection->prepare("
            SELECT 
                sec.section_id,
                sec.section_name,
                sec.education_level,
                sec.year_level,
                sec.strand_course,
                sec.max_capacity,
                sec.school_year,
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

    // get sections by year level (for filtering)
    public function getSectionsByYearLevel($year_level)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                section_id,
                section_name,
                education_level,
                year_level,
                strand_course,
                max_capacity,
                school_year
            FROM sections
            WHERE year_level = ?
            ORDER BY section_name ASC
        ");
        $stmt->execute([$year_level]);
        return $stmt->fetchAll();
    }

    // get sections by education level (shs or college)
    public function getSectionsByEducationLevel($education_level, $school_year = '2025-2026')
    {
        $stmt = $this->connection->prepare("
            SELECT 
                section_id,
                section_name,
                education_level,
                year_level,
                strand_course,
                max_capacity,
                school_year
            FROM sections
            WHERE education_level = ? AND school_year = ?
            ORDER BY year_level ASC, section_name ASC
        ");
        $stmt->execute([$education_level, $school_year]);
        return $stmt->fetchAll();
    }

    // get section by id
    public function getSectionById($section_id)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                section_id,
                section_name,
                education_level,
                year_level,
                strand_course,
                max_capacity,
                school_year
            FROM sections
            WHERE section_id = ?
        ");

        $stmt->execute([$section_id]);

        return $stmt->fetch();
    }

    // get section by id with student count
    public function getSectionWithStudentCount($section_id)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                sec.section_id,
                sec.section_name,
                sec.education_level,
                sec.year_level,
                sec.strand_course,
                sec.max_capacity,
                sec.school_year,
                COUNT(s.student_id) as student_count
            FROM sections sec
            LEFT JOIN students s ON sec.section_id = s.section_id AND s.enrollment_status = 'active'
            WHERE sec.section_id = ?
            GROUP BY sec.section_id, sec.section_name, sec.education_level, sec.year_level, sec.strand_course, sec.max_capacity, sec.school_year
        ");

        $stmt->execute([$section_id]);

        return $stmt->fetch();
    }

    // create new section
    public function create($section_name, $education_level, $year_level, $strand_course, $max_capacity, $school_year)
    {
        $stmt = $this->connection->prepare("
            INSERT INTO sections (section_name, education_level, year_level, strand_course, max_capacity, school_year)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([$section_name, $education_level, $year_level, $strand_course, $max_capacity, $school_year]);
    }

    // update section
    public function update($section_id, $section_name, $education_level, $year_level, $strand_course, $max_capacity, $school_year)
    {
        $stmt = $this->connection->prepare("
            UPDATE sections 
            SET section_name = ?, 
                education_level = ?, 
                year_level = ?, 
                strand_course = ?, 
                max_capacity = ?, 
                school_year = ?
            WHERE section_id = ?
        ");

        return $stmt->execute([$section_name, $education_level, $year_level, $strand_course, $max_capacity, $school_year, $section_id]);
    }

    // delete section (only if no students enrolled)
    public function delete($section_id)
    {
        // check if section has students
        $check_stmt = $this->connection->prepare("
            SELECT COUNT(*) as student_count 
            FROM students 
            WHERE section_id = ?
        ");
        $check_stmt->execute([$section_id]);
        $result = $check_stmt->fetch();

        if ($result['student_count'] > 0) {
            return false; // cannot delete section with students
        }

        $stmt = $this->connection->prepare("
            DELETE FROM sections 
            WHERE section_id = ?
        ");

        return $stmt->execute([$section_id]);
    }

    // check if section name already exists for the same school year
    public function sectionExists($section_name, $school_year, $exclude_id = null)
    {
        if ($exclude_id) {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count 
                FROM sections 
                WHERE section_name = ? AND school_year = ? AND section_id != ?
            ");
            $stmt->execute([$section_name, $school_year, $exclude_id]);
        } else {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count 
                FROM sections 
                WHERE section_name = ? AND school_year = ?
            ");
            $stmt->execute([$section_name, $school_year]);
        }

        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    // get sections with pagination
    public function getWithPagination($limit, $offset, $search = '', $school_year = '2025-2026')
    {
        $sql = "
            SELECT 
                sec.section_id,
                sec.section_name,
                sec.education_level,
                sec.year_level,
                sec.strand_course,
                sec.max_capacity,
                sec.school_year,
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
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindValue(':search', $search_term);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    // get total count for pagination
    public function getTotalCount($search = '', $school_year = '2025-2026')
    {
        if (!empty($search)) {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count 
                FROM sections 
                WHERE school_year = ? AND (section_name LIKE ? OR strand_course LIKE ?)
            ");
            $search_term = "%{$search}%";
            $stmt->execute([$school_year, $search_term, $search_term]);
        } else {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count 
                FROM sections 
                WHERE school_year = ?
            ");
            $stmt->execute([$school_year]);
        }

        $result = $stmt->fetch();
        return $result['count'];
    }
}
