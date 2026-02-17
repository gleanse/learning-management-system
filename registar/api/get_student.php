<?php
// api/get_student.php
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Student ID required']);
    exit;
}

$student_id = (int)$_GET['id'];

$query = $conn->query("
    SELECT s.*, sec.section_name, u.first_name, u.middle_name, u.last_name
    FROM students s
    JOIN sections sec ON s.section_id = sec.section_id
    JOIN users u ON s.user_id = u.id
    WHERE s.student_id = $student_id
");

if ($query->num_rows == 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Student not found']);
    exit;
}

$student = $query->fetch_assoc();

// Get all sections
$sections_query = $conn->query("SELECT * FROM sections ORDER BY section_name");
$sections = [];
while ($sec = $sections_query->fetch_assoc()) {
    $sections[] = $sec;
}

echo json_encode([
    'student' => $student,
    'sections' => $sections
]);
?>
