<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Management - LMS</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Grading</li>
                    </ol>
                </nav>

                <h2>Select Year Level</h2>

                <div class="card mt-3">
                    <div class="card-body">
                        <?php if (empty($year_levels)): ?>
                            <p class="text-muted">No year levels assigned yet.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($year_levels as $level): ?>
                                    <a href="index.php?page=grading_subjects&year_level=<?php echo urlencode($level['year_level']); ?>" 
                                       class="list-group-item list-group-item-action">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($level['year_level']); ?></h5>
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