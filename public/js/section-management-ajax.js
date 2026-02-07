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

/* DELETE section handlers */

// populate delete modal when delete button is clicked
document.querySelectorAll('.delete-section-btn').forEach((button) => {
  button.addEventListener('click', function () {
    const sectionId = this.dataset.sectionId;
    const sectionName = this.dataset.sectionName;

    // populate modal
    document.getElementById('deleteSectionId').value = sectionId;
    document.getElementById('deleteSectionName').textContent = sectionName;

    // show modal
    const modal = new bootstrap.Modal(
      document.getElementById('deleteSectionModal')
    );
    modal.show();
  });
});

// handle delete form submission
document
  .getElementById('deleteSectionForm')
  ?.addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);

    // set loading state
    setButtonLoading(submitButton, true);

    try {
      const response = await fetch('index.php?page=delete_section', {
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

        // close modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById('deleteSectionModal')
        );
        modal.hide();

        // reload page to remove deleted section
        setTimeout(() => {
          window.location.reload();
        }, 1000);
      } else {
        // show error message
        showToast(data.message, 'danger');
      }
    } catch (error) {
      console.error('Error:', error);
      showToast('An error occurred. Please try again.', 'danger');
    } finally {
      setButtonLoading(submitButton, false);
    }
  });

/* SEARCH functionality */
document.getElementById('searchInput')?.addEventListener('input', function (e) {
  const searchTerm = e.target.value.toLowerCase();
  const tableRows = document.querySelectorAll('#sectionsTableBody tr');

  tableRows.forEach((row) => {
    // skip empty state row
    if (row.querySelector('.empty-state')) {
      return;
    }

    const sectionName =
      row.querySelector('.section-name')?.textContent.toLowerCase() || '';
    const educationLevel =
      row.querySelector('.education-level-badge')?.textContent.toLowerCase() ||
      '';
    const yearLevel =
      row.querySelector('.year-level')?.textContent.toLowerCase() || '';
    const strandCourse =
      row.querySelector('.strand-course')?.textContent.toLowerCase() || '';
    const schoolYear =
      row.querySelector('.school-year')?.textContent.toLowerCase() || '';

    const matches =
      sectionName.includes(searchTerm) ||
      educationLevel.includes(searchTerm) ||
      yearLevel.includes(searchTerm) ||
      strandCourse.includes(searchTerm) ||
      schoolYear.includes(searchTerm);

    if (matches) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });

  // check if any rows are visible
  const visibleRows = Array.from(tableRows).filter((row) => {
    return row.style.display !== 'none' && !row.querySelector('.empty-state');
  });

  // show/hide empty state based on visible rows
  const emptyStateRow = document
    .querySelector('#sectionsTableBody .empty-state')
    ?.closest('tr');
  if (emptyStateRow) {
    emptyStateRow.style.display = visibleRows.length === 0 ? '' : 'none';
  }
});

/* SCHOOL year filter */
document
  .getElementById('schoolYearFilter')
  ?.addEventListener('change', function (e) {
    const selectedYear = e.target.value;
    // reload page with selected school year
    window.location.href = `index.php?page=manage_sections&school_year=${selectedYear}`;
  });
