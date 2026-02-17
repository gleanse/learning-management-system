<?php
// includes/functions.php

function generateStudentNumber($conn) {
    // Generate DCSC format: DCSC-XXXXX
    $query = "SELECT MAX(CAST(SUBSTRING_INDEX(student_number, '-', -1) AS UNSIGNED)) as max_num FROM students WHERE student_number LIKE 'DCSC-%'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $max_num = $row['max_num'] ?? 0;
    $next_num = $max_num + 1;
    return 'DCSC-' . str_pad($next_num, 5, '0', STR_PAD_LEFT);
}

function generateReceiptNumber($conn) {
    $date = date('Ymd');
    $query = "SELECT COUNT(*) as count FROM cash_payments WHERE receipt_number LIKE '$date%'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $count = $row['count'] + 1;
    return $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

function getPaymentStatusBadge($status) {
    switch($status) {
        case 'paid':
            return '<span class="badge bg-success">Paid</span>';
        case 'partial':
            return '<span class="badge bg-warning text-dark">Partial</span>';
        case 'unpaid':
            return '<span class="badge bg-danger">Unpaid</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function getActiveAcademicYear($conn) {
    $query = "SELECT * FROM academic_years WHERE is_active = 1 LIMIT 1";
    $result = $conn->query($query);
    return $result->fetch_assoc();
}

function calculateInstallments($semester_fee, $term_id) {
    $terms = [
        1 => 1,  // Full Payment (buong semester)
        2 => 2,  // Per Semester (2 installments)
        3 => 4   // Per Quarter (4 installments)
    ];
    
    $num_payments = $terms[$term_id] ?? 1;
    return $semester_fee / $num_payments;
}

// FIXED: getInstallmentNames function - TAMA NA ITO
function getInstallmentNames($term_id, $semester) {
    $names = [];
    $semester_text = ($semester == 'First') ? '1st Sem' : '2nd Sem';
    
    switch($term_id) {
        case 1: // Full Payment - Isang installment lang para sa buong semester
            $names[] = "$semester_text - Full Payment";
            break;
        case 2: // Per Semester - 2 installments
            $names[] = "$semester_text - 1st Installment";
            $names[] = "$semester_text - 2nd Installment";
            break;
        case 3: // Per Quarter - 4 installments
            $names[] = "$semester_text - Quarter 1";
            $names[] = "$semester_text - Quarter 2";
            $names[] = "$semester_text - Quarter 3";
            $names[] = "$semester_text - Quarter 4";
            break;
    }
    
    return $names;
}

// Function para makuha ang term name
function getTermName($term_id) {
    switch($term_id) {
        case 1:
            return 'Full Payment';
        case 2:
            return 'Per Semester';
        case 3:
            return 'Per Quarter';
        default:
            return 'Unknown';
    }
}

// FIXED: Table name is 'cash_drawer' not 'cash_drawers'
function getOpenDrawer($conn) {
    // Check if there's an open drawer for today
    $today = date('Y-m-d');
    $query = "SELECT * FROM cash_drawer WHERE drawer_date = '$today' AND status = 'open' LIMIT 1";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    // If no open drawer for today, create one
    $insert_query = "INSERT INTO cash_drawer (drawer_date, opening_balance, status) VALUES ('$today', 0.00, 'open')";
    $conn->query($insert_query);
    
    $new_id = $conn->insert_id;
    $query = "SELECT * FROM cash_drawer WHERE id = $new_id";
    $result = $conn->query($query);
    
    return $result->fetch_assoc();
}

// Add this function to check if drawer is open
function isDrawerOpen($conn) {
    $today = date('Y-m-d');
    $query = "SELECT * FROM cash_drawer WHERE drawer_date = '$today' AND status = 'open'";
    $result = $conn->query($query);
    return $result->num_rows > 0;
}

// Add this function to close drawer
function closeDrawer($conn, $closing_balance) {
    $today = date('Y-m-d');
    $query = "UPDATE cash_drawer SET 
              closing_balance = $closing_balance, 
              status = 'closed',
              closed_at = NOW()
              WHERE drawer_date = '$today' AND status = 'open'";
    return $conn->query($query);
}
?>