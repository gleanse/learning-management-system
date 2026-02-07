-- seed sample subjects
INSERT INTO subjects (subject_code, subject_name, description, created_at, updated_at) 
VALUES 
-- college subjects (BSIT)
(
    'PROG1',
    'Programming 1',
    'Introduction to programming concepts and fundamentals',
    NOW(),
    NOW()
),
(
    'DSA',
    'Data Structure and Algorithm',
    'Study of data structures and algorithm design and analysis',
    NOW(),
    NOW()
),
-- senior high subjects (STEM)
(
    'GEN-MATH',
    'General Mathematics',
    'Fundamental concepts in mathematics for senior high',
    NOW(),
    NOW()
),
(
    'BASIC-CAL',
    'Basic Calculus',
    'Introduction to differential and integral calculus',
    NOW(),
    NOW()
),
(
    'PRE-CAL',
    'Pre-Calculus',
    'Preparation for calculus and advanced mathematics',
    NOW(),
    NOW()
),
-- senior high subjects (HUMSS)
(
    'PHIL-HIST',
    'Philippine History',
    'Study of Philippine history and culture',
    NOW(),
    NOW()
),
(
    'SOC-SCI',
    'Social Science',
    'Introduction to social sciences and humanities',
    NOW(),
    NOW()
),
(
    'CREATIVE-WRITING',
    'Creative Writing',
    'Development of creative writing skills',
    NOW(),
    NOW()
),
-- common senior high subjects
(
    'ENGLISH',
    'English',
    'English language and literature',
    NOW(),
    NOW()
),
(
    'FILIPINO',
    'Filipino',
    'Filipino language and literature',
    NOW(),
    NOW()
);
