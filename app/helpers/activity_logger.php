<?php
require_once __DIR__ . '/../models/ActivityLog.php';

function logAction($action, $description = null, $table = null, $record_id = null, $old_data = null, $new_data = null)
{
    if (!isset($_SESSION['user_id'])) return false;
    
    $logger = new ActivityLog();
    return $logger->log($_SESSION['user_id'], $action, $description, $table, $record_id, $old_data, $new_data);
}

// usage examples:
// logAction('login', 'user logged in');
// logAction('create_student', 'created new student', 'students', $student_id, null, $student_data);
// logAction('update_grade', 'updated grade', 'grades', $grade_id, $old_grade, $new_grade);
// logAction('delete_section', 'deleted section', 'sections', $section_id, $section_data, null);