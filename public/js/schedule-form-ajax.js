// schedule form ajax operations (shared between create and edit)

// initialize on page load
document.addEventListener('DOMContentLoaded', function () {
  // time validation on change
  const startTimeInput = document.getElementById('startTime');
  const endTimeInput = document.getElementById('endTime');

  if (startTimeInput && endTimeInput) {
    startTimeInput.addEventListener('change', validateTimes);
    endTimeInput.addEventListener('change', validateTimes);
  }

  // form submission validation
  const scheduleForm = document.getElementById('scheduleForm');
  if (scheduleForm) {
    scheduleForm.addEventListener('submit', function (event) {
      // clear previous custom validation
      clearCustomValidation();

      // validate times
      if (!validateTimes()) {
        event.preventDefault();
        return false;
      }
    });
  }
});

// validate start and end times
function validateTimes() {
  const startTimeInput = document.getElementById('startTime');
  const endTimeInput = document.getElementById('endTime');

  if (!startTimeInput || !endTimeInput) return true;

  const startTime = startTimeInput.value;
  const endTime = endTimeInput.value;

  if (!startTime || !endTime) return true;

  // compare times
  if (startTime >= endTime) {
    // show error on end time field
    endTimeInput.classList.add('is-invalid');

    // create or update error message
    let errorDiv = endTimeInput.parentElement.querySelector(
      '.time-validation-error'
    );
    if (!errorDiv) {
      errorDiv = document.createElement('div');
      errorDiv.className = 'invalid-feedback d-block time-validation-error';
      endTimeInput.parentElement.appendChild(errorDiv);
    }
    errorDiv.textContent = 'End time must be after start time.';

    return false;
  } else {
    // remove error
    endTimeInput.classList.remove('is-invalid');
    const errorDiv = endTimeInput.parentElement.querySelector(
      '.time-validation-error'
    );
    if (errorDiv) {
      errorDiv.remove();
    }
    return true;
  }
}

// clear custom validation
function clearCustomValidation() {
  const endTimeInput = document.getElementById('endTime');
  if (endTimeInput) {
    endTimeInput.classList.remove('is-invalid');
    const errorDiv = endTimeInput.parentElement.querySelector(
      '.time-validation-error'
    );
    if (errorDiv) {
      errorDiv.remove();
    }
  }
}
