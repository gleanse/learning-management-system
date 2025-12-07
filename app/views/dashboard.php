<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userTypes = [
    1 => 'Student',
    2 => 'Teacher',
    3 => 'Admin',
    4 => 'SuperAdmin'
];

$userTypeName = $userTypes[$_SESSION['user_type']] ?? 'Unknown';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/bootstrap/css/bootstrap.min.css">
    <title>Dashboard</title>
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Welcome, <?= $_SESSION['user_name'] ?>!</h2>
                    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
                <p>Email: <?= $_SESSION['user_email'] ?></p>
                <p>User Type: <span class="badge bg-primary"><?= $userTypeName ?></span></p>
            </div>
        </div>
    </div>
</body>

</html>