<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades - Semesters - LMS</title>
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
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= htmlspecialchars($year_level ?? 'Year Level') ?>
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header mb-4">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-calendar-range"></i>
                        </div>
                        <div class="header-text">
                            <h2 class="header-title">Select Semester</h2>
                            <p class="header-subtitle">
                                <?= htmlspecialchars($year_level ?? 'Year Level') ?> &bull; <?= htmlspecialchars($school_year) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- semesters card -->
                <div class="card year-levels-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-range"></i>
                            Available Semesters
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($semesters)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">No semesters available.</p>
                                <p class="empty-state-subtext">No enrollment records found for this year level.</p>
                            </div>
                        <?php else: ?>
                            <div class="year-levels-grid">
                                <?php foreach ($semesters as $sem): ?>
                                    <a href="index.php?page=student_subjects&year_level=<?= urlencode($year_level) ?>&semester=<?= urlencode($sem['semester']) ?>&school_year=<?= urlencode($school_year) ?>"
                                        class="year-level-card">
                                        <div class="year-level-icon">
                                            <i class="bi bi-calendar-check-fill"></i>
                                        </div>
                                        <div class="year-level-content">
                                            <h5 class="year-level-title"><?= htmlspecialchars($sem['semester']) ?> Semester</h5>
                                            <p class="year-level-description">View subjects for this semester</p>
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
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>