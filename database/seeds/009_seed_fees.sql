-- seed data - initial data for fresh database
-- run this after schema.sql
-- update amounts to match actual school tuition before going live

INSERT INTO fee_config (school_year, education_level, strand_course, tuition_fee, miscellaneous, other_fees) VALUES
('2025-2026', 'college',     'BSIT', 15000.00, 3000.00, 2000.00),
('2025-2026', 'college',     'BSOA', 15000.00, 3000.00, 2000.00),
('2025-2026', 'college',     'BSHM',  18000.00, 3500.00, 2000.00),
('2025-2026', 'college',     'ACT',  18000.00, 3500.00, 2000.00),
('2025-2026', 'senior_high', 'STEM', 12000.00, 2500.00, 1500.00),
('2025-2026', 'senior_high', 'ABM',  12000.00, 2500.00, 1500.00),
('2025-2026', 'senior_high', 'HUMSS',12000.00, 2500.00, 1500.00),
('2025-2026', 'senior_high', 'GAS',  12000.00, 2500.00, 1500.00),
('2025-2026', 'senior_high', 'TVL',  12000.00, 2500.00, 1500.00);
('2025-2026', 'senior_high', 'ICT',  12000.00, 2500.00, 1500.00);