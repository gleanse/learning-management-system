<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <title>Dashboard</title>
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Welcome, <?= htmlspecialchars($_SESSION['user_username']) ?>!</h2>
                    <a href="index.php?page=logout" class="btn btn-danger btn-sm">Logout</a>
                </div>
                <p>Email: <?= htmlspecialchars($_SESSION['user_email']) ?></p>
                <p>User Account Role: <span class="badge bg-primary"><?= htmlspecialchars($_SESSION['user_role']) ?></span></p>
            </div>
        </div>
    </div>
</body>

</html>