<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Profile - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/student_profiles.css">
    <link rel="stylesheet" href="css/pages/student_profiles.css">
</head>

<body>
    <div class="d-flex">
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
                    <a class="nav-link" href="index.php?page=registrar_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=enrollment_create">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Enroll Student</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=payment_process">
                        <i class="bi bi-cash-stack"></i>
                        <span>Process Payment</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=student_profiles">
                        <i class="bi bi-people-fill"></i>
                        <span>Student Profiles</span>
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

        <div class="main-content flex-grow-1">
            <nav class="navbar top-navbar">
                <div class="container-fluid">
                    <div class="navbar-brand mb-0">
                        <div class="page-icon"><i class="bi bi-pencil-square"></i></div>
                        <span>Edit Student Profile</span>
                    </div>
                    <div class="user-info-wrapper">
                        <div class="user-details">
                            <span class="user-name"><?= htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']) ?></span>
                            <span class="user-role"><i class="bi bi-person-badge-fill"></i> <?= ucfirst($_SESSION['user_role']) ?></span>
                        </div>
                        <div class="user-avatar">
                            <?= strtoupper(substr($_SESSION['user_firstname'] ?? 'R', 0, 1) . substr($_SESSION['user_lastname'] ?? 'G', 0, 1)) ?>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?page=student_profiles"><i class="bi bi-people-fill"></i> Student Profiles</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php?page=view_student_profile&student_id=<?= $student['student_id'] ?>">View Profile</a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>

                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Edit Profile</h1>
                            <p class="header-subtitle">
                                <?= htmlspecialchars($student['student_number']) ?> &mdash;
                                <?= htmlspecialchars($student['year_level'] . ' ' . $student['strand_course']) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger mb-4">
                        <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-pencil-fill"></i> Student Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="index.php?page=save_student_profile">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">

                            <p class="form-section-title">Full Name</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                                        name="first_name" value="<?= htmlspecialchars($student['first_name'] ?? '') ?>" required>
                                    <?php if (isset($errors['first_name'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle Name <span class="text-muted small">(optional)</span></label>
                                    <input type="text" class="form-control" name="middle_name"
                                        value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                                        name="last_name" value="<?= htmlspecialchars($student['last_name'] ?? '') ?>" required>
                                    <?php if (isset($errors['last_name'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <p class="form-section-title">Contact Information</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                        name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>">
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" name="contact_number"
                                        value="<?= htmlspecialchars($student['contact_number'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Home Address</label>
                                    <textarea class="form-control" name="home_address" rows="2"><?= htmlspecialchars($student['home_address'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <p class="form-section-title">Personal Details</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth"
                                        value="<?= htmlspecialchars($student['date_of_birth'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender">
                                        <option value="">— Select —</option>
                                        <option value="male" <?= ($student['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= ($student['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="other" <?= ($student['gender'] ?? '') === 'other'  ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Previous School</label>
                                    <input type="text" class="form-control" name="previous_school"
                                        value="<?= htmlspecialchars($student['previous_school'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Special Notes</label>
                                    <textarea class="form-control" name="special_notes" rows="2"><?= htmlspecialchars($student['special_notes'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-2">
                                <a href="index.php?page=view_student_profile&student_id=<?= $student['student_id'] ?>"
                                    class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle-fill"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>