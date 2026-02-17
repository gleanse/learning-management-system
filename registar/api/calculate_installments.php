<?php
// api/calculate_installments.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'per_installment' => 0];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $term_id = isset($_POST['term_id']) ? (int)$_POST['term_id'] : 0;
    $semester_fee = isset($_POST['semester_fee']) ? (float)$_POST['semester_fee'] : 0;
    
    if ($term_id > 0 && $semester_fee > 0) {
        // Get term details
        $term = $conn->query("SELECT * FROM payment_terms WHERE id = $term_id");
        
        if ($term && $term->num_rows > 0) {
            $term_data = $term->fetch_assoc();
            $num_payments = (int)$term_data['number_of_payments'];
            $per_installment = $semester_fee / $num_payments;
            
            $response['success'] = true;
            $response['per_installment'] = round($per_installment, 2);
        } else {
            $response['error'] = 'Payment term not found';
        }
    } else {
        $response['error'] = 'Missing required parameters';
    }
}

echo json_encode($response);
?>
