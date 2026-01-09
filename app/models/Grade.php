<?php

require_once __DIR__ . '/../../config/db_connection.php';

class Grade
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // get existing grade if it exists
    public function getGrade($student_id, $subject_id, $grading_period, $semester, $school_year)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                grade_id,
                grade_value,
                remarks
            FROM grades
            WHERE student_id = ? 
                AND subject_id = ? 
                AND grading_period = ? 
                AND semester = ? 
                AND school_year = ?
        ");
        
        $stmt->execute([$student_id, $subject_id, $grading_period, $semester, $school_year]);
        
        return $stmt->fetch();
    }

    // save grade (insert or update)
    public function saveGrade($grade_data)
    {
        // check if grade already exists
        $existing_grade = $this->getGrade(
            $grade_data['student_id'],
            $grade_data['subject_id'],
            $grade_data['grading_period'],
            $grade_data['semester'],
            $grade_data['school_year']
        );

        if ($existing_grade) {
            // update existing grade
            $stmt = $this->connection->prepare("
                UPDATE grades 
                SET grade_value = ?,
                    remarks = ?,
                    teacher_id = ?,
                    graded_date = NOW(),
                    updated_at = NOW()
                WHERE grade_id = ?
            ");

            return $stmt->execute([
                $grade_data['grade_value'],
                $grade_data['remarks'],
                $grade_data['teacher_id'],
                $existing_grade['grade_id']
            ]);
        } else {
            // insert new grade
            $stmt = $this->connection->prepare("
                INSERT INTO grades (
                    student_id, 
                    subject_id, 
                    teacher_id, 
                    grading_period, 
                    semester, 
                    grade_value, 
                    remarks, 
                    school_year, 
                    graded_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            return $stmt->execute([
                $grade_data['student_id'],
                $grade_data['subject_id'],
                $grade_data['teacher_id'],
                $grade_data['grading_period'],
                $grade_data['semester'],
                $grade_data['grade_value'],
                $grade_data['remarks'],
                $grade_data['school_year']
            ]);
        }
    }
}