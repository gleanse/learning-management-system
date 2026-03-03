<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades - <?php echo htmlspecialchars($subject['subject_name'] ?? 'Subject'); ?> - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/shared/top-navbar.css">
    <link rel="stylesheet" href="css/pages/student_dashboard.css">
</head>

<body>
    <!-- ADDED: Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

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
                    <a class="nav-link" href="index.php?page=student_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=student_grades">
                        <i class="bi bi-journal-text"></i>
                        <span>My Grades</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=student_schedule">
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

        <!-- main content -->
        <div class="main-content flex-grow-1">
            <?php require __DIR__ . '/../shared/top_navbar.php'; ?>

            <!-- page content -->
            <div class="container-fluid p-4">
                <!-- breadcrumbs -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?page=student_dashboard">
                                <i class="bi bi-house-door-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php?page=student_grades&school_year=<?= urlencode($school_year) ?>">My Grades</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php?page=student_semesters&year_level=<?= urlencode($year_level) ?>&school_year=<?= urlencode($school_year) ?>">
                                <?= htmlspecialchars($year_level) ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php?page=student_subjects&year_level=<?= urlencode($year_level) ?>&semester=<?= urlencode($semester) ?>&school_year=<?= urlencode($school_year) ?>">
                                <?= htmlspecialchars($semester) ?> Semester
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= htmlspecialchars($subject['subject_code'] ?? 'Subject') ?>
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header mb-4">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <div class="header-text">
                            <h2 class="header-title"><?= htmlspecialchars($subject['subject_code'] ?? 'Subject') ?></h2>
                            <p class="header-subtitle">
                                <?= htmlspecialchars($subject['subject_name'] ?? '') ?> &bull;
                                <?= htmlspecialchars($semester) ?> Semester &bull;
                                <?= htmlspecialchars($school_year) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- grades card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-journal-check"></i>
                            Grades by Grading Period
                        </h5>
                    </div>
                    <div class="card-body p-0 p-md-4"> <!-- Removed padding on mobile for full width rows -->
                        <?php $grading_periods = ['Prelim', 'Midterm', 'Prefinal', 'Final']; ?>
                        <div class="table-responsive">
                            <!-- ADDED "mobile-card-table" class -->
                            <table class="table table-hover mobile-card-table mb-0">
                                <thead>
                                    <tr>
                                        <th><i class="bi bi-calendar-event"></i> Grading Period</th>
                                        <th><i class="bi bi-star-fill"></i> Grade</th>
                                        <th><i class="bi bi-chat-left-text"></i> Remarks</th>
                                        <th><i class="bi bi-calendar-check"></i> Graded Date</th>
                                        <th><i class="bi bi-person-badge"></i> Graded By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grading_periods as $period): ?>
                                        <?php $grade_data = $grades_by_period[$period] ?? null; ?>
                                        <tr>
                                            <!-- ADDED: data-label to every td -->
                                            <td data-label="Grading Period">
                                                <span class="period-badge">
                                                    <i class="bi bi-bookmark-fill"></i>
                                                    <?= htmlspecialchars($period) ?>
                                                </span>
                                            </td>
                                            <td data-label="Grade">
                                                <?php if ($grade_data): ?>
                                                    <span class="grade-value"><?= number_format($grade_data['grade_value'], 2) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Remarks">
                                                <?php if ($grade_data && !empty($grade_data['remarks'])): ?>
                                                    <span class="remarks-text"><?= htmlspecialchars($grade_data['remarks']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Graded Date">
                                                <?php if ($grade_data): ?>
                                                    <span class="date-text"><?= date('M d, Y', strtotime($grade_data['graded_date'])) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Graded By">
                                                <?php if ($grade_data): ?>
                                                    <span class="teacher-text">
                                                        <?= htmlspecialchars($grade_data['teacher_first_name'] . ' ' . $grade_data['teacher_last_name']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');

            function toggleSidebar() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
                document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
            }

            if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
            if (overlay) overlay.addEventListener('click', toggleSidebar);
        });
    </script>

    <!-- grades polling -->
    <script>
        (function() {
            const params = new URLSearchParams(window.location.search);
            const subject_id = params.get('subject_id');
            const year_level = params.get('year_level');
            const semester = params.get('semester');
            const school_year = params.get('school_year');

            const periods = ['Prelim', 'Midterm', 'Prefinal', 'Final'];

            function formatDate(dateStr) {
                if (!dateStr) return '-';
                const d = new Date(dateStr);
                return d.toLocaleDateString('en-US', {
                    month: 'short',
                    day: '2-digit',
                    year: 'numeric'
                });
            }

            function pollGrades() {
                fetch(`index.php?page=ajax_get_grades&subject_id=${subject_id}&year_level=${encodeURIComponent(year_level)}&semester=${encodeURIComponent(semester)}&school_year=${encodeURIComponent(school_year)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return;

                        const tbody = document.querySelector('.mobile-card-table tbody');
                        const rows = tbody.querySelectorAll('tr');

                        rows.forEach((row, index) => {
                            const period = periods[index];
                            const grade_data = data.grades[period] ?? null;
                            const cells = row.querySelectorAll('td');

                            // grade cell
                            cells[1].innerHTML = grade_data ?
                                `<span class="grade-value">${parseFloat(grade_data.grade_value).toFixed(2)}</span>` :
                                `<span class="text-muted">-</span>`;

                            // remarks cell
                            cells[2].innerHTML = (grade_data && grade_data.remarks) ?
                                `<span class="remarks-text">${grade_data.remarks}</span>` :
                                `<span class="text-muted">-</span>`;

                            // graded date cell
                            cells[3].innerHTML = grade_data ?
                                `<span class="date-text">${formatDate(grade_data.graded_date)}</span>` :
                                `<span class="text-muted">-</span>`;

                            // graded by cell
                            cells[4].innerHTML = grade_data ?
                                `<span class="teacher-text">${grade_data.teacher_first_name} ${grade_data.teacher_last_name}</span>` :
                                `<span class="text-muted">-</span>`;
                        });
                    })
                    .catch(() => {
                        // silently fail — polling will retry next interval
                    });
            }

            // stop polling when tab is hidden to save resources
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    clearInterval(poller);
                } else {
                    poller = setInterval(pollGrades, 3000);
                }
            });

            let poller = setInterval(pollGrades, 3000);
        })();
    </script>
</body>

</html>