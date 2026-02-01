<?php
// NOTE: simple test view TEST ONLY MIGHT DELETE LATER
echo "<h1>Admin Dashboard</h1>";
echo "<p>Total Students: " . $total_students . "</p>";
echo "<p>Students This Month: " . $students_this_month . "</p>";
echo "<p>Total Teachers: " . $total_teachers . "</p>";
echo "<p>Assigned Teachers: " . $assigned_teachers . "</p>";
echo "<p>Total Sections: " . $total_sections . "</p>";

echo "<h2>Recent Enrollments:</h2>";
foreach ($recent_enrollments as $enrollment) {
    echo "<p>" . $enrollment['student_number'] . " - " . 
         $enrollment['first_name'] . " " . $enrollment['last_name'] . "</p>";
}