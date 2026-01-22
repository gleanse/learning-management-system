<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
    <link rel="stylesheet" href="css/pages/student_dashboard.css">
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
                    <a class="nav-link active" href="index.php?page=student_dashboard">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=student_grades">
                        <i class="bi bi-journal-text"></i>
                        <span>My Grades</span>
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
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <span>Student Dashboard</span>
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
                            $lastname = $_SESSION['user_lastname'] ?? 'T';
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
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="bi bi-house-door-fill"></i> Dashboard
                        </li>
                    </ol>
                </nav>

                <!-- welcome banner -->
                <div class="welcome-banner mb-4">
                    <div class="welcome-content">
                        <div class="welcome-text">
                            <h2 class="welcome-title">Good <?php date_default_timezone_set('Asia/Manila');
                                                            echo (date('H') < 12) ? 'Morning' : ((date('H') < 18) ? 'Afternoon' : 'Evening'); ?>, <?php echo htmlspecialchars($_SESSION['user_firstname']) . ' ' . htmlspecialchars($_SESSION['user_lastname']); ?></h2>
                            <p class="welcome-subtitle">Student • <?php echo htmlspecialchars($student_info['student_number'] ?? 'N/A'); ?> • <?php echo htmlspecialchars($student_info['section_name'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-event"></i> My Schedule & Payments
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" id="prevMonth">‹</button>
                            <span id="currentMonth" class="mx-2 fw-bold"></span>
                            <button class="btn btn-sm btn-outline-secondary" id="nextMonth">›</button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="calendar-grid" id="calendar"></div>

                        <div class="mt-3">
                            <p class="mb-1"><strong>Payment:</strong> ₱1,750 / 28 days</p>
                            <p class="mb-0 text-danger"><strong>Penalty:</strong> ₱100 if unpaid on due date</p>
                        </div>
                    </div>
                </div>


                <!-- quick links section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-journal-bookmark"></i>
                            View My Grades
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($year_levels)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-state-text">No year levels available yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($year_levels as $level): ?>
                                    <a href="index.php?page=student_semesters&year_level=<?php echo urlencode($level['year_level']); ?>"
                                        class="list-group-item list-group-item-action">
                                        <i class="bi bi-mortarboard-fill"></i>
                                        <?php echo htmlspecialchars($level['year_level']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentDate = new Date();
        const today = new Date();
        today.setDate(1);
        today.setHours(0, 0, 0, 0);

        const scheduleDays = ['Mon', 'Wed', 'Fri'];
        const paymentInterval = 28;

        const calendarEl = document.getElementById('calendar');
        const monthLabel = document.getElementById('currentMonth');
        const prevBtn = document.getElementById('prevMonth');
        const nextBtn = document.getElementById('nextMonth');

        function updateButtonStates() {
            // Create normalized version of currentDate for comparison
            const viewingDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);

            // Disable "next" button if we're viewing current month or future
            if (viewingDate >= today) {
                nextBtn.disabled = true;
                nextBtn.style.opacity = '0.5';
                nextBtn.style.cursor = 'not-allowed';
            } else {
                nextBtn.disabled = false;
                nextBtn.style.opacity = '1';
                nextBtn.style.cursor = 'pointer';
            }
        }

        function renderCalendar() {
            calendarEl.innerHTML = '';

            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();

            monthLabel.textContent = currentDate.toLocaleDateString('en-US', {
                month: 'long',
                year: 'numeric'
            });

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);

            for (let i = 0; i < firstDay.getDay(); i++) {
                calendarEl.innerHTML += `<div></div>`;
            }

            for (let day = 1; day <= lastDay.getDate(); day++) {
                const date = new Date(year, month, day);
                const dayName = date.toLocaleDateString('en-US', {
                    weekday: 'short'
                });

                let classes = 'calendar-day';

                if (scheduleDays.includes(dayName)) {
                    classes += ' class-day';
                }

                if (date.toDateString() === new Date().toDateString()) {
                    classes += ' today';
                }

                // payment every 28 days (example base date)
                const baseDate = new Date('2025-01-01');
                const basePaymentFriday = new Date('2025-01-03'); // MUST be a Friday

                const diffDays = Math.floor(
                    (date - basePaymentFriday) / (1000 * 60 * 60 * 24)
                );

                let receiptIcon = '';

                if (
                    date.getDay() === 5 && // Friday (0=Sun, 5=Fri)
                    diffDays >= 0 &&
                    diffDays % 28 === 0
                ) {
                    receiptIcon = `<span class="receipt-icon">₱</span>`;
                }

                calendarEl.innerHTML += `
            <div class="${classes}">
                ${receiptIcon}
                <strong>${day}</strong><br>
                <small>${dayName}</small>
            </div>
        `;
            }

            updateButtonStates();
        }

        prevBtn.onclick = () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        };

        nextBtn.onclick = () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        };

        renderCalendar();
    </script>
</body>

</html>