<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Section - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/section_management.css">
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
                    <a class="nav-link active" href="index.php?page=manage_sections">
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
                            <i class="bi bi-pencil-fill"></i>
                        </div>
                        <span>Edit Section</span>
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
                            <a href="index.php?page=manage_sections">
                                Section Management
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Edit Section
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
                            <h1 class="header-title">Edit Section</h1>
                            <p class="header-subtitle">Update section information</p>
                        </div>
                    </div>
                </div>

                <!-- edit form card -->
                <div class="card subject-form-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text"></i>
                            Section Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="editSectionForm">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="section_id" value="<?= $section['section_id'] ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-tag-fill"></i>
                                        Section Name
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        class="form-control"
                                        name="section_name"
                                        placeholder="e.g., Grade 11 - Section A"
                                        value="<?= htmlspecialchars($section['section_name']) ?>"
                                        required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-mortarboard-fill"></i>
                                        Education Level
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" name="education_level" id="educationLevel" required>
                                        <option value="">Select education level</option>
                                        <option value="senior_high" <?= $section['education_level'] === 'senior_high' ? 'selected' : '' ?>>Senior High School</option>
                                        <option value="college" <?= $section['education_level'] === 'college' ? 'selected' : '' ?>>College</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-bar-chart-steps"></i>
                                        Year Level
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" name="year_level" id="yearLevel" required>
                                        <option value="">Select education level first</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-bookmark-fill"></i>
                                        <span id="strandCourseLabel">
                                            <?= $section['education_level'] === 'senior_high' ? 'Strand' : 'Course' ?>
                                        </span>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" name="strand_course" id="strandCourse" required>
                                        <option value="">Select strand/course</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-people-fill"></i>
                                        Maximum Capacity
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                        class="form-control"
                                        name="max_capacity"
                                        placeholder="e.g., 35"
                                        value="<?= htmlspecialchars($section['max_capacity'] ?? '') ?>"
                                        min="1"
                                        required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-calendar-range"></i>
                                        School Year
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        class="form-control"
                                        name="school_year"
                                        value="<?= htmlspecialchars($section['school_year']) ?>"
                                        readonly>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="index.php?page=manage_sections" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Update Section
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrfToken = '<?= $_SESSION['csrf_token'] ?>';

        const educationLevelSelect = document.getElementById('educationLevel');
        const strandCourseSelect = document.getElementById('strandCourse');
        const strandCourseLabel = document.getElementById('strandCourseLabel');
        const yearLevelSelect = document.getElementById('yearLevel');

        const currentEducationLevel = '<?= $section['education_level'] ?>';
        const currentStrandCourse = '<?= htmlspecialchars($section['strand_course']) ?>';
        const currentYearLevel = '<?= htmlspecialchars($section['year_level']) ?>';

        const strandOptions = {
            senior_high: [{
                    value: 'STEM',
                    text: 'STEM (Science, Technology, Engineering, and Mathematics)'
                },
                {
                    value: 'ABM',
                    text: 'ABM (Accountancy, Business, and Management)'
                },
                {
                    value: 'HUMSS',
                    text: 'HUMSS (Humanities and Social Sciences)'
                },
                {
                    value: 'GAS',
                    text: 'GAS (General Academic Strand)'
                },
                {
                    value: 'ICT',
                    text: 'ICT (Information and Communications Technology)'
                }
            ],
            college: [{
                    value: 'BSIT',
                    text: 'BSIT (Bachelor of Science in Information Technology)'
                },
                {
                    value: 'ACT',
                    text: 'ACT (Associate in Computer Technology) - 2 Years'
                },
                {
                    value: 'BSHM',
                    text: 'BSHM (Bachelor of Science in Hospitality Management)'
                }
            ]
        };

        const yearOptions = {
            senior_high: [{
                    value: 'Grade 11',
                    text: 'Grade 11'
                },
                {
                    value: 'Grade 12',
                    text: 'Grade 12'
                }
            ],
            college: [{
                    value: '1st Year',
                    text: '1st Year'
                },
                {
                    value: '2nd Year',
                    text: '2nd Year'
                },
                {
                    value: '3rd Year',
                    text: '3rd Year'
                },
                {
                    value: '4th Year',
                    text: '4th Year'
                }
            ]
        };

        function populateStrandCourse(educationLevel, selectedValue = '') {
            strandCourseSelect.innerHTML = '<option value="">Select strand/course</option>';

            if (educationLevel) {
                if (educationLevel === 'senior_high') {
                    strandCourseLabel.textContent = 'Strand';
                } else {
                    strandCourseLabel.textContent = 'Course';
                }

                const options = strandOptions[educationLevel] || [];
                options.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    if (option.value === selectedValue) opt.selected = true;
                    strandCourseSelect.appendChild(opt);
                });
            } else {
                strandCourseLabel.textContent = 'Strand/Course';
            }
        }

        function populateYearLevel(educationLevel, course, selectedValue = '') {
            yearLevelSelect.innerHTML = '<option value="">Select year level</option>';

            if (educationLevel) {
                let options = yearOptions[educationLevel] || [];

                if (educationLevel === 'college' && course === 'ACT') {
                    options = options.filter(y => y.value === '1st Year' || y.value === '2nd Year');
                }

                options.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    if (option.value === selectedValue) opt.selected = true;
                    yearLevelSelect.appendChild(opt);
                });
            }
        }

        populateStrandCourse(currentEducationLevel, currentStrandCourse);
        populateYearLevel(currentEducationLevel, currentStrandCourse, currentYearLevel);

        educationLevelSelect.addEventListener('change', function() {
            populateStrandCourse(this.value);
            populateYearLevel(this.value, '');
        });

        strandCourseSelect.addEventListener('change', function() {
            const currentYear = yearLevelSelect.value;
            populateYearLevel(educationLevelSelect.value, this.value, currentYear);
        });
    </script>
    <script src="js/edit-section-ajax.js"></script>

</body>

</html>