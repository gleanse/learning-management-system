-- teacher - subject - section assignments
INSERT INTO teacher_subject_assignments (teacher_id, subject_id, section_id, year_level, school_year, semester, assigned_date, created_at, updated_at)
VALUES
-- teacher1 teaches college BSIT sections
-- teacher1 teaches PROG1 to BSIT 2A
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    '2nd Year',
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
-- teacher1 teaches PROG1 to BSIT 2B
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2B'),
    '2nd Year',
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
-- teacher1 teaches DSA to BSIT 2A
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    '2nd Year',
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
-- teacher1 teaches DSA to BSIT 2B
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2B'),
    '2nd Year',
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
-- teacher2 teaches SHS sections
-- teacher2 teaches GEN-MATH to Grade 11 - STEM A
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'GEN-MATH'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Grade 11',
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
-- teacher2 teaches PRE-CAL to Grade 11 - STEM A
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PRE-CAL'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Grade 11',
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
-- teacher2 teaches ENGLISH to Grade 11 - STEM A
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'ENGLISH'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Grade 11',
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
-- teacher2 teaches PHIL-HIST to Grade 12 - HUMSS A
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PHIL-HIST'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    'Grade 12',
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
-- teacher2 teaches SOC-SCI to Grade 12 - HUMSS A
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'SOC-SCI'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    'Grade 12',
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
-- teacher2 teaches FILIPINO to Grade 12 - HUMSS A
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'FILIPINO'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    'Grade 12',
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
);
