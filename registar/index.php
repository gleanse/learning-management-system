<?php
// index.php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get current tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Office Management System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="col-md-2 p-0 bg-dark sidebar">
        <div class="sidebar-header text-center py-4">
            <h4 class="text-white">Registrar System</h4>
        </div>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="?tab=dashboard" class="nav-link <?php echo $tab == 'dashboard' ? 'active' : 'text-white'; ?>">
                    <i class="fas fa-dashboard me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="?tab=students" class="nav-link <?php echo $tab == 'students' ? 'active' : 'text-white'; ?>">
                    <i class="fas fa-users me-2"></i> Students
                </a>
            </li>
            <li>
                <a href="?tab=enrollment" class="nav-link <?php echo $tab == 'enrollment' ? 'active' : 'text-white'; ?>">
                    <i class="fas fa-user-plus me-2"></i> Enrollment
                </a>
            </li>
            <li>
                <a href="?tab=payments" class="nav-link <?php echo $tab == 'payments' ? 'active' : 'text-white'; ?>">
                    <i class="fas fa-money-bill me-2"></i> Payments
                </a>
            </li>
            <li>
                <a href="?tab=monitoring" class="nav-link <?php echo $tab == 'monitoring' ? 'active' : 'text-white'; ?>">
                    <i class="fas fa-chart-line me-2"></i> Payment Monitoring
                </a>
            </li>
            <li>
                <a href="?tab=fee_management" class="nav-link <?php echo $tab == 'fee_management' ? 'active' : 'text-white'; ?>">
                    <i class="fas fa-calculator me-2"></i> Fee Management
                </a>
            </li>
            <li>
                <a href="?tab=cash_drawer" class="nav-link <?php echo $tab == 'cash_drawer' ? 'active' : 'text-white'; ?>">
                    <i class="fas fa-cash-register me-2"></i> Cash Drawer
                </a>
            </li>
            <li>
                <a href="?tab=strands" class="nav-link <?php echo $tab == 'strands' ? 'active' : 'text-white'; ?>">
                    <i class="fas fa-book me-2"></i> Strands/Sections
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content p-4">
        <?php
        // Load the appropriate module
        $module_path = "modules/{$tab}.php";
        if (file_exists($module_path)) {
            include $module_path;
        } else {
            include 'modules/dashboard.php';
        }
        ?>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="js/script.js"></script>
    
    <!-- Toastr configuration -->
    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right"
        };
    </script>
</body>
</html>