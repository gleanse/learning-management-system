<?php

require_once __DIR__ . '/../../config/db_connection.php';

class Enrollment
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // format: SHS-2025-0001 or COL-2025-0001
    public function generateStudentNumber($education_level)
    {
        $prefix = $education_level === 'senior_high' ? 'SHS' : 'COL';
        $year   = date('Y');

        $stmt = $this->connection->prepare("
            SELECT student_number FROM students
            WHERE student_number LIKE ?
            ORDER BY student_number DESC LIMIT 1
        ");
        $stmt->execute(["$prefix-$year-%"]);
        $last = $stmt->fetchColumn();

        if ($last) {
            $last_seq = (int) substr($last, strrpos($last, '-') + 1);
            $seq      = str_pad($last_seq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $seq = '0001';
        }

        return "$prefix-$year-$seq";
    }

    // fetch one row — tuition, miscellaneous, other_fees for the given school year + level + course
    public function getFeeConfig($school_year, $education_level, $strand_course)
    {
        $stmt = $this->connection->prepare("
            SELECT tuition_fee, miscellaneous, other_fees
            FROM fee_config
            WHERE school_year    = ?
            AND education_level  = ?
            AND strand_course    = ?
            LIMIT 1
        ");
        $stmt->execute([$school_year, $education_level, $strand_course]);
        return $stmt->fetch();
    }

    // soft duplicate check — matches on first + last + optional middle name (case-insensitive)
    // returns array of matching students with enough info for the registrar to identify them
    public function checkDuplicateName($first_name, $last_name, $middle_name = '')
    {
        if ($middle_name) {
            $stmt = $this->connection->prepare("
                SELECT student_number, first_name, middle_name, last_name,
                       education_level, strand_course, year_level, enrollment_status
                FROM students
                WHERE LOWER(first_name)  = LOWER(?)
                AND   LOWER(last_name)   = LOWER(?)
                AND   LOWER(IFNULL(middle_name, '')) = LOWER(?)
            ");
            $stmt->execute([$first_name, $last_name, $middle_name]);
        } else {
            $stmt = $this->connection->prepare("
                SELECT student_number, first_name, middle_name, last_name,
                       education_level, strand_course, year_level, enrollment_status
                FROM students
                WHERE LOWER(first_name) = LOWER(?)
                AND   LOWER(last_name)  = LOWER(?)
            ");
            $stmt->execute([$first_name, $last_name]);
        }

        return $stmt->fetchAll();
    }

    public function getSectionsByFilter($education_level, $year_level, $strand_course, $school_year)
    {
        $stmt = $this->connection->prepare("
            SELECT section_id, section_name, max_capacity,
                   (SELECT COUNT(*) FROM students s
                    WHERE s.section_id = sections.section_id
                    AND s.enrollment_status = 'active') AS enrolled_count
            FROM sections
            WHERE education_level = ?
            AND year_level        = ?
            AND strand_course     = ?
            AND school_year       = ?
        ");
        $stmt->execute([$education_level, $year_level, $strand_course, $school_year]);
        return $stmt->fetchAll();
    }

    public function getSubjectsBySection($section_id, $school_year)
    {
        $stmt = $this->connection->prepare("
            SELECT s.subject_id, s.subject_code, s.subject_name
            FROM subjects s
            INNER JOIN section_subjects ss ON ss.subject_id = s.subject_id
            WHERE ss.section_id  = ?
            AND ss.school_year   = ?
            ORDER BY s.subject_code ASC
        ");
        $stmt->execute([$section_id, $school_year]);
        return $stmt->fetchAll();
    }

    public function getAllSubjects()
    {
        $stmt = $this->connection->prepare("
            SELECT subject_id, subject_code, subject_name
            FROM subjects
            ORDER BY subject_code ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getActiveSchoolYears()
    {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT school_year FROM sections ORDER BY school_year DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // main entry point — inserts across all enrollment tables
    // returns new student_id on success, false on failure
    public function enrollNewStudent($data, $registrar_id)
    {
        try {
            $this->connection->beginTransaction();

            $student_id = $this->insertStudent($data);

            $this->insertStudentProfile($student_id, $data);

            $this->insertEnrollmentDocuments($student_id, $data);

            // regular = auto enroll all section subjects, irregular = manual subject pick
            $semester = $data['semester'] ?? 'First';
            if (!empty($data['is_irregular'])) {
                $this->enrollSubjectsManual($student_id, $data['subject_ids'] ?? [], $data['school_year'], $semester);
            } else {
                $this->enrollSubjectsFromSection($student_id, $data['section_id'], $data['school_year'], $semester);
            }

            $payment_id = $this->insertEnrollmentPayment($student_id, $data, $registrar_id);

            // only record transaction if upfront amount was paid
            if (!empty($data['initial_amount_paid']) && $data['initial_amount_paid'] > 0) {
                $this->insertPaymentTransaction($payment_id, $data, $registrar_id);
            }

            $this->connection->commit();
            return $student_id;
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[Enrollment::enrollNewStudent] ' . $e->getMessage());
            return false;
        }
    }

    public function insertStudent($data)
    {
        $student_number = $this->generateStudentNumber($data['education_level']);

        $stmt = $this->connection->prepare("
            INSERT INTO students (
                user_id, first_name, middle_name, last_name,
                student_number, lrn, section_id, year_level,
                education_level, strand_course, enrollment_status,
                guardian, guardian_contact, created_at, updated_at
            ) VALUES (
                NULL, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, 'active',
                ?, ?, NOW(), NOW()
            )
        ");

        $stmt->execute([
            trim($data['first_name']),
            $data['middle_name'] ?? null,
            trim($data['last_name']),
            $student_number,
            $data['lrn'] ?? null,
            !empty($data['section_id']) ? (int) $data['section_id'] : null,
            $data['year_level'],
            $data['education_level'],
            $data['strand_course'],
            trim($data['guardian_name']),
            trim($data['guardian_contact']),
        ]);

        return $this->connection->lastInsertId();
    }

    public function insertStudentProfile($student_id, $data)
    {
        $stmt = $this->connection->prepare("
            INSERT INTO student_profiles (
                student_id, email, date_of_birth, gender,
                contact_number, home_address, previous_school, special_notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $student_id,
            $data['email'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? null,
            $data['contact_number'] ?? null,
            $data['home_address'] ?? null,
            $data['previous_school'] ?? null,
            $data['special_notes'] ?? null,
        ]);
    }

    public function insertEnrollmentDocuments($student_id, $data)
    {
        $stmt = $this->connection->prepare("
            INSERT INTO enrollment_documents (
                student_id, school_year,
                psa_birth_certificate, form_138_report_card,
                good_moral_certificate, id_pictures, medical_certificate
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $student_id,
            $data['school_year'],
            isset($data['docs']['psa_birth_certificate']) ? 1 : 0,
            isset($data['docs']['form_138_report_card']) ? 1 : 0,
            isset($data['docs']['good_moral_certificate']) ? 1 : 0,
            isset($data['docs']['id_pictures']) ? 1 : 0,
            isset($data['docs']['medical_certificate']) ? 1 : 0,
        ]);
    }

    // bulk enroll into all subjects tied to the section
    public function enrollSubjectsFromSection($student_id, $section_id, $school_year, $semester)
    {
        $subjects = $this->getSubjectsBySection($section_id, $school_year);

        if (empty($subjects)) return;

        $stmt = $this->connection->prepare("
            INSERT IGNORE INTO student_subject_enrollments
                (student_id, subject_id, school_year, semester, enrolled_date)
            VALUES (?, ?, ?, ?, CURDATE())
        ");

        foreach ($subjects as $subject) {
            $stmt->execute([$student_id, $subject['subject_id'], $school_year, $semester]);
        }
    }

    // enroll only in manually selected subjects for irregular students
    public function enrollSubjectsManual($student_id, $subject_ids, $school_year, $semester)
    {
        if (empty($subject_ids)) return;

        $stmt = $this->connection->prepare("
            INSERT IGNORE INTO student_subject_enrollments
                (student_id, subject_id, school_year, semester, enrolled_date)
            VALUES (?, ?, ?, ?, CURDATE())
        ");

        foreach ($subject_ids as $subject_id) {
            $stmt->execute([$student_id, (int)$subject_id, $school_year, $semester]);
        }
    }

    public function insertEnrollmentPayment($student_id, $data, $created_by)
    {
        $total_amount    = (float) ($data['total_amount'] ?? 0);
        $discount_amount = 0.00;
        $net_amount      = $total_amount - $discount_amount;
        $amount_paid     = (float) ($data['initial_amount_paid'] ?? 0);

        $status = 'pending';
        if ($amount_paid >= $net_amount && $net_amount > 0) {
            $status = 'paid';
        } elseif ($amount_paid > 0) {
            $status = 'partial';
        }

        $stmt = $this->connection->prepare("
            INSERT INTO enrollment_payments (
                student_id, school_year, semester,
                total_amount, discount_amount, net_amount, status, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $student_id,
            $data['school_year'],
            $data['semester'] ?? 'First',
            $total_amount,
            $discount_amount,
            $net_amount,
            $status,
            $created_by,
        ]);

        return $this->connection->lastInsertId();
    }

    public function insertPaymentTransaction($payment_id, $data, $received_by)
    {
        $stmt = $this->connection->prepare("
            INSERT INTO payment_transactions (
                payment_id, amount_paid, payment_date, notes, received_by
            ) VALUES (?, ?, CURDATE(), ?, ?)
        ");

        $stmt->execute([
            $payment_id,
            (float) $data['initial_amount_paid'],
            $data['payment_notes'] ?? null,
            $received_by,
        ]);
    }

    public function saveDraft($form_data)
    {
        $_SESSION['enrollment_draft'] = json_encode($form_data);
    }

    public function getDraft()
    {
        if (empty($_SESSION['enrollment_draft'])) return null;
        return json_decode($_SESSION['enrollment_draft'], true);
    }

    public function clearDraft()
    {
        unset($_SESSION['enrollment_draft']);
    }

    public function getStudentWithDetails($student_id)
    {
        $stmt = $this->connection->prepare("
            SELECT s.*, sp.email, sp.date_of_birth, sp.gender,
                   sp.contact_number, sp.home_address, sp.previous_school,
                   sp.special_notes, sec.section_name
            FROM students s
            LEFT JOIN student_profiles sp ON sp.student_id = s.student_id
            LEFT JOIN sections sec ON sec.section_id = s.section_id
            WHERE s.student_id = ?
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetch();
    }
}
