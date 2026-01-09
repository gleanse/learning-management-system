-- assign teacher1 to programming 1 and data structure and algorithm for 2nd year (school year 2025-2026)
INSERT INTO teacher_subject_assignments (teacher_id, subject_id, year_level, school_year, assigned_date, created_at, updated_at)
VALUES
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    '2nd Year',
    '2025-2026',
    NOW(),
    NOW(),
    NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    '2nd Year',
    '2025-2026',
    NOW(),
    NOW(),
    NOW()
);