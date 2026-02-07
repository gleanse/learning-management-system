<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sections - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/section_management.css">
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
                    <a class="nav-link active" href="index.php?page=manage_sections">
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
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </div>
                        <span>Manage Sections</span>
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
                            Section Management
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Manage Student Sections</h1>
                            <p class="header-subtitle">Create, edit, and manage class sections</p>
                        </div>
                    </div>
                </div>

                <!-- action bar card -->
                <div class="card action-bar-card mb-3">
                    <div class="card-body">
                        <div class="action-bar">
                            <div class="search-box">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search sections...">
                                </div>
                            </div>
                            <div class="action-buttons">
                                <a href="index.php?page=create_section" class="btn btn-primary">
                                    <i class="bi bi-plus-circle-fill"></i>
                                    Create Section
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- school year filter card -->
                <div class="card filter-card mb-3">
                    <div class="card-body">
                        <div class="filter-bar">
                            <label class="filter-label">
                                <i class="bi bi-calendar-range"></i>
                                School Year:
                            </label>
                            <select class="form-select school-year-select" id="schoolYearFilter">
                                <option value="2025-2026" selected>2025-2026</option>
                                <option value="2024-2025">2024-2025</option>
                                <option value="2023-2024">2023-2024</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- sections table card -->
                <div class="card sections-table-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-table"></i>
                            Sections List
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Section Name</th>
                                        <th>Education Level</th>
                                        <th>Year Level</th>
                                        <th>Strand/Course</th>
                                        <th>Students</th>
                                        <th>School Year</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="sectionsTableBody">
                                    <?php if (empty($sections)): ?>
                                        <tr>
                                            <td colspan="7">
                                                <div class="empty-state">
                                                    <div class="empty-state-icon">
                                                        <i class="bi bi-inbox"></i>
                                                    </div>
                                                    <p class="empty-state-text">No sections found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($sections as $section): ?>
                                            <tr data-section-id="<?= $section['section_id'] ?>">
                                                <td>
                                                    <span class="section-name"><?= htmlspecialchars($section['section_name']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="education-level-badge <?= $section['education_level'] === 'senior_high' ? 'badge-shs' : 'badge-college' ?>">
                                                        <?= $section['education_level'] === 'senior_high' ? 'Senior High' : 'College' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="year-level"><?= htmlspecialchars($section['year_level']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="strand-course"><?= htmlspecialchars($section['strand_course']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="student-count">
                                                        <i class="bi bi-people-fill"></i>
                                                        <?= $section['student_count'] ?><?= $section['max_capacity'] ? '/' . $section['max_capacity'] : '' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="school-year"><?= htmlspecialchars($section['school_year']) ?></span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="index.php?page=view_section&section_id=<?= $section['section_id'] ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye-fill"></i>
                                                            View
                                                        </a>
                                                        <a href="index.php?page=edit_section&section_id=<?= $section['section_id'] ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pencil-fill"></i>
                                                            Edit
                                                        </a>
                                                        <button class="btn btn-sm btn-outline-danger delete-section-btn"
                                                                data-section-id="<?= $section['section_id'] ?>"
                                                                data-section-name="<?= htmlspecialchars($section['section_name']) ?>">
                                                            <i class="bi bi-trash-fill"></i>
                                                            Delete
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- delete section modal -->
    <div class="modal fade" id="deleteSectionModal" tabindex="-1" aria-labelledby="deleteSectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteSectionModalLabel">
                        <i class="bi bi-trash-fill"></i>
                        Delete Section
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteSectionForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="section_id" id="deleteSectionId">

                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>
                                <strong>Warning!</strong> This action cannot be undone.
                            </div>
                        </div>

                        <div class="section-info">
                            <p>Are you sure you want to delete this section?</p>
                            <p><strong>Section:</strong> <span id="deleteSectionName"></span></p>
                            <p class="mb-0 text-muted">Note: Sections with enrolled students cannot be deleted.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                            <i class="bi bi-trash-fill"></i>
                            Delete Section
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrfToken = '<?= $_SESSION['csrf_token'] ?>';
    </script>
    <script src="js/section-management-ajax.js"></script>

</body>

</html>