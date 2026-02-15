<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedules - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/manage_schedules.css">
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
                    <a class="nav-link" href="index.php?page=student_sections">
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
                    <a class="nav-link active" href="index.php?page=manage_schedules">
                        <i class="bi bi-calendar-week-fill"></i>
                        <span>Manage Schedules</span>
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
                            <i class="bi bi-calendar-week-fill"></i>
                        </div>
                        <span>Manage Schedules</span>
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
                            Manage Schedules
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-calendar-week-fill"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Teacher Schedule Management</h1>
                            <p class="header-subtitle">Create and manage teacher class schedules with conflict detection</p>
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="index.php?page=create_schedule" class="btn btn-primary">
                            <i class="bi bi-plus-circle-fill"></i>
                            Create New Schedule
                        </a>
                    </div>
                </div>

                <!-- filters card -->
                <div class="card filters-card mb-4">
                    <div class="card-body">
                        <form method="GET" action="index.php" id="filtersForm">
                            <input type="hidden" name="page" value="manage_schedules">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="bi bi-calendar3"></i>
                                        School Year
                                    </label>
                                    <select class="form-select" name="school_year" id="filterSchoolYear">
                                        <option value="2025-2026" <?= ($school_year ?? '2025-2026') === '2025-2026' ? 'selected' : '' ?>>2025-2026</option>
                                        <option value="2024-2025" <?= ($school_year ?? '2025-2026') === '2024-2025' ? 'selected' : '' ?>>2024-2025</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="bi bi-calendar2-range"></i>
                                        Semester
                                    </label>
                                    <select class="form-select" name="semester" id="filterSemester">
                                        <option value="First" <?= ($semester ?? 'First') === 'First' ? 'selected' : '' ?>>First Semester</option>
                                        <option value="Second" <?= ($semester ?? 'First') === 'Second' ? 'selected' : '' ?>>Second Semester</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="bi bi-funnel-fill"></i>
                                        Status
                                    </label>
                                    <select class="form-select" name="status" id="filterStatus">
                                        <option value="active" <?= ($status ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= ($status ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- schedules accordion card -->
                <div class="card schedules-accordion-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-people-fill"></i>
                            Teacher Schedules
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($schedules)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-calendar-x"></i>
                                </div>
                                <p class="empty-state-text">No schedules found for selected filters</p>
                            </div>
                        <?php else:
                            // group schedules by teacher
                            $grouped_schedules = [];
                            foreach ($schedules as $schedule) {
                                $teacher_id = $schedule['teacher_id'];
                                if (!isset($grouped_schedules[$teacher_id])) {
                                    $grouped_schedules[$teacher_id] = [
                                        'teacher_name' => $schedule['teacher_name'],
                                        'schedules' => []
                                    ];
                                }
                                $grouped_schedules[$teacher_id]['schedules'][] = $schedule;
                            }
                        ?>
                            <div class="accordion" id="teacherSchedulesAccordion">
                                <?php foreach ($grouped_schedules as $teacher_id => $teacher_data): ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading<?= $teacher_id ?>">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse<?= $teacher_id ?>" aria-expanded="false"
                                                aria-controls="collapse<?= $teacher_id ?>">
                                                <div class="teacher-header-info">
                                                    <span class="teacher-name">
                                                        <i class="bi bi-person-fill"></i>
                                                        <?= htmlspecialchars($teacher_data['teacher_name']) ?>
                                                    </span>
                                                    <span class="badge bg-primary ms-2">
                                                        <?= count($teacher_data['schedules']) ?> schedules
                                                    </span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse<?= $teacher_id ?>" class="accordion-collapse collapse"
                                            aria-labelledby="heading<?= $teacher_id ?>" data-bs-parent="#teacherSchedulesAccordion">
                                            <div class="accordion-body">
                                                <div class="table-responsive">
                                                    <table class="table table-hover schedule-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Schedule ID</th>
                                                                <th>Day</th>
                                                                <th>Time</th>
                                                                <th>Subject</th>
                                                                <th>Section</th>
                                                                <th>Room</th>
                                                                <th>Status</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($teacher_data['schedules'] as $schedule): ?>
                                                                <tr>
                                                                    <td>
                                                                        <span class="schedule-id-badge">
                                                                            <?= htmlspecialchars($schedule['display_id']) ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="day-badge day-<?= strtolower($schedule['day_of_week']) ?>">
                                                                            <?= htmlspecialchars($schedule['day_of_week']) ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="time-display">
                                                                            <i class="bi bi-clock"></i>
                                                                            <?= date('g:i A', strtotime($schedule['start_time'])) ?> -
                                                                            <?= date('g:i A', strtotime($schedule['end_time'])) ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="subject-info">
                                                                            <span class="subject-code"><?= htmlspecialchars($schedule['subject_code']) ?></span>
                                                                            <span class="subject-name"><?= htmlspecialchars($schedule['subject_name']) ?></span>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-primary">
                                                                            <?= htmlspecialchars($schedule['section_name']) ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <?php if (!empty($schedule['room'])): ?>
                                                                            <span class="room-badge">
                                                                                <i class="bi bi-door-closed"></i>
                                                                                <?= htmlspecialchars($schedule['room']) ?>
                                                                            </span>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">-</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <div class="form-check form-switch">
                                                                            <input class="form-check-input status-toggle" type="checkbox"
                                                                                data-schedule-id="<?= $schedule['schedule_id'] ?>"
                                                                                <?= $schedule['status'] === 'active' ? 'checked' : '' ?>>
                                                                            <label class="form-check-label status-label">
                                                                                <?= $schedule['status'] === 'active' ? 'Active' : 'Inactive' ?>
                                                                            </label>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <div class="action-buttons">
                                                                            <a href="index.php?page=edit_schedule&id=<?= $schedule['schedule_id'] ?>"
                                                                                class="btn btn-sm btn-outline-primary">
                                                                                <i class="bi bi-pencil-fill"></i>
                                                                            </a>
                                                                            <button class="btn btn-sm btn-outline-danger delete-schedule-btn"
                                                                                data-schedule-id="<?= $schedule['schedule_id'] ?>">
                                                                                <i class="bi bi-trash-fill"></i>
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- delete confirmation modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this schedule? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash-fill"></i>
                        Delete Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrfToken = '<?= $_SESSION['csrf_token'] ?>';
    </script>
    <script src="js/manage-schedules-ajax.js"></script>

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