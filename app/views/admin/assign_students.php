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
    <link rel="stylesheet" href="css/pages/assign_students.css">
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
                    <a class="nav-link active" href="index.php?page=assign_students">
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
                            <p class="header-subtitle">Manage student section assignments</p>
                        </div>
                    </div>
                    <div class="header-action">
                        <button class="btn btn-primary btn-bulk-assign" id="btnBulkAssign" disabled>
                            <i class="bi bi-people-fill"></i>
                            Bulk Assign
                        </button>
                    </div>
                </div>

                <!-- assignment card -->
                <div class="card assignment-card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <!-- left: search & select students -->
                            <div class="col-lg-6">
                                <div class="section-label">
                                    <i class="bi bi-search"></i>
                                    Search & Select Students
                                </div>

                                <div class="search-box mb-3">
                                    <input type="text" class="form-control" id="studentSearch"
                                        placeholder="Search students by name or ID...">
                                    <i class="bi bi-search search-icon"></i>
                                </div>

                                <div class="students-list-container" id="studentsListContainer">
                                    <?php if (empty($students)): ?>
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="bi bi-inbox"></i>
                                            </div>
                                            <p class="empty-state-text">No unassigned students found</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="students-list" id="studentsList">
                                            <?php foreach ($students as $student): ?>
                                                <div class="student-item">
                                                    <input type="checkbox" class="student-checkbox"
                                                        value="<?= $student['student_id'] ?>"
                                                        id="student_<?= $student['student_id'] ?>">
                                                    <label for="student_<?= $student['student_id'] ?>">
                                                        <div class="student-info">
                                                            <div class="student-name">
                                                                <?= htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']) ?>
                                                            </div>
                                                            <div class="student-meta">
                                                                ID: <?= htmlspecialchars($student['student_number']) ?> -
                                                                <?= htmlspecialchars($student['year_level']) ?>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- pagination -->
                                        <?php if ($total_pages > 1): ?>
                                            <div class="pagination-wrapper mt-3" id="paginationWrapper">
                                                <nav aria-label="Page navigation">
                                                    <ul class="pagination justify-content-center mb-0">
                                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                                            <a class="page-link" href="#" data-page="<?= $page - 1 ?>">
                                                                <i class="bi bi-chevron-left"></i>
                                                            </a>
                                                        </li>

                                                        <?php
                                                        $start_page = max(1, $page - 2);
                                                        $end_page = min($total_pages, $page + 2);

                                                        for ($i = $start_page; $i <= $end_page; $i++):
                                                        ?>
                                                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                                <a class="page-link" href="#" data-page="<?= $i ?>">
                                                                    <?= $i ?>
                                                                </a>
                                                            </li>
                                                        <?php endfor; ?>

                                                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                                            <a class="page-link" href="#" data-page="<?= $page + 1 ?>">
                                                                <i class="bi bi-chevron-right"></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </nav>
                                                <div class="pagination-info text-center mt-2 text-muted small">
                                                    Showing page <?= $page ?> of <?= $total_pages ?> (<?= $total_students ?> total students)
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="selection-actions mt-3">
                                            <button class="btn btn-sm btn-outline-secondary" id="btnSelectAll">
                                                Select All
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" id="btnClearAll">
                                                Clear All
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- right: select section -->
                            <div class="col-lg-6">
                                <div class="section-label">
                                    <i class="bi bi-diagram-3-fill"></i>
                                    Select Section
                                </div>

                                <div class="mb-3">
                                    <select class="form-select" id="sectionSelect">
                                        <option value="">Select a section...</option>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?= $section['section_id'] ?>"
                                                data-education="<?= htmlspecialchars($section['education_level']) ?>"
                                                data-year="<?= htmlspecialchars($section['year_level']) ?>"
                                                data-strand="<?= htmlspecialchars($section['strand_course']) ?>"
                                                data-capacity="<?= $section['max_capacity'] ?>"
                                                data-current="<?= $section['current_students'] ?>"
                                                data-available="<?= $section['available_slots'] ?>">
                                                <?= htmlspecialchars($section['section_name']) ?>
                                                (<?= htmlspecialchars($section['year_level']) ?>) -
                                                <?= $section['available_slots'] ?> slots
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="section-info-card" id="sectionInfoCard" style="display: none;">
                                    <div class="info-label">Section Info</div>

                                    <div class="info-row">
                                        <span class="info-key">Section Name:</span>
                                        <span class="info-value" id="infoSectionName">-</span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-key">Education Level:</span>
                                        <span class="info-value" id="infoEducationLevel">-</span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-key">Year Level:</span>
                                        <span class="info-value" id="infoYearLevel">-</span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-key">Strand/Course:</span>
                                        <span class="info-value" id="infoStrandCourse">-</span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-key">Capacity:</span>
                                        <span class="info-value" id="infoCapacity">-</span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-key">Available Slots:</span>
                                        <span class="info-value text-success" id="infoAvailableSlots">-</span>
                                    </div>
                                </div>

                                <div class="section-info-placeholder" id="sectionInfoPlaceholder">
                                    <i class="bi bi-info-circle"></i>
                                    <p>Select a section to view details</p>
                                </div>

                                <div class="assignment-actions mt-4">
                                    <button class="btn btn-secondary" id="btnCancel">
                                        <i class="bi bi-x-circle"></i>
                                        Cancel
                                    </button>
                                    <button class="btn btn-success" id="btnAssignSelected" disabled>
                                        <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                                        <i class="bi bi-check-circle-fill"></i>
                                        Assign Selected Students
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- recent assignments table -->
                <div class="card assignments-table-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i>
                            Recent Assignments
                        </h5>
                    </div>
                    <div class="card-body" id="recentAssignmentsBody">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrfToken = '<?= $_SESSION['csrf_token'] ?>';
    </script>
    <script src="js/assign-students-ajax.js"></script>

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