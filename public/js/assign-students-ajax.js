let selectedStudents = [];
let selectedSection = null;
let currentPage = 1;
let searchQuery = '';

// UTILITY FUNCTIONS

function showAlert(type, message) {
  const toastContainer = document.getElementById('toastContainer');
  const toastId = 'toast_' + Date.now();

  const toastHTML = `
        <div class="toast toast-${type}" role="alert" id="${toastId}">
            <div class="toast-header">
                <i class="bi bi-${
                  type === 'success'
                    ? 'check-circle-fill'
                    : 'exclamation-triangle-fill'
                } me-2"></i>
                <strong class="me-auto">${
                  type === 'success' ? 'Success' : 'Error'
                }</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

  toastContainer.insertAdjacentHTML('beforeend', toastHTML);

  const toastElement = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastElement, {
    autohide: true,
    delay: 5000,
  });

  toast.show();

  toastElement.addEventListener('hidden.bs.toast', function () {
    toastElement.remove();
  });
}

function updateButtonStates() {
  const selectedCount = selectedStudents.length;
  const hasSection = selectedSection !== null;

  // update assign button
  const btnAssign = document.getElementById('btnAssignSelected');
  if (btnAssign) {
    btnAssign.disabled = !(selectedCount > 0 && hasSection);
  }

  // update bulk assign button
  const btnBulk = document.getElementById('btnBulkAssign');
  if (btnBulk) {
    btnBulk.disabled = !(selectedCount > 0 && hasSection);
    btnBulk.innerHTML = `
            <i class="bi bi-people-fill"></i>
            Bulk Assign ${selectedCount > 0 ? '(' + selectedCount + ')' : ''}
        `;
  }
}

// SECTION SELECTION

function handleSectionChange(event) {
  const select = event.target;
  const selectedOption = select.options[select.selectedIndex];

  if (!select.value) {
    selectedSection = null;
    document.getElementById('sectionInfoCard').style.display = 'none';
    document.getElementById('sectionInfoPlaceholder').style.display = 'block';
    updateButtonStates();
    return;
  }

  selectedSection = {
    id: select.value,
    name: selectedOption.text.split('(')[0].trim(),
    education: selectedOption.dataset.education,
    year: selectedOption.dataset.year,
    strand: selectedOption.dataset.strand,
    capacity: selectedOption.dataset.capacity,
    current: selectedOption.dataset.current,
    available: selectedOption.dataset.available,
  };

  // update section info display
  document.getElementById('infoSectionName').textContent = selectedSection.name;

  const eduLabel =
    selectedSection.education === 'senior_high' ? 'Senior High' : 'College';
  document.getElementById('infoEducationLevel').textContent = eduLabel;

  document.getElementById('infoYearLevel').textContent = selectedSection.year;
  document.getElementById('infoStrandCourse').textContent =
    selectedSection.strand;
  document.getElementById(
    'infoCapacity'
  ).textContent = `${selectedSection.current} / ${selectedSection.capacity}`;
  document.getElementById('infoAvailableSlots').textContent =
    selectedSection.available;

  // show info card
  document.getElementById('sectionInfoCard').style.display = 'block';
  document.getElementById('sectionInfoPlaceholder').style.display = 'none';

  updateButtonStates();
}

// STUDENT SELECTION

function handleStudentCheckbox(event) {
  const checkbox = event.target;
  const studentId = parseInt(checkbox.value);

  if (checkbox.checked) {
    if (!selectedStudents.includes(studentId)) {
      selectedStudents.push(studentId);
    }
  } else {
    selectedStudents = selectedStudents.filter((id) => id !== studentId);
  }

  updateButtonStates();
}

function selectAllStudents() {
  const checkboxes = document.querySelectorAll('.student-checkbox');
  checkboxes.forEach((checkbox) => {
    checkbox.checked = true;
    const studentId = parseInt(checkbox.value);
    if (!selectedStudents.includes(studentId)) {
      selectedStudents.push(studentId);
    }
  });
  updateButtonStates();
}

function clearAllStudents() {
  const checkboxes = document.querySelectorAll('.student-checkbox');
  checkboxes.forEach((checkbox) => {
    checkbox.checked = false;
  });
  selectedStudents = [];
  updateButtonStates();
}

// SEARCH & PAGINATION

function handleSearch(event) {
  searchQuery = event.target.value;
  currentPage = 1;
  loadStudents();
}

function handlePagination(event) {
  event.preventDefault();
  const page = parseInt(event.target.dataset.page);

  if (page && page !== currentPage) {
    currentPage = page;
    loadStudents();
  }
}

function loadStudents() {
  const container = document.getElementById('studentsListContainer');

  // show loading
  container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

  // build query params
  const params = new URLSearchParams({
    page: 'ajax_search_students',
    p: currentPage,
    search: searchQuery,
  });

  fetch(`index.php?${params.toString()}`)
    .then((response) => response.json())
    .then((data) => {
      if (!data.success) {
        throw new Error(data.message || 'Failed to load students');
      }

      renderStudentsList(data);
    })
    .catch((error) => {
      console.error('Error loading students:', error);
      container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <p class="empty-state-text">Failed to load students</p>
                </div>
            `;
    });
}

function renderStudentsList(data) {
  const container = document.getElementById('studentsListContainer');

  if (data.students.length === 0) {
    container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="empty-state-text">No students found</p>
            </div>
        `;
    return;
  }

  let html = '<div class="students-list" id="studentsList">';

  data.students.forEach((student) => {
    const isChecked = selectedStudents.includes(student.student_id);
    const middleName = student.middle_name ? student.middle_name + ' ' : '';
    const fullName = `${student.first_name} ${middleName}${student.last_name}`;

    html += `
            <div class="student-item">
                <input type="checkbox" class="student-checkbox" 
                    value="${student.student_id}"
                    id="student_${student.student_id}"
                    ${isChecked ? 'checked' : ''}>
                <label for="student_${student.student_id}">
                    <div class="student-info">
                        <div class="student-name">${fullName}</div>
                        <div class="student-meta">
                            ID: ${student.student_number} - ${
      student.year_level
    }
                        </div>
                    </div>
                </label>
            </div>
        `;
  });

  html += '</div>';

  // add pagination if needed
  if (data.total_pages > 1) {
    html += renderPagination(data);
  }

  // add selection actions
  html += `
        <div class="selection-actions mt-3">
            <button class="btn btn-sm btn-outline-secondary" id="btnSelectAll">
                Select All
            </button>
            <button class="btn btn-sm btn-outline-secondary" id="btnClearAll">
                Clear All
            </button>
        </div>
    `;

  container.innerHTML = html;

  // reattach event listeners
  attachStudentListeners();
}

function renderPagination(data) {
  const startPage = Math.max(1, data.current_page - 2);
  const endPage = Math.min(data.total_pages, data.current_page + 2);

  let html = `
        <div class="pagination-wrapper mt-3">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item ${
                      data.current_page <= 1 ? 'disabled' : ''
                    }">
                        <a class="page-link" href="#" data-page="${
                          data.current_page - 1
                        }">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
    `;

  for (let i = startPage; i <= endPage; i++) {
    html += `
            <li class="page-item ${i === data.current_page ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `;
  }

  html += `
                    <li class="page-item ${
                      data.current_page >= data.total_pages ? 'disabled' : ''
                    }">
                        <a class="page-link" href="#" data-page="${
                          data.current_page + 1
                        }">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="pagination-info text-center mt-2 text-muted small">
                Showing page ${data.current_page} of ${data.total_pages} (${
    data.total_students
  } total students)
            </div>
        </div>
    `;

  return html;
}

function attachStudentListeners() {
  // checkboxes
  document.querySelectorAll('.student-checkbox').forEach((checkbox) => {
    checkbox.addEventListener('change', handleStudentCheckbox);
  });

  // pagination
  document.querySelectorAll('.pagination .page-link').forEach((link) => {
    link.addEventListener('click', handlePagination);
  });

  // select/clear all
  const btnSelectAll = document.getElementById('btnSelectAll');
  const btnClearAll = document.getElementById('btnClearAll');

  if (btnSelectAll) {
    btnSelectAll.addEventListener('click', selectAllStudents);
  }

  if (btnClearAll) {
    btnClearAll.addEventListener('click', clearAllStudents);
  }
}

// ASSIGNMENT ACTIONS

function assignSelectedStudents() {
  if (selectedStudents.length === 0 || !selectedSection) {
    showAlert('danger', 'Please select students and a section');
    return;
  }

  // check capacity
  if (selectedStudents.length > parseInt(selectedSection.available)) {
    showAlert(
      'danger',
      `Section only has ${selectedSection.available} available slot(s)`
    );
    return;
  }

  const btn = document.getElementById('btnAssignSelected');
  const spinner = btn.querySelector('.spinner-border');
  const icon = btn.querySelector('i:not(.spinner-border)');

  // show loading
  btn.disabled = true;
  spinner.classList.remove('d-none');
  icon.classList.add('d-none');

  const formData = new FormData();
  formData.append('csrf_token', csrfToken);
  formData.append('section_id', selectedSection.id);
  selectedStudents.forEach((id) => {
    formData.append('student_ids[]', id);
  });

  fetch('index.php?page=bulk_assign_students', {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert('success', data.message);

        // reset selections
        selectedStudents = [];
        clearAllStudents();

        // reload students and assignments
        loadStudents();
        loadRecentAssignments();

        // reset section select
        document.getElementById('sectionSelect').value = '';
        selectedSection = null;
        document.getElementById('sectionInfoCard').style.display = 'none';
        document.getElementById('sectionInfoPlaceholder').style.display =
          'block';
      } else {
        showAlert('danger', data.message || 'Failed to assign students');
      }
    })
    .catch((error) => {
      console.error('Error assigning students:', error);
      showAlert('danger', 'An error occurred. Please try again.');
    })
    .finally(() => {
      btn.disabled = false;
      spinner.classList.add('d-none');
      icon.classList.remove('d-none');
      updateButtonStates();
    });
}

function cancelAssignment() {
  clearAllStudents();
  selectedSection = null;

  document.getElementById('sectionSelect').value = '';
  document.getElementById('sectionInfoCard').style.display = 'none';
  document.getElementById('sectionInfoPlaceholder').style.display = 'block';

  updateButtonStates();
}

// RECENT ASSIGNMENTS

function loadRecentAssignments() {
  const container = document.getElementById('recentAssignmentsBody');

  fetch('index.php?page=ajax_get_recent_assignments&limit=10')
    .then((response) => response.json())
    .then((data) => {
      if (!data.success) {
        throw new Error(data.message || 'Failed to load assignments');
      }

      renderRecentAssignments(data.assignments);
    })
    .catch((error) => {
      console.error('Error loading recent assignments:', error);
      container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <p class="empty-state-text">Failed to load recent assignments</p>
                </div>
            `;
    });
}

function renderRecentAssignments(assignments) {
  const container = document.getElementById('recentAssignmentsBody');

  if (assignments.length === 0) {
    container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="empty-state-text">No recent assignments</p>
            </div>
        `;
    return;
  }

  let html = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><i class="bi bi-person-fill"></i> Student</th>
                        <th><i class="bi bi-mortarboard-fill"></i> Grade</th>
                        <th><i class="bi bi-diagram-3-fill"></i> Section Assigned</th>
                        <th><i class="bi bi-calendar-fill"></i> Date Assigned</th>
                        <th><i class="bi bi-person-badge-fill"></i> Assigned By</th>
                    </tr>
                </thead>
                <tbody>
    `;

  assignments.forEach((assignment) => {
    const assignedDate = new Date(assignment.assigned_at).toLocaleDateString();
    const studentName = `${assignment.student_first_name} ${assignment.student_last_name}`;
    const eduLevel =
      assignment.education_level === 'senior_high' ? 'SHS' : 'College';

    html += `
            <tr>
                <td>${studentName}</td>
                <td>${assignment.year_level}</td>
                <td>
                    ${assignment.section_name}
                    <small class="text-muted d-block">${eduLevel} - ${
      assignment.strand_course
    }</small>
                </td>
                <td>${assignedDate}</td>
                <td>${
                  assignment.admin_username || assignment.admin_first_name
                }</td>
            </tr>
        `;
  });

  html += `
                </tbody>
            </table>
        </div>
    `;

  container.innerHTML = html;
}

// INITIALIZATION

document.addEventListener('DOMContentLoaded', function () {
  // section select
  const sectionSelect = document.getElementById('sectionSelect');
  if (sectionSelect) {
    sectionSelect.addEventListener('change', handleSectionChange);
  }

  // search input with debounce
  const searchInput = document.getElementById('studentSearch');
  if (searchInput) {
    let debounceTimer;
    searchInput.addEventListener('input', function (e) {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => handleSearch(e), 300);
    });
  }

  // assign button
  const btnAssign = document.getElementById('btnAssignSelected');
  if (btnAssign) {
    btnAssign.addEventListener('click', assignSelectedStudents);
  }

  // bulk assign button
  const btnBulk = document.getElementById('btnBulkAssign');
  if (btnBulk) {
    btnBulk.addEventListener('click', assignSelectedStudents);
  }

  // cancel button
  const btnCancel = document.getElementById('btnCancel');
  if (btnCancel) {
    btnCancel.addEventListener('click', cancelAssignment);
  }

  // attach initial listeners
  attachStudentListeners();

  // load recent assignments
  loadRecentAssignments();

  // initial button state
  updateButtonStates();
});
