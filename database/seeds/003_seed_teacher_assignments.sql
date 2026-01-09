-- assign teacher1 to programming 1 and data structure and algorithm (school year 2025-2026)
INSERT INTO teacher_subject_assignments (teacher_id, subject_id, school_year, assigned_date, created_at, updated_at)
VALUES
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    '2025-2026',
    NOW(),
    NOW(),
    NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    '2025-2026',
    NOW(),
    NOW(),
    NOW()
);