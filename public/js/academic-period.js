// toast helper
function showToast(type, message) {
  const container = document.getElementById('toastContainer');
  const icons = {
    success: 'bi-check-circle-fill',
    danger: 'bi-exclamation-circle-fill',
    warning: 'bi-exclamation-triangle-fill',
  };
  const labels = { success: 'Success', danger: 'Error', warning: 'Warning' };

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

// format number with commas
function formatNumber(n) {
  return parseInt(n).toLocaleString();
}

// format date string to readable format
function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: '2-digit',
    year: 'numeric',
  });
}

// format datetime string
function formatDateTime(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

// password verification helper verifies before critical actions
// returns true if verified, false if not
async function verifyPassword(modalId) {
  const modal = document.getElementById(modalId);
  const input = modal.querySelector('.password-verify-input');
  const errorEl = modal.querySelector('.password-verify-error');
  const submitBtn = modal.querySelector('.password-verify-submit');

  const password = input.value.trim();

  if (!password) {
    errorEl.textContent = 'Please enter your password.';
    errorEl.classList.remove('d-none');
    input.classList.add('is-invalid');
    return false;
  }

  // show loading state
  const originalHtml = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';
  errorEl.classList.add('d-none');
  input.classList.remove('is-invalid');

  try {
    const formData = new FormData();
    formData.append('password', password);

    const res = await fetch('index.php?page=ajax_verify_admin_password', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData,
    });
    const data = await res.json();

    if (data.success) {
      return true;
    } else {
      errorEl.textContent =
        data.message || 'Incorrect password. Please try again.';
      errorEl.classList.remove('d-none');
      input.classList.add('is-invalid');
      input.value = '';
      input.focus();
      return false;
    }
  } catch (err) {
    errorEl.textContent =
      'An error occurred while verifying. Please try again.';
    errorEl.classList.remove('d-none');
    return false;
  } finally {
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalHtml;
  }
}

// clear password field and errors when modal closes
function resetPasswordField(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  const input = modal.querySelector('.password-verify-input');
  const errorEl = modal.querySelector('.password-verify-error');
  if (input) {
    input.value = '';
    input.classList.remove('is-invalid');
  }
  if (errorEl) errorEl.classList.add('d-none');
}

// update all 4 stat cards with fresh data
function updateStatCards(data) {
  const current = data.current;

  const semEl = document.getElementById('statCurrentSem');
  const yearEl = document.getElementById('statCurrentYear');
  if (semEl && current) {
    semEl.textContent = current.semester + ' Sem';
    yearEl.textContent = 'S.Y. ' + current.school_year;
  }

  const activeEl = document.getElementById('statActiveCount');
  if (activeEl) activeEl.textContent = formatNumber(data.active_count);

  const periodEl = document.getElementById('statPeriodCount');
  const periodSubEl = document.getElementById('statPeriodSub');
  if (periodEl) {
    periodEl.textContent = formatNumber(data.period_count);
    periodSubEl.textContent = current ? 'for this period' : 'no period yet';
  }

  const missingEl = document.getElementById('statMissingCount');
  const missingSubEl = document.getElementById('statMissingSub');
  const missingCard = document.getElementById('statMissingCard');
  if (missingEl) {
    missingEl.textContent = formatNumber(data.missing_config);
    missingSubEl.textContent =
      data.missing_config > 0
        ? 'students without fee setup'
        : 'all students configured';
    missingCard.className = missingCard.className
      .replace(/stat-card-(danger|muted)/g, '')
      .trim();
    missingCard.classList.add(
      data.missing_config > 0 ? 'stat-card-danger' : 'stat-card-muted'
    );
  }
}

// rebuild the history timeline
function updateHistory(history) {
  const timeline = document.getElementById('historyTimeline');
  if (!timeline) return;

  if (!history || history.length === 0) {
    timeline.innerHTML = `
      <div class="empty-state py-5">
        <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
        <p class="empty-state-text">no period history yet</p>
      </div>`;
    return;
  }

  timeline.innerHTML = history
    .map((record) => {
      const isActive = parseInt(record.is_active) === 1;
      const advancedBy = record.advanced_by_first
        ? `<span><i class="bi bi-person-fill"></i> ${record.advanced_by_first} ${record.advanced_by_last}</span>`
        : '';

      return `
      <div class="history-item ${isActive ? 'history-item-active' : ''}">
        <div class="history-dot">
          <i class="bi ${
            isActive ? 'bi-circle-fill' : 'bi-check-circle-fill'
          }"></i>
        </div>
        <div class="history-content">
          <div class="history-header">
            <span class="history-period">
              ${record.semester} Semester &mdash; S.Y. ${record.school_year}
            </span>
            ${
              isActive
                ? `<span class="badge-active"><i class="bi bi-broadcast"></i> active</span>`
                : `<span class="badge-completed"><i class="bi bi-check2"></i> completed</span>`
            }
          </div>
          <div class="history-meta">
            <span><i class="bi bi-clock"></i> ${formatDateTime(
              record.advanced_at
            )}</span>
            ${advancedBy}
          </div>
        </div>
      </div>`;
    })
    .join('');
}

// rebuild grading periods table from fresh data
function updateGradingPeriods(gradingPeriods) {
  const tbody = document.getElementById('gradingPeriodsBody');
  if (!tbody) return;

  if (!gradingPeriods || gradingPeriods.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="4" class="text-center text-muted py-3">no grading periods configured</td>
      </tr>`;
    return;
  }

  tbody.innerHTML = gradingPeriods
    .map((period) => {
      const statusBadge =
        period.lock_status === 'locked'
          ? `<span class="grading-badge grading-badge-locked"><i class="bi bi-lock-fill"></i> locked</span>`
          : period.lock_status === 'expired'
          ? `<span class="grading-badge grading-badge-expired"><i class="bi bi-clock-history"></i> expired</span>`
          : `<span class="grading-badge grading-badge-open"><i class="bi bi-unlock-fill"></i> open</span>`;

      const deadlineDisplay = period.deadline_date
        ? `<span class="deadline-display ${
            period.lock_status === 'expired' ? 'text-danger' : ''
          }">${formatDate(period.deadline_date)}</span>`
        : `<span class="text-muted fst-italic">not set</span>`;

      const rowClass =
        period.lock_status === 'locked'
          ? 'grading-period-row row-locked'
          : period.lock_status === 'expired'
          ? 'grading-period-row row-expired'
          : 'grading-period-row';

      return `
      <tr class="${rowClass}" data-period-id="${period.period_id}">
        <td class="fw-semibold">
          <i class="bi bi-flag-fill me-2 text-primary"></i>${
            period.grading_period
          }
        </td>
        <td>${deadlineDisplay}</td>
        <td>${statusBadge}</td>
        <td class="text-center">
          <div class="form-check form-switch d-flex justify-content-center mb-0">
            <input
              class="form-check-input grading-lock-toggle"
              type="checkbox"
              role="switch"
              data-period-id="${period.period_id}"
              ${period.is_locked ? 'checked' : ''}>
          </div>
        </td>
      </tr>`;
    })
    .join('');

  const lockedCount = gradingPeriods.filter(
    (p) => parseInt(p.is_locked) === 1
  ).length;
  const statusBadge = document.getElementById('gradingStatusBadge');
  if (statusBadge)
    statusBadge.textContent = `${lockedCount} / ${gradingPeriods.length} locked`;
}

// update undo/redo button states
function updateUndoRedoState(canRedo) {
  const redoBtn = document.getElementById('redoBtn');
  if (redoBtn) redoBtn.disabled = !canRedo;
}

// swap initialize form panel to current period panel after first init
function swapToPeriodPanel(data) {
  const actionCol = document.getElementById('actionColumn');
  if (!actionCol) return;

  const current = data.current;
  const next = data.next_period;
  const missing = parseInt(data.missing_config);
  const periodCount = parseInt(data.period_count);
  const canRedo = data.can_redo || false;

  const missingWarning =
    missing > 0
      ? `
    <div class="alert alert-warning mb-3" style="font-size: 0.875rem;">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <strong>${missing}</strong> student(s) don't have a fee configuration for this school year. They will be skipped when creating payment records.
    </div>`
      : '';

  actionCol.innerHTML = `
    <div class="card action-card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bi bi-calendar2-range-fill"></i>
          Current Period
        </h5>
      </div>
      <div class="card-body">
        <div class="current-period-display mb-4">
          <div class="period-badge-large">
            <i class="bi bi-calendar2-check-fill"></i>
            <div>
              <span class="period-sem">${current.semester} Semester</span>
              <span class="period-year">S.Y. ${current.school_year}</span>
            </div>
          </div>
          <div class="period-meta mt-3">
            <div class="meta-item">
              <span class="meta-label"><i class="bi bi-clock"></i> Started</span>
              <span class="meta-value">${formatDate(current.advanced_at)}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label"><i class="bi bi-receipt"></i> Payment Records</span>
              <span class="meta-value">${formatNumber(periodCount)}</span>
            </div>
            ${
              missing > 0
                ? `
            <div class="meta-item meta-item-warning">
              <span class="meta-label"><i class="bi bi-exclamation-triangle-fill"></i> Missing Config</span>
              <span class="meta-value text-danger fw-bold">${missing} students</span>
            </div>`
                : ''
            }
          </div>
        </div>
        <div class="advance-section">
          <div class="advance-preview mb-3">
            <span class="advance-label">next period will be:</span>
            <span class="advance-target" id="advanceTargetLabel">
              <i class="bi bi-arrow-right-circle-fill"></i> ${next}
            </span>
          </div>
          ${missingWarning}
          <button type="button" class="btn btn-primary btn-action w-100 mb-2" id="advanceBtn">
            <i class="bi bi-skip-forward-fill"></i>
            Advance to ${next}
          </button>
          <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" id="undoBtn">
              <i class="bi bi-arrow-counterclockwise"></i>
              Undo Advance
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm flex-fill" id="redoBtn" ${
              canRedo ? '' : 'disabled'
            }>
              <i class="bi bi-arrow-clockwise"></i>
              Redo
            </button>
          </div>
          <p class="text-muted mt-2 mb-0" style="font-size: 0.75rem;">
            <i class="bi bi-info-circle me-1"></i>
            undo is only available within the same school year and if no grades have been submitted yet.
          </p>
        </div>
      </div>
    </div>`;

  updateAdvanceModalLabel(next);
  bindAdvanceBtn();
  bindUndoBtn();
  bindRedoBtn();
}

// update advance modal next period label
function updateAdvanceModalLabel(nextPeriod) {
  const labelEl = document.getElementById('advanceModalNextLabel');
  if (labelEl) labelEl.textContent = nextPeriod;
}

// update the action panel values after advance or undo/redo
function updatePeriodPanel(data) {
  const current = data.current;
  const next = data.next_period;
  const missing = parseInt(data.missing_config);
  const periodCount = parseInt(data.period_count);

  const semEl = document.querySelector('.period-sem');
  const yearEl = document.querySelector('.period-year');
  if (semEl) semEl.textContent = current.semester + ' Semester';
  if (yearEl) yearEl.textContent = 'S.Y. ' + current.school_year;

  const metaItems = document.querySelectorAll('.meta-item');
  if (metaItems[0])
    metaItems[0].querySelector('.meta-value').textContent = formatDate(
      current.advanced_at
    );
  if (metaItems[1])
    metaItems[1].querySelector('.meta-value').textContent =
      formatNumber(periodCount);

  const advanceTargetEl = document.getElementById('advanceTargetLabel');
  if (advanceTargetEl) {
    advanceTargetEl.innerHTML = `<i class="bi bi-arrow-right-circle-fill"></i> ${next}`;
  }

  const advanceBtn = document.getElementById('advanceBtn');
  if (advanceBtn) {
    advanceBtn.innerHTML = `<i class="bi bi-skip-forward-fill"></i> Advance to ${next}`;
  }

  updateAdvanceModalLabel(next);
  updateUndoRedoState(data.can_redo || false);

  if (data.grading_periods) {
    updateGradingPeriods(data.grading_periods);
  }
}

// bind advance button
function bindAdvanceBtn() {
  const advanceBtn = document.getElementById('advanceBtn');
  if (!advanceBtn) return;

  const advanceModal = new bootstrap.Modal(
    document.getElementById('advanceConfirmModal')
  );
  advanceBtn.addEventListener('click', function () {
    resetPasswordField('advanceConfirmModal');
    advanceModal.show();
  });
}

// bind undo button
function bindUndoBtn() {
  const undoBtn = document.getElementById('undoBtn');
  if (!undoBtn) return;

  const undoModal = new bootstrap.Modal(
    document.getElementById('undoConfirmModal')
  );
  undoBtn.addEventListener('click', function () {
    resetPasswordField('undoConfirmModal');
    undoModal.show();
  });
}

// bind redo button
function bindRedoBtn() {
  const redoBtn = document.getElementById('redoBtn');
  if (!redoBtn) return;

  const redoModal = new bootstrap.Modal(
    document.getElementById('redoConfirmModal')
  );
  redoBtn.addEventListener('click', function () {
    if (!redoBtn.disabled) {
      resetPasswordField('redoConfirmModal');
      redoModal.show();
    }
  });
}

document.addEventListener('DOMContentLoaded', function () {
  // show flash messages on load
  if (academicPeriodConfig.success)
    showToast('success', academicPeriodConfig.success);
  if (academicPeriodConfig.error)
    showToast('danger', academicPeriodConfig.error);

  // bind action buttons
  bindAdvanceBtn();
  bindUndoBtn();
  bindRedoBtn();

  // clear password fields whenever any modal is hidden
  ['advanceConfirmModal', 'undoConfirmModal', 'redoConfirmModal'].forEach(
    (id) => {
      const el = document.getElementById(id);
      if (el)
        el.addEventListener('hidden.bs.modal', () => resetPasswordField(id));
    }
  );

  // show/hide password toggle for all password fields in modals
  document.addEventListener('click', function (e) {
    if (e.target.closest('.password-toggle-btn')) {
      const btn = e.target.closest('.password-toggle-btn');
      const input = btn
        .closest('.input-group')
        .querySelector('.password-verify-input');
      const icon = btn.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
      }
    }
  });

  // enable submit button only when password field has value
  document.addEventListener('input', function (e) {
    if (e.target.classList.contains('password-verify-input')) {
      const modal = e.target.closest('.modal');
      if (!modal) return;
      const submitBtn = modal.querySelector('.password-verify-submit');
      if (submitBtn) submitBtn.disabled = e.target.value.trim() === '';
    }
  });

  // -------------------------------------------------------
  // confirm advance with password
  // -------------------------------------------------------
  const confirmAdvanceBtn = document.getElementById('confirmAdvanceBtn');
  if (confirmAdvanceBtn) {
    confirmAdvanceBtn.addEventListener('click', async function () {
      const btn = this;
      const advanceModal = bootstrap.Modal.getInstance(
        document.getElementById('advanceConfirmModal')
      );

      // verify password first
      const verified = await verifyPassword('advanceConfirmModal');
      if (!verified) return;

      btn.disabled = true;
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

      try {
        const res = await fetch('index.php?page=ajax_academic_period_advance', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();

        if (data.success) {
          advanceModal.hide();
          showToast('success', data.message);
          updateStatCards(data);
          updatePeriodPanel(data);
          updateHistory(data.history);
          if (data.grading_periods) updateGradingPeriods(data.grading_periods);
        } else {
          advanceModal.hide();
          showToast('danger', data.message || 'Failed to advance period.');
        }
      } catch (err) {
        showToast('danger', 'An unexpected error occurred. Please try again.');
      } finally {
        btn.disabled = false;
        btn.innerHTML =
          '<i class="bi bi-skip-forward-fill"></i> Confirm Advance';
      }
    });
  }

  // -------------------------------------------------------
  // confirm undo with password
  // -------------------------------------------------------
  const confirmUndoBtn = document.getElementById('confirmUndoBtn');
  if (confirmUndoBtn) {
    confirmUndoBtn.addEventListener('click', async function () {
      const btn = this;
      const undoModal = bootstrap.Modal.getInstance(
        document.getElementById('undoConfirmModal')
      );

      // verify password first
      const verified = await verifyPassword('undoConfirmModal');
      if (!verified) return;

      btn.disabled = true;
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

      try {
        const res = await fetch('index.php?page=ajax_academic_period_undo', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();

        if (data.success) {
          undoModal.hide();
          showToast('success', data.message);
          updateStatCards(data);
          updatePeriodPanel(data);
          updateHistory(data.history);
          if (data.grading_periods) updateGradingPeriods(data.grading_periods);
        } else {
          undoModal.hide();
          showToast('danger', data.message || 'Failed to undo period.');
        }
      } catch (err) {
        showToast('danger', 'An unexpected error occurred. Please try again.');
      } finally {
        btn.disabled = false;
        btn.innerHTML =
          '<i class="bi bi-arrow-counterclockwise"></i> Confirm Undo';
      }
    });
  }

  // -------------------------------------------------------
  // confirm redo with password
  // -------------------------------------------------------
  const confirmRedoBtn = document.getElementById('confirmRedoBtn');
  if (confirmRedoBtn) {
    confirmRedoBtn.addEventListener('click', async function () {
      const btn = this;
      const redoModal = bootstrap.Modal.getInstance(
        document.getElementById('redoConfirmModal')
      );

      // verify password first
      const verified = await verifyPassword('redoConfirmModal');
      if (!verified) return;

      btn.disabled = true;
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

      try {
        const res = await fetch('index.php?page=ajax_academic_period_redo', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();

        if (data.success) {
          redoModal.hide();
          showToast('success', data.message);
          updateStatCards(data);
          updatePeriodPanel(data);
          updateHistory(data.history);
          if (data.grading_periods) updateGradingPeriods(data.grading_periods);
        } else {
          redoModal.hide();
          showToast('danger', data.message || 'Failed to redo period.');
        }
      } catch (err) {
        showToast('danger', 'An unexpected error occurred. Please try again.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Confirm Redo';
      }
    });
  }

  // -------------------------------------------------------
  // initialize period flow with year validation
  // -------------------------------------------------------
  const initializeBtn = document.getElementById('initializeBtn');
  if (initializeBtn) {
    const initializeModal = new bootstrap.Modal(
      document.getElementById('initializeConfirmModal')
    );

    initializeBtn.addEventListener('click', function (e) {
      e.preventDefault();

      const schoolYear = document
        .querySelector('input[name="school_year"]')
        .value.trim();
      const semester = document.querySelector('select[name="semester"]').value;

      if (!schoolYear || !semester) {
        showToast(
          'danger',
          'Please fill in all required fields before proceeding.'
        );
        return;
      }

      const yearPattern = /^\d{4}-\d{4}$/;
      if (!yearPattern.test(schoolYear)) {
        showToast(
          'danger',
          'School year must be in YYYY-YYYY format (e.g. 2024-2025).'
        );
        return;
      }

      const parts = schoolYear.split('-');
      const startYear = parseInt(parts[0]);
      const endYear = parseInt(parts[1]);
      const currentYear = new Date().getFullYear();

      // hard block: end year must be start year + 1
      if (endYear !== startYear + 1) {
        showToast(
          'danger',
          `End year must be exactly one year after start year (e.g. ${startYear}-${
            startYear + 1
          }).`
        );
        return;
      }

      // hard block: future year
      if (startYear > currentYear) {
        showToast(
          'danger',
          `Cannot initialize a future school year. The current year is ${currentYear}.`
        );
        return;
      }

      // soft warning: past year — show warning in modal but allow proceed
      const warningEl = document.getElementById('initializePastYearWarning');
      if (startYear < currentYear) {
        if (warningEl) {
          warningEl.textContent = `Warning: ${schoolYear} is a past school year. Make sure this is intentional before proceeding.`;
          warningEl.classList.remove('d-none');
        }
      } else {
        if (warningEl) warningEl.classList.add('d-none');
      }

      initializeModal.show();
    });

    document
      .getElementById('confirmInitializeBtn')
      .addEventListener('click', async function () {
        const btn = this;
        const initializeModal = bootstrap.Modal.getInstance(
          document.getElementById('initializeConfirmModal')
        );
        const schoolYear = document
          .querySelector('input[name="school_year"]')
          .value.trim();
        const semester = document.querySelector(
          'select[name="semester"]'
        ).value;

        btn.disabled = true;
        btn.innerHTML =
          '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

        const formData = new FormData();
        formData.append('school_year', schoolYear);
        formData.append('semester', semester);

        try {
          const res = await fetch(
            'index.php?page=ajax_academic_period_initialize',
            {
              method: 'POST',
              headers: { 'X-Requested-With': 'XMLHttpRequest' },
              body: formData,
            }
          );
          const data = await res.json();

          if (data.success) {
            initializeModal.hide();
            showToast('success', data.message);
            updateStatCards(data);
            swapToPeriodPanel(data);
            updateHistory(data.history);
            if (data.grading_periods)
              updateGradingPeriods(data.grading_periods);
          } else {
            initializeModal.hide();

            if (data.errors) {
              Object.entries(data.errors).forEach(([field, msg]) => {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) {
                  input.classList.add('is-invalid');
                  let feedback = input.nextElementSibling;
                  if (
                    !feedback ||
                    !feedback.classList.contains('invalid-feedback')
                  ) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    input.after(feedback);
                  }
                  feedback.textContent = msg;
                }
              });
            } else {
              showToast(
                'danger',
                data.message || 'Failed to initialize period.'
              );
            }
          }
        } catch (err) {
          showToast(
            'danger',
            'An unexpected error occurred. Please try again.'
          );
        } finally {
          btn.disabled = false;
          btn.innerHTML =
            '<i class="bi bi-play-circle-fill"></i> Confirm Initialize';
        }
      });
  }

  // -------------------------------------------------------
  // grading lock toggle — individual switch
  // -------------------------------------------------------
  document.addEventListener('change', async function (e) {
    if (!e.target.classList.contains('grading-lock-toggle')) return;

    const toggle = e.target;
    const periodId = toggle.dataset.periodId;
    const isLocked = toggle.checked;

    toggle.disabled = true;

    const formData = new FormData();
    formData.append('period_id', periodId);
    formData.append('is_locked', isLocked ? '1' : '0');

    try {
      const res = await fetch('index.php?page=ajax_toggle_grading_lock', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData,
      });
      const data = await res.json();

      if (data.success) {
        showToast('success', data.message);
        if (data.grading_periods) updateGradingPeriods(data.grading_periods);
      } else {
        toggle.checked = !isLocked;
        showToast('danger', data.message || 'Failed to update lock status.');
      }
    } catch (err) {
      toggle.checked = !isLocked;
      showToast('danger', 'An unexpected error occurred. Please try again.');
    } finally {
      toggle.disabled = false;
    }
  });

  // -------------------------------------------------------
  // lock all grading periods
  // -------------------------------------------------------
  const lockAllBtn = document.getElementById('lockAllBtn');
  if (lockAllBtn) {
    lockAllBtn.addEventListener('click', async function () {
      const btn = this;

      btn.disabled = true;
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Locking...';

      try {
        const res = await fetch('index.php?page=ajax_lock_all_grading', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();

        if (data.success) {
          showToast('success', data.message);
          if (data.grading_periods) updateGradingPeriods(data.grading_periods);
        } else {
          showToast(
            'danger',
            data.message || 'Failed to lock all grading periods.'
          );
        }
      } catch (err) {
        showToast('danger', 'An unexpected error occurred. Please try again.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock-fill"></i> Lock All';
      }
    });
  }

  // -------------------------------------------------------
  // save grading period deadlines
  // -------------------------------------------------------
  const saveDeadlinesBtn = document.getElementById('saveDeadlinesBtn');
  if (saveDeadlinesBtn) {
    saveDeadlinesBtn.addEventListener('click', async function () {
      const btn = this;

      const formData = new FormData();
      ['prelim', 'midterm', 'prefinal', 'final'].forEach((key) => {
        const input = document.getElementById(`deadline_${key}`);
        if (input && input.value)
          formData.append(`deadline_${key}`, input.value);
      });

      if ([...formData.keys()].length === 0) {
        showToast('danger', 'Please set at least one deadline before saving.');
        return;
      }

      btn.disabled = true;
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

      try {
        const res = await fetch('index.php?page=ajax_save_grading_periods', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: formData,
        });
        const data = await res.json();

        if (data.success) {
          showToast('success', data.message);
          if (data.grading_periods) updateGradingPeriods(data.grading_periods);
        } else {
          showToast('danger', data.message || 'Failed to save deadlines.');
        }
      } catch (err) {
        showToast('danger', 'An unexpected error occurred. Please try again.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-floppy-fill"></i> Save Deadlines';
      }
    });
  }

  // -------------------------------------------------------
  // clear inline validation on input
  // -------------------------------------------------------
  document.querySelectorAll('input, select').forEach((el) => {
    el.addEventListener('input', function () {
      this.classList.remove('is-invalid');
    });
  });

  // auto-format school year input
  const schoolYearInput = document.querySelector('input[name="school_year"]');
  if (schoolYearInput) {
    schoolYearInput.addEventListener('input', function () {
      let val = this.value.replace(/[^\d-]/g, '');
      if (val.length === 4 && !val.includes('-')) val = val + '-';
      this.value = val;
    });
  }

  // -------------------------------------------------------
  // graduation section
  // -------------------------------------------------------
  const selectAllGraduates = document.getElementById('selectAllGraduates');
  if (selectAllGraduates) {
    selectAllGraduates.addEventListener('change', function () {
      document.querySelectorAll('.graduate-checkbox').forEach((cb) => {
        cb.checked = this.checked;
        cb.closest('tr').classList.toggle('selected-row', this.checked);
      });
      updateGraduateCount();
    });
  }

  document.addEventListener('change', function (e) {
    if (e.target.classList.contains('graduate-checkbox')) {
      e.target.closest('tr').classList.toggle('selected-row', e.target.checked);
      updateGraduateCount();
    }
  });

  function updateGraduateCount() {
    const checked = document.querySelectorAll('.graduate-checkbox:checked');
    const countEl = document.getElementById('selectedGraduateCount');
    const btn = document.getElementById('graduateBtn');
    if (countEl) countEl.textContent = `${checked.length} selected`;
    if (btn) btn.disabled = checked.length === 0;
  }

  const graduateBtn = document.getElementById('graduateBtn');
  if (graduateBtn) {
    const graduateModal = new bootstrap.Modal(
      document.getElementById('graduateConfirmModal')
    );

    graduateBtn.addEventListener('click', function () {
      const checked = document.querySelectorAll('.graduate-checkbox:checked');
      const countEl = document.getElementById('graduateModalCount');
      if (countEl) countEl.textContent = `${checked.length} student(s)`;
      graduateModal.show();
    });
  }

  const confirmGraduateBtn = document.getElementById('confirmGraduateBtn');
  if (confirmGraduateBtn) {
    confirmGraduateBtn.addEventListener('click', async function () {
      const btn = this;
      const graduateModal = bootstrap.Modal.getInstance(
        document.getElementById('graduateConfirmModal')
      );
      const checked = document.querySelectorAll('.graduate-checkbox:checked');
      const studentIds = Array.from(checked).map((cb) => cb.value);

      if (studentIds.length === 0) {
        graduateModal.hide();
        showToast('danger', 'Please select at least one student.');
        return;
      }

      btn.disabled = true;
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

      const formData = new FormData();
      studentIds.forEach((id) => formData.append('student_ids[]', id));

      try {
        const res = await fetch('index.php?page=ajax_graduate_students', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: formData,
        });
        const data = await res.json();

        if (data.success) {
          graduateModal.hide();
          showToast('success', data.message);

          const activeEl = document.getElementById('statActiveCount');
          if (activeEl) activeEl.textContent = formatNumber(data.active_count);

          studentIds.forEach((id) => {
            const cb = document.querySelector(
              `.graduate-checkbox[value="${id}"]`
            );
            if (cb) cb.closest('tr').remove();
          });

          const remaining =
            document.querySelectorAll('.graduate-checkbox').length;
          const badge = document.querySelector(
            '.graduation-card .card-header .badge'
          );
          if (badge) badge.textContent = `${remaining} eligible`;

          if (remaining === 0) {
            const tableWrapper = document.querySelector(
              '.graduation-card .table-responsive'
            );
            const footer = document.querySelector('.graduation-card .d-flex');
            if (tableWrapper)
              tableWrapper.innerHTML = `
              <div class="empty-state py-3">
                <div class="empty-state-icon"><i class="bi bi-mortarboard"></i></div>
                <p class="empty-state-text">no students eligible for graduation</p>
              </div>`;
            if (footer) footer.remove();
          }

          updateGraduateCount();
        } else {
          showToast('danger', data.message || 'Failed to graduate students.');
        }
      } catch (err) {
        showToast('danger', 'An unexpected error occurred. Please try again.');
      } finally {
        btn.disabled = false;
        btn.innerHTML =
          '<i class="bi bi-mortarboard-fill"></i> Confirm Graduate';
      }
    });
  }
});
