<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/admin_dashboard.css">
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
                    <a class="nav-link active" href="index.php?page=admin_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=subjects">
                        <i class="bi bi-book-fill"></i>
                        <span>Subject Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=teacher_assignments">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Teacher Assignments</span>
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
                            <i class="bi bi-house-door-fill"></i>
                        </div>
                        <span>Admin Dashboard</span>
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
                            $firstname = $_SESSION['user_firstname'] ?? 'A';
                            $lastname = $_SESSION['user_lastname'] ?? 'D';
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

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Admin Dashboard</h1>
                            <p class="header-subtitle">
                                Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_firstname']); ?></strong>!
                            </p>
                        </div>
                    </div>
                </div>

                <!-- stats cards grid -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4">
                    <!-- students card -->
                    <div class="col">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="stat-icon">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <h5 class="stat-title">Total Students</h5>
                                <div class="stat-value"><?php echo $total_students; ?></div>
                                <div class="stat-trend">
                                    <span class="trend-up">
                                        <i class="bi bi-arrow-up-right"></i>
                                        <?php echo $students_this_month; ?> new this month
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- teachers card -->
                    <div class="col">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="stat-icon" style="background: linear-gradient(135deg, var(--secondary) 0%, #3a2f6b 100%);">
                                    <i class="bi bi-person-badge-fill"></i>
                                </div>
                                <h5 class="stat-title">Total Teachers</h5>
                                <div class="stat-value"><?php echo $total_teachers; ?></div>
                                <div class="stat-trend">
                                    <span class="trend-neutral">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <?php echo $assigned_teachers; ?> assigned
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- sections card -->
                    <div class="col">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <i class="bi bi-diagram-3-fill"></i>
                                </div>
                                <h5 class="stat-title">Total Sections</h5>
                                <div class="stat-value"><?php echo $total_sections; ?></div>
                                <div class="stat-trend">
                                    <span class="trend-info">
                                        <i class="bi bi-info-circle-fill"></i>
                                        Active for 2025-2026
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- assignment ratio card -->
                    <div class="col">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                                    <i class="bi bi-percent"></i>
                                </div>
                                <h5 class="stat-title">Assignment Ratio</h5>
                                <div class="stat-value">
                                    <?php
                                    $ratio = $total_teachers > 0 ? round(($assigned_teachers / $total_teachers) * 100) : 0;
                                    echo $ratio . '%';
                                    ?>
                                </div>
                                <div class="stat-trend">
                                    <span class="trend-info">
                                        <i class="bi bi-graph-up-arrow"></i>
                                        Teachers assigned to sections
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- two column layout -->
                <div class="row g-4">
                    <!-- recent enrollments -->
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-clock-history"></i>
                                    Recent Student Enrollments
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_enrollments)): ?>
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <p class="empty-state-text">No recent enrollments</p>
                                        <p class="empty-state-subtext">New student enrollments will appear here.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th><i class="bi bi-hash"></i> Student ID</th>
                                                    <th><i class="bi bi-person-fill"></i> Name</th>
                                                    <th><i class="bi bi-calendar-event"></i> Year Level</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_enrollments as $enrollment): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?php echo htmlspecialchars($enrollment['student_number']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="student-info">
                                                                <div class="student-name">
                                                                    <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($enrollment['year_level'])): ?>
                                                                <span class="badge year-level-badge">
                                                                    <?php echo htmlspecialchars($enrollment['year_level']); ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted">Not assigned</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- quick links -->
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-link-45deg"></i>
                                    Quick Links
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="quick-links-list">
                                    <a href="index.php?page=subjects" class="quick-link-item">
                                        <div class="link-icon">
                                            <i class="bi bi-book-fill"></i>
                                        </div>
                                        <div class="link-content">
                                            <h6 class="link-title">Subject Management</h6>
                                            <p class="link-desc">Create, view, edit, and manage all subjects</p>
                                        </div>
                                        <div class="link-arrow">
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    </a>

                                    <a href="index.php?page=teacher_assignments" class="quick-link-item">
                                        <div class="link-icon">
                                            <i class="bi bi-person-plus-fill"></i>
                                        </div>
                                        <div class="link-content">
                                            <h6 class="link-title">Teacher Assignments</h6>
                                            <p class="link-desc">Assign teachers to sections and subjects</p>
                                        </div>
                                        <div class="link-arrow">
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>