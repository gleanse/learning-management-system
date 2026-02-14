// STUDENT SECTIONS AJAX HANDLER

// global state
let currentSectionId = null;
let selectedEligibleStudents = new Set();
let selectedCurrentStudents = new Set();

// dom ready
document.addEventListener('DOMContentLoaded', function () {
  initializeEventListeners();
});

// initialize all event listeners
function initializeEventListeners() {
  // section selector change
  const sectionSelector = document.getElementById('sectionSelector');
  if (sectionSelector) {
    sectionSelector.addEventListener('change', handleSectionChange);
  }

  // search handlers
  const eligibleSearch = document.getElementById('eligibleSearch');
  if (eligibleSearch) {
    eligibleSearch.addEventListener(
      'input',
      debounce(handleEligibleSearch, 300)
    );
  }

  const currentSearch = document.getElementById('currentSearch');
  if (currentSearch) {
    currentSearch.addEventListener('input', debounce(handleCurrentSearch, 300));
  }

  // bulk selection buttons
  const selectAllBtn = document.getElementById('selectAllEligible');
  if (selectAllBtn) {
    selectAllBtn.addEventListener('click', handleSelectAllEligible);
  }

  const clearAllBtn = document.getElementById('clearAllEligible');
  if (clearAllBtn) {
    clearAllBtn.addEventListener('click', handleClearAllEligible);
  }

  // assign button
  const assignBtn = document.getElementById('assignSelectedBtn');
  if (assignBtn) {
    assignBtn.addEventListener('click', handleAssignStudents);
  }

  // bulk remove button
  const bulkRemoveBtn = document.getElementById('bulkRemoveBtn');
  if (bulkRemoveBtn) {
    bulkRemoveBtn.addEventListener('click', handleBulkRemoveClick);
  }

  // modal confirm buttons
  const confirmRemoveBtn = document.getElementById('confirmRemoveBtn');
  if (confirmRemoveBtn) {
    confirmRemoveBtn.addEventListener('click', handleConfirmRemove);
  }

  const confirmBulkRemoveBtn = document.getElementById('confirmBulkRemoveBtn');
  if (confirmBulkRemoveBtn) {
    confirmBulkRemoveBtn.addEventListener('click', handleConfirmBulkRemove);
  }
}

// handle section selection change
async function handleSectionChange(e) {
  const sectionId = e.target.value;

  if (!sectionId) {
    // reset to initial state
    showInitialState();
    return;
  }

  currentSectionId = sectionId;
  const selectedOption = e.target.options[e.target.selectedIndex];

  // update section info card
  updateSectionInfo(selectedOption);

  // load section data
  await loadSectionData(sectionId);
}

// update section info card display
function updateSectionInfo(option) {
  const infoCard = document.getElementById('sectionInfoCard');
  const sectionName = option.getAttribute('data-name');
  const educationLevel = option.getAttribute('data-level');
  const yearLevel = option.getAttribute('data-year');
  const strandCourse = option.getAttribute('data-strand');
  const capacity = option.getAttribute('data-capacity');
  const currentCount = option.getAttribute('data-count');
  const available = capacity - currentCount;

  // update info values
  document.getElementById('infoSectionName').textContent = sectionName;
  document.getElementById('infoEducationLevel').textContent =
    educationLevel === 'senior_high' ? 'Senior High' : 'College';
  document.getElementById('infoYearLevel').textContent = yearLevel;
  document.getElementById('infoStrandCourse').textContent = strandCourse;
  document.getElementById(
    'infoCapacity'
  ).textContent = `${currentCount}/${capacity}`;
  document.getElementById('infoAvailable').textContent = `${available} slot${
    available !== 1 ? 's' : ''
  }`;

  // show info card
  infoCard.classList.remove('d-none');
}

// load section data from server
async function loadSectionData(sectionId) {
  try {
    const response = await fetch(
      `index.php?page=student_section_data&section_id=${sectionId}`,
      {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      }
    );

    const data = await response.json();

    if (data.success) {
      // update section info with fresh data
      updateSectionInfoFromData(data.section);

      // render eligible students
      renderEligibleStudents(data.eligible_students);

      // render current students
      renderCurrentStudents(data.current_students);

      // show loaded state
      showLoadedState();

      // reset selections
      resetSelections();
    } else {
      showAlert('danger', data.message || 'failed to load section data.');
    }
  } catch (error) {
    console.error('error loading section data:', error);
    showAlert('danger', 'an error occurred while loading section data.');
  }
}

// update section info from server data
function updateSectionInfoFromData(section) {
  const educationLabel =
    section.education_level === 'senior_high' ? 'Senior High' : 'College';

  document.getElementById('infoSectionName').textContent = section.section_name;
  document.getElementById('infoEducationLevel').textContent = educationLabel;
  document.getElementById('infoYearLevel').textContent = section.year_level;
  document.getElementById('infoStrandCourse').textContent =
    section.strand_course;
  document.getElementById(
    'infoCapacity'
  ).textContent = `${section.student_count}/${section.max_capacity}`;
  document.getElementById('infoAvailable').textContent = `${
    section.available_slots
  } slot${section.available_slots !== 1 ? 's' : ''}`;

  // update selector option
  const sectionSelector = document.getElementById('sectionSelector');
  const currentOption = sectionSelector.options[sectionSelector.selectedIndex];
  if (currentOption) {
    currentOption.setAttribute('data-count', section.student_count);
  }
}

// render eligible students list
function renderEligibleStudents(students) {
  const container = document.getElementById('eligibleStudentsList');
  const countBadge = document.getElementById('eligibleCount');

  countBadge.textContent = students.length;

  if (students.length === 0) {
    container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="empty-state-text">no eligible students found</p>
            </div>
        `;
    return;
  }

  container.innerHTML = students
    .map(
      (student) => `
        <div class="student-item" data-student-id="${student.student_id}">
            <input type="checkbox" class="form-check-input eligible-checkbox" value="${
              student.student_id
            }">
            <div class="student-info">
                <p class="student-name">${escapeHtml(
                  student.first_name
                )} ${escapeHtml(student.last_name)}</p>
                <p class="student-details">
                    <span>ID: ${escapeHtml(student.student_number)}</span>
                    <span class="separator">•</span>
                    <span>${escapeHtml(student.year_level)}</span>
                </p>
            </div>
        </div>
    `
    )
    .join('');

  // add checkbox listeners
  container.querySelectorAll('.eligible-checkbox').forEach((checkbox) => {
    checkbox.addEventListener('change', handleEligibleCheckboxChange);
  });
}

// render current students list
function renderCurrentStudents(students) {
  const container = document.getElementById('currentStudentsList');
  const countBadge = document.getElementById('currentCount');

  countBadge.textContent = students.length;

  if (students.length === 0) {
    container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="empty-state-text">no students in this section yet</p>
            </div>
        `;
    return;
  }

  container.innerHTML = students
    .map(
      (student) => `
        <div class="student-item" data-student-id="${student.student_id}">
            <input type="checkbox" class="form-check-input current-checkbox" value="${
              student.student_id
            }">
            <div class="student-info">
                <p class="student-name">${escapeHtml(
                  student.first_name
                )} ${escapeHtml(student.last_name)}</p>
                <p class="student-details">
                    <span>ID: ${escapeHtml(student.student_number)}</span>
                    <span class="separator">•</span>
                    <span>${escapeHtml(student.year_level)}</span>
                </p>
            </div>
            <div class="student-actions">
                <button class="btn btn-sm btn-remove-student" data-student-id="${
                  student.student_id
                }" data-student-name="${escapeHtml(
        student.first_name
      )} ${escapeHtml(student.last_name)}">
                    <i class="bi bi-trash"></i>
                    remove
                </button>
            </div>
        </div>
    `
    )
    .join('');

  // add remove button listeners
  container.querySelectorAll('.btn-remove-student').forEach((btn) => {
    btn.addEventListener('click', handleRemoveStudentClick);
  });

  // add checkbox listeners
  container.querySelectorAll('.current-checkbox').forEach((checkbox) => {
    checkbox.addEventListener('change', handleCurrentCheckboxChange);
  });
}

// handle eligible student checkbox change
function handleEligibleCheckboxChange(e) {
  const studentId = parseInt(e.target.value);

  if (e.target.checked) {
    selectedEligibleStudents.add(studentId);
  } else {
    selectedEligibleStudents.delete(studentId);
  }

  updateAssignButton();
}

// handle current student checkbox change
function handleCurrentCheckboxChange(e) {
  const studentId = parseInt(e.target.value);

  if (e.target.checked) {
    selectedCurrentStudents.add(studentId);
  } else {
    selectedCurrentStudents.delete(studentId);
  }

  updateBulkRemoveButton();
}

// handle select all eligible students
function handleSelectAllEligible() {
  const checkboxes = document.querySelectorAll('.eligible-checkbox');
  checkboxes.forEach((checkbox) => {
    checkbox.checked = true;
    selectedEligibleStudents.add(parseInt(checkbox.value));
  });
  updateAssignButton();
}

// handle clear all eligible students
function handleClearAllEligible() {
  const checkboxes = document.querySelectorAll('.eligible-checkbox');
  checkboxes.forEach((checkbox) => {
    checkbox.checked = false;
  });
  selectedEligibleStudents.clear();
  updateAssignButton();
}

// update assign button state
function updateAssignButton() {
  const assignBtn = document.getElementById('assignSelectedBtn');
  const countBadge = document.getElementById('selectedCount');
  const count = selectedEligibleStudents.size;

  countBadge.textContent = count;
  assignBtn.disabled = count === 0;
}

// update bulk remove button state
function updateBulkRemoveButton() {
  const bulkRemoveBtn = document.getElementById('bulkRemoveBtn');
  const countBadge = document.getElementById('removeCount');
  const count = selectedCurrentStudents.size;

  countBadge.textContent = count;

  if (count > 0) {
    bulkRemoveBtn.classList.remove('d-none');
  } else {
    bulkRemoveBtn.classList.add('d-none');
  }
}

// handle eligible students search
async function handleEligibleSearch(e) {
  const search = e.target.value.trim();

  if (!currentSectionId) return;

  try {
    const response = await fetch(
      `index.php?page=search_eligible_students&section_id=${currentSectionId}&search=${encodeURIComponent(
        search
      )}`,
      {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      }
    );

    const data = await response.json();

    if (data.success) {
      renderEligibleStudents(data.students);
      // reapply selections
      reapplyEligibleSelections();
    }
  } catch (error) {
    console.error('error searching eligible students:', error);
  }
}

// handle current students search
async function handleCurrentSearch(e) {
  const search = e.target.value.trim();

  if (!currentSectionId) return;

  try {
    const response = await fetch(
      `index.php?page=search_current_students&section_id=${currentSectionId}&search=${encodeURIComponent(
        search
      )}`,
      {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      }
    );

    const data = await response.json();

    if (data.success) {
      renderCurrentStudents(data.students);
      // reapply selections
      reapplyCurrentSelections();
    }
  } catch (error) {
    console.error('error searching current students:', error);
  }
}

// reapply eligible student selections after re-render
function reapplyEligibleSelections() {
  selectedEligibleStudents.forEach((studentId) => {
    const checkbox = document.querySelector(
      `.eligible-checkbox[value="${studentId}"]`
    );
    if (checkbox) {
      checkbox.checked = true;
    }
  });
}

// reapply current student selections after re-render
function reapplyCurrentSelections() {
  selectedCurrentStudents.forEach((studentId) => {
    const checkbox = document.querySelector(
      `.current-checkbox[value="${studentId}"]`
    );
    if (checkbox) {
      checkbox.checked = true;
    }
  });
}

// handle assign students
async function handleAssignStudents() {
  if (selectedEligibleStudents.size === 0 || !currentSectionId) return;

  const assignBtn = document.getElementById('assignSelectedBtn');
  const spinner = assignBtn.querySelector('.spinner-border');

  try {
    // show loading
    assignBtn.disabled = true;
    if (spinner) spinner.classList.remove('d-none');

    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('section_id', currentSectionId);
    selectedEligibleStudents.forEach((studentId) => {
      formData.append('student_ids[]', studentId);
    });

    const response = await fetch('index.php?page=assign_students', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      showAlert('success', data.message);

      // update section info
      if (data.section) {
        updateSectionInfoFromData({
          ...data.section,
          section_name: document.getElementById('infoSectionName').textContent,
          education_level:
            document.getElementById('infoEducationLevel').textContent ===
            'Senior High'
              ? 'senior_high'
              : 'college',
          year_level: document.getElementById('infoYearLevel').textContent,
          strand_course:
            document.getElementById('infoStrandCourse').textContent,
          max_capacity: parseInt(
            document.getElementById('infoCapacity').textContent.split('/')[1]
          ),
        });
      }

      // update student lists
      if (data.current_students) {
        renderCurrentStudents(data.current_students);
      }

      if (data.eligible_students) {
        renderEligibleStudents(data.eligible_students);
      }

      // update recent assignments table
      if (data.recent_assignments) {
        updateRecentAssignmentsTable(data.recent_assignments);
      }

      // reset selections
      selectedEligibleStudents.clear();
      updateAssignButton();
    } else {
      showAlert('danger', data.message || 'failed to assign students.');
    }
  } catch (error) {
    console.error('error assigning students:', error);
    showAlert('danger', 'an error occurred while assigning students.');
  } finally {
    // hide loading
    assignBtn.disabled = false;
    if (spinner) spinner.classList.add('d-none');
  }
}

// handle remove student button click
function handleRemoveStudentClick(e) {
  const btn = e.currentTarget;
  const studentId = btn.getAttribute('data-student-id');
  const studentName = btn.getAttribute('data-student-name');

  // show confirmation modal
  const modal = new bootstrap.Modal(
    document.getElementById('removeConfirmModal')
  );
  document.getElementById('removeStudentName').textContent = studentName;
  document
    .getElementById('confirmRemoveBtn')
    .setAttribute('data-student-id', studentId);
  modal.show();
}

// handle confirm remove single student
async function handleConfirmRemove(e) {
  const studentId = e.target.getAttribute('data-student-id');

  if (!studentId || !currentSectionId) return;

  const modal = bootstrap.Modal.getInstance(
    document.getElementById('removeConfirmModal')
  );

  try {
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('student_id', studentId);
    formData.append('section_id', currentSectionId);

    const response = await fetch('index.php?page=remove_student', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      showAlert('success', data.message);

      // update section info
      if (data.section) {
        updateSectionInfoFromData({
          ...data.section,
          section_name: document.getElementById('infoSectionName').textContent,
          education_level:
            document.getElementById('infoEducationLevel').textContent ===
            'Senior High'
              ? 'senior_high'
              : 'college',
          year_level: document.getElementById('infoYearLevel').textContent,
          strand_course:
            document.getElementById('infoStrandCourse').textContent,
          max_capacity: parseInt(
            document.getElementById('infoCapacity').textContent.split('/')[1]
          ),
        });
      }

      // update student lists
      if (data.current_students) {
        renderCurrentStudents(data.current_students);
      }

      if (data.eligible_students) {
        renderEligibleStudents(data.eligible_students);
      }

      modal.hide();
    } else {
      showAlert('danger', data.message || 'failed to remove student.');
    }
  } catch (error) {
    console.error('error removing student:', error);
    showAlert('danger', 'an error occurred while removing student.');
  }
}

// handle bulk remove button click
function handleBulkRemoveClick() {
  const count = selectedCurrentStudents.size;

  if (count === 0) return;

  // show confirmation modal
  const modal = new bootstrap.Modal(
    document.getElementById('bulkRemoveConfirmModal')
  );
  document.getElementById('bulkRemoveCount').textContent = count;
  modal.show();
}

// handle confirm bulk remove
async function handleConfirmBulkRemove() {
  if (selectedCurrentStudents.size === 0 || !currentSectionId) return;

  const modal = bootstrap.Modal.getInstance(
    document.getElementById('bulkRemoveConfirmModal')
  );

  try {
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('section_id', currentSectionId);
    selectedCurrentStudents.forEach((studentId) => {
      formData.append('student_ids[]', studentId);
    });

    const response = await fetch('index.php?page=bulk_remove_students', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      showAlert('success', data.message);

      // update section info
      if (data.section) {
        updateSectionInfoFromData({
          ...data.section,
          section_name: document.getElementById('infoSectionName').textContent,
          education_level:
            document.getElementById('infoEducationLevel').textContent ===
            'Senior High'
              ? 'senior_high'
              : 'college',
          year_level: document.getElementById('infoYearLevel').textContent,
          strand_course:
            document.getElementById('infoStrandCourse').textContent,
          max_capacity: parseInt(
            document.getElementById('infoCapacity').textContent.split('/')[1]
          ),
        });
      }

      // update student lists
      if (data.current_students) {
        renderCurrentStudents(data.current_students);
      }

      if (data.eligible_students) {
        renderEligibleStudents(data.eligible_students);
      }

      // reset selections
      selectedCurrentStudents.clear();
      updateBulkRemoveButton();

      modal.hide();
    } else {
      showAlert('danger', data.message || 'failed to remove students.');
    }
  } catch (error) {
    console.error('error removing students:', error);
    showAlert('danger', 'an error occurred while removing students.');
  }
}

// update recent assignments table
function updateRecentAssignmentsTable(assignments) {
  const tbody = document.querySelector('#recentAssignmentsTable tbody');

  if (!tbody) return;

  if (assignments.length === 0) {
    tbody.closest('.card-body').innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="empty-state-text">no recent assignments yet</p>
            </div>
        `;
    return;
  }

  tbody.innerHTML = assignments
    .map((assignment) => {
      const assignedDate = new Date(assignment.assigned_at);
      const formattedDate =
        assignedDate.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric',
          year: 'numeric',
        }) +
        ' ' +
        assignedDate.toLocaleTimeString('en-US', {
          hour: 'numeric',
          minute: '2-digit',
          hour12: true,
        });

      return `
            <tr>
                <td>${escapeHtml(assignment.student_first_name)} ${escapeHtml(
        assignment.student_last_name
      )}</td>
                <td><span class="text-muted">${escapeHtml(
                  assignment.student_number
                )}</span></td>
                <td>${escapeHtml(assignment.year_level)}</td>
                <td>
                    <span class="badge bg-primary">
                        ${escapeHtml(assignment.section_name)}
                    </span>
                </td>
                <td>${escapeHtml(assignment.strand_course)}</td>
                <td>
                    <span class="text-muted">
                        <i class="bi bi-clock"></i>
                        ${formattedDate}
                    </span>
                </td>
                <td>
                    <span class="admin-badge">
                        <i class="bi bi-person-badge-fill"></i>
                        ${escapeHtml(assignment.admin_username)}
                    </span>
                </td>
            </tr>
        `;
    })
    .join('');
}

// show initial state
function showInitialState() {
  document.getElementById('initialState').classList.remove('d-none');
  document.getElementById('loadedState').classList.add('d-none');
  document.getElementById('sectionInfoCard').classList.add('d-none');
  currentSectionId = null;
  resetSelections();
}

// show loaded state
function showLoadedState() {
  document.getElementById('initialState').classList.add('d-none');
  document.getElementById('loadedState').classList.remove('d-none');
}

// reset all selections
function resetSelections() {
  selectedEligibleStudents.clear();
  selectedCurrentStudents.clear();
  updateAssignButton();
  updateBulkRemoveButton();
}

// show alert toast
function showAlert(type, message) {
  const toastContainer = document.getElementById('toastContainer');
  const toastId = 'toast_' + Date.now();

  const iconMap = {
    success: 'bi-check-circle-fill',
    danger: 'bi-exclamation-triangle-fill',
    warning: 'bi-exclamation-circle-fill',
    info: 'bi-info-circle-fill',
  };

  const icon = iconMap[type] || iconMap.info;

  const toastHtml = `
        <div class="toast toast-${type}" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi ${icon} me-2"></i>
                <strong class="me-auto">${
                  type.charAt(0).toUpperCase() + type.slice(1)
                }</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

  toastContainer.insertAdjacentHTML('beforeend', toastHtml);

  const toastElement = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastElement, {
    autohide: true,
    delay: 5000,
  });

  toast.show();

  // remove toast from dom after hidden
  toastElement.addEventListener('hidden.bs.toast', function () {
    toastElement.remove();
  });
}

// utility debounce function
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// utility escape html
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
