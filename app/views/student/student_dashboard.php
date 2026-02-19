<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/student_dashboard.css">
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
                    <a class="nav-link active" href="index.php?page=student_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=student_grades">
                        <i class="bi bi-journal-text"></i>
                        <span>My Grades</span>
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
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <span>Student Dashboard</span>
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
                            $firstname = $_SESSION['user_firstname'] ?? 'S';
                            $lastname  = $_SESSION['user_lastname']  ?? 'T';
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
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="bi bi-house-door-fill"></i> Dashboard
                        </li>
                    </ol>
                </nav>

                <!-- welcome banner -->
                <div class="welcome-banner mb-4">
                    <div class="welcome-content">
                        <h2 class="welcome-title">
                            Good <?php
                                    date_default_timezone_set('Asia/Manila');
                                    $hour = (int) date('H');
                                    echo $hour < 12 ? 'Morning' : ($hour < 18 ? 'Afternoon' : 'Evening');
                                    ?>, <?= htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']) ?>
                        </h2>
                        <p class="welcome-subtitle">
                            Student &bull;
                            <?= htmlspecialchars($student_info['student_number'] ?? 'N/A') ?> &bull;
                            <?= htmlspecialchars($student_info['section_name'] ?? 'No Section') ?>
                        </p>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <!-- balance card -->
                    <div class="col-md-4">
                        <div class="card h-100 balance-card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-wallet2"></i>
                                    My Balance
                                </h5>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center py-4">
                                <?php if ($balance): ?>
                                    <div class="balance-status-badge mb-2">
                                        <?php if ($balance['status'] === 'paid'): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Paid</span>
                                        <?php elseif ($balance['status'] === 'partial'): ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock-fill"></i> Partial</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-exclamation-circle-fill"></i> Pending</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="balance-amount <?= $balance['remaining'] > 0 ? 'text-danger' : 'text-success' ?>">
                                        ₱<?= number_format($balance['remaining'], 2) ?>
                                    </div>
                                    <p class="balance-label">Remaining Balance</p>
                                    <div class="balance-meta mt-2">
                                        <small class="text-muted">
                                            Total: ₱<?= number_format($balance['net_amount'], 2) ?><br>
                                            Paid: ₱<?= number_format($balance['total_paid'], 2) ?>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state py-2">
                                        <div class="empty-state-icon" style="width:64px;height:64px;margin-bottom:1rem;">
                                            <i class="bi bi-wallet2" style="font-size:1.75rem;"></i>
                                        </div>
                                        <p class="empty-state-text" style="font-size:0.938rem;">No payment record found.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- today's schedule card -->
                    <div class="col-md-8">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-calendar-day-fill"></i>
                                    Today's Classes
                                    <span class="schedule-date-badge ms-2">
                                        <?= date('l, F j', strtotime('today')) ?>
                                    </span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($today_schedule)): ?>
                                    <div class="empty-state py-2">
                                        <div class="empty-state-icon" style="width:64px;height:64px;margin-bottom:1rem;">
                                            <i class="bi bi-calendar-x" style="font-size:1.75rem;"></i>
                                        </div>
                                        <p class="empty-state-text" style="font-size:0.938rem;">No classes scheduled today.</p>
                                        <p class="empty-state-subtext">Enjoy your free day!</p>
                                    </div>
                                <?php else: ?>
                                    <div class="schedule-list">
                                        <?php foreach ($today_schedule as $class): ?>
                                            <div class="schedule-item">
                                                <div class="schedule-time">
                                                    <i class="bi bi-clock"></i>
                                                    <?= htmlspecialchars($class['time_range']) ?>
                                                </div>
                                                <div class="schedule-info">
                                                    <div class="schedule-subject"><?= htmlspecialchars($class['subject_name']) ?></div>
                                                    <div class="schedule-meta">
                                                        <span><i class="bi bi-code-square"></i> <?= htmlspecialchars($class['subject_code']) ?></span>
                                                        <span><i class="bi bi-door-open"></i> <?= htmlspecialchars($class['room_display']) ?></span>
                                                    </div>
                                                </div>
                                                <div class="schedule-status">
                                                    <?php
                                                    date_default_timezone_set('Asia/Manila');
                                                    $now       = strtotime(date('H:i:s'));
                                                    $start     = strtotime($class['start_time']);
                                                    $end       = strtotime($class['end_time']);
                                                    ?>
                                                    <?php if ($now >= $start && $now <= $end): ?>
                                                        <span class="badge bg-success"><i class="bi bi-dot"></i> Ongoing</span>
                                                    <?php elseif ($now < $start): ?>
                                                        <span class="badge bg-primary"><i class="bi bi-hourglass-split"></i> Upcoming</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><i class="bi bi-check2"></i> Done</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- quick links to grades -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-journal-bookmark"></i>
                            View My Grades
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($year_levels)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">No year levels available yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($year_levels as $level): ?>
                                    <a href="index.php?page=student_semesters&year_level=<?= urlencode($level['year_level']) ?>&school_year=<?= urlencode($school_year) ?>"
                                        class="list-group-item list-group-item-action">
                                        <i class="bi bi-mortarboard-fill"></i>
                                        <?= htmlspecialchars($level['year_level']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>