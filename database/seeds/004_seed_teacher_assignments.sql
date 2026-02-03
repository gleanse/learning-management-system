-- teacher - subject - section assignments
INSERT INTO teacher_subject_assignments (teacher_id, subject_id, section_id, year_level, school_year, semester, assigned_date, created_at, updated_at)
VALUES
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
-- teacher1 teaches PROG1 to BSIT 2B (same teacher, same subject, different section)
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
);