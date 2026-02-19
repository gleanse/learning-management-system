<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Period - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/academic_period.css">
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
                    <a class="nav-link" href="index.php?page=admin_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=manage_sections">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                        <span>Section Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=subjects">
                        <i class="bi bi-book-fill"></i>
                        <span>Subject Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=student_sections">
                        <i class="bi bi-people-fill"></i>
                        <span>Assign Students</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=teacher_assignments">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Teacher Assignments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=teacher_schedules">
                        <i class="bi bi-calendar-week-fill"></i>
                        <span>Manage Schedules</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=academic_period">
                        <i class="bi bi-calendar2-range-fill"></i>
                        <span>Academic Period</span>
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
                            <i class="bi bi-calendar2-range-fill"></i>
                        </div>
                        <span>Academic Period</span>
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
                            $firstname = $_SESSION['user_firstname'] ?? 'A';
                            $lastname  = $_SESSION['user_lastname']  ?? 'U';
                            echo strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1));
                            ?>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- page content -->
            <div class="container-fluid p-4">

                <!-- breadcrumbs -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?page=admin_dashboard">
                                <i class="bi bi-house-door-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Academic Period
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-calendar2-range-fill"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Academic Period Management</h1>
                            <p class="header-subtitle">initialize and advance school semesters and track period history</p>
                        </div>
                    </div>
                </div>

                <!-- stats row -->
                <div class="row mb-4">
                    <!-- current period -->
                    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">
                                <i class="bi bi-calendar2-check-fill"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">current period</p>
                                <h3 class="stat-value" id="statCurrentSem">
                                    <?php if ($current): ?>
                                        <?= htmlspecialchars($current['semester']) ?> Sem
                                    <?php else: ?>
                                        Not Set
                                    <?php endif; ?>
                                </h3>
                                <p class="stat-sub" id="statCurrentYear">
                                    <?php if ($current): ?>
                                        S.Y. <?= htmlspecialchars($current['school_year']) ?>
                                    <?php else: ?>
                                        no period initialized
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- active students -->
                    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                        <div class="stat-card stat-card-secondary">
                            <div class="stat-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">active students</p>
                                <h3 class="stat-value" id="statActiveCount"><?= number_format($active_count) ?></h3>
                                <p class="stat-sub">enrolled and active</p>
                            </div>
                        </div>
                    </div>

                    <!-- payment records -->
                    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                        <div class="stat-card stat-card-success">
                            <div class="stat-icon">
                                <i class="bi bi-receipt-cutoff"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">payment records</p>
                                <h3 class="stat-value" id="statPeriodCount"><?= number_format($period_count) ?></h3>
                                <p class="stat-sub" id="statPeriodSub">
                                    <?php if ($current): ?>
                                        for this period
                                    <?php else: ?>
                                        no period yet
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- missing fee config -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card <?= $missing_config > 0 ? 'stat-card-danger' : 'stat-card-muted' ?>" id="statMissingCard">
                            <div class="stat-icon">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">missing fee config</p>
                                <h3 class="stat-value" id="statMissingCount"><?= number_format($missing_config) ?></h3>
                                <p class="stat-sub" id="statMissingSub">
                                    <?= $missing_config > 0 ? 'students without fee setup' : 'all students configured' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- action + history row -->
                <div class="row">
                    <!-- left column: actions -->
                    <div class="col-lg-5 mb-4 mb-lg-0" id="actionColumn">

                        <?php if (!$has_period): ?>
                            <!-- initialize form -->
                            <div class="card action-card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-play-circle-fill"></i>
                                        Initialize First Period
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3" style="font-size: 0.875rem;">
                                        no academic period has been set up yet. initialize the first semester to begin tracking enrollment payments.
                                    </p>

                                    <?php if (!empty($errors['general'])): ?>
                                        <div class="alert alert-danger mb-3">
                                            <i class="bi bi-exclamation-circle-fill me-2"></i>
                                            <?= htmlspecialchars($errors['general']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <form action="index.php?page=academic_period_initialize" method="POST">
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="bi bi-calendar3"></i>
                                                School Year
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                name="school_year"
                                                class="form-control <?= !empty($errors['school_year']) ? 'is-invalid' : '' ?>"
                                                placeholder="e.g. 2024-2025"
                                                value="<?= htmlspecialchars($_POST['school_year'] ?? '') ?>"
                                                maxlength="9">
                                            <?php if (!empty($errors['school_year'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($errors['school_year']) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="form-text">format: YYYY-YYYY</div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label">
                                                <i class="bi bi-layers-half"></i>
                                                Semester
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select
                                                name="semester"
                                                class="form-select <?= !empty($errors['semester']) ? 'is-invalid' : '' ?>">
                                                <option value="">choose semester...</option>
                                                <option value="First" <?= ($_POST['semester'] ?? '') === 'First'  ? 'selected' : '' ?>>First Semester</option>
                                                <option value="Second" <?= ($_POST['semester'] ?? '') === 'Second' ? 'selected' : '' ?>>Second Semester</option>
                                            </select>
                                            <?php if (!empty($errors['semester'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($errors['semester']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-action w-100" id="initializeBtn">
                                            <i class="bi bi-play-circle-fill"></i>
                                            Initialize Academic Period
                                        </button>
                                    </form>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- current period info + advance action -->
                            <div class="card action-card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-calendar2-range-fill"></i>
                                        Current Period
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($current): ?>
                                        <!-- active period details -->
                                        <div class="current-period-display mb-4">
                                            <div class="period-badge-large">
                                                <i class="bi bi-calendar2-check-fill"></i>
                                                <div>
                                                    <span class="period-sem"><?= htmlspecialchars($current['semester']) ?> Semester</span>
                                                    <span class="period-year">S.Y. <?= htmlspecialchars($current['school_year']) ?></span>
                                                </div>
                                            </div>

                                            <div class="period-meta mt-3">
                                                <div class="meta-item">
                                                    <span class="meta-label">
                                                        <i class="bi bi-clock"></i>
                                                        Started
                                                    </span>
                                                    <span class="meta-value">
                                                        <?= date('M d, Y', strtotime($current['advanced_at'])) ?>
                                                    </span>
                                                </div>
                                                <div class="meta-item">
                                                    <span class="meta-label">
                                                        <i class="bi bi-receipt"></i>
                                                        Payment Records
                                                    </span>
                                                    <span class="meta-value"><?= number_format($period_count) ?></span>
                                                </div>
                                                <?php if ($missing_config > 0): ?>
                                                    <div class="meta-item meta-item-warning">
                                                        <span class="meta-label">
                                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                                            Missing Config
                                                        </span>
                                                        <span class="meta-value text-danger fw-bold"><?= $missing_config ?> students</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if (!empty($errors['general'])): ?>
                                            <div class="alert alert-danger mb-3">
                                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                                <?= htmlspecialchars($errors['general']) ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- advance button -->
                                        <div class="advance-section">
                                            <div class="advance-preview mb-3">
                                                <span class="advance-label">next period will be:</span>
                                                <span class="advance-target">
                                                    <i class="bi bi-arrow-right-circle-fill"></i>
                                                    <?= htmlspecialchars($next_period) ?>
                                                </span>
                                            </div>

                                            <?php if ($missing_config > 0): ?>
                                                <div class="alert alert-warning mb-3" style="font-size: 0.875rem;">
                                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                    <strong><?= $missing_config ?></strong> student(s) don't have a fee configuration for this school year. they will be skipped when creating payment records.
                                                </div>
                                            <?php endif; ?>

                                            <form action="index.php?page=academic_period_advance" method="POST">
                                                <button type="button" class="btn btn-primary btn-action w-100" id="advanceBtn">
                                                    <i class="bi bi-skip-forward-fill"></i>
                                                    Advance to <?= htmlspecialchars($next_period) ?>
                                                </button>
                                            </form>
                                        </div>

                                    <?php else: ?>
                                        <!-- no active period but records exist — edge case -->
                                        <div class="empty-state py-3">
                                            <div class="empty-state-icon">
                                                <i class="bi bi-calendar-x"></i>
                                            </div>
                                            <p class="empty-state-text">no active period found</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>

                    <!-- right column: history -->
                    <div class="col-lg-7">
                        <div class="card history-card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-clock-history"></i>
                                    Period History
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($history)): ?>
                                    <div class="empty-state py-5">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <p class="empty-state-text">no period history yet</p>
                                    </div>
                                <?php else: ?>
                                    <div class="history-timeline" id="historyTimeline">
                                        <?php foreach ($history as $index => $record): ?>
                                            <div class="history-item <?= $record['is_active'] ? 'history-item-active' : '' ?>">
                                                <div class="history-dot">
                                                    <?php if ($record['is_active']): ?>
                                                        <i class="bi bi-circle-fill"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-check-circle-fill"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="history-content">
                                                    <div class="history-header">
                                                        <span class="history-period">
                                                            <?= htmlspecialchars($record['semester']) ?> Semester
                                                            &mdash;
                                                            S.Y. <?= htmlspecialchars($record['school_year']) ?>
                                                        </span>
                                                        <?php if ($record['is_active']): ?>
                                                            <span class="badge-active">
                                                                <i class="bi bi-broadcast"></i>
                                                                active
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge-completed">
                                                                <i class="bi bi-check2"></i>
                                                                completed
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="history-meta">
                                                        <span>
                                                            <i class="bi bi-clock"></i>
                                                            <?= date('M d, Y h:i A', strtotime($record['advanced_at'])) ?>
                                                        </span>
                                                        <?php if ($record['advanced_by_first']): ?>
                                                            <span>
                                                                <i class="bi bi-person-fill"></i>
                                                                <?= htmlspecialchars($record['advanced_by_first'] . ' ' . $record['advanced_by_last']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =====================================================
                     GRADUATION SECTION — only shows during second semester
                     ===================================================== -->
                <?php if ($current && $current['semester'] === 'Second'): ?>
                    <div class="row mt-4" id="graduationSection">
                        <div class="col-12">
                            <div class="card graduation-card">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0">
                                        <i class="bi bi-mortarboard-fill"></i>
                                        Graduate Students
                                    </h5>
                                    <?php if (!empty($graduatable_students)): ?>
                                        <span class="badge bg-warning text-dark" id="graduatableCount">
                                            <?= count($graduatable_students) ?> eligible
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($graduatable_students)): ?>
                                        <div class="empty-state py-3">
                                            <div class="empty-state-icon">
                                                <i class="bi bi-mortarboard"></i>
                                            </div>
                                            <p class="empty-state-text">no students eligible for graduation</p>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted mb-3" style="font-size: 0.875rem;">
                                            the following students are in their final year (4th Year / Grade 12). select those who have completed their requirements to mark them as graduated.
                                        </p>

                                        <div class="table-responsive" id="graduationTableWrapper">
                                            <table class="table table-hover mb-3" id="graduationTable">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px;">
                                                            <input type="checkbox" class="form-check-input" id="selectAllGraduates">
                                                        </th>
                                                        <th>Student Number</th>
                                                        <th>Name</th>
                                                        <th>Year Level</th>
                                                        <th>Course / Strand</th>
                                                        <th>Education Level</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="graduationTableBody">
                                                    <?php foreach ($graduatable_students as $student): ?>
                                                        <tr>
                                                            <td>
                                                                <input type="checkbox" class="form-check-input graduate-checkbox"
                                                                    value="<?= $student['student_id'] ?>">
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold"><?= htmlspecialchars($student['student_number']) ?></span>
                                                            </td>
                                                            <td>
                                                                <?= htmlspecialchars(
                                                                    $student['first_name'] . ' ' .
                                                                        ($student['middle_name'] ? $student['middle_name'] . ' ' : '') .
                                                                        $student['last_name']
                                                                ) ?>
                                                            </td>
                                                            <td>
                                                                <span class="education-level-badge badge-shs">
                                                                    <?= htmlspecialchars($student['year_level']) ?>
                                                                </span>
                                                            </td>
                                                            <td><?= htmlspecialchars($student['strand_course']) ?></td>
                                                            <td>
                                                                <span class="education-level-badge <?= $student['education_level'] === 'senior_high' ? 'badge-shs' : 'badge-college' ?>">
                                                                    <?= $student['education_level'] === 'senior_high' ? 'Senior High' : 'College' ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between" id="graduationFooter">
                                            <span class="text-muted small" id="selectedGraduateCount">0 selected</span>
                                            <button type="button" class="btn btn-success btn-action" id="graduateBtn" disabled>
                                                <i class="bi bi-mortarboard-fill"></i>
                                                Graduate Selected
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- advance confirmation modal -->
    <div class="modal fade" id="advanceConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Confirm Period Advance
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">You are about to advance the academic period to:</p>
                    <p class="fw-bold fs-5 text-primary mb-3">
                        <span id="advanceModalNextLabel"><?php if ($next_period): ?><?= htmlspecialchars($next_period) ?><?php endif; ?></span>
                    </p>
                    <p class="mb-0 text-muted" style="font-size: 0.875rem;">
                        This will deactivate the current period and create payment records for all active students with a valid fee configuration. This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmAdvanceBtn">
                        <i class="bi bi-skip-forward-fill"></i>
                        Confirm Advance
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- initialize confirmation modal -->
    <div class="modal fade" id="initializeConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-play-circle-fill"></i>
                        Confirm Initialization
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0 text-muted" style="font-size: 0.875rem;">
                        This will initialize the first academic period and create payment records for all active students with a valid fee configuration. This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmInitializeBtn">
                        <i class="bi bi-play-circle-fill"></i>
                        Confirm Initialize
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- graduation confirmation modal -->
    <div class="modal fade" id="graduateConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-mortarboard-fill"></i>
                        Confirm Graduation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">You are about to graduate:</p>
                    <p class="fw-bold fs-5 text-success mb-3" id="graduateModalCount"></p>
                    <p class="mb-0 text-muted" style="font-size: 0.875rem;">
                        Their enrollment status will be set to <strong>graduated</strong> and their section assignment will be cleared. This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="confirmGraduateBtn">
                        <i class="bi bi-mortarboard-fill"></i>
                        Confirm Graduate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        const academicPeriodConfig = {
            success: <?= !empty($success) ? json_encode($success['message']) : 'null' ?>,
            error: <?= !empty($errors['general']) ? json_encode($errors['general']) : 'null' ?>
        };
    </script>
    <script src="js/academic-period.js"></script>

</body>

</html>