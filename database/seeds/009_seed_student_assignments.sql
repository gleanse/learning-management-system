-- seed data for student_assignments table
-- this shows assignment history for the students that were already assigned to sections
-- simulates that an admin assigned these students previously

-- IMPORTANT: run this AFTER inserting users (admin1 must exist first)

INSERT INTO student_assignments (student_id, section_id, assigned_by, assigned_at)
VALUES
-- student1 assigned to BSIT 2A by admin1
(
    (SELECT student_id FROM students WHERE student_number = '2025-00001'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'),
    (SELECT id FROM users WHERE username = 'admin1'),
    DATE_SUB(NOW(), INTERVAL 5 DAY)
),
-- student2 assigned to BSIT 2B by admin1
(
    (SELECT student_id FROM students WHERE student_number = '2025-00002'),
    (SELECT section_id FROM sections WHERE section_name = 'BSIT 2B'),
    (SELECT id FROM users WHERE username = 'admin1'),
    DATE_SUB(NOW(), INTERVAL 4 DAY)
),
-- student3 assigned to Grade 11 STEM A by admin1
(
    (SELECT student_id FROM students WHERE student_number = '2025-00003'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'),
    (SELECT id FROM users WHERE username = 'admin1'),
    DATE_SUB(NOW(), INTERVAL 3 DAY)
),
-- student4 assigned to Grade 12 HUMSS A by admin1
(
    (SELECT student_id FROM students WHERE student_number = '2025-00004'),
    (SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'),
    (SELECT id FROM users WHERE username = 'admin1'),
    DATE_SUB(NOW(), INTERVAL 2 DAY)
);