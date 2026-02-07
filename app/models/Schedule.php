<?php

require_once __DIR__ . '/../../config/db_connection.php';

class Schedule
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // get todays schedule for a specific teacher
    public function getTodaySchedule($teacher_id, $school_year = '2025-2026', $semester = 'First')
    {
        $today = date('l'); // gets day name like "Monday"

        $stmt = $this->connection->prepare("
            SELECT 
                cs.schedule_id,
                cs.start_time,
                cs.end_time,
                cs.room,
                cs.day_of_week,
                sub.subject_name,
                sub.subject_code,
                sec.section_name,
                sec.education_level,
                sec.year_level,
                sec.strand_course
            FROM class_schedules cs
            INNER JOIN subjects sub ON cs.subject_id = sub.subject_id
            INNER JOIN sections sec ON cs.section_id = sec.section_id
            WHERE cs.teacher_id = ?
                AND cs.day_of_week = ?
                AND cs.school_year = ?
                AND cs.semester = ?
            ORDER BY cs.start_time ASC
        ");

        $stmt->execute([$teacher_id, $today, $school_year, $semester]);

        return $stmt->fetchAll();
    }

    // get full week schedule for a specific teacher
    public function getWeekSchedule($teacher_id, $school_year = '2025-2026', $semester = 'First')
    {
        $stmt = $this->connection->prepare("
            SELECT 
                cs.schedule_id,
                cs.start_time,
                cs.end_time,
                cs.room,
                cs.day_of_week,
                sub.subject_name,
                sub.subject_code,
                sec.section_name,
                sec.education_level,
                sec.year_level,
                sec.strand_course
            FROM class_schedules cs
            INNER JOIN subjects sub ON cs.subject_id = sub.subject_id
            INNER JOIN sections sec ON cs.section_id = sec.section_id
            WHERE cs.teacher_id = ?
                AND cs.school_year = ?
                AND cs.semester = ?
            ORDER BY 
                FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                cs.start_time ASC
        ");

        $stmt->execute([$teacher_id, $school_year, $semester]);

        return $stmt->fetchAll();
    }

    // get schedule for a specific day
    public function getScheduleByDay($teacher_id, $day_of_week, $school_year = '2025-2026', $semester = 'First')
    {
        $stmt = $this->connection->prepare("
            SELECT 
                cs.schedule_id,
                cs.start_time,
                cs.end_time,
                cs.room,
                cs.day_of_week,
                sub.subject_name,
                sub.subject_code,
                sec.section_name,
                sec.education_level,
                sec.year_level,
                sec.strand_course
            FROM class_schedules cs
            INNER JOIN subjects sub ON cs.subject_id = sub.subject_id
            INNER JOIN sections sec ON cs.section_id = sec.section_id
            WHERE cs.teacher_id = ?
                AND cs.day_of_week = ?
                AND cs.school_year = ?
                AND cs.semester = ?
            ORDER BY cs.start_time ASC
        ");

        $stmt->execute([$teacher_id, $day_of_week, $school_year, $semester]);

        return $stmt->fetchAll();
    }
}
