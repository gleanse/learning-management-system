<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Section - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/section_management.css">
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
                            <i class="bi bi-eye-fill"></i>
                        </div>
                        <span>View Section</span>
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
                            View Section
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-eye-fill"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title"><?= htmlspecialchars($section['section_name']) ?></h1>
                            <p class="header-subtitle">Section details and enrolled students</p>
                        </div>
                    </div>
                </div>

                <!-- section details card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle-fill"></i>
                            Section Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">
                                    <i class="bi bi-tag-fill"></i>
                                    Section Name
                                </label>
                                <p class="fw-bold"><?= htmlspecialchars($section['section_name']) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">
                                    <i class="bi bi-mortarboard-fill"></i>
                                    Education Level
                                </label>
                                <p>
                                    <span class="education-level-badge <?= $section['education_level'] === 'senior_high' ? 'badge-shs' : 'badge-college' ?>">
                                        <?= $section['education_level'] === 'senior_high' ? 'Senior High School' : 'College' ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">
                                    <i class="bi bi-bar-chart-steps"></i>
                                    Year Level
                                </label>
                                <p class="fw-bold"><?= htmlspecialchars($section['year_level']) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">
                                    <i class="bi bi-bookmark-fill"></i>
                                    Strand/Course
                                </label>
                                <p class="fw-bold"><?= htmlspecialchars($section['strand_course']) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">
                                    <i class="bi bi-people-fill"></i>
                                    Student Count
                                </label>
                                <p>
                                    <span class="student-count">
                                        <i class="bi bi-people-fill"></i>
                                        <?= $section['student_count'] ?><?= $section['max_capacity'] ? '/' . $section['max_capacity'] : '' ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">
                                    <i class="bi bi-calendar-range"></i>
                                    School Year
                                </label>
                                <p class="fw-bold"><?= htmlspecialchars($section['school_year']) ?></p>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="index.php?page=manage_sections" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                Back to Sections
                            </a>
                        </div>
                    </div>
                </div>

                <!-- enrolled students card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-people-fill"></i>
                            Enrolled Students (<span id="totalStudentsBadge"><?= $total_students ?></span>)
                        </h5>
                        <!-- search form -->
                        <div class="d-flex" style="width: 250px;">
                            <div class="input-group input-group-sm">
                                <input type="text" id="studentSearchInput" class="form-control" placeholder="Search student..." value="<?= htmlspecialchars($search) ?>">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Student Number</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Year Level</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsTableBody">
                                    <?php if (empty($students)): ?>
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state">
                                                    <div class="empty-state-icon">
                                                        <i class="bi bi-inbox"></i>
                                                    </div>
                                                    <p class="empty-state-text">No students found in this section</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td>
                                                    <span class="subject-code"><?= htmlspecialchars($student['student_number']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="section-name">
                                                        <?= htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="strand-course"><?= htmlspecialchars($student['email']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="year-level"><?= htmlspecialchars($student['year_level']) ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = '';
                                                    $statusText = ucfirst($student['enrollment_status']);
                                                    switch ($student['enrollment_status']) {
                                                        case 'active':
                                                            $statusClass = 'badge-shs';
                                                            break;
                                                        case 'inactive':
                                                            $statusClass = 'badge-college';
                                                            break;
                                                        default:
                                                            $statusClass = 'badge-college';
                                                    }
                                                    ?>
                                                    <span class="education-level-badge <?= $statusClass ?>">
                                                        <?= $statusText ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- pagination container -->
                        <div id="paginationContainer" class="pagination-wrapper mt-4">
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mb-0">
                                        <!-- previous button -->
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="#" data-page="<?= $page - 1 ?>">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>

                                        <!-- page numbers -->
                                        <?php
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $page + 2);

                                        for ($i = $start_page; $i <= $end_page; $i++):
                                        ?>
                                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                <a class="page-link" href="#" data-page="<?= $i ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- next button -->
                                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="#" data-page="<?= $page + 1 ?>">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                                <div class="pagination-info text-center mt-2 text-muted small">
                                    Showing page <?= $page ?> of <?= $total_pages ?> (<?= $total_students ?> total students)
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- hidden inputs for js -->
    <input type="hidden" id="sectionId" value="<?= $section['section_id'] ?>">

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('studentSearchInput');
            const tableBody = document.getElementById('studentsTableBody');
            const paginationContainer = document.getElementById('paginationContainer');
            const totalBadge = document.getElementById('totalStudentsBadge');
            const sectionId = document.getElementById('sectionId').value;

            let debounceTimer;

            // debounce function to limit api calls
            function debounce(func, timeout = 300) {
                return (...args) => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        func.apply(this, args);
                    }, timeout);
                };
            }

            // fetch function
            function loadStudents(page, search) {
                const url = `index.php?page=ajax_section_students&section_id=${sectionId}&p=${page}&search=${encodeURIComponent(search)}`;

                // add loading state
                tableBody.style.opacity = '0.5';

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            tableBody.innerHTML = data.table_html;
                            paginationContainer.innerHTML = data.pagination_html;
                            totalBadge.textContent = data.total_students;
                        }
                        tableBody.style.opacity = '1';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        tableBody.style.opacity = '1';
                    });
            }

            // search event listener
            searchInput.addEventListener('input', debounce(() => {
                loadStudents(1, searchInput.value);
            }));

            // pagination click delegation
            paginationContainer.addEventListener('click', function(e) {
                // handle icon clicks inside links
                const link = e.target.closest('.page-link');

                if (link) {
                    e.preventDefault();

                    const parentLi = link.closest('.page-item');
                    if (parentLi && parentLi.classList.contains('disabled')) return;

                    const page = link.getAttribute('data-page');
                    if (page) {
                        loadStudents(page, searchInput.value);
                    }
                }
            });
        });
    </script>

</body>

</html>