let secondsLeft;
let countdownInterval;

function initLockoutCountdown(seconds) {
    secondsLeft = seconds;
    
    function updateCountdown() {
        if (secondsLeft <= 0) {
            clearInterval(countdownInterval);
            document.getElementById('lockout-message').style.display = 'none';
            return;
        }
        
        const minutes = Math.floor(secondsLeft / 60);
        const seconds = secondsLeft % 60;
        
        let display;
        if (minutes > 0) {
            display = `${minutes} minute${minutes > 1 ? 's' : ''} ${seconds} second${seconds !== 1 ? 's' : ''}`;
        } else {
            display = `${seconds} second${seconds !== 1 ? 's' : ''}`;
        }
        
        document.getElementById('countdown').textContent = display;
        secondsLeft--;
    }
    
    updateCountdown();
    countdownInterval = setInterval(updateCountdown, 1000);
}