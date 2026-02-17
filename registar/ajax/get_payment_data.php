<?php
// IMPORTANT: No whitespace before <?php
require_once '../includes/functions.php';

// TURN OFF ERROR DISPLAY - Iwas sa HTML errors
ini_set('display_errors', 0);
error_reporting(0);

// Clear any output buffer
if (ob_get_level()) {
    ob_clean();
}


// Get filters from AJAX request
$year_level_filter = isset($_GET['year_level']) ? $_GET['year_level'] : '';
$strand_filter = isset($_GET['section_id']) ? (int)$_GET['section_id'] : '';
$semester_filter = isset($_GET['semester']) ? $_GET['semester'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Initialize response
$response = [
    'table_html' => '',
    'summary' => []
];

try {
    // Get current active academic year
    $active_ay = $conn->query("SELECT id, school_year FROM academic_years WHERE is_active = 1");
    if (!$active_ay) {
        throw new Exception("Active year query failed: " . $conn->error);
    }
    $active_ay_data = $active_ay->fetch_assoc();
    $active_ay_id = $active_ay_data['id'];

    // Build main query with ALL filters
    $query = "
        SELECT 
            s.student_id,
            s.student_number,
            u.first_name,
            u.last_name,
            s.year_level,
            sec.section_name,
            e.semester,
            e.ay_id,
            ay.school_year,
            pi.id as installment_id,
            pi.installment_name,
            pi.installment_number,
            pi.amount_due,
            pi.amount_paid,
            pi.status as installment_status,
            (pi.amount_due - pi.amount_paid) as remaining_balance
        FROM students s
        JOIN users u ON s.user_id = u.id
        JOIN sections sec ON s.section_id = sec.section_id
        JOIN enrollments e ON s.student_id = e.student_id
        JOIN academic_years ay ON e.ay_id = ay.id
        JOIN payment_installments pi ON e.id = pi.enrollment_id
        WHERE ay.is_active = 1
    ";

    if ($year_level_filter) {
        $query .= " AND s.year_level = '" . $conn->real_escape_string($year_level_filter) . "'";
    }

    if ($strand_filter) {
        $query .= " AND s.section_id = $strand_filter";
    }

    if ($semester_filter) {
        $query .= " AND e.semester = '" . $conn->real_escape_string($semester_filter) . "'";
    }

    if ($status_filter) {
        $query .= " AND pi.status = '" . $conn->real_escape_string($status_filter) . "'";
    }

    $query .= " ORDER BY s.year_level, sec.section_name, u.last_name, e.semester, pi.installment_number";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query error: " . $conn->error);
    }

    // Organize data by student
    $students_data = [];
    while ($row = $result->fetch_assoc()) {
        $student_id = $row['student_id'];
        $semester = $row['semester'];
        $installment_number = $row['installment_number'];
        
        if (!isset($students_data[$student_id])) {
            $students_data[$student_id] = [
                'info' => [
                    'student_number' => $row['student_number'],
                    'name' => $row['first_name'] . ' ' . $row['last_name'],
                    'year_level' => $row['year_level'],
                    'section' => $row['section_name']
                ],
                'installments' => [
                    'First' => [],
                    'Second' => []
                ]
            ];
        }
        
        $students_data[$student_id]['installments'][$semester][$installment_number] = [
            'name' => $row['installment_name'],
            'status' => $row['installment_status'],
            'amount_due' => $row['amount_due'],
            'amount_paid' => $row['amount_paid'],
            'remaining' => $row['remaining_balance']
        ];
    }

    // Generate Table HTML
    ob_start();
    if (empty($students_data)): ?>
        <tr>
            <td colspan="16" class="text-center text-muted">
                <i class="fas fa-info-circle"></i> No data available for the selected filters
            </td>
        </tr>
    <?php else: ?>
        <?php 
        $counter = 1;
        foreach($students_data as $student): 
            $total_paid = 0;
            $total_due = 0;
        ?>
        <tr>
            <td class="text-center"><?php echo $counter++; ?></td>
            <td><?php echo htmlspecialchars($student['info']['student_number']); ?></td>
            <td><?php echo htmlspecialchars($student['info']['name']); ?></td>
            <td><?php echo htmlspecialchars($student['info']['year_level']); ?></td>
            <td><?php echo htmlspecialchars($student['info']['section']); ?></td>
            
            <!-- First Semester Quarters (1-4) -->
            <?php for($q = 1; $q <= 4; $q++): 
                $status = 'unpaid';
                $amount_paid = 0;
                $amount_due = 0;
                
                if (isset($student['installments']['First'][$q])) {
                    $inst = $student['installments']['First'][$q];
                    $status = $inst['status'];
                    $amount_paid = $inst['amount_paid'];
                    $amount_due = $inst['amount_due'];
                    
                    $total_paid += $amount_paid;
                    $total_due += $amount_due;
                }
                
                $status_class = $status == 'paid' ? 'success' : ($status == 'partial' ? 'warning' : 'danger');
                $icon = $status == 'paid' ? 'fa-check-circle' : ($status == 'partial' ? 'fa-adjust' : 'fa-times-circle');
            ?>
            <td class="bg-<?php echo $status_class; ?> text-center">
                <i class="fas <?php echo $icon; ?> text-white"></i>
            </td>
            <?php endfor; ?>
            
            <!-- Second Semester Quarters (1-4) -->
            <?php for($q = 1; $q <= 4; $q++): 
                $status = 'unpaid';
                $amount_paid = 0;
                $amount_due = 0;
                
                if (isset($student['installments']['Second'][$q])) {
                    $inst = $student['installments']['Second'][$q];
                    $status = $inst['status'];
                    $amount_paid = $inst['amount_paid'];
                    $amount_due = $inst['amount_due'];
                    
                    $total_paid += $amount_paid;
                    $total_due += $amount_due;
                }
                
                $status_class = $status == 'paid' ? 'success' : ($status == 'partial' ? 'warning' : 'danger');
                $icon = $status == 'paid' ? 'fa-check-circle' : ($status == 'partial' ? 'fa-adjust' : 'fa-times-circle');
            ?>
            <td class="bg-<?php echo $status_class; ?> text-center">
                <i class="fas <?php echo $icon; ?> text-white"></i>
            </td>
            <?php endfor; ?>
            
            <td class="text-end">
                <strong>₱ <?php echo number_format($total_paid, 2); ?></strong>
            </td>
            <td class="text-end">
                <?php $balance = $total_due - $total_paid; ?>
                <strong class="<?php echo $balance > 0 ? 'text-danger' : 'text-success'; ?>">
                    ₱ <?php echo number_format($balance, 2); ?>
                </strong>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif;
    
    $response['table_html'] = ob_get_clean();

    // Get summary statistics with filters
    $summary_query = "
        SELECT 
            COUNT(DISTINCT s.student_id) as total_students,
            SUM(CASE WHEN pi.status = 'paid' THEN 1 ELSE 0 END) as fully_paid_count,
            SUM(CASE WHEN pi.status = 'partial' THEN 1 ELSE 0 END) as partial_count,
            SUM(CASE WHEN pi.status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_count,
            SUM(pi.amount_paid) as total_collected,
            SUM(pi.amount_due) as total_receivable
        FROM students s
        JOIN enrollments e ON s.student_id = e.student_id
        JOIN payment_installments pi ON e.id = pi.enrollment_id
        WHERE e.ay_id = $active_ay_id
    ";

    if ($year_level_filter) {
        $summary_query .= " AND s.year_level = '" . $conn->real_escape_string($year_level_filter) . "'";
    }

    if ($strand_filter) {
        $summary_query .= " AND s.section_id = $strand_filter";
    }

    if ($semester_filter) {
        $summary_query .= " AND e.semester = '" . $conn->real_escape_string($semester_filter) . "'";
    }

    if ($status_filter) {
        $summary_query .= " AND pi.status = '" . $conn->real_escape_string($status_filter) . "'";
    }

    $summary_result = $conn->query($summary_query);
    if ($summary_result) {
        $response['summary'] = $summary_result->fetch_assoc();
    }

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

// IMPORTANT: Clear any output buffers before sending JSON
while (ob_get_level()) {
    ob_end_clean();
}

// Set JSON header
header('Content-Type: application/json');
echo json_encode($response);
exit; // Make sure nothing else is output
?>