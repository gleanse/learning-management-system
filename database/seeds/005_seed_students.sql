-- link students to the students table with sections
INSERT INTO students (user_id, student_number, lrn, section_id, year_level, education_level, enrollment_status, created_at, updated_at)
VALUES
-- student1 - college BSIT 2A
(
    (SELECT id FROM users WHERE username = 'student1'),
    '2025-00001',
    NULL,
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    '2nd Year',
    'college',
    'active',
    NOW(),
    NOW()
),
-- student2 - college BSIT 2B
(
    (SELECT id FROM users WHERE username = 'student2'),
    '2025-00002',
    NULL,
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2B'),
    '2nd Year',
    'college',
    'active',
    NOW(),
    NOW()
),
-- student3 - shs grade 11 STEM
(
    (SELECT id FROM users WHERE username = 'student3'),
    '2025-00003',
    '123456789012',
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Grade 11',
    'senior_high',
    'active',
    NOW(),
    NOW()
),
-- student4 - shs grade 12 HUMSS
(
    (SELECT id FROM users WHERE username = 'student4'),
    '2025-00004',
    '123456789013',
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    'Grade 12',
    'senior_high',
    'active',
    NOW(),
    NOW()
);
