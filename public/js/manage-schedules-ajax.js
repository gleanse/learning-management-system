// manage schedules ajax operations

// global variables
let deleteScheduleId = null;
let deleteModal = null;

// initialize on page load
document.addEventListener('DOMContentLoaded', function () {
  deleteModal = new bootstrap.Modal(
    document.getElementById('deleteConfirmModal')
  );

  // filter change listeners - auto submit form
  document
    .getElementById('filterSchoolYear')
    ?.addEventListener('change', submitFilters);
  document
    .getElementById('filterSemester')
    ?.addEventListener('change', submitFilters);
  document
    .getElementById('filterStatus')
    ?.addEventListener('change', submitFilters);

  // delete button listeners (event delegation)
  document.addEventListener('click', handleDeleteClick);

  // status toggle listeners (event delegation)
  document.addEventListener('change', handleStatusToggle);

  // confirm delete button
  document
    .getElementById('confirmDeleteBtn')
    ?.addEventListener('click', confirmDelete);
});

// submit filters form
function submitFilters() {
  document.getElementById('filtersForm')?.submit();
}

// handle delete button click
function handleDeleteClick(event) {
  const deleteBtn = event.target.closest('.delete-schedule-btn');
  if (deleteBtn) {
    deleteScheduleId = deleteBtn.getAttribute('data-schedule-id');
    deleteModal.show();
  }
}

// confirm delete schedule
function confirmDelete() {
  if (!deleteScheduleId) return;

  const formData = new FormData();
  formData.append('csrf_token', csrfToken);
  formData.append('schedule_id', deleteScheduleId);

  fetch('index.php?page=delete_schedule', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        deleteModal.hide();
        showAlert('success', data.message);

        // reload page after short delay
        setTimeout(() => {
          window.location.reload();
        }, 1000);
      } else {
        deleteModal.hide();
        showAlert('danger', data.message || 'failed to delete schedule.');
      }
    })
    .catch((error) => {
      console.error('error:', error);
      deleteModal.hide();
      showAlert('danger', 'an error occurred while deleting schedule.');
    })
    .finally(() => {
      deleteScheduleId = null;
    });
}

// handle status toggle
function handleStatusToggle(event) {
  if (event.target.classList.contains('status-toggle')) {
    const checkbox = event.target;
    const scheduleId = checkbox.getAttribute('data-schedule-id');
    const newStatus = checkbox.checked ? 'active' : 'inactive';

    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('schedule_id', scheduleId);
    formData.append('status', newStatus);

    fetch('index.php?page=toggle_schedule_status', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showAlert('success', data.message);

          // update status label
          const row = checkbox.closest('tr');
          const statusLabel = row.querySelector('.status-label');
          if (statusLabel) {
            statusLabel.textContent =
              data.new_status === 'active' ? 'Active' : 'Inactive';
          }
        } else {
          // revert checkbox state
          checkbox.checked = !checkbox.checked;
          showAlert('danger', data.message || 'failed to update status.');
        }
      })
      .catch((error) => {
        console.error('error:', error);
        // revert checkbox state
        checkbox.checked = !checkbox.checked;
        showAlert('danger', 'an error occurred while updating status.');
      });
  }
}

// show toast alert
function showAlert(type, message) {
  const toastContainer = document.getElementById('toastContainer');

  const toastId = 'toast-' + Date.now();
  const iconClass =
    type === 'success'
      ? 'bi-check-circle-fill'
      : 'bi-exclamation-triangle-fill';
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

  // remove toast element after it's hidden
  toastElement.addEventListener('hidden.bs.toast', function () {
    toastElement.remove();
  });
}
