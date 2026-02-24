<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Configuration - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/fee_config.css">
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
                    <a class="nav-link" href="index.php?page=teacher_schedules">
                        <i class="bi bi-calendar-week-fill"></i>
                        <span>Manage Schedules</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=academic_period">
                        <i class="bi bi-calendar2-range-fill"></i>
                        <span>Academic Period</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=fee_config">
                        <i class="bi bi-cash-coin"></i>
                        <span>Fee Configuration</span>
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
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <span>Fee Configuration</span>
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
                            <a href="index.php?page=admin_dashboard">
                                <i class="bi bi-house-door-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Fee Configuration</li>
                    </ol>
                </nav>

                <!-- page header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Fee Configuration</h1>
                            <p class="header-subtitle">manage tuition and fee amounts per course and year level</p>
                        </div>
                    </div>
                </div>

                <!-- senior high table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-mortarboard-fill"></i>
                            Senior High School
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table fee-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Year Level</th>
                                        <th>Strand</th>
                                        <th class="text-end">Tuition Fee</th>
                                        <th class="text-end">Miscellaneous</th>
                                        <th class="text-end">Other Fees</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($grouped['senior_high'])): ?>
                                        <tr>
                                            <td colspan="7">
                                                <div class="empty-state py-4">
                                                    <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                                                    <p class="empty-state-text">no senior high fee configuration found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($grouped['senior_high'] as $fee): ?>
                                            <?php $total = $fee['tuition_fee'] + $fee['miscellaneous'] + $fee['other_fees']; ?>
                                            <tr data-fee-id="<?= $fee['fee_id'] ?>">
                                                <td>
                                                    <span class="level-badge badge-shs"><?= htmlspecialchars($fee['school_year']) ?></span>
                                                </td>
                                                <td class="fw-semibold"><?= htmlspecialchars($fee['strand_course']) ?></td>
                                                <td class="text-end fee-cell" data-field="tuition_fee">₱<?= number_format($fee['tuition_fee'], 2) ?></td>
                                                <td class="text-end fee-cell" data-field="miscellaneous">₱<?= number_format($fee['miscellaneous'], 2) ?></td>
                                                <td class="text-end fee-cell" data-field="other_fees">₱<?= number_format($fee['other_fees'], 2) ?></td>
                                                <td class="text-end fee-total fw-bold">₱<?= number_format($total, 2) ?></td>
                                                <td class="text-center">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary edit-fee-btn"
                                                        data-fee-id="<?= $fee['fee_id'] ?>">
                                                        <i class="bi bi-pencil-fill"></i> Edit
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

                <!-- college table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-building"></i>
                            College
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table fee-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Year Level</th>
                                        <th>Course</th>
                                        <th class="text-end">Tuition Fee</th>
                                        <th class="text-end">Miscellaneous</th>
                                        <th class="text-end">Other Fees</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($grouped['college'])): ?>
                                        <tr>
                                            <td colspan="7">
                                                <div class="empty-state py-4">
                                                    <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                                                    <p class="empty-state-text">no college fee configuration found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($grouped['college'] as $fee): ?>
                                            <?php $total = $fee['tuition_fee'] + $fee['miscellaneous'] + $fee['other_fees']; ?>
                                            <tr data-fee-id="<?= $fee['fee_id'] ?>">
                                                <td>
                                                    <span class="level-badge badge-college"><?= htmlspecialchars($fee['school_year']) ?></span>
                                                </td>
                                                <td class="fw-semibold"><?= htmlspecialchars($fee['strand_course']) ?></td>
                                                <td class="text-end fee-cell" data-field="tuition_fee">₱<?= number_format($fee['tuition_fee'], 2) ?></td>
                                                <td class="text-end fee-cell" data-field="miscellaneous">₱<?= number_format($fee['miscellaneous'], 2) ?></td>
                                                <td class="text-end fee-cell" data-field="other_fees">₱<?= number_format($fee['other_fees'], 2) ?></td>
                                                <td class="text-end fee-total fw-bold">₱<?= number_format($total, 2) ?></td>
                                                <td class="text-center">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary edit-fee-btn"
                                                        data-fee-id="<?= $fee['fee_id'] ?>">
                                                        <i class="bi bi-pencil-fill"></i> Edit
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

    <!-- edit fee modal -->
    <div class="modal fade" id="editFeeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-fill"></i>
                        Edit Fee Configuration
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- meta info -->
                    <div class="fee-meta-info mb-4">
                        <div class="fee-meta-row">
                            <span class="fee-meta-label">Year Level</span>
                            <span class="fee-meta-value" id="modalYearLevel">—</span>
                        </div>
                        <div class="fee-meta-row">
                            <span class="fee-meta-label">Strand / Course</span>
                            <span class="fee-meta-value" id="modalStrandCourse">—</span>
                        </div>
                    </div>

                    <input type="hidden" id="modalFeeId">

                    <!-- tuition fee -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-cash"></i>
                            Tuition Fee
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="modalTuitionFee" min="0.01" max="100000" step="0.01" placeholder="0.00">
                        </div>
                        <div class="invalid-feedback" id="errorTuitionFee"></div>
                    </div>

                    <!-- miscellaneous -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-receipt"></i>
                            Miscellaneous
                            <span class="text-muted small">(optional)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="modalMiscellaneous" min="0" max="100000" step="0.01" placeholder="0.00">
                        </div>
                    </div>

                    <!-- other fees -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-plus-circle"></i>
                            Other Fees
                            <span class="text-muted small">(optional)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="modalOtherFees" min="0" max="100000" step="0.01" placeholder="0.00">
                        </div>
                    </div>

                    <!-- live total -->
                    <div class="fee-total-preview">
                        <span class="total-label">Total Amount</span>
                        <span class="total-value" id="modalTotal">₱0.00</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary btn-action" id="saveFeeBtn">
                        <i class="bi bi-save-fill"></i>
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/fee-config.js"></script>

</body>

</html>