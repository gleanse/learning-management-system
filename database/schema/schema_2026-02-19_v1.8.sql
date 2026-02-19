-- clean schema - final database structure
-- this represents the final state of all migrations
-- for quick recreation of fresh database

CREATE TABLE users(
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'registrar', 'admin', 'superadmin') NOT NULL,
    status ENUM('active', 'inactive', 'suspended', 'graduated') DEFAULT 'active',
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL 
);

CREATE TABLE login_lockouts (
    ip_address VARCHAR(45) PRIMARY KEY,
    fail_count INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_locked (locked_until)
);

CREATE TABLE remember_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE sections (
    section_id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(100) NOT NULL,
    education_level ENUM('senior_high', 'college') NOT NULL,
    year_level VARCHAR(50) NOT NULL,
    strand_course VARCHAR(50) NOT NULL,
    max_capacity INT NULL,
    school_year VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_section (section_name, year_level, school_year)
);

CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    subject_name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    student_number VARCHAR(50) UNIQUE NOT NULL,
    lrn VARCHAR(12) NULL,
    section_id INT NULL,
    year_level VARCHAR(50) NOT NULL,
    education_level ENUM('senior_high', 'college') NOT NULL,
    strand_course VARCHAR(50) NOT NULL,
    enrollment_status ENUM('active', 'inactive', 'graduated', 'dropped') DEFAULT 'active',
    guardian_contact VARCHAR(20) NULL,
    guardian VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE RESTRICT,
    INDEX idx_students_user_id (user_id)
);

CREATE TABLE teacher_subject_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    section_id INT NOT NULL,
    year_level VARCHAR(50) NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    semester ENUM('First','Second') NOT NULL DEFAULT 'First',
    assigned_date DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    CONSTRAINT fk_teacher_section 
        FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_subject_section_semester (teacher_id, subject_id, year_level, school_year, semester, section_id)
);

CREATE TABLE student_subject_enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    semester ENUM('First', 'Second') NOT NULL,
    enrolled_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, subject_id, school_year, semester)
);

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
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_grade (student_id, subject_id, grading_period, semester, school_year)
);

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
);

CREATE TABLE section_subjects (
    section_id INT NOT NULL,
    subject_id INT NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (section_id, subject_id, school_year),
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);

CREATE TABLE class_schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    section_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(50) NULL,
    school_year VARCHAR(20) NOT NULL,
    semester ENUM('First', 'Second') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE,
    UNIQUE KEY unique_schedule (teacher_id, subject_id, section_id, day_of_week, start_time, school_year, semester)
);

CREATE TABLE student_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    section_id INT NOT NULL,
    assigned_by INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student (student_id),
    INDEX idx_section (section_id),
    INDEX idx_assigned_at (assigned_at)
);

-- one row per school year + education level + strand/course
CREATE TABLE fee_config (
    fee_id INT AUTO_INCREMENT PRIMARY KEY,
    school_year VARCHAR(20) NOT NULL,
    education_level ENUM('senior_high', 'college') NOT NULL,
    strand_course VARCHAR(50) NOT NULL,
    tuition_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    miscellaneous DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    other_fees DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_fee (school_year, education_level, strand_course)
);

CREATE TABLE student_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL UNIQUE,
    email VARCHAR(255) NULL,
    date_of_birth DATE NULL,
    gender ENUM('male', 'female', 'other') NULL,
    contact_number VARCHAR(20) NULL,
    home_address TEXT NULL,
    previous_school VARCHAR(255) NULL,
    special_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

CREATE TABLE enrollment_documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    psa_birth_certificate BOOLEAN DEFAULT FALSE,
    form_138_report_card BOOLEAN DEFAULT FALSE,
    good_moral_certificate BOOLEAN DEFAULT FALSE,
    id_pictures BOOLEAN DEFAULT FALSE,
    medical_certificate BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    UNIQUE KEY unique_docs (student_id, school_year)
);

-- payment snapshot recorded at enrollment time
CREATE TABLE enrollment_payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    semester ENUM('First', 'Second') NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    net_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'partial', 'paid') DEFAULT 'pending',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- each row is one cash collection recorded by registrar
CREATE TABLE payment_transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    notes TEXT NULL,
    received_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES enrollment_payments(payment_id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE school_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_year VARCHAR(20) NOT NULL,
    semester ENUM('First', 'Second') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    advanced_by INT NULL,
    advanced_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (advanced_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE student_section_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    section_id INT NULL,
    section_name VARCHAR(100) NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    semester ENUM('First', 'Second') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE SET NULL,
    UNIQUE KEY unique_snapshot (student_id, section_id, school_year, semester)
);