ALTER TABLE users 
ADD COLUMN setup_token VARCHAR(64) NULL,
ADD COLUMN setup_expires DATETIME NULL,
ADD COLUMN password_status ENUM('pending', 'active') DEFAULT 'active';