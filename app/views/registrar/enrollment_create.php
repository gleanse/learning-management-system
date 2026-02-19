<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll New Student - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/enrollment_create.css">
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
                    <a class="nav-link" href="index.php?page=registrar_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=enrollment_create">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Enroll Student</span>
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
                        <span>Enroll New Student</span>
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
                            $firstname = $_SESSION['user_firstname'] ?? 'R';
                            $lastname  = $_SESSION['user_lastname']  ?? 'U';
                            echo strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1));
                            ?>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- page content -->
            <div class="container-fluid p-4">
                <!-- breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?page=registrar_dashboard">
                                <i class="bi bi-house-door-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Enroll New Student
                        </li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Enroll New Student</h1>
                            <p class="header-subtitle">Fill in the student's information to complete enrollment</p>
                        </div>
                        <div class="ms-auto">
                            <button type="button" class="btn-clear-form" id="clearFormBtn">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Clear Form
                            </button>
                        </div>
                    </div>
                </div>

                <!-- general error alert -->
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?= htmlspecialchars($errors['general']) ?></span>
                    </div>
                <?php endif; ?>

                <!-- fee config missing warning — shown when total is zero -->
                <?php if (!empty($errors['total_amount'])): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?= htmlspecialchars($errors['total_amount']) ?></span>
                    </div>
                <?php endif; ?>

                <!-- enrollment form card -->
                <div class="card enrollment-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-plus-fill"></i>
                            Student Enrollment Form
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <!-- wizard steps indicator -->
                        <div class="wizard-steps">
                            <div class="wizard-step active" data-step="1">
                                <div class="step-circle">
                                    <i class="bi bi-person-fill"></i>
                                    <span class="step-number">1</span>
                                </div>
                                <span class="step-label">Personal Info</span>
                            </div>
                            <div class="wizard-connector"></div>
                            <div class="wizard-step" data-step="2">
                                <div class="step-circle">
                                    <i class="bi bi-mortarboard-fill"></i>
                                    <span class="step-number">2</span>
                                </div>
                                <span class="step-label">Academic Details</span>
                            </div>
                            <div class="wizard-connector"></div>
                            <div class="wizard-step" data-step="3">
                                <div class="step-circle">
                                    <i class="bi bi-cash-stack"></i>
                                    <span class="step-number">3</span>
                                </div>
                                <span class="step-label">Payment</span>
                            </div>
                            <div class="wizard-connector"></div>
                            <div class="wizard-step" data-step="4">
                                <div class="step-circle">
                                    <i class="bi bi-file-earmark-check-fill"></i>
                                    <span class="step-number">4</span>
                                </div>
                                <span class="step-label">Documents</span>
                            </div>
                        </div>

                        <!-- form -->
                        <form method="POST" action="index.php?page=enrollment_store" id="enrollmentForm">

                            <!-- step 1: personal information -->
                            <div class="step-content active" id="step-1">
                                <div class="step-inner">
                                    <div class="step-heading">
                                        <i class="bi bi-person-fill"></i>
                                        Personal Information
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label" for="first_name">
                                                <i class="bi bi-person"></i>
                                                First Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                                                id="first_name" name="first_name"
                                                value="<?= htmlspecialchars($form_data['first_name'] ?? '') ?>"
                                                placeholder="Enter first name">
                                            <?php if (isset($errors['first_name'])): ?>
                                                <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label" for="middle_name">
                                                <i class="bi bi-person"></i>
                                                Middle Name
                                            </label>
                                            <input type="text" class="form-control"
                                                id="middle_name" name="middle_name"
                                                value="<?= htmlspecialchars($form_data['middle_name'] ?? '') ?>"
                                                placeholder="Enter middle name">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label" for="last_name">
                                                <i class="bi bi-person"></i>
                                                Last Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                                                id="last_name" name="last_name"
                                                value="<?= htmlspecialchars($form_data['last_name'] ?? '') ?>"
                                                placeholder="Enter last name">
                                            <?php if (isset($errors['last_name'])): ?>
                                                <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- duplicate name warning — populated via ajax on last_name blur -->
                                    <div class="duplicate-name-alert d-none" id="duplicateNameAlert"></div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label" for="date_of_birth">
                                                <i class="bi bi-calendar-date"></i>
                                                Date of Birth <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control <?= isset($errors['date_of_birth']) ? 'is-invalid' : '' ?>"
                                                id="date_of_birth" name="date_of_birth"
                                                value="<?= htmlspecialchars($form_data['date_of_birth'] ?? '') ?>">
                                            <?php if (isset($errors['date_of_birth'])): ?>
                                                <div class="invalid-feedback" data-php-rendered="1"><?= htmlspecialchars($errors['date_of_birth']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label" for="gender">
                                                <i class="bi bi-gender-ambiguous"></i>
                                                Gender <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select <?= isset($errors['gender']) ? 'is-invalid' : '' ?>" id="gender" name="gender">
                                                <option value="">Select gender</option>
                                                <option value="male" <?= ($form_data['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>Male</option>
                                                <option value="female" <?= ($form_data['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                            </select>
                                            <?php if (isset($errors['gender'])): ?>
                                                <div class="invalid-feedback" data-php-rendered="1"><?= htmlspecialchars($errors['gender']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label" for="lrn">
                                                <i class="bi bi-upc-scan"></i>
                                                LRN <span class="text-danger lrn-required-indicator" style="display:none;">*</span>
                                                <span class="lrn-optional-badge">Optional for College</span>
                                            </label>
                                            <input type="text" class="form-control <?= isset($errors['lrn']) ? 'is-invalid' : '' ?>"
                                                id="lrn" name="lrn"
                                                value="<?= htmlspecialchars($form_data['lrn'] ?? '') ?>"
                                                placeholder="Learner Reference Number"
                                                maxlength="12">
                                            <?php if (isset($errors['lrn'])): ?>
                                                <div class="invalid-feedback" data-php-rendered="1"><?= htmlspecialchars($errors['lrn']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="contact_number">
                                                <i class="bi bi-telephone"></i>
                                                Contact Number
                                            </label>
                                            <input type="text" class="form-control"
                                                id="contact_number" name="contact_number"
                                                value="<?= htmlspecialchars($form_data['contact_number'] ?? '') ?>"
                                                placeholder="+63 912 345 6789">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="email">
                                                <i class="bi bi-envelope"></i>
                                                Email Address
                                            </label>
                                            <input type="email" class="form-control"
                                                id="email" name="email"
                                                value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                                                placeholder="student@example.com">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="home_address">
                                            <i class="bi bi-geo-alt"></i>
                                            Home Address
                                        </label>
                                        <textarea class="form-control" id="home_address" name="home_address"
                                            rows="3" placeholder="Enter complete address"><?= htmlspecialchars($form_data['home_address'] ?? '') ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="guardian_name">
                                                <i class="bi bi-person-heart"></i>
                                                Guardian / Parent Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control <?= isset($errors['guardian_name']) ? 'is-invalid' : '' ?>"
                                                id="guardian_name" name="guardian_name"
                                                value="<?= htmlspecialchars($form_data['guardian_name'] ?? '') ?>"
                                                placeholder="Guardian name">
                                            <?php if (isset($errors['guardian_name'])): ?>
                                                <div class="invalid-feedback"><?= htmlspecialchars($errors['guardian_name']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="guardian_contact">
                                                <i class="bi bi-telephone-fill"></i>
                                                Guardian Contact <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control <?= isset($errors['guardian_contact']) ? 'is-invalid' : '' ?>"
                                                id="guardian_contact" name="guardian_contact"
                                                value="<?= htmlspecialchars($form_data['guardian_contact'] ?? '') ?>"
                                                placeholder="Guardian phone number">
                                            <?php if (isset($errors['guardian_contact'])): ?>
                                                <div class="invalid-feedback"><?= htmlspecialchars($errors['guardian_contact']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- step 2: academic details -->
                            <div class="step-content" id="step-2">
                                <div class="step-inner">
                                    <div class="step-heading">
                                        <i class="bi bi-mortarboard-fill"></i>
                                        Academic Details
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="education_level">
                                                <i class="bi bi-mortarboard"></i>
                                                Education Level <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select <?= isset($errors['education_level']) ? 'is-invalid' : '' ?>"
                                                id="education_level" name="education_level">
                                                <option value="">Select education level</option>
                                                <option value="senior_high" <?= ($form_data['education_level'] ?? '') === 'senior_high' ? 'selected' : '' ?>>Senior High School</option>
                                                <option value="college" <?= ($form_data['education_level'] ?? '') === 'college'     ? 'selected' : '' ?>>College</option>
                                            </select>
                                            <?php if (isset($errors['education_level'])): ?>
                                                <div class="invalid-feedback"><?= htmlspecialchars($errors['education_level']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="year_level">
                                                <i class="bi bi-bar-chart-steps"></i>
                                                Grade / Year Level <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select <?= isset($errors['year_level']) ? 'is-invalid' : '' ?>"
                                                id="year_level" name="year_level">
                                                <option value="">Select grade level</option>
                                            </select>
                                            <?php if (isset($errors['year_level'])): ?>
                                                <div class="invalid-feedback"><?= htmlspecialchars($errors['year_level']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="strand_course">
                                                <i class="bi bi-award"></i>
                                                Strand / Course <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select <?= isset($errors['strand_course']) ? 'is-invalid' : '' ?>"
                                                id="strand_course" name="strand_course">
                                                <option value="">Select strand or course</option>
                                            </select>
                                            <?php if (isset($errors['strand_course'])): ?>
                                                <div class="invalid-feedback"><?= htmlspecialchars($errors['strand_course']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="school_year">
                                                <i class="bi bi-calendar-range"></i>
                                                School Year <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select <?= isset($errors['school_year']) ? 'is-invalid' : '' ?>"
                                                id="school_year" name="school_year">
                                                <option value="">Select school year</option>
                                                <?php foreach ($school_years as $sy): ?>
                                                    <option value="<?= htmlspecialchars($sy['school_year']) ?>"
                                                        <?= ($form_data['school_year'] ?? '') === $sy['school_year'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($sy['school_year']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (isset($errors['school_year'])): ?>
                                                <div class="invalid-feedback"><?= htmlspecialchars($errors['school_year']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="semester">
                                                <i class="bi bi-calendar2-week"></i>
                                                Semester <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select <?= isset($errors['semester']) ? 'is-invalid' : '' ?>"
                                                id="semester" name="semester">
                                                <option value="">Select semester</option>
                                                <option value="First" <?= ($form_data['semester'] ?? '') === 'First'  ? 'selected' : '' ?>>First Semester</option>
                                                <option value="Second" <?= ($form_data['semester'] ?? '') === 'Second' ? 'selected' : '' ?>>Second Semester</option>
                                            </select>
                                            <?php if (isset($errors['semester'])): ?>
                                                <div class="invalid-feedback"><?= htmlspecialchars($errors['semester']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6 mb-3" id="sectionWrapper">
                                            <label class="form-label" for="section_id">
                                                <i class="bi bi-diagram-3"></i>
                                                Section
                                            </label>
                                            <select class="form-select" id="section_id" name="section_id">
                                                <option value="">To be assigned</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="previous_school">
                                            <i class="bi bi-building"></i>
                                            Previous School
                                        </label>
                                        <input type="text" class="form-control"
                                            id="previous_school" name="previous_school"
                                            value="<?= htmlspecialchars($form_data['previous_school'] ?? '') ?>"
                                            placeholder="Name of previous school">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="special_notes">
                                            <i class="bi bi-sticky"></i>
                                            Special Requirements / Notes
                                        </label>
                                        <textarea class="form-control" id="special_notes" name="special_notes"
                                            rows="3" placeholder="Any special requirements or notes"><?= htmlspecialchars($form_data['special_notes'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- step 3: payment -->
                            <div class="step-content" id="step-3">
                                <div class="step-inner">
                                    <div class="step-heading">
                                        <i class="bi bi-cash-stack"></i>
                                        Payment
                                    </div>

                                    <!-- fee breakdown — auto-loaded based on course and school year -->
                                    <div class="fee-breakdown mb-4">
                                        <div class="fee-breakdown-title">
                                            <i class="bi bi-receipt"></i>
                                            Tuition Fee Breakdown
                                        </div>
                                        <div class="fee-items">
                                            <div class="fee-item">
                                                <div class="fee-item-label">
                                                    <i class="bi bi-mortarboard"></i>
                                                    Tuition Fee
                                                    <small>Basic tuition for academic year</small>
                                                </div>
                                                <span class="fee-item-amount" id="feeTuition">
                                                    ₱<?= number_format($fee_config['tuition_fee'], 2) ?>
                                                </span>
                                            </div>
                                            <div class="fee-item">
                                                <div class="fee-item-label">
                                                    <i class="bi bi-flask"></i>
                                                    Miscellaneous
                                                    <small>Laboratory, library, etc.</small>
                                                </div>
                                                <span class="fee-item-amount" id="feeMisc">
                                                    ₱<?= number_format($fee_config['miscellaneous'], 2) ?>
                                                </span>
                                            </div>
                                            <div class="fee-item">
                                                <div class="fee-item-label">
                                                    <i class="bi bi-card-list"></i>
                                                    Other Fees
                                                    <small>ID, yearbook, etc.</small>
                                                </div>
                                                <span class="fee-item-amount" id="feeOther">
                                                    ₱<?= number_format($fee_config['other_fees'], 2) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="fee-total">
                                            <span>Total Amount Due</span>
                                            <span class="fee-total-amount" id="feeTotal">
                                                ₱<?= number_format($fee_config['total'], 2) ?>
                                            </span>
                                        </div>
                                        <!-- hidden field — submitted with the form -->
                                        <input type="hidden" name="total_amount" id="totalAmountInput"
                                            value="<?= $fee_config['total'] ?>">
                                    </div>

                                    <!-- initial payment recorded by registrar -->
                                    <div class="mb-3">
                                        <label class="form-label" for="initial_amount_paid">
                                            <i class="bi bi-cash-coin"></i>
                                            Amount Paid Today
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="initial_amount_paid"
                                                name="initial_amount_paid" min="0" step="0.01"
                                                value="<?= htmlspecialchars($form_data['initial_amount_paid'] ?? '0') ?>"
                                                placeholder="0.00">
                                        </div>
                                    </div>

                                    <!-- payment notes -->
                                    <div class="mb-3">
                                        <label class="form-label" for="payment_notes">
                                            <i class="bi bi-sticky"></i>
                                            Payment Notes
                                        </label>
                                        <textarea class="form-control" id="payment_notes" name="payment_notes"
                                            rows="2" placeholder="Optional notes for this payment"><?= htmlspecialchars($form_data['payment_notes'] ?? '') ?></textarea>
                                    </div>

                                    <!-- payment summary -->
                                    <div class="payment-summary" id="paymentSummary">
                                        <div class="payment-summary-title">
                                            <i class="bi bi-calculator"></i>
                                            Payment Summary
                                        </div>
                                        <div class="summary-row">
                                            <span>Total Amount Due</span>
                                            <span id="summaryTotal">₱<?= number_format($fee_config['total'], 2) ?></span>
                                        </div>
                                        <div class="summary-row">
                                            <span>Amount Paid Today</span>
                                            <span id="summaryInitial">₱0.00</span>
                                        </div>
                                        <div class="summary-divider"></div>
                                        <div class="summary-row summary-total">
                                            <span>Remaining Balance</span>
                                            <span id="summaryBalance">₱<?= number_format($fee_config['total'], 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- step 4: documents -->
                            <div class="step-content" id="step-4">
                                <div class="step-inner">
                                    <div class="step-heading">
                                        <i class="bi bi-file-earmark-check-fill"></i>
                                        Documents Checklist
                                    </div>
                                    <div class="documents-info mb-4">
                                        <i class="bi bi-info-circle-fill"></i>
                                        Check each document as the student submits it. Incomplete documents can be updated later.
                                    </div>

                                    <div class="documents-checklist">
                                        <label class="document-item">
                                            <input type="checkbox" name="docs[psa_birth_certificate]" value="1"
                                                <?= !empty($form_data['docs']['psa_birth_certificate']) ? 'checked' : '' ?>>
                                            <div class="document-info">
                                                <i class="bi bi-file-earmark-person-fill"></i>
                                                <div>
                                                    <span class="document-name">PSA Birth Certificate</span>
                                                    <span class="document-note">Original & Photocopy</span>
                                                </div>
                                            </div>
                                            <span class="document-status">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </span>
                                        </label>

                                        <label class="document-item">
                                            <input type="checkbox" name="docs[form_138_report_card]" value="1"
                                                <?= !empty($form_data['docs']['form_138_report_card']) ? 'checked' : '' ?>>
                                            <div class="document-info">
                                                <i class="bi bi-file-earmark-text-fill"></i>
                                                <div>
                                                    <span class="document-name">Form 138 / Report Card</span>
                                                    <span class="document-note">Original</span>
                                                </div>
                                            </div>
                                            <span class="document-status">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </span>
                                        </label>

                                        <label class="document-item">
                                            <input type="checkbox" name="docs[good_moral_certificate]" value="1"
                                                <?= !empty($form_data['docs']['good_moral_certificate']) ? 'checked' : '' ?>>
                                            <div class="document-info">
                                                <i class="bi bi-patch-check-fill"></i>
                                                <div>
                                                    <span class="document-name">Certificate of Good Moral Character</span>
                                                    <span class="document-note">Original</span>
                                                </div>
                                            </div>
                                            <span class="document-status">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </span>
                                        </label>

                                        <label class="document-item">
                                            <input type="checkbox" name="docs[id_pictures]" value="1"
                                                <?= !empty($form_data['docs']['id_pictures']) ? 'checked' : '' ?>>
                                            <div class="document-info">
                                                <i class="bi bi-image-fill"></i>
                                                <div>
                                                    <span class="document-name">2x2 ID Pictures</span>
                                                    <span class="document-note">4 copies</span>
                                                </div>
                                            </div>
                                            <span class="document-status">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </span>
                                        </label>

                                        <label class="document-item">
                                            <input type="checkbox" name="docs[medical_certificate]" value="1"
                                                <?= !empty($form_data['docs']['medical_certificate']) ? 'checked' : '' ?>>
                                            <div class="document-info">
                                                <i class="bi bi-heart-pulse-fill"></i>
                                                <div>
                                                    <span class="document-name">Medical Certificate</span>
                                                    <span class="document-note">Issued by a licensed physician</span>
                                                </div>
                                            </div>
                                            <span class="document-status">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </span>
                                        </label>
                                    </div>

                                    <div class="documents-summary mt-4">
                                        <i class="bi bi-clipboard-check"></i>
                                        <span id="docSummaryText">0 of 5 documents submitted</span>
                                    </div>
                                </div>
                            </div>

                            <!-- wizard navigation footer -->
                            <div class="wizard-footer">
                                <button type="button" class="btn-save-draft" id="saveDraftBtn">
                                    <i class="bi bi-floppy"></i>
                                    Save as Draft
                                </button>
                                <div class="wizard-nav-btns">
                                    <button type="button" class="btn-wizard-back d-none" id="backBtn">
                                        <i class="bi bi-arrow-left"></i>
                                        Back
                                    </button>
                                    <button type="button" class="btn-wizard-next" id="nextBtn">
                                        Next
                                        <i class="bi bi-arrow-right"></i>
                                    </button>
                                    <button type="submit" class="btn-complete-enrollment d-none" id="submitBtn">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Complete Enrollment
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // pass php data to js
        const ENROLLMENT_DATA = {
            feeConfig: <?= json_encode($fee_config) ?>,
            formData: <?= json_encode($form_data) ?>,
            ajaxUrls: {
                getSections: 'index.php?page=enrollment_get_sections',
                getSubjects: 'index.php?page=enrollment_get_subjects',
                getFees: 'index.php?page=enrollment_get_fees',
                saveDraft: 'index.php?page=enrollment_save_draft',
                checkDuplicateName: 'index.php?page=enrollment_check_duplicate_name',
            }
        };
    </script>
    <script src="js/enrollment-create.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($errors) && !isset($errors['general']) && !isset($errors['total_amount'])): ?>
                showToast('warning', 'Please complete all required fields before submitting.');
            <?php endif; ?>

            <?php if (!empty($draft) && !$is_validation_error): ?>
                showToast('success', 'Draft restored. Continue where you left off.');
            <?php endif; ?>
        });
    </script>

</body>

</html>