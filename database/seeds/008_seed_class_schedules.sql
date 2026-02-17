-- seed class schedules for all teachers
-- pattern: MWF (mon/wed/fri) or TThs (tue/thu/sat) only — no conflicts on teacher time or room
INSERT INTO class_schedules (teacher_id, subject_id, section_id, day_of_week, start_time, end_time, room, school_year, semester, created_at, updated_at)
VALUES

-- teacher1
-- PROG1 → BSIT 2A  |  MWF  |  08:00-09:30  |  Room 201
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    'Monday', '08:00:00', '09:30:00', 'Room 201', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    'Wednesday', '08:00:00', '09:30:00', 'Room 201', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    'Friday', '08:00:00', '09:30:00', 'Room 201', '2025-2026', 'First', NOW(), NOW()
),

-- PROG1 → BSIT 2B  |  TThs  |  08:00-09:30  |  Room 202
-- (different room from 2A, teacher free on TThs at this time)
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2B'),
    'Tuesday', '08:00:00', '09:30:00', 'Room 202', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2B'),
    'Thursday', '08:00:00', '09:30:00', 'Room 202', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2B'),
    'Saturday', '08:00:00', '09:30:00', 'Room 202', '2025-2026', 'First', NOW(), NOW()
),

-- DSA → BSIT 2A  |  TThs  |  10:00-11:30  |  Room 201
-- (teacher free on TThs at 10:00, room 201 free on TThs at 10:00)
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    'Tuesday', '10:00:00', '11:30:00', 'Room 201', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    'Thursday', '10:00:00', '11:30:00', 'Room 201', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    'Saturday', '10:00:00', '11:30:00', 'Room 201', '2025-2026', 'First', NOW(), NOW()
),

-- DSA → BSIT 2B  |  MWF  |  10:00-11:30  |  Room 202
-- (teacher free on MWF at 10:00, room 202 free on MWF at 10:00)
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2B'),
    'Monday', '10:00:00', '11:30:00', 'Room 202', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2B'),
    'Wednesday', '10:00:00', '11:30:00', 'Room 202', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher1'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2B'),
    'Friday', '10:00:00', '11:30:00', 'Room 202', '2025-2026', 'First', NOW(), NOW()
),

-- teacher2
-- GEN-MATH → Grade 11 - STEM A  |  MWF  |  08:00-09:30  |  Room 301
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'GEN-MATH'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Monday', '08:00:00', '09:30:00', 'Room 301', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'GEN-MATH'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Wednesday', '08:00:00', '09:30:00', 'Room 301', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'GEN-MATH'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Friday', '08:00:00', '09:30:00', 'Room 301', '2025-2026', 'First', NOW(), NOW()
),

-- PRE-CAL → Grade 11 - STEM A  |  TThs  |  08:00-09:30  |  Room 301
-- (teacher free on TThs at 08:00, same room ok — different days from GEN-MATH)
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PRE-CAL'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Tuesday', '08:00:00', '09:30:00', 'Room 301', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PRE-CAL'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Thursday', '08:00:00', '09:30:00', 'Room 301', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PRE-CAL'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Saturday', '08:00:00', '09:30:00', 'Room 301', '2025-2026', 'First', NOW(), NOW()
),

-- ENGLISH → Grade 11 - STEM A  |  MWF  |  10:00-11:30  |  Room 301
-- (teacher free on MWF at 10:00, room 301 free at 10:00 on all days)
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'ENGLISH'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Monday', '10:00:00', '11:30:00', 'Room 301', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'ENGLISH'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Wednesday', '10:00:00', '11:30:00', 'Room 301', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'ENGLISH'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    'Friday', '10:00:00', '11:30:00', 'Room 301', '2025-2026', 'First', NOW(), NOW()
),

-- PHIL-HIST → Grade 12 - HUMSS A  |  TThs  |  10:00-11:30  |  Room 302
-- (teacher free on TThs at 10:00, different room from STEM A)
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PHIL-HIST'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    'Tuesday', '10:00:00', '11:30:00', 'Room 302', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PHIL-HIST'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    'Thursday', '10:00:00', '11:30:00', 'Room 302', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'PHIL-HIST'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    'Saturday', '10:00:00', '11:30:00', 'Room 302', '2025-2026', 'First', NOW(), NOW()
),

-- SOC-SCI → Grade 12 - HUMSS A  |  MWF  |  13:00-14:30  |  Room 302
-- (teacher free on MWF at 13:00, room 302 free at 13:00)
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'SOC-SCI'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    'Monday', '13:00:00', '14:30:00', 'Room 302', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'SOC-SCI'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    'Wednesday', '13:00:00', '14:30:00', 'Room 302', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'SOC-SCI'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    'Friday', '13:00:00', '14:30:00', 'Room 302', '2025-2026', 'First', NOW(), NOW()
),

-- FILIPINO → Grade 12 - HUMSS A  |  TThs  |  13:00-14:30  |  Room 302
-- (teacher free on TThs at 13:00, room 302 free on TThs at 13:00)
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'FILIPINO'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    'Tuesday', '13:00:00', '14:30:00', 'Room 302', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'FILIPINO'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    'Thursday', '13:00:00', '14:30:00', 'Room 302', '2025-2026', 'First', NOW(), NOW()
),
(
    (SELECT id FROM users WHERE username = 'teacher2'),
    (SELECT subject_id FROM subjects WHERE subject_code = 'FILIPINO'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    'Saturday', '13:00:00', '14:30:00', 'Room 302', '2025-2026', 'First', NOW(), NOW()
);
