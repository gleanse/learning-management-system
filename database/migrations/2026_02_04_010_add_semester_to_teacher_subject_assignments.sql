-- Add semester column
ALTER TABLE teacher_subject_assignments 
ADD COLUMN semester ENUM('First','Second') NOT NULL AFTER school_year;

-- Drop old unique constraint
ALTER TABLE teacher_subject_assignments 
DROP INDEX unique_teacher_subject_section;

-- Add new unique constraint with semester
ALTER TABLE teacher_subject_assignments 
ADD UNIQUE KEY unique_teacher_subject_section 
(teacher_id, subject_id, section_id, year_level, school_year, semester);
