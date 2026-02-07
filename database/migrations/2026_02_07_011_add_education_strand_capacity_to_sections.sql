-- add education_level, strand_course, max_capacity to sections table
ALTER TABLE sections
ADD COLUMN education_level ENUM('senior_high', 'college') NOT NULL AFTER section_name,
ADD COLUMN strand_course VARCHAR(50) NOT NULL AFTER year_level,
ADD COLUMN max_capacity INT NULL AFTER strand_course;
