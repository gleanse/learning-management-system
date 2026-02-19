<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Dashboard - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/shared/sidenav.css">
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
                    <a class="nav-link active" href="index.php?page=registrar_dashboard">
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
                        <div class="page-icon">
                            <i class="bi bi-house-door-fill"></i>
                        </div>
                        <span>Dashboard</span>
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

            <div class="container-fluid p-4">
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-house-door-fill"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Welcome, <?= htmlspecialchars($_SESSION['user_firstname']) ?></h1>
                            <p class="header-subtitle">Registrar Dashboard — coming soon</p>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-radius: 1rem; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-tools" style="font-size: 3rem; color: #cbd5e1;"></i>
                        <h5 class="mt-3 fw-700" style="color: var(--secondary);">Dashboard under construction</h5>
                        <p style="color: var(--gray-500); font-size: 0.938rem;">
                            This page is a placeholder. Head to enrollment to get started.
                        </p>
                        <a href="index.php?page=enrollment_create"
                            style="display:inline-flex; align-items:center; gap:0.5rem; padding: 0.75rem 1.5rem;
                                   border-radius: 0.625rem; background: linear-gradient(135deg, var(--primary) 0%, #a01410 100%);
                                   color: #fff; font-weight: 700; text-decoration: none; font-size: 0.938rem;">
                            <i class="bi bi-person-plus-fill"></i>
                            Enroll a Student
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>