<?php

require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Schedule.php';

class ScheduleController
{
    private $teacher_model;
    private $schedule_model;

    public function __construct()
    {
        $this->teacher_model = new Teacher();
        $this->schedule_model = new Schedule();
    }

    public function showTeacherSchedule()
    {
        // check if teacher is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
            header('Location: index.php?page=login');
            exit();
        }

        $teacher_id = $_SESSION['user_id'];
        $school_year = $_GET['school_year'] ?? '2025-2026';
        $semester = $_GET['semester'] ?? 'First';
        
        // get todays date and schedule
        $current_date = date('l, F j, Y'); // example output "Monday, January 1, 2026"
        $today_schedule = $this->schedule_model->getTodaySchedule($teacher_id, $school_year, $semester);

        // format schedule data for display
        foreach ($today_schedule as &$schedule) {
            $schedule['time_range'] = date('g:i A', strtotime($schedule['start_time'])) . ' - ' . 
                                      date('g:i A', strtotime($schedule['end_time']));
            $schedule['room_display'] = !empty($schedule['room']) ? $schedule['room'] : 'TBA';
        }

        require __DIR__ . '/../views/teacher/teacher_schedule.php';
    }

    public function showWeekSchedule()
    {
        // check if teacher is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
            header('Location: index.php?page=login');
            exit();
        }

        $teacher_id = $_SESSION['user_id'];
        $school_year = $_GET['school_year'] ?? '2025-2026';
        $semester = $_GET['semester'] ?? 'First';
        
        // get full week schedule
        $week_schedule = $this->schedule_model->getWeekSchedule($teacher_id, $school_year, $semester);

        // group schedules by day
        $schedule_by_day = [];
        foreach ($week_schedule as $schedule) {
            $day = $schedule['day_of_week'];
            if (!isset($schedule_by_day[$day])) {
                $schedule_by_day[$day] = [];
            }
            
            $schedule['time_range'] = date('g:i A', strtotime($schedule['start_time'])) . ' - ' . 
                                      date('g:i A', strtotime($schedule['end_time']));
            $schedule['room_display'] = !empty($schedule['room']) ? $schedule['room'] : 'TBA';
            
            $schedule_by_day[$day][] = $schedule;
        }

        require __DIR__ . '/../views/teacher/week_schedule.php';
    }
}