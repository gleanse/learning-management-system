<?php

require_once __DIR__ . '/../../config/db_connection.php';

class ScheduleView
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // get full weekly schedule for a section (student view)
    public function getWeeklyScheduleBySection($section_id, $school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT
                cs.schedule_id,
                cs.day_of_week,
                cs.start_time,
                cs.end_time,
                cs.room,
                sub.subject_code,
                sub.subject_name,
                CONCAT(u.first_name, ' ', u.last_name) AS teacher_name
            FROM class_schedules cs
            INNER JOIN subjects sub ON cs.subject_id = sub.subject_id
            INNER JOIN users u ON cs.teacher_id = u.id
            WHERE cs.section_id = ?
                AND cs.school_year = ?
                AND cs.semester = ?
                AND cs.status = 'active'
            ORDER BY
                FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                cs.start_time ASC
        ");

        $stmt->execute([$section_id, $school_year, $semester]);
        return $stmt->fetchAll();
    }

    // get full weekly schedule for a teacher (teacher view)
    public function getWeeklyScheduleByTeacher($teacher_id, $school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT
                cs.schedule_id,
                cs.day_of_week,
                cs.start_time,
                cs.end_time,
                cs.room,
                sub.subject_code,
                sub.subject_name,
                sec.section_name,
                sec.year_level,
                sec.strand_course
            FROM class_schedules cs
            INNER JOIN subjects sub ON cs.subject_id = sub.subject_id
            INNER JOIN sections sec ON cs.section_id = sec.section_id
            WHERE cs.teacher_id = ?
                AND cs.school_year = ?
                AND cs.semester = ?
                AND cs.status = 'active'
            ORDER BY
                FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                cs.start_time ASC
        ");

        $stmt->execute([$teacher_id, $school_year, $semester]);
        return $stmt->fetchAll();
    }
}
