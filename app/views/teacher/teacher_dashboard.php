<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - LMS</title>
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
                    <a class="nav-link text-white active" href="index.php?page=teacher_dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=grading">Grading Management</a>
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
                    <span class="navbar-brand mb-0 h1">Teacher Dashboard</span>
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
                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    </ol>
                </nav>

                <!-- schedule section now displays todays schedule -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">My Schedule - <?php echo htmlspecialchars($current_date); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($today_schedule)): ?>
                            <p class="text-muted">No classes scheduled for today.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Subject</th>
                                            <th>Section</th>
                                            <th>Room</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($today_schedule as $schedule): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($schedule['time_range']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['subject_code']) . ' - ' . htmlspecialchars($schedule['subject_name']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['section_name']) . ' (' . htmlspecialchars($schedule['year_level']) . ')'; ?></td>
                                                <td><?php echo htmlspecialchars($schedule['room_display']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- quick links section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Links - Grading</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($year_levels)): ?>
                            <p class="text-muted">No year levels assigned yet.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($year_levels as $level): ?>
                                    <a href="index.php?page=grading_subjects&year_level=<?php echo urlencode($level['year_level']); ?>" 
                                       class="list-group-item list-group-item-action">
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
</body>
</html>