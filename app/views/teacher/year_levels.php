<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Management - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/year_levels.css">
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
                            <?php
                            $firstname = $_SESSION['user_firstname'] ?? 'T';
                            $lastname  = $_SESSION['user_lastname']  ?? 'U';
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
                        <li class="breadcrumb-item active" aria-current="page">Grading</li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header mb-4">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-layers-fill"></i>
                        </div>
                        <div class="header-text">
                            <h2 class="header-title">Select Year Level</h2>
                            <p class="header-subtitle">Choose a year level to manage student grades</p>
                        </div>
                    </div>
                </div>

                <!-- school year filter — no card wrapper so nothing clips the dropdown -->
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
                                            href="index.php?page=grading&school_year=<?= urlencode($year) ?>&semester=<?= urlencode($semester) ?>">
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
                <div class="card year-levels-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-layers-fill"></i>
                            Available Year Levels
                            <?php if (!empty($school_year)): ?>
                                <span class="badge bg-secondary ms-2"><?= htmlspecialchars($school_year) ?></span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($year_levels)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">No year levels assigned for this school year.</p>
                                <p class="empty-state-subtext">Try selecting a different school year above.</p>
                            </div>
                        <?php else: ?>
                            <div class="year-levels-grid">
                                <?php foreach ($year_levels as $level): ?>
                                    <a href="index.php?page=grading_subjects&year_level=<?= urlencode($level['year_level']) ?>&school_year=<?= urlencode($school_year) ?>&semester=<?= urlencode($semester) ?>"
                                        class="year-level-card">
                                        <div class="year-level-icon">
                                            <i class="bi bi-bookmarks-fill"></i>
                                        </div>
                                        <div class="year-level-content">
                                            <h5 class="year-level-title"><?= htmlspecialchars($level['year_level']); ?></h5>
                                            <p class="year-level-description">View subjects and manage grades</p>
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