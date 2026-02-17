-- link students to the students table.
-- NOTE: student2 and student4 are intentionally left without a section.
INSERT INTO students (user_id, student_number, lrn, section_id, year_level, education_level, strand_course, enrollment_status, guardian_contact, guardian, created_at, updated_at)
VALUES
-- student1 - college BSIT 2A (Assigned)
(
    (SELECT id FROM users WHERE username = 'student1'),
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
-- student2 - college BSIT 2B (Unassigned)
(
    (SELECT id FROM users WHERE username = 'student2'),
    '2025-00002',
    NULL,
    NULL, -- section is null, student is unassigned
    '2nd Year',
    'college',
    'BSIT',
    'active',
    NULL,
    NULL,
    NOW(),
    NOW()
),
-- student3 - shs grade 11 STEM (Assigned)
(
    (SELECT id FROM users WHERE username = 'student3'),
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
    '2025-00004',
    '123456789013',
    NULL, -- section is null, student is unassigned
    'Grade 12',
    'senior_high',
    'HUMSS',
    'active',
    NULL,
    NULL,
    NOW(),
    NOW()
);


-- log ONLY the initial assignments that were actually made.
-- this assumes you have at least one admin user in your users seed.
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
