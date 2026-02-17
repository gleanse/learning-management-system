<?php
// modules/payment_monitoring.php
require_once 'includes/functions.php';

// Get current active academic year
$active_ay = $conn->query("SELECT id, school_year FROM academic_years WHERE is_active = 1");
$active_ay_data = $active_ay->fetch_assoc();
$active_ay_id = $active_ay_data['id'];

// Get strands for filter
$strands = $conn->query("SELECT * FROM sections ORDER BY section_name");

// Get year levels for filter
$year_levels = ['Grade 11', 'Grade 12', '1st Year', '2nd Year', '3rd Year', '4th Year'];

// Get initial data para may laman agad ang table
$initial_query = "
    SELECT 
        s.student_id,
        s.student_number,
        u.first_name ,
        u.last_name,
        s.year_level,
        sec.section_name,
        e.semester,
        pi.installment_number,
        pi.amount_due,
        pi.amount_paid,
        pi.status as installment_status
    FROM students s
    JOIN users u ON s.user_id = u.id
    JOIN sections sec ON s.section_id = sec.section_id
    JOIN enrollments e ON s.student_id = e.student_id
    JOIN payment_installments pi ON e.id = pi.enrollment_id
    WHERE e.ay_id = $active_ay_id
    ORDER BY s.year_level, sec.section_name, u.last_name, e.semester, pi.installment_number
";

$initial_result = $conn->query($initial_query);

// Organize initial data
$initial_students = [];
while ($row = $initial_result->fetch_assoc()) {
    $student_id = $row['student_id'];
    $semester = $row['semester'];
    $installment_number = $row['installment_number'];
    
    if (!isset($initial_students[$student_id])) {
        $initial_students[$student_id] = [
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
    
    $initial_students[$student_id]['installments'][$semester][$installment_number] = [
        'status' => $row['installment_status'],
        'amount_due' => $row['amount_due'],
        'amount_paid' => $row['amount_paid']
    ];
}

// Get initial summary
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
$summary = $conn->query($summary_query)->fetch_assoc();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-chart-line me-2"></i>Payment Monitoring - <?php echo $active_ay_data['school_year']; ?></h2>
    <div>
        <button class="btn btn-success" onclick="exportToExcel()">
            <i class="fas fa-file-excel"></i> Export to Excel
        </button>
        <button class="btn btn-danger" onclick="exportToPDF()">
            <i class="fas fa-file-pdf"></i> Export to PDF
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div id="summary-cards">
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Students</h5>
                    <h3><?php echo number_format($summary['total_students'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Fully Paid</h5>
                    <h3><?php echo number_format($summary['fully_paid_count'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Partial</h5>
                    <h3><?php echo number_format($summary['partial_count'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Unpaid</h5>
                    <h3><?php echo number_format($summary['unpaid_count'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Collection</h5>
                    <h3>₱ <?php echo number_format($summary['total_collected'] ?? 0, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Receivable</h5>
                    <h3>₱ <?php echo number_format($summary['total_receivable'] ?? 0, 2); ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters - REAL TIME! No submit button -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2"></i>Filter Options (Real-time - No need to click search)
        <span class="badge bg-info ms-2" id="filter-status">Ready</span>
        <span class="spinner-border spinner-border-sm text-primary ms-2" id="loading-spinner" style="display: none;" role="status"></span>
    </div>
    <div class="card-body">
        <div class="row">
            <input type="hidden" name="tab" value="monitoring">
            
            <div class="col-md-3">
                <label class="form-label">Year Level</label>
                <select name="year_level" class="form-control filter-select" id="year_level">
                    <option value="">All Year Levels</option>
                    <?php foreach($year_levels as $year): ?>
                    <option value="<?php echo $year; ?>">
                        <?php echo $year; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Strand/Section</label>
                <select name="section_id" class="form-control filter-select" id="section_id">
                    <option value="">All Strands</option>
                    <?php 
                    $strands->data_seek(0);
                    while($strand = $strands->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $strand['section_id']; ?>">
                        <?php echo $strand['section_name']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Semester</label>
                <select name="semester" class="form-control filter-select" id="semester">
                    <option value="">All Semesters</option>
                    <option value="First">First Semester</option>
                    <option value="Second">Second Semester</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Payment Status</label>
                <select name="status" class="form-control filter-select" id="status">
                    <option value="">All Status</option>
                    <option value="paid">Paid</option>
                    <option value="partial">Partial</option>
                    <option value="unpaid">Unpaid</option>
                </select>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <small class="text-muted">
                    <i class="fas fa-bolt"></i> Changes apply automatically
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Payment Status Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-table me-2"></i>Student Payment Status
        <span class="badge bg-secondary" id="row-count"><?php echo count($initial_students); ?> row(s)</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="monitoringTable">
                <thead class="table-dark">
                    <tr>
                        <th rowspan="2" class="text-center align-middle">#</th>
                        <th rowspan="2" class="align-middle">Student #</th>
                        <th rowspan="2" class="align-middle">Name</th>
                        <th rowspan="2" class="align-middle">Year Level</th>
                        <th rowspan="2" class="align-middle">Strand</th>
                        <th colspan="4" class="text-center">First Semester</th>
                        <th colspan="4" class="text-center">Second Semester</th>
                        <th rowspan="2" class="align-middle">Total Paid</th>
                        <th rowspan="2" class="align-middle">Balance</th>
                    </tr>
                    <tr>
                        <th class="text-center">Q1</th>
                        <th class="text-center">Q2</th>
                        <th class="text-center">Q3</th>
                        <th class="text-center">Q4</th>
                        <th class="text-center">Q1</th>
                        <th class="text-center">Q2</th>
                        <th class="text-center">Q3</th>
                        <th class="text-center">Q4</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php if (empty($initial_students)): ?>
                    <tr>
                        <td colspan="16" class="text-center text-muted">
                            <i class="fas fa-info-circle"></i> No data available
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $counter = 1;
                        foreach($initial_students as $student): 
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
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Legend -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <span class="me-3">
                    <span class="badge bg-success">●</span> Paid (Full payment)
                </span>
                <span class="me-3">
                    <span class="badge bg-warning text-dark">●</span> Partial (Partial payment)
                </span>
                <span class="me-3">
                    <span class="badge bg-danger">●</span> Unpaid (No payment)
                </span>
                <span class="me-3">
                    <i class="fas fa-check-circle text-success"></i> - Fully Paid
                </span>
                <span class="me-3">
                    <i class="fas fa-adjust text-warning"></i> - Partial
                </span>
                <span class="me-3">
                    <i class="fas fa-times-circle text-danger"></i> - Unpaid
                </span>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let timeoutId;
    
    // REAL-TIME FILTERING: Trigger on any select change
    $('.filter-select').on('change', function() {
        clearTimeout(timeoutId);
        $('#loading-spinner').show();
        $('#filter-status').text('Updating...');
        
        // Wait 300ms after last change before loading
        timeoutId = setTimeout(function() {
            loadFilteredData();
        }, 300);
    });
    
    function loadFilteredData() {
        // Get filter values
        var year_level = $('#year_level').val();
        var section_id = $('#section_id').val();
        var semester = $('#semester').val();
        var status = $('#status').val();
        
        // Build query string
        var queryString = 'year_level=' + encodeURIComponent(year_level) +
                         '&section_id=' + encodeURIComponent(section_id) +
                         '&semester=' + encodeURIComponent(semester) +
                         '&status=' + encodeURIComponent(status);
        
        // AJAX request
        $.ajax({
            url: 'ajax/get_payment_data.php?' + queryString,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                // Update table
                $('#table-body').html(response.table_html);
                
                // Update summary cards
                if (response.summary) {
                    updateSummaryCards(response.summary);
                }
                
                // Update row count
                var rowCount = $('#table-body tr').length;
                $('#row-count').text(rowCount + ' row(s)');
                
                // Update status
                $('#loading-spinner').hide();
                $('#filter-status').text('Updated at ' + new Date().toLocaleTimeString());
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                $('#loading-spinner').hide();
                $('#filter-status').text('Error updating data');
            }
        });
    }
    
    function updateSummaryCards(summary) {
        if (!summary) return;
        
        var summaryHtml = `
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Students</h5>
                            <h3>${numberFormat(summary.total_students || 0)}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Fully Paid</h5>
                            <h3>${numberFormat(summary.fully_paid_count || 0)}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Partial</h5>
                            <h3>${numberFormat(summary.partial_count || 0)}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5 class="card-title">Unpaid</h5>
                            <h3>${numberFormat(summary.unpaid_count || 0)}</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Collection</h5>
                            <h3>₱ ${numberFormat(summary.total_collected || 0, 2)}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Receivable</h5>
                            <h3>₱ ${numberFormat(summary.total_receivable || 0, 2)}</h3>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#summary-cards').html(summaryHtml);
    }
    
    function numberFormat(number, decimals = 0) {
        return new Intl.NumberFormat('en-PH', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(number);
    }
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        loadFilteredData();
    }, 30000);
});

function exportToExcel() {
    var table = document.getElementById('monitoringTable');
    var html = table.outerHTML;
    var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    var link = document.createElement('a');
    link.download = 'payment_monitoring_<?php echo date('Y-m-d'); ?>.xls';
    link.href = url;
    link.click();
}

function exportToPDF() {
    alert('PDF export functionality will be implemented here');
}
</script>