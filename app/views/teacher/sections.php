<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sections - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/sections.css">
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
                    <a class="nav-link" href="index.php?page=teacher_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=grading">
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
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <span>Grading Management</span>
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
                        <li class="breadcrumb-item">
                            <a href="index.php?page=teacher_dashboard">
                                <i class="bi bi-house-door-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php?page=grading">Grading</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php?page=grading_subjects&year_level=<?= urlencode($year_level) ?>&school_year=<?= urlencode($school_year) ?>&semester=<?= urlencode($semester) ?>">
                                <?php echo htmlspecialchars($year_level); ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header mb-4">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="header-text">
                            <h2 class="header-title">Select Section</h2>
                            <p class="header-subtitle">
                                <span class="subject-badge">
                                    <i class="bi bi-book-fill"></i>
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- sections card -->
                <div class="card sections-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-diagram-3-fill"></i>
                            Available Sections
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($sections)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">No sections found for this subject.</p>
                                <p class="empty-state-subtext">Sections will appear here once they are assigned.</p>
                            </div>
                        <?php else: ?>
                            <div class="sections-grid">
                                <?php foreach ($sections as $section): ?>
                                    <a href="index.php?page=grading_students&year_level=<?= urlencode($year_level) ?>&subject_id=<?= urlencode($subject_id) ?>&section_id=<?= urlencode($section['section_id']) ?>&school_year=<?= urlencode($school_year) ?>&semester=<?= urlencode($semester) ?>"
                                        class="section-card">
                                        <div class="section-card-header">
                                            <div class="section-icon">
                                                <i class="bi bi-grid-3x3-gap-fill"></i>
                                            </div>
                                        </div>
                                        <div class="section-card-body">
                                            <h5 class="section-name"><?php echo htmlspecialchars($section['section_name']); ?></h5>
                                            <p class="section-year-level">
                                                <?php echo htmlspecialchars($section['year_level']); ?>
                                            </p>
                                        </div>
                                        <div class="section-card-footer">
                                            <div class="student-count">
                                                <i class="bi bi-people-fill"></i>
                                                <span class="count-number"><?php echo htmlspecialchars($section['student_count']); ?></span>
                                                <span class="count-label">Students</span>
                                            </div>
                                            <div class="action-arrow">
                                                <i class="bi bi-arrow-right-circle-fill"></i>
                                            </div>
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
</body>

</html>