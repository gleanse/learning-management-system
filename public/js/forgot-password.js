// forgot password — handles step flow, otp boxes, timers, resend cooldown

document.addEventListener('DOMContentLoaded', function () {
  // elements
  const step1 = document.getElementById('step1');
  const step2 = document.getElementById('step2');
  const step3 = document.getElementById('step3');
  const stepSuccess = document.getElementById('stepSuccess');
  const stepSubtitle = document.getElementById('stepSubtitle');
  const backWrapper = document.getElementById('backToLoginWrapper');

  const alertWrapper = document.getElementById('alertWrapper');
  const alertBox = document.getElementById('alertBox');
  const alertIcon = document.getElementById('alertIcon');
  const alertMessage = document.getElementById('alertMessage');

  const stepDot1 = document.getElementById('stepDot1');
  const stepDot2 = document.getElementById('stepDot2');
  const stepDot3 = document.getElementById('stepDot3');
  const connectors = document.querySelectorAll('.step-connector');

  let otpTimerInterval = null;
  let cooldownInterval = null;
  let otpSecondsLeft = 15 * 60;
  let cooldownSecondsLeft = 60;

  // show alert
  function showAlert(type, message) {
    alertBox.className = 'forgot-alert alert-' + type;
    const icons = {
      success: 'bi-check-circle-fill',
      danger: 'bi-x-circle-fill',
      warning: 'bi-exclamation-triangle-fill',
    };
    alertIcon.className = 'bi ' + (icons[type] || 'bi-info-circle-fill');
    alertMessage.textContent = message;
    alertWrapper.classList.remove('d-none');
  }

  function hideAlert() {
    alertWrapper.classList.add('d-none');
  }

  // show field error
  function showFieldError(el, message) {
    el.textContent = message;
    el.classList.remove('d-none');
  }

  function clearFieldError(el) {
    el.textContent = '';
    el.classList.add('d-none');
  }

  // set loading state on button
  function setLoading(btn, loading) {
    const text = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.btn-loading');
    btn.disabled = loading;
    text.classList.toggle('d-none', loading);
    spinner.classList.toggle('d-none', !loading);
  }

  // move to step
  function goToStep(step) {
    [step1, step2, step3, stepSuccess].forEach((s) =>
      s.classList.add('d-none')
    );

    hideAlert();

    if (step === 1) {
      step1.classList.remove('d-none');
      stepSubtitle.textContent =
        'Enter your username or email to receive an OTP';
      setStepIndicator(1);
    } else if (step === 2) {
      step2.classList.remove('d-none');
      stepSubtitle.textContent = 'Enter the 6-digit OTP sent to your email';
      setStepIndicator(2);
      startOtpTimer();
      startCooldown();
      focusFirstOtpBox();
    } else if (step === 3) {
      step3.classList.remove('d-none');
      stepSubtitle.textContent = 'Set your new password';
      setStepIndicator(3);
      stopOtpTimer();
    } else if (step === 'success') {
      stepSuccess.classList.remove('d-none');
      stepSubtitle.textContent = 'Password successfully reset';
      backWrapper.classList.add('d-none');
      setStepIndicator('success');
    }
  }

  function setStepIndicator(step) {
    [stepDot1, stepDot2, stepDot3].forEach((d) => {
      d.classList.remove('active', 'completed');
    });
    connectors.forEach((c) => c.classList.remove('completed'));

    if (step === 1) {
      stepDot1.classList.add('active');
    } else if (step === 2) {
      stepDot1.classList.add('completed');
      connectors[0].classList.add('completed');
      stepDot2.classList.add('active');
    } else if (step === 3 || step === 'success') {
      stepDot1.classList.add('completed');
      stepDot2.classList.add('completed');
      connectors[0].classList.add('completed');
      connectors[1].classList.add('completed');
      stepDot3.classList.add(step === 'success' ? 'completed' : 'active');
    }
  }

  // =====================
  // step 1 — send otp
  // =====================
  const sendOtpForm = document.getElementById('sendOtpForm');
  const sendOtpBtn = document.getElementById('sendOtpBtn');
  const identifierInput = document.getElementById('identifier');
  const identifierError = document.getElementById('identifierError');

  sendOtpForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    hideAlert();
    clearFieldError(identifierError);

    const identifier = identifierInput.value.trim();
    if (!identifier) {
      showFieldError(identifierError, 'Please enter your username or email.');
      identifierInput.classList.add('is-invalid');
      return;
    }

    identifierInput.classList.remove('is-invalid');
    setLoading(sendOtpBtn, true);

    try {
      const form = new FormData();
      form.append('identifier', identifier);

      const res = await fetch('index.php?page=forgot_password_send', {
        method: 'POST',
        body: form,
      });
      const data = await res.json();

      if (data.success) {
        document.getElementById('otpSentMessage').textContent = data.message;
        goToStep(2);
      } else {
        showAlert('danger', data.message);
      }
    } catch (err) {
      showAlert('danger', 'Something went wrong. Please try again.');
    } finally {
      setLoading(sendOtpBtn, false);
    }
  });

  identifierInput.addEventListener('input', function () {
    this.classList.remove('is-invalid');
    clearFieldError(identifierError);
  });

  // =====================
  // otp boxes — auto focus, backspace, paste
  // =====================
  const otpBoxes = document.querySelectorAll('.otp-box');
  const otpCode = document.getElementById('otpCode');

  otpBoxes.forEach((box, index) => {
    box.addEventListener('input', function () {
      // only allow digits
      this.value = this.value.replace(/[^0-9]/g, '');

      if (this.value) {
        this.classList.add('filled');
        this.classList.remove('is-invalid');
        if (index < otpBoxes.length - 1) {
          otpBoxes[index + 1].focus();
        }
      } else {
        this.classList.remove('filled');
      }

      syncOtpCode();
    });

    box.addEventListener('keydown', function (e) {
      if (e.key === 'Backspace' && !this.value && index > 0) {
        otpBoxes[index - 1].focus();
        otpBoxes[index - 1].value = '';
        otpBoxes[index - 1].classList.remove('filled');
        syncOtpCode();
      }
    });

    // paste handler — distribute digits across boxes
    box.addEventListener('paste', function (e) {
      e.preventDefault();
      const pasted = e.clipboardData
        .getData('text')
        .replace(/[^0-9]/g, '')
        .slice(0, 6);
      pasted.split('').forEach((char, i) => {
        if (otpBoxes[i]) {
          otpBoxes[i].value = char;
          otpBoxes[i].classList.add('filled');
        }
      });
      syncOtpCode();
      const nextEmpty = [...otpBoxes].findIndex((b) => !b.value);
      if (nextEmpty !== -1) otpBoxes[nextEmpty].focus();
      else otpBoxes[5].focus();
    });
  });

  function syncOtpCode() {
    otpCode.value = [...otpBoxes].map((b) => b.value).join('');
  }

  function focusFirstOtpBox() {
    otpBoxes[0].focus();
  }

  function clearOtpBoxes() {
    otpBoxes.forEach((b) => {
      b.value = '';
      b.classList.remove('filled', 'is-invalid');
    });
    otpCode.value = '';
  }

  function markOtpInvalid() {
    otpBoxes.forEach((b) => b.classList.add('is-invalid'));
  }

  // =====================
  // otp expiry timer — 15 min countdown
  // =====================
  function startOtpTimer() {
    otpSecondsLeft = 15 * 60;
    updateOtpTimerDisplay();

    clearInterval(otpTimerInterval);
    otpTimerInterval = setInterval(function () {
      otpSecondsLeft--;
      updateOtpTimerDisplay();

      if (otpSecondsLeft <= 0) {
        clearInterval(otpTimerInterval);
        showAlert('warning', 'Your OTP has expired. Please request a new one.');
        document.getElementById('verifyOtpBtn').disabled = true;
      }
    }, 1000);
  }

  function stopOtpTimer() {
    clearInterval(otpTimerInterval);
  }

  function updateOtpTimerDisplay() {
    const mins = String(Math.floor(otpSecondsLeft / 60)).padStart(2, '0');
    const secs = String(otpSecondsLeft % 60).padStart(2, '0');
    const timerEl = document.getElementById('otpTimer');
    const wrapperEl = document.querySelector('.otp-timer-wrapper');

    timerEl.textContent = `${mins}:${secs}`;

    // turn red when under 2 minutes
    if (otpSecondsLeft <= 120) {
      wrapperEl.classList.add('expiring');
    } else {
      wrapperEl.classList.remove('expiring');
    }
  }

  // =====================
  // resend cooldown — 60 sec
  // =====================
  function startCooldown() {
    cooldownSecondsLeft = 60;
    const resendBtn = document.getElementById('resendBtn');
    const cooldownEl = document.getElementById('resendCooldown');
    const cooldownTimer = document.getElementById('cooldownTimer');

    resendBtn.classList.add('d-none');
    cooldownEl.classList.remove('d-none');
    cooldownTimer.textContent = cooldownSecondsLeft;

    clearInterval(cooldownInterval);
    cooldownInterval = setInterval(function () {
      cooldownSecondsLeft--;
      cooldownTimer.textContent = cooldownSecondsLeft;

      if (cooldownSecondsLeft <= 0) {
        clearInterval(cooldownInterval);
        cooldownEl.classList.add('d-none');
        resendBtn.classList.remove('d-none');
      }
    }, 1000);
  }

  // =====================
  // step 2 — verify otp
  // =====================
  const verifyOtpForm = document.getElementById('verifyOtpForm');
  const verifyOtpBtn = document.getElementById('verifyOtpBtn');
  const otpError = document.getElementById('otpError');

  verifyOtpForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    hideAlert();
    clearFieldError(otpError);

    const code = otpCode.value;
    if (code.length !== 6) {
      markOtpInvalid();
      showFieldError(otpError, 'Please enter the complete 6-digit OTP.');
      return;
    }

    setLoading(verifyOtpBtn, true);

    try {
      const form = new FormData();
      form.append('otp_code', code);

      const res = await fetch('index.php?page=forgot_password_verify', {
        method: 'POST',
        body: form,
      });
      const data = await res.json();

      if (data.success) {
        stopOtpTimer();
        clearInterval(cooldownInterval);
        goToStep(3);
      } else {
        markOtpInvalid();
        showAlert('danger', data.message);
      }
    } catch (err) {
      showAlert('danger', 'Something went wrong. Please try again.');
    } finally {
      setLoading(verifyOtpBtn, false);
    }
  });

  // =====================
  // resend otp
  // =====================
  document
    .getElementById('resendBtn')
    .addEventListener('click', async function () {
      hideAlert();
      this.disabled = true;

      try {
        const res = await fetch('index.php?page=forgot_password_resend', {
          method: 'POST',
        });
        const data = await res.json();

        if (data.success) {
          showAlert('success', data.message);
          clearOtpBoxes();
          document.getElementById('verifyOtpBtn').disabled = false;
          startOtpTimer();
          startCooldown();
          focusFirstOtpBox();
        } else {
          showAlert('danger', data.message);
          this.disabled = false;
        }
      } catch (err) {
        showAlert('danger', 'Something went wrong. Please try again.');
        this.disabled = false;
      }
    });

  // =====================
  // step 3 — reset password
  // =====================
  const resetPasswordForm = document.getElementById('resetPasswordForm');
  const resetPasswordBtn = document.getElementById('resetPasswordBtn');
  const newPasswordInput = document.getElementById('newPassword');
  const confirmPassInput = document.getElementById('confirmPassword');
  const newPasswordError = document.getElementById('newPasswordError');
  const confirmPassError = document.getElementById('confirmPasswordError');
  const strengthBar = document.getElementById('strengthBar');

  // password toggle
  document
    .getElementById('toggleNewPass')
    .addEventListener('click', function () {
      togglePasswordVisibility('newPassword', this);
    });

  document
    .getElementById('toggleConfirmPass')
    .addEventListener('click', function () {
      togglePasswordVisibility('confirmPassword', this);
    });

  function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.className = 'bi bi-eye-slash-fill';
    } else {
      input.type = 'password';
      icon.className = 'bi bi-eye-fill';
    }
  }

  // password strength + requirements
  newPasswordInput.addEventListener('input', function () {
    const val = this.value;
    newPasswordInput.classList.remove('is-invalid');
    clearFieldError(newPasswordError);
    checkRequirements(val);
    updateStrengthBar(val);
  });

  confirmPassInput.addEventListener('input', function () {
    confirmPassInput.classList.remove('is-invalid');
    clearFieldError(confirmPassError);
  });

  function checkRequirements(val) {
    const rules = {
      length: val.length >= 8,
      uppercase: /[A-Z]/.test(val),
      lowercase: /[a-z]/.test(val),
      number: /[0-9]/.test(val),
      special: /[\W_]/.test(val),
    };

    document.querySelectorAll('#step3 .requirement').forEach((el) => {
      const rule = el.dataset.rule;
      const icon = el.querySelector('i');
      if (rules[rule]) {
        el.classList.add('met');
        icon.className = 'bi bi-check-circle-fill';
      } else {
        el.classList.remove('met');
        icon.className = 'bi bi-x-circle-fill';
      }
    });

    return Object.values(rules).every(Boolean);
  }

  function updateStrengthBar(val) {
    const metCount = [
      val.length >= 8,
      /[A-Z]/.test(val),
      /[a-z]/.test(val),
      /[0-9]/.test(val),
      /[\W_]/.test(val),
    ].filter(Boolean).length;

    strengthBar.className = 'strength-bar';
    if (metCount <= 2) strengthBar.classList.add('weak');
    else if (metCount <= 4) strengthBar.classList.add('medium');
    else strengthBar.classList.add('strong');
  }

  resetPasswordForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    hideAlert();
    clearFieldError(newPasswordError);
    clearFieldError(confirmPassError);

    const newPass = newPasswordInput.value;
    const confirmPass = confirmPassInput.value;
    let hasError = false;

    if (!newPass) {
      showFieldError(newPasswordError, 'Password is required.');
      newPasswordInput.classList.add('is-invalid');
      hasError = true;
    }

    if (newPass && !checkRequirements(newPass)) {
      showFieldError(
        newPasswordError,
        'Password does not meet all requirements.'
      );
      newPasswordInput.classList.add('is-invalid');
      hasError = true;
    }

    if (newPass !== confirmPass) {
      showFieldError(confirmPassError, 'Passwords do not match.');
      confirmPassInput.classList.add('is-invalid');
      hasError = true;
    }

    if (hasError) return;

    setLoading(resetPasswordBtn, true);

    try {
      const form = new FormData();
      form.append('new_password', newPass);
      form.append('confirm_password', confirmPass);

      const res = await fetch('index.php?page=forgot_password_reset', {
        method: 'POST',
        body: form,
      });
      const data = await res.json();

      if (data.success) {
        goToStep('success');
      } else {
        showAlert('danger', data.message);
      }
    } catch (err) {
      showAlert('danger', 'Something went wrong. Please try again.');
    } finally {
      setLoading(resetPasswordBtn, false);
    }
  });
});
