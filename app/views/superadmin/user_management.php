<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/user_management.css">
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
                    <a class="nav-link" href="index.php?page=superadmin_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=user_management">
                        <i class="bi bi-people-fill"></i>
                        <span>User Management</span>
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
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <span>User Management</span>
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
                            $firstname = $_SESSION['user_firstname'] ?? 'S';
                            $lastname = $_SESSION['user_lastname'] ?? 'A';
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
                            <a href="index.php?page=superadmin_dashboard">
                                <i class="bi bi-house-door-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            User Management
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
                            <h1 class="header-title">User Management</h1>
                            <p class="header-subtitle">Manage user accounts and create student logins</p>
                        </div>
                    </div>
                    <div class="header-stats">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value"><?= number_format($total_users) ?></span>
                                <span class="stat-label">total users</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="bi bi-person-x-fill"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value"><?= number_format($total_without_accounts) ?></span>
                                <span class="stat-label">without accounts</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- tabs navigation -->
                <ul class="nav nav-tabs custom-tabs" id="userManagementTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#usersPanel" type="button" role="tab">
                            <i class="bi bi-person-lines-fill"></i>
                            All Users
                            <span class="badge bg-primary ms-2"><?= number_format($total_users) ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="students-tab" data-bs-toggle="tab" data-bs-target="#studentsPanel" type="button" role="tab">
                            <i class="bi bi-person-plus-fill"></i>
                            Students Without Accounts
                            <span class="badge bg-warning ms-2"><?= number_format($total_without_accounts) ?></span>
                        </button>
                    </li>
                </ul>

                <!-- tabs content -->
                <div class="tab-content" id="userManagementTabsContent">
                    <!-- all users tab -->
                    <div class="tab-pane fade show active" id="usersPanel" role="tabpanel">
                        <div class="card users-card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-person-lines-fill"></i>
                                    User Accounts
                                </h5>
                                <button class="btn btn-light" id="createUserBtn">
                                    <i class="bi bi-person-plus-fill"></i>
                                    Create User
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- filters -->
                                <div class="filters-wrapper">
                                    <div class="search-wrapper">
                                        <i class="bi bi-search"></i>
                                        <input type="text" class="form-control" id="userSearch" placeholder="Search by Name, Username, or Email...">
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="bi bi-funnel-fill"></i>
                                            Role Filter:
                                        </label>
                                        <select class="form-select" id="roleFilter">
                                            <option value="">All roles</option>
                                            <option value="student">Student</option>
                                            <option value="teacher">Teacher</option>
                                            <option value="registrar">Registrar</option>
                                            <option value="admin">Admin</option>
                                            <option value="superadmin">Super Admin</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- users table -->
                                <div class="table-responsive" id="usersTableWrapper">
                                    <div class="loading-state">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="loading-text">loading users...</p>
                                    </div>
                                </div>

                                <!-- pagination -->
                                <div class="pagination-wrapper d-none" id="usersPaginationWrapper">
                                    <div class="pagination-info">
                                        showing <span id="usersShowingStart">0</span> to <span id="usersShowingEnd">0</span> of <span id="usersTotalCount">0</span> users
                                    </div>
                                    <nav>
                                        <ul class="pagination" id="usersPagination">
                                            <!-- dynamically populated -->
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- students without accounts tab -->
                    <div class="tab-pane fade" id="studentsPanel" role="tabpanel">
                        <div class="card students-card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-person-plus-fill"></i>
                                    Students Without User Accounts
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- search -->
                                <div class="filters-wrapper">
                                    <div class="search-wrapper">
                                        <i class="bi bi-search"></i>
                                        <input type="text" class="form-control" id="studentSearch" placeholder="Search by Name, Student Number, or LRN...">
                                    </div>
                                </div>

                                <!-- students table -->
                                <div class="table-responsive" id="studentsTableWrapper">
                                    <div class="loading-state">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="loading-text">Loading students...</p>
                                    </div>
                                </div>

                                <!-- pagination -->
                                <div class="pagination-wrapper d-none" id="studentsPaginationWrapper">
                                    <div class="pagination-info">
                                        showing <span id="studentsShowingStart">0</span> to <span id="studentsShowingEnd">0</span> of <span id="studentsTotalCount">0</span> students
                                    </div>
                                    <nav>
                                        <ul class="pagination" id="studentsPagination">
                                            <!-- dynamically populated -->
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- create user modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus-fill"></i>
                        Create New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createUserForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person-fill"></i>
                                    First Name
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="first_name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person-fill"></i>
                                    Middle Name
                                </label>
                                <input type="text" class="form-control" name="middle_name">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person-fill"></i>
                                    Last Name
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="last_name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person-badge-fill"></i>
                                    Username
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="username" id="createUsername" required>
                                <div class="availability-feedback"></div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-envelope-fill"></i>
                                    Email
                                </label>
                                <input type="email" class="form-control" name="email" id="createEmail">
                                <div class="availability-feedback"></div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-shield-lock-fill"></i>
                                    Password
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" class="form-control" name="password" id="createPassword" required>
                                    <button type="button" class="btn-toggle-password" data-target="createPassword">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                                <div class="password-strength-meter">
                                    <div class="strength-bar"></div>
                                </div>
                                <div class="password-requirements">
                                    <small class="requirement" data-rule="length">
                                        <i class="bi bi-x-circle-fill"></i>
                                        At least 8 characters
                                    </small>
                                    <small class="requirement" data-rule="uppercase">
                                        <i class="bi bi-x-circle-fill"></i>
                                        One uppercase letter
                                    </small>
                                    <small class="requirement" data-rule="lowercase">
                                        <i class="bi bi-x-circle-fill"></i>
                                        One lowercase letter
                                    </small>
                                    <small class="requirement" data-rule="number">
                                        <i class="bi bi-x-circle-fill"></i>
                                        One number
                                    </small>
                                    <small class="requirement" data-rule="special">
                                        <i class="bi bi-x-circle-fill"></i>
                                        One special character
                                    </small>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person-workspace"></i>
                                    Role
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="role" required>
                                    <option value="">select role...</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="registrar">Registrar</option>
                                    <option value="admin">Admin</option>
                                    <option value="superadmin">Super Admin</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle-fill"></i>
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- create student account modal -->
    <div class="modal fade" id="createStudentAccountModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus-fill"></i>
                        Create Student Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createStudentAccountForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="student_id" id="studentAccountStudentId">
                    <div class="modal-body">
                        <div class="student-info-display mb-4">
                            <h6 class="student-info-title">
                                <i class="bi bi-info-circle-fill"></i>
                                Student Information
                            </h6>
                            <div class="student-info-grid">
                                <div class="info-row">
                                    <span class="info-label">Name:</span>
                                    <span class="info-value" id="studentAccountName">-</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Student Number:</span>
                                    <span class="info-value" id="studentAccountNumber">-</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Year Level:</span>
                                    <span class="info-value" id="studentAccountYear">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-person-badge-fill"></i>
                                Username
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="username" id="studentAccountUsername" required>
                            <div class="availability-feedback"></div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-envelope-fill"></i>
                                Email
                            </label>
                            <input type="email" class="form-control" name="email" id="studentAccountEmail">
                            <div class="availability-feedback"></div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-shield-lock-fill"></i>
                                Password
                                <span class="text-danger">*</span>
                            </label>
                            <div class="password-input-wrapper">
                                <input type="password" class="form-control" name="password" id="studentAccountPassword" required>
                                <button type="button" class="btn-toggle-password" data-target="studentAccountPassword">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                            </div>
                            <div class="password-strength-meter">
                                <div class="strength-bar"></div>
                            </div>
                            <div class="password-requirements">
                                <small class="requirement" data-rule="length">
                                    <i class="bi bi-x-circle-fill"></i>
                                    At least 8 characters
                                </small>
                                <small class="requirement" data-rule="uppercase">
                                    <i class="bi bi-x-circle-fill"></i>
                                    One uppercase letter
                                </small>
                                <small class="requirement" data-rule="lowercase">
                                    <i class="bi bi-x-circle-fill"></i>
                                    One lowercase letter
                                </small>
                                <small class="requirement" data-rule="number">
                                    <i class="bi bi-x-circle-fill"></i>
                                    One number
                                </small>
                                <small class="requirement" data-rule="special">
                                    <i class="bi bi-x-circle-fill"></i>
                                    One special character
                                </small>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle-fill"></i>
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- edit user modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square"></i>
                        Edit User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editUserForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person-fill"></i>
                                    First Name
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="first_name" id="editFirstName" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person-fill"></i>
                                    Middle Name
                                </label>
                                <input type="text" class="form-control" name="middle_name" id="editMiddleName">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person-fill"></i>
                                    Last Name
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="last_name" id="editLastName" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person-badge-fill"></i>
                                    Username
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="username" id="editUsername" required>
                                <div class="availability-feedback"></div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-envelope-fill"></i>
                                    Email
                                </label>
                                <input type="email" class="form-control" name="email" id="editEmail">
                                <div class="availability-feedback"></div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person-workspace"></i>
                                    Role
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="role" id="editRole" required>
                                    <option value="">select role...</option>
                                    <option value="student">Student</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="registrar">Registrar</option>
                                    <option value="admin">Admin</option>
                                    <option value="superadmin">Super Admin</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-toggle-on"></i>
                                    Status
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="status" id="editStatus" required>
                                    <option value="">select status...</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-shield-lock-fill"></i>
                                New Password
                                <small class="text-muted">(Leave blank to keep current)</small>
                            </label>
                            <div class="password-input-wrapper">
                                <input type="password" class="form-control" name="password" id="editPassword">
                                <button type="button" class="btn-toggle-password" data-target="editPassword">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                            </div>
                            <div class="password-strength-meter">
                                <div class="strength-bar"></div>
                            </div>
                            <div class="password-requirements">
                                <small class="requirement" data-rule="length">
                                    <i class="bi bi-x-circle-fill"></i>
                                    At least 8 characters
                                </small>
                                <small class="requirement" data-rule="uppercase">
                                    <i class="bi bi-x-circle-fill"></i>
                                    One uppercase letter
                                </small>
                                <small class="requirement" data-rule="lowercase">
                                    <i class="bi bi-x-circle-fill"></i>
                                    One lowercase letter
                                </small>
                                <small class="requirement" data-rule="number">
                                    <i class="bi bi-x-circle-fill"></i>
                                    One number
                                </small>
                                <small class="requirement" data-rule="special">
                                    <i class="bi bi-x-circle-fill"></i>
                                    One special character
                                </small>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle-fill"></i>
                            Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrfToken = '<?= $_SESSION['csrf_token'] ?>';
    </script>
    <script src="js/user-management-ajax.js"></script>

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