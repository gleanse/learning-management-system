<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/shared/top-navbar.css">
    <link rel="stylesheet" href="css/pages/reports.css">
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;" id="toastContainer"></div>

    <div class="d-flex">
        <!-- sidebar -->
        <div class="sidenav" id="sidebar">
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
                    <a class="nav-link" href="index.php?page=academic_period">
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
                    <a class="nav-link active" href="index.php?page=reports">
                        <i class="bi bi-graph-up"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=announcements">
                        <i class="bi bi-megaphone-fill"></i>
                        <span>Announcements</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=admin_activity_logs">
                        <i class="bi bi-journal-text"></i>
                        <span>Activity Logs</span>
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
                            Reports
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
                            <h1 class="header-title">Reports</h1>
                            <p class="header-subtitle">View and export enrollment, payment, and performance reports</p>
                        </div>
                    </div>
                </div>

                <!-- summary stats row -->
                <div class="row mb-4">
                    <!-- total students -->
                    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">Active Students</p>
                                <h3 class="stat-value" id="statTotalStudents">
                                    <?= number_format($summary['total_active_students'] ?? 0) ?>
                                </h3>
                                <p class="stat-sub">Currently Enrolled</p>
                            </div>
                        </div>
                    </div>

                    <!-- total teachers -->
                    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                        <div class="stat-card stat-card-secondary">
                            <div class="stat-icon">
                                <i class="bi bi-person-badge-fill"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">Active Teachers</p>
                                <h3 class="stat-value"><?= number_format($summary['total_active_teachers'] ?? 0) ?></h3>
                                <p class="stat-sub"><?= number_format($summary['assigned_teachers'] ?? 0) ?> With Assignments</p>
                            </div>
                        </div>
                    </div>

                    <!-- sections -->
                    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                        <div class="stat-card stat-card-success">
                            <div class="stat-icon">
                                <i class="bi bi-grid-3x3-gap-fill"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">Total Sections</p>
                                <h3 class="stat-value"><?= number_format($summary['total_sections'] ?? 0) ?></h3>
                                <p class="stat-sub">This School Year</p>
                            </div>
                        </div>
                    </div>

                    <!-- yearly revenue -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">Yearly Revenue</p>
                                <h3 class="stat-value">₱<?= number_format($summary['yearly_revenue'] ?? 0) ?></h3>
                                <p class="stat-sub">Current year collections</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- report type tabs -->
                <div class="report-tabs mb-4">
                    <div class="tabs-container">
                        <a href="?page=reports&type=enrollment&interval=<?= $interval ?>"
                            class="tab-item <?= $report_type == 'enrollment' ? 'active' : '' ?>">
                            <i class="bi bi-person-plus-fill"></i>
                            <span>Enrollment</span>
                        </a>
                        <a href="?page=reports&type=payment&interval=<?= $interval ?>"
                            class="tab-item <?= $report_type == 'payment' ? 'active' : '' ?>">
                            <i class="bi bi-cash-coin"></i>
                            <span>Payment</span>
                        </a>
                        <a href="?page=reports&type=teacher&interval=<?= $interval ?>"
                            class="tab-item <?= $report_type == 'teacher' ? 'active' : '' ?>">
                            <i class="bi bi-person-workspace"></i>
                            <span>Teacher Workload</span>
                        </a>
                        <a href="?page=reports&type=section&interval=<?= $interval ?>"
                            class="tab-item <?= $report_type == 'section' ? 'active' : '' ?>">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                            <span>Section Utilization</span>
                        </a>
                        <a href="?page=reports&type=grade&interval=<?= $interval ?>"
                            class="tab-item <?= $report_type == 'grade' ? 'active' : '' ?>">
                            <i class="bi bi-file-check-fill"></i>
                            <span>Grade Submission</span>
                        </a>
                        <a href="?page=reports&type=performance&interval=<?= $interval ?>"
                            class="tab-item <?= $report_type == 'performance' ? 'active' : '' ?>">
                            <i class="bi bi-mortarboard-fill"></i>
                            <span>Student Performance</span>
                        </a>
                    </div>
                </div>

                <!-- interval filters -->
                <div class="interval-filters mb-4">
                    <div class="filters-container">
                        <span class="filter-label">Time Period:</span>
                        <a href="?page=reports&type=<?= $report_type ?>&interval=daily"
                            class="filter-item <?= $interval == 'daily' ? 'active' : '' ?>">Daily</a>
                        <a href="?page=reports&type=<?= $report_type ?>&interval=weekly"
                            class="filter-item <?= $interval == 'weekly' ? 'active' : '' ?>">Weekly</a>
                        <a href="?page=reports&type=<?= $report_type ?>&interval=monthly"
                            class="filter-item <?= $interval == 'monthly' ? 'active' : '' ?>">Monthly</a>
                        <a href="?page=reports&type=<?= $report_type ?>&interval=yearly"
                            class="filter-item <?= $interval == 'yearly' ? 'active' : '' ?>">Yearly</a>
                    </div>

                    <!-- export buttons -->
                    <div class="export-buttons">
                        <a href="?page=reports&action=export&type=<?= $report_type ?>&interval=<?= $interval ?>&format=csv"
                            class="btn btn-success btn-sm" id="exportCsvBtn">
                            <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                            CSV
                        </a>
                        <a href="?page=reports&action=export&type=<?= $report_type ?>&interval=<?= $interval ?>&format=pdf"
                            class="btn btn-danger btn-sm" id="exportPdfBtn">
                            <i class="bi bi-file-earmark-pdf-fill"></i>
                            PDF
                        </a>
                    </div>
                </div>

                <!-- report content -->
                <div class="row">
                    <div class="col-12">
                        <?php if ($report_type == 'enrollment'): ?>
                            <?php include __DIR__ . '/reports/enrollment_report.php'; ?>
                        <?php elseif ($report_type == 'payment'): ?>
                            <?php include __DIR__ . '/reports/payment_report.php'; ?>
                        <?php elseif ($report_type == 'teacher'): ?>
                            <?php include __DIR__ . '/reports/teacher_report.php'; ?>
                        <?php elseif ($report_type == 'section'): ?>
                            <?php include __DIR__ . '/reports/section_report.php'; ?>
                        <?php elseif ($report_type == 'grade'): ?>
                            <?php include __DIR__ . '/reports/grade_report.php'; ?>
                        <?php elseif ($report_type == 'performance'): ?>
                            <?php include __DIR__ . '/reports/performance_report.php'; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const reportConfig = {
            success: <?= !empty($success) ? json_encode($success['message']) : 'null' ?>,
            error: <?= !empty($errors['general']) ? json_encode($errors['general']) : 'null' ?>,
            type: '<?= $report_type ?>',
            interval: '<?= $interval ?>'
        };
    </script>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/reports.js"></script>
    <script src="js/shared/top-navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');

            function toggleSidebar() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }

            if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
            if (overlay) overlay.addEventListener('click', toggleSidebar);
        });
    </script>
</body>

</html>