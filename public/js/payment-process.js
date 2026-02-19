document.addEventListener('DOMContentLoaded', function () {
  initSearch();
  initPaymentPreview();
});

// search logic
function initSearch() {
  const input = document.getElementById('studentSearchInput');
  const btn = document.getElementById('searchBtn');
  const results = document.getElementById('searchResults');

  if (!input || !btn) return;

  let searchTimeout = null;

  input.addEventListener('input', function () {
    clearTimeout(searchTimeout);
    const q = this.value.trim();

    if (q.length < 2) {
      results.classList.add('d-none');
      results.innerHTML = '';
      return;
    }

    // debounce — wait 350ms after typing stops
    searchTimeout = setTimeout(() => fetchStudents(q), 350);
  });

  btn.addEventListener('click', function () {
    const q = input.value.trim();
    if (q.length >= 2) fetchStudents(q);
  });

  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      const q = this.value.trim();
      if (q.length >= 2) fetchStudents(q);
    }
  });

  // close dropdown when clicking outside
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.search-card')) {
      results.classList.add('d-none');
    }
  });
}

function fetchStudents(q) {
  const results = document.getElementById('searchResults');

  fetch(`${PAYMENT_DATA.ajaxUrls.search}&q=${encodeURIComponent(q)}`)
    .then((res) => res.json())
    .then((data) => {
      if (!data.success || !data.data.length) {
        results.innerHTML =
          '<div class="search-no-results"><i class="bi bi-person-x"></i> No students found.</div>';
        results.classList.remove('d-none');
        return;
      }

      results.innerHTML = data.data.map((s) => buildResultItem(s)).join('');
      results.classList.remove('d-none');
    })
    .catch(() => {
      results.innerHTML =
        '<div class="search-no-results">Search failed. Try again.</div>';
      results.classList.remove('d-none');
    });
}

function buildResultItem(s) {
  const initials =
    s.first_name.charAt(0).toUpperCase() + s.last_name.charAt(0).toUpperCase();
  const name = `${s.last_name}, ${s.first_name}`;
  const meta = `${s.student_number} · ${s.year_level} · ${s.strand_course}`;

  return `
        <a class="search-result-item" href="index.php?page=payment_process&student_id=${
          s.student_id
        }">
            <div class="result-avatar">${initials}</div>
            <div class="result-info">
                <span class="result-name">${escapeHtml(name)}</span>
                <span class="result-meta">${escapeHtml(meta)}</span>
            </div>
            <i class="bi bi-chevron-right result-arrow"></i>
        </a>
    `;
}

// live payment preview
function initPaymentPreview() {
  const amountInput = document.getElementById('amount_paid');
  if (!amountInput) return;

  amountInput.addEventListener('input', updatePreview);
}

function updatePreview() {
  const input = document.getElementById('amount_paid');
  const preview = document.getElementById('previewAmount');
  const remaining = document.getElementById('previewRemaining');

  if (!input || !preview || !remaining) return;

  const amount = parseFloat(input.value) || 0;
  const bal = parseFloat(PAYMENT_DATA.remaining) || 0;
  const after = Math.max(0, bal - amount);

  preview.textContent =
    '₱' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  remaining.textContent =
    '₱' + after.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

  // turn remaining red if overpaying
  remaining.style.color = amount > bal ? 'var(--danger)' : '';
}

// print receipt
function printReceipt() {
  const content = document.getElementById('receiptContent');
  if (!content) return;

  const win = window.open('', '_blank', 'width=400,height=600');
  win.document.write(`
        <html>
        <head>
            <title>Payment Record</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: 'Courier New', Courier, monospace; padding: 1.5rem; font-size: 13px; }
                .receipt-school { text-align: center; margin-bottom: 0.75rem; }
                .receipt-school strong { display: block; font-size: 14px; font-weight: 800; letter-spacing: 0.02em; }
                .receipt-school span { display: block; font-size: 11px; font-weight: 700; margin-top: 0.25rem; letter-spacing: 0.05em; }
                .receipt-school small { display: block; font-size: 11px; color: #555; margin-top: 0.25rem; font-family: system-ui; }
                .receipt-divider { border-top: 1px dashed #aaa; margin: 0.75rem 0; }
                .receipt-meta { display: flex; flex-direction: column; gap: 0.3rem; }
                .receipt-meta-row { display: flex; justify-content: space-between; font-size: 12px; }
                .receipt-meta-row span:first-child { color: #555; font-family: system-ui; }
                .receipt-meta-row span:last-child { font-weight: 700; text-align: right; max-width: 60%; }
                .receipt-amount-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; }
                .receipt-amount-row span { font-size: 13px; font-weight: 600; font-family: system-ui; }
                .receipt-amount-row strong { font-size: 22px; font-weight: 800; }
                .receipt-footer-note { text-align: center; margin-top: 0.5rem; font-family: system-ui; }
                .receipt-footer-note p { font-size: 12px; font-weight: 600; margin-bottom: 0.25rem; }
                .receipt-footer-note small { font-size: 11px; color: #555; }
            </style>
        </head>
        <body>${content.innerHTML}</body>
        </html>
    `);
  win.document.close();
  win.focus();
  win.print();
  win.close();
}

// toast helper
function showToast(type, message) {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const id = 'toast_' + Date.now();
  const html = `
        <div id="${id}" class="toast toast-${type}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-${
                  type === 'success'
                    ? 'check-circle-fill'
                    : type === 'warning'
                    ? 'exclamation-triangle-fill'
                    : 'x-circle-fill'
                } me-2"></i>
                ${
                  type === 'success'
                    ? 'Success'
                    : type === 'warning'
                    ? 'Warning'
                    : 'Error'
                }
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `;

  container.insertAdjacentHTML('beforeend', html);
  const el = document.getElementById(id);
  const toast = new bootstrap.Toast(el, { delay: 4000 });
  toast.show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.appendChild(document.createTextNode(str));
  return div.innerHTML;
}
