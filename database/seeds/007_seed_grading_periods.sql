-- set up grading periods for first semester 2025-2026
INSERT INTO grading_periods (school_year, semester, grading_period, deadline_date, is_locked, created_at, updated_at)
VALUES
('2025-2026', 'First', 'Prelim', '2026-1-15', FALSE, NOW(), NOW()),
('2025-2026', 'First', 'Midterm', '2026-2-30', FALSE, NOW(), NOW()),
('2025-2026', 'First', 'Prefinal', '2026-3-15', FALSE, NOW(), NOW()),
('2025-2026', 'First', 'Final', '2026-04-28', FALSE, NOW(), NOW());