<?php

require_once __DIR__ . '/../models/ScheduleView.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/AcademicPeriod.php';

class ScheduleViewController
{
    private $schedule_view_model;
    private $student_model;
    private $academic_model;

    public function __construct()
    {
        $this->schedule_view_model = new ScheduleView();
        $this->student_model       = new Student();
        $this->academic_model      = new AcademicPeriod();
    }

    // verify student session and return student info
    private function requireStudent()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            header('Location: index.php?page=login');
            exit();
        }

        $student_info = $this->student_model->getStudentInfoByUserId($_SESSION['user_id']);

        if (!$student_info) {
            session_destroy();
            header('Location: index.php?page=login');
            exit();
        }

        return $student_info;
    }

    // verify teacher session
    private function requireTeacher()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
            header('Location: index.php?page=login');
            exit();
        }
    }

    public function showStudentSchedule()
    {
        $student_info = $this->requireStudent();

        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $raw_schedule = [];

        if (!empty($student_info['section_id'])) {
            $raw_schedule = $this->schedule_view_model->getWeeklyScheduleBySection(
                $student_info['section_id'],
                $school_year,
                $semester
            );
        }

        // group by day and format time range for the timetable grid
        $days_order = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $schedule_by_day = array_fill_keys($days_order, []);

        foreach ($raw_schedule as $class) {
            $class['time_range']   = date('g:i A', strtotime($class['start_time']))
                . ' – '
                . date('g:i A', strtotime($class['end_time']));
            $class['room_display'] = !empty($class['room']) ? $class['room'] : 'TBA';

            $day = $class['day_of_week'];
            if (isset($schedule_by_day[$day])) {
                $schedule_by_day[$day][] = $class;
            }
        }

        require __DIR__ . '/../views/student/schedule.php';
    }

    public function showTeacherSchedule()
    {
        $this->requireTeacher();

        $teacher_id = $_SESSION['user_id'];

        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $raw_schedule = $this->schedule_view_model->getWeeklyScheduleByTeacher(
            $teacher_id,
            $school_year,
            $semester
        );

        // group by day and format time range for the timetable grid
        $days_order = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $schedule_by_day = array_fill_keys($days_order, []);

        foreach ($raw_schedule as $class) {
            $class['time_range']   = date('g:i A', strtotime($class['start_time']))
                . ' – '
                . date('g:i A', strtotime($class['end_time']));
            $class['room_display'] = !empty($class['room']) ? $class['room'] : 'TBA';

            $day = $class['day_of_week'];
            if (isset($schedule_by_day[$day])) {
                $schedule_by_day[$day][] = $class;
            }
        }

        require __DIR__ . '/../views/teacher/schedule.php';
    }
}
