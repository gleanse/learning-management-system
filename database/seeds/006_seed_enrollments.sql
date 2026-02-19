-- ============================================================
-- enrollment seed
-- section_subjects must run before student_subject_enrollments
-- ============================================================

-- section subjects
INSERT INTO section_subjects (section_id, subject_id, school_year)
VALUES
-- BSIT 2A subjects
((SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'), (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'), '2025-2026'),
((SELECT section_id FROM sections WHERE section_name = 'BSIT 2A'), (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'), '2025-2026'),

-- BSIT 2B subjects
((SELECT section_id FROM sections WHERE section_name = 'BSIT 2B'), (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'), '2025-2026'),
((SELECT section_id FROM sections WHERE section_name = 'BSIT 2B'), (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'), '2025-2026'),

-- Grade 11 STEM A subjects
((SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'), (SELECT subject_id FROM subjects WHERE subject_code = 'GEN-MATH'), '2025-2026'),
((SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'), (SELECT subject_id FROM subjects WHERE subject_code = 'PRE-CAL'), '2025-2026'),
((SELECT section_id FROM sections WHERE section_name = 'Grade 11 - STEM A'), (SELECT subject_id FROM subjects WHERE subject_code = 'ENGLISH'), '2025-2026'),

-- Grade 12 HUMSS A subjects
((SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'), (SELECT subject_id FROM subjects WHERE subject_code = 'PHIL-HIST'), '2025-2026'),
((SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'), (SELECT subject_id FROM subjects WHERE subject_code = 'SOC-SCI'), '2025-2026'),
((SELECT section_id FROM sections WHERE section_name = 'Grade 12 - HUMSS A'), (SELECT subject_id FROM subjects WHERE subject_code = 'FILIPINO'), '2025-2026');

-- ============================================================
-- student subject enrollments — mirrors section_subjects above
-- ============================================================

INSERT INTO student_subject_enrollments (student_id, subject_id, school_year, semester, enrolled_date, created_at, updated_at)
VALUES
-- student1 (BSIT 2A)
((SELECT student_id FROM students WHERE student_number = '2025-00001'), (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'), '2025-2026', 'First', CURDATE(), NOW(), NOW()),
((SELECT student_id FROM students WHERE student_number = '2025-00001'), (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'), '2025-2026', 'First', CURDATE(), NOW(), NOW()),

-- student2 (BSIT 2B)
((SELECT student_id FROM students WHERE student_number = '2025-00002'), (SELECT subject_id FROM subjects WHERE subject_code = 'PROG1'), '2025-2026', 'First', CURDATE(), NOW(), NOW()),
((SELECT student_id FROM students WHERE student_number = '2025-00002'), (SELECT subject_id FROM subjects WHERE subject_code = 'DSA'), '2025-2026', 'First', CURDATE(), NOW(), NOW()),

-- student3 (Grade 11 STEM A)
((SELECT student_id FROM students WHERE student_number = '2025-00003'), (SELECT subject_id FROM subjects WHERE subject_code = 'GEN-MATH'), '2025-2026', 'First', CURDATE(), NOW(), NOW()),
((SELECT student_id FROM students WHERE student_number = '2025-00003'), (SELECT subject_id FROM subjects WHERE subject_code = 'PRE-CAL'), '2025-2026', 'First', CURDATE(), NOW(), NOW()),
((SELECT student_id FROM students WHERE student_number = '2025-00003'), (SELECT subject_id FROM subjects WHERE subject_code = 'ENGLISH'), '2025-2026', 'First', CURDATE(), NOW(), NOW()),

-- student4 (Grade 12 HUMSS A)
((SELECT student_id FROM students WHERE student_number = '2025-00004'), (SELECT subject_id FROM subjects WHERE subject_code = 'PHIL-HIST'), '2025-2026', 'First', CURDATE(), NOW(), NOW()),
((SELECT student_id FROM students WHERE student_number = '2025-00004'), (SELECT subject_id FROM subjects WHERE subject_code = 'SOC-SCI'), '2025-2026', 'First', CURDATE(), NOW(), NOW()),
((SELECT student_id FROM students WHERE student_number = '2025-00004'), (SELECT subject_id FROM subjects WHERE subject_code = 'FILIPINO'), '2025-2026', 'First', CURDATE(), NOW(), NOW());