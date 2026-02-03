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

    // assign teacher to multiple subjects for one section
    public function assignTeacherToSection($teacher_id, $subject_ids, $section_id, $year_level, $school_year)
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare("
                INSERT INTO teacher_subject_assignments 
                (teacher_id, subject_id, section_id, year_level, school_year, assigned_date, status)
                VALUES (?, ?, ?, ?, ?, CURDATE(), 'active')
            ");

            $inserted = 0;

            foreach ($subject_ids as $subject_id) {
                $subject_id = (int) $subject_id;

                if ($this->inactiveAssignmentExists($teacher_id, $subject_id, $section_id, $school_year)) {
                    $this->reactivateSingleAssignment($teacher_id, $subject_id, $section_id, $school_year);
                    $inserted++;
                } elseif (!$this->assignmentExists($teacher_id, $subject_id, $section_id, $school_year)) {
                    $stmt->execute([$teacher_id, $subject_id, $section_id, $year_level, $school_year]);
                    $inserted++;
                }
            }

            $this->connection->commit();
            return $inserted > 0 ? true : null;
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log("Assignment error: " . $e->getMessage());
            return false;
        }
    }

    // reassign: diffs current vs new, deactivates removed, inserts/reactivates added
    public function reassignTeacherSubjects($teacher_id, $section_id, $new_subject_ids, $year_level, $school_year)
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare("
                SELECT subject_id FROM teacher_subject_assignments
                WHERE teacher_id = ? AND section_id = ? AND school_year = ? AND status = 'active'
            ");
            $stmt->execute([$teacher_id, $section_id, $school_year]);
            $current_rows = $stmt->fetchAll();
            $current_subject_ids = array_column($current_rows, 'subject_id');

            $new_subject_ids = array_map('intval', $new_subject_ids);

            $to_remove = array_diff($current_subject_ids, $new_subject_ids);
            $to_add    = array_diff($new_subject_ids, $current_subject_ids);

            if (!empty($to_remove)) {
                $placeholders = implode(',', array_fill(0, count($to_remove), '?'));
                $deactivate_stmt = $this->connection->prepare("
                    UPDATE teacher_subject_assignments
                    SET status = 'inactive'
                    WHERE teacher_id = ? AND section_id = ? AND school_year = ? AND subject_id IN ($placeholders)
                ");
                $params = array_merge([$teacher_id, $section_id, $school_year], array_values($to_remove));
                $deactivate_stmt->execute($params);
            }

            if (!empty($to_add)) {
                $insert_stmt = $this->connection->prepare("
                    INSERT INTO teacher_subject_assignments 
                    (teacher_id, subject_id, section_id, year_level, school_year, assigned_date, status)
                    VALUES (?, ?, ?, ?, ?, CURDATE(), 'active')
                ");

                foreach ($to_add as $subject_id) {
                    if ($this->inactiveAssignmentExists($teacher_id, $subject_id, $section_id, $school_year)) {
                        $this->reactivateSingleAssignment($teacher_id, $subject_id, $section_id, $school_year);
                    } else {
                        $insert_stmt->execute([$teacher_id, $subject_id, $section_id, $year_level, $school_year]);
                    }
                }
            }

            $this->connection->commit();
            return true;
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log("Reassignment error: " . $e->getMessage());
            return false;
        }
    }

    // get all assignments grouped by teacher and section
    // $status param lets you fetch 'active' or 'inactive' without duplicating the query
    public function getAllAssignmentsGrouped($status = 'active')
    {
        $stmt = $this->connection->prepare("
            SELECT 
                tsa.teacher_id,
                tsa.section_id,
                tsa.year_level,
                tsa.school_year,
                tsa.status,
                CONCAT(u.first_name, ' ', IF(u.middle_name IS NOT NULL, CONCAT(u.middle_name, ' '), ''), u.last_name) as teacher_name,
                sec.section_name,
                GROUP_CONCAT(s.subject_name ORDER BY s.subject_name SEPARATOR ', ') as subjects,
                GROUP_CONCAT(s.subject_id ORDER BY s.subject_name SEPARATOR ',') as subject_ids,
                COUNT(tsa.subject_id) as subject_count
            FROM teacher_subject_assignments tsa
            INNER JOIN users u ON tsa.teacher_id = u.id
            INNER JOIN subjects s ON tsa.subject_id = s.subject_id
            INNER JOIN sections sec ON tsa.section_id = sec.section_id
            WHERE tsa.status = ?
            GROUP BY tsa.teacher_id, tsa.section_id, tsa.year_level, tsa.school_year, tsa.status
            ORDER BY u.last_name ASC, sec.section_name ASC
        ");

        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    // check if an ACTIVE assignment already exists
    public function assignmentExists($teacher_id, $subject_id, $section_id, $school_year)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count
            FROM teacher_subject_assignments
            WHERE teacher_id = ? AND subject_id = ? AND section_id = ? AND school_year = ? AND status = 'active'
        ");

        $stmt->execute([$teacher_id, $subject_id, $section_id, $school_year]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    // check if an INACTIVE assignment exists (blocks INSERT due to unique key)
    public function inactiveAssignmentExists($teacher_id, $subject_id, $section_id, $school_year)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count
            FROM teacher_subject_assignments
            WHERE teacher_id = ? AND subject_id = ? AND section_id = ? AND school_year = ? AND status = 'inactive'
        ");

        $stmt->execute([$teacher_id, $subject_id, $section_id, $school_year]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    // reactivate a single specific assignment row
    private function reactivateSingleAssignment($teacher_id, $subject_id, $section_id, $school_year)
    {
        $stmt = $this->connection->prepare("
            UPDATE teacher_subject_assignments
            SET status = 'active', assigned_date = CURDATE()
            WHERE teacher_id = ? AND subject_id = ? AND section_id = ? AND school_year = ? AND status = 'inactive'
        ");

        return $stmt->execute([$teacher_id, $subject_id, $section_id, $school_year]);
    }

    // soft delete- set all assignments for a teacher-section to inactive
    public function removeTeacherSectionAssignments($teacher_id, $section_id, $school_year)
    {
        $stmt = $this->connection->prepare("
            UPDATE teacher_subject_assignments
            SET status = 'inactive'
            WHERE teacher_id = ? AND section_id = ? AND school_year = ?
        ");

        return $stmt->execute([$teacher_id, $section_id, $school_year]);
    }

    // restore- set all assignments for a teacher-section back to active
    public function reactivateAssignments($teacher_id, $section_id, $school_year)
    {
        $stmt = $this->connection->prepare("
            UPDATE teacher_subject_assignments
            SET status = 'active'
            WHERE teacher_id = ? AND section_id = ? AND school_year = ?
        ");

        return $stmt->execute([$teacher_id, $section_id, $school_year]);
    }

    public function getGroupedAssignmentByTeacherSection($teacher_id, $section_id, $school_year, $status = 'active')
    {
        $stmt = $this->connection->prepare("
            SELECT 
                tsa.teacher_id,
                tsa.section_id,
                tsa.year_level,
                tsa.school_year,
                tsa.status,
                CONCAT(u.first_name, ' ', IF(u.middle_name IS NOT NULL, CONCAT(u.middle_name, ' '), ''), u.last_name) as teacher_name,
                sec.section_name,
                GROUP_CONCAT(s.subject_name ORDER BY s.subject_name SEPARATOR ', ') as subjects,
                GROUP_CONCAT(s.subject_id ORDER BY s.subject_name SEPARATOR ',') as subject_ids,
                COUNT(tsa.subject_id) as subject_count
            FROM teacher_subject_assignments tsa
            INNER JOIN users u ON tsa.teacher_id = u.id
            INNER JOIN subjects s ON tsa.subject_id = s.subject_id
            INNER JOIN sections sec ON tsa.section_id = sec.section_id
            WHERE tsa.teacher_id = ? 
                AND tsa.section_id = ? 
                AND tsa.school_year = ?
                AND tsa.status = ?
            GROUP BY tsa.teacher_id, tsa.section_id, tsa.year_level, tsa.school_year, tsa.status
        ");

        $stmt->execute([$teacher_id, $section_id, $school_year, $status]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
