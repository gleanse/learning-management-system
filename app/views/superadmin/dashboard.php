<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/superadmin_dashboard.css">
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
                    <a class="nav-link active" href="index.php?page=superadmin_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=user_management">
                        <i class="bi bi-people-fill"></i>
                        <span>User Management</span>
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
                        <span>Super Admin Dashboard</span>
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
                            $lastname = $_SESSION['user_lastname'] ?? 'A';
                            echo strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1));
                            ?>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- page content -->
            <div class="container-fluid p-4">
                <!-- welcome section -->
                <div class="welcome-section">
                    <div class="welcome-content">
                        <h1 class="welcome-title">
                            Welcome back, <?php echo htmlspecialchars($_SESSION['user_firstname']); ?>!
                        </h1>
                        <p class="welcome-subtitle">
                            Here's an overview of your system's user management statistics
                        </p>
                    </div>
                    <div class="quick-actions">
                        <a href="index.php?page=user_management" class="btn btn-primary">
                            <i class="bi bi-people-fill"></i>
                            Manage Users
                        </a>
                    </div>
                </div>

                <!-- users by role stats -->
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="bi bi-person-workspace"></i>
                        Users by Role
                    </h2>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-xl-2 col-lg-4 col-md-6">
                        <div class="stat-card stat-students">
                            <div class="stat-icon">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value" id="stat-students"><?php echo number_format($users_by_role['student'] ?? 0); ?></span>
                                <span class="stat-label">Students</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-4 col-md-6">
                        <div class="stat-card stat-teachers">
                            <div class="stat-icon">
                                <i class="bi bi-person-video3"></i>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value" id="stat-teachers"><?php echo number_format($users_by_role['teacher'] ?? 0); ?></span>
                                <span class="stat-label">Teachers</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-4 col-md-6">
                        <div class="stat-card stat-registrars">
                            <div class="stat-icon">
                                <i class="bi bi-clipboard-check-fill"></i>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value" id="stat-registrars"><?php echo number_format($users_by_role['registrar'] ?? 0); ?></span>
                                <span class="stat-label">Registrars</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-4 col-md-6">
                        <div class="stat-card stat-admins">
                            <div class="stat-icon">
                                <i class="bi bi-shield-fill-check"></i>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value" id="stat-admins"><?php echo number_format($users_by_role['admin'] ?? 0); ?></span>
                                <span class="stat-label">Admins</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-4 col-md-6">
                        <div class="stat-card stat-superadmins">
                            <div class="stat-icon">
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value" id="stat-superadmins"><?php echo number_format($users_by_role['superadmin'] ?? 0); ?></span>
                                <span class="stat-label">Super Admins</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-4 col-md-6">
                        <div class="stat-card stat-total">
                            <div class="stat-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value" id="stat-total">
                                    <?php
                                    $total_users = array_sum($users_by_role);
                                    echo number_format($total_users);
                                    ?>
                                </span>
                                <span class="stat-label">Total Users</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- student account stats -->
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="bi bi-person-badge-fill"></i>
                        Student Account Status
                    </h2>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="info-card info-total">
                            <div class="info-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-value" id="stat-total-students"><?php echo number_format($total_students); ?></span>
                                <span class="info-label">Total Students</span>
                                <p class="info-description">All active student records in the system</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="info-card info-success">
                            <div class="info-icon">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-value" id="stat-students-with-account"><?php echo number_format($students_with_account); ?></span>
                                <span class="info-label">With Accounts</span>
                                <p class="info-description">Students who have user login accounts</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="info-card info-warning">
                            <div class="info-icon">
                                <i class="bi bi-person-x-fill"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-value" id="stat-students-without-account"><?php echo number_format($students_without_account); ?></span>
                                <span class="info-label">Without Accounts</span>
                                <p class="info-description">Students needing account creation</p>
                            </div>
                            <a href="index.php?page=user_management" class="info-action" id="create-accounts-link" style="<?php echo $students_without_account > 0 ? '' : 'display: none;'; ?>">
                                <i class="bi bi-arrow-right-circle-fill"></i>
                                Create Accounts
                            </a>
                        </div>
                    </div>
                </div>

                <!-- recent users table -->
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="bi bi-clock-history"></i>
                        Recently Created Users
                    </h2>
                </div>

                <div class="card recent-users-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person-plus-fill"></i>
                            Latest User Accounts
                        </h5>
                        <span class="badge bg-light text-dark">Last 10 Users</span>
                    </div>
                    <div class="card-body" id="recent-users-table-wrapper">
                        <?php if (empty($recent_users)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">No users created yet</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-person-fill"></i> Name</th>
                                            <th><i class="bi bi-person-badge-fill"></i> Username</th>
                                            <th><i class="bi bi-envelope-fill"></i> Email</th>
                                            <th><i class="bi bi-person-workspace"></i> Role</th>
                                            <th><i class="bi bi-toggle-on"></i> Status</th>
                                            <th><i class="bi bi-calendar-event"></i> Created Date</th>
                                            <th><i class="bi bi-person-badge"></i> Created By</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recent-users-tbody">
                                        <?php foreach ($recent_users as $user): ?>
                                            <tr>
                                                <td class="user-full-name">
                                                    <?php
                                                    $fullName = $user['first_name'];
                                                    if (!empty($user['middle_name'])) $fullName .= ' ' . $user['middle_name'];
                                                    $fullName .= ' ' . $user['last_name'];
                                                    echo htmlspecialchars($fullName);
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td>
                                                    <?php if (!empty($user['email'])): ?>
                                                        <?php echo htmlspecialchars($user['email']); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $role_badges = [
                                                        'student' => '<span class="badge bg-primary">Student</span>',
                                                        'teacher' => '<span class="badge bg-success">Teacher</span>',
                                                        'registrar' => '<span class="badge bg-warning">Registrar</span>',
                                                        'admin' => '<span class="badge bg-danger">Admin</span>',
                                                        'superadmin' => '<span class="badge bg-secondary">Super Admin</span>'
                                                    ];
                                                    echo $role_badges[$user['role']] ?? '<span class="badge bg-secondary">' . htmlspecialchars($user['role']) . '</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_badges = [
                                                        'active' => '<span class="badge bg-success">Active</span>',
                                                        'inactive' => '<span class="badge bg-secondary">Inactive</span>',
                                                        'suspended' => '<span class="badge bg-danger">Suspended</span>'
                                                    ];
                                                    echo $status_badges[$user['status']] ?? '<span class="badge bg-secondary">' . htmlspecialchars($user['status']) . '</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="text-muted">
                                                        <i class="bi bi-clock"></i>
                                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($user['created_by_username'])): ?>
                                                        <span class="admin-badge">
                                                            <i class="bi bi-person-badge-fill"></i>
                                                            <?php echo htmlspecialchars($user['created_by_username']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">System</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($recent_users)): ?>
                        <div class="card-footer">
                            <a href="index.php?page=user_management" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-people-fill"></i>
                                View All Users
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/superadmin-dashboard.js"></script>

</body>

</html>