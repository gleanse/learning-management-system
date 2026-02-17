<?php
// receipts/print_receipt.php
require_once '../config/database.php';
require_once '../includes/functions.php';

$receipt_number = isset($_GET['receipt']) ? $_GET['receipt'] : '';

if (!$receipt_number) {
    die('No receipt number provided');
}

// Get receipt details
$receipt = $conn->query("
    SELECT cp.*, 
           pi.installment_name, pi.amount_due,
           e.semester, e.year_level,
           s.student_number,
           u.first_name, u.last_name,
           sec.section_name,
           cd.drawer_date
    FROM cash_payments cp
    JOIN payment_installments pi ON cp.installment_id = pi.id
    JOIN enrollments e ON pi.enrollment_id = e.id
    JOIN students s ON e.student_id = s.student_id
    JOIN users u ON s.user_id = u.id
    JOIN sections sec ON s.section_id = sec.section_id
    JOIN cash_drawer cd ON cp.drawer_id = cd.id
    WHERE cp.receipt_number = '$receipt_number'
");

if ($receipt->num_rows == 0) {
    die('Receipt not found');
}

$data = $receipt->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo $receipt_number; ?></title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 20px;
            font-size: 14px;
        }
        .receipt {
            max-width: 300px;
            margin: 0 auto;
            border: 1px dashed #000;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .total {
            font-weight: bold;
            font-size: 16px;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h2>REGISTRAR OFFICE</h2>
            <p>Official Receipt</p>
            <p><?php echo date('F d, Y', strtotime($data['payment_date'])); ?></p>
        </div>
        
        <div class="divider"></div>
        
        <div class="row">
            <span>Receipt #:</span>
            <span><?php echo $receipt_number; ?></span>
        </div>
        
        <div class="row">
            <span>Student #:</span>
            <span><?php echo $data['student_number']; ?></span>
        </div>
        
        <div class="row">
            <span>Student Name:</span>
            <span><?php echo $data['last_name'] . ', ' . $data['first_name']; ?></span>
        </div>
        
        <div class="row">
            <span>Year Level:</span>
            <span><?php echo $data['year_level']; ?></span>
        </div>
        
        <div class="row">
            <span>Section:</span>
            <span><?php echo $data['section_name']; ?></span>
        </div>
        
        <div class="row">
            <span>Semester:</span>
            <span><?php echo $data['semester']; ?> Sem</span>
        </div>
        
        <div class="divider"></div>
        
        <div class="row">
            <span>Payment for:</span>
            <span><?php echo $data['installment_name']; ?></span>
        </div>
        
        <div class="row">
            <span>Amount Due:</span>
            <span><?php echo formatCurrency($data['amount_due']); ?></span>
        </div>
        
        <div class="row">
            <span>Amount Paid:</span>
            <span><?php echo formatCurrency($data['amount']); ?></span>
        </div>
        
        <div class="divider"></div>
        
        <div class="row total">
            <span>TOTAL PAID:</span>
            <span><?php echo formatCurrency($data['amount']); ?></span>
        </div>
        
        <div class="row">
            <span>Payment Mode:</span>
            <span>CASH</span>
        </div>
        
        <div class="row">
            <span>Cashier:</span>
            <span>Registrar</span>
        </div>
        
        <div class="footer">
            <p>Thank you for your payment!</p>
            <p>This serves as your official receipt</p>
        </div>
    </div>
    
    <div class="no-print text-center mt-3">
        <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>
    
    <script>
        window.onload = function() {
            // Auto-print when page loads
            // window.print();
        }
    </script>
</body>
</html>