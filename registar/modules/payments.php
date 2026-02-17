<?php
// modules/payments.php
require_once 'includes/functions.php';

// Ensure session is started at the beginning of the script
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if cash drawer is open
$today = date('Y-m-d');
$drawer = $conn->query("SELECT * FROM cash_drawer WHERE drawer_date = '$today' AND status = 'open'");

if ($drawer->num_rows == 0) {
    echo '<div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            Cash drawer is not open for today. Please open the cash drawer first.
            <a href="?tab=cash_drawer" class="btn btn-sm btn-warning ms-3">Open Drawer</a>
          </div>';
    return;
}

$drawer_data = $drawer->fetch_assoc();

// Process payment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_payment'])) {
    $installment_ids = $_POST['installment_ids'];
    $amounts = $_POST['amounts'];
    $total_amount = (float)$_POST['total_amount'];
    $cash_received = (float)$_POST['cash_received'];
    $drawer_id = (int)$_POST['drawer_id'];
    $student_name = $conn->real_escape_string($_POST['student_name']);
    
    $receipt_number = generateReceiptNumber($conn);
    $payment_success = true;
    
    // Start transaction
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
                    END,
                    updated_at = NOW()
                WHERE id = $installment_id
            ");
            
            if ($conn->affected_rows === -1) {
                throw new Exception("Failed to update installment");
            }
            
            // Record payment
            $conn->query("
                INSERT INTO cash_payments (installment_id, drawer_id, amount, receipt_number, payment_date)
                VALUES ($installment_id, $drawer_id, $amount, '$receipt_number', NOW())
            ");
            
            if ($conn->affected_rows === -1) {
                throw new Exception("Failed to insert payment record");
            }
        }
        
        // Update cash drawer total
        $conn->query("
            UPDATE cash_drawer 
            SET total_cash_in = total_cash_in + $total_amount,
                updated_at = NOW()
            WHERE id = $drawer_id
        ");
        
        if ($conn->affected_rows === -1) {
            throw new Exception("Failed to update cash drawer");
        }
        
        // Commit transaction
        $conn->commit();
        
        // Set session variables for success message
        $_SESSION['payment_success'] = true;
        $_SESSION['payment_data'] = [
            'receipt_number' => $receipt_number,
            'total_amount' => $total_amount,
            'cash_received' => $cash_received,
            'student_name' => $student_name,
            'change_amount' => $cash_received - $total_amount
        ];
        
        // Redirect to clear POST data and show success
        header("Location: ?tab=payments&success=1");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}

// Check for success in session or URL
$show_success = false;
$payment_data = [];

if (isset($_SESSION['payment_success']) && $_SESSION['payment_success'] === true) {
    $show_success = true;
    $payment_data = $_SESSION['payment_data'];
    
    // Clear session data
    unset($_SESSION['payment_success']);
    unset($_SESSION['payment_data']);
    
} elseif (isset($_GET['success']) && $_GET['success'] == 1) {
    // For backward compatibility with URL parameters
    $show_success = true;
    $payment_data = [
        'receipt_number' => isset($_GET['receipt']) ? $_GET['receipt'] : 'N/A',
        'total_amount' => isset($_GET['amount']) ? $_GET['amount'] : 0,
        'student_name' => isset($_GET['student']) ? urldecode($_GET['student']) : 'Student',
        'change_amount' => isset($_GET['change']) ? $_GET['change'] : 0
    ];
}

// Display success message if payment was successful
if (isset($_SESSION['payment_success']) && $_SESSION['payment_success']) {
    $payment_data = $_SESSION['payment_data'];
    echo '<div class="alert alert-success">';
    echo '<strong>Payment Successful!</strong><br>';
    echo 'Receipt Number: ' . $payment_data['receipt_number'] . '<br>';
    echo 'Total Amount Paid: ' . number_format($payment_data['total_amount'], 2) . '<br>';
    echo 'Cash Received: ' . number_format($payment_data['cash_received'], 2) . '<br>';
    echo 'Change: ' . number_format($payment_data['change_amount'], 2) . '<br>';
    echo '</div>';

    // Clear session variables after displaying the message
    unset($_SESSION['payment_success']);
    unset($_SESSION['payment_data']);
}

// Get search query
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-money-bill me-2"></i>Process Payment</h2>
</div>

<div class="row">
    <div class="col-md-5">
        <!-- Student Search -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-search me-2"></i>Search Student
            </div>
            <div class="card-body">
                <input type="text" id="searchStudent" class="form-control mb-3" 
                       placeholder="Enter student name or number... (min 3 characters)">
                
                <div id="searchResults" class="list-group" style="max-height: 400px; overflow-y: auto; display: none;">
                    <!-- Results will appear here -->
                </div>
            </div>
        </div>
        
        <!-- Selected Student Info -->
        <div class="card mt-3" id="selectedStudentCard" style="display: none;">
            <div class="card-header bg-info text-white">
                <i class="fas fa-user me-2"></i>Selected Student
            </div>
            <div class="card-body">
                <h5 id="selectedStudentName"></h5>
                <p id="selectedStudentDetails" class="mb-0"></p>
                <input type="hidden" id="selectedStudentId">
                <input type="hidden" id="selectedStudentFullName">
            </div>
        </div>
    </div>
    
    <div class="col-md-7">
        <!-- Payment Form -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-credit-card me-2"></i>Payment Details
            </div>
            <div class="card-body">
                <form method="POST" id="paymentForm" onsubmit="return validateForm()">
                    <input type="hidden" name="drawer_id" value="<?php echo $drawer_data['id']; ?>">
                    <input type="hidden" name="student_name" id="studentNameInput" value="">
                    <input type="hidden" name="process_payment" value="1">
                    
                    <div id="installmentsList">
                        <p class="text-muted text-center">Select a student to view unpaid installments</p>
                    </div>
                    
                    <div id="paymentSection" style="display: none;">
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Total Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" id="totalAmount" name="total_amount" 
                                           class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cash Received</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" id="cashReceived" name="cash_received" 
                                           class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Change</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" id="changeAmount" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-success" 
                                    id="processPaymentBtn" onclick="confirmPayment()">
                                <i class="fas fa-check-circle"></i> Process Payment
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payment Confirmation Modal -->
<div class="modal fade" id="paymentConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Confirm Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-money-bill-wave fa-4x text-success mb-3"></i>
                    <h4>Review Payment Details</h4>
                </div>
                
                <table class="table table-bordered">
                    <tr>
                        <th>Student:</th>
                        <td id="confirmStudentName"></td>
                    </tr>
                    <tr>
                        <th>Number of Items:</th>
                        <td id="confirmItemCount"></td>
                    </tr>
                    <tr class="table-success">
                        <th>Total Amount:</th>
                        <td><strong>₱<span id="confirmTotalAmount"></span></strong></td>
                    </tr>
                    <tr>
                        <th>Cash Received:</th>
                        <td>₱<span id="confirmCashReceived"></span></td>
                    </tr>
                    <tr>
                        <th>Change:</th>
                        <td>₱<span id="confirmChange"></span></td>
                    </tr>
                </table>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Please verify the payment details before confirming.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="submitPayment()">
                    <i class="fas fa-check"></i> Confirm Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Success Modal -->
<div class="modal fade" id="paymentSuccessModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Payment Successful!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                <h4 class="mb-3">Payment Completed Successfully</h4>
                
                <div class="alert alert-success">
                    <p class="mb-1"><strong>Receipt #:</strong> <span id="successReceipt"></span></p>
                    <p class="mb-1"><strong>Amount Paid:</strong> ₱<span id="successAmount"></span></p>
                    <p class="mb-1"><strong>Cash Received:</strong> ₱<span id="successCash"></span></p>
                    <p class="mb-1"><strong>Change:</strong> ₱<span id="successChange"></span></p>
                    <p class="mb-0"><strong>Student:</strong> <span id="successStudent"></span></p>
                </div>
                
                <div class="mt-3">
                    <button class="btn btn-primary" onclick="printReceipt()">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                    <button class="btn btn-success" onclick="closeSuccessModal()">
                        <i class="fas fa-check"></i> Done
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script data-module="payments-search">
// Global variables
var selectedInstallments = [];

// Initialize payment search when jQuery is available
if (typeof jQuery !== 'undefined') {
    $(document).ready(function() {
        initializePaymentSearch();
        <?php if ($show_success): ?>
        showPaymentSuccess(<?php echo json_encode($payment_data); ?>);
        <?php endif; ?>
    });
} else {
    var checkJQuery = setInterval(function() {
        if (typeof jQuery !== 'undefined') {
            clearInterval(checkJQuery);
            $(document).ready(function() {
                initializePaymentSearch();
                <?php if ($show_success): ?>
                showPaymentSuccess(<?php echo json_encode($payment_data); ?>);
                <?php endif; ?>
            });
        }
    }, 50);
}

function initializePaymentSearch() {
    var $ = jQuery;
    
    // Real-time search student
    $('#searchStudent').on('keyup', function() {
        var search = $(this).val().trim();
        
        if (search.length > 2) {
            $.ajax({
                url: 'api/search_student.php',
                method: 'POST',
                data: {search: search},
                success: function(response) {
                    if (response.trim() !== '') {
                        $('#searchResults').html(response).show();
                    } else {
                        $('#searchResults').hide();
                    }
                },
                error: function() {
                    $('#searchResults').html('<div class="alert alert-danger">Error searching students.</div>').show();
                }
            });
        } else {
            $('#searchResults').hide().empty();
        }
    });
    
    // Select student
    $(document).on('click', '.student-result', function(e) {
        e.preventDefault();
        var studentId = $(this).data('id');
        var studentName = $(this).data('name');
        var studentDetails = $(this).data('details');
        var studentFullName = $(this).data('fullname');
        
        $('#selectedStudentId').val(studentId);
        $('#selectedStudentFullName').val(studentFullName);
        $('#studentNameInput').val(studentFullName);
        $('#selectedStudentName').text(studentName);
        $('#selectedStudentDetails').text(studentDetails);
        $('#selectedStudentCard').show();
        $('#searchResults').hide().empty();
        $('#searchStudent').val('');
        
        // Clear previous selections
        selectedInstallments = [];
        $('#installmentsList').empty();
        
        loadUnpaidInstallments(studentId);
    });
    
    // Load unpaid installments
    function loadUnpaidInstallments(studentId) {
        $.ajax({
            url: 'api/get_unpaid_installments.php',
            method: 'POST',
            data: {student_id: studentId},
            success: function(response) {
                $('#installmentsList').html(response);
                $('#paymentSection').show();
                
                // Reset selected installments array
                selectedInstallments = [];
                
                // Attach change event to checkboxes
                attachCheckboxEvents();
            },
            error: function() {
                $('#installmentsList').html('<div class="alert alert-danger">Error loading installments</div>');
            }
        });
    }
    
    // Function to attach events to checkboxes
    function attachCheckboxEvents() {
        $('.installment-checkbox').off('change').on('change', function() {
            updateSelectedInstallments();
        });
    }
    
    // Function to update selected installments
    function updateSelectedInstallments() {
        var total = 0;
        selectedInstallments = [];
        
        $('.installment-checkbox:checked').each(function() {
            var remaining = parseFloat($(this).data('remaining'));
            var installmentId = $(this).val();
            
            if (!isNaN(remaining) && remaining > 0) {
                total += remaining;
                selectedInstallments.push({
                    id: installmentId,
                    amount: remaining
                });
            }
        });
        
        if (selectedInstallments.length > 0) {
            $('#totalAmount').val(total.toFixed(2));
            calculateChange();
        } else {
            $('#totalAmount').val('');
            $('#changeAmount').val('0.00');
            $('#processPaymentBtn').prop('disabled', true);
        }
    }
    
    // Calculate change
    $('#cashReceived').on('keyup change', function() {
        calculateChange();
    });
    
    function calculateChange() {
        var total = parseFloat($('#totalAmount').val()) || 0;
        var received = parseFloat($('#cashReceived').val()) || 0;
        var change = received - total;
        
        if (change >= 0 && total > 0 && selectedInstallments.length > 0) {
            $('#changeAmount').val(change.toFixed(2));
            $('#processPaymentBtn').prop('disabled', false);
        } else {
            $('#changeAmount').val('0.00');
            $('#processPaymentBtn').prop('disabled', true);
        }
    }
    
    // Initialize by attaching events
    attachCheckboxEvents();
}

// Form validation function
function validateForm() {
    if (selectedInstallments.length === 0) {
        toastr.warning('Please select at least one installment to pay.');
        return false;
    }
    
    var total = parseFloat($('#totalAmount').val()) || 0;
    var received = parseFloat($('#cashReceived').val()) || 0;
    
    if (received < total) {
        toastr.error('Cash received must be greater than or equal to total amount.');
        return false;
    }
    
    if (received <= 0) {
        toastr.error('Please enter cash received amount.');
        return false;
    }
    
    return true;
}

// Function to show payment confirmation
function confirmPayment() {
    if (!validateForm()) {
        return false;
    }
    
    // Get student name
    var studentName = $('#selectedStudentFullName').val() || $('#selectedStudentName').text() || 'Selected Student';
    var total = $('#totalAmount').val();
    var received = $('#cashReceived').val();
    var change = (parseFloat(received) - parseFloat(total)).toFixed(2);
    
    // Update confirmation modal
    $('#confirmStudentName').text(studentName);
    $('#confirmItemCount').text(selectedInstallments.length);
    $('#confirmTotalAmount').text(total);
    $('#confirmCashReceived').text(received);
    $('#confirmChange').text(change);
    
    // Show confirmation modal
    var confirmModal = new bootstrap.Modal(document.getElementById('paymentConfirmModal'));
    confirmModal.show();
}

// Function to submit payment after confirmation
function submitPayment() {
    // Hide confirmation modal
    bootstrap.Modal.getInstance(document.getElementById('paymentConfirmModal')).hide();
    
    // Show loading toast
    toastr.info('Processing payment...', 'Please wait', {
        timeOut: 0,
        extendedTimeOut: 0,
        closeButton: false,
        progressBar: true
    });
    
    // Remove any previously added dynamic inputs
    $('input[name="installment_ids[]"], input[name="amounts[]"]').remove();
    
    // Add hidden inputs for selected installments
    selectedInstallments.forEach(function(item) {
        $('<input>').attr({
            type: 'hidden',
            name: 'installment_ids[]',
            value: item.id
        }).appendTo('#paymentForm');
        
        $('<input>').attr({
            type: 'hidden',
            name: 'amounts[]',
            value: item.amount
        }).appendTo('#paymentForm');
    });
    
    // Submit the form
    document.getElementById('paymentForm').submit();
}

// Function to show payment success
function showPaymentSuccess(data) {
    console.log('Showing payment success:', data);
    
    // Format amounts
    var total = parseFloat(data.total_amount).toFixed(2);
    var cash = parseFloat(data.cash_received || 0).toFixed(2);
    var change = parseFloat(data.change_amount || (cash - total)).toFixed(2);
    
    // Update success modal
    $('#successReceipt').text(data.receipt_number || 'N/A');
    $('#successAmount').text(total);
    $('#successCash').text(cash);
    $('#successChange').text(change);
    $('#successStudent').text(data.student_name || 'Student');
    
    // Show success modal
    var successModal = new bootstrap.Modal(document.getElementById('paymentSuccessModal'));
    successModal.show();
    
    // Show toastr notifications
    toastr.success('Payment of ₱' + total + ' processed successfully!', 'Payment Successful');
    toastr.info('Receipt #: ' + (data.receipt_number || 'N/A'), 'Receipt Number');
    
    // Open receipt in new window
    if (data.receipt_number) {
        setTimeout(function() {
            window.open('receipts/print_receipt.php?receipt=' + data.receipt_number, '_blank');
        }, 1000);
    }
}

// Function to print receipt
function printReceipt() {
    var receipt = $('#successReceipt').text();
    if (receipt && receipt !== 'N/A') {
        window.open('receipts/print_receipt.php?receipt=' + receipt, '_blank');
    }
}

// Function to close success modal
function closeSuccessModal() {
    bootstrap.Modal.getInstance(document.getElementById('paymentSuccessModal')).hide();
    
    // Reset the form for new payment
    resetForm();
    
    // Clear selected student
    $('#selectedStudentCard').hide();
    $('#selectedStudentId').val('');
    $('#selectedStudentFullName').val('');
    $('#studentNameInput').val('');
}

// Function to reset form
function resetForm() {
    $('.installment-checkbox').prop('checked', false);
    $('#totalAmount').val('');
    $('#cashReceived').val('');
    $('#changeAmount').val('0.00');
    $('#processPaymentBtn').prop('disabled', true);
    selectedInstallments = [];
}

// Add keyboard shortcut (F8) for quick payment processing
$(document).keydown(function(e) {
    if (e.key === 'F8' || e.keyCode === 119) {
        e.preventDefault();
        if ($('#processPaymentBtn').prop('disabled') === false) {
            confirmPayment();
        }
    }
});

// Add tooltip for keyboard shortcut
$(document).ready(function() {
    $('#processPaymentBtn').attr('title', 'Press F8 to process payment');
});
</script>