function showToast(message, type = 'success') {
  const toastContainer = document.getElementById('toastContainer');
  const toastId = 'toast-' + Date.now();

  const iconClass =
    type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
  const titleText = type === 'success' ? 'Success' : 'Error';

  const toastHTML = `
    <div id="${toastId}" class="toast toast-${type}" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <i class="bi ${iconClass} me-2"></i>
        <strong class="me-auto">${titleText}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        ${message}
      </div>
    </div>
  `;

  toastContainer.insertAdjacentHTML('beforeend', toastHTML);

  const toastElement = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastElement, {
    autohide: true,
    delay: 5000,
  });

  toast.show();

  toastElement.addEventListener('hidden.bs.toast', function () {
    toastElement.remove();
  });
}

function clearFormErrors(form) {
  const invalidInputs = form.querySelectorAll('.is-invalid');
  invalidInputs.forEach((input) => {
    input.classList.remove('is-invalid');
  });

  const errorMessages = form.querySelectorAll('.invalid-feedback');
  errorMessages.forEach((message) => {
    message.textContent = '';
  });
}

function displayFormErrors(form, errors) {
  clearFormErrors(form);

  Object.keys(errors).forEach((fieldName) => {
    const input = form.querySelector(`[name="${fieldName}"]`);
    if (input) {
      input.classList.add('is-invalid');
      const feedback = input.nextElementSibling;
      if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = errors[fieldName];
      }
    }
  });
}

function setButtonLoading(button, isLoading) {
  const spinner = button.querySelector('.spinner-border');
  const icon = button.querySelector('i:not(.spinner-border)');

  if (isLoading) {
    spinner.classList.remove('d-none');
    if (icon) icon.classList.add('d-none');
    button.disabled = true;
  } else {
    spinner.classList.add('d-none');
    if (icon) icon.classList.remove('d-none');
    button.disabled = false;
  }
}

/* CREATE section form handler */
document
  .getElementById('createSectionForm')
  ?.addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);

    // clear previous errors
    clearFormErrors(form);

    // set loading state
    setButtonLoading(submitButton, true);

    try {
      const response = await fetch('index.php?page=create_section', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        // show success message
        showToast(data.message, 'success');

        // redirect to manage sections after short delay
        setTimeout(() => {
          window.location.href = 'index.php?page=manage_sections';
        }, 1500);
      } else {
        // display validation errors
        if (data.errors) {
          displayFormErrors(form, data.errors);
        } else if (data.message) {
          showToast(data.message, 'danger');
        }
      }
    } catch (error) {
      console.error('Error:', error);
      showToast('An error occurred. Please try again.', 'danger');
    } finally {
      setButtonLoading(submitButton, false);
    }
  });
