<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Profile - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/student_profiles.css">
</head>

<body>
    <div class="d-flex">
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
                    <a class="nav-link" href="index.php?page=payment_process">
                        <i class="bi bi-cash-stack"></i>
                        <span>Process Payment</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=student_profiles">
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

        <div class="main-content flex-grow-1">
            <nav class="navbar top-navbar">
                <div class="container-fluid">
                    <div class="navbar-brand mb-0">
                        <div class="page-icon"><i class="bi bi-person-lines-fill"></i></div>
                        <span>View Student Profile</span>
                    </div>
                    <div class="user-info-wrapper">
                        <div class="user-details">
                            <span class="user-name"><?= htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']) ?></span>
                            <span class="user-role"><i class="bi bi-person-badge-fill"></i> <?= ucfirst($_SESSION['user_role']) ?></span>
                        </div>
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['user_firstname'] ?? 'R', 0, 1) . substr($_SESSION['user_lastname'] ?? 'G', 0, 1)); ?>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?page=student_profiles"><i class="bi bi-people-fill"></i> Student Profiles</a>
                        </li>
                        <li class="breadcrumb-item active">View Profile</li>
                    </ol>
                </nav>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- student info -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-person-fill"></i> Student Information</h5>
                                <a href="index.php?page=edit_student_profile&student_id=<?= $student['student_id'] ?>"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-fill"></i> Edit Profile
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Student Number</label>
                                        <p class="fw-bold mb-0"><?= htmlspecialchars($student['student_number']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Full Name</label>
                                        <p class="fw-bold mb-0">
                                            <?= htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']) ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Year Level</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['year_level']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Strand/Course</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['strand_course']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Section</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['section_name'] ?? 'Not Assigned') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Education Level</label>
                                        <p class="mb-0"><?= $student['education_level'] === 'senior_high' ? 'Senior High' : 'College' ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Email</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['email'] ?? '—') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Contact Number</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['contact_number'] ?? '—') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Date of Birth</label>
                                        <p class="mb-0"><?= $student['date_of_birth'] ? date('F j, Y', strtotime($student['date_of_birth'])) : '—' ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Gender</label>
                                        <p class="mb-0"><?= $student['gender'] ? ucfirst($student['gender']) : '—' ?></p>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label text-muted small">Home Address</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['home_address'] ?? '—') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Previous School</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['previous_school'] ?? '—') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Guardian</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['guardian'] ?? '—') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Guardian Contact</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['guardian_contact'] ?? '—') ?></p>
                                    </div>
                                    <?php if (!empty($student['special_notes'])): ?>
                                        <div class="col-12">
                                            <label class="form-label text-muted small">Special Notes</label>
                                            <p class="mb-0"><?= htmlspecialchars($student['special_notes']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- payment history -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-receipt"></i> Payment History</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($payment_history)): ?>
                                    <div class="empty-state py-4">
                                        <div class="empty-state-icon"><i class="bi bi-receipt"></i></div>
                                        <p class="empty-state-text">No payment transactions yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>School Year</th>
                                                    <th>Semester</th>
                                                    <th>Amount Paid</th>
                                                    <th>Received By</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($payment_history as $tx): ?>
                                                    <tr>
                                                        <td><?= date('M j, Y', strtotime($tx['payment_date'])) ?></td>
                                                        <td><?= htmlspecialchars($tx['school_year']) ?></td>
                                                        <td><?= htmlspecialchars($tx['semester']) ?></td>
                                                        <td class="text-success fw-bold">₱<?= number_format($tx['amount_paid'], 2) ?></td>
                                                        <td><?= htmlspecialchars($tx['received_by_name']) ?></td>
                                                        <td><?= htmlspecialchars($tx['notes'] ?? '—') ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- balance card -->
                    <div class="col-md-4">
                        <div class="card balance-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-wallet2"></i> Current Balance</h5>
                            </div>
                            <div class="card-body text-center py-4">
                                <?php if ($student['payment_id']): ?>
                                    <div class="mb-2">
                                        <?php if ($student['payment_status'] === 'paid'): ?>
                                            <span class="badge bg-success fs-6"><i class="bi bi-check-circle-fill"></i> Paid</span>
                                        <?php elseif ($student['payment_status'] === 'partial'): ?>
                                            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-clock-fill"></i> Partial</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger fs-6"><i class="bi bi-exclamation-circle-fill"></i> Pending</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="balance-amount <?= $student['remaining'] > 0 ? 'text-danger' : 'text-success' ?> fs-3 fw-bold">
                                        ₱<?= number_format($student['remaining'], 2) ?>
                                    </div>
                                    <p class="text-muted">Remaining Balance</p>
                                    <hr>
                                    <div class="text-start">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted">Total Amount</small>
                                            <small class="fw-bold">₱<?= number_format($student['net_amount'], 2) ?></small>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">Total Paid</small>
                                            <small class="fw-bold text-success">₱<?= number_format($student['total_paid'], 2) ?></small>
                                        </div>
                                    </div>
                                    <a href="index.php?page=payment_process&student_id=<?= $student['student_id'] ?>"
                                        class="btn btn-warning w-100 mt-3">
                                        <i class="bi bi-cash-coin"></i> Record Payment
                                    </a>
                                <?php else: ?>
                                    <div class="empty-state py-2">
                                        <div class="empty-state-icon"><i class="bi bi-wallet2"></i></div>
                                        <p class="empty-state-text">No payment record for this period.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>