<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/shared/top-navbar.css">
    <link rel="stylesheet" href="css/pages/subjects.css">
</head>

<body>
    <!-- UPDATED: Added Overlay for Mobile Sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="d-flex">
        <!-- UPDATED: Added id="sidebar" -->
        <div class="sidenav" id="sidebar">
            <div class="sidenav-header">
                <div class="school-brand">
                    <div class="school-logo"><img src="assets/DCSA-LOGO.png" alt="School Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 0.75rem;"></div>
                    <div class="school-info">
                        <h5>Datamex College of Saint Adeline</h5>
                        <p class="subtitle">Learning Management System</p>
                    </div>
                </div>
            </div>
            <ul class="sidenav-menu">
                <li class="nav-item"><a class="nav-link" href="index.php?page=teacher_dashboard"><i class="bi bi-house-door-fill"></i><span>Dashboard</span></a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?page=grading"><i class="bi bi-journal-text"></i><span>Grading Management</span></a></li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=teacher_schedule">
                        <i class="bi bi-calendar-week-fill"></i>
                        <span>My Schedule</span>
                    </a>
                </li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=logout"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
            </ul>
        </div>

        <!-- main content -->
        <div class="main-content flex-grow-1">
            <?php require __DIR__ . '/../shared/top_navbar.php'; ?>

            <!-- page content -->
            <div class="container-fluid p-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?page=teacher_dashboard"><i class="bi bi-house-door-fill"></i> Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=grading&school_year=<?= urlencode($school_year) ?>&semester=<?= urlencode($semester) ?>">Grading</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($year_level) ?></li>
                    </ol>
                </nav>

                <div class="page-header mb-4">
                    <div class="header-content">
                        <div class="header-icon"><i class="bi bi-book-fill"></i></div>
                        <div class="header-text">
                            <h2 class="header-title">Select Subject</h2>
                            <p class="header-subtitle"><span class="year-level-badge"><i class="bi bi-mortarboard-fill"></i> <?= htmlspecialchars($year_level) ?></span></p>
                        </div>
                    </div>
                </div>

                <div class="card subjects-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-journal-bookmark-fill"></i> Available Subjects</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($subjects)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                                <p class="empty-state-text">No subjects assigned for this year level.</p>
                                <p class="empty-state-subtext">Subjects will appear here once they are assigned.</p>
                            </div>
                        <?php else: ?>
                            <div class="subjects-grid">
                                <?php foreach ($subjects as $subject): ?>
                                    <a href="index.php?page=grading_sections&year_level=<?= urlencode($year_level) ?>&subject_id=<?= urlencode($subject['subject_id']) ?>&school_year=<?= urlencode($school_year) ?>&semester=<?= urlencode($semester) ?>" class="subject-card">
                                        <div class="subject-card-header">
                                            <div class="subject-icon"><i class="bi bi-book-half"></i></div>
                                            <span class="school-year-badge"><i class="bi bi-calendar-event"></i> <?= htmlspecialchars($subject['school_year']) ?></span>
                                        </div>
                                        <div class="subject-card-body">
                                            <h5 class="subject-name"><?= htmlspecialchars($subject['subject_name']) ?></h5>
                                            <p class="subject-code"><?= htmlspecialchars($subject['subject_code']) ?></p>
                                        </div>
                                        <div class="subject-card-footer">
                                            <span class="action-text">Manage Grades <i class="bi bi-arrow-right"></i></span>
                                        </div>
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
    <!-- UPDATED: Added Sidebar Toggle Script -->
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