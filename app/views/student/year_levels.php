<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades - Year Levels - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/shared/top-navbar.css">
    <link rel="stylesheet" href="css/pages/student_dashboard.css">
</head>

<body>
    <!-- sidebar overlay for mobile -->
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
                        <li class="breadcrumb-item active" aria-current="page">My Grades</li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header mb-4">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-layers-fill"></i>
                        </div>
                        <div class="header-text">
                            <h2 class="header-title">My Grades</h2>
                            <p class="header-subtitle">View your grades by subject or see your overall performance</p>
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════ -->
                <!-- SECTION 1: individual grades — select year level -->
                <!-- ═══════════════════════════════════════════════ -->

                <div class="section-divider-label mb-2">
                    <span class="section-tag">
                        <i class="bi bi-layers-fill me-1"></i> Browse by Year Level
                    </span>
                </div>

                <!-- school year filter for individual grades -->
                <?php if (!empty($available_years)): ?>
                    <div class="sy-filter-bar mb-3">
                        <span class="sy-filter-label">
                            <i class="bi bi-calendar-range"></i> School Year
                        </span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-primary dropdown-toggle sy-dropdown-btn"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-calendar-check"></i>
                                <?= htmlspecialchars($school_year) ?>
                            </button>
                            <ul class="dropdown-menu sy-dropdown-menu shadow-sm">
                                <?php foreach ($available_years as $year): ?>
                                    <li>
                                        <a class="dropdown-item <?= $school_year === $year ? 'active' : '' ?>"
                                            href="index.php?page=student_grades&school_year=<?= urlencode($year) ?>&ov_school_year=<?= urlencode($ov_school_year) ?>&ov_year_level=<?= urlencode($ov_year_level ?? '') ?>&ov_semester=<?= urlencode($ov_semester ?? '') ?>">
                                            <i class="bi bi-<?= $school_year === $year ? 'check2' : 'calendar3' ?> me-1"></i>
                                            <?= htmlspecialchars($year) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <span class="sy-year-count">
                            <?= count($available_years) ?> school year(s) available
                        </span>
                    </div>
                <?php endif; ?>

                <!-- year levels card -->
                <div class="card year-levels-card mb-5">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-layers-fill"></i>
                            Available Year Levels
                            <?php if (!empty($school_year)): ?>
                                <span class="badge bg-white bg-opacity-25 ms-2"><?= htmlspecialchars($school_year) ?></span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($year_levels)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">No year levels available yet.</p>
                                <p class="empty-state-subtext">Try selecting a different school year above.</p>
                            </div>
                        <?php else: ?>
                            <div class="year-levels-grid">
                                <?php foreach ($year_levels as $level): ?>
                                    <a href="index.php?page=student_semesters&year_level=<?= urlencode($level['year_level']) ?>&school_year=<?= urlencode($school_year) ?>"
                                        class="year-level-card">
                                        <div class="year-level-icon">
                                            <i class="bi bi-bookmarks-fill"></i>
                                        </div>
                                        <div class="year-level-content">
                                            <h5 class="year-level-title"><?= htmlspecialchars($level['year_level']) ?></h5>
                                            <p class="year-level-description">View subjects and grades</p>
                                        </div>
                                        <div class="year-level-arrow">
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════ -->
                <!-- SECTION 2: overall grades table                 -->
                <!-- ═══════════════════════════════════════════════ -->

                <div class="section-divider-label mb-2">
                    <span class="section-tag">
                        <i class="bi bi-table me-1"></i> Overall Grades
                    </span>
                </div>

                <!-- overall grades filters — separate from section 1 using ov_ params -->
                <form method="GET" action="index.php" id="overallGradesFilterForm">
                    <input type="hidden" name="page" value="student_grades">
                    <input type="hidden" name="school_year" value="<?= htmlspecialchars($school_year) ?>">

                    <div class="sy-filter-bar mb-3">
                        <span class="sy-filter-label">
                            <i class="bi bi-funnel"></i> Filter
                        </span>

                        <!-- ov school year -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-primary dropdown-toggle sy-dropdown-btn"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-calendar-check"></i>
                                <?= htmlspecialchars($ov_school_year) ?>
                            </button>
                            <ul class="dropdown-menu sy-dropdown-menu shadow-sm">
                                <?php foreach ($available_years as $year): ?>
                                    <li>
                                        <a class="dropdown-item <?= $ov_school_year === $year ? 'active' : '' ?>"
                                            href="index.php?page=student_grades&school_year=<?= urlencode($school_year) ?>&ov_school_year=<?= urlencode($year) ?>">
                                            <i class="bi bi-<?= $ov_school_year === $year ? 'check2' : 'calendar3' ?> me-1"></i>
                                            <?= htmlspecialchars($year) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- ov year level -->
                        <?php if (!empty($ov_year_levels)): ?>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle sy-dropdown-btn"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="bi bi-layers"></i>
                                    <?= htmlspecialchars($ov_year_level ?? 'Year Level') ?>
                                </button>
                                <ul class="dropdown-menu sy-dropdown-menu shadow-sm">
                                    <?php foreach ($ov_year_levels as $yl): ?>
                                        <li>
                                            <a class="dropdown-item <?= $ov_year_level === $yl['year_level'] ? 'active' : '' ?>"
                                                href="index.php?page=student_grades&school_year=<?= urlencode($school_year) ?>&ov_school_year=<?= urlencode($ov_school_year) ?>&ov_year_level=<?= urlencode($yl['year_level']) ?>">
                                                <i class="bi bi-<?= $ov_year_level === $yl['year_level'] ? 'check2' : 'layers' ?> me-1"></i>
                                                <?= htmlspecialchars($yl['year_level']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- ov semester -->
                        <?php if (!empty($ov_semesters)): ?>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle sy-dropdown-btn"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="bi bi-calendar2-week"></i>
                                    <?= htmlspecialchars($ov_semester ?? 'Semester') ?> Semester
                                </button>
                                <ul class="dropdown-menu sy-dropdown-menu shadow-sm">
                                    <?php foreach ($ov_semesters as $sem): ?>
                                        <li>
                                            <a class="dropdown-item <?= $ov_semester === $sem['semester'] ? 'active' : '' ?>"
                                                href="index.php?page=student_grades&school_year=<?= urlencode($school_year) ?>&ov_school_year=<?= urlencode($ov_school_year) ?>&ov_year_level=<?= urlencode($ov_year_level ?? '') ?>&ov_semester=<?= urlencode($sem['semester']) ?>">
                                                <i class="bi bi-<?= $ov_semester === $sem['semester'] ? 'check2' : 'calendar2-week' ?> me-1"></i>
                                                <?= htmlspecialchars($sem['semester']) ?> Semester
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- overall grades card -->
                <div class="card overall-grades-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-table"></i>
                            Overall Grades
                            <?php if ($ov_year_level && $ov_semester): ?>
                                <span class="badge bg-white bg-opacity-25 ms-2">
                                    <?= htmlspecialchars($ov_year_level) ?> &mdash; <?= htmlspecialchars($ov_semester) ?> Sem
                                </span>
                                <span class="badge bg-white bg-opacity-10 ms-1">
                                    <?= htmlspecialchars($ov_school_year) ?>
                                </span>
                            <?php endif; ?>
                        </h5>
                        <span class="badge bg-white bg-opacity-25 ms-auto" id="overallSubjectCount">
                            <?= count($grades_overview) ?> Subject<?= count($grades_overview) !== 1 ? 's' : '' ?>
                        </span>
                        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary ms-2">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>

                    <div class="card-body p-0">
                        <?php if (empty($grades_overview)): ?>
                            <div class="empty-state" id="overallEmptyState">
                                <div class="empty-state-icon">
                                    <i class="bi bi-journal-x"></i>
                                </div>
                                <p class="empty-state-text">No subjects found.</p>
                                <p class="empty-state-subtext">Try adjusting the filters above.</p>
                            </div>
                            <div class="table-responsive d-none" id="overallTableWrapper">
                            <?php else: ?>
                                <div class="table-responsive" id="overallTableWrapper">
                                <?php endif; ?>
                                <table class="table table-hover align-middle mb-0 mobile-card-table" id="overallGradesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Subject Code</th>
                                            <th>Subject Name</th>
                                            <th class="text-center">Prelim</th>
                                            <th class="text-center">Midterm</th>
                                            <th class="text-center">Prefinal</th>
                                            <th class="text-center">Final</th>
                                            <th class="text-center pe-4">Average</th>
                                        </tr>
                                    </thead>
                                    <tbody id="overallGradesBody">
                                        <?php
                                        $total_avg = 0;
                                        $avg_count = 0;

                                        foreach ($grades_overview as $row):
                                            $available = array_filter(
                                                [$row['prelim'], $row['midterm'], $row['prefinal'], $row['final']],
                                                fn($v) => $v !== null
                                            );
                                            $subject_avg = !empty($available)
                                                ? array_sum($available) / count($available)
                                                : null;

                                            if ($subject_avg !== null) {
                                                $total_avg += $subject_avg;
                                                $avg_count++;
                                            }

                                            $is_complete = count($available) === 4;
                                        ?>
                                            <tr>
                                                <td class="ps-4" data-label="Code">
                                                    <span class="period-badge">
                                                        <i class="bi bi-book"></i>
                                                        <?= htmlspecialchars($row['subject_code']) ?>
                                                    </span>
                                                </td>
                                                <td data-label="Subject">
                                                    <span class="teacher-text">
                                                        <?= htmlspecialchars($row['subject_name']) ?>
                                                    </span>
                                                    <?php if ($is_complete): ?>
                                                        <i class="bi bi-check-circle-fill text-success ms-1" title="All periods graded" style="font-size: 0.75rem;"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center" data-label="Prelim">
                                                    <?php if ($row['prelim'] !== null): ?>
                                                        <span class="grade-value"><?= number_format($row['prelim'], 2) ?></span>
                                                    <?php else: ?>
                                                        <span class="remarks-text text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center" data-label="Midterm">
                                                    <?php if ($row['midterm'] !== null): ?>
                                                        <span class="grade-value"><?= number_format($row['midterm'], 2) ?></span>
                                                    <?php else: ?>
                                                        <span class="remarks-text text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center" data-label="Prefinal">
                                                    <?php if ($row['prefinal'] !== null): ?>
                                                        <span class="grade-value"><?= number_format($row['prefinal'], 2) ?></span>
                                                    <?php else: ?>
                                                        <span class="remarks-text text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center" data-label="Final">
                                                    <?php if ($row['final'] !== null): ?>
                                                        <span class="grade-value"><?= number_format($row['final'], 2) ?></span>
                                                    <?php else: ?>
                                                        <span class="remarks-text text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center pe-4" data-label="Average">
                                                    <?php if ($subject_avg !== null): ?>
                                                        <span class="grade-value <?= $subject_avg >= 75 ? 'text-success' : 'text-danger' ?>">
                                                            <?= number_format($subject_avg, 2) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="remarks-text text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>

                                    <tfoot class="table-light">
                                        <tr id="overallAvgRow" <?= $avg_count === 0 ? 'class="d-none"' : '' ?>>
                                            <td colspan="6" class="ps-4 fw-semibold text-end pe-3 teacher-text">
                                                Overall Average
                                            </td>
                                            <td class="text-center pe-4" id="overallAvgCell">
                                                <?php if ($avg_count > 0): ?>
                                                    <?php $overall = $total_avg / $avg_count; ?>
                                                    <span class="grade-value <?= $overall >= 75 ? 'text-success' : 'text-danger' ?>">
                                                        <?= number_format($overall, 2) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                </div>
                            </div>
                    </div>
                    <!-- end overall grades card -->

                </div>
                <!-- end page content -->
            </div>
            <!-- end main content -->
        </div>

        <script src="bootstrap/js/bootstrap.bundle.min.js"></script>

        <script>
            // mobile sidebar toggle
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

            // overall grades polling
            const OV_POLL_INTERVAL = 15000;
            const ovSchoolYear = '<?= htmlspecialchars($ov_school_year) ?>';
            const ovYearLevel = '<?= htmlspecialchars($ov_year_level ?? '') ?>';
            const ovSemester = '<?= htmlspecialchars($ov_semester ?? '') ?>';

            if (ovYearLevel && ovSemester) {
                setInterval(pollOverallGrades, OV_POLL_INTERVAL);
            }

            function pollOverallGrades() {
                const url = `index.php?page=ajax_get_overall_grades` +
                    `&school_year=${encodeURIComponent(ovSchoolYear)}` +
                    `&year_level=${encodeURIComponent(ovYearLevel)}` +
                    `&semester=${encodeURIComponent(ovSemester)}`;

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return;
                        renderOverallGrades(data.grades_overview, data.overall_average);
                    })
                    .catch(() => {
                        // silently fail — next poll will retry
                    });
            }

            function renderOverallGrades(grades, overallAverage) {
                const tbody = document.getElementById('overallGradesBody');
                const emptyState = document.getElementById('overallEmptyState');
                const tableWrapper = document.getElementById('overallTableWrapper');
                const countBadge = document.getElementById('overallSubjectCount');

                if (!grades.length) {
                    emptyState.classList.remove('d-none');
                    tableWrapper.classList.add('d-none');
                    countBadge.textContent = '0 Subjects';
                    return;
                }

                emptyState.classList.add('d-none');
                tableWrapper.classList.remove('d-none');

                const count = grades.length;
                countBadge.textContent = `${count} Subject${count !== 1 ? 's' : ''}`;

                tbody.innerHTML = grades.map(row => {
                    const prelim = row.prelim !== null ? parseFloat(row.prelim).toFixed(2) : null;
                    const midterm = row.midterm !== null ? parseFloat(row.midterm).toFixed(2) : null;
                    const prefinal = row.prefinal !== null ? parseFloat(row.prefinal).toFixed(2) : null;
                    const final = row.final !== null ? parseFloat(row.final).toFixed(2) : null;
                    const average = row.average !== null ? parseFloat(row.average).toFixed(2) : null;

                    const gradeCell = (val, label) => `
                    <td class="text-center" data-label="${label}">
                        ${val !== null
                            ? `<span class="grade-value">${val}</span>`
                            : `<span class="remarks-text text-muted">—</span>`
                        }
                    </td>`;

                    const avgColor = average !== null ? (parseFloat(average) >= 75 ? 'text-success' : 'text-danger') : '';
                    const completeIcon = row.is_complete ?
                        `<i class="bi bi-check-circle-fill text-success ms-1" title="All periods graded" style="font-size: 0.75rem;"></i>` :
                        '';

                    return `
                    <tr>
                        <td class="ps-4" data-label="Code">
                            <span class="period-badge">
                                <i class="bi bi-book"></i>
                                ${escapeHtml(row.subject_code)}
                            </span>
                        </td>
                        <td data-label="Subject">
                            <span class="teacher-text">${escapeHtml(row.subject_name)}</span>
                            ${completeIcon}
                        </td>
                        ${gradeCell(prelim,   'Prelim')}
                        ${gradeCell(midterm,  'Midterm')}
                        ${gradeCell(prefinal, 'Prefinal')}
                        ${gradeCell(final,    'Final')}
                        <td class="text-center pe-4" data-label="Average">
                            ${average !== null
                                ? `<span class="grade-value ${avgColor}">${average}</span>`
                                : `<span class="remarks-text text-muted">—</span>`
                            }
                        </td>
                    </tr>`;
                }).join('');

                // update overall average footer
                const avgRow = document.getElementById('overallAvgRow');
                const avgCell = document.getElementById('overallAvgCell');

                if (overallAverage !== null) {
                    const color = parseFloat(overallAverage) >= 75 ? 'text-success' : 'text-danger';
                    avgCell.innerHTML = `<span class="grade-value ${color}">${parseFloat(overallAverage).toFixed(2)}</span>`;
                    avgRow.classList.remove('d-none');
                } else {
                    avgRow.classList.add('d-none');
                }
            }

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.appendChild(document.createTextNode(str));
                return div.innerHTML;
            }
        </script>
        <script src="js/shared/top-navbar.js"></script>

        <style>
            /* section divider label */
            .section-divider-label {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .section-divider-label::after {
                content: '';
                flex: 1;
                height: 2px;
                background: linear-gradient(90deg, #e2e8f0 0%, transparent 100%);
                border-radius: 1px;
            }

            .section-tag {
                font-size: 0.8rem;
                font-weight: 700;
                color: var(--secondary);
                text-transform: uppercase;
                letter-spacing: 0.05em;
                white-space: nowrap;
                padding: 0.25rem 0.75rem;
                background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
                border-radius: 2rem;
                border: 1px solid #e2e8f0;
            }

            /* overall grades card header flex fix */
            .overall-grades-card .card-header {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                flex-wrap: wrap;
            }

            .overall-grades-card .card-header h5 {
                flex: 1;
            }

            @media print {

                .sidenav,
                .sidebar-overlay,
                .top-navbar,
                .breadcrumb,
                .page-header,
                .sy-filter-bar,
                .year-levels-card,
                .section-divider-label,
                button {
                    display: none !important;
                }

                .main-content {
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .overall-grades-card {
                    box-shadow: none !important;
                    border: 1px solid #dee2e6 !important;
                }

                .card-header {
                    background: #2e275d !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .overall-grades-card .grade-value {
                    font-size: 0.75rem !important;
                }

                .overall-grades-card .table thead th,
                .overall-grades-card .table tbody td {
                    font-size: 0.75rem !important;
                    padding: 0.4rem 0.6rem !important;
                }

                .overall-grades-card .period-badge {
                    font-size: 0.7rem !important;
                    padding: 0.2rem 0.4rem !important;
                }
            }
        </style>
</body>

</html>