<?php

require_once __DIR__ . '/../../config/db_connection.php';

class RegistrarDashboard
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // total active students
    public function getTotalActiveStudents()
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total FROM students WHERE enrollment_status = 'active'
        ");
        $stmt->execute();
        return (int) $stmt->fetch()['total'];
    }

    // total enrollments for current period
    public function getTotalEnrollments($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total FROM enrollment_payments
            WHERE school_year = ? AND semester = ?
        ");
        $stmt->execute([$school_year, $semester]);
        return (int) $stmt->fetch()['total'];
    }

    // count by payment status for current period
    public function getPaymentStatusCounts($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT status, COUNT(*) as total
            FROM enrollment_payments
            WHERE school_year = ? AND semester = ?
            GROUP BY status
        ");
        $stmt->execute([$school_year, $semester]);
        $rows = $stmt->fetchAll();

        $counts = ['pending' => 0, 'partial' => 0, 'paid' => 0];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }

    // total collected this period
    public function getTotalCollected($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT COALESCE(SUM(pt.amount_paid), 0) as total
            FROM payment_transactions pt
            INNER JOIN enrollment_payments ep ON ep.payment_id = pt.payment_id
            WHERE ep.school_year = ? AND ep.semester = ?
        ");
        $stmt->execute([$school_year, $semester]);
        return (float) $stmt->fetch()['total'];
    }

    // total outstanding balance this period
    public function getTotalOutstanding($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT COALESCE(SUM(ep.net_amount - COALESCE(paid.total_paid, 0)), 0) as total
            FROM enrollment_payments ep
            LEFT JOIN (
                SELECT payment_id, SUM(amount_paid) as total_paid
                FROM payment_transactions
                GROUP BY payment_id
            ) paid ON paid.payment_id = ep.payment_id
            WHERE ep.school_year = ? AND ep.semester = ?
              AND ep.status != 'paid'
        ");
        $stmt->execute([$school_year, $semester]);
        return (float) $stmt->fetch()['total'];
    }

    // recent enrollments — last 8 students enrolled
    public function getRecentEnrollments($school_year, $semester, $limit = 8)
    {
        $stmt = $this->connection->prepare("
        SELECT
            s.student_id,
            s.student_number,
            s.first_name,
            s.last_name,
            s.year_level,
            s.strand_course,
            s.education_level,
            ep.status AS payment_status,
            ep.net_amount,
            COALESCE(SUM(pt.amount_paid), 0) AS total_paid,
            s.created_at
        FROM students s
        INNER JOIN enrollment_payments ep
            ON ep.student_id = s.student_id
            AND ep.school_year = ?
            AND ep.semester = ?
        LEFT JOIN payment_transactions pt ON pt.payment_id = ep.payment_id
        WHERE s.enrollment_status = 'active'
        GROUP BY s.student_id, ep.payment_id, ep.status, ep.net_amount, s.created_at
        ORDER BY s.created_at DESC
        LIMIT " . (int) $limit . "
    ");
        $stmt->execute([$school_year, $semester]);
        return $stmt->fetchAll();
    }

    // recent payment transactions — last 8
    public function getRecentTransactions($school_year, $semester, $limit = 8)
    {
        $stmt = $this->connection->prepare("
        SELECT
            pt.transaction_id,
            pt.amount_paid,
            pt.payment_date,
            pt.created_at,
            s.student_id,
            s.student_number,
            s.first_name,
            s.last_name,
            ep.school_year,
            ep.semester,
            CONCAT(u.first_name, ' ', u.last_name) AS received_by_name
        FROM payment_transactions pt
        INNER JOIN enrollment_payments ep ON ep.payment_id = pt.payment_id
        INNER JOIN students s ON s.student_id = ep.student_id
        INNER JOIN users u ON u.id = pt.received_by
        WHERE ep.school_year = ? AND ep.semester = ?
        ORDER BY pt.created_at DESC
        LIMIT " . (int) $limit . "
    ");
        $stmt->execute([$school_year, $semester]);
        return $stmt->fetchAll();
    }

    // breakdown by education level
    public function getEnrollmentByLevel($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT
                s.education_level,
                COUNT(*) as total
            FROM students s
            INNER JOIN enrollment_payments ep
                ON ep.student_id = s.student_id
                AND ep.school_year = ?
                AND ep.semester = ?
            WHERE s.enrollment_status = 'active'
            GROUP BY s.education_level
        ");
        $stmt->execute([$school_year, $semester]);
        $rows = $stmt->fetchAll();

        $result = ['senior_high' => 0, 'college' => 0];
        foreach ($rows as $row) {
            $result[$row['education_level']] = (int) $row['total'];
        }
        return $result;
    }

    // students with no payment record yet for current period
    public function getStudentsWithoutPayment($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total
            FROM students s
            WHERE s.enrollment_status = 'active'
              AND NOT EXISTS (
                  SELECT 1 FROM enrollment_payments ep
                  WHERE ep.student_id = s.student_id
                    AND ep.school_year = ?
                    AND ep.semester = ?
              )
        ");
        $stmt->execute([$school_year, $semester]);
        return (int) $stmt->fetch()['total'];
    }
}
