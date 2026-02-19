// superadmin dashboard ajax handlers

// auto-refresh interval (30 seconds)
const REFRESH_INTERVAL = 30000;
let refreshTimer = null;

// initialize on dom loaded
document.addEventListener('DOMContentLoaded', function () {
  initializeDashboard();
});

function initializeDashboard() {
  // start auto-refresh
  startAutoRefresh();

  // refresh on visibility change (when user comes back to tab)
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) {
      refreshDashboardStats();
    }
  });
}

// start auto-refresh timer
function startAutoRefresh() {
  // initial load happens from php, so we start timer for subsequent refreshes
  refreshTimer = setInterval(() => {
    refreshDashboardStats();
  }, REFRESH_INTERVAL);
}

// stop auto-refresh
function stopAutoRefresh() {
  if (refreshTimer) {
    clearInterval(refreshTimer);
    refreshTimer = null;
  }
}

// refresh all dashboard stats via ajax
function refreshDashboardStats() {
  fetch('index.php?page=ajax_dashboard_stats', {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateStats(data.stats);
      }
    })
    .catch((error) => {
      console.error('error refreshing dashboard stats:', error);
    });
}

// update stats in dom with animation
function updateStats(stats) {
  // update users by role
  updateStatValue('stat-students', stats.users_by_role.student || 0);
  updateStatValue('stat-teachers', stats.users_by_role.teacher || 0);
  updateStatValue('stat-registrars', stats.users_by_role.registrar || 0);
  updateStatValue('stat-admins', stats.users_by_role.admin || 0);
  updateStatValue('stat-superadmins', stats.users_by_role.superadmin || 0);
  updateStatValue('stat-total', stats.total_users || 0);

  // update student account stats
  updateStatValue('stat-total-students', stats.total_students || 0);
  updateStatValue(
    'stat-students-with-account',
    stats.students_with_account || 0
  );
  updateStatValue(
    'stat-students-without-account',
    stats.students_without_account || 0
  );

  // show/hide create accounts link
  const createLink = document.getElementById('create-accounts-link');
  if (createLink) {
    if (stats.students_without_account > 0) {
      createLink.style.display = '';
    } else {
      createLink.style.display = 'none';
    }
  }

  // update recent users table if needed
  if (stats.recent_users && stats.recent_users.length > 0) {
    updateRecentUsersTable(stats.recent_users);
  }
}

// update single stat value with count-up animation
function updateStatValue(elementId, newValue) {
  const element = document.getElementById(elementId);
  if (!element) return;

  const currentValue = parseInt(element.textContent.replace(/,/g, '')) || 0;

  // only animate if value changed
  if (currentValue !== newValue) {
    animateValue(element, currentValue, newValue, 500);
  }
}

// animate number count-up
function animateValue(element, start, end, duration) {
  const range = end - start;
  const increment = range / (duration / 16);
  let current = start;

  const timer = setInterval(() => {
    current += increment;
    if (
      (increment > 0 && current >= end) ||
      (increment < 0 && current <= end)
    ) {
      current = end;
      clearInterval(timer);
    }
    element.textContent = formatNumber(Math.round(current));
  }, 16);

  // add pulse animation class
  element.parentElement.classList.add('stat-updated');
  setTimeout(() => {
    element.parentElement.classList.remove('stat-updated');
  }, 500);
}

// update recent users table
function updateRecentUsersTable(users) {
  const wrapper = document.getElementById('recent-users-table-wrapper');
  if (!wrapper) return;

  if (users.length === 0) {
    wrapper.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="empty-state-text">No users created yet</p>
            </div>
        `;
    return;
  }

  const tbody = document.getElementById('recent-users-tbody');
  if (!tbody) return;

  let html = '';
  users.forEach((user) => {
    const fullName = [user.first_name, user.middle_name, user.last_name]
      .filter(Boolean)
      .join(' ');

    html += `
            <tr>
                <td class="user-full-name">${escapeHtml(fullName)}</td>
                <td>${escapeHtml(user.username)}</td>
                <td>${
                  user.email
                    ? escapeHtml(user.email)
                    : '<span class="text-muted">-</span>'
                }</td>
                <td>${getRoleBadge(user.role)}</td>
                <td>${getStatusBadge(user.status)}</td>
                <td>
                    <span class="text-muted">
                        <i class="bi bi-clock"></i>
                        ${formatDate(user.created_at)}
                    </span>
                </td>
                <td>
                    ${
                      user.created_by_username
                        ? `<span class="admin-badge"><i class="bi bi-person-badge-fill"></i>${escapeHtml(
                            user.created_by_username
                          )}</span>`
                        : '<span class="text-muted">System</span>'
                    }
                </td>
            </tr>
        `;
  });

  tbody.innerHTML = html;
}

// get role badge html
function getRoleBadge(role) {
  const badges = {
    student: '<span class="badge bg-primary">Student</span>',
    teacher: '<span class="badge bg-success">Teacher</span>',
    registrar: '<span class="badge bg-warning">Registrar</span>',
    admin: '<span class="badge bg-danger">Admin</span>',
    superadmin: '<span class="badge bg-secondary">Super Admin</span>',
  };
  return (
    badges[role] ||
    `<span class="badge bg-secondary">${escapeHtml(role)}</span>`
  );
}

// get status badge html
function getStatusBadge(status) {
  const badges = {
    active: '<span class="badge bg-success">Active</span>',
    inactive: '<span class="badge bg-secondary">Inactive</span>',
    suspended: '<span class="badge bg-danger">Suspended</span>',
  };
  return (
    badges[status] ||
    `<span class="badge bg-secondary">${escapeHtml(status)}</span>`
  );
}

// format number with commas
function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// format date
function formatDate(dateString) {
  const date = new Date(dateString);
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return date.toLocaleDateString('en-US', options);
}

// escape html
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// cleanup on page unload
window.addEventListener('beforeunload', function () {
  stopAutoRefresh();
});
