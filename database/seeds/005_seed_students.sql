-- link student1 to the students table with section (no lrn for college students)
INSERT INTO students (user_id, student_number, lrn, section_id, year_level, education_level, enrollment_status, created_at, updated_at)
VALUES
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
);