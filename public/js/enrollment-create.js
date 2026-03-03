document.addEventListener('DOMContentLoaded', function () {
  const totalSteps = 4;
  let currentStep = 1;

  const stepContents = document.querySelectorAll('.step-content');
  const wizardSteps = document.querySelectorAll('.wizard-step');
  const connectors = document.querySelectorAll('.wizard-connector');
  const backBtn = document.getElementById('backBtn');
  const nextBtn = document.getElementById('nextBtn');
  const submitBtn = document.getElementById('submitBtn');

  function goToStep(step) {
    stepContents.forEach((s) => s.classList.remove('active'));
    document.getElementById('step-' + step).classList.add('active');

    wizardSteps.forEach((s, i) => {
      s.classList.remove('active', 'completed');
      if (i + 1 < step) s.classList.add('completed');
      if (i + 1 === step) s.classList.add('active');
    });

    connectors.forEach((c, i) => {
      c.classList.toggle('completed', i + 1 < step);
    });

    backBtn.classList.toggle('d-none', step === 1);
    nextBtn.classList.toggle('d-none', step === totalSteps);
    submitBtn.classList.toggle('d-none', step !== totalSteps);

    currentStep = step;

    document
      .querySelector('.enrollment-card')
      .scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  nextBtn.addEventListener('click', function () {
    if (validateStep(currentStep)) goToStep(currentStep + 1);
  });

  backBtn.addEventListener('click', function () {
    goToStep(currentStep - 1);
  });

  function validateStep(step) {
    clearStepErrors(step);
    const errors = [];

    if (step === 1) {
      if (!getValue('first_name'))
        errors.push({ field: 'first_name', msg: 'First name is required' });
      if (!getValue('last_name'))
        errors.push({ field: 'last_name', msg: 'Last name is required' });
      if (!getValue('guardian_name'))
        errors.push({
          field: 'guardian_name',
          msg: 'Guardian name is required',
        });

      const guardianContact = getValue('guardian_contact');
      if (!guardianContact) {
        errors.push({
          field: 'guardian_contact',
          msg: 'Guardian contact is required',
        });
      } else if (!/^09\d{9}$/.test(guardianContact)) {
        errors.push({
          field: 'guardian_contact',
          msg: 'Guardian contact must be in format 09XXXXXXXXX (11 digits)',
        });
      }

      if (!getValue('gender'))
        errors.push({ field: 'gender', msg: 'Gender is required' });

      const email = getValue('email');
      if (!email) {
        errors.push({ field: 'email', msg: 'Email is required' });
      } else {
        // basic email format validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
          errors.push({
            field: 'email',
            msg: 'Please enter a valid email address',
          });
        }
      }

      // block next if email is duplicate
      if (emailIsDuplicate) {
        errors.push({
          field: 'email',
          msg: 'This email is already registered to another student',
        });
      }

      // lrn validation — numbers only, exactly 12 digits, only if senior high
      const lrn = getValue('lrn');
      if (getValue('education_level') === 'senior_high' || lrn) {
        if (lrn && !/^\d{12}$/.test(lrn)) {
          errors.push({
            field: 'lrn',
            msg: 'LRN must be exactly 12 digits, numbers only',
          });
        }
      }

      // contact number validation — PH format 09XXXXXXXXX
      const contact = getValue('contact_number');
      if (contact && !/^09\d{9}$/.test(contact)) {
        errors.push({
          field: 'contact_number',
          msg: 'Contact number must be in format 09XXXXXXXXX (11 digits)',
        });
      }

      const dob = getValue('date_of_birth');
      if (!dob) {
        errors.push({
          field: 'date_of_birth',
          msg: 'Date of birth is required',
        });
      } else {
        const today = new Date();
        const birthDate = new Date(dob);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (
          monthDiff < 0 ||
          (monthDiff === 0 && today.getDate() < birthDate.getDate())
        )
          age--;

        if (age < 10)
          errors.push({
            field: 'date_of_birth',
            msg: 'Student must be at least 10 years old to enroll',
          });
        if (age > 100)
          errors.push({
            field: 'date_of_birth',
            msg: 'Please enter a valid date of birth',
          });
      }
    }

    if (step === 2) {
      // check lrn here since education level is picked on step 2
      // but lrn field lives on step 1 — redirect back if missing
      if (getValue('education_level') === 'senior_high' && !getValue('lrn')) {
        showToast(
          'warning',
          'LRN is required for Senior High School. Please go back to Step 1 and fill it in.'
        );
        goToStep(1);
        showFieldError(
          'lrn',
          'LRN is required for Senior High School enrollment'
        );

        document.querySelector('.lrn-required-indicator').style.display = '';
        document.querySelector('.lrn-optional-badge').style.display = 'none';
        return false;
      }

      if (!getValue('education_level'))
        errors.push({
          field: 'education_level',
          msg: 'Education level is required',
        });
      if (!getValue('year_level'))
        errors.push({ field: 'year_level', msg: 'Grade level is required' });
      if (!getValue('strand_course'))
        errors.push({
          field: 'strand_course',
          msg: 'Strand or course is required',
        });

      // school_year and semester are now auto-set from active period — skip validation
    }

    if (step === 3) {
      const total =
        parseFloat(document.getElementById('totalAmountInput').value) || 0;
      const isVoucher =
        document.getElementById('shsVoucherInput').value === '1';

      if (total <= 0) {
        showToast(
          'warning',
          'Fee configuration is not set for this course. Please contact the administrator.'
        );
        return false;
      }

      if (
        !isVoucher &&
        (parseFloat(getValue('initial_amount_paid')) || 0) <= 0
      ) {
        errors.push({
          field: 'initial_amount_paid',
          msg: 'Initial payment is required to complete enrollment',
        });
      }
    }

    if (errors.length > 0) {
      errors.forEach((e) => showFieldError(e.field, e.msg));
      showToast(
        'warning',
        `Please review ${errors.length} highlighted field${
          errors.length > 1 ? 's' : ''
        } before continuing.`
      );
      return false;
    }

    return true;
  }

  function getValue(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : '';
  }

  function showFieldError(fieldId, msg) {
    const el = document.getElementById(fieldId);
    if (!el) return;
    el.classList.add('is-invalid');

    if (
      !el.nextElementSibling ||
      !el.nextElementSibling.classList.contains('invalid-feedback')
    ) {
      const div = document.createElement('div');
      div.className = 'invalid-feedback';
      div.textContent = msg;
      el.parentNode.insertBefore(div, el.nextSibling);
    }
  }

  function clearStepErrors(step) {
    document
      .getElementById('step-' + step)
      .querySelectorAll('.is-invalid')
      .forEach((el) => {
        el.classList.remove('is-invalid');
      });
    document
      .getElementById('step-' + step)
      .querySelectorAll('.invalid-feedback')
      .forEach((el) => {
        if (!el.dataset.phpRendered) el.remove();
      });
  }

  document.querySelectorAll('.invalid-feedback').forEach((el) => {
    el.dataset.phpRendered = '1';
  });

  // duplicate name check — fires on blur of last_name, or middle_name if filled
  // soft warning only — registrar can still proceed
  const firstNameInput = document.getElementById('first_name');
  const middleNameInput = document.getElementById('middle_name');
  const lastNameInput = document.getElementById('last_name');
  const duplicateAlert = document.getElementById('duplicateNameAlert');

  function runDuplicateCheck() {
    const first = firstNameInput.value.trim();
    const last = lastNameInput.value.trim();
    const middle = middleNameInput.value.trim();

    if (!first || !last) return;

    const params = new URLSearchParams({
      first_name: first,
      last_name: last,
      middle_name: middle,
    });

    fetch(`${ENROLLMENT_DATA.ajaxUrls.checkDuplicateName}&${params}`)
      .then((r) => r.json())
      .then((res) => {
        if (!res.success || !res.has_match) {
          hideDuplicateAlert();
          return;
        }
        showDuplicateAlert(res.matches);
      })
      .catch(() => {});
  }

  function showDuplicateAlert(matches) {
    const levelLabels = { senior_high: 'Senior High', college: 'College' };

    const rows = matches
      .map((m) => {
        const level = levelLabels[m.education_level] || m.education_level;
        const name = [m.first_name, m.middle_name, m.last_name]
          .filter(Boolean)
          .join(' ');
        return `
                <div class="duplicate-match-row">
                    <span class="duplicate-match-number">${m.student_number}</span>
                    <span class="duplicate-match-name">${name}</span>
                    <span class="duplicate-match-meta">${level} &bull; ${m.strand_course} &bull; ${m.year_level}</span>
                    <span class="duplicate-match-status duplicate-status-${m.enrollment_status}">${m.enrollment_status}</span>
                </div>
            `;
      })
      .join('');

    duplicateAlert.innerHTML = `
            <div class="duplicate-alert-header">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Possible duplicate — a student with this name already exists. Verify this is a different person before continuing.</span>
            </div>
            <div class="duplicate-match-list">${rows}</div>
        `;
    duplicateAlert.classList.remove('d-none');
  }

  function hideDuplicateAlert() {
    duplicateAlert.classList.add('d-none');
    duplicateAlert.innerHTML = '';
  }

  lastNameInput.addEventListener('blur', runDuplicateCheck);

  middleNameInput.addEventListener('blur', function () {
    if (this.value.trim()) runDuplicateCheck();
  });

  firstNameInput.addEventListener('input', function () {
    if (!this.value.trim()) hideDuplicateAlert();
  });

  // email duplicate check
  const emailInput = document.getElementById('email');
  let emailIsDuplicate = false;
  let emailCheckTimeout;

  emailInput.addEventListener('input', function () {
    emailIsDuplicate = false;
    clearTimeout(emailCheckTimeout);
    const email = this.value.trim();

    // clear any existing email error first
    const existingError = document.querySelector('.email-exists-error');
    if (existingError) existingError.remove();
    this.classList.remove('is-invalid');

    if (!email) return;

    // wait for user to stop typing
    emailCheckTimeout = setTimeout(() => {
      checkEmailExists(email);
    }, 500);
  });

  emailInput.addEventListener('blur', function () {
    const email = this.value.trim();
    if (!email) return;

    // check immediately on blur
    checkEmailExists(email);
  });

  function checkEmailExists(email) {
    // don't check if email format is invalid
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) return;

    fetch(
      `${ENROLLMENT_DATA.ajaxUrls.checkEmail}&email=${encodeURIComponent(
        email
      )}`
    )
      .then((r) => r.json())
      .then((res) => {
        if (res.exists) {
          // show error message
          emailIsDuplicate = true;
          const errorDiv = document.createElement('div');
          errorDiv.className = 'invalid-feedback email-exists-error d-block';
          errorDiv.textContent =
            'This email is already registered to another student';

          emailInput.classList.add('is-invalid');

          // remove existing error if any, then add new one
          const parent = emailInput.parentNode;
          const oldError = parent.querySelector('.email-exists-error');
          if (oldError) oldError.remove();
          parent.appendChild(errorDiv);
        } else {
          emailIsDuplicate = false;
          emailInput.classList.remove('is-invalid');
          const oldError = emailInput.parentNode.querySelector(
            '.email-exists-error'
          );
          if (oldError) oldError.remove();
        }
      })
      .catch(() => {});
  }

  const educationLevelSelect = document.getElementById('education_level');
  const yearLevelSelect = document.getElementById('year_level');
  const strandCourseSelect = document.getElementById('strand_course');

  const yearLevelOptions = {
    senior_high: ['Grade 11', 'Grade 12'],
    college: ['1st Year', '2nd Year', '3rd Year', '4th Year'],
  };

  const strandOptions = {
    senior_high: ['STEM', 'ABM', 'HUMSS', 'GAS', 'ICT'],
    college: ['BSIT', 'BSHM', 'BSOA', 'ACT'],
  };

  educationLevelSelect.addEventListener('change', function () {
    const level = this.value;
    populateSelect(
      yearLevelSelect,
      yearLevelOptions[level] || [],
      'Select grade level'
    );
    populateSelect(
      strandCourseSelect,
      strandOptions[level] || [],
      'Select strand or course'
    );
    populateSelect(document.getElementById('section_id'), [], 'To be assigned');

    const lrnRequired = document.querySelector('.lrn-required-indicator');
    const lrnOptional = document.querySelector('.lrn-optional-badge');
    if (level === 'senior_high') {
      lrnRequired.style.display = '';
      lrnOptional.style.display = 'none';
    } else {
      lrnRequired.style.display = 'none';
      lrnOptional.style.display = '';
    }
  });

  strandCourseSelect.addEventListener('change', function () {
    const strand = this.value;
    const level = educationLevelSelect.value;

    // ACT is only 2 years reset year level to 1st and 2nd only
    if (strand === 'ACT') {
      populateSelect(
        yearLevelSelect,
        ['1st Year', '2nd Year'],
        'Select grade level'
      );
    } else if (level === 'college') {
      // restore full college year levels if switching away from ACT
      populateSelect(
        yearLevelSelect,
        yearLevelOptions['college'],
        'Select grade level'
      );
    }

    fetchFees();
  });
  yearLevelSelect.addEventListener('change', fetchFees);

  function populateSelect(selectEl, options, placeholder) {
    selectEl.innerHTML = `<option value="">${placeholder}</option>`;
    options.forEach((opt) => {
      const el = document.createElement('option');
      el.value = opt;
      el.textContent = opt;
      selectEl.appendChild(el);
    });
  }

  // school_year is now a hidden input — no change listener needed
  [yearLevelSelect, strandCourseSelect].forEach((el) => {
    el.addEventListener('change', fetchSections);
  });

  function fetchSections() {
    const educationLevel = educationLevelSelect.value;
    const yearLevel = yearLevelSelect.value;
    const strandCourse = strandCourseSelect.value;
    const schoolYear = document.getElementById('school_year').value;

    if (!educationLevel || !yearLevel || !strandCourse || !schoolYear) return;

    const params = new URLSearchParams({
      education_level: educationLevel,
      year_level: yearLevel,
      strand_course: strandCourse,
      // school_year still sent for reference but controller uses active period
    });

    fetch(`${ENROLLMENT_DATA.ajaxUrls.getSections}&${params}`)
      .then((r) => r.json())
      .then((res) => {
        if (!res.success) return;
        const sectionSelect = document.getElementById('section_id');
        sectionSelect.innerHTML = '<option value="">To be assigned</option>';
        res.data.forEach((sec) => {
          const available = sec.max_capacity
            ? `${sec.enrolled_count}/${sec.max_capacity}`
            : sec.enrolled_count;
          const opt = document.createElement('option');
          opt.value = sec.section_id;
          opt.textContent = `${sec.section_name} (${available})`;
          sectionSelect.appendChild(opt);
        });
      })
      .catch(() => showToast('danger', 'Failed to load sections.'));
  }

  function fetchFees() {
    const yearLevel = yearLevelSelect.value;
    const educationLevel = educationLevelSelect.value;
    const strandCourse = strandCourseSelect.value;

    if (!yearLevel || !educationLevel || !strandCourse) return;

    const params = new URLSearchParams({
      year_level: yearLevel,
      education_level: educationLevel,
      strand_course: strandCourse,
    });

    fetch(`${ENROLLMENT_DATA.ajaxUrls.getFees}&${params}`)
      .then((r) => r.json())
      .then((res) => {
        if (!res.success) return;
        const c = res.data;
        document.getElementById('feeTuition').textContent =
          '₱' + formatAmount(c.tuition_fee);
        document.getElementById('feeMisc').textContent =
          '₱' + formatAmount(c.miscellaneous);
        document.getElementById('feeOther').textContent =
          '₱' + formatAmount(c.other_fees);
        document.getElementById('feeTotal').textContent =
          '₱' + formatAmount(c.total);
        document.getElementById('totalAmountInput').value = c.total;
        updatePaymentSummary();
      })
      .catch(() => showToast('danger', 'Failed to load fee configuration.'));
  }

  document
    .getElementById('initial_amount_paid')
    .addEventListener('input', updatePaymentSummary);

  function updatePaymentSummary() {
    const total =
      parseFloat(document.getElementById('totalAmountInput').value) || 0;
    const initial =
      parseFloat(document.getElementById('initial_amount_paid').value) || 0;
    const balance = Math.max(total - initial, 0);

    document.getElementById('summaryTotal').textContent =
      '₱' + formatAmount(total);
    document.getElementById('summaryInitial').textContent =
      '₱' + formatAmount(initial);
    document.getElementById('summaryBalance').textContent =
      '₱' + formatAmount(balance);
  }

  updatePaymentSummary();

  const docCheckboxes = document.querySelectorAll(
    '.document-item input[type="checkbox"]'
  );
  const docSummary = document.getElementById('docSummaryText');

  docCheckboxes.forEach((cb) => cb.addEventListener('change', updateDocCount));

  function updateDocCount() {
    const checked = document.querySelectorAll(
      '.document-item input:checked'
    ).length;
    docSummary.textContent = `${checked} of ${docCheckboxes.length} documents submitted`;
  }

  updateDocCount();

  document
    .getElementById('saveDraftBtn')
    .addEventListener('click', function () {
      const formData = new FormData(document.getElementById('enrollmentForm'));
      fetch(ENROLLMENT_DATA.ajaxUrls.saveDraft, {
        method: 'POST',
        body: formData,
      })
        .then((r) => r.json())
        .then((res) =>
          showToast(res.success ? 'success' : 'danger', res.message)
        )
        .catch(() => showToast('danger', 'Failed to save draft.'));
    });

  document
    .getElementById('clearFormBtn')
    .addEventListener('click', function () {
      if (
        !confirm(
          'Are you sure you want to clear the form? All unsaved data will be lost.'
        )
      )
        return;
      document.getElementById('enrollmentForm').reset();
      populateSelect(yearLevelSelect, [], 'Select grade level');
      populateSelect(strandCourseSelect, [], 'Select strand or course');
      populateSelect(
        document.getElementById('section_id'),
        [],
        'To be assigned'
      );
      hideDuplicateAlert();
      goToStep(1);
      updatePaymentSummary();
      updateDocCount();
    });

  document
    .getElementById('cancelEnrollmentBtn')
    .addEventListener('click', function () {
      // check if form has any data filled
      const form = document.getElementById('enrollmentForm');
      const inputs = form.querySelectorAll(
        'input[type="text"], input[type="email"], input[type="date"], input[type="number"], select, textarea'
      );

      const hasData = Array.from(inputs).some((el) => {
        // skip hidden, readonly, and always-populated fields
        if (el.type === 'hidden' || el.readOnly) return false;
        return el.value.trim() !== '' && el.value !== '0';
      });

      if (hasData) {
        if (
          !confirm(
            'Are you sure you want to cancel this enrollment?\n\nAll entered data will be lost and cannot be recovered.'
          )
        ) {
          return;
        }
      }

      // clear draft and redirect to dashboard
      fetch(ENROLLMENT_DATA.ajaxUrls.saveDraft, {
        method: 'POST',
        body: new FormData(), // empty post clears draft
      }).finally(() => {
        window.location.href = 'index.php?page=registrar_dashboard';
      });
    });

  goToStep(1);

  const savedLevel = educationLevelSelect.value;
  if (savedLevel) {
    educationLevelSelect.dispatchEvent(new Event('change'));

    setTimeout(() => {
      const savedYear = ENROLLMENT_DATA.formData.year_level || '';
      const savedStrand = ENROLLMENT_DATA.formData.strand_course || '';

      if (savedYear) yearLevelSelect.value = savedYear;
      if (savedStrand) strandCourseSelect.value = savedStrand;

      if (savedYear && savedStrand) {
        fetchSections();
        fetchFees();
      }
    }, 0);
  }

  window.showToast = function (type, message) {
    const container = document.getElementById('toastContainer');
    const id = 'toast-' + Date.now();
    const icons = {
      success: 'bi-check-circle-fill',
      danger: 'bi-exclamation-triangle-fill',
      warning: 'bi-exclamation-triangle-fill',
    };
    const labels = { success: 'Success', danger: 'Error', warning: 'Warning' };

    container.insertAdjacentHTML(
      'beforeend',
      `
            <div id="${id}" class="toast toast-${type}" role="alert">
                <div class="toast-header">
                    <i class="bi ${icons[type]} me-2"></i>
                    <strong>${labels[type]}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `
    );

    const toastEl = new bootstrap.Toast(document.getElementById(id), {
      delay: 4000,
    });
    toastEl.show();
    document
      .getElementById(id)
      .addEventListener('hidden.bs.toast', () =>
        document.getElementById(id)?.remove()
      );
  };

  function formatAmount(amount) {
    return parseFloat(amount).toLocaleString('en-PH', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  }
});
