<?php
// api/save_payment.php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $installment_ids = $_POST['installment_ids'];
    $amounts = $_POST['amounts'];
    $drawer_id = (int)$_POST['drawer_id'];
    $total_amount = (float)$_POST['total_amount'];
    
    $receipt_number = generateReceiptNumber($conn);
    
    $conn->begin_transaction();
    
    try {
        foreach ($installment_ids as $index => $installment_id) {
            $amount = (float)$amounts[$index];
            
            // Update installment
            $conn->query("
                UPDATE payment_installments 
                SET amount_paid = amount_paid + $amount,
                    status = CASE 
                        WHEN amount_paid + $amount >= amount_due THEN 'paid'
                        ELSE 'partial'
                    END
                WHERE id = $installment_id
            ");
            
            // Record payment
            $conn->query("
                INSERT INTO cash_payments (installment_id, drawer_id, amount, receipt_number)
                VALUES ($installment_id, $drawer_id, $amount, '$receipt_number')
            ");
        }
        
        // Update cash drawer
        $conn->query("
            UPDATE cash_drawer 
            SET total_cash_in = total_cash_in + $total_amount
            WHERE id = $drawer_id
        ");
        
        $conn->commit();
        $response['success'] = true;
        $response['receipt'] = $receipt_number;
        $response['message'] = 'Payment saved successfully';
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Error saving payment: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>