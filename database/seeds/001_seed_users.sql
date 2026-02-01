-- seed sample teacher user (password: password)
INSERT INTO users (username, email, password, role, status, first_name, middle_name, last_name, created_at, updated_at) 
VALUES (
    'teacher1',
    'teacher1@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'teacher',
    'active',
    'Juan',
    'Santos',
    'Dela Cruz',
    NOW(),
    NOW()
);

-- seed sample student user (password: password)
INSERT INTO users (username, email, password, role, status, first_name, middle_name, last_name, created_at, updated_at) 
VALUES (
    'student1',
    'student1@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'student',
    'active',
    'Nicka',
    'Garcia',
    'Reyes',
    NOW(),
    NOW()
);

-- seed sample admin user (password: password)
INSERT INTO users (username, email, password, role, status, first_name, middle_name, last_name, created_at, updated_at) 
VALUES (
    'admin1',
    'admin@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'active',
    'Admin',
    'System',
    'User',
    NOW(),
    NOW()
);