DROP DATABASE IF EXISTS registrar;
CREATE DATABASE registrar;
USE registrar;

SET FOREIGN_KEY_CHECKS = 0;

-- ===============================
-- USERS
-- ===============================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'finance', 'registrar', 'student', 'teacher') DEFAULT 'student',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===============================
-- SECTIONS
-- ===============================
CREATE TABLE sections (
    section_id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(100) NOT NULL,
    year_level VARCHAR(50) NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_section (section_name, school_year)
) ENGINE=InnoDB;

-- ===============================
-- SUBJECTS
-- ===============================
CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    subject_name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===============================
-- STUDENTS
-- ===============================
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    student_number VARCHAR(50) UNIQUE NOT NULL,
    lrn VARCHAR(12) NULL,
    section_id INT NOT NULL,
    year_level VARCHAR(50) NOT NULL,
    education_level ENUM('senior_high', 'college') NOT NULL,
    enrollment_status ENUM('active', 'inactive', 'graduated', 'dropped') DEFAULT 'active',
    contact VARCHAR(20) NULL,
    guardian VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (user_id),
    INDEX (section_id)
) ENGINE=InnoDB;

-- ===============================
-- TEACHER SUBJECT ASSIGNMENTS
-- ===============================
CREATE TABLE teacher_subject_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    year_level VARCHAR(50) NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    assigned_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_teacher_subject (teacher_id, subject_id, year_level, school_year),
    INDEX (teacher_id),
    INDEX (subject_id)
) ENGINE=InnoDB;

-- ===============================
-- STUDENT SUBJECT ENROLLMENTS
-- ===============================
CREATE TABLE student_subject_enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    semester ENUM('First', 'Second') NOT NULL,
    enrolled_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_enrollment (student_id, subject_id, school_year, semester),
    INDEX (student_id),
    INDEX (subject_id)
) ENGINE=InnoDB;

-- ===============================
-- GRADES
-- ===============================
CREATE TABLE grades (
    grade_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    grading_period ENUM('Prelim', 'Midterm', 'Prefinal', 'Final') NOT NULL,
    semester ENUM('First', 'Second') NOT NULL,
    grade_value DECIMAL(5,2) NOT NULL,
    remarks TEXT NULL,
    school_year VARCHAR(20) NOT NULL,
    graded_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_grade (student_id, subject_id, grading_period, semester, school_year),
    INDEX (student_id),
    INDEX (subject_id),
    INDEX (teacher_id)
) ENGINE=InnoDB;

-- ===============================
-- GRADING PERIODS
-- ===============================
CREATE TABLE grading_periods (
    period_id INT AUTO_INCREMENT PRIMARY KEY,
    school_year VARCHAR(20) NOT NULL,
    semester ENUM('First', 'Second') NOT NULL,
    grading_period ENUM('Prelim', 'Midterm', 'Prefinal', 'Final') NOT NULL,
    deadline_date DATE NOT NULL,
    is_locked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_period (school_year, semester, grading_period)
) ENGINE=InnoDB;

-- ===============================
-- SECTION SUBJECTS
-- ===============================
CREATE TABLE section_subjects (
    section_id INT NOT NULL,
    subject_id INT NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (section_id, subject_id, school_year),
    INDEX (section_id),
    INDEX (subject_id)
) ENGINE=InnoDB;

-- ===============================
-- ACADEMIC YEARS
-- ===============================
CREATE TABLE academic_years (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_year VARCHAR(20) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===============================
-- PAYMENT TERMS
-- ===============================
CREATE TABLE payment_terms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    term_name VARCHAR(50) NOT NULL,
    number_of_payments INT NOT NULL
) ENGINE=InnoDB;

INSERT INTO payment_terms (term_name, number_of_payments) VALUES
('Full Payment', 1),
('Per Semester', 2),
('Per Quarter', 4);

-- ===============================
-- FEE STRUCTURE
-- ===============================
CREATE TABLE fee_structure (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_id INT NOT NULL,
    year_level VARCHAR(50) NOT NULL,
    semester_fee DECIMAL(10,2) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_section_year (section_id, year_level),
    INDEX (section_id)
) ENGINE=InnoDB;

-- ===============================
-- ENROLLMENTS (FINANCIAL)
-- ===============================
CREATE TABLE enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    ay_id INT NOT NULL,
    semester ENUM('First', 'Second') NOT NULL,
    year_level VARCHAR(50) NOT NULL,
    section_id INT NOT NULL,
    term_id INT NOT NULL,
    base_fees DECIMAL(10,2) NOT NULL,
    payment_per_installment DECIMAL(10,2) NOT NULL,
    total_fees DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (student_id),
    INDEX (ay_id),
    INDEX (section_id),
    INDEX (term_id)
) ENGINE=InnoDB;

-- ===============================
-- PAYMENT INSTALLMENTS
-- ===============================
CREATE TABLE payment_installments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    enrollment_id INT NOT NULL,
    installment_number INT NOT NULL,
    installment_name VARCHAR(50) NOT NULL,
    amount_due DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) DEFAULT 0,
    status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (enrollment_id)
) ENGINE=InnoDB;

-- ===============================
-- CASH DRAWER
-- ===============================
CREATE TABLE cash_drawer (
    id INT PRIMARY KEY AUTO_INCREMENT,
    drawer_date DATE NOT NULL UNIQUE,
    opening_balance DECIMAL(10,2) NOT NULL,
    closing_balance DECIMAL(10,2) DEFAULT 0,
    total_cash_in DECIMAL(10,2) DEFAULT 0,
    status ENUM('open', 'closed') DEFAULT 'open',
    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at DATETIME NULL
) ENGINE=InnoDB;

-- ===============================
-- CASH DRAWERS
-- ===============================
CREATE TABLE cash_drawers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    drawer_date DATE NOT NULL UNIQUE,
    opening_balance DECIMAL(10,2) NOT NULL,
    closing_balance DECIMAL(10,2) DEFAULT 0,
    total_cash_in DECIMAL(10,2) DEFAULT 0,
    is_open TINYINT(1) DEFAULT 1,
    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at DATETIME NULL
) ENGINE=InnoDB;

-- ===============================
-- CASH PAYMENTS
-- ===============================
CREATE TABLE cash_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    installment_id INT NOT NULL,
    drawer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    receipt_number VARCHAR(50) UNIQUE NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (installment_id),
    INDEX (drawer_id)
) ENGINE=InnoDB;

-- ===============================
-- FEE HISTORY
-- ===============================
CREATE TABLE fee_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_id INT NOT NULL,
    year_level VARCHAR(50) NOT NULL,
    old_fee DECIMAL(10,2),
    new_fee DECIMAL(10,2),
    changed_by VARCHAR(100),
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (section_id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- Insert academic years
INSERT INTO academic_years (school_year, is_active) VALUES
('2024-2025', 1),  -- Set as active
('2023-2024', 0),
('2025-2026', 0);

-- Verify
SELECT * FROM academic_years;


-- Baguhin ang unique constraint para isama ang year_level
ALTER TABLE sections DROP INDEX unique_section;
ALTER TABLE sections ADD UNIQUE KEY unique_section_year (section_name, year_level, school_year);

-- Ngayon i-insert ang college sections
INSERT INTO sections (section_name, year_level, school_year) VALUES
('BSIT', '1st Year', '2024-2025'),
('BSIT', '2nd Year', '2024-2025'),
('BSIT', '3rd Year', '2024-2025'),
('BSIT', '4th Year', '2024-2025'),
('BSHM', '1st Year', '2024-2025'),
('BSHM', '2nd Year', '2024-2025'),
('BSHM', '3rd Year', '2024-2025'),
('BSHM', '4th Year', '2024-2025'),
('BSOA', '1st Year', '2024-2025'),
('BSOA', '2nd Year', '2024-2025'),
('BSOA', '3rd Year', '2024-2025'),
('BSOA', '4th Year', '2024-2025');

-- I-verify
SELECT * FROM sections ORDER BY section_name, 
    CASE year_level
        WHEN 'Grade 11' THEN 1
        WHEN 'Grade 12' THEN 2
        WHEN '1st Year' THEN 3
        WHEN '2nd Year' THEN 4
        WHEN '3rd Year' THEN 5
        WHEN '4th Year' THEN 6
    END;

    ALTER TABLE students MODIFY COLUMN enrollment_status ENUM('active', 'inactive', 'graduated', 'dropped') DEFAULT 'inactive';
    UPDATE students SET enrollment_status = 'inactive' WHERE student_id NOT IN (SELECT student_id FROM enrollments);

    -- Add updated_at column to payment_installments table
ALTER TABLE payment_installments ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add updated_at column to cash_drawer table
ALTER TABLE cash_drawer ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;



