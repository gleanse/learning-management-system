// toast helper
function showToast(type, message) {
  const container = document.getElementById('toastContainer');
  const icons = {
    success: 'bi-check-circle-fill',
    danger: 'bi-exclamation-circle-fill',
  };
  const labels = { success: 'Success', danger: 'Error' };

  const toast = document.createElement('div');
  toast.className = `toast toast-${type} show`;
  toast.innerHTML = `
        <div class="toast-header">
            <i class="bi ${icons[type]} me-2"></i>
            <strong class="me-auto">${labels[type]}</strong>
            <button type="button" class="btn-close" onclick="this.closest('.toast').remove()"></button>
        </div>
        <div class="toast-body">${message}</div>
    `;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 5000);
}

// current page tracker
let currentPage = 1;
let currentSearch = '';

// debounce timer
let searchTimer;

// do ajax search and table refresh
async function doSearch(page = 1) {
  currentPage = page;

  const tableBody = document.getElementById('profilesTableBody');
  const paginationEl = document.getElementById('paginationWrapper');

  // show loading state
  tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4">
                <span class="spinner-border spinner-border-sm me-2"></span> Loading...
            </td>
        </tr>
    `;

  try {
    const res = await fetch(
      `index.php?page=ajax_search_student_profiles&search=${encodeURIComponent(
        currentSearch
      )}&p=${page}`,
      { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
    );
    const data = await res.json();

    if (!data.success) {
      showToast('danger', data.message || 'Failed to load students.');
      return;
    }

    // update table
    tableBody.innerHTML =
      data.html ||
      `
            <tr>
                <td colspan="6">
                    <div class="empty-state py-4">
                        <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                        <p class="empty-state-text">No students found.</p>
                    </div>
                </td>
            </tr>
        `;

    // rebuild pagination
    if (paginationEl) {
      paginationEl.innerHTML = buildPagination(
        data.current_page,
        data.total_pages,
        data.total
      );
    }
  } catch (err) {
    showToast('danger', 'An unexpected error occurred. Please try again.');
  }
}

// build pagination html
function buildPagination(page, totalPages, total) {
  if (totalPages <= 1) return '';

  let html = `<nav><ul class="pagination justify-content-center mb-0">`;

  html += `<li class="page-item ${page <= 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${
          page - 1
        }"><i class="bi bi-chevron-left"></i></a>
    </li>`;

  const start = Math.max(1, page - 2);
  const end = Math.min(totalPages, page + 2);

  for (let i = start; i <= end; i++) {
    html += `<li class="page-item ${i === page ? 'active' : ''}">
            <a class="page-link" href="#" data-page="${i}">${i}</a>
        </li>`;
  }

  html += `<li class="page-item ${page >= totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${
          page + 1
        }"><i class="bi bi-chevron-right"></i></a>
    </li>`;

  html += `</ul></nav>
    <div class="text-center mt-2 text-muted small">
        Showing page ${page} of ${totalPages} (${total} total students)
    </div>`;

  return html;
}

document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('searchInput');

  // live search with debounce
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      currentSearch = this.value;
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => doSearch(1), 400);
    });
  }

  // pagination click — delegated
  document.addEventListener('click', function (e) {
    const pageLink = e.target.closest('[data-page]');
    if (!pageLink) return;
    e.preventDefault();

    const page = parseInt(pageLink.dataset.page);
    if (!isNaN(page) && page > 0) {
      doSearch(page);
    }
  });

  // flash messages on load
  if (typeof profileConfig !== 'undefined') {
    if (profileConfig.success) showToast('success', profileConfig.success);
    if (profileConfig.error) showToast('danger', profileConfig.error);
  }
});
