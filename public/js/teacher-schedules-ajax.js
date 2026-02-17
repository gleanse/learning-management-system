// teacher schedules page ajax operations

// state
let currentTeacherId = null;
let currentSchoolYear = null;
let currentSemester = null;
let currentTeacherName = null;
let pickerCurrentPage = 1;
let searchDebounce = null;

let pendingDeleteId = null;
let pendingDeleteTeacherId = null;
let pendingDeleteSubjectId = null;
let pendingDeleteSectionId = null;

let addModal = null;
let editModal = null;
let deleteModal = null;

document.addEventListener('DOMContentLoaded', function () {
  addModal = new bootstrap.Modal(document.getElementById('addScheduleModal'));
  editModal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
  deleteModal = new bootstrap.Modal(
    document.getElementById('deleteScheduleModal')
  );

  // load first page of teachers immediately
  loadTeachers(1);

  // debounced search
  document
    .getElementById('teacherSearchInput')
    ?.addEventListener('input', function () {
      clearTimeout(searchDebounce);
      searchDebounce = setTimeout(() => {
        pickerCurrentPage = 1;
        loadTeachers(1);
      }, 300);
    });

  // filter changes auto-reload schedules
  document
    .getElementById('filterSchoolYear')
    ?.addEventListener('change', () => {
      if (currentTeacherId) loadAssignments();
    });
  document.getElementById('filterSemester')?.addEventListener('change', () => {
    if (currentTeacherId) loadAssignments();
  });

  // modal buttons
  document
    .getElementById('confirmAddScheduleBtn')
    ?.addEventListener('click', submitAddSchedule);
  document
    .getElementById('confirmEditScheduleBtn')
    ?.addEventListener('click', submitEditSchedule);
  document
    .getElementById('confirmDeleteScheduleBtn')
    ?.addEventListener('click', confirmDelete);

  document
    .getElementById('addScheduleModal')
    ?.addEventListener('hidden.bs.modal', clearAddErrors);
  document
    .getElementById('editScheduleModal')
    ?.addEventListener('hidden.bs.modal', clearEditErrors);

  ['add', 'edit'].forEach((p) => {
    document
      .getElementById(`${p}StartTime`)
      ?.addEventListener('change', () => validateModalTimes(p));
    document
      .getElementById(`${p}EndTime`)
      ?.addEventListener('change', () => validateModalTimes(p));
  });

  document
    .getElementById('assignmentsPanel')
    ?.addEventListener('click', handlePanelClick);
});

// TEACHER PICKER — server-side paginated
function loadTeachers(page) {
  const search =
    document.getElementById('teacherSearchInput')?.value.trim() || '';
  const list = document.getElementById('teacherList');

  list.innerHTML = `<div class="picker-loading"><span class="spinner-border spinner-border-sm"></span> Loading...</div>`;

  fetch(
    `index.php?page=ajax_get_teachers&page_num=${page}&search=${encodeURIComponent(
      search
    )}`,
    {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    }
  )
    .then((r) => r.json())
    .then((data) => {
      if (!data.success) {
        list.innerHTML = `<div class="picker-empty">failed to load teachers.</div>`;
        return;
      }

      pickerCurrentPage = data.page;

      // update count badge
      const badge = document.getElementById('pickerCount');
      if (badge) badge.textContent = data.total;

      if (data.teachers.length === 0) {
        list.innerHTML = `<div class="picker-empty"><i class="bi bi-search"></i> No teachers found.</div>`;
        document.getElementById('pickerPagination').innerHTML = '';
        return;
      }

      list.innerHTML = data.teachers
        .map((t) => {
          const initials = t.full_name
            .split(' ')
            .map((w) => w[0])
            .slice(0, 2)
            .join('')
            .toUpperCase();
          const isActive = t.id == currentTeacherId;
          return `
          <button class="picker-item ${isActive ? 'active' : ''}" data-id="${
            t.id
          }" data-name="${t.full_name}">
            <span class="picker-item-avatar">${initials}</span>
            <span class="picker-item-name">${t.full_name}</span>
            <i class="bi bi-chevron-right picker-item-arrow"></i>
          </button>`;
        })
        .join('');

      // attach click handlers
      list.querySelectorAll('.picker-item').forEach((btn) => {
        btn.addEventListener('click', () =>
          selectTeacher(btn.dataset.id, btn.dataset.name)
        );
      });

      renderPickerPagination(data.total_pages, data.page);
    })
    .catch(() => {
      list.innerHTML = `<div class="picker-empty">an error occurred.</div>`;
    });
}

function renderPickerPagination(totalPages, current) {
  const container = document.getElementById('pickerPagination');
  if (totalPages <= 1) {
    container.innerHTML = '';
    return;
  }

  let html = `<button class="pager-btn" ${
    current === 1 ? 'disabled' : ''
  } data-p="${current - 1}"><i class="bi bi-chevron-left"></i></button>`;
  html += `<span class="pager-info">Page ${current} of ${totalPages}</span>`;
  html += `<button class="pager-btn" ${
    current === totalPages ? 'disabled' : ''
  } data-p="${current + 1}"><i class="bi bi-chevron-right"></i></button>`;

  container.innerHTML = html;
  container.querySelectorAll('.pager-btn:not([disabled])').forEach((btn) => {
    btn.addEventListener('click', () => loadTeachers(parseInt(btn.dataset.p)));
  });
}

function selectTeacher(id, name) {
  currentTeacherId = id;
  currentTeacherName = name;

  document.getElementById('filterTeacher').value = id;

  // update avatar + name in filter bar
  const initials = name
    .split(' ')
    .map((w) => w[0])
    .slice(0, 2)
    .join('')
    .toUpperCase();
  const avatarEl = document.getElementById('filterTeacherAvatar');
  if (avatarEl) avatarEl.textContent = initials;

  const nameEl = document.getElementById('selectedTeacherName');
  if (nameEl) nameEl.textContent = name;

  // show schedule content, hide prompt
  document.getElementById('pickPrompt')?.classList.add('d-none');
  document.getElementById('scheduleContent')?.classList.remove('d-none');

  // highlight active picker item without re-fetching
  document
    .querySelectorAll('.picker-item')
    .forEach((b) => b.classList.toggle('active', b.dataset.id == id));

  loadAssignments();
}

// load assignments
function loadAssignments() {
  const teacherId = document.getElementById('filterTeacher').value;
  const schoolYear = document.getElementById('filterSchoolYear').value;
  const semester = document.getElementById('filterSemester').value;
  if (!teacherId) return;

  currentSchoolYear = schoolYear;
  currentSemester = semester;

  document.getElementById('statsRow').innerHTML = '';
  document.getElementById('assignmentsPanel').innerHTML = buildLoadingHtml();

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
        renderStatsRow(data.assignments);
        document.getElementById('assignmentsPanel').innerHTML =
          buildAssignmentsPanelHtml(data.assignments, schoolYear, semester);
      } else {
        document.getElementById('assignmentsPanel').innerHTML = buildErrorHtml(
          data.message || 'failed to load assignments.'
        );
      }
    })
    .catch(() => {
      document.getElementById('assignmentsPanel').innerHTML =
        buildErrorHtml('an error occurred.');
    });
}

function renderStatsRow(assignments) {
  if (!assignments || assignments.length === 0) {
    document.getElementById('statsRow').innerHTML = '';
    return;
  }

  const total = assignments.length;
  const scheduled = assignments.filter(
    (a) => a.schedules && a.schedules.length > 0
  ).length;
  const unscheduled = total - scheduled;

  document.getElementById('statsRow').innerHTML = `
    <div class="stat-chip"><i class="bi bi-journal-bookmark-fill"></i> ${total} Assignment${
    total !== 1 ? 's' : ''
  }</div>
    <div class="stat-chip scheduled"><i class="bi bi-check-circle-fill"></i> ${scheduled} Scheduled</div>
    ${
      unscheduled > 0
        ? `<div class="stat-chip pending"><i class="bi bi-clock-fill"></i> ${unscheduled} Pending</div>`
        : ''
    }
  `;
}

// panel click delegation
function handlePanelClick(e) {
  const add = e.target.closest('.btn-add-schedule');
  const edit = e.target.closest('.btn-edit-schedule');
  const del = e.target.closest('.btn-delete-schedule');
  const toggle = e.target.closest('.accordion-toggle');

  if (add) openAddModal(add);
  else if (edit) openEditModal(edit);
  else if (del) openDeleteModal(del);
  else if (toggle) toggleAccordion(toggle);
}

function toggleAccordion(toggleEl) {
  const card = toggleEl.closest('.assignment-card');
  const body = card?.querySelector('.assignment-card-body');
  const chevron = toggleEl.querySelector('.accordion-chevron');
  if (!card || !body) return;

  const isOpen = card.classList.contains('accordion-open');
  if (isOpen) {
    card.classList.remove('accordion-open');
    body.style.maxHeight = '0';
    if (chevron) chevron.style.transform = 'rotate(0deg)';
  } else {
    card.classList.add('accordion-open');
    body.style.maxHeight = body.scrollHeight + 'px';
    if (chevron) chevron.style.transform = 'rotate(180deg)';
  }
}

// builds the 3-column assignment context card html
function buildAssignmentContextHtml(teacherName, subjectName, sectionName) {
  return `
    <div class="assignment-context-header">
      <i class="bi bi-info-circle-fill"></i> assignment info
    </div>
    <div class="assignment-context-body">
      <div class="assignment-context-item">
        <div class="assignment-context-label"><i class="bi bi-person-fill"></i> teacher</div>
        <div class="assignment-context-value">${teacherName}</div>
      </div>
      <div class="assignment-context-item">
        <div class="assignment-context-label"><i class="bi bi-book-fill"></i> subject</div>
        <div class="assignment-context-value">${subjectName}</div>
      </div>
      <div class="assignment-context-item">
        <div class="assignment-context-label"><i class="bi bi-people-fill"></i> section</div>
        <div class="assignment-context-value">${sectionName}</div>
      </div>
    </div>`;
}

// modals
function openAddModal(btn) {
  document.getElementById('addTeacherId').value = btn.dataset.teacherId;
  document.getElementById('addSubjectId').value = btn.dataset.subjectId;
  document.getElementById('addSectionId').value = btn.dataset.sectionId;
  document.getElementById('addSchoolYear').value = btn.dataset.schoolYear;
  document.getElementById('addSemester').value = btn.dataset.semester;
  document.getElementById('addAssignmentContext').innerHTML =
    buildAssignmentContextHtml(
      currentTeacherName,
      btn.dataset.subjectName,
      btn.dataset.sectionName
    );
  document.getElementById('addDayOfWeek').value = '';
  document.getElementById('addStartTime').value = '';
  document.getElementById('addEndTime').value = '';
  document.getElementById('addRoom').value = '';
  clearAddErrors();
  addModal.show();
}

function openEditModal(btn) {
  document.getElementById('editScheduleId').value = btn.dataset.scheduleId;
  document.getElementById('editTeacherId').value = btn.dataset.teacherId;
  document.getElementById('editSubjectId').value = btn.dataset.subjectId;
  document.getElementById('editSectionId').value = btn.dataset.sectionId;
  document.getElementById('editSchoolYear').value = btn.dataset.schoolYear;
  document.getElementById('editSemester').value = btn.dataset.semester;
  document.getElementById('editDayOfWeek').value = btn.dataset.day;
  document.getElementById('editStartTime').value = btn.dataset.startTime;
  document.getElementById('editEndTime').value = btn.dataset.endTime;
  document.getElementById('editRoom').value = btn.dataset.room || '';
  document.getElementById('editStatus').value = btn.dataset.status;
  document.getElementById('editAssignmentContext').innerHTML =
    buildAssignmentContextHtml(
      currentTeacherName,
      btn.dataset.subjectName,
      btn.dataset.sectionName
    );
  clearEditErrors();
  editModal.show();
}

function openDeleteModal(btn) {
  pendingDeleteId = btn.dataset.scheduleId;
  pendingDeleteTeacherId = btn.dataset.teacherId;
  pendingDeleteSubjectId = btn.dataset.subjectId;
  pendingDeleteSectionId = btn.dataset.sectionId;
  deleteModal.show();
}

function submitAddSchedule() {
  clearAddErrors();
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
      } else showModalErrors('addScheduleError', data.errors || data.message);
    })
    .catch(() =>
      showModalErrors(
        'addScheduleError',
        'an error occurred. please try again.'
      )
    )
    .finally(() => setModalLoading('confirmAddScheduleBtn', false));
}

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
      } else showModalErrors('editScheduleError', data.errors || data.message);
    })
    .catch(() =>
      showModalErrors(
        'editScheduleError',
        'an error occurred. please try again.'
      )
    )
    .finally(() => setModalLoading('confirmEditScheduleBtn', false));
}

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
      deleteModal.hide();
      if (data.success) {
        showAlert('success', data.message);
        refreshAssignmentRow(data.row_key, data.schedules);
      } else showAlert('danger', data.message || 'failed to delete schedule.');
    })
    .catch(() => {
      deleteModal.hide();
      showAlert('danger', 'an error occurred.');
    })
    .finally(() => {
      setModalLoading('confirmDeleteScheduleBtn', false);
      pendingDeleteId =
        pendingDeleteTeacherId =
        pendingDeleteSubjectId =
        pendingDeleteSectionId =
          null;
    });
}

function refreshAssignmentRow(rowKey, schedules) {
  const card = document.querySelector(`[data-row-key="${rowKey}"]`);
  if (!card) return;
  const entries = card.querySelector('.schedule-entries');
  const badge = card.querySelector('.schedule-status-badge');
  const body = card.querySelector('.assignment-card-body');
  if (!entries) return;

  if (schedules && schedules.length > 0) {
    const addBtn = card.querySelector('.btn-add-schedule');
    entries.innerHTML = schedules
      .map((s) =>
        buildScheduleEntryHtml(
          s,
          addBtn?.dataset.teacherId,
          addBtn?.dataset.subjectId,
          addBtn?.dataset.sectionId,
          addBtn?.dataset.schoolYear,
          addBtn?.dataset.semester,
          addBtn?.dataset.subjectName,
          addBtn?.dataset.sectionName
        )
      )
      .join('');
    if (badge) {
      badge.className = 'schedule-status-badge has-schedules';
      badge.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${
        schedules.length
      } schedule${schedules.length > 1 ? 's' : ''}`;
    }
  } else {
    entries.innerHTML = buildEmptyEntriesHtml();
    if (badge) {
      badge.className = 'schedule-status-badge no-schedules';
      badge.innerHTML = `<i class="bi bi-clock"></i> Not scheduled`;
    }
  }

  if (card.classList.contains('accordion-open') && body) {
    body.style.maxHeight = 'none';
    body.style.maxHeight = body.scrollHeight + 'px';
  }

  // refresh stats row
  const allCards = document.querySelectorAll('.assignment-card');
  const fakeMapped = Array.from(allCards).map((c) => ({
    schedules: c.querySelectorAll('.schedule-entry').length > 0 ? [1] : [],
  }));
  renderStatsRow(fakeMapped);
}

// time validation
function validateModalTimes(prefix) {
  const s = document.getElementById(`${prefix}StartTime`);
  const e = document.getElementById(`${prefix}EndTime`);
  if (!s || !e || !s.value || !e.value) return true;
  if (s.value >= e.value) {
    e.classList.add('is-invalid');
    let err = e.parentElement.querySelector('.time-validation-error');
    if (!err) {
      err = document.createElement('div');
      err.className = 'invalid-feedback d-block time-validation-error';
      e.parentElement.appendChild(err);
    }
    err.textContent = 'end time must be after start time.';
    return false;
  }
  e.classList.remove('is-invalid');
  e.parentElement.querySelector('.time-validation-error')?.remove();
  return true;
}

function showModalErrors(containerId, errors) {
  const el = document.getElementById(containerId);
  if (!el) return;
  el.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i>${
    typeof errors === 'string' ? errors : Object.values(errors).join('<br>')
  }`;
  el.classList.remove('d-none');
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
    btn.dataset.orig = btn.innerHTML;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Please wait...`;
  } else btn.innerHTML = btn.dataset.orig || btn.innerHTML;
}

// HTML builders
function buildLoadingHtml() {
  return `<div class="panel-loading"><div class="spinner-border" role="status"></div><p class="mb-0">Loading...</p></div>`;
}

function buildErrorHtml(msg) {
  return `<div class="empty-state"><div class="empty-state-icon"><i class="bi bi-exclamation-triangle"></i></div><p class="empty-state-title">Something went wrong</p><p class="empty-state-text">${msg}</p></div>`;
}

function buildAssignmentsPanelHtml(assignments, schoolYear, semester) {
  if (!assignments || assignments.length === 0) {
    return `<div class="empty-state"><div class="empty-state-icon"><i class="bi bi-calendar-x"></i></div><p class="empty-state-title">No assignments found</p><p class="empty-state-text">${currentTeacherName} has no active assignments for ${schoolYear} &mdash; ${semester} Semester.</p></div>`;
  }
  return assignments
    .map((a) => buildAssignmentCardHtml(a, schoolYear, semester))
    .join('');
}

function buildAssignmentCardHtml(a, schoolYear, semester) {
  const rowKey = `${a.teacher_id}_${a.subject_id}_${a.section_id}`;
  const hasSchedules = a.schedules && a.schedules.length > 0;
  const isOpen = !hasSchedules;

  const statusBadge = hasSchedules
    ? `<span class="schedule-status-badge has-schedules"><i class="bi bi-check-circle-fill"></i> ${
        a.schedules.length
      } schedule${a.schedules.length > 1 ? 's' : ''}</span>`
    : `<span class="schedule-status-badge no-schedules"><i class="bi bi-clock"></i> Not scheduled</span>`;

  const entriesHtml = hasSchedules
    ? a.schedules
        .map((s) =>
          buildScheduleEntryHtml(
            s,
            a.teacher_id,
            a.subject_id,
            a.section_id,
            schoolYear,
            semester,
            a.subject_name,
            a.section_name
          )
        )
        .join('')
    : buildEmptyEntriesHtml();

  return `
    <div class="assignment-card ${
      isOpen ? 'accordion-open' : ''
    }" data-row-key="${rowKey}">
      <div class="assignment-card-header accordion-toggle">
        <div class="assignment-info">
          <div class="subject-icon"><i class="bi bi-book-fill"></i></div>
          <div class="assignment-details">
            <p class="assignment-subject-code">${a.subject_code}</p>
            <p class="assignment-subject-name">${a.subject_name}</p>
            <span class="assignment-section-badge"><i class="bi bi-people-fill"></i> ${
              a.section_name
            }</span>
          </div>
        </div>
        <div class="assignment-card-actions">
          ${statusBadge}
          <button class="btn-add-schedule"
            data-teacher-id="${a.teacher_id}" data-subject-id="${
    a.subject_id
  }" data-section-id="${a.section_id}"
            data-school-year="${schoolYear}" data-semester="${semester}"
            data-subject-name="${a.subject_name}" data-section-name="${
    a.section_name
  }">
            <i class="bi bi-plus-circle-fill"></i> Add Schedule
          </button>
          <span class="accordion-chevron" style="transform:rotate(${
            isOpen ? 180 : 0
          }deg)"><i class="bi bi-chevron-down"></i></span>
        </div>
      </div>
      <div class="assignment-card-body" style="max-height:${
        isOpen ? '999px' : '0'
      }">
        <div class="schedule-entries">${entriesHtml}</div>
      </div>
    </div>`;
}

function buildScheduleEntryHtml(
  s,
  teacherId,
  subjectId,
  sectionId,
  schoolYear,
  semester,
  subjectName,
  sectionName
) {
  const dayClass = `day-${s.day_of_week.toLowerCase()}`;
  const startFormatted = formatTime(s.start_time);
  const endFormatted = formatTime(s.end_time);

  return `
    <div class="schedule-entry">
      <span class="day-badge ${dayClass}">${s.day_of_week}</span>
      <span class="entry-time"><i class="bi bi-clock"></i> ${startFormatted} &ndash; ${endFormatted}</span>
      ${
        s.room
          ? `<span class="entry-room"><i class="bi bi-door-closed"></i> ${s.room}</span>`
          : `<span class="entry-room-none">No room</span>`
      }
      <span class="entry-status ${s.status}">${
    s.status === 'active' ? 'Active' : 'Inactive'
  }</span>
      <div class="entry-actions">
        <button class="btn btn-sm btn-outline-primary btn-edit-schedule"
          data-schedule-id="${
            s.schedule_id
          }" data-teacher-id="${teacherId}" data-subject-id="${subjectId}"
          data-section-id="${sectionId}" data-school-year="${schoolYear}" data-semester="${semester}"
          data-day="${s.day_of_week}" data-start-time="${
    s.start_time
  }" data-end-time="${s.end_time}"
          data-room="${s.room || ''}" data-status="${s.status}"
          data-subject-name="${subjectName}" data-section-name="${sectionName}">
          <i class="bi bi-pencil-fill"></i> Edit
        </button>
        <button class="btn btn-sm btn-outline-danger btn-delete-schedule"
          data-schedule-id="${s.schedule_id}" data-teacher-id="${teacherId}"
          data-subject-id="${subjectId}" data-section-id="${sectionId}">
          <i class="bi bi-trash-fill"></i> Remove
        </button>
      </div>
    </div>`;
}

function buildEmptyEntriesHtml() {
  return `<div class="schedule-entries-empty"><i class="bi bi-calendar-plus"></i> No schedules yet. Click "Add" to get started.</div>`;
}

function formatTime(timeStr) {
  if (!timeStr) return '';
  const [hours, minutes] = timeStr.split(':');
  const h = parseInt(hours);
  return `${h % 12 === 0 ? 12 : h % 12}:${minutes} ${h >= 12 ? 'PM' : 'AM'}`;
}

function showAlert(type, message) {
  const container = document.getElementById('toastContainer');
  const id = 'toast-' + Date.now();
  const icon =
    type === 'success'
      ? 'bi-check-circle-fill'
      : 'bi-exclamation-triangle-fill';
  container.insertAdjacentHTML(
    'beforeend',
    `
    <div id="${id}" class="toast toast-${type}" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header"><i class="bi ${icon} me-2"></i><strong class="me-auto">${
      type === 'success' ? 'Success' : 'Error'
    }</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast"></button></div>
      <div class="toast-body">${message}</div>
    </div>`
  );
  const el = document.getElementById(id);
  new bootstrap.Toast(el, { autohide: true, delay: 5000 }).show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}
