-- updated students seed with name fields
-- compatible with migrated schema

INSERT INTO students (
    user_id, 
    first_name, 
    middle_name, 
    last_name, 
    student_number, 
    lrn, 
    section_id, 
    year_level, 
    education_level, 
    strand_course, 
    enrollment_status, 
    guardian_contact, 
    guardian, 
    created_at, 
    updated_at
)
VALUES
-- student1 - college BSIT 2A (assigned)
(
    (SELECT id FROM users WHERE username = 'student1'),
    'Nicka',
    'Garcia',
    'Reyes',
    '2025-00001',
    NULL,
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    '2nd Year',
    'college',
    'BSIT',
    'active',
    '09171234567',
    'Maria Dela Cruz',
    NOW(),
    NOW()
),
-- student2 - college BSIT (unassigned)
(
    (SELECT id FROM users WHERE username = 'student2'),
    'Pedro',
    'Cruz',
    'Ramos',
    '2025-00002',
    NULL,
    NULL,
    '2nd Year',
    'college',
    'BSIT',
    'active',
    NULL,
    NULL,
    NOW(),
    NOW()
),
-- student3 - shs grade 11 STEM (assigned)
(
    (SELECT id FROM users WHERE username = 'student3'),
    'Ana',
    'Marie',
    'Torres',
    '2025-00003',
    '123456789012',
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Grade 11',
    'senior_high',
    'STEM',
    'active',
    '09981234567',
    'Juan Santos',
    NOW(),
    NOW()
),
-- student4 - shs grade 12 HUMSS (unassigned)
(
    (SELECT id FROM users WHERE username = 'student4'),
    'Carlos',
    'David',
    'Gonzales',
    '2025-00004',
    '123456789013',
    NULL,
    'Grade 12',
    'senior_high',
    'HUMSS',
    'active',
    NULL,
    NULL,
    NOW(),
    NOW()
);

-- enrolled student WITHOUT user account

INSERT INTO students (
    user_id,
    first_name,
    middle_name,
    last_name,
    student_number,
    lrn,
    section_id,
    year_level,
    education_level,
    strand_course,
    enrollment_status,
    guardian_contact,
    guardian,
    created_at,
    updated_at
)
VALUES
(
    NULL,
    'John',
    'Michael',
    'Dela Cruz',
    '2025-00005',
    '123456789014',
    NULL,
    'Grade 11',
    'senior_high',
    'STEM',
    'active',
    '09181234567',
    'Maria Dela Cruz',
    NOW(),
    NOW()
);


-- log only the initial assignments that were actually made
INSERT INTO student_assignments (student_id, section_id, assigned_by, assigned_at)
VALUES
(
    (SELECT student_id FROM students WHERE student_number = '2025-00001'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    (SELECT id FROM users WHERE role = 'admin' LIMIT 1),
    NOW()
),
(
    (SELECT student_id FROM students WHERE student_number = '2025-00003'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    (SELECT id FROM users WHERE role = 'admin' LIMIT 1),
    NOW()
);

-- student profiles seed for all 5 students

INSERT INTO student_profiles (
    student_id,
    email,
    date_of_birth,
    gender,
    contact_number,
    home_address,
    previous_school,
    special_notes,
    created_at,
    updated_at
)
VALUES
-- nicka garcia reyes
(
    (SELECT student_id FROM students WHERE student_number = '2025-00001'),
    'nicka.reyes@email.com',
    '2003-05-15',
    'female',
    '09171234567',
    '123 Rizal St., Quezon City',
    'Quezon City Science High School',
    NULL,
    NOW(),
    NOW()
),
-- pedro cruz ramos
(
    (SELECT student_id FROM students WHERE student_number = '2025-00002'),
    'pedro.ramos@email.com',
    '2003-08-22',
    'male',
    NULL,
    '456 Mabini Ave., Manila',
    'Manila High School',
    NULL,
    NOW(),
    NOW()
),
-- ana marie torres
(
    (SELECT student_id FROM students WHERE student_number = '2025-00003'),
    'ana.torres@email.com',
    '2007-02-10',
    'female',
    '09981234567',
    '789 Bonifacio St., Makati',
    'Makati Science High School',
    NULL,
    NOW(),
    NOW()
),
-- carlos david gonzales
(
    (SELECT student_id FROM students WHERE student_number = '2025-00004'),
    NULL,
    '2006-11-30',
    'male',
    NULL,
    '321 Luna St., Pasig',
    'Pasig High School',
    NULL,
    NOW(),
    NOW()
),
-- john michael dela cruz
(
    (SELECT student_id FROM students WHERE student_number = '2025-00005'),
    NULL,
    '2007-07-04',
    'male',
    '09181234567',
    '654 Aguinaldo St., Caloocan',
    'Caloocan National High School',
    NULL,
    NOW(),
    NOW()
);
