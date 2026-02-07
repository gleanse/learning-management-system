-- enroll students in appropriate subjects based on their strand/course
INSERT INTO student_subject_enrollments (student_id, subject_id, school_year, semester, enrolled_date, created_at, updated_at)
VALUES
-- student1 enrollments (BSIT 2A - college)
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
),
-- student2 enrollments (BSIT 2B - college)
(
    (SELECT student_id FROM students WHERE student_number = '2025-00002'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
(
    (SELECT student_id FROM students WHERE student_number = '2025-00002'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
-- student3 enrollments (Grade 11 - STEM A)
(
    (SELECT student_id FROM students WHERE student_number = '2025-00003'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'GEN-MATH'),
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
(
    (SELECT student_id FROM students WHERE student_number = '2025-00003'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PRE-CAL'),
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
(
    (SELECT student_id FROM students WHERE student_number = '2025-00003'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'ENGLISH'),
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
-- student4 enrollments (Grade 12 - HUMSS A)
(
    (SELECT student_id FROM students WHERE student_number = '2025-00004'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PHIL-HIST'),
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
(
    (SELECT student_id FROM students WHERE student_number = '2025-00004'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'SOC-SCI'),
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
),
(
    (SELECT student_id FROM students WHERE student_number = '2025-00004'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'FILIPINO'),
    '2025-2026',
    'First',
    NOW(),
    NOW(),
    NOW()
);
