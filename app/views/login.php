<?php

require_once __DIR__ . '/../controllers/AuthController.php';

$controller = new AuthController();
$error = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $error = $controller->login();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
    <title>Sign In</title>
</head>

<body class="bg-light d-flex align-items-center" style="min-height:100vh;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-8 col-md-6 col-lg-4">

                <div class="card shadow login-card">
                    <div class="card-body p-4">
                        <h4 class="mb-4 text-center">Sign In</h4>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($email) ?>"
                                    placeholder="your@email.com" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary">Sign In</button>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>