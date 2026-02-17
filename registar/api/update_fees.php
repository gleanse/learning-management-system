<?php
// api/update_fees.php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Single fee update
    if (isset($_POST['update_single'])) {
        $section_id = (int)$_POST['section_id'];
        $year_level = $conn->real_escape_string($_POST['year_level']);
        $new_fee = (float)$_POST['semester_fee'];
        
        // Get old fee
        $old_fee_query = $conn->query("
            SELECT semester_fee FROM fee_structure 
            WHERE section_id = $section_id AND year_level = '$year_level'
        ");
        
        if ($old_fee_query->num_rows > 0) {
            $old_fee = $old_fee_query->fetch_assoc()['semester_fee'];
            
            // Updatea
            $conn->query("
                UPDATE fee_structure 
                SET semester_fee = $new_fee 
                WHERE section_id = $section_id AND year_level = '$year_level'
            ");
        } else {
            $old_fee = null;
            
            // Insert
            $conn->query("
                INSERT INTO fee_structure (section_id, year_level, semester_fee) 
                VALUES ($section_id, '$year_level', $new_fee)
            ");
        }
        
        // Log to history
        $changed_by = 'Registrar';
        $conn->query("
            INSERT INTO fee_history (section_id, year_level, old_fee, new_fee, changed_by) 
            VALUES ($section_id, '$year_level', " . ($old_fee ?? 'NULL') . ", $new_fee, '$changed_by')
        ");
        
        $response['success'] = true;
        $response['message'] = 'Fee updated successfully';
    }
    
    // Bulk update
    if (isset($_POST['bulk_update']) && isset($_POST['fees'])) {
        $success = true;
        
        foreach ($_POST['fees'] as $section_id => $year_fees) {
            foreach ($year_fees as $year_level => $semester_fee) {
                $section_id = (int)$section_id;
                $year_level = $conn->real_escape_string($year_level);
                $semester_fee = (float)$semester_fee;
                
                // Check if exists
                $check = $conn->query("
                    SELECT id, semester_fee FROM fee_structure 
                    WHERE section_id = $section_id AND year_level = '$year_level'
                ");
                
                if ($check->num_rows > 0) {
                    $old_fee = $check->fetch_assoc()['semester_fee'];
                    
                    // Update
                    $conn->query("
                        UPDATE fee_structure 
                        SET semester_fee = $semester_fee 
                        WHERE section_id = $section_id AND year_level = '$year_level'
                    ");
                    
                    // Log if changed
                    if ($old_fee != $semester_fee) {
                        $changed_by = 'Registrar';
                        $conn->query("
                            INSERT INTO fee_history (section_id, year_level, old_fee, new_fee, changed_by) 
                            VALUES ($section_id, '$year_level', $old_fee, $semester_fee, '$changed_by')
                        ");
                    }
                } else {
                    // Insert
                    $conn->query("
                        INSERT INTO fee_structure (section_id, year_level, semester_fee) 
                        VALUES ($section_id, '$year_level', $semester_fee)
                    ");
                    
                    // Log
                    $changed_by = 'Registrar';
                    $conn->query("
                        INSERT INTO fee_history (section_id, year_level, new_fee, changed_by) 
                        VALUES ($section_id, '$year_level', $semester_fee, '$changed_by')
                    ");
                }
                
                if ($conn->error) {
                    $success = false;
                }
            }
        }
        
        $response['success'] = $success;
        $response['message'] = $success ? 'All fees updated successfully' : 'Error updating some fees';
    }
}

echo json_encode($response);
?>