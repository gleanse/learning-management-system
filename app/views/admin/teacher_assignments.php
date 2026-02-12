<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Assignments - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/teacher_assignments.css">
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
                    <a class="nav-link" href="index.php?page=assign_students">
                        <i class="bi bi-people-fill"></i>
                        <span>Assign Students</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=teacher_assignments">
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
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <span>Teacher Assignments</span>
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
                            Teacher Assignments
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
                            <h1 class="header-title">Teacher Assignments</h1>
                            <p class="header-subtitle">Assign teachers to sections and subjects</p>
                        </div>
                    </div>
                </div>

                <!-- assign form card -->
                <div class="card assignment-form-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person-plus"></i>
                            New Assignment
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="assignForm">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-person-fill"></i>
                                        Teacher
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" name="teacher_id" required>
                                        <option value="">Select a teacher...</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['full_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-diagram-3-fill"></i>
                                        Section
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" name="section_id" required>
                                        <option value="">Select a section...</option>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?= $section['section_id'] ?>"><?= htmlspecialchars($section['section_name']) ?> (<?= htmlspecialchars($section['year_level']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-calendar-range"></i>
                                        School Year
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" name="school_year" required>
                                        <?php foreach ($school_year_options as $index => $sy): ?>
                                            <option value="<?= $sy ?>" <?= ($index === 1) ? 'selected' : '' ?>>
                                                <?= $sy ?> <?= ($index === 0) ? '(Current)' : '(Next)' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-calendar3"></i>
                                        Semester
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" name="semester" required>
                                        <option value="First">First Semester</option>
                                        <option value="Second">Second Semester</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-book-fill"></i>
                                    Subjects
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="subjects-container" id="assignSubjectsContainer">
                                    <?php foreach ($subjects as $subject): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="subject_ids[]" value="<?= $subject['subject_id'] ?>" id="subject_<?= $subject['subject_id'] ?>">
                                            <label class="form-check-label" for="subject_<?= $subject['subject_id'] ?>">
                                                <?= htmlspecialchars($subject['subject_name']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="invalid-feedback d-block" id="subject_ids_error"></div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary btn-submit">
                                    <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Assign Teacher
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- active assignments table -->
                <div class="card assignments-table-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-people-fill"></i>
                            Active Assignments
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($assignments)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">No active assignments yet</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="activeAssignmentsTable">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-person-fill"></i> Teacher</th>
                                            <th><i class="bi bi-diagram-3-fill"></i> Section</th>
                                            <th><i class="bi bi-mortarboard-fill"></i> Year Level</th>
                                            <th><i class="bi bi-calendar-range"></i> School Year</th>
                                            <th><i class="bi bi-calendar3"></i> Semester</th>
                                            <th><i class="bi bi-book-fill"></i> Subjects</th>
                                            <th><i class="bi bi-gear-fill"></i> Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assignments as $assignment): ?>
                                            <tr data-row-key="<?= $assignment['teacher_id'] ?>_<?= $assignment['section_id'] ?>_<?= htmlspecialchars($assignment['school_year']) ?>_<?= htmlspecialchars($assignment['semester']) ?>">
                                                <td><?= htmlspecialchars($assignment['teacher_name']) ?></td>
                                                <td><?= htmlspecialchars($assignment['section_name']) ?></td>
                                                <td><?= htmlspecialchars($assignment['year_level']) ?></td>
                                                <td><?= htmlspecialchars($assignment['school_year']) ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?= htmlspecialchars($assignment['semester']) ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($assignment['subject_count'] < 2): ?>
                                                        <span class="text-muted"><?= htmlspecialchars($assignment['subjects']) ?></span>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#subjectsModal" data-subjects="<?= htmlspecialchars($assignment['subjects']) ?>">
                                                            <i class="bi bi-book"></i> <?= $assignment['subject_count'] ?> subjects
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary me-1 btn-reassign"
                                                        data-teacher-id="<?= $assignment['teacher_id'] ?>"
                                                        data-section-id="<?= $assignment['section_id'] ?>"
                                                        data-school-year="<?= htmlspecialchars($assignment['school_year']) ?>"
                                                        data-semester="<?= htmlspecialchars($assignment['semester']) ?>"
                                                        data-subject-ids="<?= htmlspecialchars($assignment['subject_ids']) ?>">
                                                        <i class="bi bi-pencil"></i> Reassign
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger btn-remove"
                                                        data-teacher-id="<?= $assignment['teacher_id'] ?>"
                                                        data-section-id="<?= $assignment['section_id'] ?>"
                                                        data-school-year="<?= htmlspecialchars($assignment['school_year']) ?>"
                                                        data-semester="<?= htmlspecialchars($assignment['semester']) ?>"
                                                        data-teacher-name="<?= htmlspecialchars($assignment['teacher_name']) ?>"
                                                        data-section-name="<?= htmlspecialchars($assignment['section_name']) ?>">
                                                        <i class="bi bi-trash"></i> Remove
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- removed assignments table -->
                <div class="card assignments-table-card">
                    <div class="card-header bg-secondary">
                        <h5 class="mb-0">
                            <i class="bi bi-archive-fill"></i>
                            Removed Assignments
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($inactive_assignments)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">No removed assignments</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="inactiveAssignmentsTable">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-person-fill"></i> Teacher</th>
                                            <th><i class="bi bi-diagram-3-fill"></i> Section</th>
                                            <th><i class="bi bi-mortarboard-fill"></i> Year Level</th>
                                            <th><i class="bi bi-calendar-range"></i> School Year</th>
                                            <th><i class="bi bi-calendar3"></i> Semester</th>
                                            <th><i class="bi bi-book-fill"></i> Subjects</th>
                                            <th><i class="bi bi-gear-fill"></i> Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inactive_assignments as $assignment): ?>
                                            <tr data-row-key="<?= $assignment['teacher_id'] ?>_<?= $assignment['section_id'] ?>_<?= htmlspecialchars($assignment['school_year']) ?>_<?= htmlspecialchars($assignment['semester']) ?>">
                                                <td><?= htmlspecialchars($assignment['teacher_name']) ?></td>
                                                <td><?= htmlspecialchars($assignment['section_name']) ?></td>
                                                <td><?= htmlspecialchars($assignment['year_level']) ?></td>
                                                <td><?= htmlspecialchars($assignment['school_year']) ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($assignment['semester']) ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($assignment['subject_count'] < 2): ?>
                                                        <span class="text-muted"><?= htmlspecialchars($assignment['subjects']) ?></span>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#subjectsModal" data-subjects="<?= htmlspecialchars($assignment['subjects']) ?>">
                                                            <i class="bi bi-book"></i> <?= $assignment['subject_count'] ?> subjects
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-success btn-restore"
                                                        data-teacher-id="<?= $assignment['teacher_id'] ?>"
                                                        data-section-id="<?= $assignment['section_id'] ?>"
                                                        data-school-year="<?= htmlspecialchars($assignment['school_year']) ?>"
                                                        data-semester="<?= htmlspecialchars($assignment['semester']) ?>"
                                                        data-teacher-name="<?= htmlspecialchars($assignment['teacher_name']) ?>"
                                                        data-section-name="<?= htmlspecialchars($assignment['section_name']) ?>">
                                                        <i class="bi bi-arrow-clockwise"></i> Restore
                                                    </button>
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

    <!-- reassign modal -->
    <div class="modal fade" id="reassignModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil"></i>
                        Reassign Teacher
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="reassignForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="teacher_id" id="reassign_teacher_id">
                        <input type="hidden" name="section_id" id="reassign_section_id">
                        <input type="hidden" name="school_year" id="reassign_school_year">
                        <input type="hidden" name="semester" id="reassign_semester">

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <div>
                                <strong>Note:</strong> Unchecking subjects will soft-remove them. Checking removed subjects will restore them.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-book-fill"></i>
                                Subjects
                                <span class="text-danger">*</span>
                            </label>
                            <div class="subjects-container" id="reassignSubjectsContainer">
                                <?php foreach ($subjects as $subject): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="subject_ids[]" value="<?= $subject['subject_id'] ?>" id="reassign_subject_<?= $subject['subject_id'] ?>">
                                        <label class="form-check-label" for="reassign_subject_<?= $subject['subject_id'] ?>">
                                            <?= htmlspecialchars($subject['subject_name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="invalid-feedback d-block" id="reassign_subject_ids_error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                            <i class="bi bi-check-circle-fill"></i>
                            Update Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- subjects list modal -->
    <div class="modal fade" id="subjectsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-book"></i>
                        Assigned Subjects
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush" id="subjectsList"></ul>
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

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrfToken = '<?= $_SESSION['csrf_token'] ?>';
    </script>
    <script src="js/teacher-assignments-ajax.js"></script>

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