-- migration to allow students without sections (for assignment feature)
-- this makes section_id nullable so students can exist in unassigned state

ALTER TABLE students 
MODIFY COLUMN section_id INT NULL;