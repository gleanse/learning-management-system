-- seed sample subjects
INSERT INTO subjects (subject_code, subject_name, description, created_at, updated_at) 
VALUES 
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
);