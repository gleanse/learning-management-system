<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Students - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/student_list.css">
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
                    <a class="nav-link" href="index.php?page=teacher_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=grading">
                        <i class="bi bi-journal-text"></i>
                        <span>Grading Management</span>
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
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <span>Grading Management</span>
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
                            <!-- user avatar placeholder first letters of name -->
                            <?php
                            $firstname = $_SESSION['user_firstname'] ?? 'T';
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
                            <a href="index.php?page=teacher_dashboard">
                                <i class="bi bi-house-door-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php?page=grading">Grading</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php?page=grading_subjects&year_level=<?= urlencode($year_level) ?>&school_year=<?= urlencode($school_year) ?>&semester=<?= urlencode($semester) ?>">
                                <?php echo htmlspecialchars($year_level); ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php?page=grading_sections&year_level=<?= urlencode($year_level) ?>&subject_id=<?= urlencode($subject_id) ?>&school_year=<?= urlencode($school_year) ?>&semester=<?= urlencode($semester) ?>">
                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?php echo htmlspecialchars($section['section_name']); ?>
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-clipboard2-check-fill"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Grade Students</h1>
                            <p class="header-subtitle">
                                <span class="info-badge">
                                    <i class="bi bi-book-fill"></i>
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </span>
                                <span class="info-badge">
                                    <i class="bi bi-people-fill"></i>
                                    <?php echo htmlspecialchars($section['section_name']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- success message -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- grading period selector -->
                <div class="card grading-selector-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-sliders"></i>
                            Grading Period Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="index.php">
                            <input type="hidden" name="page" value="grading_students">
                            <input type="hidden" name="year_level" value="<?php echo htmlspecialchars($year_level); ?>">
                            <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($subject_id); ?>">
                            <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($section_id); ?>">

                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">
                                        <i class="bi bi-calendar-range"></i>
                                        School Year
                                    </label>
                                    <select name="school_year" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ($available_years as $year): ?>
                                            <option value="<?= $year ?>" <?= $school_year === $year ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($year) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">
                                        <i class="bi bi-calendar3"></i>
                                        Semester
                                    </label>
                                    <select name="semester" class="form-select" onchange="this.form.submit()">
                                        <option value="First" <?php echo $semester === 'First' ? 'selected' : ''; ?>>First Semester</option>
                                        <option value="Second" <?php echo $semester === 'Second' ? 'selected' : ''; ?>>Second Semester</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">
                                        <i class="bi bi-calendar-check"></i>
                                        Grading Period
                                    </label>
                                    <select name="grading_period" class="form-select" onchange="this.form.submit()">
                                        <option value="Prelim" <?php echo $grading_period === 'Prelim' ? 'selected' : ''; ?>>Prelim</option>
                                        <option value="Midterm" <?php echo $grading_period === 'Midterm' ? 'selected' : ''; ?>>Midterm</option>
                                        <option value="Prefinal" <?php echo $grading_period === 'Prefinal' ? 'selected' : ''; ?>>Prefinal</option>
                                        <option value="Final" <?php echo $grading_period === 'Final' ? 'selected' : ''; ?>>Final</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">
                                        <i class="bi bi-shield-lock"></i>
                                        Status
                                    </label>
                                    <div class="status-badge-wrapper">
                                        <?php if ($is_locked): ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-lock-fill"></i> Locked
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-unlock-fill"></i> Open
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- student list -->
                <div class="card student-list-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-people-fill"></i>
                            Student List
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($students)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">No students enrolled for this period.</p>
                                <p class="empty-state-subtext">
                                    There are no students enrolled in <strong><?php echo htmlspecialchars($section['section_name']); ?></strong>
                                    for <strong><?php echo htmlspecialchars($semester); ?> Semester, <?php echo htmlspecialchars($school_year); ?></strong>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-hash"></i> Student Number</th>
                                            <th><i class="bi bi-person-fill"></i> Name</th>
                                            <th><i class="bi bi-diagram-3-fill"></i> Section</th>
                                            <th><i class="bi bi-award-fill"></i> Grade</th>
                                            <th><i class="bi bi-chat-left-text-fill"></i> Remarks</th>
                                            <th><i class="bi bi-gear-fill"></i> Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                                                <td>
                                                    <?php
                                                    $full_name = $student['first_name'] . ' ';
                                                    if (!empty($student['middle_name'])) {
                                                        $full_name .= $student['middle_name'] . ' ';
                                                    }
                                                    $full_name .= $student['last_name'];
                                                    echo htmlspecialchars($full_name);
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($student['section_name']); ?></td>
                                                <!-- displays percentage and GPA using data from controller -->
                                                <td>
                                                    <?php if (!empty($student['percentage_display'])): ?>
                                                        <span class="badge bg-primary">
                                                            <i class="bi bi-graph-up"></i>
                                                            <?php echo htmlspecialchars($student['percentage_display']); ?>%
                                                            <span class="gpa-separator">•</span>
                                                            <?php echo htmlspecialchars($student['gpa_display']); ?> GPA
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">
                                                            <i class="bi bi-dash-circle"></i> Not graded
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($student['remarks'] ?? ''); ?></td>
                                                <td>
                                                    <?php if ($is_locked): ?>
                                                        <span class="badge badge-locked">
                                                            <i class="bi bi-lock-fill"></i> Period Locked
                                                        </span>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-sm btn-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#gradeModal<?php echo $student['student_id']; ?>">
                                                            <i class="bi bi-<?php echo !empty($student['grade_value']) ? 'pencil-square' : 'plus-circle'; ?>"></i>
                                                            <?php echo !empty($student['grade_value']) ? 'Edit' : 'Add'; ?> Grade
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>

                                            <!-- grade modal -->
                                            <div class="modal fade" id="gradeModal<?php echo $student['student_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form method="POST" action="index.php?page=save_grade">
                                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                            <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                                            <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                                                            <input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
                                                            <input type="hidden" name="year_level" value="<?php echo $year_level; ?>">
                                                            <input type="hidden" name="school_year" value="<?php echo $school_year; ?>">
                                                            <input type="hidden" name="semester" value="<?php echo $semester; ?>">
                                                            <input type="hidden" name="grading_period" value="<?php echo $grading_period; ?>">
                                                            <input type="hidden" name="grade_format" id="gradeFormatHidden<?php echo $student['student_id']; ?>" value="percentage">

                                                            <div class="modal-header">
                                                                <h5 class="modal-title">
                                                                    <i class="bi bi-clipboard2-check-fill"></i>
                                                                    Grade Student
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="student-info-card">
                                                                    <div class="student-info-row">
                                                                        <span class="info-label">
                                                                            <i class="bi bi-person-circle"></i> Student:
                                                                        </span>
                                                                        <span class="info-value"><?php echo htmlspecialchars($full_name); ?></span>
                                                                    </div>
                                                                    <div class="student-info-row">
                                                                        <span class="info-label">
                                                                            <i class="bi bi-hash"></i> Student Number:
                                                                        </span>
                                                                        <span class="info-value"><?php echo htmlspecialchars($student['student_number']); ?></span>
                                                                    </div>
                                                                </div>

                                                                <hr>

                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">
                                                                                <i class="bi bi-percent"></i>
                                                                                Percentage (0-100)
                                                                            </label>
                                                                            <input type="number"
                                                                                id="percentage<?php echo $student['student_id']; ?>"
                                                                                class="form-control"
                                                                                step="0.01"
                                                                                min="0"
                                                                                max="100"
                                                                                placeholder="Enter percentage"
                                                                                value="<?php echo htmlspecialchars($student['grade_value'] ?? ''); ?>">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">
                                                                                <i class="bi bi-award-fill"></i>
                                                                                GPA (1.0-5.0)
                                                                            </label>
                                                                            <select id="gpa<?php echo $student['student_id']; ?>" class="form-select">
                                                                                <option value="">Select GPA</option>
                                                                                <option value="1.0">1.0 (97-100)</option>
                                                                                <option value="1.25">1.25 (94-96)</option>
                                                                                <option value="1.5">1.5 (91-93)</option>
                                                                                <option value="1.75">1.75 (88-90)</option>
                                                                                <option value="2.0">2.0 (85-87)</option>
                                                                                <option value="2.25">2.25 (82-84)</option>
                                                                                <option value="2.5">2.5 (79-81)</option>
                                                                                <option value="2.75">2.75 (76-78)</option>
                                                                                <option value="3.0">3.0 (75)</option>
                                                                                <option value="5.0">5.0 (0-74)</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <input type="hidden" name="grade_value" id="gradeValue<?php echo $student['student_id']; ?>" value="<?php echo htmlspecialchars($student['grade_value'] ?? ''); ?>">

                                                                <div class="mb-3">
                                                                    <label class="form-label">
                                                                        <i class="bi bi-chat-left-text-fill"></i>
                                                                        Remarks (Optional)
                                                                    </label>
                                                                    <textarea name="remarks"
                                                                        class="form-control"
                                                                        rows="3"
                                                                        placeholder="Add any remarks or comments..."><?php echo htmlspecialchars($student['remarks'] ?? ''); ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                    <i class="bi bi-x-circle"></i> Cancel
                                                                </button>
                                                                <button type="submit" class="btn btn-primary">
                                                                    <i class="bi bi-check-circle-fill"></i> Save Grade
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
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

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // TODO: might put this externally later
        document.addEventListener('DOMContentLoaded', function() {
            <?php foreach ($students as $student): ?>
                    (function() {
                        const studentId = <?php echo $student['student_id']; ?>;
                        const percentageInput = document.getElementById('percentage' + studentId);
                        const gpaSelect = document.getElementById('gpa' + studentId);
                        const gradeValueHidden = document.getElementById('gradeValue' + studentId);
                        const gradeFormatHidden = document.getElementById('gradeFormatHidden' + studentId);

                        // GPA to percentage conversion
                        const gpaToPercentage = {
                            '1.0': 98,
                            '1.25': 95.5,
                            '1.5': 92.5,
                            '1.75': 90.5,
                            '2.0': 87.5,
                            '2.25': 84.5,
                            '2.5': 81.5,
                            '2.75': 78.5,
                            '3.0': 75,
                            '5.0': 50
                        };

                        // percentage to GPA conversion
                        function percentageToGPA(percentage) {
                            const p = parseFloat(percentage);
                            if (p >= 97) return '1.0';
                            if (p >= 94) return '1.25';
                            if (p >= 91) return '1.5';
                            if (p >= 88) return '1.75';
                            if (p >= 85) return '2.0';
                            if (p >= 82) return '2.25';
                            if (p >= 79) return '2.5';
                            if (p >= 76) return '2.75';
                            if (p >= 75) return '3.0';
                            return '5.0';
                        }

                        // when percentage changes, update GPA
                        percentageInput.addEventListener('input', function() {
                            const percentage = this.value;
                            if (percentage !== '') {
                                const gpa = percentageToGPA(percentage);
                                gpaSelect.value = gpa;
                                gradeValueHidden.value = percentage;
                                gradeFormatHidden.value = 'percentage';
                            }
                        });

                        // When GPA changes, update percentage
                        gpaSelect.addEventListener('change', function() {
                            const gpa = this.value;
                            if (gpa !== '') {
                                const percentage = gpaToPercentage[gpa];
                                percentageInput.value = percentage;
                                gradeValueHidden.value = percentage;
                                gradeFormatHidden.value = 'percentage';
                            }
                        });

                        // initialize GPA select if grade exists
                        <?php if (!empty($student['grade_value'])): ?>
                            const initialGPA = percentageToGPA(<?php echo $student['grade_value']; ?>);
                            gpaSelect.value = initialGPA;
                        <?php endif; ?>
                    })();
            <?php endforeach; ?>
        });
    </script>
</body>

</html>