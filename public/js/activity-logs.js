document.addEventListener('DOMContentLoaded', function() {
    // initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // show toast if there are messages
    if (typeof activityLogConfig !== 'undefined') {
        if (activityLogConfig.success) {
            showToast('success', 'Success', activityLogConfig.success);
        }
        if (activityLogConfig.error) {
            showToast('danger', 'Error', activityLogConfig.error);
        }
    }

    // handle view details button clicks
    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const logId = this.dataset.logId;
            viewLogDetails(logId);
        });
    });

    // handle pagination clicks
    document.querySelectorAll('.page-link[data-page]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.dataset.page;
            if (page) {
                loadPage(page);
            }
        });
    });

    // handle filter form submit - preserve page number
    document.getElementById('filterForm')?.addEventListener('submit', function() {
        // reset to page 1 when applying new filters
        const url = new URL(window.location.href);
        url.searchParams.set('p', '1');
        window.location.href = url.toString();
    });

    // handle export button click - show loading state
    document.getElementById('exportBtn')?.addEventListener('click', function(e) {
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="bi bi-hourglass-split"></i> Exporting...';
        this.disabled = true;
        
        // re-enable after 2 seconds (prevents double-click)
        setTimeout(() => {
            this.innerHTML = originalText;
            this.disabled = false;
        }, 2000);
    });
});

// view log details in modal
function viewLogDetails(logId) {
    const modal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
    const modalBody = document.getElementById('logDetailsBody');
    
    // show loading
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // fetch log details
    fetch(`index.php?page=ajax_get_log_details&log_id=${logId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderLogDetails(data.log);
            } else {
                modalBody.innerHTML = `
                    <div class="empty-state py-4">
                        <div class="empty-state-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <p class="empty-state-text">${data.error || 'Failed to load log details'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `
                <div class="empty-state py-4">
                    <div class="empty-state-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <p class="empty-state-text">Failed to load log details</p>
                </div>
            `;
        });
}

// render log details in modal
function renderLogDetails(log) {
    const modalBody = document.getElementById('logDetailsBody');
    
    let html = `
        <div class="log-detail-section">
            <h6><i class="bi bi-info-circle-fill me-2"></i>Basic Information</h6>
            <div class="log-detail-grid">
                <div class="log-detail-item">
                    <span class="log-detail-label">Date/Time</span>
                    <span class="log-detail-value">${log.formatted_time}</span>
                </div>
                <div class="log-detail-item">
                    <span class="log-detail-label">User</span>
                    <span class="log-detail-value">${log.first_name} ${log.last_name} (${log.username})</span>
                </div>
                <div class="log-detail-item">
                    <span class="log-detail-label">Role</span>
                    <span class="log-detail-value">
                        <span class="user-role badge ${log.role === 'superadmin' ? 'badge-danger' : (log.role === 'admin' ? 'badge-warning' : 'badge-secondary')}">
                            ${log.role}
                        </span>
                    </span>
                </div>
                <div class="log-detail-item">
                    <span class="log-detail-label">Email</span>
                    <span class="log-detail-value">${log.email || 'N/A'}</span>
                </div>
            </div>
        </div>
    `;
    
    html += `
        <div class="log-detail-section">
            <h6><i class="bi bi-tag-fill me-2"></i>Action Details</h6>
            <div class="log-detail-grid">
                <div class="log-detail-item">
                    <span class="log-detail-label">Action</span>
                    <span class="log-detail-value">
                        <span class="action-badge action-${log.action.replace(/_/g, '-')}">
                            ${ucwords(log.action.replace(/_/g, ' '))}
                        </span>
                    </span>
                </div>
                <div class="log-detail-item">
                    <span class="log-detail-label">Description</span>
                    <span class="log-detail-value">${log.description || '—'}</span>
                </div>
                <div class="log-detail-item">
                    <span class="log-detail-label">Table</span>
                    <span class="log-detail-value">
                        ${log.table_affected ? 
                            `<span class="table-badge"><i class="bi bi-table"></i> ${ucwords(log.table_affected.replace(/_/g, ' '))}</span>` : 
                            '—'}
                    </span>
                </div>
                <div class="log-detail-item">
                    <span class="log-detail-label">Record ID</span>
                    <span class="log-detail-value">${log.record_id ? '#' + log.record_id : '—'}</span>
                </div>
            </div>
        </div>
    `;
    
    html += `
        <div class="log-detail-section">
            <h6><i class="bi bi-geo-alt-fill me-2"></i>Request Information</h6>
            <div class="log-detail-grid">
                <div class="log-detail-item">
                    <span class="log-detail-label">IP Address</span>
                    <span class="log-detail-value">
                        ${log.ip_address ? 
                            `<span class="ip-address">${log.ip_address}</span>` : 
                            '—'}
                    </span>
                </div>
                <div class="log-detail-item">
                    <span class="log-detail-label">User Agent</span>
                    <span class="log-detail-value small">${log.user_agent || '—'}</span>
                </div>
            </div>
        </div>
    `;
    
    // show old_data if exists
    if (log.old_data && Object.keys(log.old_data).length > 0) {
        html += `
            <div class="log-detail-section">
                <h6><i class="bi bi-arrow-left-circle-fill me-2"></i>Before Changes</h6>
                <pre class="log-json">${JSON.stringify(log.old_data, null, 2)}</pre>
            </div>
        `;
    }
    
    // show new_data if exists
    if (log.new_data && Object.keys(log.new_data).length > 0) {
        html += `
            <div class="log-detail-section">
                <h6><i class="bi bi-arrow-right-circle-fill me-2"></i>After Changes</h6>
                <pre class="log-json">${JSON.stringify(log.new_data, null, 2)}</pre>
            </div>
        `;
    }
    
    modalBody.innerHTML = html;
}

// load page via ajax (optional - currently uses full page reload)
function loadPage(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('p', page);
    window.location.href = url.toString();
}

// show toast function
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

    // toast html structure
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

// helper: uppercase words
function ucwords(str) {
    return (str + '').replace(/^(.)|\s+(.)/g, function($1) {
        return $1.toUpperCase();
    });
}

// helper: format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        hour12: true
    });
}

// auto-refresh every 60 seconds (optional - uncomment if wanted)
setInterval(function() {
    if (document.visibilityState === 'visible') {
        window.location.reload();
    }
}, 60000);