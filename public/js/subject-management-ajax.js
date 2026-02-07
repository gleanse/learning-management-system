// toast notification function
function showAlert(type, message) {
  const toastContainer = document.getElementById('toastContainer');
  const toastId = 'toast-' + Date.now();

  // determine header text based on message content
  let headerText = 'Success';
  if (type === 'danger') {
    if (message.toLowerCase().includes('validation')) {
      headerText = 'Validation Failed';
    } else if (message.toLowerCase().includes('not found')) {
      headerText = 'Not Found';
    } else if (message.toLowerCase().includes('unauthorized')) {
      headerText = 'Unauthorized';
    } else if (message.toLowerCase().includes('failed')) {
      headerText = 'Failed';
    } else {
      headerText = 'Error';
    }
  }

  const toastHTML = `
        <div id="${toastId}" class="toast toast-${type}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi ${
                  type === 'success'
                    ? 'bi-check-circle-fill'
                    : 'bi-exclamation-triangle-fill'
                } me-2"></i>
                <strong class="me-auto">${headerText}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

  toastContainer.insertAdjacentHTML('beforeend', toastHTML);
  const toastElement = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
  toast.show();

  toastElement.addEventListener('hidden.bs.toast', function () {
    toastElement.remove();
  });
}

// clear form validation errors
function clearFormErrors(form) {
  const inputs = form.querySelectorAll('.form-control');
  inputs.forEach((input) => {
    input.classList.remove('is-invalid');
    const feedback = input.parentElement.querySelector('.invalid-feedback');
    if (feedback) {
      feedback.textContent = '';
    }
  });
}

// show form validation errors
function showFormErrors(form, errors) {
  clearFormErrors(form);

  for (const [field, message] of Object.entries(errors)) {
    const input = form.querySelector(`[name="${field}"]`);
    if (input) {
      input.classList.add('is-invalid');
      const feedback = input.parentElement.querySelector('.invalid-feedback');
      if (feedback) {
        feedback.textContent = message;
      }
    }
  }
}

// handle create subject form
document.addEventListener('DOMContentLoaded', function () {
  const createForm = document.getElementById('createSubjectForm');

  if (createForm) {
    createForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const submitBtn = this.querySelector('button[type="submit"]');
      const spinner = submitBtn.querySelector('.spinner-border');
      const formData = new FormData(this);

      // disable button and show spinner
      submitBtn.disabled = true;
      spinner.classList.remove('d-none');
      clearFormErrors(this);

      fetch('index.php?page=create_subject_action', {
        method: 'POST',
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showAlert('success', data.message);
            // redirect after short delay
            setTimeout(() => {
              window.location.href = 'index.php?page=subjects';
            }, 1500);
          } else {
            if (data.errors) {
              showFormErrors(createForm, data.errors);
            }
            showAlert('danger', data.message);
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          showAlert('danger', 'an error occurred while creating subject');
          submitBtn.disabled = false;
          spinner.classList.add('d-none');
        });
    });
  }

  // handle edit subject form
  const editForm = document.getElementById('editSubjectForm');

  if (editForm) {
    editForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const submitBtn = this.querySelector('button[type="submit"]');
      const spinner = submitBtn.querySelector('.spinner-border');
      const formData = new FormData(this);

      // disable button and show spinner
      submitBtn.disabled = true;
      spinner.classList.remove('d-none');
      clearFormErrors(this);

      fetch('index.php?page=update_subject_action', {
        method: 'POST',
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showAlert('success', data.message);
            // redirect after short delay
            setTimeout(() => {
              window.location.href = 'index.php?page=subjects';
            }, 1500);
          } else {
            if (data.errors) {
              showFormErrors(editForm, data.errors);
            }
            showAlert('danger', data.message);
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          showAlert('danger', 'an error occurred while updating subject');
          submitBtn.disabled = false;
          spinner.classList.add('d-none');
        });
    });
  }

  // handle delete subject with event delegation
  const deleteModal = document.getElementById('deleteModal');
  const deleteForm = document.getElementById('deleteForm');

  if (deleteModal) {
    const bsDeleteModal = new bootstrap.Modal(deleteModal);

    // use event delegation for delete buttons (works with dynamically loaded content)
    document.addEventListener('click', function (e) {
      if (e.target.closest('.btn-delete')) {
        const button = e.target.closest('.btn-delete');
        const subjectId = button.getAttribute('data-subject-id');
        const subjectCode = button.getAttribute('data-subject-code');
        const subjectName = button.getAttribute('data-subject-name');

        document.getElementById('delete_subject_id').value = subjectId;
        document.getElementById('delete_subject_code').textContent =
          subjectCode;
        document.getElementById('delete_subject_name').textContent =
          subjectName;

        bsDeleteModal.show();
      }
    });

    if (deleteForm) {
      deleteForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const submitBtn = this.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');
        const formData = new FormData(this);

        // disable button and show spinner
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');

        fetch('index.php?page=delete_subject_action', {
          method: 'POST',
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              bsDeleteModal.hide();
              showAlert('success', data.message);

              // if AJAX search is active, refresh the table
              const searchInput = document.getElementById('searchInput');
              if (searchInput && window.subjectSearchHandler) {
                setTimeout(() => {
                  window.subjectSearchHandler.refresh();
                }, 1500);
              } else {
                // otherwise reload the page
                setTimeout(() => {
                  window.location.reload();
                }, 1500);
              }
            } else {
              showAlert('danger', data.message);
              submitBtn.disabled = false;
              spinner.classList.add('d-none');
            }
          })
          .catch((error) => {
            console.error('Error:', error);
            showAlert('danger', 'an error occurred while deleting subject');
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
          });
      });
    }
  }
});

// AJAX real-time search functionality
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('searchInput');
  const subjectsTableCard = document.querySelector('.subjects-table-card');

  if (searchInput && subjectsTableCard) {
    let searchTimeout;
    let currentPage = 1;
    let currentSearch = searchInput.value.trim();

    // create search handler object
    window.subjectSearchHandler = {
      performSearch: performSearch,
      refresh: function () {
        performSearch(currentSearch, currentPage);
      },
    };

    // function to perform AJAX search
    function performSearch(searchValue, page = 1) {
      const searchIcon = searchInput.parentElement.querySelector(
        '.input-group-text i'
      );
      const cardBody = subjectsTableCard.querySelector('.card-body');

      // show loading state with spinning icon
      searchIcon.classList.remove('bi-search');
      searchIcon.classList.add('bi-arrow-clockwise', 'spinning-icon');

      // show loading state with fade
      cardBody.style.opacity = '0.5';
      cardBody.style.pointerEvents = 'none';
      cardBody.style.transition = 'opacity 0.2s ease';

      // build URL
      const url = `index.php?page=ajax_search_subjects&search=${encodeURIComponent(
        searchValue
      )}&p=${page}`;

      fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            updateTable(data.html);
            currentPage = page;
            currentSearch = searchValue;

            // update URL without reloading
            updateURL(searchValue, page);

            // hide loading state
            cardBody.style.opacity = '1';
            cardBody.style.pointerEvents = 'auto';

            // restore search icon
            searchIcon.classList.remove('bi-arrow-clockwise', 'spinning-icon');
            searchIcon.classList.add('bi-search');

            // keep focus on search input
            searchInput.focus();
          } else {
            throw new Error(data.message || 'Failed to search subjects');
          }
        })
        .catch((error) => {
          console.error('Search error:', error);
          showAlert('danger', 'An error occurred while searching');

          // hide loading state
          cardBody.style.opacity = '1';
          cardBody.style.pointerEvents = 'auto';

          // restore search icon
          searchIcon.classList.remove('bi-arrow-clockwise', 'spinning-icon');
          searchIcon.classList.add('bi-search');
        });
    }

    // function to update table content
    function updateTable(html) {
      const cardBody = subjectsTableCard.querySelector('.card-body');
      cardBody.innerHTML = html;

      // attach pagination handlers
      attachPaginationHandlers();
    }

    // attach handlers to pagination links
    function attachPaginationHandlers() {
      const paginationLinks = subjectsTableCard.querySelectorAll(
        '.pagination .page-link'
      );

      paginationLinks.forEach((link) => {
        link.addEventListener('click', function (e) {
          e.preventDefault();

          // check if button is disabled
          if (this.parentElement.classList.contains('disabled')) {
            return;
          }

          const page = parseInt(this.getAttribute('data-page'));
          const searchValue = searchInput.value.trim();

          performSearch(searchValue, page);
        });
      });
    }

    // update URL without reloading
    function updateURL(searchValue, page) {
      const newUrl = new URL(window.location.href);

      if (searchValue) {
        newUrl.searchParams.set('search', searchValue);
      } else {
        newUrl.searchParams.delete('search');
      }

      if (page > 1) {
        newUrl.searchParams.set('p', page);
      } else {
        newUrl.searchParams.delete('p');
      }

      window.history.pushState({}, '', newUrl.toString());
    }

    // search input handler with debouncing
    searchInput.addEventListener('input', function () {
      clearTimeout(searchTimeout);

      searchTimeout = setTimeout(() => {
        const searchValue = this.value.trim();
        performSearch(searchValue, 1);
      }, 500);
    });

    // initial attachment of pagination handlers
    attachPaginationHandlers();
  }
});

// add CSS for spinning icon animation
const style = document.createElement('style');
style.textContent = `
  .spinning-icon {
    animation: spin-icon 0.8s linear infinite;
  }
  
  @keyframes spin-icon {
    from {
      transform: rotate(0deg);
    }
    to {
      transform: rotate(360deg);
    }
  }
`;
document.head.appendChild(style);
