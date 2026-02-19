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
                            ${record.semester} Semester &mdash; S.Y. ${
        record.school_year
      }
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

// swap initialize form panel to current period panel after first init
function swapToPeriodPanel(data) {
  const actionCol = document.getElementById('actionColumn');
  if (!actionCol) return;

  const current = data.current;
  const next = data.next_period;
  const missing = parseInt(data.missing_config);
  const periodCount = parseInt(data.period_count);

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
                            <span class="period-sem">${
                              current.semester
                            } Semester</span>
                            <span class="period-year">S.Y. ${
                              current.school_year
                            }</span>
                        </div>
                    </div>
                    <div class="period-meta mt-3">
                        <div class="meta-item">
                            <span class="meta-label"><i class="bi bi-clock"></i> Started</span>
                            <span class="meta-value">${formatDate(
                              current.advanced_at
                            )}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label"><i class="bi bi-receipt"></i> Payment Records</span>
                            <span class="meta-value">${formatNumber(
                              periodCount
                            )}</span>
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
                    <form id="advanceForm">
                        <button type="button" class="btn btn-primary btn-action w-100" id="advanceBtn">
                            <i class="bi bi-skip-forward-fill"></i>
                            Advance to ${next}
                        </button>
                    </form>
                </div>
            </div>
        </div>`;

  updateAdvanceModalLabel(next);
  bindAdvanceBtn();
}

// update advance modal next period label
function updateAdvanceModalLabel(nextPeriod) {
  const labelEl = document.getElementById('advanceModalNextLabel');
  if (labelEl) labelEl.textContent = nextPeriod;
}

// update the action panel values after advance
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
}

// bind advance button — separated so it can be re-bound after dom swap
function bindAdvanceBtn() {
  const advanceBtn = document.getElementById('advanceBtn');
  if (!advanceBtn) return;

  const advanceModal = new bootstrap.Modal(
    document.getElementById('advanceConfirmModal')
  );
  advanceBtn.addEventListener('click', function () {
    advanceModal.show();
  });
}

document.addEventListener('DOMContentLoaded', function () {
  // show flash messages passed from php on initial page load
  if (academicPeriodConfig.success)
    showToast('success', academicPeriodConfig.success);
  if (academicPeriodConfig.error)
    showToast('danger', academicPeriodConfig.error);

  // bind advance button
  bindAdvanceBtn();

  // confirm advance — ajax submit
  const confirmAdvanceBtn = document.getElementById('confirmAdvanceBtn');
  if (confirmAdvanceBtn) {
    confirmAdvanceBtn.addEventListener('click', async function () {
      const btn = this;
      const advanceModal = bootstrap.Modal.getInstance(
        document.getElementById('advanceConfirmModal')
      );

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
        } else {
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

  // initialize period flow
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

      initializeModal.show();
    });

    // confirm initialize — ajax submit
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
          } else {
            initializeModal.hide();

            // show server-side field errors inline if returned
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

  // clear inline validation on input
  document.querySelectorAll('input, select').forEach((el) => {
    el.addEventListener('input', function () {
      this.classList.remove('is-invalid');
    });
  });

  // auto-format school year input — inserts dash after 4 digits
  const schoolYearInput = document.querySelector('input[name="school_year"]');
  if (schoolYearInput) {
    schoolYearInput.addEventListener('input', function () {
      let val = this.value.replace(/[^\d-]/g, '');
      if (val.length === 4 && !val.includes('-')) val = val + '-';
      this.value = val;
    });
  }

  // select all graduates checkbox
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

  // individual checkbox change
  document.addEventListener('change', function (e) {
    if (e.target.classList.contains('graduate-checkbox')) {
      e.target.closest('tr').classList.toggle('selected-row', e.target.checked);
      updateGraduateCount();
    }
  });

  // update selected count and enable/disable graduate button
  function updateGraduateCount() {
    const checked = document.querySelectorAll('.graduate-checkbox:checked');
    const countEl = document.getElementById('selectedGraduateCount');
    const btn = document.getElementById('graduateBtn');

    if (countEl) countEl.textContent = `${checked.length} selected`;
    if (btn) btn.disabled = checked.length === 0;
  }

  // graduate button — show confirmation modal
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

  // confirm graduate — ajax submit
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

          // update active students stat card
          const activeEl = document.getElementById('statActiveCount');
          if (activeEl) activeEl.textContent = formatNumber(data.active_count);

          // remove graduated rows from table
          studentIds.forEach((id) => {
            const cb = document.querySelector(
              `.graduate-checkbox[value="${id}"]`
            );
            if (cb) cb.closest('tr').remove();
          });

          // update eligible badge count
          const remaining =
            document.querySelectorAll('.graduate-checkbox').length;
          const badge = document.querySelector(
            '.graduation-card .card-header .badge'
          );
          if (badge) badge.textContent = `${remaining} eligible`;

          // if no students left show empty state
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
