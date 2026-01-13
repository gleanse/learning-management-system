<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/landing/navbar.css">
    <link rel="stylesheet" href="css/pages/login.css">
    <title>Sign In - LMS</title>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/DCSA-LOGO.png" alt="LMS Logo" class="navbar-logo">
            </a>
        </div>
    </nav>

    <div class="login-wrapper">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-11 col-sm-10 col-md-9 col-lg-8 col-xl-7">

                    <div class="login-container">

                        <!-- LEFT side -->
                        <div class="login-brand">
                            <div class="brand-content">
                                <div class="brand-logo">
                                    <img src="assets/DCSA-LOGO.PNG" alt="LMS Brand Logo">
                                </div>
                                <div class="brand-text-wrapper">
                                    <h2 class="brand-title">Learning Management System</h2>
                                    <p class="brand-subtitle">Empowering education through technology</p>
                                </div>
                                <div class="brand-decorative">
                                    <div class="deco-line"></div>
                                    <div class="deco-circle"></div>
                                    <div class="deco-line"></div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT side -->
                        <div class="login-form-wrapper">
                            <div class="login-form-content">

                                <!-- HEADER -->
                                <div class="login-header">
                                    <h4 class="login-title">Welcome Back</h4>
                                    <p class="login-subtitle">Sign in to access your account</p>
                                </div>

                                <?php if (isset($errors['general'])): ?>
                                    <div class="alert alert-danger" role="alert" id="lockout-message">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <?php if (isset($ip_status['locked']) && $ip_status['locked']): ?>
                                            <?= htmlspecialchars($errors['general']) ?> <span id="countdown"></span>.
                                        <?php else: ?>
                                            <?= htmlspecialchars($errors['general']) ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <form action="index.php?page=login" method="POST" class="login-form">

                                    <!-- token gets compared on server side csrf token to prevent fake form submissions -->
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                                    <!-- USERNAME or EMAIL input -->
                                    <div class="mb-4">
                                        <label for="username_or_email" class="form-label">
                                            <i class="bi bi-person-circle me-1"></i>Username or Email
                                        </label>
                                        <div class="input-wrapper">
                                            <input
                                                type="text"
                                                class="form-control <?= isset($errors['username_or_email']) ? 'is-invalid' : '' ?>"
                                                id="username_or_email"
                                                name="username_or_email"
                                                value="<?= htmlspecialchars($old_input['username_or_email'] ?? '') ?>"
                                                placeholder="Enter your username or email"
                                                required
                                                autofocus>
                                            <span class="input-icon">
                                                <i class="bi bi-person"></i>
                                            </span>
                                            <?php if (isset($errors['username_or_email'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($errors['username_or_email']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- PASSWORD input with toggle visibility -->
                                    <div class="mb-4">
                                        <label for="password" class="form-label">
                                            <i class="bi bi-shield-lock me-1"></i>Password
                                        </label>
                                        <div class="input-wrapper">
                                            <input
                                                type="password"
                                                class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                                id="password"
                                                name="password"
                                                placeholder="Enter your password"
                                                required>
                                            <span class="input-icon">
                                                <i class="bi bi-lock"></i>
                                            </span>
                                            <button class="password-toggle" type="button" id="togglePassword">
                                                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                                            </button>
                                            <?php if (isset($errors['password'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($errors['password']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- REMEMBER me checkbox -->
                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me">
                                        <label class="form-check-label" for="remember_me">
                                            Remember me
                                        </label>
                                    </div>

                                    <!-- SUBMIT button -->
                                    <div class="d-grid mb-4">
                                        <button type="submit" class="btn btn-primary btn-login">
                                            <span>Sign In</span>
                                            <i class="bi bi-arrow-right-short"></i>
                                        </button>
                                    </div>
                                </form>

                                <!-- ADMIN contact message -->
                                <div class="login-footer">
                                    <p class="footer-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Don't have an account? Please contact your system administrator.
                                    </p>
                                </div>

                            </div>
                        </div>

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
    <script src="js/password-toggle.js"></script>

</body>

</html>