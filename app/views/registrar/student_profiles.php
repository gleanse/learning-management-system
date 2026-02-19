<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profiles - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/student_profiles.css">
</head>

<body>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;" id="toastContainer"></div>

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
                    <a class="nav-link" href="index.php?page=registrar_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=enrollment_create">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Enroll Student</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=payment_process">
                        <i class="bi bi-cash-stack"></i>
                        <span>Process Payment</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=student_profiles">
                        <i class="bi bi-people-fill"></i>
                        <span>Student Profiles</span>
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
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <span>Student Profiles Management</span>
                    </div>
                    <div class="user-info-wrapper">
                        <div class="user-details">
                            <span class="user-name">
                                <?= htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']) ?>
                            </span>
                            <span class="user-role">
                                <i class="bi bi-person-badge-fill"></i>
                                <?= ucfirst(htmlspecialchars($_SESSION['user_role'])) ?>
                            </span>
                        </div>
                        <div class="user-avatar">
                            <?php
                            $fn = $_SESSION['user_firstname'] ?? 'R';
                            $ln = $_SESSION['user_lastname']  ?? 'G';
                            echo strtoupper(substr($fn, 0, 1) . substr($ln, 0, 1));
                            ?>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- page content -->
            <div class="container-fluid p-4">
                <!-- breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="bi bi-people-fill"></i> Student Profiles
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Student Profiles</h1>
                            <p class="header-subtitle">
                                <?= htmlspecialchars($semester) ?> Semester &mdash; S.Y. <?= htmlspecialchars($school_year) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <!-- table card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul"></i>
                            Student List
                        </h5>
                        <div class="d-flex gap-2 align-items-center">
                            <!-- search -->
                            <div class="input-group" style="width: 280px;">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Search by name or student ID..."
                                    value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <!-- export -->
                            <a href="index.php?page=export_student_profiles" class="btn btn-outline-success">
                                <i class="bi bi-download"></i> Export
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="profilesTable">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Grade</th>
                                        <th>Current Balance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="profilesTableBody">
                                    <?php if (empty($students)): ?>
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state py-4">
                                                    <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                                                    <p class="empty-state-text">No students found.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($students as $s):
                                            $full_name   = htmlspecialchars($s['last_name'] . ', ' . $s['first_name'] . ($s['middle_name'] ? ' ' . $s['middle_name'] : ''));
                                            $grade_label = htmlspecialchars($s['year_level'] . ' - ' . $s['strand_course']);
                                            $remaining   = $s['remaining'] ?? null;
                                            $pay_status  = $s['payment_status'] ?? null;

                                            $status_badge = match ($pay_status) {
                                                'paid'    => '<span class="badge bg-success">Paid</span>',
                                                'partial' => '<span class="badge bg-warning text-dark">Partial</span>',
                                                'pending' => '<span class="badge bg-danger">Pending</span>',
                                                default   => '<span class="badge bg-secondary">No Record</span>',
                                            };
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($s['student_number']) ?></td>
                                                <td><?= $full_name ?></td>
                                                <td><?= $grade_label ?></td>
                                                <td><?= $remaining !== null ? '₱' . number_format($remaining, 2) : '—' ?></td>
                                                <td><?= $status_badge ?></td>
                                                <td>
                                                    <a href="index.php?page=edit_student_profile&student_id=<?= $s['student_id'] ?>"
                                                        class="btn btn-sm btn-outline-primary me-1">
                                                        <i class="bi bi-pencil-fill"></i> Edit
                                                    </a>
                                                    <a href="index.php?page=view_student_profile&student_id=<?= $s['student_id'] ?>"
                                                        class="btn btn-sm btn-outline-secondary me-1">
                                                        <i class="bi bi-eye-fill"></i> View
                                                    </a>
                                                    <a href="index.php?page=payment_process&student_id=<?= $s['student_id'] ?>"
                                                        class="btn btn-sm btn-outline-warning">
                                                        <i class="bi bi-cash-coin"></i> Payment
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-wrapper p-3" id="paginationWrapper">
                                <nav>
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="#" data-page="<?= $page - 1 ?>">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                        <?php
                                        $start_page = max(1, $page - 2);
                                        $end_page   = min($total_pages, $page + 2);
                                        for ($i = $start_page; $i <= $end_page; $i++):
                                        ?>
                                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="#" data-page="<?= $page + 1 ?>">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                                <div class="text-center mt-2 text-muted small">
                                    Showing page <?= $page ?> of <?= $total_pages ?> (<?= $total ?> total students)
                                </div>
                            </div>
                        <?php else: ?>
                            <div id="paginationWrapper"></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const profileConfig = {
            success: <?= json_encode($_SESSION['profile_success'] ?? null) ?>,
            error: <?= json_encode($errors['general'] ?? null) ?>
        };
    </script>
    <script src="js/student-profiles-ajax.js"></script>
</body>

</html>