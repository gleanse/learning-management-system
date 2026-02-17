<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Schedules - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/teacher_schedules.css">
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
                    <a class="nav-link active" href="index.php?page=teacher_schedules">
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
                            $lastname  = $_SESSION['user_lastname'] ?? 'U';
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
                            <p class="header-subtitle">Select a teacher to view and manage their class schedules by assignment</p>
                        </div>
                    </div>
                </div>

                <!-- filter card -->
                <div class="card filters-card mb-4">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="bi bi-person-fill"></i>
                                    Teacher
                                </label>
                                <select class="form-select" id="filterTeacher">
                                    <option value="">Select a teacher...</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?= $teacher['id'] ?>">
                                            <?= htmlspecialchars($teacher['full_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="bi bi-calendar3"></i>
                                    School Year
                                </label>
                                <select class="form-select" id="filterSchoolYear">
                                    <?php
                                    $current_year = (int) date('Y');
                                    for ($y = $current_year; $y >= $current_year - 3; $y--):
                                        $year_value = $y . '-' . ($y + 1);
                                    ?>
                                        <option value="<?= $year_value ?>" <?= $y === $current_year ? 'selected' : '' ?>>
                                            <?= $year_value ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="bi bi-calendar2-range"></i>
                                    Semester
                                </label>
                                <select class="form-select" id="filterSemester">
                                    <option value="First">First Semester</option>
                                    <option value="Second">Second Semester</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" id="loadAssignmentsBtn">
                                    <i class="bi bi-search"></i>
                                    Load
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- assignments panel - fully js driven, no php conditions needed -->
                <div id="assignmentsPanel">
                    <div class="select-prompt">
                        <div class="select-prompt-icon">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <p class="select-prompt-title">Select a teacher to get started</p>
                        <p class="select-prompt-text">Choose a teacher and filter by school year and semester to view their assignments and manage schedules.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- add schedule modal -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-plus-fill"></i>
                        Add Schedule
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="addScheduleError" class="alert alert-danger d-none"></div>
                    <form id="addScheduleForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="return_page" value="teacher_schedules">
                        <input type="hidden" name="teacher_id" id="addTeacherId">
                        <input type="hidden" name="subject_id" id="addSubjectId">
                        <input type="hidden" name="section_id" id="addSectionId">
                        <input type="hidden" name="school_year" id="addSchoolYear">
                        <input type="hidden" name="semester" id="addSemester">
                        <input type="hidden" name="status" value="active">

                        <!-- context info -->
                        <div class="assignment-context mb-3" id="addAssignmentContext"></div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="bi bi-calendar-day"></i>
                                    Day of Week
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="day_of_week" id="addDayOfWeek" required>
                                    <option value="">Select day...</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                    <option value="Sunday">Sunday</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">
                                    <i class="bi bi-clock-fill"></i>
                                    Start Time
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="time" class="form-control" name="start_time" id="addStartTime" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">
                                    <i class="bi bi-clock-fill"></i>
                                    End Time
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="time" class="form-control" name="end_time" id="addEndTime" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="bi bi-door-closed-fill"></i>
                                    Room
                                    <span class="text-muted fw-normal">(optional)</span>
                                </label>
                                <input type="text" class="form-control" name="room" id="addRoom" placeholder="e.g., Room 201">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmAddScheduleBtn">
                        <i class="bi bi-check-circle-fill"></i>
                        Add Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- edit schedule modal -->
    <div class="modal fade" id="editScheduleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-fill"></i>
                        Edit Schedule
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="editScheduleError" class="alert alert-danger d-none"></div>
                    <form id="editScheduleForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="return_page" value="teacher_schedules">
                        <input type="hidden" name="schedule_id" id="editScheduleId">
                        <input type="hidden" name="teacher_id" id="editTeacherId">
                        <input type="hidden" name="subject_id" id="editSubjectId">
                        <input type="hidden" name="section_id" id="editSectionId">
                        <input type="hidden" name="school_year" id="editSchoolYear">
                        <input type="hidden" name="semester" id="editSemester">

                        <!-- context info -->
                        <div class="assignment-context mb-3" id="editAssignmentContext"></div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="bi bi-calendar-day"></i>
                                    Day of Week
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="day_of_week" id="editDayOfWeek" required>
                                    <option value="">Select day...</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                    <option value="Sunday">Sunday</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">
                                    <i class="bi bi-clock-fill"></i>
                                    Start Time
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="time" class="form-control" name="start_time" id="editStartTime" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">
                                    <i class="bi bi-clock-fill"></i>
                                    End Time
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="time" class="form-control" name="end_time" id="editEndTime" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="bi bi-door-closed-fill"></i>
                                    Room
                                    <span class="text-muted fw-normal">(optional)</span>
                                </label>
                                <input type="text" class="form-control" name="room" id="editRoom" placeholder="e.g., Room 201">
                            </div>
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="bi bi-toggle-on"></i>
                                    Status
                                </label>
                                <select class="form-select" name="status" id="editStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmEditScheduleBtn">
                        <i class="bi bi-check-circle-fill"></i>
                        Update Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- delete confirmation modal -->
    <div class="modal fade" id="deleteScheduleModal" tabindex="-1">
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
                    <p class="mb-0">Are you sure you want to delete this schedule entry? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteScheduleBtn">
                        <i class="bi bi-trash-fill"></i>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrfToken = '<?= $_SESSION['csrf_token'] ?>';
    </script>
    <script src="js/teacher-schedules-ajax.js"></script>

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