ALTER TABLE class_schedules 
ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' 
AFTER semester;