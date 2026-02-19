<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Dashboard - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/registrar_dashboard.css">
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
                    <a class="nav-link active" href="index.php?page=registrar_dashboard">
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
                            <i class="bi bi-house-door-fill"></i>
                        </div>
                        <span>Registrar Dashboard</span>
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
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="bi bi-house-door-fill"></i> Dashboard
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Registrar Dashboard</h1>
                            <p class="header-subtitle">
                                Welcome back, <strong><?= htmlspecialchars($_SESSION['user_firstname']) ?></strong>!
                                &mdash; <?= htmlspecialchars($semester) ?> Semester, S.Y. <?= htmlspecialchars($school_year) ?>
                            </p>
                        </div>
                        <div class="ms-auto d-flex gap-2">
                            <a href="index.php?page=enrollment_create" class="btn-header-action">
                                <i class="bi bi-person-plus-fill"></i>
                                Enroll Student
                            </a>
                            <a href="index.php?page=payment_process" class="btn-header-action btn-header-action-alt">
                                <i class="bi bi-cash-coin"></i>
                                Record Payment
                            </a>
                        </div>
                    </div>
                </div>

                <!-- no active period warning -->
                <?php if (empty($school_year)): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>No active academic period set. Please contact the administrator.</span>
                    </div>
                <?php endif; ?>

                <!-- stats cards -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4">

                    <!-- active students -->
                    <div class="col">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="stat-icon">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <h5 class="stat-title">Active Students</h5>
                                <div class="stat-value"><?= number_format($total_active) ?></div>
                                <div class="stat-trend">
                                    <span class="trend-neutral">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Currently enrolled
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- enrollments this period -->
                    <div class="col">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="stat-icon" style="background: linear-gradient(135deg, var(--secondary) 0%, #3a2f6b 100%);">
                                    <i class="bi bi-clipboard-check-fill"></i>
                                </div>
                                <h5 class="stat-title">Enrollments</h5>
                                <div class="stat-value"><?= number_format($total_enrollments) ?></div>
                                <div class="stat-trend">
                                    <span class="trend-info">
                                        <i class="bi bi-info-circle-fill"></i>
                                        This semester
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- total collected -->
                    <div class="col">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                                <h5 class="stat-title">Total Collected</h5>
                                <div class="stat-value stat-value-sm">₱<?= number_format($total_collected, 2) ?></div>
                                <div class="stat-trend">
                                    <span class="trend-up">
                                        <i class="bi bi-arrow-up-right"></i>
                                        Payments received
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- outstanding balance -->
                    <div class="col">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                </div>
                                <h5 class="stat-title">Outstanding</h5>
                                <div class="stat-value stat-value-sm">₱<?= number_format($total_outstanding, 2) ?></div>
                                <div class="stat-trend">
                                    <span class="trend-danger">
                                        <i class="bi bi-clock-fill"></i>
                                        Remaining balance
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- two column layout -->
                <div class="row g-4 mb-4">

                    <!-- payment status breakdown -->
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-pie-chart-fill"></i>
                                    Payment Status — <?= htmlspecialchars($semester) ?> Sem, S.Y. <?= htmlspecialchars($school_year) ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $total_ep    = array_sum($payment_counts);
                                $pct_paid    = $total_ep > 0 ? round($payment_counts['paid']    / $total_ep * 100) : 0;
                                $pct_partial = $total_ep > 0 ? round($payment_counts['partial'] / $total_ep * 100) : 0;
                                $pct_pending = $total_ep > 0 ? round($payment_counts['pending'] / $total_ep * 100) : 0;
                                ?>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="status-card status-paid">
                                            <div class="status-count"><?= number_format($payment_counts['paid']) ?></div>
                                            <div class="status-label">Fully Paid</div>
                                            <div class="status-bar-wrap">
                                                <div class="status-bar-fill" style="width: <?= $pct_paid ?>%"></div>
                                            </div>
                                            <div class="status-pct"><?= $pct_paid ?>%</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="status-card status-partial">
                                            <div class="status-count"><?= number_format($payment_counts['partial']) ?></div>
                                            <div class="status-label">Partial</div>
                                            <div class="status-bar-wrap">
                                                <div class="status-bar-fill" style="width: <?= $pct_partial ?>%"></div>
                                            </div>
                                            <div class="status-pct"><?= $pct_partial ?>%</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="status-card status-pending">
                                            <div class="status-count"><?= number_format($payment_counts['pending']) ?></div>
                                            <div class="status-label">Pending</div>
                                            <div class="status-bar-wrap">
                                                <div class="status-bar-fill" style="width: <?= $pct_pending ?>%"></div>
                                            </div>
                                            <div class="status-pct"><?= $pct_pending ?>%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- quick links -->
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-link-45deg"></i>
                                    Quick Links
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="quick-links-list">
                                    <a href="index.php?page=enrollment_create" class="quick-link-item">
                                        <div class="link-icon">
                                            <i class="bi bi-person-plus-fill"></i>
                                        </div>
                                        <div class="link-content">
                                            <h6 class="link-title">Enroll Student</h6>
                                            <p class="link-desc">Register a new student for this period</p>
                                        </div>
                                        <div class="link-arrow">
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    </a>
                                    <a href="index.php?page=payment_process" class="quick-link-item">
                                        <div class="link-icon">
                                            <i class="bi bi-cash-coin"></i>
                                        </div>
                                        <div class="link-content">
                                            <h6 class="link-title">Process Payment</h6>
                                            <p class="link-desc">Record and manage student payments</p>
                                        </div>
                                        <div class="link-arrow">
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    </a>
                                    <a href="index.php?page=student_profiles" class="quick-link-item">
                                        <div class="link-icon">
                                            <i class="bi bi-people-fill"></i>
                                        </div>
                                        <div class="link-content">
                                            <h6 class="link-title">Student Profiles</h6>
                                            <p class="link-desc">View and manage student records</p>
                                        </div>
                                        <div class="link-arrow">
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- recent enrollments + recent transactions -->
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-person-plus-fill"></i>
                                    Recent Enrollments
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_enrollments)): ?>
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <p class="empty-state-text">No enrollments this period yet.</p>
                                        <p class="empty-state-subtext">New enrollments will appear here.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th><i class="bi bi-person-fill"></i> Student</th>
                                                    <th><i class="bi bi-mortarboard"></i> Level</th>
                                                    <th><i class="bi bi-cash"></i> Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_enrollments as $e): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="student-info">
                                                                <div class="student-name">
                                                                    <?= htmlspecialchars($e['last_name'] . ', ' . $e['first_name']) ?>
                                                                </div>
                                                                <div class="student-number">
                                                                    <?= htmlspecialchars($e['student_number']) ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge year-level-badge">
                                                                <?= htmlspecialchars($e['year_level']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $badge = match ($e['payment_status']) {
                                                                'paid'    => '<span class="badge bg-success">Paid</span>',
                                                                'partial' => '<span class="badge bg-warning text-dark">Partial</span>',
                                                                default   => '<span class="badge bg-danger">Pending</span>',
                                                            };
                                                            echo $badge;
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-clock-history"></i>
                                    Recent Transactions
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_transactions)): ?>
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-receipt"></i>
                                        </div>
                                        <p class="empty-state-text">No transactions recorded yet.</p>
                                        <p class="empty-state-subtext">Payment transactions will appear here.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th><i class="bi bi-person-fill"></i> Student</th>
                                                    <th><i class="bi bi-calendar-event"></i> Date</th>
                                                    <th><i class="bi bi-cash"></i> Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_transactions as $tx): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="student-info">
                                                                <div class="student-name">
                                                                    <?= htmlspecialchars($tx['last_name'] . ', ' . $tx['first_name']) ?>
                                                                </div>
                                                                <div class="student-number">
                                                                    by <?= htmlspecialchars($tx['received_by_name']) ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?= date('M j, Y', strtotime($tx['payment_date'])) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="amount-value">₱<?= number_format($tx['amount_paid'], 2) ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
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