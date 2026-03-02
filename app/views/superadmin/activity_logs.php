<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/shared/top-navbar.css">
    <link rel="stylesheet" href="css/pages/activity-logs.css">
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;" id="toastContainer"></div>

    <div class="d-flex">
        <!-- sidebar - SUPERADMIN VERSION (correct) -->
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
                    <a class="nav-link" href="index.php?page=superadmin_dashboard">
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
                    <a class="nav-link active" href="index.php?page=activity_logs">
                        <i class="bi bi-journal-text"></i>
                        <span>Activity Logs</span>
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
                            <a href="index.php?page=superadmin_dashboard">
                                <i class="bi bi-house-door-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Activity Logs
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Activity Logs</h1>
                            <p class="header-subtitle">View audit trail of all system actions and changes</p>
                        </div>
                    </div>
                </div>

                <!-- summary stats row -->
                <div class="row mb-4">
                    <!-- total logs -->
                    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">Total Logs</p>
                                <h3 class="stat-value"><?= number_format($total_logs ?? 0) ?></h3>
                                <p class="stat-sub">All recorded actions</p>
                            </div>
                        </div>
                    </div>

                    <!-- active users -->
                    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                        <div class="stat-card stat-card-secondary">
                            <div class="stat-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">Active Users</p>
                                <h3 class="stat-value"><?= number_format($summary['active_users'] ?? 0) ?></h3>
                                <p class="stat-sub">Last 7 days</p>
                            </div>
                        </div>
                    </div>

                    <!-- logins -->
                    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                        <div class="stat-card stat-card-success">
                            <div class="stat-icon">
                                <i class="bi bi-box-arrow-in-right"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">Logins</p>
                                <h3 class="stat-value"><?= number_format(array_sum(array_column($summary, 'logins'))) ?></h3>
                                <p class="stat-sub">Last 7 days</p>
                            </div>
                        </div>
                    </div>

                    <!-- modifications -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <i class="bi bi-pencil-fill"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">Changes</p>
                                <h3 class="stat-value"><?= number_format(array_sum(array_column($summary, 'updates')) + array_sum(array_column($summary, 'creates'))) ?></h3>
                                <p class="stat-sub">Creates & updates</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- filters card -->
                <div class="card filters-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-funnel-fill"></i>
                            Filter Logs
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="index.php" id="filterForm">
                            <input type="hidden" name="page" value="activity_logs">

                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">
                                        <i class="bi bi-person-fill"></i>
                                        User
                                    </label>
                                    <select class="form-select" name="user_id">
                                        <option value="">All Users</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>" <?= ($filters['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['role'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">
                                        <i class="bi bi-tag-fill"></i>
                                        Action
                                    </label>
                                    <select class="form-select" name="action">
                                        <option value="">All Actions</option>
                                        <?php foreach ($actions as $action): ?>
                                            <option value="<?= htmlspecialchars($action) ?>" <?= ($filters['action'] ?? '') == $action ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $action))) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">
                                        <i class="bi bi-table"></i>
                                        Table
                                    </label>
                                    <select class="form-select" name="table">
                                        <option value="">All Tables</option>
                                        <?php foreach ($tables as $table): ?>
                                            <option value="<?= htmlspecialchars($table) ?>" <?= ($filters['table_affected'] ?? '') == $table ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $table))) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">
                                        <i class="bi bi-calendar"></i>
                                        From
                                    </label>
                                    <input type="date" class="form-control" name="date_from"
                                        value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">
                                        <i class="bi bi-calendar"></i>
                                        To
                                    </label>
                                    <input type="date" class="form-control" name="date_to"
                                        value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0" name="search"
                                            placeholder="Search by description, user name, or IP address..."
                                            value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="bi bi-funnel-fill"></i>
                                        Apply Filters
                                    </button>
                                    <a href="index.php?page=activity_logs" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- logs table card -->
                <div class="card logs-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul"></i>
                            Audit Trail
                        </h5>
                        <div class="d-flex gap-2">
                            <!-- SUPERADMIN ONLY: clear logs button -->
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                                <i class="bi bi-trash"></i>
                                Clear Old Logs
                            </button>
                            <a href="index.php?page=activity_logs&action=export&<?= http_build_query($_GET) ?>"
                                class="btn btn-sm btn-success" id="exportBtn">
                                <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                                Export to CSV
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($logs)): ?>
                            <div class="empty-state py-5">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">No activity logs found</p>
                                <?php if (!empty($filters)): ?>
                                    <button class="btn btn-outline-primary mt-3" onclick="window.location.href='index.php?page=activity_logs'">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        Clear Filters
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table logs-table">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Description</th>
                                            <th>Table</th>
                                            <th>Record ID</th>
                                            <th>IP Address</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="logsTableBody">
                                        <?php foreach ($logs as $log): ?>
                                            <tr class="log-row" data-log-id="<?= $log['log_id'] ?>">
                                                <td>
                                                    <span class="log-time">
                                                        <i class="bi bi-clock"></i>
                                                        <?= date('M d, Y g:i A', strtotime($log['created_at'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="user-info">
                                                        <span class="user-name"><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></span>
                                                        <span class="user-role badge <?= $log['role'] === 'superadmin' ? 'badge-danger' : ($log['role'] === 'admin' ? 'badge-warning' : 'badge-secondary') ?>">
                                                            <?= htmlspecialchars($log['role']) ?>
                                                        </span>
                                                        <span class="user-username">@<?= htmlspecialchars($log['username']) ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="action-badge action-<?= str_replace('_', '-', $log['action']) ?>">
                                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $log['action']))) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="log-description"><?= htmlspecialchars($log['description']) ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($log['table_affected']): ?>
                                                        <span class="table-badge">
                                                            <i class="bi bi-table"></i>
                                                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $log['table_affected']))) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($log['record_id']): ?>
                                                        <span class="record-id">#<?= $log['record_id'] ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($log['ip_address']): ?>
                                                        <span class="ip-address"><?= htmlspecialchars($log['ip_address']) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info view-details-btn"
                                                        data-log-id="<?= $log['log_id'] ?>"
                                                        title="View Details">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="pagination-wrapper mt-3 pb-3 px-3">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center mb-0">
                                            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                                <a class="page-link" href="#" data-page="<?= $current_page - 1 ?>">
                                                    <i class="bi bi-chevron-left"></i>
                                                </a>
                                            </li>
                                            <?php
                                            $start_page = max(1, $current_page - 2);
                                            $end_page = min($total_pages, $current_page + 2);
                                            for ($i = $start_page; $i <= $end_page; $i++):
                                            ?>
                                                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                                    <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                                                <a class="page-link" href="#" data-page="<?= $current_page + 1 ?>">
                                                    <i class="bi bi-chevron-right"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                    <div class="pagination-info text-center mt-2 text-muted small">
                                        Showing page <?= $current_page ?> of <?= $total_pages ?> (<?= number_format($total_logs) ?> total logs)
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- clear logs modal (superadmin only) -->
    <div class="modal fade" id="clearLogsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                        Clear Old Logs
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?page=clear_old_logs">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="modal-body">
                        <p class="text-muted mb-3">This action cannot be undone. Logs older than the specified days will be permanently deleted.</p>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-calendar"></i>
                                Delete logs older than
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="days" value="90" min="1" max="365">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-trash"></i>
                            Clear Logs
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- log details modal -->
    <div class="modal fade" id="logDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle-fill"></i>
                        Log Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="logDetailsBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const activityLogConfig = {
            success: <?= !empty($success_message) ? json_encode($success_message) : 'null' ?>,
            error: <?= !empty($errors) ? json_encode($errors['general'] ?? null) : 'null' ?>,
            current_page: <?= $current_page ?? 1 ?>,
            total_pages: <?= $total_pages ?? 1 ?>
        };
    </script>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/activity-logs.js"></script>
    <script src="js/shared/top-navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');

            function toggleSidebar() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }

            if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
            if (overlay) overlay.addEventListener('click', toggleSidebar);
        });
    </script>
</body>

</html>