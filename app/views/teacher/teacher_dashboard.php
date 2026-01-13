<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/teacher_dashboard.css">
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
                    <a class="nav-link active" href="index.php?page=teacher_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=grading">
                        <i class="bi bi-journal-text"></i>
                        <span>Grading Management</span>
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
                            <i class="bi bi-easel-fill"></i>
                        </div>
                        <span>Teacher Dashboard</span>
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
                            <!-- user avatar placeholder first letters of name -->
                            <?php
                            $firstname = $_SESSION['user_firstname'] ?? 'T';
                            $lastname = $_SESSION['user_lastname'] ?? 'U';
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
                        <div class="welcome-text">
                            <h2 class="welcome-title">Good <?php echo (date('H') < 12) ? 'Morning' : ((date('H') < 18) ? 'Afternoon' : 'Evening'); ?>, <?php echo htmlspecialchars($_SESSION['user_firstname']) . ' ' . htmlspecialchars($_SESSION['user_lastname']); ?></h2>
                            <p class="welcome-subtitle"><?php echo ucfirst(htmlspecialchars($_SESSION['user_role'])); ?> • <?php echo htmlspecialchars($current_date); ?></p>
                        </div>
                    </div>
                </div>

                <!-- schedule section now displays todays schedule -->
                <div class="card schedule-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-event"></i>
                            My Schedule - <?php echo htmlspecialchars($current_date); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($today_schedule)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-calendar-x"></i>
                                </div>
                                <p class="empty-state-text">No classes scheduled for today.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-clock"></i> Time</th>
                                            <th><i class="bi bi-book"></i> Subject</th>
                                            <th><i class="bi bi-people"></i> Section</th>
                                            <th><i class="bi bi-geo-alt"></i> Room</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($today_schedule as $schedule): ?>
                                            <tr>
                                                <td>
                                                    <span class="time-badge">
                                                        <i class="bi bi-clock-fill"></i>
                                                        <?php echo htmlspecialchars($schedule['time_range']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="subject-info">
                                                        <span class="subject-code"><?php echo htmlspecialchars($schedule['subject_code']); ?></span>
                                                        <span class="subject-name"><?php echo htmlspecialchars($schedule['subject_name']); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="section-badge">
                                                        <i class="bi bi-diagram-3"></i>
                                                        <?php echo htmlspecialchars($schedule['section_name']) . ' (' . htmlspecialchars($schedule['year_level']) . ')'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="room-badge">
                                                        <i class="bi bi-door-open"></i>
                                                        <?php echo htmlspecialchars($schedule['room_display']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- quick links section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-journal-bookmark"></i>
                            Quick Links - Grading
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($year_levels)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">No year levels assigned yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($year_levels as $level): ?>
                                    <a href="index.php?page=grading_subjects&year_level=<?php echo urlencode($level['year_level']); ?>"
                                        class="list-group-item list-group-item-action">
                                        <i class="bi bi-bookmark-star-fill"></i>
                                        <?php echo htmlspecialchars($level['year_level']); ?>
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