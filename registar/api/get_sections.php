<?php
// api/get_sections.php
require_once '../config/database.php';

$query = $conn->query("SELECT * FROM sections ORDER BY section_name");

$sections = [];
while ($row = $query->fetch_assoc()) {
    $sections[] = $row;
}

echo json_encode(['sections' => $sections]);
?>
