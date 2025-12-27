<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <title>Sign In</title>
</head>

<body class="bg-light d-flex align-items-center" style="min-height:100vh;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-8 col-md-6 col-lg-4">

                <div class="card shadow login-card">
                    <div class="card-body p-4">
                        <h4 class="mb-4 text-center">Sign In</h4>
                        
                        <?php if (isset($errors['general'])): ?>
                            <div class="alert alert-danger" role="alert" id="lockout-message">
                                <?php if (isset($ip_status['locked']) && $ip_status['locked']): ?>
                                    Too many failed attempts. Try again in <span id="countdown"></span>.
                                <?php else: ?>
                                    <?= htmlspecialchars($errors['general']) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <form action="index.php?page=login" method="POST">

                            <!-- token gets compared on server side csrf token to prevent fake form submissions -->
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                            <div class="mb-3">
                                <label for="username_or_email" class="form-label">Username or Email</label>
                                <input
                                    type="text"
                                    class="form-control <?= isset($errors['username_or_email']) ? 'is-invalid' : '' ?>"
                                    id="username_or_email"
                                    name="username_or_email"
                                    value="<?= htmlspecialchars($_POST['username_or_email'] ?? '') ?>"
                                    placeholder="Enter username or email"
                                    required
                                    autofocus>
                                <?php if (isset($errors['username_or_email'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['username_or_email']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input
                                    type="password"
                                    class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    required>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['password']) ?>
                                    </div>
                                <?php endif; ?>
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
    
    <?php if (isset($ip_status['locked']) && $ip_status['locked']): ?>
        <script src="js/lockout-countdown.js"></script>
        <script>
            initLockoutCountdown(<?php echo $ip_status['seconds_remaining']; ?>);
        </script>
    <?php endif; ?>
    
</body>

</html>