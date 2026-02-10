document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('searchInput');
  const schoolYearFilter = document.getElementById('schoolYearFilter');
  const sectionsContainer = document.getElementById('sections-container');
  const deleteSectionForm = document.getElementById('deleteSectionForm');
  let searchTimeout;

  // fetch sections with current filters
  const fetchSections = async (page = 1) => {
    const search = searchInput.value;
    const schoolYear = schoolYearFilter.value;
    const url = `index.php?page=ajax_search_sections&p=${page}&search=${encodeURIComponent(
      search
    )}&school_year=${schoolYear}`;

    try {
      const response = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
      const data = await response.json();
      if (data.success) {
        sectionsContainer.innerHTML = data.html;
        currentPage = data.current_page; // update global current page
      } else {
        showToast('Failed to load sections.', 'danger');
      }
    } catch (error) {
      console.error('Fetch error:', error);
      showToast('An error occurred while fetching data.', 'danger');
    }
  };

  // debounce search input to avoid excessive requests
  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      fetchSections(1); // reset to page 1 for new search
    }, 300);
  });

  // handle school year change
  schoolYearFilter.addEventListener('change', () => {
    fetchSections(1); // reset to page 1 for new filter
  });

  // event delegation for pagination and delete buttons
  sectionsContainer.addEventListener('click', (e) => {
    // pagination links
    const pageLink = e.target.closest('.page-link');
    if (pageLink && !pageLink.closest('.disabled')) {
      e.preventDefault();
      const page = pageLink.dataset.page;
      fetchSections(page);
    }

    // delete buttons
    const deleteButton = e.target.closest('.btn-delete');
    if (deleteButton) {
      const sectionId = deleteButton.dataset.sectionId;
      const sectionName = deleteButton.dataset.sectionName;

      // populate and show modal
      document.getElementById('deleteSectionId').value = sectionId;
      document.getElementById('deleteSectionName').textContent = sectionName;
      const deleteModal = new bootstrap.Modal(
        document.getElementById('deleteSectionModal')
      );
      deleteModal.show();
    }
  });

  // handle delete form submission
  deleteSectionForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const submitButton = e.target.querySelector('button[type="submit"]');
    const formData = new FormData(e.target);

    setButtonLoading(submitButton, true);

    try {
      const response = await fetch('index.php?page=delete_section', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        showToast(data.message, 'success');
        const modal = bootstrap.Modal.getInstance(
          document.getElementById('deleteSectionModal')
        );
        modal.hide();

        // remove the row from the table without reloading the page
        const sectionId = formData.get('section_id');
        const rowToRemove = document.querySelector(
          `tr[data-section-id="${sectionId}"]`
        );

        if (rowToRemove) {
          rowToRemove.style.transition = 'opacity 0.5s';
          rowToRemove.style.opacity = '0';
          setTimeout(() => {
            rowToRemove.remove();
            fetchSections(currentPage);
          }, 500);
        } else {
          // if row not found, just refresh
          fetchSections(currentPage);
        }
      } else {
        showToast(data.message || 'Failed to delete section.', 'danger');
      }
    } catch (error) {
      console.error('Delete error:', error);
      showToast('An error occurred. Please try again.', 'danger');
    } finally {
      setButtonLoading(submitButton, false);
    }
  });
});

// utility functions

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

function setButtonLoading(button, isLoading) {
  const spinner = button.querySelector('.spinner-border');
  const icon = button.querySelector('i:not(.spinner-border)');

  if (isLoading) {
    button.disabled = true;
    if (spinner) spinner.classList.remove('d-none');
    if (icon) icon.classList.add('d-none');
  } else {
    button.disabled = false;
    if (spinner) spinner.classList.add('d-none');
    if (icon) icon.classList.remove('d-none');
  }
}
