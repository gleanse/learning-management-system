<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Management - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/subject_management.css">
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
                    <a class="nav-link active" href="index.php?page=subjects">
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
                            <i class="bi bi-book-fill"></i>
                        </div>
                        <span>Subject Management</span>
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
                            Subject Management
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-book-fill"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Subject Management</h1>
                            <p class="header-subtitle">Create, view, edit, and manage all subjects</p>
                        </div>
                    </div>
                </div>

                <!-- search and action bar -->
                <div class="card action-bar-card mb-4">
                    <div class="card-body">
                        <div class="action-bar">
                            <div class="search-box">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text"
                                        class="form-control"
                                        id="searchInput"
                                        placeholder="Search by subject code or name..."
                                        value="<?= htmlspecialchars($search ?? '') ?>">
                                </div>
                            </div>
                            <div class="action-buttons">
                                <a href="index.php?page=create_subject" class="btn btn-primary">
                                    <i class="bi bi-plus-circle-fill"></i>
                                    Add New Subject
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- subjects table -->
                <div class="card subjects-table-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul"></i>
                            All Subjects
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($subjects)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">No subjects found</p>
                                <?php if (!empty($search)): ?>
                                    <button class="btn btn-outline-primary mt-3" onclick="window.location.href='index.php?page=subjects'">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        Clear Search
                                    </button>
                                <?php else: ?>
                                    <a href="index.php?page=create_subject" class="btn btn-primary mt-3">
                                        <i class="bi bi-plus-circle-fill"></i>
                                        Add Your First Subject
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="subjectsTable">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-hash"></i> Subject Code</th>
                                            <th><i class="bi bi-book"></i> Subject Name</th>
                                            <th><i class="bi bi-text-left"></i> Description</th>
                                            <th><i class="bi bi-gear-fill"></i> Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subjects as $subject): ?>
                                            <tr>
                                                <td>
                                                    <span class="subject-code"><?= htmlspecialchars($subject['subject_code']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="subject-name"><?= htmlspecialchars($subject['subject_name']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="subject-description">
                                                        <?= htmlspecialchars($subject['description'] ?? 'No description') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="index.php?page=edit_subject&id=<?= $subject['subject_id'] ?>"
                                                        class="btn btn-sm btn-outline-primary me-1">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger btn-delete"
                                                        data-subject-id="<?= $subject['subject_id'] ?>"
                                                        data-subject-code="<?= htmlspecialchars($subject['subject_code']) ?>"
                                                        data-subject-name="<?= htmlspecialchars($subject['subject_name']) ?>">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="pagination-wrapper">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center mb-0">
                                            <!-- previous button -->
                                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                                <a class="page-link" href="#" data-page="<?= $page - 1 ?>">
                                                    <i class="bi bi-chevron-left"></i>
                                                </a>
                                            </li>

                                            <!-- page numbers -->
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

                                            <!-- next button -->
                                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                                <a class="page-link" href="#" data-page="<?= $page + 1 ?>">
                                                    <i class="bi bi-chevron-right"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                    <div class="pagination-info">
                                        Showing page <?= $page ?> of <?= $total_pages ?> (<?= $total_subjects ?> total subjects)
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- delete confirmation modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="subject_id" id="delete_subject_id">

                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle"></i>
                            <div>
                                <strong>Warning:</strong> This action cannot be undone.
                            </div>
                        </div>

                        <p class="mb-0">Are you sure you want to delete this subject?</p>
                        <div class="subject-info mt-3">
                            <strong>Subject Code:</strong> <span id="delete_subject_code"></span><br>
                            <strong>Subject Name:</strong> <span id="delete_subject_name"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                            <i class="bi bi-trash"></i>
                            Delete Subject
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
    <script src="js/subject-management-ajax.js"></script>

</body>

</html>