-- add section_id column to teacher_subject_assignments
ALTER TABLE teacher_subject_assignments
ADD COLUMN section_id INT NOT NULL AFTER subject_id,
ADD FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE;

-- drop the old unique constraint
ALTER TABLE teacher_subject_assignments
DROP INDEX unique_teacher_subject;

-- add new unique constraint that includes section_id
ALTER TABLE teacher_subject_assignments
ADD UNIQUE KEY unique_teacher_subject_section (teacher_id, subject_id, section_id, year_level, school_year);
