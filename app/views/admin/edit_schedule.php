<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Schedule - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/schedule_form.css">
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
                            <i class="bi bi-pencil-fill"></i>
                        </div>
                        <span>Edit Schedule</span>
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
                        <li class="breadcrumb-item">
                            <a href="index.php?page=manage_schedules">
                                <i class="bi bi-calendar-week-fill"></i> Manage Schedules
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Edit Schedule (<?= htmlspecialchars($schedule['display_id']) ?>)
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-pencil-fill"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Edit Schedule</h1>
                            <p class="header-subtitle">Modify schedule information for <?= htmlspecialchars($schedule['display_id']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- form card -->
                <div class="card form-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-edit"></i>
                            Schedule Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="index.php?page=update_schedule" id="scheduleForm">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="schedule_id" value="<?= $schedule['schedule_id'] ?>">

                            <!-- conflict alert -->
                            <?php if (!empty($errors['conflict'])): ?>
                                <div class="alert alert-danger conflict-alert">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span><?= htmlspecialchars($errors['conflict']) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($errors['general'])): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-x-circle-fill"></i>
                                    <?= htmlspecialchars($errors['general']) ?>
                                </div>
                            <?php endif; ?>

                            <div class="row g-3">
                                <!-- teacher -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-person-fill"></i>
                                        Teacher
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?= !empty($errors['teacher_id']) ? 'is-invalid' : '' ?>"
                                        name="teacher_id" id="teacherId" required>
                                        <option value="">Select teacher...</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?= $teacher['id'] ?>"
                                                <?= $schedule['teacher_id'] == $teacher['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($teacher['full_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!empty($errors['teacher_id'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['teacher_id']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- subject -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-book-fill"></i>
                                        Subject
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?= !empty($errors['subject_id']) ? 'is-invalid' : '' ?>"
                                        name="subject_id" id="subjectId" required>
                                        <option value="">Select subject...</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?= $subject['subject_id'] ?>"
                                                <?= $schedule['subject_id'] == $subject['subject_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($subject['subject_code']) ?> - <?= htmlspecialchars($subject['subject_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!empty($errors['subject_id'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['subject_id']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- section -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-diagram-3-fill"></i>
                                        Section
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?= !empty($errors['section_id']) ? 'is-invalid' : '' ?>"
                                        name="section_id" id="sectionId" required>
                                        <option value="">Select section...</option>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?= $section['section_id'] ?>"
                                                <?= $schedule['section_id'] == $section['section_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($section['section_name']) ?> -
                                                <?= htmlspecialchars($section['year_level']) ?>
                                                <?= htmlspecialchars($section['strand_course']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!empty($errors['section_id'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['section_id']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- day of week -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-calendar-day"></i>
                                        Day of Week
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?= !empty($errors['day_of_week']) ? 'is-invalid' : '' ?>"
                                        name="day_of_week" id="dayOfWeek" required>
                                        <option value="">Select day...</option>
                                        <option value="Monday" <?= $schedule['day_of_week'] === 'Monday' ? 'selected' : '' ?>>Monday</option>
                                        <option value="Tuesday" <?= $schedule['day_of_week'] === 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                                        <option value="Wednesday" <?= $schedule['day_of_week'] === 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                                        <option value="Thursday" <?= $schedule['day_of_week'] === 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                                        <option value="Friday" <?= $schedule['day_of_week'] === 'Friday' ? 'selected' : '' ?>>Friday</option>
                                        <option value="Saturday" <?= $schedule['day_of_week'] === 'Saturday' ? 'selected' : '' ?>>Saturday</option>
                                        <option value="Sunday" <?= $schedule['day_of_week'] === 'Sunday' ? 'selected' : '' ?>>Sunday</option>
                                    </select>
                                    <?php if (!empty($errors['day_of_week'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['day_of_week']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- start time -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-clock-fill"></i>
                                        Start Time
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" class="form-control <?= !empty($errors['start_time']) ? 'is-invalid' : '' ?>"
                                        name="start_time" id="startTime" value="<?= htmlspecialchars($schedule['start_time']) ?>" required>
                                    <?php if (!empty($errors['start_time'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['start_time']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- end time -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-clock-fill"></i>
                                        End Time
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" class="form-control <?= !empty($errors['end_time']) ? 'is-invalid' : '' ?>"
                                        name="end_time" id="endTime" value="<?= htmlspecialchars($schedule['end_time']) ?>" required>
                                    <?php if (!empty($errors['end_time'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['end_time']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($errors['time'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['time']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- room -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-door-closed-fill"></i>
                                        Room
                                    </label>
                                    <input type="text" class="form-control <?= !empty($errors['room']) ? 'is-invalid' : '' ?>"
                                        name="room" id="room" placeholder="e.g., Room 201"
                                        value="<?= htmlspecialchars($schedule['room'] ?? '') ?>">
                                    <?php if (!empty($errors['room'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['room']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- school year -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-calendar3"></i>
                                        School Year
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?= !empty($errors['school_year']) ? 'is-invalid' : '' ?>"
                                        name="school_year" id="schoolYear" required>
                                        <option value="2025-2026" <?= $schedule['school_year'] === '2025-2026' ? 'selected' : '' ?>>2025-2026</option>
                                        <option value="2024-2025" <?= $schedule['school_year'] === '2024-2025' ? 'selected' : '' ?>>2024-2025</option>
                                    </select>
                                    <?php if (!empty($errors['school_year'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['school_year']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- semester -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-calendar2-range"></i>
                                        Semester
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?= !empty($errors['semester']) ? 'is-invalid' : '' ?>"
                                        name="semester" id="semester" required>
                                        <option value="First" <?= $schedule['semester'] === 'First' ? 'selected' : '' ?>>First Semester</option>
                                        <option value="Second" <?= $schedule['semester'] === 'Second' ? 'selected' : '' ?>>Second Semester</option>
                                    </select>
                                    <?php if (!empty($errors['semester'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['semester']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- status -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-toggle-on"></i>
                                        Status
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?= !empty($errors['status']) ? 'is-invalid' : '' ?>"
                                        name="status" id="status" required>
                                        <option value="active" <?= $schedule['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $schedule['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                    <?php if (!empty($errors['status'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['status']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- form actions -->
                            <div class="form-actions">
                                <a href="index.php?page=manage_schedules" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Update Schedule
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/schedule-form-ajax.js"></script>

</body>

</html>