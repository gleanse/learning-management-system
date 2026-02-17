<?php
// api/get_student_installments.php
require_once '../config/database.php';

header('Content-Type: application/json');

$student_id = (int)$_POST['student_id'];

$query = "
    SELECT 
        pi.id,
        pi.installment_name,
        pi.installment_number,
        pi.amount_due,
        pi.amount_paid,
        pi.status,
        e.semester,
        e.term_id,
        e.year_level,
        sec.section_name
    FROM payment_installments pi
    JOIN enrollments e ON pi.enrollment_id = e.id
    JOIN sections sec ON e.section_id = sec.section_id
    WHERE e.student_id = $student_id
    AND pi.status != 'paid'
    ORDER BY e.semester, pi.installment_number
";

$result = $conn->query($query);
$installments = [];

while($row = $result->fetch_assoc()) {
    $installments[] = $row;
}

echo json_encode([
    'success' => true,
    'installments' => $installments
]);