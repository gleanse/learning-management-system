-- seed class schedules for teacher1 teaching PROG1 and DSA to BSIT 2A
-- Programming 1 schedule: Monday and Wednesday 8:00 AM - 9:30 AM
INSERT INTO class_schedules (teacher_id, subject_id, section_id, day_of_week, start_time, end_time, room, school_year, semester, created_at, updated_at)
VALUES
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    'Monday',
    '08:00:00',
    '09:30:00',
    'Room 201',
    '2025-2026',
    'First',
    NOW(),
    NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    'Wednesday',
    '08:00:00',
    '09:30:00',
    'Room 201',
    '2025-2026',
    'First',
    NOW(),
    NOW()
),
-- Data Structure and Algorithm schedule: Monday and Wednesday 1:00 PM - 2:30 PM
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    'Monday',
    '13:00:00',
    '14:30:00',
    NULL,  -- no room assigned
    '2025-2026',
    'First',
    NOW(),
    NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    'Wednesday',
    '13:00:00',
    '14:30:00',
    NULL,  -- no room assigned
    '2025-2026',
    'First',
    NOW(),
    NOW()
);