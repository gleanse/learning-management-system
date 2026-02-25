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
                        AND fc.school_year = s.year_level
                )
        ");
        $stmt->execute([]);
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
                u.last_name  AS advanced_by_last
            FROM school_settings ss
            LEFT JOIN users u ON ss.advanced_by = u.id
            ORDER BY ss.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // initialize the very first period
    public function initializePeriod($school_year, $semester, $admin_id, $deadlines = [])
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare("
                INSERT INTO school_settings (school_year, semester, is_active, advanced_by, advanced_at)
                VALUES (?, ?, TRUE, ?, NOW())
            ");
            $stmt->execute([$school_year, $semester, $admin_id]);

            $created = $this->createEnrollmentPayments($school_year, $semester, $admin_id);

            if (!empty($deadlines)) {
                $this->createGradingPeriods($school_year, $semester, $deadlines);
            }

            $this->connection->commit();
            return ['success' => true, 'created' => $created];
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[AcademicPeriod::initializePeriod] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // advance to the next semester or school year
    public function advancePeriod($admin_id, $deadlines = [])
    {
        try {
            $this->connection->beginTransaction();

            $current = $this->getCurrentPeriod();

            if (!$current) {
                throw new Exception('No active period found.');
            }

            // all grading periods must be locked before advancing
            if (!$this->allGradingPeriodsLocked($current['school_year'], $current['semester'])) {
                throw new Exception('All grading periods must be locked before advancing.');
            }

            $next = $this->resolveNextPeriod($current['school_year'], $current['semester']);
            $school_year_changed = $next['school_year'] !== $current['school_year'];

            $this->snapshotSectionHistory($current['school_year'], $current['semester']);

            // deactivate current period
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

            $new_period_id = (int) $this->connection->lastInsertId();

            if ($school_year_changed) {
                $this->promoteStudents($current['school_year']);
            }

            $created = $this->createEnrollmentPayments($next['school_year'], $next['semester'], $admin_id);

            if (!empty($deadlines)) {
                $this->createGradingPeriods($next['school_year'], $next['semester'], $deadlines);
            }

            $this->connection->commit();

            return [
                'success'             => true,
                'next'                => $next,
                'created'             => $created,
                'school_year_changed' => $school_year_changed,
                'new_period_id'       => $new_period_id,
            ];
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[AcademicPeriod::advancePeriod] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // undo last advancement — reverts to previous period and cleans up what was created
    public function undoPeriod($admin_id)
    {
        try {
            $this->connection->beginTransaction();

            $current = $this->getCurrentPeriod();

            if (!$current) {
                throw new Exception('No active period found.');
            }

            // block undo if grades already exist for current period
            if ($this->hasGradesForPeriod($current['school_year'], $current['semester'])) {
                throw new Exception('Cannot undo: grades have already been submitted for the current period.');
            }

            // get the previous period row
            $stmt = $this->connection->prepare("
                SELECT * FROM school_settings
                WHERE is_active = FALSE
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $stmt->execute();
            $previous = $stmt->fetch();

            if (!$previous) {
                throw new Exception('No previous period to undo to.');
            }

            $school_year_changed = $current['school_year'] !== $previous['school_year'];

            // block undo across school years — promotions already ran and cannot be safely reversed
            // without knowing exactly which students were promoted vs already at that level
            if ($school_year_changed) {
                throw new Exception('Cannot undo: school year has already changed. Student year levels cannot be safely reversed.');
            }

            // remove only pending payment records created for current period
            $stmt = $this->connection->prepare("
                DELETE FROM enrollment_payments
                WHERE school_year = ? AND semester = ?
                  AND status = 'pending'
            ");
            $stmt->execute([$current['school_year'], $current['semester']]);

            // remove section history snapshot that was taken when this period was created
            $stmt = $this->connection->prepare("
                DELETE FROM student_section_history
                WHERE school_year = ? AND semester = ?
            ");
            $stmt->execute([$current['school_year'], $current['semester']]);

            // unlock grading periods of the previous period so teachers can still submit
            $this->unlockAllGradingPeriods($previous['school_year'], $previous['semester']);

            // deactivate current and reactivate previous
            $stmt = $this->connection->prepare("
                UPDATE school_settings SET is_active = FALSE WHERE id = ?
            ");
            $stmt->execute([$current['id']]);

            $stmt = $this->connection->prepare("
                UPDATE school_settings
                SET is_active = TRUE, advanced_by = ?, advanced_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$admin_id, $previous['id']]);

            $this->connection->commit();
            return ['success' => true, 'previous' => $previous];
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[AcademicPeriod::undoPeriod] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // redo — readvance after an undo without losing history row
    public function redoPeriod($admin_id, $deadlines = [])
    {
        try {
            $this->connection->beginTransaction();

            $current = $this->getCurrentPeriod();

            if (!$current) {
                throw new Exception('No active period found.');
            }

            // look for a newer deactivated period row to redo into
            $stmt = $this->connection->prepare("
                SELECT * FROM school_settings
                WHERE is_active = FALSE
                  AND created_at > (
                      SELECT created_at FROM school_settings WHERE id = ?
                  )
                ORDER BY created_at ASC
                LIMIT 1
            ");
            $stmt->execute([$current['id']]);
            $redo_target = $stmt->fetch();

            if (!$redo_target) {
                throw new Exception('Nothing to redo. Use advance instead.');
            }

            // same lock guard as advance
            if (!$this->allGradingPeriodsLocked($current['school_year'], $current['semester'])) {
                throw new Exception('All grading periods must be locked before redoing.');
            }

            $school_year_changed = $redo_target['school_year'] !== $current['school_year'];

            // block redo across school years — students were already demoted via undo which was blocked
            // so if we get here with a school year change something is inconsistent
            if ($school_year_changed) {
                throw new Exception('Cannot redo across school years. Please use advance instead.');
            }

            $this->snapshotSectionHistory($current['school_year'], $current['semester']);

            $stmt = $this->connection->prepare("
                UPDATE school_settings SET is_active = FALSE WHERE is_active = TRUE
            ");
            $stmt->execute();

            // reactivate the redo target row instead of inserting a new one
            $stmt = $this->connection->prepare("
                UPDATE school_settings
                SET is_active = TRUE, advanced_by = ?, advanced_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$admin_id, $redo_target['id']]);

            // only create payments that don't exist yet (redo may have partial data)
            $created = $this->createEnrollmentPayments(
                $redo_target['school_year'],
                $redo_target['semester'],
                $admin_id
            );

            if (!empty($deadlines)) {
                $this->createGradingPeriods($redo_target['school_year'], $redo_target['semester'], $deadlines);
            }

            $this->connection->commit();

            return [
                'success' => true,
                'target'  => $redo_target,
                'created' => $created,
            ];
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[AcademicPeriod::redoPeriod] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // check whether a redo target exists after the current period
    public function canRedo()
    {
        $current = $this->getCurrentPeriod();

        if (!$current) return false;

        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total FROM school_settings
            WHERE is_active = FALSE
              AND created_at > ?
        ");
        $stmt->execute([$current['created_at']]);
        $result = $stmt->fetch();
        return (int) $result['total'] > 0;
    }

    // snapshot section assignments before they get wiped on advancement
    private function snapshotSectionHistory($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            INSERT IGNORE INTO student_section_history (student_id, section_id, section_name, school_year, semester)
            SELECT s.student_id, s.section_id, sec.section_name, ?, ?
            FROM students s
            INNER JOIN sections sec ON sec.section_id = s.section_id
            WHERE s.section_id IS NOT NULL
              AND s.enrollment_status = 'active'
        ");
        $stmt->execute([$school_year, $semester]);
    }

    // promote students to next year level when school year changes
    private function promoteStudents($current_school_year)
    {
        $year_level_map = [
            '3rd Year' => '4th Year',
            '2nd Year' => '3rd Year',
            '1st Year' => '2nd Year',
            'Grade 11' => 'Grade 12',
        ];

        foreach ($year_level_map as $current_level => $next_level) {
            $stmt = $this->connection->prepare("
                UPDATE students
                SET year_level = ?, section_id = NULL
                WHERE year_level = ?
                  AND enrollment_status = 'active'
                  AND student_id IN (
                      SELECT student_id FROM enrollment_payments
                      WHERE school_year = ? AND status = 'paid'
                  )
            ");
            $stmt->execute([$next_level, $current_level, $current_school_year]);
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
                AND fc.strand_course  = s.strand_course
                AND fc.school_year = s.year_level
            WHERE s.enrollment_status = 'active'
              AND s.student_id NOT IN (
                  SELECT student_id FROM enrollment_payments
                  WHERE school_year = ? AND semester = ?
              )
        ");

        $stmt->execute([$school_year, $semester, $admin_id, $school_year, $semester]);
        return $stmt->rowCount();
    }

    // determine what period comes after the given one
    private function resolveNextPeriod($school_year, $semester)
    {
        if ($semester === 'First') {
            return [
                'school_year' => $school_year,
                'semester'    => 'Second',
            ];
        }

        $parts      = explode('-', $school_year);
        $next_start = (int) ($parts[1] ?? date('Y'));
        $next_end   = $next_start + 1;

        return [
            'school_year' => $next_start . '-' . $next_end,
            'semester'    => 'First',
        ];
    }

    // get active students eligible for graduation
    public function getGraduatableStudents()
    {
        $stmt = $this->connection->prepare("
            SELECT student_id, first_name, middle_name, last_name,
                   student_number, year_level, strand_course, education_level
            FROM students
            WHERE year_level IN ('4th Year', 'Grade 12')
              AND enrollment_status = 'active'
            ORDER BY year_level, last_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // graduate selected students
    public function graduateStudents(array $student_ids)
    {
        $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
        $stmt = $this->connection->prepare("
            UPDATE students
            SET enrollment_status = 'graduated', section_id = NULL
            WHERE student_id IN ($placeholders)
              AND year_level IN ('4th Year', 'Grade 12')
              AND enrollment_status = 'active'
        ");
        return $stmt->execute($student_ids);
    }

    // check if any grades exist for a period
    public function hasGradesForPeriod($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total FROM grades
            WHERE school_year = ? AND semester = ?
        ");
        $stmt->execute([$school_year, $semester]);
        $result = $stmt->fetch();
        return (int) $result['total'] > 0;
    }

    // get grading periods for a given school year and semester
    public function getGradingPeriods($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM grading_periods
            WHERE school_year = ? AND semester = ?
            ORDER BY CASE grading_period
                WHEN 'Prelim'   THEN 1
                WHEN 'Midterm'  THEN 2
                WHEN 'Prefinal' THEN 3
                WHEN 'Final'    THEN 4
            END ASC
        ");
        $stmt->execute([$school_year, $semester]);
        return $stmt->fetchAll();
    }

    // check if all four grading periods exist and are locked for a given period
    public function allGradingPeriodsLocked($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total
            FROM grading_periods
            WHERE school_year = ?
              AND semester    = ?
              AND is_locked   = TRUE
        ");
        $stmt->execute([$school_year, $semester]);
        $result = $stmt->fetch();
        return (int) $result['total'] >= 4;
    }

    // lock a single grading period by period id
    public function lockGradingPeriod($period_id)
    {
        $stmt = $this->connection->prepare("
            UPDATE grading_periods SET is_locked = TRUE WHERE period_id = ?
        ");
        return $stmt->execute([$period_id]);
    }

    // unlock a single grading period by period id
    public function unlockGradingPeriod($period_id)
    {
        $stmt = $this->connection->prepare("
            UPDATE grading_periods SET is_locked = FALSE WHERE period_id = ?
        ");
        return $stmt->execute([$period_id]);
    }

    // unlock all grading periods for a semester — used during undo
    public function unlockAllGradingPeriods($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            UPDATE grading_periods
            SET is_locked = FALSE
            WHERE school_year = ? AND semester = ?
        ");
        return $stmt->execute([$school_year, $semester]);
    }

    // toggle lock status of a grading period
    public function toggleGradingPeriodLock($period_id, $is_locked)
    {
        $stmt = $this->connection->prepare("
            UPDATE grading_periods SET is_locked = ? WHERE period_id = ?
        ");
        return $stmt->execute([$is_locked ? 1 : 0, $period_id]);
    }

    // check if a grading period is locked — used by grade submission
    public function isGradingPeriodLocked($school_year, $semester, $grading_period)
    {
        $stmt = $this->connection->prepare("
            SELECT is_locked, deadline_date FROM grading_periods
            WHERE school_year = ? AND semester = ? AND grading_period = ?
        ");
        $stmt->execute([$school_year, $semester, $grading_period]);
        $result = $stmt->fetch();

        if (!$result) return false;

        // auto-lock if deadline has passed
        if ($result['deadline_date'] && $result['deadline_date'] < date('Y-m-d')) {
            return true;
        }

        return (bool) $result['is_locked'];
    }

    // lock all grading periods that have passed their deadline — run this on a cron or at page load
    public function autoLockExpiredPeriods()
    {
        $stmt = $this->connection->prepare("
            UPDATE grading_periods
            SET is_locked = TRUE
            WHERE deadline_date < CURDATE()
              AND is_locked = FALSE
        ");
        return $stmt->execute();
    }

    // create or update grading period deadlines
    private function createGradingPeriods($school_year, $semester, $deadlines)
    {
        $stmt = $this->connection->prepare("
            INSERT INTO grading_periods (school_year, semester, grading_period, deadline_date, is_locked)
            VALUES (?, ?, ?, ?, FALSE)
            ON DUPLICATE KEY UPDATE deadline_date = VALUES(deadline_date)
        ");

        $periods = ['Prelim', 'Midterm', 'Prefinal', 'Final'];

        foreach ($periods as $period) {
            $key = strtolower($period);
            if (!empty($deadlines[$key])) {
                $stmt->execute([$school_year, $semester, $period, $deadlines[$key]]);
            }
        }
    }

    // save or update grading period deadlines from outside the advance flow
    public function saveGradingPeriods($school_year, $semester, $deadlines)
    {
        try {
            $this->connection->beginTransaction();
            $this->createGradingPeriods($school_year, $semester, $deadlines);
            $this->connection->commit();
            return true;
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[AcademicPeriod::saveGradingPeriods] ' . $e->getMessage());
            return false;
        }
    }

    // get grading period summary with lock status and deadline for ui display
    public function getGradingPeriodSummary($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT
                period_id,
                grading_period,
                deadline_date,
                is_locked,
                CASE
                    WHEN is_locked = TRUE THEN 'locked'
                    WHEN deadline_date < CURDATE() THEN 'expired'
                    ELSE 'open'
                END AS lock_status
            FROM grading_periods
            WHERE school_year = ? AND semester = ?
            ORDER BY CASE grading_period
                WHEN 'Prelim'   THEN 1
                WHEN 'Midterm'  THEN 2
                WHEN 'Prefinal' THEN 3
                WHEN 'Final'    THEN 4
            END ASC
        ");
        $stmt->execute([$school_year, $semester]);
        return $stmt->fetchAll();
    }

    public function getGradingPeriodById($period_id)
    {
        $stmt = $this->connection->prepare("
        SELECT * FROM grading_periods WHERE period_id = ?
    ");
        $stmt->execute([$period_id]);
        return $stmt->fetch();
    }
}
