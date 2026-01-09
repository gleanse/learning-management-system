<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Students - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
</head>
<body>
    <div class="d-flex">
        <!-- sidebar -->
        <div class="bg-dark text-white p-3" style="width: 250px; min-height: 100vh;">
            <h5>School Name</h5>
            <p class="text-muted small">Learning Management System</p>
            <hr>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=teacher_dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white active" href="index.php?page=grading">Grading Management</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=logout">Logout</a>
                </li>
            </ul>
        </div>

        <!-- main content -->
        <div class="flex-grow-1">
            <!-- top navbar -->
            <nav class="navbar navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1">Grading Management</span>
                    <div class="d-flex align-items-center">
                        <span class="me-2">
                            <strong><?php echo htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']); ?></strong>
                            <small class="text-muted d-block"><?php echo ucfirst(htmlspecialchars($_SESSION['user_role'])); ?></small>
                        </span>
                    </div>
                </div>
            </nav>

            <!-- page content -->
            <div class="container-fluid p-4">
                <!-- breadcrumbs -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?page=grading">Grading</a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=grading_subjects&year_level=<?php echo urlencode($year_level); ?>"><?php echo htmlspecialchars($year_level); ?></a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=grading_sections&year_level=<?php echo urlencode($year_level); ?>&subject_id=<?php echo urlencode($subject_id); ?>&school_year=<?php echo urlencode($school_year); ?>">Subject</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Section</li>
                    </ol>
                </nav>

                <h2>Grade Students</h2>

                <!-- success message -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- grading period selector -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" action="index.php">
                            <input type="hidden" name="page" value="grading_students">
                            <input type="hidden" name="year_level" value="<?php echo htmlspecialchars($year_level); ?>">
                            <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($subject_id); ?>">
                            <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($section_id); ?>">
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">School Year</label>
                                    <select name="school_year" class="form-select" onchange="this.form.submit()">
                                        <option value="2025-2026" <?php echo $school_year === '2025-2026' ? 'selected' : ''; ?>>2025-2026</option>
                                        <option value="2024-2025" <?php echo $school_year === '2024-2025' ? 'selected' : ''; ?>>2024-2025</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Semester</label>
                                    <select name="semester" class="form-select" onchange="this.form.submit()">
                                        <option value="First" <?php echo $semester === 'First' ? 'selected' : ''; ?>>First Semester</option>
                                        <option value="Second" <?php echo $semester === 'Second' ? 'selected' : ''; ?>>Second Semester</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Grading Period</label>
                                    <select name="grading_period" class="form-select" onchange="this.form.submit()">
                                        <option value="Prelim" <?php echo $grading_period === 'Prelim' ? 'selected' : ''; ?>>Prelim</option>
                                        <option value="Midterm" <?php echo $grading_period === 'Midterm' ? 'selected' : ''; ?>>Midterm</option>
                                        <option value="Prefinal" <?php echo $grading_period === 'Prefinal' ? 'selected' : ''; ?>>Prefinal</option>
                                        <option value="Final" <?php echo $grading_period === 'Final' ? 'selected' : ''; ?>>Final</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <div>
                                        <?php if ($is_locked): ?>
                                            <span class="badge bg-danger">Locked</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Open</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- student list -->
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($students)): ?>
                            <p class="text-muted">No students enrolled in this section.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student Number</th>
                                            <th>Name</th>
                                            <th>Section</th>
                                            <th>Grade</th>
                                            <th>Remarks</th>
                                            <th>Action</th>
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
                                                        <span class="badge bg-primary"><?php echo htmlspecialchars($student['percentage_display']); ?> (<?php echo htmlspecialchars($student['gpa_display']); ?>)</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not graded</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($student['remarks'] ?? ''); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#gradeModal<?php echo $student['student_id']; ?>"
                                                            <?php echo $is_locked ? 'disabled' : ''; ?>>
                                                        <?php echo !empty($student['grade_value']) ? 'Edit' : 'Add'; ?> Grade
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- grade modal -->
                                            <div class="modal fade" id="gradeModal<?php echo $student['student_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
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
                                                                <h5 class="modal-title">Grade Student</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Student:</strong> <?php echo htmlspecialchars($full_name); ?></p>
                                                                <p><strong>Student Number:</strong> <?php echo htmlspecialchars($student['student_number']); ?></p>
                                                                <hr>
                                                                
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Percentage (0-100)</label>
                                                                            <input type="number" 
                                                                                   id="percentage<?php echo $student['student_id']; ?>"
                                                                                   class="form-control" 
                                                                                   step="0.01"
                                                                                   min="0"
                                                                                   max="100"
                                                                                   value="<?php echo htmlspecialchars($student['grade_value'] ?? ''); ?>">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">GPA (1.0-5.0)</label>
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
                                                                    <label class="form-label">Remarks (Optional)</label>
                                                                    <textarea name="remarks" 
                                                                              class="form-control" 
                                                                              rows="3"><?php echo htmlspecialchars($student['remarks'] ?? ''); ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">Save Grade</button>
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
                        gradeFormatHidden.value = 'gpa';
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