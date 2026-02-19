<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Students - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/student_sections.css">
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
                    <a class="nav-link" href="index.php?page=admin_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=manage_sections">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                        <span>Section Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=subjects">
                        <i class="bi bi-book-fill"></i>
                        <span>Subject Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=student_sections">
                        <i class="bi bi-people-fill"></i>
                        <span>Assign Students</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=teacher_assignments">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Teacher Assignments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=teacher_schedules">
                        <i class="bi bi-calendar-week-fill"></i>
                        <span>Manage Schedules</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=academic_period">
                        <i class="bi bi-calendar2-range-fill"></i>
                        <span>Academic Period</span>
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
                        <span>Assign Students</span>
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
                            <a href="index.php?page=admin_dashboard">
                                <i class="bi bi-house-door-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Assign Students
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
                            <h1 class="header-title">Assign Students to Sections</h1>
                            <p class="header-subtitle">manage student section assignments with smart filtering</p>
                        </div>
                    </div>
                </div>

                <!-- assignment interface card -->
                <div class="card assignment-interface-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-ui-checks-grid"></i>
                            Assignment Interface
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- section selector column -->
                            <div class="col-lg-4 mb-3 mb-lg-0">
                                <div class="section-selector-wrapper">
                                    <label class="form-label">
                                        <i class="bi bi-diagram-3-fill"></i>
                                        Select Section
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="sectionSelector">
                                        <option value="">choose a section...</option>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?= $section['section_id'] ?>"
                                                data-name="<?= htmlspecialchars($section['section_name']) ?>"
                                                data-level="<?= htmlspecialchars($section['education_level']) ?>"
                                                data-year="<?= htmlspecialchars($section['year_level']) ?>"
                                                data-strand="<?= htmlspecialchars($section['strand_course']) ?>"
                                                data-capacity="<?= $section['max_capacity'] ?>"
                                                data-count="<?= $section['student_count'] ?>">
                                                <?= htmlspecialchars($section['section_name']) ?> -
                                                <?= htmlspecialchars($section['year_level']) ?>
                                                <?= htmlspecialchars($section['strand_course']) ?>
                                                (<?= $section['student_count'] ?>/<?= $section['max_capacity'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <!-- section info display -->
                                    <div class="section-info-card mt-3 d-none" id="sectionInfoCard">
                                        <h6 class="section-info-title">
                                            <i class="bi bi-info-circle-fill"></i>
                                            Section Information
                                        </h6>
                                        <div class="section-info-grid">
                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="bi bi-diagram-3"></i>
                                                    Section:
                                                </span>
                                                <span class="info-value" id="infoSectionName">-</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="bi bi-mortarboard"></i>
                                                    Education:
                                                </span>
                                                <span class="info-value" id="infoEducationLevel">-</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="bi bi-bar-chart-steps"></i>
                                                    Year Level:
                                                </span>
                                                <span class="info-value" id="infoYearLevel">-</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="bi bi-award"></i>
                                                    Strand/Course:
                                                </span>
                                                <span class="info-value" id="infoStrandCourse">-</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="bi bi-people"></i>
                                                    Capacity:
                                                </span>
                                                <span class="info-value" id="infoCapacity">-</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="bi bi-door-open"></i>
                                                    Available:
                                                </span>
                                                <span class="info-value text-success fw-bold" id="infoAvailable">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- students panels column -->
                            <div class="col-lg-8">
                                <div class="students-panels-wrapper" id="studentsPanelsWrapper">
                                    <!-- initial state message -->
                                    <div class="initial-state" id="initialState">
                                        <div class="initial-state-icon">
                                            <i class="bi bi-hand-index-thumb"></i>
                                        </div>
                                        <p class="initial-state-text">select a section to begin assignment</p>
                                    </div>

                                    <!-- loaded state: eligible and current students -->
                                    <div class="loaded-state d-none" id="loadedState">
                                        <!-- eligible students panel -->
                                        <div class="students-panel mb-3">
                                            <div class="panel-header">
                                                <h6 class="panel-title">
                                                    <i class="bi bi-person-plus-fill"></i>
                                                    Eligible Students
                                                    <span class="badge bg-primary ms-2" id="eligibleCount">0</span>
                                                </h6>
                                                <div class="panel-actions">
                                                    <div class="search-wrapper">
                                                        <i class="bi bi-search"></i>
                                                        <input type="text" class="form-control" id="eligibleSearch" placeholder="search students...">
                                                    </div>
                                                    <button class="btn btn-sm btn-outline-primary" id="selectAllEligible">
                                                        <i class="bi bi-check-all"></i>
                                                        select all
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-secondary" id="clearAllEligible">
                                                        <i class="bi bi-x-lg"></i>
                                                        clear all
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="panel-body">
                                                <div class="students-list" id="eligibleStudentsList">
                                                    <!-- dynamically populated -->
                                                </div>
                                            </div>
                                            <div class="panel-footer">
                                                <button class="btn btn-primary btn-assign" id="assignSelectedBtn" disabled>
                                                    <i class="bi bi-check-circle-fill"></i>
                                                    assign selected students
                                                    <span class="badge bg-white text-primary ms-2" id="selectedCount">0</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- current students panel -->
                                        <div class="students-panel">
                                            <div class="panel-header">
                                                <h6 class="panel-title">
                                                    <i class="bi bi-people-fill"></i>
                                                    Current Students
                                                    <span class="badge bg-secondary ms-2" id="currentCount">0</span>
                                                </h6>
                                                <div class="panel-actions">
                                                    <div class="search-wrapper">
                                                        <i class="bi bi-search"></i>
                                                        <input type="text" class="form-control" id="currentSearch" placeholder="search students...">
                                                    </div>
                                                    <button class="btn btn-sm btn-outline-danger d-none" id="bulkRemoveBtn">
                                                        <i class="bi bi-trash"></i>
                                                        remove selected
                                                        <span class="badge bg-white text-danger ms-1" id="removeCount">0</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="panel-body">
                                                <div class="students-list" id="currentStudentsList">
                                                    <!-- dynamically populated -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- recent assignments table -->
                <div class="card recent-assignments-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i>
                            Recent Assignments
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_assignments)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">no recent assignments yet</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="recentAssignmentsTable">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-person-fill"></i> Student</th>
                                            <th><i class="bi bi-hash"></i> Student ID</th>
                                            <th><i class="bi bi-mortarboard-fill"></i> Year Level</th>
                                            <th><i class="bi bi-diagram-3-fill"></i> Section</th>
                                            <th><i class="bi bi-award"></i> Strand/Course</th>
                                            <th><i class="bi bi-calendar-event"></i> Assigned Date</th>
                                            <th><i class="bi bi-person-badge"></i> Assigned By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_assignments as $assignment): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($assignment['student_first_name'] . ' ' . $assignment['student_last_name']) ?></td>
                                                <td><span class="text-muted"><?= htmlspecialchars($assignment['student_number']) ?></span></td>
                                                <td><?= htmlspecialchars($assignment['year_level']) ?></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?= htmlspecialchars($assignment['section_name']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($assignment['strand_course']) ?></td>
                                                <td>
                                                    <span class="text-muted">
                                                        <i class="bi bi-clock"></i>
                                                        <?= date('M d, Y h:i A', strtotime($assignment['assigned_at'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="admin-badge">
                                                        <i class="bi bi-person-badge-fill"></i>
                                                        <?= htmlspecialchars($assignment['admin_username']) ?>
                                                    </span>
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
        </div>
    </div>

    <!-- remove confirmation modal -->
    <div class="modal fade" id="removeConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        confirm removal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to remove <strong id="removeStudentName"></strong> from this section?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmRemoveBtn">
                        <i class="bi bi-trash-fill"></i>
                        remove student
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- bulk remove confirmation modal -->
    <div class="modal fade" id="bulkRemoveConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        confirm bulk removal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to remove <strong id="bulkRemoveCount"></strong> student(s) from this section?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmBulkRemoveBtn">
                        <i class="bi bi-trash-fill"></i>
                        remove students
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrfToken = '<?= $_SESSION['csrf_token'] ?>';
    </script>
    <script src="js/student-sections-ajax.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($success_message)): ?>
                showAlert('success', '<?= addslashes($success_message) ?>');
            <?php endif; ?>

            <?php if (!empty($errors['general'])): ?>
                showAlert('danger', '<?= addslashes($errors['general']) ?>');
            <?php endif; ?>
        });
    </script>

</body>

</html>