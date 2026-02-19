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
            <!-- top navbar -->
            <nav class="navbar top-navbar">
                <div class="container-fluid">
                    <div class="navbar-brand mb-0">
                        <div class="page-icon">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <span>My Grades</span>
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
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-journal-check"></i>
                            Grades by Grading Period
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php $grading_periods = ['Prelim', 'Midterm', 'Prefinal', 'Final']; ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                                            <td>
                                                <span class="period-badge">
                                                    <i class="bi bi-bookmark-fill"></i>
                                                    <?= htmlspecialchars($period) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($grade_data): ?>
                                                    <span class="grade-value"><?= number_format($grade_data['grade_value'], 2) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($grade_data && !empty($grade_data['remarks'])): ?>
                                                    <span class="remarks-text"><?= htmlspecialchars($grade_data['remarks']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($grade_data): ?>
                                                    <span class="date-text"><?= date('M d, Y', strtotime($grade_data['graded_date'])) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
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
</body>

</html>