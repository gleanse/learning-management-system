<?php
// modules/dashboard.php

// Get statistics
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];

// Total collections today
$today = date('Y-m-d');
$total_collections = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM cash_payments
    WHERE DATE(payment_date) = '$today'
")->fetch_assoc()['total'];

// Get enrollment payment status PER SEMESTER - FIXED for your database
$semester_status = $conn->query("
    SELECT 
        e.id as enrollment_id,
        e.student_id,
        e.semester,
        e.year_level,
        e.section_id,
        e.base_fees,
        -- Check if all installments for this enrollment are paid
        CASE 
            WHEN SUM(CASE WHEN pi.status = 'paid' THEN 1 ELSE 0 END) = COUNT(pi.id) THEN 'paid'
            WHEN SUM(CASE WHEN pi.status = 'paid' THEN 1 ELSE 0 END) > 0 THEN 'partial'
            ELSE 'unpaid'
        END as semester_status
    FROM enrollments e
    LEFT JOIN payment_installments pi ON e.id = pi.enrollment_id
    GROUP BY e.id
");

// Check if query succeeded
if(!$semester_status) {
    die("Error in semester_status query: " . $conn->error);
}

$fully_paid_semesters = 0;
$partial_semesters = 0;
$unpaid_semesters = 0;

while($row = $semester_status->fetch_assoc()) {
    if($row['semester_status'] == 'paid') {
        $fully_paid_semesters++;
    } elseif($row['semester_status'] == 'partial') {
        $partial_semesters++;
    } else {
        $unpaid_semesters++;
    }
}

// Reset pointer
$semester_status->data_seek(0);

// Get students with payment summary - FIXED for your database
$students_payment_summary = $conn->query("
    SELECT 
        s.student_id,
        s.student_number,
        u.first_name,
        u.last_name,
        s.year_level,
        sec.section_name,
        COUNT(DISTINCT e.id) as total_semesters,
        SUM(CASE 
            WHEN (SELECT COUNT(*) FROM payment_installments pi2 
                  WHERE pi2.enrollment_id = e.id AND pi2.status = 'paid') = 
                 (SELECT COUNT(*) FROM payment_installments pi3 
                  WHERE pi3.enrollment_id = e.id)
            THEN 1 ELSE 0 
        END) as paid_semesters,
        SUM(CASE 
            WHEN (SELECT COUNT(*) FROM payment_installments pi2 
                  WHERE pi2.enrollment_id = e.id AND pi2.status = 'paid') > 0 
                 AND (SELECT COUNT(*) FROM payment_installments pi2 
                      WHERE pi2.enrollment_id = e.id AND pi2.status = 'paid') < 
                     (SELECT COUNT(*) FROM payment_installments pi3 
                      WHERE pi3.enrollment_id = e.id)
            THEN 1 ELSE 0 
        END) as partial_semesters,
        SUM(CASE 
            WHEN (SELECT COUNT(*) FROM payment_installments pi2 
                  WHERE pi2.enrollment_id = e.id AND pi2.status = 'paid') = 0
            THEN 1 ELSE 0 
        END) as unpaid_semesters
    FROM students s
    LEFT JOIN users u ON s.user_id = u.id
    LEFT JOIN sections sec ON s.section_id = sec.section_id
    LEFT JOIN enrollments e ON s.student_id = e.student_id
    GROUP BY s.student_id
    ORDER BY u.last_name ASC
    LIMIT 10
");

// Recent enrollments with payment status - FIXED for your database
$recent_enrollments = $conn->query("
    SELECT 
        e.*,
        s.student_number,
        u.first_name,
        u.last_name,
        sec.section_name,
        -- Get payment status for this enrollment
        CASE 
            WHEN SUM(CASE WHEN pi.status = 'paid' THEN 1 ELSE 0 END) = COUNT(pi.id) THEN 'paid'
            WHEN SUM(CASE WHEN pi.status = 'paid' THEN 1 ELSE 0 END) > 0 THEN 'partial'
            ELSE 'unpaid'
        END as payment_status,
        CONCAT(
            SUM(CASE WHEN pi.status = 'paid' THEN 1 ELSE 0 END),
            '/',
            COUNT(pi.id)
        ) as paid_quarters
    FROM enrollments e
    JOIN students s ON e.student_id = s.student_id
    JOIN users u ON s.user_id = u.id
    JOIN sections sec ON e.section_id = sec.section_id
    LEFT JOIN payment_installments pi ON e.id = pi.enrollment_id
    GROUP BY e.id
    ORDER BY e.created_at DESC
    LIMIT 5
");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
    <div>
        <button class="btn btn-primary" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
</div>
<!-- Statistics Cards - FIXED with inline styles -->
<div class="row">
    <div class="col-md-3">
        <div class="stat-card stat-primary" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: relative; overflow: hidden; height: 140px; margin-bottom: 15px; border-left: 4px solid #007bff;">
            <i class="fas fa-users" style="font-size: 3rem; position: absolute; right: 15px; top: 15px; opacity: 0.2; color: #000;"></i>
            <div class="stat-value" style="font-size: 2rem; font-weight: bold; margin-bottom: 5px; color: #000000;"><?php echo $total_students; ?></div>
            <div class="stat-label" style="color: #333333; font-size: 0.95rem; font-weight: 500;">Total Students</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card stat-success" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: relative; overflow: hidden; height: 140px; margin-bottom: 15px; border-left: 4px solid #28a745;">
            <i class="fas fa-money-bill" style="font-size: 3rem; position: absolute; right: 15px; top: 15px; opacity: 0.2; color: #000;"></i>
            <div class="stat-value" style="font-size: 2rem; font-weight: bold; margin-bottom: 5px; color: #000000;">₱<?php echo number_format($total_collections, 2); ?></div>
            <div class="stat-label" style="color: #333333; font-size: 0.95rem; font-weight: 500;">Today's Collections</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card stat-info" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: relative; overflow: hidden; height: 140px; margin-bottom: 15px; border-left: 4px solid #17a2b8;">
            <i class="fas fa-check-circle" style="font-size: 3rem; position: absolute; right: 15px; top: 15px; opacity: 0.2; color: #000;"></i>
            <div class="stat-value" style="font-size: 2rem; font-weight: bold; margin-bottom: 5px; color: #000000;"><?php echo $fully_paid_semesters; ?></div>
            <div class="stat-label" style="color: #333333; font-size: 0.95rem; font-weight: 500;">Fully Paid Semesters</div>
            <small style="color: #555555; font-size: 0.75rem; display: block; margin-top: 3px;">All quarters paid</small>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card stat-warning" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: relative; overflow: hidden; height: 140px; margin-bottom: 15px; border-left: 4px solid #ffc107;">
            <i class="fas fa-clock" style="font-size: 3rem; position: absolute; right: 15px; top: 15px; opacity: 0.2; color: #000;"></i>
            <div class="stat-value" style="font-size: 2rem; font-weight: bold; margin-bottom: 5px; color: #000000;"><?php echo $partial_semesters; ?></div>
            <div class="stat-label" style="color: #333333; font-size: 0.95rem; font-weight: 500;">Partial Semesters</div>
            <small style="color: #555555; font-size: 0.75rem; display: block; margin-top: 3px;">1-3 quarters paid</small>
        </div>
    </div>
</div>

<!-- Second row of stats -->
<div class="row mt-3" style="margin-top: 1rem;">
    <div class="col-md-4">
        <div class="stat-card stat-danger" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: relative; overflow: hidden; height: 140px; margin-bottom: 15px; border-left: 4px solid #dc3545;">
            <i class="fas fa-exclamation-circle" style="font-size: 3rem; position: absolute; right: 15px; top: 15px; opacity: 0.2; color: #000;"></i>
            <div class="stat-value" style="font-size: 2rem; font-weight: bold; margin-bottom: 5px; color: #000000;"><?php echo $unpaid_semesters; ?></div>
            <div class="stat-label" style="color: #333333; font-size: 0.95rem; font-weight: 500;">Unpaid Semesters</div>
            <small style="color: #555555; font-size: 0.75rem; display: block; margin-top: 3px;">No payments yet</small>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card stat-secondary" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: relative; overflow: hidden; height: 140px; margin-bottom: 15px; border-left: 4px solid #6c757d;">
            <i class="fas fa-layer-group" style="font-size: 3rem; position: absolute; right: 15px; top: 15px; opacity: 0.2; color: #000;"></i>
            <div class="stat-value" style="font-size: 2rem; font-weight: bold; margin-bottom: 5px; color: #000000;"><?php echo $fully_paid_semesters + $partial_semesters + $unpaid_semesters; ?></div>
            <div class="stat-label" style="color: #333333; font-size: 0.95rem; font-weight: 500;">Total Enrollments</div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: relative; overflow: hidden; height: 140px; margin-bottom: 15px; border-left: 4px solid #6f42c1;">
            <i class="fas fa-percent" style="font-size: 3rem; position: absolute; right: 15px; top: 15px; opacity: 0.2; color: #000;"></i>
            <div class="stat-value" style="font-size: 2rem; font-weight: bold; margin-bottom: 5px; color: #000000;">
                <?php 
                $total_enrollments = $fully_paid_semesters + $partial_semesters + $unpaid_semesters;
                if($total_enrollments > 0) {
                    $collection_rate = ($fully_paid_semesters / $total_enrollments) * 100;
                    echo number_format($collection_rate, 1) . '%';
                } else {
                    echo '0%';
                }
                ?>
            </div>
            <div class="stat-label" style="color: #333333; font-size: 0.95rem; font-weight: 500;">Collection Rate</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4 mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-bolt me-2"></i>Quick Actions
            </div>
            <div class="card-body">
                <a href="?tab=fee_management" class="btn btn-outline-primary me-2 mb-2">
                    <i class="fas fa-coins"></i> Tuition Fees
                </a>
                <a href="?tab=students" class="btn btn-outline-primary me-2 mb-2">
                    <i class="fas fa-user-plus"></i> Add Student
                </a>
                <a href="?tab=enrollment" class="btn btn-outline-success me-2 mb-2">
                    <i class="fas fa-user-graduate"></i> New Enrollment
                </a>
                <a href="?tab=payments" class="btn btn-outline-info me-2 mb-2">
                    <i class="fas fa-cash-register"></i> Process Payment
                </a>
                <a href="?tab=cash_drawer" class="btn btn-outline-warning me-2 mb-2">
                    <i class="fas fa-drawer"></i> Cash Drawer
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Student Payment Summary -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="fas fa-users me-2"></i>Student Payment Summary (Per Semester)
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student #</th>
                                <th>Name</th>
                                <th>Year/Section</th>
                                <th>Total Semesters</th>
                                <th>Fully Paid</th>
                                <th>Partial</th>
                                <th>Unpaid</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($students_payment_summary && $students_payment_summary->num_rows > 0): ?>
                                <?php while($student = $students_payment_summary->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $student['student_number']; ?></td>
                                    <td><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>
                                    <td><?php echo $student['year_level'] . ' - ' . $student['section_name']; ?></td>
                                    <td><?php echo $student['total_semesters'] ?: 0; ?></td>
                                    <td class="text-success fw-bold"><?php echo $student['paid_semesters'] ?: 0; ?></td>
                                    <td class="text-warning fw-bold"><?php echo $student['partial_semesters'] ?: 0; ?></td>
                                    <td class="text-danger fw-bold"><?php echo $student['unpaid_semesters'] ?: 0; ?></td>
                                    <td>
                                        <?php if($student['unpaid_semesters'] > 0): ?>
                                            <span class="badge bg-danger">Has Unpaid</span>
                                        <?php elseif($student['partial_semesters'] > 0): ?>
                                            <span class="badge bg-warning">Partial</span>
                                        <?php elseif($student['paid_semesters'] > 0): ?>
                                            <span class="badge bg-success">All Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No Enrollment</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No students found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Enrollments with Payment Status -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="fas fa-history me-2"></i>Recent Enrollments (with Payment Status)
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student #</th>
                                <th>Name</th>
                                <th>Year/Section</th>
                                <th>Semester</th>
                                <th>Quarters Paid</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($recent_enrollments && $recent_enrollments->num_rows > 0): ?>
                                <?php while($enrollment = $recent_enrollments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $enrollment['student_number']; ?></td>
                                    <td><?php echo $enrollment['first_name'] . ' ' . $enrollment['last_name']; ?></td>
                                    <td><?php echo $enrollment['year_level'] . ' - ' . $enrollment['section_name']; ?></td>
                                    <td><?php echo $enrollment['semester']; ?> Sem</td>
                                    <td><?php echo $enrollment['paid_quarters'] ?: '0/0'; ?></td>
                                    <td>
                                        <?php 
                                        $status = $enrollment['payment_status'];
                                        $badge_class = 'secondary';
                                        if($status == 'paid') $badge_class = 'success';
                                        elseif($status == 'partial') $badge_class = 'warning';
                                        elseif($status == 'unpaid') $badge_class = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($enrollment['created_at'])); ?></td>
                                    <td>
                                        <a href="?tab=payments&student_id=<?php echo $enrollment['student_id']; ?>&enrollment_id=<?php echo $enrollment['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-money-bill"></i> Pay
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No recent enrollments</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Status Charts -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i>Semester Payment Status
            </div>
            <div class="card-body">
                <canvas id="paymentChart" style="max-height: 300px;"></canvas>
                <div class="mt-3 text-center">
                    <div class="row">
                        <div class="col-4">
                            <span class="badge bg-success">Paid</span> <?php echo $fully_paid_semesters; ?>
                        </div>
                        <div class="col-4">
                            <span class="badge bg-warning">Partial</span> <?php echo $partial_semesters; ?>
                        </div>
                        <div class="col-4">
                            <span class="badge bg-danger">Unpaid</span> <?php echo $unpaid_semesters; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Payment Legend
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <strong>✅ Fully Paid Semester</strong> - Lahat ng quarters (Prelim, Midterm, Prefinal, Final) ay bayad na
                </div>
                <div class="alert alert-warning">
                    <strong>⚠️ Partial Semester</strong> - 1, 2, o 3 quarters pa lang ang bayad (kulang pa)
                </div>
                <div class="alert alert-danger">
                    <strong>❌ Unpaid Semester</strong> - Wala pang kahit anong quarter na bayad
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Payment Status Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('paymentChart');
    if(ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Fully Paid Semesters', 'Partial Semesters', 'Unpaid Semesters'],
                datasets: [{
                    data: [
                        <?php echo $fully_paid_semesters; ?>, 
                        <?php echo $partial_semesters; ?>, 
                        <?php echo $unpaid_semesters; ?>
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                let total = <?php echo $fully_paid_semesters + $partial_semesters + $unpaid_semesters; ?>;
                                let percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<style>
.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
    transition: transform 0.3s;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.stat-card i {
    font-size: 3rem;
    position: absolute;
    right: 15px;
    top: 15px;
    opacity: 0.2;
}
.stat-card .stat-value {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 5px;
}
.stat-card .stat-label {
    color: #666;
    font-size: 0.9rem;
}
.stat-primary { border-left: 4px solid #007bff; }
.stat-success { border-left: 4px solid #28a745; }
.stat-info { border-left: 4px solid #17a2b8; }
.stat-warning { border-left: 4px solid #ffc107; }
.stat-danger { border-left: 4px solid #dc3545; }
.stat-secondary { border-left: 4px solid #6c757d; }

.badge {
    padding: 5px 10px;
    font-size: 0.85rem;
}
.table td {
    vertical-align: middle;
}
.card-header {
    font-weight: bold;
}
</style>