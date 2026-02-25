<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/shared/top-navbar.css">
    <link rel="stylesheet" href="css/pages/announcements.css">
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
                        <i class="bi bi-house-door-fill"></i><span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=manage_sections">
                        <i class="bi bi-grid-3x3-gap-fill"></i><span>Section Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=subjects">
                        <i class="bi bi-book-fill"></i><span>Subject Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=student_sections">
                        <i class="bi bi-people-fill"></i><span>Assign Students</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=teacher_assignments">
                        <i class="bi bi-person-plus-fill"></i><span>Teacher Assignments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=teacher_schedules">
                        <i class="bi bi-calendar-week-fill"></i><span>Manage Schedules</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=academic_period">
                        <i class="bi bi-calendar2-range-fill"></i><span>Academic Period</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=fee_config">
                        <i class="bi bi-cash-coin"></i><span>Fee Configuration</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=reports">
                        <i class="bi bi-graph-up"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=announcements">
                        <i class="bi bi-megaphone-fill"></i><span>Announcements</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=admin_activity_logs">
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

            <div class="container-fluid p-4">

                <!-- breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?page=admin_dashboard">
                                <i class="bi bi-house-door-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Announcements</li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header mb-4">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-megaphone-fill"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Announcements</h1>
                            <p class="header-subtitle">create and publish announcements to staff and students</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- left: create form -->
                    <div class="col-lg-5 mb-4 mb-lg-0">
                        <div class="card announcement-form-card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">
                                    <i class="bi bi-plus-circle-fill"></i>
                                    New Announcement
                                </h5>
                                <button type="button" class="btn btn-sm btn-light" id="clearFormBtn">
                                    <i class="bi bi-arrow-counterclockwise"></i> Clear
                                </button>
                            </div>
                            <div class="card-body">
                                <input type="hidden" id="editAnnouncementId" value="">
                                <input type="hidden" id="editAnnouncementStatus" value="">

                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-type"></i>
                                        Title <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="announcementTitle"
                                        placeholder="Enter announcement title" maxlength="255">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-text-paragraph"></i>
                                        Content <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="announcementContent" rows="5"
                                        placeholder="Enter announcement details..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-people-fill"></i>
                                        Target Audience <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="targetType">
                                        <option value="all">Everyone</option>
                                        <option value="role" data-value="teacher">All Teachers</option>
                                        <option value="role" data-value="registrar">All Registrars</option>
                                        <option value="role" data-value="student">All Students</option>
                                        <option value="role" data-value="admin">All Admins</option>
                                        <option value="student_education_level" data-value="senior_high">Senior High Students</option>
                                        <option value="student_education_level" data-value="college">College Students</option>
                                        <?php foreach ($year_levels as $yl): ?>
                                            <option value="student_year_level" data-value="<?= htmlspecialchars($yl) ?>">
                                                <?= htmlspecialchars($yl) ?> Students
                                            </option>
                                        <?php endforeach; ?>
                                        <?php foreach ($strand_courses as $sc): ?>
                                            <option value="student_strand_course" data-value="<?= htmlspecialchars($sc) ?>">
                                                <?= htmlspecialchars($sc) ?> Students
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="button" class="btn btn-outline-secondary flex-fill" id="saveDraftBtn">
                                        <i class="bi bi-floppy-fill"></i>
                                        Save Draft
                                    </button>
                                    <button type="button" class="btn btn-primary flex-fill" id="publishBtn">
                                        <i class="bi bi-send-fill"></i>
                                        Publish
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- right: announcements list -->
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">
                                    <i class="bi bi-list-ul"></i>
                                    My Announcements
                                </h5>
                                <div class="d-flex gap-2">
                                    <select class="form-select form-select-sm" id="statusFilter" style="width: auto;">
                                        <option value="">All</option>
                                        <option value="published" <?= ($_GET['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                                        <option value="draft" <?= ($_GET['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Drafts</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($announcements)): ?>
                                    <div class="empty-state py-5">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-megaphone"></i>
                                        </div>
                                        <p class="empty-state-text">no announcements yet</p>
                                    </div>
                                <?php else: ?>
                                    <div class="announcement-list" id="announcementList">
                                        <?php foreach ($announcements as $ann): ?>
                                            <div class="announcement-item" data-id="<?= $ann['announcement_id'] ?>">
                                                <div class="announcement-item-header">
                                                    <span class="announcement-item-title">
                                                        <?= htmlspecialchars($ann['title']) ?>
                                                    </span>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <?php if ($ann['status'] === 'published'): ?>
                                                            <span class="ann-badge ann-badge-published">
                                                                <i class="bi bi-broadcast"></i> published
                                                            </span>
                                                            <button type="button"
                                                                class="btn btn-sm btn-link text-primary p-0 edit-ann-btn"
                                                                data-id="<?= $ann['announcement_id'] ?>">
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-sm btn-link text-danger p-0 delete-published-btn"
                                                                data-id="<?= $ann['announcement_id'] ?>">
                                                                <i class="bi bi-trash-fill"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="ann-badge ann-badge-draft">
                                                                <i class="bi bi-pencil"></i> draft
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if ($ann['status'] === 'draft'): ?>
                                                            <button type="button"
                                                                class="btn btn-sm btn-link text-primary p-0 edit-ann-btn"
                                                                data-id="<?= $ann['announcement_id'] ?>">
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-sm btn-link text-danger p-0 delete-draft-btn"
                                                                data-id="<?= $ann['announcement_id'] ?>">
                                                                <i class="bi bi-trash-fill"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="announcement-item-meta">
                                                    <span>
                                                        <i class="bi bi-people-fill"></i>
                                                        <?= htmlspecialchars($ann['target_type'] === 'all' ? 'Everyone' : ($ann['target_value'] ?? $ann['target_type'])) ?>
                                                    </span>
                                                    <?php if ($ann['status'] === 'published'): ?>
                                                        <span>
                                                            <i class="bi bi-person-check-fill"></i>
                                                            <?= $ann['read_count'] ?> / <?= $ann['recipient_count'] ?> read
                                                        </span>
                                                        <span>
                                                            <i class="bi bi-clock"></i>
                                                            <?= date('M d, Y h:i A', strtotime($ann['published_at'])) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span>
                                                            <i class="bi bi-clock"></i>
                                                            saved <?= date('M d, Y h:i A', strtotime($ann['created_at'])) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- pagination -->
                                    <?php if ($total_pages > 1): ?>
                                        <div class="d-flex justify-content-center py-3 border-top">
                                            <nav>
                                                <ul class="pagination pagination-sm mb-0">
                                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                                            <a class="page-link"
                                                                href="index.php?page=announcements&p=<?= $i ?><?= isset($_GET['status']) ? '&status=' . $_GET['status'] : '' ?>">
                                                                <?= $i ?>
                                                            </a>
                                                        </li>
                                                    <?php endfor; ?>
                                                </ul>
                                            </nav>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- publish confirmation modal -->
    <div class="modal fade" id="publishConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-send-fill"></i>
                        Confirm Publish
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">You are about to publish this announcement to:</p>
                    <p class="fw-bold fs-5 text-primary mb-3" id="publishTargetLabel"></p>
                    <p class="mb-0 text-muted" id="publishConfirmNote" style="font-size: 0.875rem;">
                        All matched users will receive a bell notification.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmPublishBtn">
                        <i class="bi bi-send-fill"></i> Confirm Publish
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- delete draft confirmation modal -->
    <div class="modal fade" id="deleteDraftModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-trash-fill"></i>
                        Delete Draft
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0 text-muted" style="font-size: 0.875rem;">
                        Are you sure you want to delete this draft? This cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteDraftBtn">
                        <i class="bi bi-trash-fill"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- delete published confirmation modal -->
    <div class="modal fade" id="deletePublishedModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-trash-fill"></i>
                        Delete Announcement
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0 text-muted" style="font-size: 0.875rem;">
                        Are you sure you want to permanently delete this announcement? All recipient records will also be removed. This cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeletePublishedBtn">
                        <i class="bi bi-trash-fill"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/shared/top-navbar.js"></script>
    <script src="js/announcements.js"></script>
</body>

</html>