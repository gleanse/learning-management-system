-- enroll student1 in programming 1 and data structure and algorithm
INSERT INTO student_subject_enrollments (student_id, subject_id, school_year, semester, enrolled_date, created_at, updated_at)
VALUES
(
    (SELECT student_id FROM students WHERE student_number = '2025-00001'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
(
    (SELECT student_id FROM students WHERE student_number = '2025-00001'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
);
