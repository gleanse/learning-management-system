<?php
require_once __DIR__ . '/../../config/db_connection.php';

class AcademicPeriod
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // get the current active school setting
    public function getCurrentPeriod()
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM school_settings
            WHERE is_active = TRUE
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch();
    }

    // check if any period has been set up yet
    public function hasPeriod()
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as total FROM school_settings");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['total'] > 0;
    }

    // get total active students
    public function getActiveStudentCount()
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total FROM students WHERE enrollment_status = 'active'
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['total'];
    }

    // get count of students who already have enrollment_payment for current period
    public function getPaidStudentCount($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(DISTINCT student_id) as total
            FROM enrollment_payments
            WHERE school_year = ? AND semester = ?
        ");
        $stmt->execute([$school_year, $semester]);
        $result = $stmt->fetch();
        return (int) $result['total'];
    }

    // get count of students missing fee_config for current period
    public function getStudentsMissingFeeConfig($school_year)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total
            FROM students s
            WHERE s.enrollment_status = 'active'
                AND NOT EXISTS (
                    SELECT 1 FROM fee_config fc
                    WHERE fc.education_level = s.education_level
                        AND fc.strand_course = s.strand_course
                        AND fc.school_year = ?
                )
        ");
        $stmt->execute([$school_year]);
        $result = $stmt->fetch();
        return (int) $result['total'];
    }

    // get advancement history
    public function getHistory($limit = 10)
    {
        $stmt = $this->connection->prepare("
            SELECT
                ss.id,
                ss.school_year,
                ss.semester,
                ss.is_active,
                ss.advanced_at,
                ss.created_at,
                u.first_name AS advanced_by_first,
                u.last_name AS advanced_by_last
            FROM school_settings ss
            LEFT JOIN users u ON ss.advanced_by = u.id
            ORDER BY ss.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // initialize the very first period — only called once
    public function initializePeriod($school_year, $semester, $admin_id)
    {
        try {
            $this->connection->beginTransaction();

            // insert the first school_settings record
            $stmt = $this->connection->prepare("
                INSERT INTO school_settings (school_year, semester, is_active, advanced_by, advanced_at)
                VALUES (?, ?, TRUE, ?, NOW())
            ");
            $stmt->execute([$school_year, $semester, $admin_id]);

            // create enrollment_payments for all active students
            $created = $this->createEnrollmentPayments($school_year, $semester, $admin_id);

            $this->connection->commit();
            return ['success' => true, 'created' => $created];
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[AcademicPeriod::initializePeriod] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // advance to next semester or school year
    public function advancePeriod($admin_id)
    {
        try {
            $this->connection->beginTransaction();

            $current = $this->getCurrentPeriod();

            if (!$current) {
                throw new Exception('No active period found.');
            }

            // determine next period
            $next = $this->resolveNextPeriod($current['school_year'], $current['semester']);

            // deactivate current
            $stmt = $this->connection->prepare("
                UPDATE school_settings SET is_active = FALSE WHERE is_active = TRUE
            ");
            $stmt->execute();

            // insert new active period
            $stmt = $this->connection->prepare("
                INSERT INTO school_settings (school_year, semester, is_active, advanced_by, advanced_at)
                VALUES (?, ?, TRUE, ?, NOW())
            ");
            $stmt->execute([$next['school_year'], $next['semester'], $admin_id]);

            // create enrollment_payments for all active students for new period
            $created = $this->createEnrollmentPayments($next['school_year'], $next['semester'], $admin_id);

            $this->connection->commit();
            return ['success' => true, 'next' => $next, 'created' => $created];
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[AcademicPeriod::advancePeriod] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // creates enrollment_payments for all active students that don't have one yet for this period
    private function createEnrollmentPayments($school_year, $semester, $admin_id)
    {
        $stmt = $this->connection->prepare("
            INSERT INTO enrollment_payments
                (student_id, school_year, semester, total_amount, discount_amount, net_amount, status, created_by)
            SELECT
                s.student_id,
                ?,
                ?,
                (fc.tuition_fee + fc.miscellaneous + fc.other_fees),
                0.00,
                (fc.tuition_fee + fc.miscellaneous + fc.other_fees),
                'pending',
                ?
            FROM students s
            INNER JOIN fee_config fc
                ON fc.education_level = s.education_level
                AND fc.strand_course = s.strand_course
                AND fc.school_year = ?
            WHERE s.enrollment_status = 'active'
                AND s.student_id NOT IN (
                    SELECT student_id FROM enrollment_payments
                    WHERE school_year = ? AND semester = ?
                )
        ");

        $stmt->execute([$school_year, $semester, $admin_id, $school_year, $school_year, $semester]);
        return $stmt->rowCount();
    }

    // determine what comes next based on current period
    private function resolveNextPeriod($school_year, $semester)
    {
        if ($semester === 'First') {
            return [
                'school_year' => $school_year,
                'semester'    => 'Second',
            ];
        }

        // second semester → next school year first semester
        $parts      = explode('-', $school_year);
        $next_start = (int) ($parts[1] ?? date('Y'));
        $next_end   = $next_start + 1;

        return [
            'school_year' => $next_start . '-' . $next_end,
            'semester'    => 'First',
        ];
    }
}
