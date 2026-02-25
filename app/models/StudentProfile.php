<?php

require_once __DIR__ . '/../../config/db_connection.php';

class StudentProfile
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    public function getStudentsWithBalance($school_year, $semester, $limit, $offset, $search = '')
    {
        $search_param = '%' . $search . '%';

        $stmt = $this->connection->prepare("
        SELECT
            s.student_id,
            s.student_number,
            s.first_name,
            s.middle_name,
            s.last_name,
            s.year_level,
            s.strand_course,
            s.education_level,
            s.enrollment_status,
            ep.payment_id,
            ep.net_amount,
            ep.status AS payment_status,
            COALESCE(SUM(pt.amount_paid), 0) AS total_paid,
            ep.net_amount - COALESCE(SUM(pt.amount_paid), 0) AS remaining
        FROM students s
        LEFT JOIN enrollment_payments ep
            ON ep.student_id = s.student_id
            AND ep.school_year = :school_year
            AND ep.semester = :semester
        LEFT JOIN payment_transactions pt ON pt.payment_id = ep.payment_id
        WHERE s.enrollment_status = 'active'
            AND (
                s.student_number LIKE :search1
                OR s.first_name LIKE :search2
                OR s.last_name LIKE :search3
                OR CONCAT(s.first_name, ' ', s.last_name) LIKE :search4
            )
        GROUP BY s.student_id, ep.payment_id, ep.net_amount, ep.status
        ORDER BY s.last_name ASC, s.first_name ASC
        LIMIT :limit OFFSET :offset
    ");

        $stmt->bindValue(':school_year', $school_year);
        $stmt->bindValue(':semester',    $semester);
        $stmt->bindValue(':search1',     $search_param);
        $stmt->bindValue(':search2',     $search_param);
        $stmt->bindValue(':search3',     $search_param);
        $stmt->bindValue(':search4',     $search_param);
        $stmt->bindValue(':limit',       (int) $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset',      (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // get total count for pagination
    public function getTotalCount($search = '')
    {
        $search_param = '%' . $search . '%';

        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total
            FROM students s
            WHERE s.enrollment_status = 'active'
                AND (
                    s.student_number LIKE ?
                    OR s.first_name LIKE ?
                    OR s.last_name LIKE ?
                    OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?
                )
        ");
        $stmt->execute([$search_param, $search_param, $search_param, $search_param]);
        $result = $stmt->fetch();
        return (int) $result['total'];
    }

    // get single student with profile and current balance
    public function getStudentWithProfile($student_id, $school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT
                s.*,
                COALESCE(u.email, sp.email) AS email,
                sp.date_of_birth,
                sp.gender,
                sp.contact_number,
                sp.home_address,
                sp.previous_school,
                sp.special_notes,
                sec.section_name,
                ep.payment_id,
                ep.net_amount,
                ep.status AS payment_status,
                COALESCE(SUM(pt.amount_paid), 0) AS total_paid,
                ep.net_amount - COALESCE(SUM(pt.amount_paid), 0) AS remaining
            FROM students s
            LEFT JOIN student_profiles sp ON sp.student_id = s.student_id
            LEFT JOIN users u ON u.id = s.user_id
            LEFT JOIN sections sec ON sec.section_id = s.section_id
            LEFT JOIN enrollment_payments ep
                ON ep.student_id = s.student_id
                AND ep.school_year = ?
                AND ep.semester = ?
            LEFT JOIN payment_transactions pt ON pt.payment_id = ep.payment_id
            WHERE s.student_id = ?
            GROUP BY s.student_id, sp.profile_id, sec.section_id, ep.payment_id, ep.net_amount, ep.status
        ");
        $stmt->execute([$school_year, $semester, $student_id]);
        return $stmt->fetch();
    }

    // get payment transaction history for a student
    public function getPaymentHistory($student_id)
    {
        $stmt = $this->connection->prepare("
            SELECT
                pt.transaction_id,
                pt.amount_paid,
                pt.payment_date,
                pt.notes,
                pt.created_at,
                ep.school_year,
                ep.semester,
                ep.status AS payment_status,
                CONCAT(u.first_name, ' ', u.last_name) AS received_by_name
            FROM payment_transactions pt
            INNER JOIN enrollment_payments ep ON ep.payment_id = pt.payment_id
            INNER JOIN users u ON u.id = pt.received_by
            WHERE ep.student_id = ?
            ORDER BY pt.payment_date DESC, pt.created_at DESC
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetchAll();
    }

    // update name fields in students table
    public function updateStudentName($student_id, $first_name, $middle_name, $last_name)
    {
        $stmt = $this->connection->prepare("
            UPDATE students
            SET first_name = ?, middle_name = ?, last_name = ?
            WHERE student_id = ?
        ");
        return $stmt->execute([$first_name, $middle_name, $last_name, $student_id]);
    }

    // upsert student profile
    public function saveProfile($student_id, $data)
    {
        // check if student has a linked user account
        $stmt = $this->connection->prepare("SELECT user_id FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();
        $has_account = $student && $student['user_id'] !== null;

        // if student has account, email lives in users table so dont touch student_profiles.email
        $email = $has_account ? null : ($data['email'] ?? null);

        $stmt = $this->connection->prepare("
            INSERT INTO student_profiles
                (student_id, email, date_of_birth, gender, contact_number, home_address, previous_school, special_notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                email           = VALUES(email),
                date_of_birth   = VALUES(date_of_birth),
                gender          = VALUES(gender),
                contact_number  = VALUES(contact_number),
                home_address    = VALUES(home_address),
                previous_school = VALUES(previous_school),
                special_notes   = VALUES(special_notes),
                updated_at      = CURRENT_TIMESTAMP
        ");
        return $stmt->execute([
            $student_id,
            $email,
            $data['date_of_birth']   ?? null,
            $data['gender']          ?? null,
            $data['contact_number']  ?? null,
            $data['home_address']    ?? null,
            $data['previous_school'] ?? null,
            $data['special_notes']   ?? null,
        ]);
    }

    // get all active students for export
    public function getAllForExport($school_year, $semester)
    {
        $stmt = $this->connection->prepare("
            SELECT
                s.student_number,
                CONCAT(s.last_name, ', ', s.first_name, IF(s.middle_name IS NOT NULL, CONCAT(' ', s.middle_name), '')) AS student_name,
                s.year_level,
                s.strand_course,
                s.education_level,
                ep.net_amount,
                ep.status AS payment_status,
                COALESCE(SUM(pt.amount_paid), 0) AS total_paid,
                ep.net_amount - COALESCE(SUM(pt.amount_paid), 0) AS remaining
            FROM students s
            LEFT JOIN enrollment_payments ep
                ON ep.student_id = s.student_id
                AND ep.school_year = ?
                AND ep.semester = ?
            LEFT JOIN payment_transactions pt ON pt.payment_id = ep.payment_id
            WHERE s.enrollment_status = 'active'
            GROUP BY s.student_id, ep.payment_id, ep.net_amount, ep.status
            ORDER BY s.last_name ASC, s.first_name ASC
        ");
        $stmt->execute([$school_year, $semester]);
        return $stmt->fetchAll();
    }

    // get enrollment documents for a student by school year
    public function getEnrollmentDocuments($student_id, $school_year)
    {
        $stmt = $this->connection->prepare("
            SELECT *
            FROM enrollment_documents
            WHERE student_id = ? AND school_year = ?
        ");
        $stmt->execute([$student_id, $school_year]);
        return $stmt->fetch();
    }

    // upsert enrollment documents
    public function saveEnrollmentDocuments($student_id, $school_year, $data)
    {
        $stmt = $this->connection->prepare("
            INSERT INTO enrollment_documents
                (student_id, school_year, psa_birth_certificate, form_138_report_card,
                 good_moral_certificate, id_pictures, medical_certificate)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                psa_birth_certificate   = VALUES(psa_birth_certificate),
                form_138_report_card    = VALUES(form_138_report_card),
                good_moral_certificate  = VALUES(good_moral_certificate),
                id_pictures             = VALUES(id_pictures),
                medical_certificate     = VALUES(medical_certificate),
                updated_at              = CURRENT_TIMESTAMP
        ");
        return $stmt->execute([
            $student_id,
            $school_year,
            (int) ($data['psa_birth_certificate']  ?? 0),
            (int) ($data['form_138_report_card']   ?? 0),
            (int) ($data['good_moral_certificate'] ?? 0),
            (int) ($data['id_pictures']            ?? 0),
            (int) ($data['medical_certificate']    ?? 0),
        ]);
    }

    public function getLinkedUserId($student_id)
    {
        $stmt = $this->connection->prepare("SELECT user_id FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $result = $stmt->fetch();
        return $result ? $result['user_id'] : null;
    }

    public function updateUserEmail($user_id, $email)
    {
        $stmt = $this->connection->prepare("UPDATE users SET email = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$email, $user_id]);
    }

    public function getProfileData($student_id)
    {
        $stmt = $this->connection->prepare("
        SELECT 
            email,
            date_of_birth,
            gender,
            contact_number,
            home_address,
            previous_school,
            special_notes
        FROM student_profiles 
        WHERE student_id = ?
    ");
        $stmt->execute([$student_id]);
        return $stmt->fetch();
    }
}
