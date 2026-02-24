<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - LMS</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/pages/forgot_password.css">
</head>

<body class="forgot-body">

    <div class="forgot-wrapper">
        <div class="forgot-card">

            <!-- brand header -->
            <div class="forgot-brand">
                <div class="brand-logo">
                    <img src="assets/DCSA-LOGO.png" alt="School Logo">
                </div>
                <div class="brand-info">
                    <h5>Datamex College of Saint Adeline</h5>
                    <p>Learning Management System</p>
                </div>
            </div>

            <!-- card header -->
            <div class="forgot-header">
                <div class="forgot-header-icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div>
                    <h5 class="forgot-header-title">Forgot Password</h5>
                    <p class="forgot-header-subtitle" id="stepSubtitle">Enter your username or email to receive an OTP</p>
                </div>
            </div>

            <!-- step indicators -->
            <div class="step-indicators">
                <div class="step-item active" id="stepDot1">
                    <div class="step-dot">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <span class="step-label">Verify</span>
                </div>
                <div class="step-connector"></div>
                <div class="step-item" id="stepDot2">
                    <div class="step-dot">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <span class="step-label">OTP</span>
                </div>
                <div class="step-connector"></div>
                <div class="step-item" id="stepDot3">
                    <div class="step-dot">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <span class="step-label">Reset</span>
                </div>
            </div>

            <!-- alert -->
            <div class="alert-wrapper d-none" id="alertWrapper">
                <div class="forgot-alert" id="alertBox">
                    <i class="bi bi-info-circle-fill" id="alertIcon"></i>
                    <span id="alertMessage"></span>
                </div>
            </div>

            <!-- step 1: identifier input -->
            <div class="step-panel" id="step1">
                <form id="sendOtpForm">
                    <div class="mb-4">
                        <label class="forgot-label">
                            <i class="bi bi-person-fill"></i>
                            Username or Email
                        </label>
                        <div class="input-icon-wrapper">
                            <i class="bi bi-person-fill input-icon"></i>
                            <input type="text" class="forgot-input" id="identifier" name="identifier" placeholder="Enter your username or email" autocomplete="username" autofocus>
                        </div>
                        <div class="field-error d-none" id="identifierError"></div>
                    </div>
                    <button type="submit" class="btn-forgot-primary w-100" id="sendOtpBtn">
                        <span class="btn-text">
                            <i class="bi bi-send-fill"></i>
                            Send OTP
                        </span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm"></span>
                            Sending...
                        </span>
                    </button>
                </form>
            </div>

            <!-- step 2: otp verification -->
            <div class="step-panel d-none" id="step2">
                <div class="otp-sent-info" id="otpSentInfo">
                    <i class="bi bi-envelope-check-fill"></i>
                    <span id="otpSentMessage">OTP sent to your email</span>
                </div>

                <form id="verifyOtpForm">
                    <div class="mb-4">
                        <label class="forgot-label">
                            <i class="bi bi-shield-check"></i>
                            Enter 6-Digit OTP
                        </label>
                        <div class="otp-inputs" id="otpInputs">
                            <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]">
                            <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]">
                        </div>
                        <input type="hidden" id="otpCode" name="otp_code">
                        <div class="field-error d-none" id="otpError"></div>
                    </div>

                    <!-- otp expiry timer -->
                    <div class="otp-timer-wrapper">
                        <i class="bi bi-clock-fill"></i>
                        <span>Expires in <strong id="otpTimer">15:00</strong></span>
                    </div>

                    <button type="submit" class="btn-forgot-primary w-100 mb-3" id="verifyOtpBtn">
                        <span class="btn-text">
                            <i class="bi bi-shield-check"></i>
                            Verify OTP
                        </span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm"></span>
                            Verifying...
                        </span>
                    </button>

                    <!-- resend -->
                    <div class="resend-wrapper">
                        <span class="resend-text">Didn't receive the OTP?</span>
                        <button type="button" class="btn-resend d-none" id="resendBtn">
                            <i class="bi bi-arrow-clockwise"></i>
                            Resend OTP
                        </button>
                        <span class="resend-cooldown" id="resendCooldown">
                            Resend available in <strong id="cooldownTimer">60</strong>s
                        </span>
                    </div>
                </form>
            </div>

            <!-- step 3: new password -->
            <div class="step-panel d-none" id="step3">
                <form id="resetPasswordForm">
                    <div class="mb-3">
                        <label class="forgot-label">
                            <i class="bi bi-shield-lock-fill"></i>
                            New Password
                        </label>
                        <div class="input-icon-wrapper">
                            <i class="bi bi-shield-lock-fill input-icon"></i>
                            <input type="password" class="forgot-input with-toggle" id="newPassword" name="new_password" placeholder="Enter new password">
                            <button type="button" class="btn-toggle-pass" id="toggleNewPass">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                        <div class="password-strength-meter mt-2">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="password-requirements mt-2">
                            <small class="requirement" data-rule="length">
                                <i class="bi bi-x-circle-fill"></i>
                                At least 8 characters
                            </small>
                            <small class="requirement" data-rule="uppercase">
                                <i class="bi bi-x-circle-fill"></i>
                                One uppercase letter
                            </small>
                            <small class="requirement" data-rule="lowercase">
                                <i class="bi bi-x-circle-fill"></i>
                                One lowercase letter
                            </small>
                            <small class="requirement" data-rule="number">
                                <i class="bi bi-x-circle-fill"></i>
                                One number
                            </small>
                            <small class="requirement" data-rule="special">
                                <i class="bi bi-x-circle-fill"></i>
                                One special character
                            </small>
                        </div>
                        <div class="field-error d-none" id="newPasswordError"></div>
                    </div>

                    <div class="mb-4">
                        <label class="forgot-label">
                            <i class="bi bi-shield-lock-fill"></i>
                            Confirm Password
                        </label>
                        <div class="input-icon-wrapper">
                            <i class="bi bi-shield-lock-fill input-icon"></i>
                            <input type="password" class="forgot-input with-toggle" id="confirmPassword" name="confirm_password" placeholder="Confirm new password">
                            <button type="button" class="btn-toggle-pass" id="toggleConfirmPass">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                        <div class="field-error d-none" id="confirmPasswordError"></div>
                    </div>

                    <button type="submit" class="btn-forgot-primary w-100" id="resetPasswordBtn">
                        <span class="btn-text">
                            <i class="bi bi-check-circle-fill"></i>
                            Reset Password
                        </span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm"></span>
                            Resetting...
                        </span>
                    </button>
                </form>
            </div>

            <!-- step 4: success -->
            <div class="step-panel d-none" id="stepSuccess">
                <div class="success-state">
                    <div class="success-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h5 class="success-title">Password Reset!</h5>
                    <p class="success-text">Your password has been successfully reset. You can now log in with your new password.</p>
                    <a href="index.php?page=login" class="btn-forgot-primary w-100">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Back to Login
                    </a>
                </div>
            </div>

            <!-- back to login link -->
            <div class="back-to-login" id="backToLoginWrapper">
                <a href="index.php?page=login" class="back-link">
                    <i class="bi bi-arrow-left"></i>
                    Back to Login
                </a>
            </div>

        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/forgot-password.js"></script>

</body>

</html>