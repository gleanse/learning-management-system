-- fix unique constraint on sections to include year_level
ALTER TABLE sections 
DROP INDEX unique_section,
ADD UNIQUE KEY unique_section (section_name, year_level, school_year);

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