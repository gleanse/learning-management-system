-- add 'registrar' to the role ENUM
ALTER TABLE users 
MODIFY COLUMN role ENUM('student', 'teacher', 'registrar', 'admin', 'superadmin') NOT NULL;