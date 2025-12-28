function initLockoutCountdown(secondsRemaining) {
  const unlockTime = Date.now() + secondsRemaining * 1000;
  const countdownEl = document.getElementById('countdown');
  const lockoutMessage = document.getElementById('lockout-message');

  function updateCountdown() {
    const now = Date.now();
    const remaining = Math.max(0, Math.ceil((unlockTime - now) / 1000));

    if (remaining <= 0) {
      clearInterval(countdownInterval);

      if (lockoutMessage) {
        lockoutMessage.style.display = 'none';
      }

      return;
    }

    const minutes = Math.floor(remaining / 60);
    const seconds = remaining % 60;

    let display;
    if (minutes > 0) {
      display = `${minutes} minute${minutes > 1 ? 's' : ''} ${seconds} second${
        seconds !== 1 ? 's' : ''
      }`;
    } else {
      display = `${seconds} second${seconds !== 1 ? 's' : ''}`;
    }

    countdownEl.textContent = display;
  }

  updateCountdown();
  const countdownInterval = setInterval(updateCountdown, 1000);
}
