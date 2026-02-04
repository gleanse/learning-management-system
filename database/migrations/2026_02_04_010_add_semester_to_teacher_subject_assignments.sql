-- add semester column
ALTER TABLE teacher_subject_assignments 
ADD COLUMN semester ENUM('First','Second') NOT NULL DEFAULT 'First' 
AFTER school_year;

-- add new unique constraint with semester (use DIFFERENT name)
ALTER TABLE teacher_subject_assignments 
ADD UNIQUE KEY unique_teacher_subject_section_semester 
(teacher_id, subject_id, year_level, school_year, semester, section_id);

-- drop the previous unique constraint (the one WITHOUT semester)
ALTER TABLE teacher_subject_assignments 
DROP INDEX unique_teacher_subject_section;