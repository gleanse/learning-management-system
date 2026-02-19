-- seed data - initial data for fresh database
-- run this after schema.sql
-- update amounts to match actual school tuition before going live

DELETE FROM fee_config;

INSERT INTO fee_config (school_year, education_level, strand_course, tuition_fee, miscellaneous, other_fees) VALUES
('1st Year', 'college', 'BSIT', 15000.00, 3000.00, 2000.00),
('2nd Year', 'college', 'BSIT', 15000.00, 3000.00, 2000.00),
('3rd Year', 'college', 'BSIT', 15000.00, 3000.00, 2000.00),
('4th Year', 'college', 'BSIT', 15000.00, 3000.00, 2000.00),
('1st Year', 'college', 'BSOA', 15000.00, 3000.00, 2000.00),
('2nd Year', 'college', 'BSOA', 15000.00, 3000.00, 2000.00),
('3rd Year', 'college', 'BSOA', 15000.00, 3000.00, 2000.00),
('4th Year', 'college', 'BSOA', 15000.00, 3000.00, 2000.00),
('1st Year', 'college', 'BSHM', 18000.00, 3500.00, 2000.00),
('2nd Year', 'college', 'BSHM', 18000.00, 3500.00, 2000.00),
('3rd Year', 'college', 'BSHM', 18000.00, 3500.00, 2000.00),
('4th Year', 'college', 'BSHM', 18000.00, 3500.00, 2000.00),
('1st Year', 'college', 'ACT',  18000.00, 3500.00, 2000.00),
('2nd Year', 'college', 'ACT',  18000.00, 3500.00, 2000.00),
('3rd Year', 'college', 'ACT',  18000.00, 3500.00, 2000.00),
('4th Year', 'college', 'ACT',  18000.00, 3500.00, 2000.00),
('Grade 11', 'senior_high', 'STEM', 12000.00, 2500.00, 1500.00),
('Grade 12', 'senior_high', 'STEM', 12000.00, 2500.00, 1500.00),
('Grade 11', 'senior_high', 'ABM',  12000.00, 2500.00, 1500.00),
('Grade 12', 'senior_high', 'ABM',  12000.00, 2500.00, 1500.00),
('Grade 11', 'senior_high', 'HUMSS',12000.00, 2500.00, 1500.00),
('Grade 12', 'senior_high', 'HUMSS',12000.00, 2500.00, 1500.00),
('Grade 11', 'senior_high', 'GAS',  12000.00, 2500.00, 1500.00),
('Grade 12', 'senior_high', 'GAS',  12000.00, 2500.00, 1500.00),
('Grade 11', 'senior_high', 'TVL',  12000.00, 2500.00, 1500.00),
('Grade 12', 'senior_high', 'TVL',  12000.00, 2500.00, 1500.00),
('Grade 11', 'senior_high', 'ICT',  12000.00, 2500.00, 1500.00),
('Grade 12', 'senior_high', 'ICT',  12000.00, 2500.00, 1500.00);