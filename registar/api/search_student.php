<?php
// api/search_student.php
require_once '../config/database.php';

if (!isset($_POST['search'])) {
    http_response_code(400);
    echo '<div class="alert alert-danger">Search parameter missing</div>';
    exit;
}

$search = $conn->real_escape_string(trim($_POST['search']));

if (strlen($search) < 3) {
    echo '';
    exit;
}

$query = $conn->query("
    SELECT s.student_id, s.student_number, 
           u.first_name, u.middle_name, u.last_name,
           s.year_level, sec.section_name
    FROM students s
    JOIN users u ON s.user_id = u.id
    JOIN sections sec ON s.section_id = sec.section_id
    WHERE s.enrollment_status = 'active'
    AND (s.student_number LIKE '%$search%' 
         OR u.first_name LIKE '%$search%' 
         OR u.last_name LIKE '%$search%')
    ORDER BY u.last_name, u.first_name
    LIMIT 10
");

if (!$query) {
    http_response_code(500);
    echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($conn->error) . '</div>';
    exit;
}

$count = 0;
while($row = $query->fetch_assoc()) {
    $count++;
    $middle = $row['middle_name'] ? ' ' . $row['middle_name'] : '';
    $name = $row['last_name'] . ', ' . $row['first_name'] . $middle;
    $details = $row['student_number'] . ' | ' . $row['year_level'] . ' | ' . $row['section_name'];
    
    echo '<a href="#" class="list-group-item list-group-item-action student-result" 
          data-id="' . $row['student_id'] . '"
          data-name="' . htmlspecialchars($name) . '"
          data-details="' . htmlspecialchars($details) . '">';
    echo '<strong>' . htmlspecialchars($name) . '</strong><br>';
    echo '<small>' . htmlspecialchars($details) . '</small>';
    echo '</a>';
}

if ($count == 0) {
    echo '<div class="alert alert-info small mb-0">No active students found matching "' . htmlspecialchars($_POST['search']) . '"</div>';
}
?>