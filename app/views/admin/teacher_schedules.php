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
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1100;" id="toastContainer"></div>

    <div class="d-flex">
        <div class="sidenav">
            <div class="sidenav-header">
                <div class="school-brand">
                    <div class="school-logo">
                        <img src="assets/DCSA-LOGO.png" alt="School Logo" style="width:100%;height:100%;object-fit:contain;border-radius:0.75rem;">
                    </div>
                    <div class="school-info">
                        <h5>Datamex College of Saint Adeline</h5>
                        <p class="subtitle">Learning Management System</p>
                    </div>
                </div>
            </div>
            <ul class="sidenav-menu">
                <li class="nav-item"><a class="nav-link" href="index.php?page=admin_dashboard"><i class="bi bi-house-door-fill"></i><span>Dashboard</span></a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=manage_sections"><i class="bi bi-grid-3x3-gap-fill"></i><span>Section Management</span></a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=subjects"><i class="bi bi-book-fill"></i><span>Subject Management</span></a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=student_sections"><i class="bi bi-people-fill"></i><span>Assign Students</span></a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=teacher_assignments"><i class="bi bi-person-plus-fill"></i><span>Teacher Assignments</span></a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?page=teacher_schedules"><i class="bi bi-calendar-week-fill"></i><span>Manage Schedules</span></a></li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=academic_period">
                        <i class="bi bi-calendar2-range-fill"></i>
                        <span>Academic Period</span>
                    </a>
                </li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=logout"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
            </ul>
        </div>

        <div class="main-content flex-grow-1">
            <nav class="navbar top-navbar">
                <div class="container-fluid">
                    <div class="navbar-brand mb-0">
                        <div class="page-icon"><i class="bi bi-calendar-week-fill"></i></div>
                        <span>Manage Schedules</span>
                    </div>
                    <div class="user-info-wrapper">
                        <div class="user-details">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']); ?></span>
                            <span class="user-role"><i class="bi bi-person-badge-fill"></i> <?php echo ucfirst(htmlspecialchars($_SESSION['user_role'])); ?></span>
                        </div>
                        <div class="user-avatar">
                            <?php
                            $firstname = $_SESSION['user_firstname'] ?? 'A';
                            $lastname  = $_SESSION['user_lastname']  ?? 'U';
                            echo strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1));
                            ?>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?page=admin_dashboard"><i class="bi bi-house-door-fill"></i> Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Manage Schedules</li>
                    </ol>
                </nav>

                <div class="page-header mb-4">
                    <div class="header-content">
                        <div class="header-icon"><i class="bi bi-calendar-week-fill"></i></div>
                        <div class="header-text">
                            <h1 class="header-title">Teacher Schedule Management</h1>
                            <p class="header-subtitle">Select a teacher to manage their class schedules</p>
                        </div>
                    </div>
                </div>

                <!-- two-column layout -->
                <div class="schedule-layout">

                    <!-- LEFT: teacher picker -->
                    <aside class="teacher-picker">
                        <div class="picker-header">
                            <span class="picker-title">
                                <i class="bi bi-people-fill"></i> Teachers
                            </span>
                            <span class="picker-count" id="pickerCount"><?= $total_teachers ?></span>
                        </div>
                        <div class="picker-search">
                            <i class="bi bi-search"></i>
                            <input type="text" id="teacherSearchInput" placeholder="Search name..." autocomplete="off">
                        </div>
                        <div class="picker-list" id="teacherList">
                            <div class="picker-loading">
                                <span class="spinner-border spinner-border-sm"></span> Loading...
                            </div>
                        </div>
                        <div class="picker-pagination" id="pickerPagination"></div>
                    </aside>

                    <!-- RIGHT: schedule panel -->
                    <div class="schedule-panel">

                        <div class="pick-prompt" id="pickPrompt">
                            <div class="pick-prompt-icon"><i class="bi bi-person-lines-fill"></i></div>
                            <p class="pick-prompt-title">No teacher selected</p>
                            <p class="pick-prompt-text">Search and click a teacher on the left to view and manage their schedules.</p>
                        </div>

                        <div id="scheduleContent" class="d-none">

                            <!-- slim single-row filter bar -->
                            <div class="filter-bar mb-3">
                                <div class="filter-bar-teacher">
                                    <div class="filter-teacher-avatar" id="filterTeacherAvatar"></div>
                                    <span class="filter-teacher-name" id="selectedTeacherName"></span>
                                </div>
                                <div class="filter-bar-controls">
                                    <select class="filter-select" id="filterSchoolYear">
                                        <?php
                                        $current_year = (int) date('Y');
                                        for ($y = $current_year; $y >= $current_year - 3; $y--):
                                            $yv = $y . '-' . ($y + 1);
                                        ?>
                                            <option value="<?= $yv ?>" <?= $y === $current_year ? 'selected' : '' ?>><?= $yv ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select class="filter-select" id="filterSemester">
                                        <option value="First">First Semester</option>
                                        <option value="Second">Second Semester</option>
                                    </select>
                                </div>
                            </div>

                            <!-- quick stats row -->
                            <div class="stats-row" id="statsRow"></div>

                            <!-- assignments list -->
                            <div id="assignmentsPanel"></div>

                        </div>
                    </div>

                </div>

                <input type="hidden" id="filterTeacher" value="">

            </div>
        </div>
    </div>

    <!-- add schedule modal -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-calendar-plus-fill"></i> Add Schedule</h5>
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
                        <div class="assignment-context mb-3" id="addAssignmentContext"></div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-calendar-day"></i> Day of Week <span class="text-danger">*</span></label>
                                <select class="form-select" name="day_of_week" id="addDayOfWeek" required>
                                    <option value="">Select day...</option>
                                    <option>Monday</option>
                                    <option>Tuesday</option>
                                    <option>Wednesday</option>
                                    <option>Thursday</option>
                                    <option>Friday</option>
                                    <option>Saturday</option>
                                    <option>Sunday</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label"><i class="bi bi-clock-fill"></i> Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="start_time" id="addStartTime" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label"><i class="bi bi-clock-fill"></i> End Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="end_time" id="addEndTime" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-door-closed-fill"></i> Room <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="text" class="form-control" name="room" id="addRoom" placeholder="e.g., Room 201">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmAddScheduleBtn"><i class="bi bi-check-circle-fill"></i> Add Schedule</button>
                </div>
            </div>
        </div>
    </div>

    <!-- edit schedule modal -->
    <div class="modal fade" id="editScheduleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-fill"></i> Edit Schedule</h5>
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
                        <div class="assignment-context mb-3" id="editAssignmentContext"></div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-calendar-day"></i> Day of Week <span class="text-danger">*</span></label>
                                <select class="form-select" name="day_of_week" id="editDayOfWeek" required>
                                    <option value="">Select day...</option>
                                    <option>Monday</option>
                                    <option>Tuesday</option>
                                    <option>Wednesday</option>
                                    <option>Thursday</option>
                                    <option>Friday</option>
                                    <option>Saturday</option>
                                    <option>Sunday</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label"><i class="bi bi-clock-fill"></i> Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="start_time" id="editStartTime" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label"><i class="bi bi-clock-fill"></i> End Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="end_time" id="editEndTime" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-door-closed-fill"></i> Room <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="text" class="form-control" name="room" id="editRoom" placeholder="e.g., Room 201">
                            </div>
                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-toggle-on"></i> Status</label>
                                <select class="form-select" name="status" id="editStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmEditScheduleBtn"><i class="bi bi-check-circle-fill"></i> Update Schedule</button>
                </div>
            </div>
        </div>
    </div>

    <!-- delete modal -->
    <div class="modal fade" id="deleteScheduleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill"></i> Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this schedule? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteScheduleBtn"><i class="bi bi-trash-fill"></i> Delete</button>
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
            <?php if (!empty($success_message)): ?>showAlert('success', '<?= addslashes($success_message) ?>');
        <?php endif; ?>
        <?php if (!empty($errors['general'])): ?>showAlert('danger', '<?= addslashes($errors['general']) ?>');
        <?php endif; ?>
        });
    </script>
</body>

</html>