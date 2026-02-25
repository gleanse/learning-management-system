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
    <link rel="stylesheet" href="css/shared/top-navbar.css">
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
                    <a class="nav-link" href="index.php?page=fee_config">
                        <i class="bi bi-cash-coin"></i>
                        <span>Fee Configuration</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=reports">
                        <i class="bi bi-graph-up"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=announcements">
                        <i class="bi bi-megaphone-fill"></i><span>Announcements</span>
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
            <?php require __DIR__ . '/../shared/top_navbar.php'; ?>

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
                                        No academic period has been set up yet. Initialize the first semester to begin tracking enrollment payments.
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
                            <!-- current period info + advance/undo/redo actions -->
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

                                        <!-- advance + undo/redo -->
                                        <div class="advance-section">
                                            <div class="advance-preview mb-3">
                                                <span class="advance-label">next period will be:</span>
                                                <span class="advance-target" id="advanceTargetLabel">
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

                                            <button type="button" class="btn btn-primary btn-action w-100 mb-2" id="advanceBtn">
                                                <i class="bi bi-skip-forward-fill"></i>
                                                Advance to <?= htmlspecialchars($next_period) ?>
                                            </button>

                                            <!-- undo / redo row -->
                                            <div class="d-flex gap-2 mt-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" id="undoBtn">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                    Undo Advance
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-sm flex-fill" id="redoBtn"
                                                    <?= !$can_redo ? 'disabled' : '' ?>>
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                    Redo
                                                </button>
                                            </div>
                                            <p class="text-muted mt-2 mb-0" style="font-size: 0.75rem;">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Undo is only available within the same school year and if no grades have been submitted yet.
                                            </p>
                                        </div>

                                    <?php else: ?>
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
                                        <?php foreach ($history as $record): ?>
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
                     GRADING PERIODS SECTION — only shows when period is active
                     ===================================================== -->
                <?php if ($current && !empty($grading_periods)): ?>
                    <div class="row mt-4" id="gradingPeriodsSection">
                        <div class="col-12">
                            <div class="card grading-card">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0">
                                        <i class="bi bi-lock-fill"></i>
                                        Grading Period Deadlines &amp; Locks
                                    </h5>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-dark border" id="gradingStatusBadge">
                                            <?php
                                            $locked_count = count(array_filter($grading_periods, fn($p) => $p['is_locked']));
                                            echo $locked_count . ' / ' . count($grading_periods) . ' locked';
                                            ?>
                                        </span>
                                        <button type="button" class="btn btn-sm btn-warning" id="lockAllBtn">
                                            <i class="bi bi-lock-fill"></i>
                                            Lock All
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3" style="font-size: 0.875rem;">
                                        Set deadlines for each grading period and lock them to prevent further grade submissions. All four periods must be locked before advancing.
                                    </p>

                                    <div class="table-responsive">
                                        <table class="table grading-table mb-3" id="gradingPeriodsTable">
                                            <thead>
                                                <tr>
                                                    <th>Grading Period</th>
                                                    <th>Deadline</th>
                                                    <th>Status</th>
                                                    <th class="text-center">Lock</th>
                                                </tr>
                                            </thead>
                                            <tbody id="gradingPeriodsBody">
                                                <?php foreach ($grading_periods as $period): ?>
                                                    <tr class="grading-period-row <?= $period['lock_status'] === 'locked' ? 'row-locked' : ($period['lock_status'] === 'expired' ? 'row-expired' : '') ?>"
                                                        data-period-id="<?= $period['period_id'] ?>">
                                                        <td class="fw-semibold">
                                                            <i class="bi bi-flag-fill me-2 text-primary"></i>
                                                            <?= htmlspecialchars($period['grading_period']) ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($period['deadline_date']): ?>
                                                                <span class="deadline-display <?= $period['lock_status'] === 'expired' ? 'text-danger' : '' ?>">
                                                                    <?= date('M d, Y', strtotime($period['deadline_date'])) ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted fst-italic">not set</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($period['lock_status'] === 'locked'): ?>
                                                                <span class="grading-badge grading-badge-locked">
                                                                    <i class="bi bi-lock-fill"></i> locked
                                                                </span>
                                                            <?php elseif ($period['lock_status'] === 'expired'): ?>
                                                                <span class="grading-badge grading-badge-expired">
                                                                    <i class="bi bi-clock-history"></i> expired
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="grading-badge grading-badge-open">
                                                                    <i class="bi bi-unlock-fill"></i> open
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="form-check form-switch d-flex justify-content-center mb-0">
                                                                <input
                                                                    class="form-check-input grading-lock-toggle"
                                                                    type="checkbox"
                                                                    role="switch"
                                                                    data-period-id="<?= $period['period_id'] ?>"
                                                                    <?= $period['is_locked'] ? 'checked' : '' ?>>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- deadline editor -->
                                    <div class="deadline-editor mt-3">
                                        <div class="deadline-editor-header mb-3">
                                            <h6 class="mb-0">
                                                <i class="bi bi-pencil-fill me-2"></i>
                                                Set / Update Deadlines
                                            </h6>
                                        </div>
                                        <div class="row g-3">
                                            <?php
                                            $deadline_map = [];
                                            foreach ($grading_periods as $p) {
                                                $deadline_map[strtolower($p['grading_period'])] = $p['deadline_date'];
                                            }
                                            $period_labels = ['prelim' => 'Prelim', 'midterm' => 'Midterm', 'prefinal' => 'Prefinal', 'final' => 'Final'];
                                            ?>
                                            <?php foreach ($period_labels as $key => $label): ?>
                                                <div class="col-md-3 col-sm-6">
                                                    <label class="form-label" style="font-size: 0.813rem; font-weight: 600;">
                                                        <?= $label ?>
                                                    </label>
                                                    <input
                                                        type="date"
                                                        class="form-control form-control-sm"
                                                        name="deadline_<?= $key ?>"
                                                        id="deadline_<?= $key ?>"
                                                        value="<?= htmlspecialchars($deadline_map[$key] ?? '') ?>">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-primary btn-sm" id="saveDeadlinesBtn">
                                                <i class="bi bi-floppy-fill"></i>
                                                Save Deadlines
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($current && empty($grading_periods)): ?>
                    <!-- period exists but no grading periods set up yet — show deadline setup only -->
                    <div class="row mt-4" id="gradingPeriodsSection">
                        <div class="col-12">
                            <div class="card grading-card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-lock-fill"></i>
                                        Grading Period Deadlines &amp; Locks
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3" style="font-size: 0.875rem;">
                                        No grading periods have been configured yet for this semester. Set deadlines below to create them.
                                    </p>
                                    <div class="row g-3">
                                        <?php foreach (['prelim' => 'Prelim', 'midterm' => 'Midterm', 'prefinal' => 'Prefinal', 'final' => 'Final'] as $key => $label): ?>
                                            <div class="col-md-3 col-sm-6">
                                                <label class="form-label" style="font-size: 0.813rem; font-weight: 600;">
                                                    <?= $label ?>
                                                </label>
                                                <input type="date" class="form-control form-control-sm"
                                                    name="deadline_<?= $key ?>" id="deadline_<?= $key ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-primary btn-sm" id="saveDeadlinesBtn">
                                            <i class="bi bi-floppy-fill"></i>
                                            Save Deadlines
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

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
                        This will deactivate the current period and create payment records for all active students with a valid fee configuration. All grading periods must be locked before advancing.
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

    <!-- undo confirmation modal -->
    <div class="modal fade" id="undoConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Confirm Undo Advance
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3 text-muted" style="font-size: 0.875rem;">
                        This will revert the academic period to the previous semester. The following will happen:
                    </p>
                    <ul class="mb-3" style="font-size: 0.875rem;">
                        <li>Pending payment records for the current period will be deleted.</li>
                        <li>The previous period will be reactivated.</li>
                        <li>Grading periods of the previous semester will be unlocked.</li>
                    </ul>
                    <div class="alert alert-warning mb-0" style="font-size: 0.813rem;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Undo is blocked if grades have already been submitted or if the school year has changed.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-warning" id="confirmUndoBtn">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Confirm Undo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- redo confirmation modal -->
    <div class="modal fade" id="redoConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-clockwise"></i>
                        Confirm Redo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0 text-muted" style="font-size: 0.875rem;">
                        This will redo the previously undone advancement. Payment records will be recreated for students that don't have one yet. All grading periods must be locked before redoing.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmRedoBtn">
                        <i class="bi bi-arrow-clockwise"></i>
                        Confirm Redo
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
            error: <?= !empty($errors['general']) ? json_encode($errors['general']) : 'null' ?>,
            canRedo: <?= json_encode($can_redo) ?>
        };
    </script>
    <script src="js/academic-period.js"></script>
    <script src="js/shared/top-navbar.js"></script>
</body>

</html>