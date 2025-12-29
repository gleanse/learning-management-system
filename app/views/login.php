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

                <div class="card shadow-lg login-card">
                    <div class="card-body p-4 p-sm-5">
                        <h4 class="mb-4 text-center fw-bold">Sign In</h4>

                        <?php if (isset($errors['general'])): ?>
                            <div class="alert alert-danger mb-4" role="alert" id="lockout-message">
                                <?php if (isset($ip_status['locked']) && $ip_status['locked']): ?>
                                    <?= htmlspecialchars($errors['general']) ?> <span id="countdown"></span>.
                                <?php else: ?>
                                    <?= htmlspecialchars($errors['general']) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <form action="index.php?page=login" method="POST">

                            <!-- token gets compared on server side csrf token to prevent fake form submissions -->
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                            <div class="form-floating mb-3">
                                <input
                                    type="text"
                                    class="form-control <?= isset($errors['username_or_email']) ? 'is-invalid' : '' ?>"
                                    id="username_or_email"
                                    name="username_or_email"
                                    value="<?= htmlspecialchars($old_input['username_or_email'] ?? '') ?>"
                                    placeholder="Username or Email"
                                    required
                                    autofocus>
                                <label for="username_or_email">Username or Email</label>
                                <?php if (isset($errors['username_or_email'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['username_or_email']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-floating mb-4">
                                <input
                                    type="password"
                                    class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                    id="password"
                                    name="password"
                                    placeholder="Password"
                                    required>
                                <label for="password">Password</label>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['password']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me">
                                <label class="form-check-label" for="remember_me">
                                    Remember me
                                </label>
                            </div>

                            <div class="d-grid">
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
    
    <script src="js/form-validation.js"></script>

</body>

</html>