-- add status column to teacher_subject_assignments for tracking assignment state
ALTER TABLE teacher_subject_assignments
ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' NOT NULL AFTER assigned_date;
