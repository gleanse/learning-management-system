-- seed data - initial data for fresh database
-- run this after schema.sql
-- update amounts to match actual school tuition before going live

DELETE FROM fee_config;

INSERT INTO fee_config (school_year, education_level, strand_course, tuition_fee, miscellaneous, other_fees) VALUES
-- college — BSIT (4 years)
('1st Year', 'college', 'BSIT', 18000.00, 0.00, 0.00),
('2nd Year', 'college', 'BSIT', 18000.00, 0.00, 0.00),
('3rd Year', 'college', 'BSIT', 18000.00, 0.00, 0.00),
('4th Year', 'college', 'BSIT', 18000.00, 0.00, 0.00),

-- college — BSOA (4 years)
('1st Year', 'college', 'BSOA', 18000.00, 0.00, 0.00),
('2nd Year', 'college', 'BSOA', 18000.00, 0.00, 0.00),
('3rd Year', 'college', 'BSOA', 18000.00, 0.00, 0.00),
('4th Year', 'college', 'BSOA', 18000.00, 0.00, 0.00),

-- college — BSHM (4 years)
('1st Year', 'college', 'BSHM', 20000.00, 0.00, 0.00),
('2nd Year', 'college', 'BSHM', 20000.00, 0.00, 0.00),
('3rd Year', 'college', 'BSHM', 20000.00, 0.00, 0.00),
('4th Year', 'college', 'BSHM', 20000.00, 0.00, 0.00),

-- college — ACT (2 years only)
('1st Year', 'college', 'ACT', 18000.00, 0.00, 0.00),
('2nd Year', 'college', 'ACT', 18000.00, 0.00, 0.00),

-- senior high — STEM
('Grade 11', 'senior_high', 'STEM', 20000.00, 0.00, 0.00),
('Grade 12', 'senior_high', 'STEM', 20000.00, 0.00, 0.00),

-- senior high — ABM
('Grade 11', 'senior_high', 'ABM', 20000.00, 0.00, 0.00),
('Grade 12', 'senior_high', 'ABM', 20000.00, 0.00, 0.00),

-- senior high — HUMSS
('Grade 11', 'senior_high', 'HUMSS', 20000.00, 0.00, 0.00),
('Grade 12', 'senior_high', 'HUMSS', 20000.00, 0.00, 0.00),

-- senior high — GAS
('Grade 11', 'senior_high', 'GAS', 20000.00, 0.00, 0.00),
('Grade 12', 'senior_high', 'GAS', 20000.00, 0.00, 0.00),

-- senior high — TVL
('Grade 11', 'senior_high', 'TVL', 20000.00, 0.00, 0.00),
('Grade 12', 'senior_high', 'TVL', 20000.00, 0.00, 0.00),

-- senior high — ICT
('Grade 11', 'senior_high', 'ICT', 20000.00, 0.00, 0.00),
('Grade 12', 'senior_high', 'ICT', 20000.00, 0.00, 0.00);
