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
}