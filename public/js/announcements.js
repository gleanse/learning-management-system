document.addEventListener('DOMContentLoaded', function () {
  const titleInput = document.getElementById('announcementTitle');
  const contentInput = document.getElementById('announcementContent');
  const targetSelect = document.getElementById('targetType');
  const editIdInput = document.getElementById('editAnnouncementId');
  const editStatusInput = document.getElementById('editAnnouncementStatus');

  function getTargetData() {
    const selected = targetSelect.options[targetSelect.selectedIndex];
    return {
      target_type: selected.value,
      target_value: selected.dataset.value || '',
    };
  }

  // ─── STATUS FILTER ───────────────────────────────────────────────────────

  const statusFilter = document.getElementById('statusFilter');
  if (statusFilter) {
    statusFilter.addEventListener('change', function () {
      const url = new URL(window.location.href);
      if (this.value) {
        url.searchParams.set('status', this.value);
      } else {
        url.searchParams.delete('status');
      }
      url.searchParams.delete('p');
      window.location.href = url.toString();
    });
  }

  // ─── CLEAR FORM ──────────────────────────────────────────────────────────

  document
    .getElementById('clearFormBtn')
    ?.addEventListener('click', function () {
      titleInput.value = '';
      contentInput.value = '';
      targetSelect.selectedIndex = 0;
      editIdInput.value = '';
      editStatusInput.value = '';
      titleInput.classList.remove('is-invalid');
      contentInput.classList.remove('is-invalid');
    });

  // ─── SAVE DRAFT ──────────────────────────────────────────────────────────

  document
    .getElementById('saveDraftBtn')
    ?.addEventListener('click', async function () {
      if (!validateForm()) return;

      const btn = this;
      btn.disabled = true;
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

      const { target_type, target_value } = getTargetData();
      const fd = buildFormData(target_type, target_value);

      try {
        const res = await fetch('index.php?page=ajax_announcement_save_draft', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: fd,
        });
        const data = await res.json();

        if (data.success) {
          showToast('success', data.message);
          editIdInput.value = data.announcement_id;
          setTimeout(() => window.location.reload(), 800);
        } else {
          showToast('danger', data.message || 'Failed to save draft.');
        }
      } catch (e) {
        showToast('danger', 'An unexpected error occurred.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-floppy-fill"></i> Save Draft';
      }
    });

  // ─── PUBLISH BUTTON — open confirm modal ─────────────────────────────────

  document.getElementById('publishBtn')?.addEventListener('click', function () {
    if (!validateForm()) return;

    const isPublished = editStatusInput.value === 'published';
    const selected = targetSelect.options[targetSelect.selectedIndex];
    const confirmBtn = document.getElementById('confirmPublishBtn');

    document.getElementById('publishTargetLabel').textContent = selected.text;

    document.getElementById('publishConfirmNote').textContent = isPublished
      ? 'Recipients will be updated based on the new target audience.'
      : 'All matched users will receive a bell notification.';

    confirmBtn.innerHTML = isPublished
      ? '<i class="bi bi-arrow-repeat"></i> Confirm Update'
      : '<i class="bi bi-send-fill"></i> Confirm Publish';

    new bootstrap.Modal(document.getElementById('publishConfirmModal')).show();
  });

  // ─── CONFIRM PUBLISH / UPDATE PUBLISHED ──────────────────────────────────

  document
    .getElementById('confirmPublishBtn')
    ?.addEventListener('click', async function () {
      const btn = this;
      const publishModal = bootstrap.Modal.getInstance(
        document.getElementById('publishConfirmModal')
      );
      const isPublished = editStatusInput.value === 'published';

      btn.disabled = true;
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

      const { target_type, target_value } = getTargetData();
      const fd = buildFormData(target_type, target_value);

      const endpoint = isPublished
        ? 'index.php?page=ajax_announcement_update_published'
        : 'index.php?page=ajax_announcement_publish';

      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: fd,
        });
        const data = await res.json();

        if (data.success) {
          publishModal.hide();
          showToast('success', data.message);
          setTimeout(() => window.location.reload(), 800);
        } else {
          publishModal.hide();
          showToast('danger', data.message || 'Failed.');
        }
      } catch (e) {
        showToast('danger', 'An unexpected error occurred.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-fill"></i> Confirm Publish';
      }
    });

  // ─── EDIT BUTTON (draft + published) ─────────────────────────────────────

  document.addEventListener('click', async function (e) {
    const editBtn = e.target.closest('.edit-ann-btn');
    if (!editBtn) return;

    const id = editBtn.dataset.id;

    try {
      const res = await fetch(
        `index.php?page=ajax_get_announcement&announcement_id=${id}`
      );
      const data = await res.json();

      if (data.success) {
        const ann = data.data;
        titleInput.value = ann.title;
        contentInput.value = ann.content;
        editIdInput.value = ann.announcement_id;
        editStatusInput.value = ann.status;

        // match select option by type + value
        for (let i = 0; i < targetSelect.options.length; i++) {
          const opt = targetSelect.options[i];
          if (
            opt.value === ann.target_type &&
            (opt.dataset.value || '') === (ann.target_value || '')
          ) {
            targetSelect.selectedIndex = i;
            break;
          }
        }

        titleInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        titleInput.focus();
      }
    } catch (e) {
      showToast('danger', 'Failed to load announcement.');
    }
  });

  // ─── DELETE DRAFT ─────────────────────────────────────────────────────────

  let deleteDraftPendingId = null;

  document.addEventListener('click', function (e) {
    const deleteBtn = e.target.closest('.delete-draft-btn');
    if (!deleteBtn) return;
    deleteDraftPendingId = deleteBtn.dataset.id;
    new bootstrap.Modal(document.getElementById('deleteDraftModal')).show();
  });

  document
    .getElementById('confirmDeleteDraftBtn')
    ?.addEventListener('click', async function () {
      if (!deleteDraftPendingId) return;

      const btn = this;
      const deleteModal = bootstrap.Modal.getInstance(
        document.getElementById('deleteDraftModal')
      );

      btn.disabled = true;
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

      const fd = new FormData();
      fd.append('announcement_id', deleteDraftPendingId);

      try {
        const res = await fetch(
          'index.php?page=ajax_announcement_delete_draft',
          {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
          }
        );
        const data = await res.json();

        if (data.success) {
          deleteModal.hide();
          showToast('success', data.message);
          const row = document.querySelector(
            `.announcement-item[data-id="${deleteDraftPendingId}"]`
          );
          if (row) row.remove();
          deleteDraftPendingId = null;
        } else {
          showToast('danger', data.message || 'Failed to delete.');
        }
      } catch (e) {
        showToast('danger', 'An unexpected error occurred.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash-fill"></i> Delete';
      }
    });

  // ─── DELETE PUBLISHED ─────────────────────────────────────────────────────

  let deletePublishedPendingId = null;

  document.addEventListener('click', function (e) {
    const deleteBtn = e.target.closest('.delete-published-btn');
    if (!deleteBtn) return;
    deletePublishedPendingId = deleteBtn.dataset.id;
    new bootstrap.Modal(document.getElementById('deletePublishedModal')).show();
  });

  document
    .getElementById('confirmDeletePublishedBtn')
    ?.addEventListener('click', async function () {
      if (!deletePublishedPendingId) return;

      const btn = this;
      const deleteModal = bootstrap.Modal.getInstance(
        document.getElementById('deletePublishedModal')
      );

      btn.disabled = true;
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

      const fd = new FormData();
      fd.append('announcement_id', deletePublishedPendingId);

      try {
        const res = await fetch(
          'index.php?page=ajax_announcement_delete_published',
          {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
          }
        );
        const data = await res.json();

        if (data.success) {
          deleteModal.hide();
          showToast('success', data.message);
          const row = document.querySelector(
            `.announcement-item[data-id="${deletePublishedPendingId}"]`
          );
          if (row) row.remove();
          deletePublishedPendingId = null;
        } else {
          showToast('danger', data.message || 'Failed to delete.');
        }
      } catch (e) {
        showToast('danger', 'An unexpected error occurred.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash-fill"></i> Delete';
      }
    });

  // ─── HELPERS ─────────────────────────────────────────────────────────────

  function validateForm() {
    let valid = true;
    if (!titleInput.value.trim()) {
      titleInput.classList.add('is-invalid');
      valid = false;
    } else {
      titleInput.classList.remove('is-invalid');
    }
    if (!contentInput.value.trim()) {
      contentInput.classList.add('is-invalid');
      valid = false;
    } else {
      contentInput.classList.remove('is-invalid');
    }
    if (!valid) showToast('danger', 'Please fill in the title and content.');
    return valid;
  }

  function buildFormData(target_type, target_value) {
    const fd = new FormData();
    fd.append('title', titleInput.value.trim());
    fd.append('content', contentInput.value.trim());
    fd.append('target_type', target_type);
    fd.append('target_value', target_value);
    if (editIdInput.value) fd.append('announcement_id', editIdInput.value);
    return fd;
  }

  function showToast(type, message) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const id = 'toast-' + Date.now();
    const html = `
        <div id="${id}" class="toast align-items-center text-bg-${type} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`;

    container.insertAdjacentHTML('beforeend', html);
    const toastEl = document.getElementById(id);
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  }
});
