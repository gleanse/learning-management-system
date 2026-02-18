-- migration: add name fields to students table and allow null user_id

-- add name columns to students table
ALTER TABLE students
ADD COLUMN first_name VARCHAR(100) NULL AFTER user_id,
ADD COLUMN middle_name VARCHAR(100) NULL AFTER first_name,
ADD COLUMN last_name VARCHAR(100) NULL AFTER middle_name;

-- migrate existing data from users to students
UPDATE students s
INNER JOIN users u ON s.user_id = u.id
SET 
    s.first_name = u.first_name,
    s.middle_name = u.middle_name,
    s.last_name = u.last_name
WHERE s.user_id IS NOT NULL;

-- make name fields required (after data migration)
ALTER TABLE students
MODIFY COLUMN first_name VARCHAR(100) NOT NULL,
MODIFY COLUMN last_name VARCHAR(100) NOT NULL;

-- allow null user_id for students without accounts
ALTER TABLE students
MODIFY COLUMN user_id INT NULL;

-- update foreign key constraint to set null on delete
-- first, find the constraint name
SELECT CONSTRAINT_NAME 
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_NAME = 'students' 
AND COLUMN_NAME = 'user_id' 
AND REFERENCED_TABLE_NAME = 'users';

-- drop existing foreign key (replace 'students_ibfk_1' with actual constraint name from query above)
ALTER TABLE students
DROP FOREIGN KEY students_ibfk_1;

-- add new foreign key with SET NULL on delete
ALTER TABLE students
ADD CONSTRAINT fk_students_user_id 
    FOREIGN KEY (user_id) REFERENCES users(id) 
    ON DELETE SET NULL;

-- add index for performance when querying students without accounts
ALTER TABLE students
ADD INDEX idx_students_user_id (user_id);