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
            <div class="container-fluid p-4">
                <!-- page header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">Teacher Assignments</h2>
                        <p class="text-muted mb-0">Assign teachers to sections and subjects</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
                        <i class="bi bi-person-plus"></i> Assign Teacher
                    </button>
                </div>

                <!-- alerts -->
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($errors['general']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- active assignments table -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Active Assignments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="activeAssignmentsTable">
                                <thead>
                                    <tr>
                                        <th>Teacher</th>
                                        <th>Section</th>
                                        <th>Year Level</th>
                                        <th>School Year</th>
                                        <th>Semester</th>
                                        <th>Subjects</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($assignments)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                No active assignments yet
                                            </td>
                                        </tr>
                                    <?php else: ?>
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
                                                    <?php if ($assignment['subject_count'] <= 2): ?>
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
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- removed assignments table -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-archive-fill me-2"></i>Removed Assignments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="inactiveAssignmentsTable">
                                <thead>
                                    <tr>
                                        <th>Teacher</th>
                                        <th>Section</th>
                                        <th>Year Level</th>
                                        <th>School Year</th>
                                        <th>Semester</th>
                                        <th>Subjects</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($inactive_assignments)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                No removed assignments
                                            </td>
                                        </tr>
                                    <?php else: ?>
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
                                                    <?php if ($assignment['subject_count'] <= 2): ?>
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
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- assign modal -->
    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Assign Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="assignForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="mb-3">
                            <label class="form-label">Teacher <span class="text-danger">*</span></label>
                            <select class="form-select" name="teacher_id" required>
                                <option value="">Select a teacher...</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Section <span class="text-danger">*</span></label>
                            <select class="form-select" name="section_id" required>
                                <option value="">Select a section...</option>
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?= $section['section_id'] ?>"><?= htmlspecialchars($section['section_name']) ?> (<?= htmlspecialchars($section['year_level']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">School Year <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="school_year" value="2025-2026" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Semester <span class="text-danger">*</span></label>
                            <select class="form-select" name="semester" required>
                                <option value="First">First Semester</option>
                                <option value="Second">Second Semester</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subjects <span class="text-danger">*</span></label>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($subjects as $subject): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="subject_ids[]" value="<?= $subject['subject_id'] ?>" id="subject_<?= $subject['subject_id'] ?>">
                                        <label class="form-check-label" for="subject_<?= $subject['subject_id'] ?>">
                                            <?= htmlspecialchars($subject['subject_name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                            Assign Teacher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- reassign modal -->
    <div class="modal fade" id="reassignModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Reassign Teacher</h5>
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
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> Unchecking subjects will soft-remove them. Checking removed subjects will restore them.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subjects <span class="text-danger">*</span></label>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;" id="reassignSubjectsContainer">
                                <?php foreach ($subjects as $subject): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="subject_ids[]" value="<?= $subject['subject_id'] ?>" id="reassign_subject_<?= $subject['subject_id'] ?>">
                                        <label class="form-check-label" for="reassign_subject_<?= $subject['subject_id'] ?>">
                                            <?= htmlspecialchars($subject['subject_name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                            Update Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- subjects list modal -->
    <div class="modal fade" id="subjectsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-book me-2"></i>Assigned Subjects</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group" id="subjectsList"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // store csrf token for ajax requests
        const csrfToken = '<?= $_SESSION['csrf_token'] ?>';
    </script>
    <script src="js/teacher-assignments-ajax.js"></script>

</body>

</html>