<?php
// modules/cash_drawer.php

$today = date('Y-m-d');

// Check if drawer exists for today
$drawer = $conn->query("SELECT * FROM cash_drawer WHERE drawer_date = '$today'");
$drawer_exists = $drawer->num_rows > 0;
$drawer_data = $drawer_exists ? $drawer->fetch_assoc() : null;

// Handle open drawer
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['open_drawer'])) {
    $opening_balance = (float)$_POST['opening_balance'];
    
    $conn->query("
        INSERT INTO cash_drawer (drawer_date, opening_balance, status)
        VALUES ('$today', $opening_balance, 'open')
    ");
    
    echo "<script>toastr.success('Cash drawer opened successfully!');</script>";
    header("Refresh:0");
}

// Handle close drawer
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['close_drawer'])) {
    $drawer_id = (int)$_POST['drawer_id'];
    $closing_balance = (float)$_POST['closing_balance'];
    $total_cash_in = (float)$_POST['total_cash_in'];
    
    $conn->query("
        UPDATE cash_drawer 
        SET closing_balance = $closing_balance,
            total_cash_in = $total_cash_in,
            status = 'closed',
            closed_at = NOW()
        WHERE id = $drawer_id
    ");
    
    echo "<script>toastr.success('Cash drawer closed successfully!');</script>";
    header("Refresh:0");
}

// Get today's transactions
if ($drawer_exists && $drawer_data['status'] == 'open') {
    $transactions = $conn->query("
        SELECT cp.*, pi.installment_name, s.student_number, u.u.first_name, u.last_name
        FROM cash_payments cp
        JOIN payment_installments pi ON cp.installment_id = pi.id
        JOIN enrollments e ON pi.enrollment_id = e.id
        JOIN students s ON e.student_id = s.student_id
        JOIN users u ON s.user_id = u.id
        WHERE cp.drawer_id = {$drawer_data['id']}
        ORDER BY cp.payment_date DESC
    ");
}

// Get drawer history
$history = $conn->query("
    SELECT * FROM cash_drawer 
    ORDER BY drawer_date DESC 
    LIMIT 10
");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-cash-register me-2"></i>Cash Drawer Management</h2>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Drawer Status Card -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Drawer Status
            </div>
            <div class="card-body">
                <?php if (!$drawer_exists): ?>
                    <div class="text-center">
                        <h3 class="text-warning mb-3">
                            <i class="fas fa-clock"></i>
                        </h3>
                        <h5>Drawer is Closed</h5>
                        <p class="text-muted">Open cash drawer to start transactions</p>
                        
                        <form method="POST" class="mt-3">
                            <div class="mb-3">
                                <label class="form-label">Opening Balance</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" name="opening_balance" class="form-control" 
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                            <button type="submit" name="open_drawer" class="btn btn-success w-100">
                                <i class="fas fa-door-open"></i> Open Drawer
                            </button>
                        </form>
                    </div>
                <?php elseif ($drawer_data['status'] == 'open'): ?>
                    <div class="text-center">
                        <h3 class="text-success mb-3">
                            <i class="fas fa-door-open"></i>
                        </h3>
                        <h5>Drawer is Open</h5>
                        <p class="text-muted"><?php echo date('F d, Y', strtotime($drawer_data['drawer_date'])); ?></p>
                        
                        <hr>
                        
                        <div class="text-start">
                            <p><strong>Opening Balance:</strong> ₱<?php echo number_format($drawer_data['opening_balance'], 2); ?></p>
                            <p><strong>Total Cash In:</strong> ₱<?php echo number_format($drawer_data['total_cash_in'], 2); ?></p>
                            <p><strong>Expected Closing:</strong> ₱<?php echo number_format($drawer_data['opening_balance'] + $drawer_data['total_cash_in'], 2); ?></p>
                        </div>
                        
                        <button class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#closeDrawerModal">
                            <i class="fas fa-door-closed"></i> Close Drawer
                        </button>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <h3 class="text-secondary mb-3">
                            <i class="fas fa-door-closed"></i>
                        </h3>
                        <h5>Drawer is Closed</h5>
                        <p class="text-muted">Closed at: <?php echo date('h:i A', strtotime($drawer_data['closed_at'])); ?></p>
                        
                        <hr>
                        
                        <div class="text-start">
                            <p><strong>Opening Balance:</strong> ₱<?php echo number_format($drawer_data['opening_balance'], 2); ?></p>
                            <p><strong>Total Cash In:</strong> ₱<?php echo number_format($drawer_data['total_cash_in'], 2); ?></p>
                            <p><strong>Closing Balance:</strong> ₱<?php echo number_format($drawer_data['closing_balance'], 2); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Drawer History -->
        <div class="card mt-3">
            <div class="card-header">
                <i class="fas fa-history me-2"></i>Recent Drawer History
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php while($row = $history->fetch_assoc()): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong><?php echo date('M d, Y', strtotime($row['drawer_date'])); ?></strong>
                            <span class="badge bg-<?php echo $row['status'] == 'open' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </div>
                        <small>
                            In: ₱<?php echo number_format($row['total_cash_in'], 2); ?> | 
                            Close: ₱<?php echo number_format($row['closing_balance'], 2); ?>
                        </small>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Today's Transactions -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list me-2"></i>Today's Transactions
                <?php if ($drawer_exists && $drawer_data['status'] == 'open'): ?>
                <button class="btn btn-sm btn-primary float-end" onclick="printReport()">
                    <i class="fas fa-print"></i> Print Report
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($drawer_exists && $drawer_data['status'] == 'open'): ?>
                    <?php if ($transactions->num_rows > 0): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Receipt #</th>
                                <th>Student</th>
                                <th>Installment</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = 0;
                            while($trans = $transactions->fetch_assoc()): 
                                $total += $trans['amount'];
                            ?>
                            <tr>
                                <td><?php echo date('h:i A', strtotime($trans['payment_date'])); ?></td>
                                <td><?php echo $trans['receipt_number']; ?></td>
                                <td><?php echo $trans['first_name'] . ' ' . $trans['last_name']; ?></td>
                                <td><?php echo $trans['installment_name']; ?></td>
                                <td class="text-end">₱<?php echo number_format($trans['amount'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <tr class="table-info">
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>₱<?php echo number_format($total, 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="text-center text-muted">No transactions today</p>
                    <?php endif; ?>
                <?php else: ?>
                <p class="text-center text-muted">Open the cash drawer to view transactions</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Close Drawer Modal -->
<div class="modal fade" id="closeDrawerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Close Cash Drawer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="drawer_id" value="<?php echo $drawer_data['id']; ?>">
                    <input type="hidden" name="total_cash_in" value="<?php echo $drawer_data['total_cash_in']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Opening Balance</label>
                        <input type="text" class="form-control" value="₱<?php echo number_format($drawer_data['opening_balance'], 2); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Total Cash In</label>
                        <input type="text" class="form-control" value="₱<?php echo number_format($drawer_data['total_cash_in'], 2); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Expected Closing Balance</label>
                        <input type="text" class="form-control" value="₱<?php echo number_format($drawer_data['opening_balance'] + $drawer_data['total_cash_in'], 2); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Actual Closing Balance</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="closing_balance" class="form-control" 
                                   step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="close_drawer" class="btn btn-warning">
                        Close Drawer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function printReport() {
    var printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>Cash Drawer Report</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(document.querySelector('.card:last-child').innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>