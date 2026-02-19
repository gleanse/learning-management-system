INSERT INTO enrollment_payments (student_id, school_year, semester, total_amount, discount_amount, net_amount, status, created_by)
SELECT 
    s.student_id,
    '2025-2026',
    'First',
    (fc.tuition_fee + fc.miscellaneous + fc.other_fees),
    0.00,
    (fc.tuition_fee + fc.miscellaneous + fc.other_fees),
    'pending',
    1
FROM students s
INNER JOIN fee_config fc 
    ON fc.education_level = s.education_level 
    AND fc.strand_course = s.strand_course
    AND fc.school_year = s.year_level
WHERE s.enrollment_status = 'active'
AND s.student_id NOT IN (
    SELECT student_id FROM enrollment_payments WHERE school_year = '2025-2026'
);
