<?php
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/AcademicPeriod.php';

class Report
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // enrollment reports
    public function getDailyEnrollments($date = null)
    {
        $date = $date ?? date('Y-m-d');

        $stmt = $this->connection->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_enrollments,
                SUM(CASE WHEN education_level = 'senior_high' THEN 1 ELSE 0 END) as senior_high_count,
                SUM(CASE WHEN education_level = 'college' THEN 1 ELSE 0 END) as college_count,
                COUNT(DISTINCT strand_course) as strands_count
            FROM students
            WHERE DATE(created_at) = ?
            GROUP BY DATE(created_at)
        ");
        $stmt->execute([$date]);
        return $stmt->fetch();
    }

    public function getWeeklyEnrollments($year = null, $week = null)
    {
        $year = $year ?? date('Y');
        $week = $week ?? date('W');

        $stmt = $this->connection->prepare("
            SELECT 
                WEEK(created_at) as week_number,
                YEAR(created_at) as year,
                COUNT(*) as total_enrollments,
                COUNT(DISTINCT strand_course) as strands_count,
                SUM(CASE WHEN enrollment_status = 'active' THEN 1 ELSE 0 END) as active_count
            FROM students
            WHERE YEAR(created_at) = ? AND WEEK(created_at) = ?
            GROUP BY YEAR(created_at), WEEK(created_at)
        ");
        $stmt->execute([$year, $week]);
        return $stmt->fetch();
    }

    public function getMonthlyEnrollments($year = null, $month = null)
    {
        $year = $year ?? date('Y');
        $month = $month ?? date('m');

        $stmt = $this->connection->prepare("
            SELECT 
                MONTH(created_at) as month,
                YEAR(created_at) as year,
                COUNT(*) as total_enrollments,
                COUNT(DISTINCT section_id) as sections_used,
                SUM(CASE WHEN education_level = 'senior_high' THEN 1 ELSE 0 END) as senior_high,
                SUM(CASE WHEN education_level = 'college' THEN 1 ELSE 0 END) as college,
                SUM(CASE WHEN enrollment_status = 'active' THEN 1 ELSE 0 END) as active
            FROM students
            WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?
            GROUP BY YEAR(created_at), MONTH(created_at)
        ");
        $stmt->execute([$year, $month]);
        return $stmt->fetch();
    }

    public function getYearlyEnrollments($year = null)
    {
        $year = $year ?? date('Y');

        $stmt = $this->connection->prepare("
            SELECT 
                YEAR(created_at) as year,
                COUNT(*) as total_enrollments,
                COUNT(DISTINCT MONTH(created_at)) as months_active,
                COUNT(DISTINCT strand_course) as strands_offered,
                SUM(CASE WHEN enrollment_status = 'active' THEN 1 ELSE 0 END) as currently_active
            FROM students
            WHERE YEAR(created_at) = ?
            GROUP BY YEAR(created_at)
        ");
        $stmt->execute([$year]);
        return $stmt->fetch();
    }

    public function getEnrollmentTrends($start_date, $end_date, $interval = 'day')
    {
        $interval_format = [
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m'
        ];

        $format = $interval_format[$interval] ?? '%Y-%m-%d';

        $stmt = $this->connection->prepare("
            SELECT 
                DATE_FORMAT(created_at, ?) as period,
                COUNT(*) as enrollment_count,
                SUM(CASE WHEN education_level = 'senior_high' THEN 1 ELSE 0 END) as senior_high,
                SUM(CASE WHEN education_level = 'college' THEN 1 ELSE 0 END) as college
            FROM students
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(created_at, ?)
            ORDER BY period ASC
        ");
        $stmt->execute([$format, $start_date, $end_date, $format]);
        return $stmt->fetchAll();
    }

    // payment reports
    public function getDailyPayments($date = null)
    {
        $date = $date ?? date('Y-m-d');

        $stmt = $this->connection->prepare("
            SELECT 
                DATE(pt.created_at) as date,
                COUNT(DISTINCT pt.transaction_id) as transaction_count,
                COUNT(DISTINCT pt.payment_id) as payment_count,
                SUM(pt.amount_paid) as total_collected,
                AVG(pt.amount_paid) as average_payment
            FROM payment_transactions pt
            WHERE DATE(pt.created_at) = ?
            GROUP BY DATE(pt.created_at)
        ");
        $stmt->execute([$date]);
        return $stmt->fetch();
    }

    public function getWeeklyPayments($year = null, $week = null)
    {
        $year = $year ?? date('Y');
        $week = $week ?? date('W');

        $stmt = $this->connection->prepare("
            SELECT 
                WEEK(pt.created_at) as week_number,
                YEAR(pt.created_at) as year,
                COUNT(*) as transaction_count,
                SUM(pt.amount_paid) as total_collected,
                SUM(CASE WHEN ep.status = 'paid' THEN 1 ELSE 0 END) as fully_paid_count,
                SUM(CASE WHEN ep.status = 'partial' THEN 1 ELSE 0 END) as partial_count
            FROM payment_transactions pt
            INNER JOIN enrollment_payments ep ON pt.payment_id = ep.payment_id
            WHERE YEAR(pt.created_at) = ? AND WEEK(pt.created_at) = ?
            GROUP BY YEAR(pt.created_at), WEEK(pt.created_at)
        ");
        $stmt->execute([$year, $week]);
        return $stmt->fetch();
    }

    public function getMonthlyPayments($year = null, $month = null)
    {
        $year = $year ?? date('Y');
        $month = $month ?? date('m');

        $stmt = $this->connection->prepare("
            SELECT 
                MONTH(pt.created_at) as month,
                YEAR(pt.created_at) as year,
                COUNT(*) as total_transactions,
                SUM(pt.amount_paid) as revenue,
                COUNT(DISTINCT ep.student_id) as paying_students
            FROM payment_transactions pt
            INNER JOIN enrollment_payments ep ON pt.payment_id = ep.payment_id
            WHERE YEAR(pt.created_at) = ? AND MONTH(pt.created_at) = ?
            GROUP BY YEAR(pt.created_at), MONTH(pt.created_at)
        ");
        $stmt->execute([$year, $month]);
        return $stmt->fetch();
    }

    public function getYearlyPayments($year = null)
    {
        $year = $year ?? date('Y');

        $stmt = $this->connection->prepare("
            SELECT 
                YEAR(pt.created_at) as year,
                COUNT(*) as total_transactions,
                SUM(pt.amount_paid) as total_revenue,
                COUNT(DISTINCT MONTH(pt.created_at)) as months_with_payments,
                AVG(monthly_avg.avg_monthly) as avg_monthly_revenue
            FROM payment_transactions pt
            CROSS JOIN (
                SELECT SUM(amount_paid) / 12 as avg_monthly
                FROM payment_transactions
                WHERE YEAR(created_at) = ?
            ) monthly_avg
            WHERE YEAR(pt.created_at) = ?
            GROUP BY YEAR(pt.created_at)
        ");
        $stmt->execute([$year, $year]);
        return $stmt->fetch();
    }

    public function getPaymentTrends($start_date, $end_date, $interval = 'day')
    {
        $interval_format = [
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m'
        ];

        $format = $interval_format[$interval] ?? '%Y-%m-%d';

        $stmt = $this->connection->prepare("
            SELECT 
                DATE_FORMAT(pt.created_at, ?) as period,
                COUNT(*) as transaction_count,
                SUM(pt.amount_paid) as total_collected,
                COUNT(DISTINCT pt.payment_id) as payments_processed
            FROM payment_transactions pt
            WHERE DATE(pt.created_at) BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(pt.created_at, ?)
            ORDER BY period ASC
        ");
        $stmt->execute([$format, $start_date, $end_date, $format]);
        return $stmt->fetchAll();
    }

    // grade submission reports
    public function getGradeSubmissionRate($school_year = null, $semester = null)
    {
        $period = (new AcademicPeriod())->getCurrentPeriod();
        $school_year = $school_year ?? $period['school_year'];
        $semester = $semester ?? $period['semester'];

        $stmt = $this->connection->prepare("
            SELECT 
                tsa.teacher_id,
                CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                COUNT(DISTINCT tsa.subject_id) as subjects_handled,
                COUNT(DISTINCT sse.student_id) as total_students,
                COUNT(DISTINCT g.grade_id) as grades_submitted,
                (COUNT(DISTINCT g.grade_id) / (COUNT(DISTINCT sse.student_id) * 4)) * 100 as submission_rate
            FROM teacher_subject_assignments tsa
            INNER JOIN users u ON tsa.teacher_id = u.id
            INNER JOIN student_subject_enrollments sse 
                ON sse.subject_id = tsa.subject_id 
                AND sse.school_year = tsa.school_year 
                AND sse.semester = tsa.semester
            LEFT JOIN grades g 
                ON g.teacher_id = tsa.teacher_id 
                AND g.subject_id = tsa.subject_id 
                AND g.school_year = tsa.school_year 
                AND g.semester = tsa.semester
            WHERE tsa.school_year = ? AND tsa.semester = ? AND tsa.status = 'active'
            GROUP BY tsa.teacher_id
            ORDER BY submission_rate DESC
        ");
        $stmt->execute([$school_year, $semester]);
        return $stmt->fetchAll();
    }

    public function getGradingProgress($school_year = null, $semester = null)
    {
        $period = (new AcademicPeriod())->getCurrentPeriod();
        $school_year = $school_year ?? $period['school_year'];
        $semester = $semester ?? $period['semester'];

        $stmt = $this->connection->prepare("
            SELECT 
                gp.grading_period,
                gp.deadline_date,
                gp.is_locked,
                COUNT(DISTINCT g.grade_id) as grades_encoded,
                COUNT(DISTINCT sse.student_id) as expected_grades,
                (COUNT(DISTINCT g.grade_id) / (COUNT(DISTINCT sse.student_id) * COUNT(DISTINCT tsa.teacher_id))) * 100 as completion_rate
            FROM grading_periods gp
            CROSS JOIN student_subject_enrollments sse
            CROSS JOIN teacher_subject_assignments tsa
            LEFT JOIN grades g 
                ON g.grading_period = gp.grading_period
                AND g.school_year = gp.school_year
                AND g.semester = gp.semester
            WHERE gp.school_year = ? AND gp.semester = ?
            GROUP BY gp.grading_period
            ORDER BY FIELD(gp.grading_period, 'Prelim', 'Midterm', 'Prefinal', 'Final')
        ");
        $stmt->execute([$school_year, $semester]);
        return $stmt->fetchAll();
    }

    // section utilization report
    public function getSectionUtilization($school_year = null)
    {
        $school_year = $school_year ?? (new AcademicPeriod())->getCurrentPeriod()['school_year'];

        $stmt = $this->connection->prepare("
            SELECT 
                sec.section_id,
                sec.section_name,
                sec.education_level,
                sec.year_level,
                sec.strand_course,
                sec.max_capacity,
                COUNT(DISTINCT s.student_id) as enrolled_students,
                (COUNT(DISTINCT s.student_id) / sec.max_capacity) * 100 as utilization_rate,
                COUNT(DISTINCT tsa.teacher_id) as teachers_assigned,
                COUNT(DISTINCT tsa.subject_id) as subjects_offered
            FROM sections sec
            LEFT JOIN students s ON sec.section_id = s.section_id AND s.enrollment_status = 'active'
            LEFT JOIN teacher_subject_assignments tsa ON sec.section_id = tsa.section_id AND tsa.status = 'active'
            WHERE sec.school_year = ?
            GROUP BY sec.section_id
            HAVING enrolled_students > 0 OR teachers_assigned > 0
            ORDER BY utilization_rate DESC
        ");
        $stmt->execute([$school_year]);
        return $stmt->fetchAll();
    }

    // teacher workload report
    public function getTeacherWorkload($school_year = null, $semester = null)
    {
        $period = (new AcademicPeriod())->getCurrentPeriod();
        $school_year = $school_year ?? $period['school_year'];
        $semester = $semester ?? $period['semester'];

        $stmt = $this->connection->prepare("
            SELECT 
                u.id as teacher_id,
                CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                COUNT(DISTINCT tsa.subject_id) as subjects_taught,
                COUNT(DISTINCT tsa.section_id) as sections_handled,
                COUNT(DISTINCT sse.student_id) as total_students
            FROM teacher_subject_assignments tsa
            INNER JOIN users u ON tsa.teacher_id = u.id
            LEFT JOIN student_subject_enrollments sse 
                ON sse.subject_id = tsa.subject_id 
                AND sse.school_year = tsa.school_year 
                AND sse.semester = tsa.semester
            WHERE tsa.school_year = ? AND tsa.semester = ? AND tsa.status = 'active'
            GROUP BY u.id
            ORDER BY total_students DESC
        ");
        $stmt->execute([$school_year, $semester]);
        return $stmt->fetchAll();
    }

    // student performance summary
    public function getStudentPerformanceSummary($school_year = null, $semester = null)
    {
        $period = (new AcademicPeriod())->getCurrentPeriod();
        $school_year = $school_year ?? $period['school_year'];
        $semester = $semester ?? $period['semester'];

        $stmt = $this->connection->prepare("
            SELECT 
                s.education_level,
                s.year_level,
                s.strand_course,
                COUNT(DISTINCT s.student_id) as total_students,
                AVG(CASE WHEN g.grading_period = 'Prelim' THEN g.grade_value END) as avg_prelim,
                AVG(CASE WHEN g.grading_period = 'Midterm' THEN g.grade_value END) as avg_midterm,
                AVG(CASE WHEN g.grading_period = 'Prefinal' THEN g.grade_value END) as avg_prefinal,
                AVG(CASE WHEN g.grading_period = 'Final' THEN g.grade_value END) as avg_final,
                COUNT(DISTINCT CASE WHEN g.grade_value < 75 THEN g.student_id END) as students_at_risk,
                COUNT(DISTINCT CASE WHEN g.grade_value >= 90 THEN g.student_id END) as students_excellent
            FROM students s
            LEFT JOIN student_subject_enrollments sse 
                ON s.student_id = sse.student_id 
                AND sse.school_year = ? 
                AND sse.semester = ?
            LEFT JOIN grades g 
                ON g.student_id = s.student_id 
                AND g.school_year = sse.school_year 
                AND g.semester = sse.semester
            WHERE s.enrollment_status = 'active'
            GROUP BY s.education_level, s.year_level, s.strand_course
            ORDER BY s.education_level DESC, s.year_level ASC
        ");
        $stmt->execute([$school_year, $semester]);
        return $stmt->fetchAll();
    }

    // summary dashboard stats
    public function getSummaryStats($school_year = null)
    {
        $school_year = $school_year ?? (new AcademicPeriod())->getCurrentPeriod()['school_year'];

        $stmt = $this->connection->prepare("
            SELECT 
                (SELECT COUNT(*) FROM students WHERE enrollment_status = 'active') as total_active_students,
                (SELECT COUNT(*) FROM users WHERE role = 'teacher' AND status = 'active') as total_active_teachers,
                (SELECT COUNT(*) FROM sections WHERE school_year = ?) as total_sections,
                (SELECT COUNT(*) FROM subjects) as total_subjects,
                (SELECT COUNT(DISTINCT teacher_id) FROM teacher_subject_assignments WHERE status = 'active') as assigned_teachers,
                (SELECT COUNT(*) FROM teacher_subject_assignments WHERE status = 'active') as total_assignments,
                (SELECT SUM(amount_paid) FROM payment_transactions WHERE YEAR(created_at) = YEAR(CURDATE())) as yearly_revenue,
                (SELECT AVG(grade_value) FROM grades WHERE school_year = ?) as overall_average
        ");
        $stmt->execute([$school_year, $school_year]);
        return $stmt->fetch();
    }
}
