CREATE TABLE announcements (
    announcement_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    target_type ENUM('all', 'role', 'student_year_level', 'student_education_level', 'student_strand_course') NOT NULL DEFAULT 'all',
    -- target_value stores the specific value depending on target_type:
    -- target_type = 'role'                   → e.g. 'teacher', 'registrar', 'student'
    -- target_type = 'student_year_level'     → e.g. 'Grade 11', '2nd Year'
    -- target_type = 'student_education_level'→ e.g. 'senior_high', 'college'
    -- target_type = 'student_strand_course'  → e.g. 'BSIT', 'STEM'
    -- target_type = 'all'                    → NULL
    target_value VARCHAR(100) NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    created_by INT NOT NULL,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_published_at (published_at)
);

-- one row per user per announcement — created at publish time
CREATE TABLE announcement_recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    announcement_id INT NOT NULL,
    user_id INT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (announcement_id) REFERENCES announcements(announcement_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_recipient (announcement_id, user_id),
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_announcement (announcement_id)
);
