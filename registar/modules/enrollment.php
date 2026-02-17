<?php
// modules/enrollment.php
require_once 'includes/functions.php';

// Start session for flash messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get active academic year
$active_ay = getActiveAcademicYear($conn);
if (!$active_ay) {
    echo '<div class="alert alert-warning">Please set an active academic year first.</div>';
    return;
}

// Get counts for display only
$total_students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$enrolled_students = $conn->query("SELECT COUNT(*) as total FROM students WHERE enrollment_status = 'active'")->fetch_assoc()['total'];
$unenrolled_students = $conn->query("SELECT COUNT(*) as total FROM students WHERE enrollment_status = 'inactive'")->fetch_assoc()['total'];

// FIXED: ONLY get unenrolled students (no tabs needed)
$students_query = "
    SELECT s.student_id, s.student_number, s.year_level, s.section_id, s.enrollment_status,
           u.first_name, u.middle_name, u.last_name, sec.section_name
    FROM students s
    JOIN users u ON s.user_id = u.id
    JOIN sections sec ON s.section_id = sec.section_id
    WHERE s.enrollment_status = 'inactive'
    ORDER BY u.last_name, u.first_name";

$students = $conn->query($students_query);

// GET payment terms
$terms = $conn->query("SELECT * FROM payment_terms");

// GET all sections
$sections = $conn->query("SELECT * FROM sections ORDER BY section_name");

// HANDLE enrollment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enroll_student'])) {
    $student_id = (int)$_POST['student_id'];
    $ay_id = (int)$_POST['ay_id'];
    $semester = $conn->real_escape_string($_POST['semester']);
    $year_level = $conn->real_escape_string($_POST['year_level']);
    $section_id = (int)$_POST['section_id'];
    $term_id = (int)$_POST['term_id'];
    $base_fees = (float)$_POST['base_fees'];
    $payment_per_installment = (float)$_POST['payment_per_installment'];
    $total_fees = $base_fees;
    
    // Validate
    if ($base_fees <= 0) {
        $_SESSION['error_message'] = 'Invalid fee amount. Please check fee structure.';
        header("Location: index.php?tab=enrollment");
        exit;
    }
    
    // Check if already enrolled this semester
    $check = $conn->query("
        SELECT id FROM enrollments 
        WHERE student_id = $student_id 
        AND ay_id = $ay_id 
        AND semester = '$semester'
    ");
    
    if ($check->num_rows > 0) {
        $_SESSION['error_message'] = 'Student already enrolled for this semester!';
        header("Location: index.php?tab=enrollment");
        exit;
    }
    
    // Get student info
    $student_info = $conn->query("
        SELECT u.first_name, u.middle_name, u.last_name, s.student_number 
        FROM students s
        JOIN users u ON s.user_id = u.id
        WHERE s.student_id = $student_id
    ")->fetch_assoc();
    
    $student_name = $student_info['first_name'] . ' ' . 
                    ($student_info['middle_name'] ? $student_info['middle_name'] . ' ' : '') . 
                    $student_info['last_name'];
    $student_number = $student_info['student_number'];
    
    // Insert enrollment
    $conn->query("
        INSERT INTO enrollments 
        (student_id, ay_id, semester, year_level, section_id, term_id, base_fees, payment_per_installment, total_fees)
        VALUES 
        ($student_id, $ay_id, '$semester', '$year_level', $section_id, $term_id, $base_fees, $payment_per_installment, $total_fees)
    ");
    
    $enrollment_id = $conn->insert_id;
    
    // Update student status to 'active'
    $conn->query("
        UPDATE students 
        SET enrollment_status = 'active' 
        WHERE student_id = $student_id
    ");
    
    // Generate installments
    $installment_names = getInstallmentNames($term_id, $semester);
    $num_installments = count($installment_names);
    
    for ($i = 0; $i < $num_installments; $i++) {
        $installment_num = $i + 1;
        $installment_name = $installment_names[$i];
        
        if ($term_id == 1) {
            $amount_due = $base_fees / 4;
        } else {
            $amount_due = $payment_per_installment;
        }
        
        $due_date = date('Y-m-d', strtotime("+".($i*30)." days"));
        
        $conn->query("
            INSERT INTO payment_installments 
            (enrollment_id, installment_number, installment_name, amount_due, due_date)
            VALUES 
            ($enrollment_id, $installment_num, '$installment_name', $amount_due, '$due_date')
        ");
    }
    
    // Get payment term name
    $term_name = $conn->query("SELECT term_name FROM payment_terms WHERE id = $term_id")->fetch_assoc()['term_name'];
    
    // STORE SUCCESS DATA IN SESSION
    $_SESSION['enrollment_success'] = [
        'student_name' => $student_name,
        'student_number' => $student_number,
        'semester' => $semester,
        'term_name' => $term_name,
        'base_fees' => $base_fees,
        'num_installments' => $num_installments
    ];
    
    header("Location: index.php?tab=enrollment&success=1");
    exit;
}

// DISPLAY SUCCESS MESSAGE IF EXISTS
if (isset($_GET['success']) && isset($_SESSION['enrollment_success'])) {
    $data = $_SESSION['enrollment_success'];
    ?>
    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Enrollment Successful!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-graduate fa-4x text-success"></i>
                    </div>
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Student Name:</th>
                            <td><?php echo $data['student_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Student Number:</th>
                            <td><?php echo $data['student_number']; ?></td>
                        </tr>
                        <tr>
                            <th>Semester:</th>
                            <td><?php echo $data['semester']; ?> Semester</td>
                        </tr>
                        <tr>
                            <th>Payment Term:</th>
                            <td><?php echo $data['term_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Total Fee:</th>
                            <td>₱ <?php echo number_format($data['base_fees'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Installments:</th>
                            <td><?php echo $data['num_installments']; ?> payments</td>
                        </tr>
                    </table>
                    <div class="alert alert-success mt-3 mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Student status updated to <strong>ENROLLED</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                        <i class="fas fa-check"></i> OK
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    });
    </script>
    <?php
    unset($_SESSION['enrollment_success']);
}

// DISPLAY ERROR MESSAGE IF EXISTS
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    echo '<i class="fas fa-exclamation-triangle me-2"></i>' . $_SESSION['error_message'];
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
    unset($_SESSION['error_message']);
}
?>

<!-- HEADER with COUNTS (for info only) -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-graduate me-2"></i>Enroll Student</h2>
    <div>
        <span class="badge bg-info p-2 me-2">
            <i class="fas fa-users"></i> Total: <?php echo $total_students; ?>
        </span>
        <span class="badge bg-success p-2 me-2">
            <i class="fas fa-check-circle"></i> Enrolled: <?php echo $enrolled_students; ?>
        </span>
        <span class="badge bg-warning p-2">
            <i class="fas fa-clock"></i> Unenrolled: <?php echo $unenrolled_students; ?>
        </span>
    </div>
</div>

<!-- Simple info banner - no tabs -->
<div class="alert alert-info mb-3">
    <i class="fas fa-info-circle me-2"></i>
    Showing <strong><?php echo $students->num_rows; ?></strong> unenrolled students ready for enrollment.
</div>

<!-- MAIN FORM -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-edit me-2"></i>Enrollment Form
            </div>
            <div class="card-body">
                
                <!-- NO STUDENTS WARNING -->
                <?php if ($students->num_rows == 0): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    No unenrolled students found. 
                    <a href="?tab=students">Add new students</a> first.
                </div>
                <?php endif; ?>
                
                <!-- FORM -->
                <form method="POST" id="enrollmentForm">
                    <input type="hidden" name="ay_id" value="<?php echo $active_ay['id']; ?>">
                    <input type="hidden" name="base_fees" id="hidden_base_fees" value="0">
                    <input type="hidden" name="payment_per_installment" id="hidden_payment_per_installment" value="0">
                    
                    <!-- STUDENT SELECT -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Student <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-control" id="studentSelect" required>
                            <option value="">-- Choose Unenrolled Student --</option>
                            <?php 
                            if ($students->num_rows > 0) {
                                $students->data_seek(0);
                                while($student = $students->fetch_assoc()): 
                                    $middle = $student['middle_name'] ? ' ' . $student['middle_name'] : '';
                            ?>
                            <option value="<?php echo $student['student_id']; ?>" 
                                    data-year="<?php echo $student['year_level']; ?>"
                                    data-section="<?php echo $student['section_id']; ?>">
                                <?php echo $student['student_number'] . ' - ' . $student['last_name'] . ', ' . $student['first_name'] . $middle; ?>
                                (<?php echo $student['section_name']; ?>)
                            </option>
                            <?php 
                                endwhile; 
                            }
                            ?>
                        </select>
                        <small class="text-muted">
                            <?php echo $students->num_rows; ?> unenrolled students available
                        </small>
                    </div>
                    
                    <!-- ACADEMIC DETAILS -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Academic Year</label>
                            <input type="text" class="form-control" value="<?php echo $active_ay['school_year']; ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                            <select name="semester" class="form-control" id="semester" required>
                                <option value="First">First Semester</option>
                                <option value="Second">Second Semester</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- YEAR LEVEL & SECTION -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Year Level <span class="text-danger">*</span></label>
                            <select name="year_level" id="yearLevel" class="form-control" required>
                                <option value="">-- Select Year Level --</option>
                                <option value="Grade 11">Grade 11</option>
                                <option value="Grade 12">Grade 12</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Strand/Section <span class="text-danger">*</span></label>
                            <select name="section_id" id="sectionId" class="form-control" required>
                                <option value="">Select Strand</option>
                                <?php 
                                $sections->data_seek(0);
                                while($section = $sections->fetch_assoc()):
                                ?>
                                <option value="<?php echo $section['section_id']; ?>">
                                    <?php echo $section['section_name']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- PAYMENT DETAILS -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Payment Term <span class="text-danger">*</span></label>
                            <select name="term_id" class="form-control" id="termSelect" required>
                                <option value="">-- Select Term --</option>
                                <?php 
                                $terms->data_seek(0);
                                while($term = $terms->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $term['id']; ?>" 
                                        data-payments="<?php echo $term['number_of_payments']; ?>">
                                    <?php echo $term['term_name']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Semester Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="text" id="display_base_fees" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Per Installment</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="text" id="display_payment_per_installment" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Total Fees</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="text" id="totalFees" class="form-control fw-bold text-success" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <!-- PREVIEW TABLE -->
                    <div class="card mt-3 bg-light">
                        <div class="card-header bg-secondary text-white">
                            <i class="fas fa-table me-2"></i>Payment Schedule Preview
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-bordered" id="previewTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Installment</th>
                                        <th>Amount Due</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody id="previewBody">
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            Select payment term to preview installments
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- BUTTONS -->
                    <div class="mt-4">
                        <button type="submit" name="enroll_student" class="btn btn-success">
                            <i class="fas fa-save"></i> Enroll Student
                        </button>
                        <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                        <a href="?tab=enrollment" class="btn btn-info">
                            <i class="fas fa-sync"></i> Refresh
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- SIDEBAR INFO -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <i class="fas fa-info-circle me-2"></i>Enrollment Guide
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fas fa-check-circle text-success me-2"></i>Select unenrolled student</li>
                    <li><i class="fas fa-check-circle text-success me-2"></i>Choose semester</li>
                    <li><i class="fas fa-check-circle text-success me-2"></i>Select payment term</li>
                    <li><i class="fas fa-check-circle text-success me-2"></i>Verify fee amount</li>
                    <li><i class="fas fa-check-circle text-success me-2"></i>Submit enrollment</li>
                </ul>
                <hr>
                <p class="small text-muted mb-0">
                    <i class="fas fa-info-circle"></i>
                    After enrollment, student status becomes "Enrolled"
                </p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-warning">
                <i class="fas fa-credit-card me-2"></i>Payment Terms
            </div>
            <div class="card-body">
                <ul>
                    <li><strong>Full Payment:</strong> 4 quarters (₱<span id="sampleFull">0</span> each)</li>
                    <li><strong>Per Semester:</strong> 2 installments</li>
                    <li><strong>Per Quarter:</strong> 4 installments</li>
                </ul>
                <hr>
                <p class="small text-muted mb-0">* All payments are CASH only</p>
            </div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT (same as before) -->
<script>
// Student selection
document.getElementById('studentSelect')?.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const yearLevel = selected.dataset.year;
    const sectionId = selected.dataset.section;
    
    document.getElementById('yearLevel').value = yearLevel || '';
    document.getElementById('sectionId').value = sectionId || '';
    
    if (sectionId && yearLevel) {
        loadStudentFee(sectionId, yearLevel);
    }
});

// Term selection
document.getElementById('termSelect')?.addEventListener('change', calculateInstallments);

// Reset form
function resetForm() {
    if (confirm('Reset all fields?')) {
        document.getElementById('enrollmentForm').reset();
        document.getElementById('display_base_fees').value = '';
        document.getElementById('display_payment_per_installment').value = '';
        document.getElementById('totalFees').value = '';
        document.getElementById('hidden_base_fees').value = '0';
        document.getElementById('hidden_payment_per_installment').value = '0';
        document.getElementById('previewBody').innerHTML = '<tr><td colspan="3" class="text-center text-muted">Select payment term to preview installments</td></tr>';
    }
}

// Load fee function
function loadStudentFee(sectionId, yearLevel) {
    fetch('api/get_student_fees.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'section_id=' + sectionId + '&year_level=' + encodeURIComponent(yearLevel)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.semester_fee > 0) {
            document.getElementById('display_base_fees').value = parseFloat(data.semester_fee).toFixed(2);
            document.getElementById('hidden_base_fees').value = data.semester_fee;
            document.getElementById('sampleFull').textContent = (data.semester_fee/4).toFixed(0);
            
            if (document.getElementById('termSelect').value) {
                calculateInstallments();
            }
        } else {
            document.getElementById('display_base_fees').value = '0.00';
            document.getElementById('hidden_base_fees').value = '0';
            alert('No fee configured for this strand/year level');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('display_base_fees').value = '0.00';
        document.getElementById('hidden_base_fees').value = '0';
    });
}

// Calculate installments
function calculateInstallments() {
    const termId = document.getElementById('termSelect').value;
    const baseFee = parseFloat(document.getElementById('hidden_base_fees').value) || 0;
    
    if (!termId || baseFee <= 0) return;
    
    fetch('api/calculate_installments.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'term_id=' + termId + '&semester_fee=' + baseFee
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('display_payment_per_installment').value = parseFloat(data.per_installment).toFixed(2);
            document.getElementById('hidden_payment_per_installment').value = data.per_installment;
            document.getElementById('totalFees').value = '₱' + baseFee.toFixed(2);
            
            updatePreview(termId, baseFee, data.per_installment);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Update preview table
function updatePreview(termId, baseFee, perInstallment) {
    const termNames = {
        1: ['Full Payment - 1st Sem'],  // ✅ Isang item lang for Full Payment
        2: ['1st Installment', '2nd Installment'],
        3: ['Quarter 1', 'Quarter 2', 'Quarter 3', 'Quarter 4']
    };
    
    const names = termNames[termId] || [];
    const semester = document.getElementById('semester').value;
    const semText = semester === 'First' ? '1st Sem' : '2nd Sem';
    
    let html = '';
    for (let i = 0; i < names.length; i++) {
        // ✅ Full Payment: isang bagsakan ang total fee
        // ✅ Per Semester: 2 installments
        // ✅ Per Quarter: 4 installments
        let amount;
        if (termId == 1) { // Full Payment
            amount = baseFee; // ✅ BUONG SEMESTER FEE, hindi hinati
        } else {
            amount = perInstallment;
        }
        
        const dueDate = new Date();
        dueDate.setMonth(dueDate.getMonth() + i);
        
        html += '<tr>';
        html += '<td>' + semText + ' - ' + names[i] + '</td>';
        html += '<td>₱' + amount.toFixed(2) + '</td>';
        html += '<td>' + dueDate.toLocaleDateString() + '</td>';
        html += '</tr>';
    }
    
    document.getElementById('previewBody').innerHTML = html;
}
</script>

<style>
.nav-pills .nav-link.active {
    background-color: #0d6efd;
}
.card-header {
    font-weight: 600;
}
.table td, .table th {
    vertical-align: middle;
}
.badge {
    font-size: 0.9rem;
    padding: 0.5rem 0.8rem;
}
</style>