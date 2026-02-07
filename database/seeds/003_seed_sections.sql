-- seed sample sections
INSERT INTO sections (section_name, education_level, year_level, strand_course, max_capacity, school_year, created_at, updated_at)
VALUES
-- college sections
(
    'BSIT 2A',
    'college',
    '2nd Year',
    'BSIT',
    35,
    '2025-2026',
    NOW(),
    NOW()
),
(
    'BSIT 2B',
    'college',
    '2nd Year',
    'BSIT',
    35,
    '2025-2026',
    NOW(),
    NOW()
),
-- shs sections
(
    'Grade 11 - STEM A',
    'senior_high',
    'Grade 11',
    'STEM',
    40,
    '2025-2026',
    NOW(),
    NOW()
),
(
    'Grade 12 - HUMSS A',
    'senior_high',
    'Grade 12',
    'HUMSS',
    40,
    '2025-2026',
    NOW(),
    NOW()
);
