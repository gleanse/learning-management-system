<?php
// api/get_unpaid_installments.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$student_id = (int)$_POST['student_id'];

$query = $conn->query("
    SELECT pi.*, e.semester, ay.school_year
    FROM payment_installments pi
    JOIN enrollments e ON pi.enrollment_id = e.id
    JOIN academic_years ay ON e.ay_id = ay.id
    WHERE e.student_id = $student_id 
    AND pi.status IN ('unpaid', 'partial')
    AND ay.is_active = 1
    ORDER BY e.semester, pi.installment_number
");

if ($query->num_rows > 0) {
    while($row = $query->fetch_assoc()) {
        $remaining = $row['amount_due'] - $row['amount_paid'];
        $status_badge = getPaymentStatusBadge($row['status']);
        ?>
        <div class="form-check mb-2 p-2 border rounded">
            <input class="form-check-input installment-checkbox" 
                   type="checkbox" 
                   value="<?php echo $row['id']; ?>"
                   data-id="<?php echo $row['id']; ?>"
                   data-amount="<?php echo $row['amount_due']; ?>"
                   data-remaining="<?php echo $remaining; ?>"
                   data-name="<?php echo htmlspecialchars($row['installment_name']); ?>"
                   id="inst_<?php echo $row['id']; ?>">
            <label class="form-check-label" for="inst_<?php echo $row['id']; ?>">
                <strong><?php echo $row['installment_name']; ?></strong><br>
                <small><?php echo $row['school_year'] . ' - ' . $row['semester']; ?> Sem</small><br>
                <span class="text-muted">Amount Due: <?php echo formatCurrency($row['amount_due']); ?></span><br>
                <span class="text-muted">Amount Paid: <?php echo formatCurrency($row['amount_paid']); ?></span><br>
                <span class="text-primary fw-bold">Remaining: <?php echo formatCurrency($remaining); ?></span>
                <?php echo $status_badge; ?>
            </label>
        </div>
        <?php
    }
} else {
    echo '<div class="alert alert-info text-center">No unpaid installments found for this student.</div>';
}
?>