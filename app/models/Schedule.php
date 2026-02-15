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
        $today = date('l');

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

    // get all schedules for admin view (with filter options)
    public function getAllSchedules($school_year = '2025-2026', $semester = 'First', $status = 'active')
    {
        $stmt = $this->connection->prepare("
            SELECT 
                cs.schedule_id,
                cs.teacher_id,
                cs.subject_id,
                cs.section_id,
                cs.day_of_week,
                cs.start_time,
                cs.end_time,
                cs.room,
                cs.school_year,
                cs.semester,
                cs.status,
                CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                sub.subject_code,
                sub.subject_name,
                sec.section_name,
                sec.education_level,
                sec.year_level,
                sec.strand_course
            FROM class_schedules cs
            INNER JOIN users u ON cs.teacher_id = u.id
            INNER JOIN subjects sub ON cs.subject_id = sub.subject_id
            INNER JOIN sections sec ON cs.section_id = sec.section_id
            WHERE cs.school_year = ?
                AND cs.semester = ?
                AND cs.status = ?
            ORDER BY 
                u.last_name ASC,
                u.first_name ASC,
                FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                cs.start_time ASC
        ");

        $stmt->execute([$school_year, $semester, $status]);
        return $stmt->fetchAll();
    }

    // get schedule by id
    public function getScheduleById($schedule_id)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                cs.schedule_id,
                cs.teacher_id,
                cs.subject_id,
                cs.section_id,
                cs.day_of_week,
                cs.start_time,
                cs.end_time,
                cs.room,
                cs.school_year,
                cs.semester,
                cs.status,
                CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                sub.subject_code,
                sub.subject_name,
                sec.section_name,
                sec.education_level,
                sec.year_level,
                sec.strand_course
            FROM class_schedules cs
            INNER JOIN users u ON cs.teacher_id = u.id
            INNER JOIN subjects sub ON cs.subject_id = sub.subject_id
            INNER JOIN sections sec ON cs.section_id = sec.section_id
            WHERE cs.schedule_id = ?
        ");

        $stmt->execute([$schedule_id]);
        return $stmt->fetch();
    }

    // create new schedule
    public function create($teacher_id, $subject_id, $section_id, $day_of_week, $start_time, $end_time, $room, $school_year, $semester, $status = 'active')
    {
        $stmt = $this->connection->prepare("
            INSERT INTO class_schedules 
            (teacher_id, subject_id, section_id, day_of_week, start_time, end_time, room, school_year, semester, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([$teacher_id, $subject_id, $section_id, $day_of_week, $start_time, $end_time, $room, $school_year, $semester, $status]);
    }

    // update existing schedule
    public function update($schedule_id, $teacher_id, $subject_id, $section_id, $day_of_week, $start_time, $end_time, $room, $school_year, $semester, $status)
    {
        $stmt = $this->connection->prepare("
            UPDATE class_schedules
            SET teacher_id = ?, subject_id = ?, section_id = ?, day_of_week = ?, 
                start_time = ?, end_time = ?, room = ?, school_year = ?, semester = ?, status = ?
            WHERE schedule_id = ?
        ");

        return $stmt->execute([$teacher_id, $subject_id, $section_id, $day_of_week, $start_time, $end_time, $room, $school_year, $semester, $status, $schedule_id]);
    }

    // delete schedule (hard delete)
    public function delete($schedule_id)
    {
        $stmt = $this->connection->prepare("DELETE FROM class_schedules WHERE schedule_id = ?");
        return $stmt->execute([$schedule_id]);
    }

    // update status only (activate/deactivate)
    public function updateStatus($schedule_id, $status)
    {
        $stmt = $this->connection->prepare("UPDATE class_schedules SET status = ? WHERE schedule_id = ?");
        return $stmt->execute([$status, $schedule_id]);
    }

    // check if teacher has conflicting schedule
    public function checkTeacherConflict($teacher_id, $day_of_week, $start_time, $end_time, $school_year, $semester, $exclude_schedule_id = null)
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM class_schedules
            WHERE teacher_id = ?
                AND day_of_week = ?
                AND school_year = ?
                AND semester = ?
                AND status = 'active'
                AND (
                    (start_time < ? AND end_time > ?) OR
                    (start_time < ? AND end_time > ?) OR
                    (start_time >= ? AND end_time <= ?)
                )
        ";

        $params = [$teacher_id, $day_of_week, $school_year, $semester, $end_time, $start_time, $end_time, $end_time, $start_time, $end_time];

        if ($exclude_schedule_id !== null) {
            $sql .= " AND schedule_id != ?";
            $params[] = $exclude_schedule_id;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    // check if section has conflicting schedule
    public function checkSectionConflict($section_id, $day_of_week, $start_time, $end_time, $school_year, $semester, $exclude_schedule_id = null)
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM class_schedules
            WHERE section_id = ?
                AND day_of_week = ?
                AND school_year = ?
                AND semester = ?
                AND status = 'active'
                AND (
                    (start_time < ? AND end_time > ?) OR
                    (start_time < ? AND end_time > ?) OR
                    (start_time >= ? AND end_time <= ?)
                )
        ";

        $params = [$section_id, $day_of_week, $school_year, $semester, $end_time, $start_time, $end_time, $end_time, $start_time, $end_time];

        if ($exclude_schedule_id !== null) {
            $sql .= " AND schedule_id != ?";
            $params[] = $exclude_schedule_id;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    // check if room has conflicting schedule (only if room is not null)
    public function checkRoomConflict($room, $day_of_week, $start_time, $end_time, $school_year, $semester, $exclude_schedule_id = null)
    {
        if (empty($room)) {
            return false;
        }

        $sql = "
            SELECT COUNT(*) as count
            FROM class_schedules
            WHERE room = ?
                AND day_of_week = ?
                AND school_year = ?
                AND semester = ?
                AND status = 'active'
                AND (
                    (start_time < ? AND end_time > ?) OR
                    (start_time < ? AND end_time > ?) OR
                    (start_time >= ? AND end_time <= ?)
                )
        ";

        $params = [$room, $day_of_week, $school_year, $semester, $end_time, $start_time, $end_time, $end_time, $start_time, $end_time];

        if ($exclude_schedule_id !== null) {
            $sql .= " AND schedule_id != ?";
            $params[] = $exclude_schedule_id;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    // check all conflicts at once (returns array of conflict types)
    public function checkAllConflicts($teacher_id, $section_id, $room, $day_of_week, $start_time, $end_time, $school_year, $semester, $exclude_schedule_id = null)
    {
        $conflicts = [];

        if ($this->checkTeacherConflict($teacher_id, $day_of_week, $start_time, $end_time, $school_year, $semester, $exclude_schedule_id)) {
            $conflicts[] = 'teacher';
        }

        if ($this->checkSectionConflict($section_id, $day_of_week, $start_time, $end_time, $school_year, $semester, $exclude_schedule_id)) {
            $conflicts[] = 'section';
        }

        if ($this->checkRoomConflict($room, $day_of_week, $start_time, $end_time, $school_year, $semester, $exclude_schedule_id)) {
            $conflicts[] = 'room';
        }

        return $conflicts;
    }

    // get conflicting schedules details (for error messages)
    public function getConflictingSchedules($teacher_id, $section_id, $room, $day_of_week, $start_time, $end_time, $school_year, $semester, $exclude_schedule_id = null)
    {
        $conflicts = [];

        // teacher conflicts
        $sql = "
            SELECT 
                cs.schedule_id,
                cs.start_time,
                cs.end_time,
                cs.room,
                CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                sub.subject_name,
                sec.section_name,
                'teacher' as conflict_type
            FROM class_schedules cs
            INNER JOIN users u ON cs.teacher_id = u.id
            INNER JOIN subjects sub ON cs.subject_id = sub.subject_id
            INNER JOIN sections sec ON cs.section_id = sec.section_id
            WHERE cs.teacher_id = ?
                AND cs.day_of_week = ?
                AND cs.school_year = ?
                AND cs.semester = ?
                AND cs.status = 'active'
                AND (
                    (cs.start_time < ? AND cs.end_time > ?) OR
                    (cs.start_time < ? AND cs.end_time > ?) OR
                    (cs.start_time >= ? AND cs.end_time <= ?)
                )
        ";
        $params = [$teacher_id, $day_of_week, $school_year, $semester, $end_time, $start_time, $end_time, $end_time, $start_time, $end_time];

        if ($exclude_schedule_id !== null) {
            $sql .= " AND cs.schedule_id != ?";
            $params[] = $exclude_schedule_id;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $teacher_conflicts = $stmt->fetchAll();

        // section conflicts
        $sql = "
            SELECT 
                cs.schedule_id,
                cs.start_time,
                cs.end_time,
                cs.room,
                CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                sub.subject_name,
                sec.section_name,
                'section' as conflict_type
            FROM class_schedules cs
            INNER JOIN users u ON cs.teacher_id = u.id
            INNER JOIN subjects sub ON cs.subject_id = sub.subject_id
            INNER JOIN sections sec ON cs.section_id = sec.section_id
            WHERE cs.section_id = ?
                AND cs.day_of_week = ?
                AND cs.school_year = ?
                AND cs.semester = ?
                AND cs.status = 'active'
                AND (
                    (cs.start_time < ? AND cs.end_time > ?) OR
                    (cs.start_time < ? AND cs.end_time > ?) OR
                    (cs.start_time >= ? AND cs.end_time <= ?)
                )
        ";
        $params = [$section_id, $day_of_week, $school_year, $semester, $end_time, $start_time, $end_time, $end_time, $start_time, $end_time];

        if ($exclude_schedule_id !== null) {
            $sql .= " AND cs.schedule_id != ?";
            $params[] = $exclude_schedule_id;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $section_conflicts = $stmt->fetchAll();

        // room conflicts
        if (!empty($room)) {
            $sql = "
                SELECT 
                    cs.schedule_id,
                    cs.start_time,
                    cs.end_time,
                    cs.room,
                    CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                    sub.subject_name,
                    sec.section_name,
                    'room' as conflict_type
                FROM class_schedules cs
                INNER JOIN users u ON cs.teacher_id = u.id
                INNER JOIN subjects sub ON cs.subject_id = sub.subject_id
                INNER JOIN sections sec ON cs.section_id = sec.section_id
                WHERE cs.room = ?
                    AND cs.day_of_week = ?
                    AND cs.school_year = ?
                    AND cs.semester = ?
                    AND cs.status = 'active'
                    AND (
                        (cs.start_time < ? AND cs.end_time > ?) OR
                        (cs.start_time < ? AND cs.end_time > ?) OR
                        (cs.start_time >= ? AND cs.end_time <= ?)
                    )
            ";
            $params = [$room, $day_of_week, $school_year, $semester, $end_time, $start_time, $end_time, $end_time, $start_time, $end_time];

            if ($exclude_schedule_id !== null) {
                $sql .= " AND cs.schedule_id != ?";
                $params[] = $exclude_schedule_id;
            }

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $room_conflicts = $stmt->fetchAll();
        } else {
            $room_conflicts = [];
        }

        return array_merge($teacher_conflicts, $section_conflicts, $room_conflicts);
    }
}
