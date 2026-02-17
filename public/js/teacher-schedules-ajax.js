// teacher schedules page ajax operations

// state
let currentTeacherId = null;
let currentSchoolYear = null;
let currentSemester = null;
let pendingDeleteId = null;
let pendingDeleteRow = null;
let pendingDeleteTeacherId = null;
let pendingDeleteSubjectId = null;
let pendingDeleteSectionId = null;

// modal instances
let addModal = null;
let editModal = null;
let deleteModal = null;

// initialize on load
document.addEventListener('DOMContentLoaded', function () {
  addModal = new bootstrap.Modal(document.getElementById('addScheduleModal'));
  editModal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
  deleteModal = new bootstrap.Modal(
    document.getElementById('deleteScheduleModal')
  );

  // load btn
  document
    .getElementById('loadAssignmentsBtn')
    ?.addEventListener('click', loadAssignments);

  // modal confirm buttons
  document
    .getElementById('confirmAddScheduleBtn')
    ?.addEventListener('click', submitAddSchedule);
  document
    .getElementById('confirmEditScheduleBtn')
    ?.addEventListener('click', submitEditSchedule);
  document
    .getElementById('confirmDeleteScheduleBtn')
    ?.addEventListener('click', confirmDelete);

  // clear modal errors on close
  document
    .getElementById('addScheduleModal')
    ?.addEventListener('hidden.bs.modal', clearAddErrors);
  document
    .getElementById('editScheduleModal')
    ?.addEventListener('hidden.bs.modal', clearEditErrors);

  // time validation in both modals
  ['add', 'edit'].forEach((prefix) => {
    document
      .getElementById(`${prefix}StartTime`)
      ?.addEventListener('change', () => validateModalTimes(prefix));
    document
      .getElementById(`${prefix}EndTime`)
      ?.addEventListener('change', () => validateModalTimes(prefix));
  });

  // event delegation for add/edit/delete buttons inside assignments panel
  document
    .getElementById('assignmentsPanel')
    ?.addEventListener('click', handlePanelClick);
});

// load assignments for selected teacher/filters
function loadAssignments() {
  const teacherId = document.getElementById('filterTeacher').value;
  const schoolYear = document.getElementById('filterSchoolYear').value;
  const semester = document.getElementById('filterSemester').value;

  if (!teacherId) {
    showAlert('danger', 'please select a teacher first.');
    return;
  }

  currentTeacherId = teacherId;
  currentSchoolYear = schoolYear;
  currentSemester = semester;

  const panel = document.getElementById('assignmentsPanel');
  panel.innerHTML = buildLoadingHtml();

  fetch(
    `index.php?page=ajax_get_teacher_assignments&teacher_id=${teacherId}&school_year=${encodeURIComponent(
      schoolYear
    )}&semester=${encodeURIComponent(semester)}`,
    {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    }
  )
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        panel.innerHTML = buildAssignmentsPanelHtml(
          data.teacher,
          data.assignments,
          schoolYear,
          semester
        );
      } else {
        panel.innerHTML = buildErrorHtml(
          data.message || 'failed to load assignments.'
        );
      }
    })
    .catch(() => {
      panel.innerHTML = buildErrorHtml('an error occurred. please try again.');
    });
}

// handle clicks inside the assignments panel (event delegation)
function handlePanelClick(event) {
  const addBtn = event.target.closest('.btn-add-schedule');
  const editBtn = event.target.closest('.btn-edit-schedule');
  const deleteBtn = event.target.closest('.btn-delete-schedule');

  if (addBtn) {
    openAddModal(addBtn);
  } else if (editBtn) {
    openEditModal(editBtn);
  } else if (deleteBtn) {
    openDeleteModal(deleteBtn);
  }
}

// open add schedule modal and populate hidden fields
function openAddModal(btn) {
  const teacherId = btn.dataset.teacherId;
  const subjectId = btn.dataset.subjectId;
  const sectionId = btn.dataset.sectionId;
  const schoolYear = btn.dataset.schoolYear;
  const semester = btn.dataset.semester;
  const subjectName = btn.dataset.subjectName;
  const sectionName = btn.dataset.sectionName;

  document.getElementById('addTeacherId').value = teacherId;
  document.getElementById('addSubjectId').value = subjectId;
  document.getElementById('addSectionId').value = sectionId;
  document.getElementById('addSchoolYear').value = schoolYear;
  document.getElementById('addSemester').value = semester;

  document.getElementById(
    'addAssignmentContext'
  ).innerHTML = `<i class="bi bi-info-circle-fill"></i> Adding schedule for <strong>${subjectName}</strong> &mdash; Section <strong>${sectionName}</strong>`;

  // reset form fields
  document.getElementById('addDayOfWeek').value = '';
  document.getElementById('addStartTime').value = '';
  document.getElementById('addEndTime').value = '';
  document.getElementById('addRoom').value = '';

  clearAddErrors();
  addModal.show();
}

// open edit schedule modal
function openEditModal(btn) {
  const scheduleId = btn.dataset.scheduleId;
  const teacherId = btn.dataset.teacherId;
  const subjectId = btn.dataset.subjectId;
  const sectionId = btn.dataset.sectionId;
  const schoolYear = btn.dataset.schoolYear;
  const semester = btn.dataset.semester;
  const day = btn.dataset.day;
  const startTime = btn.dataset.startTime;
  const endTime = btn.dataset.endTime;
  const room = btn.dataset.room;
  const status = btn.dataset.status;
  const subjectName = btn.dataset.subjectName;
  const sectionName = btn.dataset.sectionName;

  document.getElementById('editScheduleId').value = scheduleId;
  document.getElementById('editTeacherId').value = teacherId;
  document.getElementById('editSubjectId').value = subjectId;
  document.getElementById('editSectionId').value = sectionId;
  document.getElementById('editSchoolYear').value = schoolYear;
  document.getElementById('editSemester').value = semester;
  document.getElementById('editDayOfWeek').value = day;
  document.getElementById('editStartTime').value = startTime;
  document.getElementById('editEndTime').value = endTime;
  document.getElementById('editRoom').value = room || '';
  document.getElementById('editStatus').value = status;

  document.getElementById(
    'editAssignmentContext'
  ).innerHTML = `<i class="bi bi-info-circle-fill"></i> Editing schedule for <strong>${subjectName}</strong> &mdash; Section <strong>${sectionName}</strong>`;

  clearEditErrors();
  editModal.show();
}

// open delete confirmation modal
function openDeleteModal(btn) {
  pendingDeleteId = btn.dataset.scheduleId;
  pendingDeleteTeacherId = btn.dataset.teacherId;
  pendingDeleteSubjectId = btn.dataset.subjectId;
  pendingDeleteSectionId = btn.dataset.sectionId;
  pendingDeleteRow = btn.closest('.schedule-entry');

  deleteModal.show();
}

// submit add schedule
function submitAddSchedule() {
  clearAddErrors();

  const startTime = document.getElementById('addStartTime').value;
  const endTime = document.getElementById('addEndTime').value;

  if (!validateModalTimes('add')) return;

  const formData = new FormData(document.getElementById('addScheduleForm'));

  setModalLoading('confirmAddScheduleBtn', true);

  fetch('index.php?page=create_schedule', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData,
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        addModal.hide();
        showAlert('success', data.message);
        refreshAssignmentRow(data.row_key, data.schedules);
      } else {
        showModalErrors('addScheduleError', data.errors || data.message);
      }
    })
    .catch(() =>
      showModalErrors(
        'addScheduleError',
        'an error occurred. please try again.'
      )
    )
    .finally(() => setModalLoading('confirmAddScheduleBtn', false));
}

// submit edit schedule
function submitEditSchedule() {
  clearEditErrors();

  if (!validateModalTimes('edit')) return;

  const formData = new FormData(document.getElementById('editScheduleForm'));

  setModalLoading('confirmEditScheduleBtn', true);

  fetch('index.php?page=update_schedule', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData,
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        editModal.hide();
        showAlert('success', data.message);
        refreshAssignmentRow(data.row_key, data.schedules);
      } else {
        showModalErrors('editScheduleError', data.errors || data.message);
      }
    })
    .catch(() =>
      showModalErrors(
        'editScheduleError',
        'an error occurred. please try again.'
      )
    )
    .finally(() => setModalLoading('confirmEditScheduleBtn', false));
}

// confirm delete
function confirmDelete() {
  if (!pendingDeleteId) return;

  const formData = new FormData();
  formData.append('csrf_token', csrfToken);
  formData.append('schedule_id', pendingDeleteId);
  formData.append('teacher_id', pendingDeleteTeacherId);
  formData.append('subject_id', pendingDeleteSubjectId);
  formData.append('section_id', pendingDeleteSectionId);
  formData.append('school_year', currentSchoolYear);
  formData.append('semester', currentSemester);

  setModalLoading('confirmDeleteScheduleBtn', true);

  fetch('index.php?page=delete_schedule', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData,
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        deleteModal.hide();
        showAlert('success', data.message);
        refreshAssignmentRow(data.row_key, data.schedules);
      } else {
        deleteModal.hide();
        showAlert('danger', data.message || 'failed to delete schedule.');
      }
    })
    .catch(() => {
      deleteModal.hide();
      showAlert('danger', 'an error occurred. please try again.');
    })
    .finally(() => {
      setModalLoading('confirmDeleteScheduleBtn', false);
      pendingDeleteId =
        pendingDeleteRow =
        pendingDeleteTeacherId =
        pendingDeleteSubjectId =
        pendingDeleteSectionId =
          null;
    });
}

// refresh only the schedule entries for one assignment card
function refreshAssignmentRow(rowKey, schedules) {
  const entriesContainer = document.querySelector(
    `[data-row-key="${rowKey}"] .schedule-entries`
  );
  const statusBadge = document.querySelector(
    `[data-row-key="${rowKey}"] .schedule-status-badge`
  );

  if (!entriesContainer) return;

  if (schedules && schedules.length > 0) {
    // get assignment data from the card's add button
    const addBtn = document.querySelector(
      `[data-row-key="${rowKey}"] .btn-add-schedule`
    );
    const teacherId = addBtn?.dataset.teacherId;
    const subjectId = addBtn?.dataset.subjectId;
    const sectionId = addBtn?.dataset.sectionId;
    const schoolYear = addBtn?.dataset.schoolYear;
    const semester = addBtn?.dataset.semester;
    const subjectName = addBtn?.dataset.subjectName;
    const sectionName = addBtn?.dataset.sectionName;

    entriesContainer.innerHTML = schedules
      .map((s) =>
        buildScheduleEntryHtml(
          s,
          teacherId,
          subjectId,
          sectionId,
          schoolYear,
          semester,
          subjectName,
          sectionName
        )
      )
      .join('');

    if (statusBadge) {
      statusBadge.className = 'schedule-status-badge has-schedules';
      statusBadge.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${
        schedules.length
      } schedule${schedules.length > 1 ? 's' : ''}`;
    }
  } else {
    entriesContainer.innerHTML = buildEmptyEntriesHtml();

    if (statusBadge) {
      statusBadge.className = 'schedule-status-badge no-schedules';
      statusBadge.innerHTML = `<i class="bi bi-clock"></i> Not scheduled`;
    }
  }
}

// TIME VALIDATION
function validateModalTimes(prefix) {
  const startInput = document.getElementById(`${prefix}StartTime`);
  const endInput = document.getElementById(`${prefix}EndTime`);

  if (!startInput || !endInput || !startInput.value || !endInput.value)
    return true;

  if (startInput.value >= endInput.value) {
    endInput.classList.add('is-invalid');

    let errDiv = endInput.parentElement.querySelector('.time-validation-error');
    if (!errDiv) {
      errDiv = document.createElement('div');
      errDiv.className = 'invalid-feedback d-block time-validation-error';
      endInput.parentElement.appendChild(errDiv);
    }
    errDiv.textContent = 'end time must be after start time.';
    return false;
  }

  endInput.classList.remove('is-invalid');
  endInput.parentElement.querySelector('.time-validation-error')?.remove();
  return true;
}

// MODAL HELPERS
function showModalErrors(containerId, errors) {
  const container = document.getElementById(containerId);
  if (!container) return;

  let message = '';
  if (typeof errors === 'string') {
    message = errors;
  } else if (typeof errors === 'object') {
    message = Object.values(errors).join('<br>');
  }

  container.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i>${message}`;
  container.classList.remove('d-none');
}

function clearAddErrors() {
  const el = document.getElementById('addScheduleError');
  if (el) {
    el.classList.add('d-none');
    el.innerHTML = '';
  }
  document.getElementById('addEndTime')?.classList.remove('is-invalid');
  document.querySelector('#addScheduleModal .time-validation-error')?.remove();
}

function clearEditErrors() {
  const el = document.getElementById('editScheduleError');
  if (el) {
    el.classList.add('d-none');
    el.innerHTML = '';
  }
  document.getElementById('editEndTime')?.classList.remove('is-invalid');
  document.querySelector('#editScheduleModal .time-validation-error')?.remove();
}

function setModalLoading(btnId, loading) {
  const btn = document.getElementById(btnId);
  if (!btn) return;
  btn.disabled = loading;
  if (loading) {
    btn.dataset.originalHtml = btn.innerHTML;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Please wait...`;
  } else {
    btn.innerHTML = btn.dataset.originalHtml || btn.innerHTML;
  }
}

// HTML BUILDERS
function buildLoadingHtml() {
  return `
        <div class="panel-loading">
            <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
            <p class="mb-0">Loading assignments...</p>
        </div>`;
}

function buildErrorHtml(message) {
  return `
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <p class="empty-state-title">Something went wrong</p>
            <p class="empty-state-text">${message}</p>
        </div>`;
}

function buildAssignmentsPanelHtml(teacher, assignments, schoolYear, semester) {
  if (!assignments || assignments.length === 0) {
    return `
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-calendar-x"></i></div>
                <p class="empty-state-title">No assignments found</p>
                <p class="empty-state-text">${teacher.full_name} has no active assignments for ${schoolYear} &mdash; ${semester} Semester.</p>
            </div>`;
  }

  const scheduledCount = assignments.filter(
    (a) => a.schedules && a.schedules.length > 0
  ).length;
  const unscheduledCount = assignments.length - scheduledCount;

  const banner = `
        <div class="teacher-banner">
            <div class="teacher-banner-avatar"><i class="bi bi-person-fill"></i></div>
            <div class="teacher-banner-info">
                <p class="teacher-banner-name">${teacher.full_name}</p>
                <p class="teacher-banner-meta">${schoolYear} &bull; ${semester} Semester &bull; ${scheduledCount} scheduled &bull; ${unscheduledCount} pending</p>
            </div>
            <div class="teacher-banner-count">
                <div class="count-number">${assignments.length}</div>
                <div class="count-label">Assignments</div>
            </div>
        </div>`;

  const cards = assignments
    .map((a) => buildAssignmentCardHtml(a, schoolYear, semester))
    .join('');

  return banner + cards;
}

function buildAssignmentCardHtml(assignment, schoolYear, semester) {
  const rowKey = `${assignment.teacher_id}_${assignment.subject_id}_${assignment.section_id}`;
  const hasSchedules = assignment.schedules && assignment.schedules.length > 0;

  const statusBadge = hasSchedules
    ? `<span class="schedule-status-badge has-schedules"><i class="bi bi-check-circle-fill"></i> ${
        assignment.schedules.length
      } schedule${assignment.schedules.length > 1 ? 's' : ''}</span>`
    : `<span class="schedule-status-badge no-schedules"><i class="bi bi-clock"></i> Not scheduled</span>`;

  const entriesHtml = hasSchedules
    ? assignment.schedules
        .map((s) =>
          buildScheduleEntryHtml(
            s,
            assignment.teacher_id,
            assignment.subject_id,
            assignment.section_id,
            schoolYear,
            semester,
            assignment.subject_name,
            assignment.section_name
          )
        )
        .join('')
    : buildEmptyEntriesHtml();

  return `
        <div class="assignment-card" data-row-key="${rowKey}">
            <div class="assignment-card-header">
                <div class="assignment-info">
                    <div class="subject-icon"><i class="bi bi-book-fill"></i></div>
                    <div class="assignment-details">
                        <p class="assignment-subject-code">${assignment.subject_code}</p>
                        <p class="assignment-subject-name">${assignment.subject_name}</p>
                        <span class="assignment-section-badge">
                            <i class="bi bi-people-fill"></i>
                            ${assignment.section_name}
                        </span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    ${statusBadge}
                    <button class="btn-add-schedule"
                        data-teacher-id="${assignment.teacher_id}"
                        data-subject-id="${assignment.subject_id}"
                        data-section-id="${assignment.section_id}"
                        data-school-year="${schoolYear}"
                        data-semester="${semester}"
                        data-subject-name="${assignment.subject_name}"
                        data-section-name="${assignment.section_name}">
                        <i class="bi bi-plus-circle-fill"></i>
                        Add Schedule
                    </button>
                </div>
            </div>
            <div class="schedule-entries">
                ${entriesHtml}
            </div>
        </div>`;
}

function buildScheduleEntryHtml(
  schedule,
  teacherId,
  subjectId,
  sectionId,
  schoolYear,
  semester,
  subjectName,
  sectionName
) {
  const dayClass = `day-${schedule.day_of_week.toLowerCase()}`;
  const startFormatted = formatTime(schedule.start_time);
  const endFormatted = formatTime(schedule.end_time);

  const roomHtml = schedule.room
    ? `<span class="entry-room"><i class="bi bi-door-closed"></i>${schedule.room}</span>`
    : `<span class="entry-room-none">No room</span>`;

  const statusHtml = `<span class="entry-status ${schedule.status}">${
    schedule.status === 'active' ? 'Active' : 'Inactive'
  }</span>`;

  return `
        <div class="schedule-entry">
            <span class="day-badge ${dayClass}">${schedule.day_of_week}</span>
            <span class="entry-time"><i class="bi bi-clock"></i>${startFormatted} &ndash; ${endFormatted}</span>
            ${roomHtml}
            ${statusHtml}
            <div class="entry-actions">
                <button class="btn btn-sm btn-outline-primary btn-edit-schedule"
                    data-schedule-id="${schedule.schedule_id}"
                    data-teacher-id="${teacherId}"
                    data-subject-id="${subjectId}"
                    data-section-id="${sectionId}"
                    data-school-year="${schoolYear}"
                    data-semester="${semester}"
                    data-day="${schedule.day_of_week}"
                    data-start-time="${schedule.start_time}"
                    data-end-time="${schedule.end_time}"
                    data-room="${schedule.room || ''}"
                    data-status="${schedule.status}"
                    data-subject-name="${subjectName}"
                    data-section-name="${sectionName}"
                    title="Edit schedule">
                    <i class="bi bi-pencil-fill"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger btn-delete-schedule"
                    data-schedule-id="${schedule.schedule_id}"
                    data-teacher-id="${teacherId}"
                    data-subject-id="${subjectId}"
                    data-section-id="${sectionId}"
                    title="Delete schedule">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>
        </div>`;
}

function buildEmptyEntriesHtml() {
  return `
        <div class="schedule-entries-empty">
            <i class="bi bi-calendar-plus"></i>
            No schedules yet. Click "Add Schedule" to get started.
        </div>`;
}

// UTILITY
function formatTime(timeStr) {
  if (!timeStr) return '';
  const [hours, minutes] = timeStr.split(':');
  const h = parseInt(hours);
  const period = h >= 12 ? 'PM' : 'AM';
  const displayH = h % 12 === 0 ? 12 : h % 12;
  return `${displayH}:${minutes} ${period}`;
}

function showAlert(type, message) {
  const container = document.getElementById('toastContainer');
  const toastId = 'toast-' + Date.now();
  const icon =
    type === 'success'
      ? 'bi-check-circle-fill'
      : 'bi-exclamation-triangle-fill';
  const title = type === 'success' ? 'Success' : 'Error';

  container.insertAdjacentHTML(
    'beforeend',
    `
        <div id="${toastId}" class="toast toast-${type}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi ${icon} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>`
  );

  const toastEl = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 5000 });
  toast.show();
  toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}
