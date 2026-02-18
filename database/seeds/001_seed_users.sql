-- seed sample teacher users (password: password)
INSERT INTO users (username, email, password, role, status, first_name, middle_name, last_name, created_at, updated_at) 
VALUES 
(
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
),
(
    'teacher2',
    'teacher2@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'teacher',
    'active',
    'Maria',
    'Lopez',
    'Santos',
    NOW(),
    NOW()
);

-- seed sample student users (password: password)
INSERT INTO users (username, email, password, role, status, first_name, middle_name, last_name, created_at, updated_at) 
VALUES 
(
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
),
(
    'student2',
    'student2@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'student',
    'active',
    'Pedro',
    'Cruz',
    'Ramos',
    NOW(),
    NOW()
),
(
    'student3',
    'student3@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'student',
    'active',
    'Ana',
    'Marie',
    'Torres',
    NOW(),
    NOW()
),
(
    'student4',
    'student4@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'student',
    'active',
    'Carlos',
    'David',
    'Gonzales',
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

-- seed sample registrar user (password: password)
INSERT INTO users (username, email, password, role, status, first_name, middle_name, last_name, created_at, updated_at) 
VALUES (
    'registrar1',
    'registrar1@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'registrar',
    'active',
    'Rosa',
    'Dela',
    'Cruz',
    NOW(),
    NOW()
);

-- seed sample superadmin user (password: password)
INSERT INTO users (username, email, password, role, status, first_name, middle_name, last_name, created_at, updated_at) 
VALUES (
    'superadmin1',
    'superadmin@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'superadmin',
    'active',
    'Super',
    'Admin',
    'User',
    NOW(),
    NOW()
);
