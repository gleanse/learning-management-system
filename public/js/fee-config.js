document.addEventListener('DOMContentLoaded', function () {
  const editModal = new bootstrap.Modal(
    document.getElementById('editFeeModal')
  );
  const saveFeeBtn = document.getElementById('saveFeeBtn');
  const modalFeeId = document.getElementById('modalFeeId');
  const modalTotal = document.getElementById('modalTotal');

  const tuitionInput = document.getElementById('modalTuitionFee');
  const miscInput = document.getElementById('modalMiscellaneous');
  const otherInput = document.getElementById('modalOtherFees');

  // open modal and populate with fetched fee data
  document.querySelectorAll('.edit-fee-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const feeId = this.dataset.feeId;
      openEditModal(feeId);
    });
  });

  [tuitionInput, miscInput, otherInput].forEach(function (input) {
    input.addEventListener('input', function () {
      const max = 100000;
      let val = parseFloat(this.value);

      if (val > max) {
        this.value = max; // Snap value back to 100,000
        this.classList.add('is-invalid');
        setTimeout(() => this.classList.remove('is-invalid'), 1000);
      }

      updateTotal(); // Recalculate totals
    });
  });

  function openEditModal(feeId) {
    clearModalErrors();

    fetch('index.php?page=ajax_get_fee&fee_id=' + feeId)
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        if (!data.success) {
          showToast('danger', data.message || 'Failed to load fee data.');
          return;
        }

        const fee = data.data;
        modalFeeId.value = fee.fee_id;

        document.getElementById('modalYearLevel').textContent = fee.school_year;
        document.getElementById('modalStrandCourse').textContent =
          fee.strand_course;
        tuitionInput.value = parseFloat(fee.tuition_fee).toFixed(2);
        miscInput.value = parseFloat(fee.miscellaneous).toFixed(2);
        otherInput.value = parseFloat(fee.other_fees).toFixed(2);

        updateTotal();
        editModal.show();
      })
      .catch(function () {
        showToast('danger', 'An error occurred while loading fee data.');
      });
  }

  // live total update on input
  [tuitionInput, miscInput, otherInput].forEach(function (input) {
    input.addEventListener('input', updateTotal);
  });

  function updateTotal() {
    const tuition = parseFloat(tuitionInput.value) || 0;
    const misc = parseFloat(miscInput.value) || 0;
    const other = parseFloat(otherInput.value) || 0;
    const total = tuition + misc + other;

    modalTotal.textContent =
      '₱' +
      total.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
  }

  // save fee changes
  saveFeeBtn.addEventListener('click', function () {
    clearModalErrors();

    const feeId = modalFeeId.value;
    const tuition = tuitionInput.value;
    const misc = miscInput.value;
    const other = otherInput.value;

    // client-side max validation
    const tuitionVal = parseFloat(tuitionInput.value) || 0;
    const miscVal = parseFloat(miscInput.value) || 0;
    const otherVal = parseFloat(otherInput.value) || 0;

    if (tuitionVal <= 0 || tuitionVal > 100000) {
      tuitionInput.classList.add('is-invalid');
      document.getElementById('errorTuitionFee').textContent =
        'Tuition fee must be between ₱0.01 and ₱100,000.';
      return;
    }
    if (miscVal > 100000) {
      showToast('danger', 'Miscellaneous fee cannot exceed ₱100,000.');
      return;
    }
    if (otherVal > 100000) {
      showToast('danger', 'Other fees cannot exceed ₱100,000.');
      return;
    }

    if (!feeId) {
      showToast('danger', 'Invalid fee ID.');
      return;
    }

    saveFeeBtn.disabled = true;
    saveFeeBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm"></span> Saving...';

    const formData = new FormData();
    formData.append('fee_id', feeId);
    formData.append('tuition_fee', tuition);
    formData.append('miscellaneous', misc);
    formData.append('other_fees', other);

    fetch('index.php?page=ajax_update_fee', {
      method: 'POST',
      body: formData,
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        if (data.success) {
          // update the row in the table without reload
          updateTableRow(data.data);
          editModal.hide();
          showToast('success', data.message);
        } else {
          if (data.errors) {
            showModalErrors(data.errors);
          } else {
            showToast('danger', data.message || 'Failed to save changes.');
          }
        }
      })
      .catch(function () {
        showToast('danger', 'An error occurred. Please try again.');
      })
      .finally(function () {
        saveFeeBtn.disabled = false;
        saveFeeBtn.innerHTML = '<i class="bi bi-save-fill"></i> Save Changes';
      });
  });

  // update the matching table row cells after successful save
  function updateTableRow(data) {
    const row = document.querySelector('tr[data-fee-id="' + data.fee_id + '"]');
    if (!row) return;

    row.querySelector('[data-field="tuition_fee"]').textContent =
      '₱' + data.tuition_fee;
    row.querySelector('[data-field="miscellaneous"]').textContent =
      '₱' + data.miscellaneous;
    row.querySelector('[data-field="other_fees"]').textContent =
      '₱' + data.other_fees;
    row.querySelector('.fee-total').textContent = '₱' + data.total;

    // brief highlight to confirm update
    row.classList.add('table-success');
    setTimeout(function () {
      row.classList.remove('table-success');
    }, 1500);
  }

  function showModalErrors(errors) {
    if (errors.tuition_fee) {
      tuitionInput.classList.add('is-invalid');
      document.getElementById('errorTuitionFee').textContent =
        errors.tuition_fee;
    }
  }

  function clearModalErrors() {
    [tuitionInput, miscInput, otherInput].forEach(function (el) {
      el.classList.remove('is-invalid');
    });
    document.getElementById('errorTuitionFee').textContent = '';
  }

  // toast helper — same pattern as academic-period.js
  function showToast(type, message) {
    const container = document.getElementById('toastContainer');
    const id = 'toast-' + Date.now();
    const icons = {
      success: 'bi-check-circle-fill',
      danger: 'bi-exclamation-triangle-fill',
    };
    const labels = { success: 'Success', danger: 'Error' };

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
      .addEventListener('hidden.bs.toast', function () {
        document.getElementById(id)?.remove();
      });
  }
});
