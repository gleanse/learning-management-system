-- seed academic period - run after schema.sql and other seeds
-- change school_year and semester as needed before running

INSERT INTO school_settings (school_year, semester, is_active, advanced_by, advanced_at)
VALUES (
    '2025-2026',
    'First',
    TRUE,
    (SELECT id FROM users WHERE role = 'admin' LIMIT 1),
    NOW()
);
