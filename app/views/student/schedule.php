<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/shared/top-navbar.css">
    <link rel="stylesheet" href="css/pages/schedule.css">
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="d-flex">
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
                    <a class="nav-link" href="index.php?page=student_dashboard">
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
                    <a class="nav-link active" href="index.php?page=student_schedule">
                        <i class="bi bi-calendar-week-fill"></i>
                        <span>My Schedule</span>
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
            <?php require __DIR__ . '/../shared/top_navbar.php'; ?>

            <div class="container-fluid p-4">
                <!-- breadcrumbs -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?page=student_dashboard"><i class="bi bi-house-door-fill"></i> Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">My Schedule</li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="welcome-banner mb-4">
                    <div class="welcome-content">
                        <div class="welcome-text">
                            <h2 class="welcome-title"><i class="bi bi-calendar-week"></i> My Weekly Schedule</h2>
                            <p class="welcome-subtitle">
                                <?php echo htmlspecialchars($student_info['section_name'] ?? 'No section assigned'); ?>
                                &bull; <?php echo htmlspecialchars($semester); ?> Semester
                                &bull; <?php echo htmlspecialchars($school_year); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- timetable card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-grid-3x3-gap"></i> Weekly Timetable
                        </h5>
                    </div>
                    <div class="card-body p-0 p-md-3">

                        <?php
                        $has_schedule = false;
                        foreach ($schedule_by_day as $classes) {
                            if (!empty($classes)) {
                                $has_schedule = true;
                                break;
                            }
                        }
                        ?>

                        <?php if (!$has_schedule): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="bi bi-calendar-x"></i></div>
                                <p class="empty-state-text">No schedule available for this semester.</p>
                            </div>
                        <?php else: ?>
                            <!-- desktop timetable grid -->
                            <div class="timetable-wrapper d-none d-md-block">
                                <div class="timetable-grid">
                                    <!-- day headers -->
                                    <div class="timetable-header-row">
                                        <?php foreach ($days_order as $day): ?>
                                            <div class="timetable-day-header <?php echo (date('l') === $day) ? 'today' : ''; ?>">
                                                <?php echo $day; ?>
                                                <?php if (date('l') === $day): ?>
                                                    <span class="today-badge">Today</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- day columns -->
                                    <div class="timetable-body-row">
                                        <?php foreach ($days_order as $day): ?>
                                            <div class="timetable-day-col <?php echo (date('l') === $day) ? 'today-col' : ''; ?>">
                                                <?php if (empty($schedule_by_day[$day])): ?>
                                                    <div class="no-class-slot">
                                                        <i class="bi bi-dash"></i>
                                                        <span>No class</span>
                                                    </div>
                                                <?php else: ?>
                                                    <?php foreach ($schedule_by_day[$day] as $class): ?>
                                                        <div class="class-card">
                                                            <div class="class-time">
                                                                <i class="bi bi-clock-fill"></i>
                                                                <?php echo htmlspecialchars($class['time_range']); ?>
                                                            </div>
                                                            <div class="class-subject-code">
                                                                <?php echo htmlspecialchars($class['subject_code']); ?>
                                                            </div>
                                                            <div class="class-subject-name">
                                                                <?php echo htmlspecialchars($class['subject_name']); ?>
                                                            </div>
                                                            <div class="class-meta">
                                                                <span class="class-teacher">
                                                                    <i class="bi bi-person-fill"></i>
                                                                    <?php echo htmlspecialchars($class['teacher_name']); ?>
                                                                </span>
                                                                <span class="class-room">
                                                                    <i class="bi bi-door-open-fill"></i>
                                                                    <?php echo htmlspecialchars($class['room_display']); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- mobile stacked list -->
                            <div class="d-block d-md-none p-3">
                                <?php foreach ($days_order as $day): ?>
                                    <div class="mobile-day-block mb-3">
                                        <div class="mobile-day-header <?php echo (date('l') === $day) ? 'today' : ''; ?>">
                                            <i class="bi bi-calendar3"></i>
                                            <?php echo $day; ?>
                                            <?php if (date('l') === $day): ?>
                                                <span class="today-badge ms-2">Today</span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (empty($schedule_by_day[$day])): ?>
                                            <div class="mobile-no-class">
                                                <i class="bi bi-dash-circle"></i> No class
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($schedule_by_day[$day] as $class): ?>
                                                <div class="mobile-class-card">
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <span class="class-subject-code"><?php echo htmlspecialchars($class['subject_code']); ?></span>
                                                        <span class="time-badge">
                                                            <i class="bi bi-clock-fill"></i>
                                                            <?php echo htmlspecialchars($class['time_range']); ?>
                                                        </span>
                                                    </div>
                                                    <div class="class-subject-name mb-2"><?php echo htmlspecialchars($class['subject_name']); ?></div>
                                                    <div class="class-meta">
                                                        <span class="class-teacher">
                                                            <i class="bi bi-person-fill"></i>
                                                            <?php echo htmlspecialchars($class['teacher_name']); ?>
                                                        </span>
                                                        <span class="class-room">
                                                            <i class="bi bi-door-open-fill"></i>
                                                            <?php echo htmlspecialchars($class['room_display']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');

            function toggle() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
                document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
            }
            if (toggleBtn) toggleBtn.addEventListener('click', toggle);
            if (overlay) overlay.addEventListener('click', toggle);
        });
    </script>
    <script src="js/shared/top-navbar.js"></script>
</body>

</html>