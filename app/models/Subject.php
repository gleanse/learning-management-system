<?php

require_once __DIR__ . '/../../config/db_connection.php';

class Subject
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // get subject by id
    public function getById($subject_id)
    {
        $stmt = $this->connection->prepare("
            SELECT subject_id, subject_code, subject_name, description
            FROM subjects
            WHERE subject_id = ?
        ");

        $stmt->execute([$subject_id]);

        return $stmt->fetch();
    }

    // get all subjects
    public function getAll()
    {
        $stmt = $this->connection->prepare("
            SELECT subject_id, subject_code, subject_name, description
            FROM subjects
            ORDER BY subject_name ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll();
    }

    // create new subject
    public function create($subject_code, $subject_name, $description = null)
    {
        $stmt = $this->connection->prepare("
            INSERT INTO subjects (subject_code, subject_name, description)
            VALUES (?, ?, ?)
        ");

        return $stmt->execute([$subject_code, $subject_name, $description]);
    }

    // update existing subject
    public function update($subject_id, $subject_code, $subject_name, $description = null)
    {
        $stmt = $this->connection->prepare("
            UPDATE subjects
            SET subject_code = ?, subject_name = ?, description = ?
            WHERE subject_id = ?
        ");

        return $stmt->execute([$subject_code, $subject_name, $description, $subject_id]);
    }

    // delete subject
    public function delete($subject_id)
    {
        $stmt = $this->connection->prepare("
            DELETE FROM subjects
            WHERE subject_id = ?
        ");

        return $stmt->execute([$subject_id]);
    }

    // check if subject code already exists (case-insensitive)
    public function isSubjectCodeExists($subject_code, $exclude_subject_id = null)
    {
        if ($exclude_subject_id) {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count
                FROM subjects
                WHERE LOWER(subject_code) = LOWER(?) AND subject_id != ?
            ");
            $stmt->execute([$subject_code, $exclude_subject_id]);
        } else {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count
                FROM subjects
                WHERE LOWER(subject_code) = LOWER(?)
            ");
            $stmt->execute([$subject_code]);
        }

        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    // check if subject name already exists (case-insensitive)
    public function isSubjectNameExists($subject_name, $exclude_subject_id = null)
    {
        if ($exclude_subject_id) {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count
                FROM subjects
                WHERE LOWER(subject_name) = LOWER(?) AND subject_id != ?
            ");
            $stmt->execute([$subject_name, $exclude_subject_id]);
        } else {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count
                FROM subjects
                WHERE LOWER(subject_name) = LOWER(?)
            ");
            $stmt->execute([$subject_name]);
        }

        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    // check if subject is being used in enrollments
    public function isSubjectInUse($subject_id)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count
            FROM student_subject_enrollments
            WHERE subject_id = ?
        ");

        $stmt->execute([$subject_id]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    // get subjects with pagination
    public function getWithPagination($limit, $offset, $search = '')
    {
        if (!empty($search)) {
            $stmt = $this->connection->prepare("
                SELECT subject_id, subject_code, subject_name, description
                FROM subjects
                WHERE subject_code LIKE :search OR subject_name LIKE :search
                ORDER BY subject_name ASC
                LIMIT :limit OFFSET :offset
            ");
            $search_term = "%{$search}%";
            $stmt->bindValue(':search', $search_term, PDO::PARAM_STR);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->connection->prepare("
                SELECT subject_id, subject_code, subject_name, description
                FROM subjects
                ORDER BY subject_name ASC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    // get total count for pagination
    public function getTotalCount($search = '')
    {
        if (!empty($search)) {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count
                FROM subjects
                WHERE subject_code LIKE ? OR subject_name LIKE ?
            ");
            $search_term = "%{$search}%";
            $stmt->execute([$search_term, $search_term]);
        } else {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count
                FROM subjects
            ");
            $stmt->execute();
        }

        $result = $stmt->fetch();
        return $result['count'];
    }
}
