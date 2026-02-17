<?php
// api/get_student_fees.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'semester_fee' => 0, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $section_id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 0;
    $year_level = isset($_POST['year_level']) ? $conn->real_escape_string($_POST['year_level']) : '';
    
    if ($section_id > 0 && !empty($year_level)) {
        // Try to get fee from fee_structure table
        $fee_query = $conn->query("
            SELECT semester_fee FROM fee_structure 
            WHERE section_id = $section_id AND year_level = '$year_level'
            LIMIT 1
        ");
        
        if ($fee_query && $fee_query->num_rows > 0) {
            $fees = $fee_query->fetch_assoc();
            $semester_fee = (float)$fees['semester_fee'];
            $response['message'] = 'Fee loaded from database';
        } else {
            // Fallback: Default fee based on year level
            $year_level_fees = [
                'Grade 11' => 7500,
                'Grade 12' => 7500,
                '1st Year' => 7500,
                '2nd Year' => 7500,
                '3rd Year' => 10000,
                '4th Year' => 10000
            ];
            
            $semester_fee = isset($year_level_fees[$year_level]) ? $year_level_fees[$year_level] : 7500;
            $response['message'] = 'Using default fee (no fee structure found)';
        }
        
        $response['success'] = true;
        $response['semester_fee'] = round($semester_fee, 2);
        
        // Also return the year level and section for debugging
        $response['year_level'] = $year_level;
        $response['section_id'] = $section_id;
    } else {
        $response['error'] = 'Missing required parameters';
        $response['message'] = 'Section ID and Year Level are required';
    }
} else {
    $response['error'] = 'Invalid request method';
    $response['message'] = 'Please use POST method';
}

echo json_encode($response);
?>