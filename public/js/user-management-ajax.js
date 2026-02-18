// user management ajax handlers

// state management
let currentUsersPage = 1;
let currentStudentsPage = 1;
let usersSearchTimeout = null;
let studentsSearchTimeout = null;
let usernameCheckTimeout = null;
let emailCheckTimeout = null;

// initialize on dom loaded
document.addEventListener('DOMContentLoaded', function () {
  initializeUserManagement();
});

function initializeUserManagement() {
  // load initial data for active tab
  loadUsers();

  // tab switching
  const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
  tabButtons.forEach((btn) => {
    btn.addEventListener('shown.bs.tab', function (e) {
      const targetId = e.target.getAttribute('data-bs-target');
      if (targetId === '#studentsPanel') {
        loadStudentsWithoutAccounts();
      }
    });
  });

  // search handlers
  document.getElementById('userSearch').addEventListener('input', function (e) {
    clearTimeout(usersSearchTimeout);
    usersSearchTimeout = setTimeout(() => {
      currentUsersPage = 1;
      loadUsers();
    }, 500);
  });

  document
    .getElementById('studentSearch')
    .addEventListener('input', function (e) {
      clearTimeout(studentsSearchTimeout);
      studentsSearchTimeout = setTimeout(() => {
        currentStudentsPage = 1;
        loadStudentsWithoutAccounts();
      }, 500);
    });

  // role filter
  document.getElementById('roleFilter').addEventListener('change', function () {
    currentUsersPage = 1;
    loadUsers();
  });

  // create user button
  document
    .getElementById('createUserBtn')
    .addEventListener('click', function () {
      openCreateUserModal();
    });

  // form submissions
  document
    .getElementById('createUserForm')
    .addEventListener('submit', handleCreateUser);
  document
    .getElementById('createStudentAccountForm')
    .addEventListener('submit', handleCreateStudentAccount);
  document
    .getElementById('editUserForm')
    .addEventListener('submit', handleUpdateUser);

  // real-time username validation
  document
    .getElementById('createUsername')
    .addEventListener('input', function (e) {
      clearTimeout(usernameCheckTimeout);
      usernameCheckTimeout = setTimeout(() => {
        checkUsernameAvailability(e.target.value, null, 'create');
      }, 500);
    });

  document
    .getElementById('editUsername')
    .addEventListener('input', function (e) {
      clearTimeout(usernameCheckTimeout);
      const userId = document.getElementById('editUserId').value;
      usernameCheckTimeout = setTimeout(() => {
        checkUsernameAvailability(e.target.value, userId, 'edit');
      }, 500);
    });

  document
    .getElementById('studentAccountUsername')
    .addEventListener('input', function (e) {
      clearTimeout(usernameCheckTimeout);
      usernameCheckTimeout = setTimeout(() => {
        checkUsernameAvailability(e.target.value, null, 'studentAccount');
      }, 500);
    });

  // real-time email validation
  document
    .getElementById('createEmail')
    .addEventListener('input', function (e) {
      clearTimeout(emailCheckTimeout);
      emailCheckTimeout = setTimeout(() => {
        checkEmailAvailability(e.target.value, null, 'create');
      }, 500);
    });

  document.getElementById('editEmail').addEventListener('input', function (e) {
    clearTimeout(emailCheckTimeout);
    const userId = document.getElementById('editUserId').value;
    emailCheckTimeout = setTimeout(() => {
      checkEmailAvailability(e.target.value, userId, 'edit');
    }, 500);
  });

  document
    .getElementById('studentAccountEmail')
    .addEventListener('input', function (e) {
      clearTimeout(emailCheckTimeout);
      emailCheckTimeout = setTimeout(() => {
        checkEmailAvailability(e.target.value, null, 'studentAccount');
      }, 500);
    });

  // password strength indicators
  document
    .getElementById('createPassword')
    .addEventListener('input', function (e) {
      updatePasswordStrength(e.target);
    });

  document
    .getElementById('editPassword')
    .addEventListener('input', function (e) {
      updatePasswordStrength(e.target);
    });

  document
    .getElementById('studentAccountPassword')
    .addEventListener('input', function (e) {
      updatePasswordStrength(e.target);
    });

  // password toggle buttons
  document.querySelectorAll('.btn-toggle-password').forEach((btn) => {
    btn.addEventListener('click', function () {
      togglePasswordVisibility(this.dataset.target);
    });
  });
}

// load users list
function loadUsers() {
  const search = document.getElementById('userSearch').value.trim();
  const role = document.getElementById('roleFilter').value;
  const wrapper = document.getElementById('usersTableWrapper');

  wrapper.innerHTML = `
        <div class="loading-state">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="loading-text">Loading users...</p>
        </div>
    `;

  const params = new URLSearchParams({
    page_num: currentUsersPage,
    search: search,
    role: role,
  });

  fetch(`index.php?page=ajax_get_users&${params.toString()}`, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        renderUsersTable(data.users);
        renderUsersPagination(data.page, data.total_pages, data.total);
      } else {
        showAlert('danger', data.message || 'Failed to load users');
      }
    })
    .catch((error) => {
      console.error('error loading users:', error);
      showAlert('danger', 'An error occurred while loading users');
    });
}

// render users table
function renderUsersTable(users) {
  const wrapper = document.getElementById('usersTableWrapper');

  if (users.length === 0) {
    wrapper.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="empty-state-text">No users found</p>
            </div>
        `;
    document.getElementById('usersPaginationWrapper').classList.add('d-none');
    return;
  }

  let html = `
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><i class="bi bi-person-fill"></i> Name</th>
                    <th><i class="bi bi-person-badge-fill"></i> Username</th>
                    <th><i class="bi bi-envelope-fill"></i> Email</th>
                    <th><i class="bi bi-person-workspace"></i> Role</th>
                    <th><i class="bi bi-toggle-on"></i> Status</th>
                    <th><i class="bi bi-calendar-event"></i> Created</th>
                    <th><i class="bi bi-gear-fill"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

  users.forEach((user) => {
    const fullName = `${user.first_name} ${
      user.middle_name ? user.middle_name + ' ' : ''
    }${user.last_name}`;
    const statusBadge = getStatusBadge(user.status);
    const roleBadge = getRoleBadge(user.role);

    html += `
            <tr>
                <td class="user-full-name">${escapeHtml(fullName)}</td>
                <td>${escapeHtml(user.username)}</td>
                <td>${
                  user.email
                    ? escapeHtml(user.email)
                    : '<span class="text-muted">-</span>'
                }</td>
                <td>${roleBadge}</td>
                <td>${statusBadge}</td>
                <td><span class="text-muted">${formatDate(
                  user.created_at
                )}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-action btn-edit" onclick="openEditUserModal(${
                          user.id
                        })">
                            <i class="bi bi-pencil-square"></i>
                            Edit
                        </button>
                    </div>
                </td>
            </tr>
        `;
  });

  html += `
            </tbody>
        </table>
    `;

  wrapper.innerHTML = html;
}

// render users pagination
function renderUsersPagination(currentPage, totalPages, totalCount) {
  const wrapper = document.getElementById('usersPaginationWrapper');
  const pagination = document.getElementById('usersPagination');

  if (totalPages <= 1) {
    wrapper.classList.add('d-none');
    return;
  }

  wrapper.classList.remove('d-none');

  const search = document.getElementById('userSearch').value.trim();
  const role = document.getElementById('roleFilter').value;
  const limit = 10;
  const start = (currentPage - 1) * limit + 1;
  const end = Math.min(currentPage * limit, totalCount);

  document.getElementById('usersShowingStart').textContent = start;
  document.getElementById('usersShowingEnd').textContent = end;
  document.getElementById('usersTotalCount').textContent = totalCount;

  let html = '';

  // previous button
  html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changeUsersPage(${
              currentPage - 1
            }); return false;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;

  // page numbers
  const maxVisible = 5;
  let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
  let endPage = Math.min(totalPages, startPage + maxVisible - 1);

  if (endPage - startPage < maxVisible - 1) {
    startPage = Math.max(1, endPage - maxVisible + 1);
  }

  if (startPage > 1) {
    html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changeUsersPage(1); return false;">1</a>
            </li>
        `;
    if (startPage > 2) {
      html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
  }

  for (let i = startPage; i <= endPage; i++) {
    html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changeUsersPage(${i}); return false;">${i}</a>
            </li>
        `;
  }

  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
    html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changeUsersPage(${totalPages}); return false;">${totalPages}</a>
            </li>
        `;
  }

  // next button
  html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changeUsersPage(${
              currentPage + 1
            }); return false;">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;

  pagination.innerHTML = html;
}

// change users page
function changeUsersPage(page) {
  currentUsersPage = page;
  loadUsers();
}

// load students without accounts
function loadStudentsWithoutAccounts() {
  const search = document.getElementById('studentSearch').value.trim();
  const wrapper = document.getElementById('studentsTableWrapper');

  wrapper.innerHTML = `
        <div class="loading-state">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="loading-text">Loading students...</p>
        </div>
    `;

  const params = new URLSearchParams({
    page_num: currentStudentsPage,
    search: search,
  });

  fetch(
    `index.php?page=ajax_get_students_without_account&${params.toString()}`,
    {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    }
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        renderStudentsTable(data.students);
        renderStudentsPagination(data.page, data.total_pages, data.total);
      } else {
        showAlert('danger', data.message || 'Failed to load students');
      }
    })
    .catch((error) => {
      console.error('error loading students:', error);
      showAlert('danger', 'An error occurred while loading students');
    });
}

// render students table
function renderStudentsTable(students) {
  const wrapper = document.getElementById('studentsTableWrapper');

  if (students.length === 0) {
    wrapper.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="empty-state-text">No students without accounts found</p>
            </div>
        `;
    document
      .getElementById('studentsPaginationWrapper')
      .classList.add('d-none');
    return;
  }

  let html = `
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><i class="bi bi-person-fill"></i> Name</th>
                    <th><i class="bi bi-hash"></i> Student Number</th>
                    <th><i class="bi bi-card-text"></i> LRN</th>
                    <th><i class="bi bi-mortarboard-fill"></i> Year Level</th>
                    <th><i class="bi bi-award"></i> Strand/Course</th>
                    <th><i class="bi bi-diagram-3-fill"></i> Section</th>
                    <th><i class="bi bi-gear-fill"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

  students.forEach((student) => {
    const fullName = `${student.first_name} ${
      student.middle_name ? student.middle_name + ' ' : ''
    }${student.last_name}`;

    html += `
            <tr>
                <td class="user-full-name">${escapeHtml(fullName)}</td>
                <td>${escapeHtml(student.student_number)}</td>
                <td>${
                  student.lrn
                    ? escapeHtml(student.lrn)
                    : '<span class="text-muted">-</span>'
                }</td>
                <td>${escapeHtml(student.year_level)}</td>
                <td>${escapeHtml(student.strand_course)}</td>
                <td>${
                  student.section_name
                    ? '<span class="badge bg-primary">' +
                      escapeHtml(student.section_name) +
                      '</span>'
                    : '<span class="text-muted">-</span>'
                }</td>
                <td>
                    <button class="btn-action btn-create-account" onclick="openCreateStudentAccountModal(${
                      student.student_id
                    }, '${escapeHtml(fullName)}', '${escapeHtml(
      student.student_number
    )}', '${escapeHtml(student.year_level)}')">
                        <i class="bi bi-person-plus-fill"></i>
                        Create Account
                    </button>
                </td>
            </tr>
        `;
  });

  html += `
            </tbody>
        </table>
    `;

  wrapper.innerHTML = html;
}

// render students pagination
function renderStudentsPagination(currentPage, totalPages, totalCount) {
  const wrapper = document.getElementById('studentsPaginationWrapper');
  const pagination = document.getElementById('studentsPagination');

  if (totalPages <= 1) {
    wrapper.classList.add('d-none');
    return;
  }

  wrapper.classList.remove('d-none');

  const limit = 10;
  const start = (currentPage - 1) * limit + 1;
  const end = Math.min(currentPage * limit, totalCount);

  document.getElementById('studentsShowingStart').textContent = start;
  document.getElementById('studentsShowingEnd').textContent = end;
  document.getElementById('studentsTotalCount').textContent = totalCount;

  let html = '';

  // previous button
  html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changeStudentsPage(${
              currentPage - 1
            }); return false;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;

  // page numbers
  const maxVisible = 5;
  let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
  let endPage = Math.min(totalPages, startPage + maxVisible - 1);

  if (endPage - startPage < maxVisible - 1) {
    startPage = Math.max(1, endPage - maxVisible + 1);
  }

  if (startPage > 1) {
    html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changeStudentsPage(1); return false;">1</a>
            </li>
        `;
    if (startPage > 2) {
      html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
  }

  for (let i = startPage; i <= endPage; i++) {
    html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changeStudentsPage(${i}); return false;">${i}</a>
            </li>
        `;
  }

  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
    html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changeStudentsPage(${totalPages}); return false;">${totalPages}</a>
            </li>
        `;
  }

  // next button
  html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changeStudentsPage(${
              currentPage + 1
            }); return false;">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;

  pagination.innerHTML = html;
}

// change students page
function changeStudentsPage(page) {
  currentStudentsPage = page;
  loadStudentsWithoutAccounts();
}

// open create user modal
function openCreateUserModal() {
  const modal = new bootstrap.Modal(document.getElementById('createUserModal'));
  const form = document.getElementById('createUserForm');
  form.reset();
  clearFormErrors(form);
  clearAvailabilityFeedback('create');
  clearPasswordStrength(document.getElementById('createPassword'));
  modal.show();
}

// open create student account modal
function openCreateStudentAccountModal(
  studentId,
  name,
  studentNumber,
  yearLevel
) {
  const modal = new bootstrap.Modal(
    document.getElementById('createStudentAccountModal')
  );
  const form = document.getElementById('createStudentAccountForm');

  form.reset();
  clearFormErrors(form);
  clearAvailabilityFeedback('studentAccount');
  clearPasswordStrength(document.getElementById('studentAccountPassword'));

  document.getElementById('studentAccountStudentId').value = studentId;
  document.getElementById('studentAccountName').textContent = name;
  document.getElementById('studentAccountNumber').textContent = studentNumber;
  document.getElementById('studentAccountYear').textContent = yearLevel;

  modal.show();
}

// open edit user modal
function openEditUserModal(userId) {
  fetch(`index.php?page=ajax_get_users&page_num=1&search=&role=`, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const user = data.users.find((u) => u.id == userId);
        if (user) {
          const modal = new bootstrap.Modal(
            document.getElementById('editUserModal')
          );
          const form = document.getElementById('editUserForm');

          clearFormErrors(form);
          clearAvailabilityFeedback('edit');
          clearPasswordStrength(document.getElementById('editPassword'));

          document.getElementById('editUserId').value = user.id;
          document.getElementById('editFirstName').value = user.first_name;
          document.getElementById('editMiddleName').value =
            user.middle_name || '';
          document.getElementById('editLastName').value = user.last_name;
          document.getElementById('editUsername').value = user.username;
          document.getElementById('editEmail').value = user.email || '';
          document.getElementById('editRole').value = user.role;
          document.getElementById('editStatus').value = user.status;
          document.getElementById('editPassword').value = '';

          modal.show();
        }
      }
    })
    .catch((error) => {
      console.error('error loading user data:', error);
      showAlert('danger', 'Failed to load user data');
    });
}

// handle create user
function handleCreateUser(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');

  submitBtn.disabled = true;
  submitBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>creating...';

  fetch('index.php?page=create_user', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert('success', data.message);
        bootstrap.Modal.getInstance(
          document.getElementById('createUserModal')
        ).hide();
        form.reset();
        loadUsers();
      } else {
        if (data.errors) {
          displayFormErrors(form, data.errors);
        } else {
          showAlert('danger', data.message);
        }
      }
    })
    .catch((error) => {
      console.error('error creating user:', error);
      showAlert('danger', 'An error occurred while creating user');
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.innerHTML =
        '<i class="bi bi-check-circle-fill"></i> Create User';
    });
}

// handle create student account
function handleCreateStudentAccount(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');

  submitBtn.disabled = true;
  submitBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>creating...';

  fetch('index.php?page=create_student_account', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert('success', data.message);
        bootstrap.Modal.getInstance(
          document.getElementById('createStudentAccountModal')
        ).hide();
        form.reset();
        loadStudentsWithoutAccounts();

        // update badge count
        const badge = document.querySelector('#students-tab .badge');
        if (badge) {
          const currentCount = parseInt(badge.textContent);
          badge.textContent = currentCount - 1;
        }
      } else {
        if (data.errors) {
          displayFormErrors(form, data.errors);
        } else {
          showAlert('danger', data.message);
        }
      }
    })
    .catch((error) => {
      console.error('error creating student account:', error);
      showAlert('danger', 'An error occurred while creating account');
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.innerHTML =
        '<i class="bi bi-check-circle-fill"></i> Create Account';
    });
}

// handle update user
function handleUpdateUser(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');

  submitBtn.disabled = true;
  submitBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>updating...';

  fetch('index.php?page=update_user', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert('success', data.message);
        bootstrap.Modal.getInstance(
          document.getElementById('editUserModal')
        ).hide();
        loadUsers();
      } else {
        if (data.errors) {
          displayFormErrors(form, data.errors);
        } else {
          showAlert('danger', data.message);
        }
      }
    })
    .catch((error) => {
      console.error('error updating user:', error);
      showAlert('danger', 'An error occurred while updating user');
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.innerHTML =
        '<i class="bi bi-check-circle-fill"></i> Update User';
    });
}

// check username availability
function checkUsernameAvailability(username, excludeId, context) {
  const inputId =
    context === 'create'
      ? 'createUsername'
      : context === 'edit'
      ? 'editUsername'
      : 'studentAccountUsername';

  const input = document.getElementById(inputId);
  const feedback = input.parentElement.querySelector('.availability-feedback');

  if (!username || username.length < 3) {
    feedback.className = 'availability-feedback';
    feedback.textContent = '';
    return;
  }

  feedback.className = 'availability-feedback checking';
  feedback.textContent = 'Checking availability...';

  const params = new URLSearchParams({
    username: username,
    exclude_id: excludeId || '',
  });

  fetch(`index.php?page=ajax_check_username&${params.toString()}`, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.available) {
        feedback.className = 'availability-feedback available';
        feedback.innerHTML =
          '<i class="bi bi-check-circle-fill"></i> ' + data.message;
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
      } else {
        feedback.className = 'availability-feedback unavailable';
        feedback.innerHTML =
          '<i class="bi bi-x-circle-fill"></i> ' + data.message;
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
      }
    })
    .catch((error) => {
      console.error('error checking username:', error);
      feedback.className = 'availability-feedback';
      feedback.textContent = '';
    });
}

// check email availability
function checkEmailAvailability(email, excludeId, context) {
  const inputId =
    context === 'create'
      ? 'createEmail'
      : context === 'edit'
      ? 'editEmail'
      : 'studentAccountEmail';

  const input = document.getElementById(inputId);
  const feedback = input.parentElement.querySelector('.availability-feedback');

  if (!email) {
    feedback.className = 'availability-feedback';
    feedback.textContent = '';
    input.classList.remove('is-invalid', 'is-valid');
    return;
  }

  // basic email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    feedback.className = 'availability-feedback unavailable';
    feedback.innerHTML =
      '<i class="bi bi-x-circle-fill"></i> Invalid email format';
    input.classList.remove('is-valid');
    input.classList.add('is-invalid');
    return;
  }

  feedback.className = 'availability-feedback checking';
  feedback.textContent = 'Checking availability...';

  const params = new URLSearchParams({
    email: email,
    exclude_id: excludeId || '',
  });

  fetch(`index.php?page=ajax_check_email&${params.toString()}`, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.available) {
        feedback.className = 'availability-feedback available';
        feedback.innerHTML =
          '<i class="bi bi-check-circle-fill"></i> ' + data.message;
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
      } else {
        feedback.className = 'availability-feedback unavailable';
        feedback.innerHTML =
          '<i class="bi bi-x-circle-fill"></i> ' + data.message;
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
      }
    })
    .catch((error) => {
      console.error('error checking email:', error);
      feedback.className = 'availability-feedback';
      feedback.textContent = '';
    });
}

// update password strength indicator
function updatePasswordStrength(input) {
  const password = input.value;
  const strengthBar =
    input.parentElement.nextElementSibling.querySelector('.strength-bar');
  const requirements =
    input.parentElement.parentElement.querySelectorAll('.requirement');

  const rules = {
    length: password.length >= 8,
    uppercase: /[A-Z]/.test(password),
    lowercase: /[a-z]/.test(password),
    number: /[0-9]/.test(password),
    special: /[\W_]/.test(password),
  };

  // update requirements
  requirements.forEach((req) => {
    const rule = req.dataset.rule;
    if (rules[rule]) {
      req.classList.add('met');
      req.querySelector('i').className = 'bi bi-check-circle-fill';
    } else {
      req.classList.remove('met');
      req.querySelector('i').className = 'bi bi-x-circle-fill';
    }
  });

  // calculate strength
  const metCount = Object.values(rules).filter((v) => v).length;

  if (password.length === 0) {
    strengthBar.className = 'strength-bar';
  } else if (metCount <= 2) {
    strengthBar.className = 'strength-bar weak';
  } else if (metCount <= 4) {
    strengthBar.className = 'strength-bar medium';
  } else {
    strengthBar.className = 'strength-bar strong';
  }
}

// toggle password visibility
function togglePasswordVisibility(targetId) {
  const input = document.getElementById(targetId);
  const btn = document.querySelector(`[data-target="${targetId}"]`);
  const icon = btn.querySelector('i');

  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash-fill';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye-fill';
  }
}

// clear password strength
function clearPasswordStrength(input) {
  const strengthBar =
    input.parentElement.nextElementSibling.querySelector('.strength-bar');
  const requirements =
    input.parentElement.parentElement.querySelectorAll('.requirement');

  strengthBar.className = 'strength-bar';
  requirements.forEach((req) => {
    req.classList.remove('met');
    req.querySelector('i').className = 'bi bi-x-circle-fill';
  });
}

// clear availability feedback
function clearAvailabilityFeedback(context) {
  const usernameId =
    context === 'create'
      ? 'createUsername'
      : context === 'edit'
      ? 'editUsername'
      : 'studentAccountUsername';
  const emailId =
    context === 'create'
      ? 'createEmail'
      : context === 'edit'
      ? 'editEmail'
      : 'studentAccountEmail';

  const usernameInput = document.getElementById(usernameId);
  const emailInput = document.getElementById(emailId);

  const usernameFeedback = usernameInput.parentElement.querySelector(
    '.availability-feedback'
  );
  const emailFeedback = emailInput.parentElement.querySelector(
    '.availability-feedback'
  );

  usernameFeedback.className = 'availability-feedback';
  usernameFeedback.textContent = '';
  usernameInput.classList.remove('is-valid', 'is-invalid');

  emailFeedback.className = 'availability-feedback';
  emailFeedback.textContent = '';
  emailInput.classList.remove('is-valid', 'is-invalid');
}

// display form errors
function displayFormErrors(form, errors) {
  clearFormErrors(form);

  Object.keys(errors).forEach((fieldName) => {
    const input = form.querySelector(`[name="${fieldName}"]`);
    if (input) {
      input.classList.add('is-invalid');
      const feedback = input.parentElement.querySelector('.invalid-feedback');
      if (feedback) {
        feedback.textContent = errors[fieldName];
        feedback.style.display = 'block';
      }
    }
  });
}

// clear form errors
function clearFormErrors(form) {
  form.querySelectorAll('.is-invalid').forEach((el) => {
    el.classList.remove('is-invalid');
  });
  form.querySelectorAll('.is-valid').forEach((el) => {
    el.classList.remove('is-valid');
  });
  form.querySelectorAll('.invalid-feedback').forEach((el) => {
    el.textContent = '';
    el.style.display = 'none';
  });
}

// get status badge
function getStatusBadge(status) {
  const badges = {
    active: '<span class="badge bg-success">Active</span>',
    inactive: '<span class="badge bg-secondary">Inactive</span>',
    suspended: '<span class="badge bg-danger">Suspended</span>',
    graduated: '<span class="badge bg-primary">Graduated</span>',
  };
  return (
    badges[status] || '<span class="badge bg-secondary">' + status + '</span>'
  );
}

// get role badge
function getRoleBadge(role) {
  const badges = {
    student: '<span class="badge bg-primary">Student</span>',
    teacher: '<span class="badge bg-success">Teacher</span>',
    registrar: '<span class="badge bg-warning">Registrar</span>',
    admin: '<span class="badge bg-danger">Admin</span>',
    superadmin: '<span class="badge bg-secondary">Super Admin</span>',
  };
  return badges[role] || '<span class="badge bg-secondary">' + role + '</span>';
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

// show alert toast
function showAlert(type, message) {
  const container = document.getElementById('toastContainer');
  const toastId = 'toast-' + Date.now();

  const typeIcons = {
    success: 'bi-check-circle-fill',
    danger: 'bi-x-circle-fill',
    warning: 'bi-exclamation-triangle-fill',
    info: 'bi-info-circle-fill',
  };

  const typeLabels = {
    success: 'Success',
    danger: 'Error',
    warning: 'Warning',
    info: 'Info',
  };

  const toastHtml = `
        <div id="${toastId}" class="toast toast-${type}" role="alert">
            <div class="toast-header">
                <i class="${typeIcons[type] || typeIcons.info} me-2"></i>
                <strong class="me-auto">${
                  typeLabels[type] || typeLabels.info
                }</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `;

  container.insertAdjacentHTML('beforeend', toastHtml);

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
