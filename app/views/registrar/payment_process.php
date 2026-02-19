<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Payment - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/payment_process.css">
</head>

<body>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;" id="toastContainer"></div>

    <div class="d-flex">
        <!-- sidebar -->
        <div class="sidenav">
            <div class="sidenav-header">
                <div class="school-brand">
                    <div class="school-logo">
                        <img src="assets/DCSA-LOGO.png" alt="School Logo"
                            style="width: 100%; height: 100%; object-fit: contain; border-radius: 0.75rem;">
                    </div>
                    <div class="school-info">
                        <h5>Datamex College of Saint Adeline</h5>
                        <p class="subtitle">Learning Management System</p>
                    </div>
                </div>
            </div>
            <ul class="sidenav-menu">
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=registrar_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=enrollment_create">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Enroll Student</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=payment_process">
                        <i class="bi bi-cash-stack"></i>
                        <span>Process Payment</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=student_profiles">
                        <i class="bi bi-people-fill"></i>
                        <span>Student Profiles</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- main content -->
        <div class="main-content flex-grow-1">
            <!-- top navbar -->
            <nav class="navbar top-navbar">
                <div class="container-fluid">
                    <div class="navbar-brand mb-0">
                        <div class="page-icon">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <span>Process Payment</span>
                    </div>
                    <div class="user-info-wrapper">
                        <div class="user-details">
                            <span class="user-name">
                                <?php echo htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']); ?>
                            </span>
                            <span class="user-role">
                                <i class="bi bi-person-badge-fill"></i>
                                <?php echo ucfirst(htmlspecialchars($_SESSION['user_role'])); ?>
                            </span>
                        </div>
                        <div class="user-avatar">
                            <?php
                            $firstname = $_SESSION['user_firstname'] ?? 'R';
                            $lastname  = $_SESSION['user_lastname']  ?? 'U';
                            echo strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1));
                            ?>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- page content -->
            <div class="container-fluid p-4">
                <!-- breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?page=registrar_dashboard">
                                <i class="bi bi-house-door-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Process Payment</li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Process Payment</h1>
                            <p class="header-subtitle">Record cash payments for student tuition fees</p>
                        </div>
                    </div>
                </div>

                <!-- general error -->
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?= htmlspecialchars($errors['general']) ?></span>
                    </div>
                <?php endif; ?>


                <!-- search bar -->
                <div class="search-card mb-4">
                    <div class="search-bar-wrapper">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="studentSearchInput" class="search-input"
                            placeholder="Search student by name, ID, or LRN..."
                            value="<?= $student ? htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) : '' ?>">
                        <button class="btn-search" id="searchBtn">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                    <!-- search results dropdown -->
                    <div class="search-results d-none" id="searchResults"></div>
                </div>

                <?php if ($student && $payment): ?>
                    <div class="row g-4">
                        <!-- left col: student info + payment form -->
                        <div class="col-lg-7">

                            <!-- student info card -->
                            <div class="card payment-card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-person-fill"></i>
                                        Student Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="student-info-grid">
                                        <div class="info-item">
                                            <span class="info-label">Student Name</span>
                                            <span class="info-value">
                                                <?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . ($student['middle_name'] ?? '')) ?>
                                            </span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Student ID</span>
                                            <span class="info-value"><?= htmlspecialchars($student['student_number']) ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Year Level</span>
                                            <span class="info-value"><?= htmlspecialchars($student['year_level']) ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Section</span>
                                            <span class="info-value"><?= htmlspecialchars($student['section_name'] ?? 'Not assigned') ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">School Year</span>
                                            <span class="info-value"><?= htmlspecialchars($payment['school_year']) ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Semester</span>
                                            <span class="info-value"><?= htmlspecialchars($payment['semester']) ?> Semester</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- balance summary -->
                            <?php
                            $total_paid  = (float) $payment['total_paid'];
                            $net_amount  = (float) $payment['net_amount'];
                            $remaining   = max(0, $net_amount - $total_paid);
                            $status      = $payment['status'];
                            ?>
                            <div class="card payment-card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-receipt"></i>
                                        Balance Summary
                                    </h5>
                                    <span class="status-badge status-<?= $status ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="balance-grid">
                                        <div class="balance-item">
                                            <span class="balance-label">Total Amount Due</span>
                                            <span class="balance-amount">₱<?= number_format($net_amount, 2) ?></span>
                                        </div>
                                        <div class="balance-item balance-paid">
                                            <span class="balance-label">Total Paid</span>
                                            <span class="balance-amount">₱<?= number_format($total_paid, 2) ?></span>
                                        </div>
                                        <div class="balance-item balance-remaining <?= $remaining <= 0 ? 'balance-settled' : '' ?>">
                                            <span class="balance-label">Remaining Balance</span>
                                            <span class="balance-amount">₱<?= number_format($remaining, 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- payment form — hide if fully paid -->
                            <?php if ($remaining > 0): ?>
                                <div class="card payment-card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-cash-coin"></i>
                                            Record Payment
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($errors['amount_paid'])): ?>
                                            <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                <span><?= htmlspecialchars($errors['amount_paid']) ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <form method="POST" action="index.php?page=payment_store" id="paymentForm">
                                            <input type="hidden" name="student_id" value="<?= (int) $student['student_id'] ?>">
                                            <input type="hidden" name="payment_id" value="<?= (int) $payment['payment_id'] ?>">
                                            <input type="hidden" name="remaining_balance" id="remainingBalanceInput" value="<?= $remaining ?>">

                                            <div class="mb-3">
                                                <label class="form-label" for="amount_paid">
                                                    <i class="bi bi-cash"></i>
                                                    Amount to Collect <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="number" class="form-control" id="amount_paid"
                                                        name="amount_paid" min="1" step="0.01"
                                                        max="<?= $remaining ?>"
                                                        placeholder="0.00"
                                                        value="<?= htmlspecialchars($_POST['amount_paid'] ?? '') ?>">
                                                </div>
                                                <div class="form-hint">
                                                    Max collectible: <strong>₱<?= number_format($remaining, 2) ?></strong>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label" for="notes">
                                                    <i class="bi bi-sticky"></i>
                                                    Notes <span class="text-muted fw-normal">(optional)</span>
                                                </label>
                                                <textarea class="form-control" id="notes" name="notes"
                                                    rows="2" placeholder="e.g. partial payment, cash on hand, etc."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                                            </div>

                                            <!-- live payment preview -->
                                            <div class="payment-preview" id="paymentPreview">
                                                <div class="preview-row">
                                                    <span>Remaining Before</span>
                                                    <span>₱<?= number_format($remaining, 2) ?></span>
                                                </div>
                                                <div class="preview-row">
                                                    <span>Amount Collecting</span>
                                                    <span id="previewAmount">₱0.00</span>
                                                </div>
                                                <div class="preview-divider"></div>
                                                <div class="preview-row preview-total">
                                                    <span>Remaining After</span>
                                                    <span id="previewRemaining">₱<?= number_format($remaining, 2) ?></span>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn-process-payment w-100" id="submitPaymentBtn">
                                                <i class="bi bi-check-circle-fill"></i>
                                                Confirm & Record Payment
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="fully-paid-notice">
                                    <i class="bi bi-patch-check-fill"></i>
                                    <div>
                                        <strong>Account fully settled</strong>
                                        <p>This student has no outstanding balance for this semester.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- right col: receipt + history -->
                        <div class="col-lg-5">

                            <!-- receipt panel -->
                            <div class="card payment-card mb-4" id="receiptPanel">
                                <?php if ($last_receipt): ?>
                                    <div class="card-header receipt-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-printer-fill"></i>
                                            Payment Receipt
                                        </h5>
                                        <button class="btn-print" onclick="printReceipt()">
                                            <i class="bi bi-printer"></i> Print
                                        </button>
                                    </div>
                                    <div class="card-body p-0" id="receiptContent">
                                        <div class="receipt-body">
                                            <div class="receipt-school">
                                                <strong>DATAMEX COLLEGE OF SAINT ADELINE</strong>
                                                <span>OFFICIAL PAYMENT RECORD</span>
                                                <small>123 Education Street, City, State 12345</small>
                                            </div>
                                            <div class="receipt-divider"></div>
                                            <div class="receipt-meta">
                                                <div class="receipt-meta-row">
                                                    <span>Receipt #</span>
                                                    <span>TXN-<?= str_pad($last_receipt['transaction_id'], 5, '0', STR_PAD_LEFT) ?></span>
                                                </div>
                                                <div class="receipt-meta-row">
                                                    <span>Date</span>
                                                    <span><?= date('F j, Y', strtotime($last_receipt['payment_date'])) ?></span>
                                                </div>
                                                <div class="receipt-meta-row">
                                                    <span>Time</span>
                                                    <span><?= date('g:i A', strtotime($last_receipt['created_at'])) ?></span>
                                                </div>
                                            </div>
                                            <div class="receipt-divider"></div>
                                            <div class="receipt-meta">
                                                <div class="receipt-meta-row">
                                                    <span>Student</span>
                                                    <span><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></span>
                                                </div>
                                                <div class="receipt-meta-row">
                                                    <span>Student ID</span>
                                                    <span><?= htmlspecialchars($student['student_number']) ?></span>
                                                </div>
                                                <div class="receipt-meta-row">
                                                    <span>Year Level</span>
                                                    <span><?= htmlspecialchars($student['year_level']) ?></span>
                                                </div>
                                            </div>
                                            <div class="receipt-divider"></div>
                                            <div class="receipt-meta">
                                                <div class="receipt-meta-row">
                                                    <span>Payment For</span>
                                                    <span>Tuition Fee <?= htmlspecialchars($last_receipt['school_year']) ?></span>
                                                </div>
                                                <div class="receipt-meta-row">
                                                    <span>Semester</span>
                                                    <span><?= htmlspecialchars($last_receipt['semester']) ?></span>
                                                </div>
                                                <div class="receipt-meta-row">
                                                    <span>Method</span>
                                                    <span>CASH</span>
                                                </div>
                                            </div>
                                            <div class="receipt-divider"></div>
                                            <div class="receipt-amount-row">
                                                <span>Amount Paid</span>
                                                <strong>₱<?= number_format($last_receipt['amount_paid'], 2) ?></strong>
                                            </div>
                                            <div class="receipt-divider"></div>
                                            <div class="receipt-footer-note">
                                                <p>Thank you for your payment!</p>
                                                <small>Received by: <?= htmlspecialchars(strtoupper($last_receipt['received_by_first'] . ' ' . $last_receipt['received_by_last'])) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="card-header receipt-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-receipt"></i>
                                            Receipt
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="receipt-empty">
                                            <i class="bi bi-receipt"></i>
                                            <p>Receipt will appear here after a payment is recorded.</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- payment history -->
                            <div class="card payment-card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-clock-history"></i>
                                        Payment History
                                    </h5>
                                    <span class="history-count"><?= count($transactions) ?> record<?= count($transactions) !== 1 ? 's' : '' ?></span>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (!empty($transactions)): ?>
                                        <div class="history-list">
                                            <?php foreach ($transactions as $txn): ?>
                                                <div class="history-item">
                                                    <div class="history-icon">
                                                        <i class="bi bi-cash"></i>
                                                    </div>
                                                    <div class="history-details">
                                                        <span class="history-amount">₱<?= number_format($txn['amount_paid'], 2) ?></span>
                                                        <span class="history-date"><?= date('M j, Y', strtotime($txn['payment_date'])) ?></span>
                                                        <?php if ($txn['notes']): ?>
                                                            <span class="history-note"><?= htmlspecialchars($txn['notes']) ?></span>
                                                        <?php endif; ?>
                                                        <span class="history-by">by <?= htmlspecialchars($txn['received_by_first'] . ' ' . $txn['received_by_last']) ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="history-empty">
                                            <i class="bi bi-clock-history"></i>
                                            <p>No payments recorded yet.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>

                <?php elseif ($student && !$payment): ?>
                    <!-- student found but no enrollment payment record -->
                    <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>No active enrollment payment record found for <strong><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></strong> this semester. Please complete enrollment first.</span>
                    </div>

                <?php else: ?>
                    <!-- no student selected — show recent enrollments -->
                    <div class="recent-enrollments-section">
                        <div class="recent-header">
                            <div class="recent-header-left">
                                <i class="bi bi-clock-history"></i>
                                <span>Recently Enrolled Students</span>
                            </div>
                            <small>Click a student to load their payment details</small>
                        </div>

                        <?php if (!empty($recent_enrollments)): ?>
                            <div class="recent-grid">
                                <?php foreach ($recent_enrollments as $r):
                                    $remaining = max(0, (float)($r['net_amount'] ?? 0) - (float)($r['total_paid'] ?? 0));
                                    $status    = $r['payment_status'] ?? 'pending';
                                    $initials  = strtoupper(substr($r['first_name'], 0, 1) . substr($r['last_name'], 0, 1));
                                ?>
                                    <a class="recent-student-card" href="index.php?page=payment_process&student_id=<?= (int) $r['student_id'] ?>">
                                        <div class="recent-card-top">
                                            <div class="recent-avatar"><?= $initials ?></div>
                                            <span class="status-badge status-<?= $status ?>"><?= ucfirst($status) ?></span>
                                        </div>
                                        <div class="recent-card-name">
                                            <?= htmlspecialchars($r['last_name'] . ', ' . $r['first_name']) ?>
                                        </div>
                                        <div class="recent-card-meta">
                                            <span><?= htmlspecialchars($r['student_number']) ?></span>
                                            <span><?= htmlspecialchars($r['year_level']) ?> · <?= htmlspecialchars($r['strand_course']) ?></span>
                                        </div>
                                        <?php if ($r['net_amount']): ?>
                                            <div class="recent-card-balance">
                                                <span class="balance-label-sm">Remaining</span>
                                                <span class="balance-amount-sm <?= $remaining <= 0 ? 'settled' : '' ?>">
                                                    ₱<?= number_format($remaining, 2) ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <div class="recent-card-balance">
                                                <span class="balance-label-sm no-record">No payment record</span>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-person-search"></i>
                                </div>
                                <h4>No Recent Enrollments</h4>
                                <p>Search for a student above to load their payment details.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- print styles injected into head when printing -->
    <div id="printArea" class="d-none"></div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const PAYMENT_DATA = {
            studentId: <?= $student ? (int) $student['student_id'] : 'null' ?>,
            remaining: <?= $payment ? (float) max(0, $payment['net_amount'] - $payment['total_paid']) : 0 ?>,
            ajaxUrls: {
                search: 'index.php?page=payment_search_students',
                details: 'index.php?page=payment_get_details',
                receipt: 'index.php?page=payment_get_receipt',
            }
        };
    </script>
    <script src="js/payment-process.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($errors) && empty($errors['general'])): ?>
                showToast('danger', 'Please fix the errors before submitting.');
            <?php endif; ?>


            <?php if ($last_receipt): ?>
                showToast('success', 'Payment of ₱<?= number_format($last_receipt['amount_paid'], 2) ?> recorded successfully.');
            <?php endif; ?>
        });
    </script>

</body>

</html>