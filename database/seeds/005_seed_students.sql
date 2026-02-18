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
