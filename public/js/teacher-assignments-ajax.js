document.addEventListener('DOMContentLoaded', function () {
  // assign form submission
  const assignForm = document.getElementById('assignForm');
  if (assignForm) {
    assignForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const submitBtn = this.querySelector('button[type="submit"]');
      const spinner = submitBtn.querySelector('.spinner-border');
      const formData = new FormData(this);

      // clear previous errors
      clearFormErrors(this);

      // show loading
      submitBtn.disabled = true;
      spinner.classList.remove('d-none');

      fetch('index.php?page=assign_teacher', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // close modal
            const modal = bootstrap.Modal.getInstance(
              document.getElementById('assignModal')
            );
            modal.hide();

            // show success message
            showAlert('success', data.message);

            // add row to active table
            addRowToActiveTable(data);

            // reset form
            assignForm.reset();
          } else {
            // show errors
            if (data.errors) {
              displayFormErrors(assignForm, data.errors);
            } else {
              showAlert('danger', data.message || 'Failed to assign teacher');
            }
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          showAlert('danger', 'An error occurred. Please try again.');
        })
        .finally(() => {
          submitBtn.disabled = false;
          spinner.classList.add('d-none');
        });
    });
  }

  // reassign buttons (using event delegation)
  document.addEventListener('click', function (e) {
    if (e.target.closest('.btn-reassign')) {
      const btn = e.target.closest('.btn-reassign');
      const teacherId = btn.dataset.teacherId;
      const sectionId = btn.dataset.sectionId;
      const schoolYear = btn.dataset.schoolYear;
      const semester = btn.dataset.semester;
      const subjectIds = btn.dataset.subjectIds
        .split(',')
        .map((id) => id.trim());

      // populate reassign form
      document.getElementById('reassign_teacher_id').value = teacherId;
      document.getElementById('reassign_section_id').value = sectionId;
      document.getElementById('reassign_school_year').value = schoolYear;
      document.getElementById('reassign_semester').value = semester;

      // check the assigned subjects
      document
        .querySelectorAll('#reassignSubjectsContainer input[type="checkbox"]')
        .forEach((checkbox) => {
          checkbox.checked = subjectIds.includes(checkbox.value);
        });

      // show modal
      const modal = new bootstrap.Modal(
        document.getElementById('reassignModal')
      );
      modal.show();
    }
  });

  // reassign form submission
  const reassignForm = document.getElementById('reassignForm');
  if (reassignForm) {
    reassignForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const submitBtn = this.querySelector('button[type="submit"]');
      const spinner = submitBtn.querySelector('.spinner-border');
      const formData = new FormData(this);

      // clear previous errors
      clearFormErrors(this);

      // show loading
      submitBtn.disabled = true;
      spinner.classList.remove('d-none');

      fetch('index.php?page=reassign_teacher', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // close modal
            const modal = bootstrap.Modal.getInstance(
              document.getElementById('reassignModal')
            );
            modal.hide();

            // show success message
            showAlert('success', data.message);

            // update or remove row based on action
            if (data.action === 'update') {
              updateActiveRow(data.row_key, data.data);
              if (data.inactive_data) {
                updateInactiveRow(data.row_key, data.inactive_data);
              }
            } else if (data.action === 'remove') {
              removeActiveRow(data.row_key);
              if (data.inactive_data) {
                addRowToInactiveTable(data.inactive_data);
              }
            }
          } else {
            // show errors
            if (data.errors) {
              displayFormErrors(reassignForm, data.errors);
            } else {
              showAlert(
                'danger',
                data.message || 'Failed to update assignment'
              );
            }
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          showAlert('danger', 'An error occurred. Please try again.');
        })
        .finally(() => {
          submitBtn.disabled = false;
          spinner.classList.add('d-none');
        });
    });
  }

  // remove buttons
  document.addEventListener('click', function (e) {
    if (e.target.closest('.btn-remove')) {
      const btn = e.target.closest('.btn-remove');
      const teacherId = btn.dataset.teacherId;
      const sectionId = btn.dataset.sectionId;
      const schoolYear = btn.dataset.schoolYear;
      const semester = btn.dataset.semester;
      const teacherName = btn.dataset.teacherName;
      const sectionName = btn.dataset.sectionName;

      if (
        confirm(
          `Remove ${teacherName} from ${sectionName} (${semester} Semester)?`
        )
      ) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('teacher_id', teacherId);
        formData.append('section_id', sectionId);
        formData.append('school_year', schoolYear);
        formData.append('semester', semester);

        btn.disabled = true;

        fetch('index.php?page=remove_teacher_assignment', {
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

              if (data.action === 'move_to_removed') {
                removeActiveRow(data.row_key);
                addRowToInactiveTable(data.data);
              }
            } else {
              showAlert(
                'danger',
                data.message || 'Failed to remove assignment'
              );
            }
          })
          .catch((error) => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred. Please try again.');
          })
          .finally(() => {
            btn.disabled = false;
          });
      }
    }
  });

  // restore buttons
  document.addEventListener('click', function (e) {
    if (e.target.closest('.btn-restore')) {
      const btn = e.target.closest('.btn-restore');
      const teacherId = btn.dataset.teacherId;
      const sectionId = btn.dataset.sectionId;
      const schoolYear = btn.dataset.schoolYear;
      const semester = btn.dataset.semester;
      const teacherName = btn.dataset.teacherName;
      const sectionName = btn.dataset.sectionName;

      if (
        confirm(
          `Restore ${teacherName} to ${sectionName} (${semester} Semester)?`
        )
      ) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('teacher_id', teacherId);
        formData.append('section_id', sectionId);
        formData.append('school_year', schoolYear);
        formData.append('semester', semester);

        btn.disabled = true;

        fetch('index.php?page=restore_teacher_assignment', {
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

              if (data.action === 'add') {
                removeInactiveRow(
                  `${teacherId}_${sectionId}_${schoolYear}_${semester}`
                );
                addRowToActiveTable(data.data);
              }
            } else {
              showAlert(
                'danger',
                data.message || 'Failed to restore assignment'
              );
            }
          })
          .catch((error) => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred. Please try again.');
          })
          .finally(() => {
            btn.disabled = false;
          });
      }
    }
  });

  // subjects modal
  const subjectsModal = document.getElementById('subjectsModal');
  if (subjectsModal) {
    subjectsModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const subjects = button.dataset.subjects;
      const subjectsList = document.getElementById('subjectsList');

      subjectsList.innerHTML = '';
      subjects.split(',').forEach((subject) => {
        const li = document.createElement('li');
        li.className = 'list-group-item';
        li.textContent = subject.trim();
        subjectsList.appendChild(li);
      });
    });
  }
});

// helper functions

function showAlert(type, message) {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
  alertDiv.innerHTML = `
        <i class="bi bi-${
          type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'
        } me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

  const container = document.querySelector('.container-fluid');
  container.insertBefore(alertDiv, container.firstChild);

  // auto dismiss after 5 seconds
  setTimeout(() => {
    alertDiv.remove();
  }, 5000);
}

function clearFormErrors(form) {
  form
    .querySelectorAll('.is-invalid')
    .forEach((el) => el.classList.remove('is-invalid'));
  form
    .querySelectorAll('.invalid-feedback')
    .forEach((el) => (el.textContent = ''));
}

function displayFormErrors(form, errors) {
  Object.keys(errors).forEach((fieldName) => {
    const field = form.querySelector(`[name="${fieldName}"]`);
    if (field) {
      field.classList.add('is-invalid');
      const feedback = field.parentElement.querySelector('.invalid-feedback');
      if (feedback) {
        feedback.textContent = errors[fieldName];
      }
    }
  });
}

function addRowToActiveTable(data) {
  const tbody = document.querySelector('#activeAssignmentsTable tbody');

  // remove empty state if exists
  const emptyRow = tbody.querySelector('td[colspan]');
  if (emptyRow) {
    emptyRow.parentElement.remove();
  }

  const subjectsDisplay =
    data.subject_count <= 3
      ? `<span class="text-muted">${data.subjects}</span>`
      : `<button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#subjectsModal" data-subjects="${data.subjects}">
            <i class="bi bi-book"></i> ${data.subject_count} subjects
           </button>`;

  const row = document.createElement('tr');
  row.dataset.rowKey = `${data.teacher_id}_${data.section_id}_${data.school_year}_${data.semester}`;
  row.innerHTML = `
        <td>${data.teacher_name}</td>
        <td>${data.section_name}</td>
        <td>${data.year_level}</td>
        <td>${data.school_year}</td>
        <td><span class="badge bg-info">${data.semester}</span></td>
        <td>${subjectsDisplay}</td>
        <td>
            <button class="btn btn-sm btn-outline-primary me-1 btn-reassign" 
                data-teacher-id="${data.teacher_id}"
                data-section-id="${data.section_id}"
                data-school-year="${data.school_year}"
                data-semester="${data.semester}"
                data-subject-ids="${data.subject_ids}">
                <i class="bi bi-pencil"></i> Reassign
            </button>
            <button class="btn btn-sm btn-outline-danger btn-remove"
                data-teacher-id="${data.teacher_id}"
                data-section-id="${data.section_id}"
                data-school-year="${data.school_year}"
                data-semester="${data.semester}"
                data-teacher-name="${data.teacher_name}"
                data-section-name="${data.section_name}">
                <i class="bi bi-trash"></i> Remove
            </button>
        </td>
    `;
  tbody.appendChild(row);
}

function addRowToInactiveTable(data) {
  const tbody = document.querySelector('#inactiveAssignmentsTable tbody');

  // remove empty state if exists
  const emptyRow = tbody.querySelector('td[colspan]');
  if (emptyRow) {
    emptyRow.parentElement.remove();
  }

  const subjectsDisplay =
    data.subject_count <= 3
      ? `<span class="text-muted">${data.subjects}</span>`
      : `<button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#subjectsModal" data-subjects="${data.subjects}">
            <i class="bi bi-book"></i> ${data.subject_count} subjects
           </button>`;

  const row = document.createElement('tr');
  row.dataset.rowKey = `${data.teacher_id}_${data.section_id}_${data.school_year}_${data.semester}`;
  row.innerHTML = `
        <td>${data.teacher_name}</td>
        <td>${data.section_name}</td>
        <td>${data.year_level}</td>
        <td>${data.school_year}</td>
        <td><span class="badge bg-secondary">${data.semester}</span></td>
        <td>${subjectsDisplay}</td>
        <td>
            <button class="btn btn-sm btn-outline-success btn-restore"
                data-teacher-id="${data.teacher_id}"
                data-section-id="${data.section_id}"
                data-school-year="${data.school_year}"
                data-semester="${data.semester}"
                data-teacher-name="${data.teacher_name}"
                data-section-name="${data.section_name}">
                <i class="bi bi-arrow-clockwise"></i> Restore
            </button>
        </td>
    `;
  tbody.appendChild(row);
}

function updateActiveRow(rowKey, data) {
  const row = document.querySelector(
    `#activeAssignmentsTable tr[data-row-key="${rowKey}"]`
  );
  if (row) {
    const subjectsDisplay =
      data.subject_count <= 3
        ? `<span class="text-muted">${data.subjects}</span>`
        : `<button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#subjectsModal" data-subjects="${data.subjects}">
                <i class="bi bi-book"></i> ${data.subject_count} subjects
               </button>`;

    row.children[5].innerHTML = subjectsDisplay;

    // update reassign button subject_ids
    const reassignBtn = row.querySelector('.btn-reassign');
    reassignBtn.dataset.subjectIds = data.subject_ids;
  }
}

function updateInactiveRow(rowKey, data) {
  const row = document.querySelector(
    `#inactiveAssignmentsTable tr[data-row-key="${rowKey}"]`
  );
  if (row) {
    const subjectsDisplay =
      data.subject_count <= 3
        ? `<span class="text-muted">${data.subjects}</span>`
        : `<button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#subjectsModal" data-subjects="${data.subjects}">
                <i class="bi bi-book"></i> ${data.subject_count} subjects
               </button>`;

    row.children[5].innerHTML = subjectsDisplay;
  }
}

function removeActiveRow(rowKey) {
  const row = document.querySelector(
    `#activeAssignmentsTable tr[data-row-key="${rowKey}"]`
  );
  if (row) {
    row.remove();

    // add empty state if no more rows
    const tbody = document.querySelector('#activeAssignmentsTable tbody');
    if (tbody.children.length === 0) {
      tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No active assignments yet
                    </td>
                </tr>
            `;
    }
  }
}

function removeInactiveRow(rowKey) {
  const row = document.querySelector(
    `#inactiveAssignmentsTable tr[data-row-key="${rowKey}"]`
  );
  if (row) {
    row.remove();

    // add empty state if no more rows
    const tbody = document.querySelector('#inactiveAssignmentsTable tbody');
    if (tbody.children.length === 0) {
      tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No removed assignments
                    </td>
                </tr>
            `;
    }
  }
}
