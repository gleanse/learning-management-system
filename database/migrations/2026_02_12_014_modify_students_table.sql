-- add strand_course to students table
ALTER TABLE students 
ADD COLUMN strand_course VARCHAR(50) NOT NULL DEFAULT 'N/A' AFTER education_level;

-- remove default after adding the column
ALTER TABLE students 
ALTER COLUMN strand_course DROP DEFAULT;