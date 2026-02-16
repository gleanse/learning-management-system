document.addEventListener('DOMContentLoaded', function () {
  // state management for pagination
  let assignCurrentPage = 1;
  let assignSearchTerm = '';
  let reassignCurrentPage = 1;
  let reassignSearchTerm = '';
  let reassignCurrentSubjectIds = [];

  // initialize custom searchable selects
  initializeCustomSelect(
    'teacherDisplay',
    'teacherDropdown',
    'teacherSearch',
    'teacherOptions',
    'teacherIdInput',
    'ajax_search_assignment_teachers'
  );

  initializeCustomSelect(
    'sectionDisplay',
    'sectionDropdown',
    'sectionSearch',
    'sectionOptions',
    'sectionIdInput',
    'ajax_search_assignment_sections'
  );

  // assign form subject search
  const assignSubjectSearch = document.getElementById('assignSubjectSearch');
  if (assignSubjectSearch) {
    let searchTimeout;
    assignSubjectSearch.addEventListener('input', function (e) {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        assignSearchTerm = e.target.value;
        assignCurrentPage = 1;
        loadAssignSubjects();
      }, 300);
    });
  }

  // reassign modal subject search
  const reassignSubjectSearch = document.getElementById(
    'reassignSubjectSearch'
  );
  if (reassignSubjectSearch) {
    let searchTimeout;
    reassignSubjectSearch.addEventListener('input', function (e) {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        reassignSearchTerm = e.target.value;
        reassignCurrentPage = 1;
        loadReassignSubjects();
      }, 300);
    });
  }

  // select/deselect all buttons for assign form
  document
    .getElementById('selectAllSubjects')
    ?.addEventListener('click', function () {
      document
        .querySelectorAll('#assignSubjectsContainer .subject-checkbox')
        .forEach((cb) => {
          cb.checked = true;
        });
      updateSelectedCount('assignSelectedCount', '#assignSubjectsContainer');
    });

  document
    .getElementById('deselectAllSubjects')
    ?.addEventListener('click', function () {
      document
        .querySelectorAll('#assignSubjectsContainer .subject-checkbox')
        .forEach((cb) => {
          cb.checked = false;
        });
      updateSelectedCount('assignSelectedCount', '#assignSubjectsContainer');
    });

  // select/deselect all buttons for reassign modal
  document
    .getElementById('reassignSelectAll')
    ?.addEventListener('click', function () {
      document
        .querySelectorAll('#reassignSubjectsContainer .subject-checkbox')
        .forEach((cb) => {
          cb.checked = true;
        });
      updateSelectedCount(
        'reassignSelectedCount',
        '#reassignSubjectsContainer'
      );
    });

  document
    .getElementById('reassignDeselectAll')
    ?.addEventListener('click', function () {
      document
        .querySelectorAll('#reassignSubjectsContainer .subject-checkbox')
        .forEach((cb) => {
          cb.checked = false;
        });
      updateSelectedCount(
        'reassignSelectedCount',
        '#reassignSubjectsContainer'
      );
    });

  // update selected count when checkboxes change
  document.addEventListener('change', function (e) {
    if (e.target.classList.contains('subject-checkbox')) {
      if (e.target.closest('#assignSubjectsContainer')) {
        updateSelectedCount('assignSelectedCount', '#assignSubjectsContainer');
      } else if (e.target.closest('#reassignSubjectsContainer')) {
        updateSelectedCount(
          'reassignSelectedCount',
          '#reassignSubjectsContainer'
        );
      }
    }
  });

  // initial count update
  updateSelectedCount('assignSelectedCount', '#assignSubjectsContainer');
  updateSelectedCount('reassignSelectedCount', '#reassignSubjectsContainer');

  // assign form submission
  const assignForm = document.getElementById('assignForm');
  if (assignForm) {
    assignForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const submitBtn = this.querySelector('button[type="submit"]');
      const spinner = submitBtn.querySelector('.spinner-border');
      const formData = new FormData(this);

      clearFormErrors(this);

      if (
        this.querySelectorAll('input[name="subject_ids[]"]:checked').length ===
        0
      ) {
        displayFormErrors(this, {
          subject_ids: 'please select at least one subject.',
        });
        return;
      }

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
            showAlert('success', data.message);

            if (data.row_key) {
              removeInactiveRow(data.row_key);
              removeActiveRow(data.row_key);
              addRowToActiveTable(data);

              if (data.inactive_data) {
                const existingInactive = document.querySelector(
                  `#inactiveAssignmentsTable tr[data-row-key="${data.row_key}"]`
                );
                if (existingInactive) {
                  updateInactiveRow(data.row_key, data.inactive_data);
                } else {
                  addRowToInactiveTable(data.inactive_data);
                }
              } else {
                removeInactiveRow(data.row_key);
              }
            } else {
              addRowToActiveTable(data);
            }

            // reset form and custom selects
            assignForm.reset();
            resetCustomSelect('teacherDisplay', 'teacherIdInput');
            resetCustomSelect('sectionDisplay', 'sectionIdInput');
            document.getElementById('assignSubjectSearch').value = '';
            assignSearchTerm = '';
            assignCurrentPage = 1;
            loadAssignSubjects();
            updateSelectedCount(
              'assignSelectedCount',
              '#assignSubjectsContainer'
            );
          } else {
            if (data.errors) {
              displayFormErrors(assignForm, data.errors);
            } else {
              showAlert('danger', data.message || 'failed to assign teacher');
            }
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          showAlert('danger', 'an error occurred. please try again.');
        })
        .finally(() => {
          submitBtn.disabled = false;
          spinner.classList.add('d-none');
        });
    });
  }

  // reassign buttons
  document.addEventListener('click', function (e) {
    if (e.target.closest('.btn-reassign')) {
      const btn = e.target.closest('.btn-reassign');
      const teacherId = btn.dataset.teacherId;
      const sectionId = btn.dataset.sectionId;
      const schoolYear = btn.dataset.schoolYear;
      const semester = btn.dataset.semester;
      const teacherName = btn.dataset.teacherName;
      const sectionName = btn.dataset.sectionName;
      const subjectIds = btn.dataset.subjectIds
        .split(',')
        .map((id) => id.trim());

      reassignCurrentSubjectIds = subjectIds;

      document.getElementById('reassign_teacher_id').value = teacherId;
      document.getElementById('reassign_section_id').value = sectionId;
      document.getElementById('reassign_school_year').value = schoolYear;
      document.getElementById('reassign_semester').value = semester;
      document.getElementById(
        'reassignContext'
      ).textContent = `${teacherName} - ${sectionName} (${semester} semester)`;

      // reset search and load subjects with current selections
      document.getElementById('reassignSubjectSearch').value = '';
      reassignSearchTerm = '';
      reassignCurrentPage = 1;
      loadReassignSubjects();

      const modal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('reassignModal')
      );
      modal.show();
    }
  });

  // view subjects buttons
  document.addEventListener('click', function (e) {
    if (e.target.closest('.view-subjects-btn')) {
      const btn = e.target.closest('.view-subjects-btn');
      const subjects = btn.dataset.subjects;
      const teacherName = btn.dataset.teacherName;
      const sectionName = btn.dataset.sectionName;

      document.getElementById(
        'subjectsModalContext'
      ).textContent = `${teacherName} - ${sectionName}`;

      const subjectsList = document.getElementById('subjectsList');
      subjectsList.innerHTML = '';
      subjects.split(',').forEach((subject) => {
        const li = document.createElement('li');
        li.className = 'list-group-item';
        li.textContent = subject.trim();
        subjectsList.appendChild(li);
      });

      const modal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('subjectsModal')
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

      clearFormErrors(this);

      if (
        this.querySelectorAll('input[name="subject_ids[]"]:checked').length ===
        0
      ) {
        displayFormErrors(this, {
          subject_ids: 'please select at least one subject.',
        });
        return;
      }

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
            const modal = bootstrap.Modal.getInstance(
              document.getElementById('reassignModal')
            );
            modal.hide();

            showAlert('success', data.message);

            if (data.action === 'update') {
              updateActiveRow(data.row_key, data.data);

              if (data.inactive_data) {
                const existingInactive = document.querySelector(
                  `#inactiveAssignmentsTable tr[data-row-key="${data.row_key}"]`
                );
                if (existingInactive) {
                  updateInactiveRow(data.row_key, data.inactive_data);
                } else {
                  addRowToInactiveTable(data.inactive_data);
                }
              } else {
                removeInactiveRow(data.row_key);
              }
            } else if (data.action === 'remove') {
              removeActiveRow(data.row_key);
              if (data.inactive_data) {
                removeInactiveRow(data.row_key);
                addRowToInactiveTable(data.inactive_data);
              }
            }
          } else {
            if (data.errors) {
              displayFormErrors(reassignForm, data.errors);
            } else {
              showAlert(
                'danger',
                data.message || 'failed to update assignment'
              );
            }
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          showAlert('danger', 'an error occurred. please try again.');
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
          `remove ${teacherName} from ${sectionName} (${semester} semester)?`
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
                removeInactiveRow(data.row_key);
                addRowToInactiveTable(data.data);
              }
            } else {
              showAlert(
                'danger',
                data.message || 'failed to remove assignment'
              );
            }
          })
          .catch((error) => {
            console.error('Error:', error);
            showAlert('danger', 'an error occurred. please try again.');
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
          `restore ${teacherName} to ${sectionName} (${semester} semester)?`
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
                removeActiveRow(
                  `${teacherId}_${sectionId}_${schoolYear}_${semester}`
                );
                addRowToActiveTable(data.data);
              }
            } else {
              showAlert(
                'danger',
                data.message || 'failed to restore assignment'
              );
            }
          })
          .catch((error) => {
            console.error('Error:', error);
            showAlert('danger', 'an error occurred. please try again.');
          })
          .finally(() => {
            btn.disabled = false;
          });
      }
    }
  });

  // load subjects for assign form
  function loadAssignSubjects() {
    const container = document.getElementById('assignSubjectsContainer');
    const pagination = document.getElementById('assignSubjectPagination');
    const paginationInfo = document.getElementById('assignPaginationInfo');

    fetch(
      `index.php?page=ajax_search_assignment_subjects&search=${encodeURIComponent(
        assignSearchTerm
      )}&p=${assignCurrentPage}`
    )
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          container.innerHTML = data.html;

          // update pagination
          if (data.total_pages > 1) {
            renderPagination(
              pagination.querySelector('.pagination'),
              assignCurrentPage,
              data.total_pages,
              (page) => {
                assignCurrentPage = page;
                loadAssignSubjects();
              }
            );
            paginationInfo.textContent = `page ${data.current_page} of ${data.total_pages} (${data.total_subjects} subjects)`;
            pagination.style.display = 'block';
          } else {
            pagination.style.display = 'none';
          }

          updateSelectedCount(
            'assignSelectedCount',
            '#assignSubjectsContainer'
          );
        }
      })
      .catch((error) => {
        console.error('Error loading subjects:', error);
      });
  }

  // load subjects for reassign modal
  function loadReassignSubjects() {
    const container = document.getElementById('reassignSubjectsContainer');
    const pagination = document.getElementById('reassignSubjectPagination');
    const paginationInfo = document.getElementById('reassignPaginationInfo');

    const currentIds = reassignCurrentSubjectIds.join(',');

    fetch(
      `index.php?page=ajax_search_reassignment_subjects&search=${encodeURIComponent(
        reassignSearchTerm
      )}&p=${reassignCurrentPage}&current_ids=${encodeURIComponent(currentIds)}`
    )
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          container.innerHTML = data.html;

          // update pagination
          if (data.total_pages > 1) {
            renderPagination(
              pagination.querySelector('.pagination'),
              reassignCurrentPage,
              data.total_pages,
              (page) => {
                reassignCurrentPage = page;
                loadReassignSubjects();
              }
            );
            paginationInfo.textContent = `page ${data.current_page} of ${data.total_pages} (${data.total_subjects} subjects)`;
            pagination.style.display = 'block';
          } else {
            pagination.style.display = 'none';
          }

          updateSelectedCount(
            'reassignSelectedCount',
            '#reassignSubjectsContainer'
          );
        }
      })
      .catch((error) => {
        console.error('Error loading subjects:', error);
      });
  }

  // initialize custom searchable select dropdown
  function initializeCustomSelect(
    displayId,
    dropdownId,
    searchInputId,
    optionsId,
    hiddenInputId,
    ajaxEndpoint
  ) {
    const display = document.getElementById(displayId);
    const dropdown = document.getElementById(dropdownId);
    const searchInput = document.getElementById(searchInputId);
    const optionsList = document.getElementById(optionsId);
    const hiddenInput = document.getElementById(hiddenInputId);

    if (!display || !dropdown || !searchInput || !optionsList || !hiddenInput)
      return;

    let searchTimeout;

    // toggle dropdown on display click
    display.addEventListener('click', function (e) {
      e.stopPropagation();
      const isActive = dropdown.style.display === 'block';

      // close all other dropdowns
      document
        .querySelectorAll('.select-dropdown')
        .forEach((dd) => (dd.style.display = 'none'));
      document
        .querySelectorAll('.select-display')
        .forEach((sd) => sd.classList.remove('active'));

      if (!isActive) {
        dropdown.style.display = 'block';
        display.classList.add('active');
        searchInput.focus();
      } else {
        dropdown.style.display = 'none';
        display.classList.remove('active');
      }
    });

    // search with debounce
    searchInput.addEventListener('input', function (e) {
      clearTimeout(searchTimeout);
      const searchTerm = e.target.value;

      // show loading
      const loadingDiv = dropdown.querySelector('.dropdown-loading');
      if (loadingDiv) {
        loadingDiv.style.display = 'flex';
        optionsList.style.display = 'none';
      }

      searchTimeout = setTimeout(() => {
        fetch(
          `index.php?page=${ajaxEndpoint}&search=${encodeURIComponent(
            searchTerm
          )}`
        )
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              // hide loading
              if (loadingDiv) {
                loadingDiv.style.display = 'none';
                optionsList.style.display = 'block';
              }

              // render options from html response
              if (data.html) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.html;

                optionsList.innerHTML = '';

                const options = tempDiv.querySelectorAll('option');
                options.forEach((option) => {
                  const li = document.createElement('li');
                  li.className = 'select-option';
                  li.dataset.value = option.value;
                  li.textContent = option.textContent;

                  if (option.value === hiddenInput.value) {
                    li.classList.add('selected');
                  }

                  optionsList.appendChild(li);
                });
              }
            }
          })
          .catch((error) => {
            console.error('search error:', error);
            if (loadingDiv) {
              loadingDiv.style.display = 'none';
              optionsList.style.display = 'block';
            }
          });
      }, 300);
    });

    // handle option selection
    optionsList.addEventListener('click', function (e) {
      const option = e.target.closest('.select-option');
      if (!option) return;

      const value = option.dataset.value;
      const text = option.textContent;

      // update hidden input
      hiddenInput.value = value;

      // update display
      const displayContent = display.querySelector('span');
      if (value) {
        displayContent.textContent = text;
        displayContent.className = 'selected-text';
      } else {
        displayContent.textContent = display.dataset.placeholder || 'select...';
        displayContent.className = 'select-placeholder';
      }

      // update selected state
      optionsList
        .querySelectorAll('.select-option')
        .forEach((opt) => opt.classList.remove('selected'));
      option.classList.add('selected');

      // close dropdown
      dropdown.style.display = 'none';
      display.classList.remove('active');

      // clear search
      searchInput.value = '';
    });

    // close on outside click
    document.addEventListener('click', function (e) {
      if (!display.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
        display.classList.remove('active');
      }
    });

    // prevent dropdown close when clicking inside
    dropdown.addEventListener('click', function (e) {
      e.stopPropagation();
    });
  }

  // reset custom select to default state
  function resetCustomSelect(displayId, hiddenInputId) {
    const display = document.getElementById(displayId);
    const hiddenInput = document.getElementById(hiddenInputId);

    if (!display || !hiddenInput) return;

    hiddenInput.value = '';
    const displayContent = display.querySelector('span');
    if (displayContent) {
      displayContent.textContent = display.dataset.placeholder || 'select...';
      displayContent.className = 'select-placeholder';
    }
  }

  // render pagination controls
  function renderPagination(
    paginationElement,
    currentPage,
    totalPages,
    onPageChange
  ) {
    paginationElement.innerHTML = '';

    // previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage <= 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#"><i class="bi bi-chevron-left"></i></a>`;
    if (currentPage > 1) {
      prevLi.querySelector('a').addEventListener('click', (e) => {
        e.preventDefault();
        onPageChange(currentPage - 1);
      });
    }
    paginationElement.appendChild(prevLi);

    // page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    for (let i = startPage; i <= endPage; i++) {
      const pageLi = document.createElement('li');
      pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
      pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
      if (i !== currentPage) {
        pageLi.querySelector('a').addEventListener('click', (e) => {
          e.preventDefault();
          onPageChange(i);
        });
      }
      paginationElement.appendChild(pageLi);
    }

    // next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${
      currentPage >= totalPages ? 'disabled' : ''
    }`;
    nextLi.innerHTML = `<a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a>`;
    if (currentPage < totalPages) {
      nextLi.querySelector('a').addEventListener('click', (e) => {
        e.preventDefault();
        onPageChange(currentPage + 1);
      });
    }
    paginationElement.appendChild(nextLi);
  }

  // update selected count badge
  function updateSelectedCount(badgeId, containerSelector) {
    const badge = document.getElementById(badgeId);
    const container = document.querySelector(containerSelector);
    if (!badge || !container) return;

    const count = container.querySelectorAll(
      '.subject-checkbox:checked'
    ).length;
    badge.textContent = `${count} selected`;
  }
});

// helper functions

function showAlert(type, message) {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const iconClass =
    type === 'success'
      ? 'bi-check-circle-fill'
      : 'bi-exclamation-triangle-fill';
  const toastThemeClass = type === 'success' ? 'toast-success' : 'toast-danger';
  const titleText = type === 'success' ? 'success' : 'warning';
  const toastId = 'toast_' + Date.now();

  const toastHtml = `
    <div id="${toastId}" class="toast ${toastThemeClass}" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <i class="bi ${iconClass} me-2"></i>
        <strong class="me-auto">${titleText}</strong>
        <small class="text-white-50"></small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        ${message}
      </div>
    </div>
  `;

  container.insertAdjacentHTML('beforeend', toastHtml);

  const toastElement = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastElement, { delay: 5000 });

  toast.show();

  toastElement.addEventListener('hidden.bs.toast', function () {
    toastElement.remove();
  });
}

function clearFormErrors(form) {
  form
    .querySelectorAll('.is-invalid')
    .forEach((el) => el.classList.remove('is-invalid'));
  form.querySelectorAll('.invalid-feedback').forEach((el) => {
    if (!el.id.includes('subject_ids_error')) {
      el.textContent = '';
    }
  });

  const assignContainer = form.querySelector('#assignSubjectsContainer');
  if (assignContainer) assignContainer.classList.remove('border-danger');

  const reassignContainer = form.querySelector('#reassignSubjectsContainer');
  if (reassignContainer) reassignContainer.classList.remove('border-danger');

  const subjectErr = form.querySelector('[id$="subject_ids_error"]');
  if (subjectErr) subjectErr.textContent = '';
}

function displayFormErrors(form, errors) {
  Object.keys(errors).forEach((fieldName) => {
    if (fieldName === 'subject_ids') {
      let container, feedback;
      if (form.id === 'assignForm') {
        container = form.querySelector('#assignSubjectsContainer');
        feedback = form.querySelector('#subject_ids_error');
      } else if (form.id === 'reassignForm') {
        container = form.querySelector('#reassignSubjectsContainer');
        feedback = form.querySelector('#reassign_subject_ids_error');
      }

      if (container) container.classList.add('border-danger');
      if (feedback) {
        feedback.textContent = errors[fieldName];
      }
    } else {
      let field = form.querySelector(`[name="${fieldName}"]`);

      if (!field) {
        field = form.querySelector(`[name="${fieldName}[]"]`);
      }

      if (field) {
        field.classList.add('is-invalid');
        const feedback = field.parentElement.querySelector('.invalid-feedback');
        if (feedback) {
          feedback.textContent = errors[fieldName];
        }
      }
    }
  });
}

function addRowToActiveTable(data) {
  const tbody = document.querySelector('#activeAssignmentsTable tbody');
  const emptyRow = tbody.querySelector('td[colspan]');
  if (emptyRow) {
    emptyRow.parentElement.remove();
  }

  const subjectsDisplay =
    data.subject_count < 2
      ? `<span class="text-muted">${data.subjects}</span>`
      : `<button class="btn btn-sm btn-outline-secondary view-subjects-btn" data-subjects="${data.subjects}" data-teacher-name="${data.teacher_name}" data-section-name="${data.section_name}"> <i class="bi bi-book"></i> ${data.subject_count} subjects </button>`;

  const row = document.createElement('tr');
  row.dataset.rowKey =
    data.row_key ||
    `${data.teacher_id}_${data.section_id}_${data.school_year}_${data.semester}`;
  row.innerHTML = `<td>${data.teacher_name}</td> <td>${data.section_name}</td> <td>${data.year_level}</td> <td>${data.school_year}</td> <td><span class="badge bg-info">${data.semester}</span></td> <td>${subjectsDisplay}</td> <td> <button class="btn btn-sm btn-outline-primary me-1 btn-reassign" data-teacher-id="${data.teacher_id}" data-section-id="${data.section_id}" data-school-year="${data.school_year}" data-semester="${data.semester}" data-subject-ids="${data.subject_ids}" data-teacher-name="${data.teacher_name}" data-section-name="${data.section_name}"> <i class="bi bi-pencil"></i> reassign </button> <button class="btn btn-sm btn-outline-danger btn-remove" data-teacher-id="${data.teacher_id}" data-section-id="${data.section_id}" data-school-year="${data.school_year}" data-semester="${data.semester}" data-teacher-name="${data.teacher_name}" data-section-name="${data.section_name}"> <i class="bi bi-trash"></i> remove </button> </td>`;
  tbody.appendChild(row);
}

function addRowToInactiveTable(data) {
  const tbody = document.querySelector('#inactiveAssignmentsTable tbody');
  const emptyRow = tbody.querySelector('td[colspan]');
  if (emptyRow) {
    emptyRow.parentElement.remove();
  }

  const subjectsDisplay =
    data.subject_count < 2
      ? `<span class="text-muted">${data.subjects}</span>`
      : `<button class="btn btn-sm btn-outline-secondary view-subjects-btn" data-subjects="${data.subjects}" data-teacher-name="${data.teacher_name}" data-section-name="${data.section_name}"> <i class="bi bi-book"></i> ${data.subject_count} subjects </button>`;

  const row = document.createElement('tr');
  const key = `${data.teacher_id}_${data.section_id}_${data.school_year}_${data.semester}`;
  row.dataset.rowKey = key;

  row.innerHTML = `<td>${data.teacher_name}</td> <td>${data.section_name}</td> <td>${data.year_level}</td> <td>${data.school_year}</td> <td><span class="badge bg-secondary">${data.semester}</span></td> <td>${subjectsDisplay}</td> <td> <button class="btn btn-sm btn-outline-success btn-restore" data-teacher-id="${data.teacher_id}" data-section-id="${data.section_id}" data-school-year="${data.school_year}" data-semester="${data.semester}" data-teacher-name="${data.teacher_name}" data-section-name="${data.section_name}"> <i class="bi bi-arrow-clockwise"></i> restore </button> </td>`;
  tbody.appendChild(row);
}

function updateActiveRow(rowKey, data) {
  const row = document.querySelector(
    `#activeAssignmentsTable tr[data-row-key="${rowKey}"]`
  );
  if (row) {
    const subjectsDisplay =
      data.subject_count < 2
        ? `<span class="text-muted">${data.subjects}</span>`
        : `<button class="btn btn-sm btn-outline-secondary view-subjects-btn" data-subjects="${data.subjects}" data-teacher-name="${data.teacher_name}" data-section-name="${data.section_name}"> <i class="bi bi-book"></i> ${data.subject_count} subjects </button>`;

    row.children[5].innerHTML = subjectsDisplay;

    const reassignBtn = row.querySelector('.btn-reassign');
    if (reassignBtn) reassignBtn.dataset.subjectIds = data.subject_ids;
  }
}

function updateInactiveRow(rowKey, data) {
  const row = document.querySelector(
    `#inactiveAssignmentsTable tr[data-row-key="${rowKey}"]`
  );
  if (row) {
    const subjectsDisplay =
      data.subject_count < 2
        ? `<span class="text-muted">${data.subjects}</span>`
        : `<button class="btn btn-sm btn-outline-secondary view-subjects-btn" data-subjects="${data.subjects}" data-teacher-name="${data.teacher_name}" data-section-name="${data.section_name}"> <i class="bi bi-book"></i> ${data.subject_count} subjects </button>`;

    row.children[5].innerHTML = subjectsDisplay;
  }
}

function removeActiveRow(rowKey) {
  const row = document.querySelector(
    `#activeAssignmentsTable tr[data-row-key="${rowKey}"]`
  );
  if (row) {
    row.remove();
    const tbody = document.querySelector('#activeAssignmentsTable tbody');
    if (tbody.children.length === 0) {
      tbody.innerHTML = `<tr> <td colspan="7" class="text-center text-muted py-4"> <i class="bi bi-inbox fs-1 d-block mb-2"></i> no active assignments yet </td> </tr>`;
    }
  }
}

function removeInactiveRow(rowKey) {
  const row = document.querySelector(
    `#inactiveAssignmentsTable tr[data-row-key="${rowKey}"]`
  );
  if (row) {
    row.remove();
    const tbody = document.querySelector('#inactiveAssignmentsTable tbody');
    if (tbody.children.length === 0) {
      tbody.innerHTML = `<tr> <td colspan="7" class="text-center text-muted py-4"> <i class="bi bi-inbox fs-1 d-block mb-2"></i> no removed assignments </td> </tr>`;
    }
  }
}
