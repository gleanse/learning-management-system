<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Successful - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/enrollment_success.css">
</head>

<body>
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
                    <a class="nav-link active" href="index.php?page=enrollment_create">
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
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <span>Enrollment Successful</span>
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
            <div class="container-fluid p-4 success-wrapper">
                <div class="w-100">
                    <!-- breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="index.php?page=registrar_dashboard">
                                    <i class="bi bi-house-door-fill"></i> Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="index.php?page=enrollment_create">Enroll Student</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Enrollment Successful
                            </li>
                        </ol>
                    </nav>

                    <!-- success card -->
                    <div class="card success-card">
                        <div class="card-header">
                            <h5>
                                <i class="bi bi-check-circle-fill"></i>
                                Enrollment Confirmed
                            </h5>
                        </div>

                        <!-- hero -->
                        <div class="success-hero">
                            <div class="success-icon-wrapper">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <h2 class="success-title">
                                <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                            </h2>
                            <p class="success-subtitle">has been successfully enrolled</p>
                            <div class="student-number-badge">
                                <span class="badge-label">Student No.</span>
                                <span class="badge-value"><?= htmlspecialchars($student['student_number']) ?></span>
                            </div>
                        </div>

                        <!-- info body -->
                        <div class="success-body">

                            <!-- personal info -->
                            <div class="info-section">
                                <div class="info-section-header">
                                    <i class="bi bi-person-fill"></i>
                                    Personal Information
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-item-label">Full Name</div>
                                        <div class="info-item-value">
                                            <?= htmlspecialchars(
                                                $student['first_name']
                                                    . (!empty($student['middle_name']) ? ' ' . $student['middle_name'] : '')
                                                    . ' ' . $student['last_name']
                                            ) ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-item-label">Date of Birth</div>
                                        <div class="info-item-value">
                                            <?= !empty($student['date_of_birth'])
                                                ? date('F j, Y', strtotime($student['date_of_birth']))
                                                : '<span class="empty">Not provided</span>' ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-item-label">Gender</div>
                                        <div class="info-item-value">
                                            <?= !empty($student['gender'])
                                                ? ucfirst(htmlspecialchars($student['gender']))
                                                : '<span class="empty">Not provided</span>' ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-item-label">LRN</div>
                                        <div class="info-item-value">
                                            <?= !empty($student['lrn'])
                                                ? htmlspecialchars($student['lrn'])
                                                : '<span class="empty">N/A</span>' ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-item-label">Guardian</div>
                                        <div class="info-item-value">
                                            <?= htmlspecialchars($student['guardian']) ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-item-label">Guardian Contact</div>
                                        <div class="info-item-value">
                                            <?= htmlspecialchars($student['guardian_contact']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- academic details -->
                            <div class="info-section">
                                <div class="info-section-header">
                                    <i class="bi bi-mortarboard-fill"></i>
                                    Academic Details
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-item-label">Education Level</div>
                                        <div class="info-item-value">
                                            <?= $student['education_level'] === 'senior_high'
                                                ? 'Senior High School'
                                                : 'College' ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-item-label">Grade / Year Level</div>
                                        <div class="info-item-value">
                                            <?= htmlspecialchars($student['year_level']) ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-item-label">Strand / Course</div>
                                        <div class="info-item-value">
                                            <?= htmlspecialchars($student['strand_course']) ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-item-label">Section</div>
                                        <div class="info-item-value">
                                            <?= !empty($student['section_name'])
                                                ? htmlspecialchars($student['section_name'])
                                                : '<span class="empty">To be assigned</span>' ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-item-label">Enrollment Status</div>
                                        <div class="info-item-value">
                                            <span class="payment-status-badge paid">
                                                <i class="bi bi-check-circle-fill"></i>
                                                Active
                                            </span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-item-label">Previous School</div>
                                        <div class="info-item-value">
                                            <?= !empty($student['previous_school'])
                                                ? htmlspecialchars($student['previous_school'])
                                                : '<span class="empty">Not provided</span>' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- payment info -->
                            <?php if (!empty($payment)): ?>
                                <div class="info-section">
                                    <div class="info-section-header">
                                        <i class="bi bi-cash-stack"></i>
                                        Payment Summary
                                    </div>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <div class="info-item-label">Total Amount Due</div>
                                            <div class="info-item-value">
                                                ₱<?= number_format($payment['total_amount'], 2) ?>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-item-label">Amount Paid</div>
                                            <div class="info-item-value">
                                                ₱<?= number_format($payment['amount_paid'] ?? 0, 2) ?>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-item-label">Balance</div>
                                            <div class="info-item-value">
                                                ₱<?= number_format($payment['net_amount'] - ($payment['amount_paid'] ?? 0), 2) ?>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-item-label">Payment Status</div>
                                            <div class="info-item-value">
                                                <?php
                                                $status      = $payment['status'] ?? 'pending';
                                                $statusIcons = [
                                                    'paid'    => 'bi-check-circle-fill',
                                                    'partial' => 'bi-clock-fill',
                                                    'pending' => 'bi-exclamation-circle-fill',
                                                ];
                                                $statusIcon = $statusIcons[$status] ?? 'bi-circle';
                                                ?>
                                                <span class="payment-status-badge <?= $status ?>">
                                                    <i class="bi <?= $statusIcon ?>"></i>
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- enrolled subjects -->
                            <?php if (!empty($subjects)): ?>
                                <div class="info-section">
                                    <div class="info-section-header">
                                        <i class="bi bi-book-fill"></i>
                                        Enrolled Subjects
                                        <span style="margin-left: auto; font-size: 0.813rem; font-weight: 600; opacity: 0.85;">
                                            <?= count($subjects) ?> subject<?= count($subjects) !== 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                    <div class="subjects-list">
                                        <?php foreach ($subjects as $subject): ?>
                                            <div class="subject-row">
                                                <span class="s-code"><?= htmlspecialchars($subject['subject_code']) ?></span>
                                                <span class="s-name"><?= htmlspecialchars($subject['subject_name']) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- documents submitted -->
                            <?php if (!empty($documents)): ?>
                                <div class="info-section">
                                    <div class="info-section-header">
                                        <i class="bi bi-file-earmark-check-fill"></i>
                                        Documents Submitted
                                    </div>
                                    <div class="doc-chips">
                                        <?php
                                        $doc_labels = [
                                            'psa_birth_certificate' => 'PSA Birth Certificate',
                                            'form_138_report_card'  => 'Form 138 / Report Card',
                                            'good_moral_certificate' => 'Good Moral Certificate',
                                            'id_pictures'           => '2x2 ID Pictures',
                                            'medical_certificate'   => 'Medical Certificate',
                                        ];

                                        foreach ($doc_labels as $key => $label):
                                            $submitted = !empty($documents[$key]);
                                        ?>
                                            <span class="doc-chip <?= $submitted ? 'submitted' : 'missing' ?>">
                                                <i class="bi <?= $submitted ? 'bi-check-circle-fill' : 'bi-x-circle' ?>"></i>
                                                <?= $label ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div><!-- end success-body -->

                        <!-- action footer -->
                        <div class="success-footer">
                            <a href="index.php?page=enrollment_create" class="btn-enroll-another">
                                <i class="bi bi-person-plus-fill"></i>
                                Enroll Another Student
                            </a>
                            <button type="button" class="btn-print" onclick="window.print()">
                                <i class="bi bi-printer-fill"></i>
                                Print Confirmation
                            </button>
                        </div>

                    </div><!-- end success-card -->
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>