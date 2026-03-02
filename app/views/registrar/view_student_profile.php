<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Profile - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/shared/top-navbar.css">
    <link rel="stylesheet" href="css/pages/student_profiles.css">
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="d-flex">
        <div class="sidenav" id="sidebar">
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
            <?php require __DIR__ . '/../shared/top_navbar.php'; ?>

            <div class="container-fluid p-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?page=student_profiles"><i class="bi bi-people-fill"></i> Student Profiles</a>
                        </li>
                        <li class="breadcrumb-item active">View Profile</li>
                    </ol>
                </nav>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- student info -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-person-fill"></i> Student Information</h5>
                                <a href="index.php?page=edit_student_profile&student_id=<?= $student['student_id'] ?>"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-fill"></i> Edit Profile
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Student Number</label>
                                        <p class="fw-bold mb-0"><?= htmlspecialchars($student['student_number']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Full Name</label>
                                        <p class="fw-bold mb-0">
                                            <?= htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']) ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Year Level</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['year_level']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Strand/Course</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['strand_course']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Section</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['section_name'] ?? 'Not Assigned') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Education Level</label>
                                        <p class="mb-0"><?= $student['education_level'] === 'senior_high' ? 'Senior High' : 'College' ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Email</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['email'] ?? '—') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Contact Number</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['contact_number'] ?? '—') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Date of Birth</label>
                                        <p class="mb-0"><?= $student['date_of_birth'] ? date('F j, Y', strtotime($student['date_of_birth'])) : '—' ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Gender</label>
                                        <p class="mb-0"><?= $student['gender'] ? ucfirst($student['gender']) : '—' ?></p>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label text-muted small">Home Address</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['home_address'] ?? '—') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Previous School</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['previous_school'] ?? '—') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Guardian</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['guardian'] ?? '—') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Guardian Contact</label>
                                        <p class="mb-0"><?= htmlspecialchars($student['guardian_contact'] ?? '—') ?></p>
                                    </div>
                                    <?php if (!empty($student['special_notes'])): ?>
                                        <div class="col-12">
                                            <label class="form-label text-muted small">Special Notes</label>
                                            <p class="mb-0"><?= htmlspecialchars($student['special_notes']) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <!-- enrollment documents section -->
                                    <?php
                                    $doc_fields = [
                                        'psa_birth_certificate'  => 'PSA Birth Certificate',
                                        'form_138_report_card'   => 'Form 138 / Report Card',
                                        'good_moral_certificate' => 'Good Moral Certificate',
                                        'id_pictures'            => 'ID Pictures',
                                        'medical_certificate'    => 'Medical Certificate',
                                    ];

                                    $total_docs = count($doc_fields);
                                    $submitted  = 0;

                                    if ($enrollment_docs) {
                                        foreach (array_keys($doc_fields) as $field) {
                                            if (!empty($enrollment_docs[$field])) $submitted++;
                                        }
                                    }

                                    $all_submitted = $submitted === $total_docs;
                                    ?>
                                    <div class="col-12">
                                        <hr class="my-2">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="form-label text-muted small mb-0">Enrollment Documents</label>
                                                <span id="docsBadge" class="badge <?= $all_submitted ? 'bg-success' : ($submitted > 0 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                                    <?= $submitted ?> / <?= $total_docs ?> Submitted
                                                </span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="toggleDocsEdit">
                                                <i class="bi bi-pencil-fill"></i> Edit Documents
                                            </button>
                                        </div>

                                        <!-- view mode -->
                                        <div id="docsViewMode">
                                            <div class="row g-2">
                                                <?php foreach ($doc_fields as $field => $label): ?>
                                                    <?php $checked = !empty($enrollment_docs[$field]); ?>
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-center gap-2 doc-status-row">
                                                            <i class="<?= $checked ? 'bi bi-check-circle-fill text-success' : 'bi bi-x-circle-fill text-danger' ?>"></i>
                                                            <span class="small <?= $checked ? '' : 'text-muted' ?>"><?= $label ?></span>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <!-- edit mode -->
                                        <div id="docsEditMode" style="display:none;">
                                            <form id="docsForm">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                                                <div class="row g-2 mb-3">
                                                    <?php foreach ($doc_fields as $field => $label): ?>
                                                        <?php $checked = !empty($enrollment_docs[$field]); ?>
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input
                                                                    class="form-check-input doc-checkbox"
                                                                    type="checkbox"
                                                                    name="<?= $field ?>"
                                                                    id="doc_<?= $field ?>"
                                                                    <?= $checked ? 'checked' : '' ?>>
                                                                <label class="form-check-label small" for="doc_<?= $field ?>">
                                                                    <?= $label ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="submit" class="btn btn-sm btn-primary" id="saveDocsBtn">
                                                        <i class="bi bi-save-fill"></i> Save
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="cancelDocsEdit">
                                                        Cancel
                                                    </button>
                                                    <span id="docsSaveMsg" class="small" style="display:none;"></span>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <!-- end enrollment documents -->

                                </div>
                            </div>
                        </div>

                        <!-- payment history -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-receipt"></i> Payment History</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($payment_history)): ?>
                                    <div class="empty-state py-4">
                                        <div class="empty-state-icon"><i class="bi bi-receipt"></i></div>
                                        <p class="empty-state-text">No payment transactions yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>School Year</th>
                                                    <th>Semester</th>
                                                    <th>Amount Paid</th>
                                                    <th>Received By</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($payment_history as $tx): ?>
                                                    <tr>
                                                        <td><?= date('M j, Y', strtotime($tx['payment_date'])) ?></td>
                                                        <td><?= htmlspecialchars($tx['school_year']) ?></td>
                                                        <td><?= htmlspecialchars($tx['semester']) ?></td>
                                                        <td class="text-success fw-bold">₱<?= number_format($tx['amount_paid'], 2) ?></td>
                                                        <td><?= htmlspecialchars($tx['received_by_name']) ?></td>
                                                        <td><?= htmlspecialchars($tx['notes'] ?? '—') ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- balance card -->
                    <div class="col-md-4">
                        <div class="card balance-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-wallet2"></i> Current Balance</h5>
                            </div>
                            <div class="card-body text-center py-4">
                                <?php if ($student['payment_id']): ?>
                                    <div class="mb-2">
                                        <?php if ($student['payment_status'] === 'paid'): ?>
                                            <span class="badge bg-success fs-6"><i class="bi bi-check-circle-fill"></i> Paid</span>
                                        <?php elseif ($student['payment_status'] === 'partial'): ?>
                                            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-clock-fill"></i> Partial</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger fs-6"><i class="bi bi-exclamation-circle-fill"></i> Pending</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="balance-amount <?= $student['remaining'] > 0 ? 'text-danger' : 'text-success' ?> fs-3 fw-bold">
                                        ₱<?= number_format($student['remaining'], 2) ?>
                                    </div>
                                    <p class="text-muted">Remaining Balance</p>
                                    <hr>
                                    <div class="text-start">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted">Total Amount</small>
                                            <small class="fw-bold">₱<?= number_format($student['net_amount'], 2) ?></small>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">Total Paid</small>
                                            <small class="fw-bold text-success">₱<?= number_format($student['total_paid'], 2) ?></small>
                                        </div>
                                    </div>
                                    <a href="index.php?page=payment_process&student_id=<?= $student['student_id'] ?>"
                                        class="btn btn-warning w-100 mt-3">
                                        <i class="bi bi-cash-coin"></i> Record Payment
                                    </a>
                                <?php else: ?>
                                    <div class="empty-state py-2">
                                        <div class="empty-state-icon"><i class="bi bi-wallet2"></i></div>
                                        <p class="empty-state-text">No payment record for this period.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const toggleBtn = document.getElementById('toggleDocsEdit');
            const cancelBtn = document.getElementById('cancelDocsEdit');
            const viewMode = document.getElementById('docsViewMode');
            const editMode = document.getElementById('docsEditMode');
            const form = document.getElementById('docsForm');
            const saveMsg = document.getElementById('docsSaveMsg');
            const badge = document.getElementById('docsBadge');

            const docFields = <?= json_encode(array_keys($doc_fields)) ?>;
            const docLabels = <?= json_encode(array_values($doc_fields)) ?>;
            const totalDocs = <?= $total_docs ?>;

            toggleBtn.addEventListener('click', function() {
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
                toggleBtn.style.display = 'none';
            });

            cancelBtn.addEventListener('click', function() {
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
                toggleBtn.style.display = '';
                saveMsg.style.display = 'none';
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const saveBtn = document.getElementById('saveDocsBtn');
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

                fetch('index.php?page=update_enrollment_documents', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form),
                    })
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            // update badge
                            const s = data.submitted;
                            badge.textContent = s + ' / ' + totalDocs + ' Submitted';
                            badge.className = 'badge ' + (s === totalDocs ? 'bg-success' : (s > 0 ? 'bg-warning text-dark' : 'bg-danger'));

                            // update view mode icons and labels
                            const rows = viewMode.querySelectorAll('.doc-status-row');
                            rows.forEach(function(row, i) {
                                const ok = data.docs[docFields[i]] === 1;
                                const icon = row.querySelector('i');
                                const label = row.querySelector('span');
                                icon.className = ok ? 'bi bi-check-circle-fill text-success' : 'bi bi-x-circle-fill text-danger';
                                label.className = 'small ' + (ok ? '' : 'text-muted');
                            });

                            saveMsg.className = 'small text-success';
                            saveMsg.textContent = 'Saved!';
                            saveMsg.style.display = 'inline';

                            // collapse back to view after short delay
                            setTimeout(function() {
                                viewMode.style.display = 'block';
                                editMode.style.display = 'none';
                                toggleBtn.style.display = '';
                                saveMsg.style.display = 'none';
                            }, 900);
                        } else {
                            saveMsg.className = 'small text-danger';
                            saveMsg.textContent = data.message || 'Failed to save.';
                            saveMsg.style.display = 'inline';
                        }
                    })
                    .catch(function() {
                        saveMsg.className = 'small text-danger';
                        saveMsg.textContent = 'An error occurred.';
                        saveMsg.style.display = 'inline';
                    })
                    .finally(function() {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="bi bi-save-fill"></i> Save';
                    });
            });
        })();
    </script>
    <script src="js/shared/top-navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');

            function toggleSidebar() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }

            if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
            if (overlay) overlay.addEventListener('click', toggleSidebar);
        });
    </script>
</body>

</html>