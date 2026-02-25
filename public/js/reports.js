document.addEventListener('DOMContentLoaded', function() {
    // initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // show toast if there are messages
    if (typeof reportConfig !== 'undefined') {
        if (reportConfig.success) {
            showToast('success', 'Success', reportConfig.success);
        }
        if (reportConfig.error) {
            showToast('danger', 'Error', reportConfig.error);
        }
    }

    // export button handlers
    document.getElementById('exportCsvBtn')?.addEventListener('click', function(e) {
        // optional: show loading state
        this.innerHTML = '<i class="bi bi-hourglass-split"></i> exporting...';
    });

    document.getElementById('exportPdfBtn')?.addEventListener('click', function(e) {
        this.innerHTML = '<i class="bi bi-hourglass-split"></i> exporting...';
    });

    // filter item click tracking
    document.querySelectorAll('.filter-item').forEach(item => {
        item.addEventListener('click', function(e) {
            // optional: add loading state when changing filters
            document.body.style.cursor = 'wait';
        });
    });

    // tab click tracking
    document.querySelectorAll('.tab-item').forEach(tab => {
        tab.addEventListener('click', function(e) {
            document.body.style.cursor = 'wait';
        });
    });

    // trends filter form submit
    document.getElementById('trendsFilterForm')?.addEventListener('submit', function() {
        document.body.style.cursor = 'wait';
    });

    // table row hover effects (optional)
    document.querySelectorAll('.data-table tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transition = 'background 0.15s ease';
        });
    });

    // auto-hide toasts after 5 seconds
    setTimeout(function() {
        document.querySelectorAll('.toast').forEach(toastEl => {
            let toast = bootstrap.Toast.getInstance(toastEl);
            if (toast) toast.hide();
        });
    }, 5000);
});

// show toast function (copied from your academic-period.js pattern)
function showToast(type, title, message) {
    // create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.id = 'toastContainer';
        document.body.appendChild(toastContainer);
    }

    // create unique id for this toast
    const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

    // toast html structure (matching your academic period style)
    const toastHtml = `
        <div id="${toastId}" class="toast toast-${type}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
            <div class="toast-header">
                <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

    // append toast
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);

    // initialize and show
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();

    // remove after hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// format number with commas (optional helper)
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// format currency (optional helper)
function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// export to csv via ajax (alternative to direct link)
function exportReportAjax(type, format, interval, filters = {}) {
    const btn = document.getElementById(`export${format.toUpperCase()}Btn`);
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> exporting...';
    btn.disabled = true;

    let url = `index.php?page=reports&action=export&type=${type}&format=${format}&interval=${interval}`;
    
    // add trend filters if present
    if (interval === 'trends') {
        if (filters.start_date) url += `&start_date=${filters.start_date}`;
        if (filters.end_date) url += `&end_date=${filters.end_date}`;
        if (filters.trend_interval) url += `&trend_interval=${filters.trend_interval}`;
    }

    // for pdf/csv download via ajax
    fetch(url)
        .then(response => response.blob())
        .then(blob => {
            // create download link
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = `${type}_report_${new Date().toISOString().split('T')[0]}.${format}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(downloadUrl);
            a.remove();
        })
        .catch(error => {
            showToast('danger', 'Export Failed', 'Failed to export report. Please try again.');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
}

// print report function
function printReport() {
    window.print();
}

// reload data with new interval (if using ajax)
function loadReportData(type, interval, filters = {}) {
    // show loading state
    document.body.style.cursor = 'wait';
    
    let url = `index.php?page=reports&type=${type}&interval=${interval}&ajax=1`;
    
    // add trend filters
    if (interval === 'trends') {
        if (filters.start_date) url += `&start_date=${filters.start_date}`;
        if (filters.end_date) url += `&end_date=${filters.end_date}`;
        if (filters.trend_interval) url += `&trend_interval=${filters.trend_interval}`;
    }
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            // update report content
            document.querySelector('.report-content').innerHTML = html;
        })
        .catch(error => {
            showToast('danger', 'Load Failed', 'Failed to load report data.');
        })
        .finally(() => {
            document.body.style.cursor = 'default';
        });
}

// search/filter table rows
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toUpperCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell) {
                const textValue = cell.textContent || cell.innerText;
                if (textValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}

// sort table by column
function sortTable(tableId, columnIndex) {
    const table = document.getElementById(tableId);
    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.rows);
    const isAsc = table.dataset.sortDir === 'asc';
    
    rows.sort((a, b) => {
        const aVal = a.cells[columnIndex].textContent.trim();
        const bVal = b.cells[columnIndex].textContent.trim();
        
        // check if numeric
        if (!isNaN(parseFloat(aVal)) && !isNaN(parseFloat(bVal))) {
            return isAsc ? parseFloat(aVal) - parseFloat(bVal) : parseFloat(bVal) - parseFloat(aVal);
        }
        
        // string comparison
        return isAsc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });
    
    // reorder rows
    rows.forEach(row => tbody.appendChild(row));
    
    // toggle sort direction
    table.dataset.sortDir = isAsc ? 'desc' : 'asc';
    
    // update sort icons
    const headers = table.tHead.rows[0].cells;
    Array.from(headers).forEach((header, idx) => {
        const icon = header.querySelector('.sort-icon');
        if (idx === columnIndex) {
            if (icon) {
                icon.className = `bi bi-arrow-${isAsc ? 'down' : 'up'} sort-icon ms-1`;
            }
        } else if (icon) {
            icon.className = 'bi bi-arrow-down-up sort-icon ms-1 opacity-50';
        }
    });
}

// export current table view to csv
function exportTableToCsv(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    for (let row of rows) {
        const cells = row.querySelectorAll('td, th');
        const rowData = [];
        for (let cell of cells) {
            rowData.push('"' + cell.textContent.trim().replace(/"/g, '""') + '"');
        }
        csv.push(rowData.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
