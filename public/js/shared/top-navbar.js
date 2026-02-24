document.addEventListener('DOMContentLoaded', function () {
  // ─── DROPDOWN TOGGLE HELPERS ─────────────────────────────────────────

  function openDropdown(dropdown) {
    dropdown.style.display = 'block';
  }

  function closeDropdown(dropdown) {
    dropdown.style.display = 'none';
  }

  function closeAllDropdowns() {
    if (bellDropdown) closeDropdown(bellDropdown);
    if (userDropdown) closeDropdown(userDropdown);
  }

  document.addEventListener('click', function (e) {
    if (!e.target.closest('#bellBtn') && !e.target.closest('#bellDropdown')) {
      if (bellDropdown) closeDropdown(bellDropdown);
    }
    if (
      !e.target.closest('#userAvatarBtn') &&
      !e.target.closest('#userDropdown')
    ) {
      if (userDropdown) closeDropdown(userDropdown);
    }
  });

  // ─── BELL ────────────────────────────────────────────────────────────

  const bellBtn = document.getElementById('bellBtn');
  const bellDropdown = document.getElementById('bellDropdown');
  const bellBadge = document.getElementById('bellBadge');
  const bellNotifList = document.getElementById('bellNotifList');
  const bellEmptyState = document.getElementById('bellEmptyState');
  const markAllReadBtn = document.getElementById('markAllReadBtn');

  if (bellBtn) {
    bellBtn.addEventListener('click', function (e) {
      e.stopPropagation();

      // use modal on mobile, dropdown on desktop
      if (window.innerWidth < 768) {
        fetchRecentNotifications();
        new bootstrap.Modal(document.getElementById('bellModal')).show();
        return;
      }

      const isOpen = bellDropdown.style.display !== 'none';
      closeAllDropdowns();
      if (!isOpen) {
        openDropdown(bellDropdown);
        fetchRecentNotifications();
      }
    });
  }

  const bellModalViewAllBtn = document.getElementById('bellModalViewAllBtn');

  if (bellModalViewAllBtn) {
    bellModalViewAllBtn.addEventListener('click', function (e) {
      e.preventDefault();
      bootstrap.Modal.getInstance(document.getElementById('bellModal'))?.hide();
      document
        .getElementById('bellModal')
        .addEventListener('hidden.bs.modal', function handler() {
          document
            .getElementById('bellModal')
            .removeEventListener('hidden.bs.modal', handler);
          fetchAllAnnouncements();
          new bootstrap.Modal(document.getElementById('annListModal')).show();
        });
    });
  }

  function fetchUnreadCount() {
    fetch('index.php?page=ajax_announcement_unread_count')
      .then((r) => r.json())
      .then((data) => {
        const count = data.count || 0;
        if (count > 0) {
          bellBadge.textContent = count > 99 ? '99+' : count;
          bellBadge.style.display = 'flex';
        } else {
          bellBadge.style.display = 'none';
        }
      })
      .catch(() => {});
  }

  function fetchRecentNotifications() {
    // target correct container based on screen size
    const isMobile = window.innerWidth < 768;
    const listEl = isMobile
      ? document.getElementById('bellNotifListModal')
      : bellNotifList;
    const emptyEl = isMobile
      ? document.getElementById('bellEmptyStateModal')
      : bellEmptyState;

    fetch('index.php?page=ajax_announcement_recent')
      .then((r) => r.json())
      .then((data) => {
        if (!data.success || !data.data.length) {
          listEl.innerHTML = '';
          listEl.appendChild(emptyEl);
          emptyEl.style.display = 'flex';
          return;
        }

        listEl.innerHTML = data.data
          .map((item) => {
            const date = new Date(item.published_at).toLocaleDateString(
              'en-US',
              { month: 'short', day: 'numeric', year: 'numeric' }
            );
            const preview =
              item.content.length > 90
                ? item.content.substring(0, 90) + '…'
                : item.content;
            const unread = item.is_read == 0;

            return `
                <div class="bell-item ${
                  unread ? 'bell-item-unread' : ''
                }" data-id="${item.announcement_id}"
                     data-title="${escapeAttr(item.title)}"
                     data-content="${escapeAttr(item.content)}"
                     data-date="${escapeAttr(date)}"
                     data-author="${escapeAttr(
                       item.author_first + ' ' + item.author_last
                     )}">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <span class="bell-item-title">${escapeHtml(
                          item.title
                        )}</span>
                        ${
                          unread
                            ? '<span class="bell-item-dot flex-shrink-0 mt-1"></span>'
                            : ''
                        }
                    </div>
                    <p class="bell-item-preview">${escapeHtml(preview)}</p>
                    <div class="bell-item-meta">
                        <i class="bi bi-clock"></i>
                        <span>${date}</span>
                        <span>&middot;</span>
                        <span>${escapeHtml(
                          item.author_first + ' ' + item.author_last
                        )}</span>
                    </div>
                </div>`;
          })
          .join('');

        listEl.querySelectorAll('.bell-item').forEach((el) => {
          el.addEventListener('click', function () {
            // close whichever is open
            if (isMobile) {
              bootstrap.Modal.getInstance(
                document.getElementById('bellModal')
              )?.hide();
            } else {
              closeDropdown(bellDropdown);
            }
            openDetailModal(
              {
                id: this.dataset.id,
                title: this.dataset.title,
                content: this.dataset.content,
                date: this.dataset.date,
                author: this.dataset.author,
              },
              this
            );
          });
        });
      })
      .catch(() => {});
  }

  if (markAllReadBtn) {
    markAllReadBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      fetch('index.php?page=ajax_announcement_mark_all_read', {
        method: 'POST',
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) {
            bellBadge.style.display = 'none';
            bellNotifList
              .querySelectorAll('.bell-item-unread')
              .forEach((el) => {
                el.classList.remove('bell-item-unread');
                const dot = el.querySelector('.bell-item-dot');
                if (dot) dot.remove();
              });
          }
        })
        .catch(() => {});
    });
  }

  // ─── VIEW ALL ANNOUNCEMENTS MODAL ────────────────────────────────────

  const annListModalEl = document.getElementById('annListModal');
  const annListBody = document.getElementById('annListBody');

  const viewAllLink = document.querySelector('.bell-dropdown-footer a');
  if (viewAllLink && annListModalEl) {
    viewAllLink.addEventListener('click', function (e) {
      e.preventDefault();
      closeDropdown(bellDropdown);
      fetchAllAnnouncements();
      new bootstrap.Modal(annListModalEl).show();
    });
  }

  function fetchAllAnnouncements() {
    annListBody.innerHTML = `
      <div class="d-flex justify-content-center align-items-center py-5">
        <span class="spinner-border spinner-border-sm me-2"></span> Loading...
      </div>`;

    fetch('index.php?page=ajax_announcement_recent&limit=100')
      .then((r) => r.json())
      .then((data) => {
        if (!data.success || !data.data.length) {
          annListBody.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
              <i class="bi bi-megaphone fs-1 mb-2 opacity-50"></i>
              <p class="fw-semibold mb-0">no announcements yet</p>
            </div>`;
          return;
        }

        annListBody.innerHTML = data.data
          .map((item) => {
            const date = new Date(item.published_at).toLocaleDateString(
              'en-US',
              { month: 'short', day: 'numeric', year: 'numeric' }
            );
            const preview =
              item.content.length > 120
                ? item.content.substring(0, 120) + '…'
                : item.content;
            const unread = item.is_read == 0;

            return `
            <div class="ann-list-item ${unread ? 'ann-list-item-unread' : ''}"
                 data-id="${item.announcement_id}"
                 data-title="${escapeAttr(item.title)}"
                 data-content="${escapeAttr(item.content)}"
                 data-date="${escapeAttr(date)}"
                 data-author="${escapeAttr(
                   item.author_first + ' ' + item.author_last
                 )}">
              <div class="d-flex align-items-start justify-content-between gap-2">
                <p class="ann-list-item-title mb-0">${escapeHtml(
                  item.title
                )}</p>
                ${
                  unread
                    ? '<span class="ann-list-unread-dot mt-1 flex-shrink-0"></span>'
                    : ''
                }
              </div>
              <p class="ann-list-item-preview mt-1">${escapeHtml(preview)}</p>
              <div class="ann-list-item-meta">
                <i class="bi bi-person-fill"></i>
                <span>${escapeHtml(
                  item.author_first + ' ' + item.author_last
                )}</span>
                <span>&middot;</span>
                <i class="bi bi-clock"></i>
                <span>${date}</span>
              </div>
            </div>
          `;
          })
          .join('');

        annListBody.querySelectorAll('.ann-list-item').forEach((el) => {
          el.addEventListener('click', function () {
            bootstrap.Modal.getInstance(annListModalEl)?.hide();
            annListModalEl.addEventListener(
              'hidden.bs.modal',
              function handler() {
                annListModalEl.removeEventListener('hidden.bs.modal', handler);
                openDetailModal(
                  {
                    id: el.dataset.id,
                    title: el.dataset.title,
                    content: el.dataset.content,
                    date: el.dataset.date,
                    author: el.dataset.author,
                  },
                  el
                );
              }
            );
          });
        });
      })
      .catch(() => {});
  }

  // ─── DETAIL MODAL ────────────────────────────────────────────────────

  const annDetailModalEl = document.getElementById('annDetailModal');
  const annDetailTitle = document.getElementById('annDetailTitle');
  const annDetailMeta = document.getElementById('annDetailMeta');
  const annDetailContent = document.getElementById('annDetailContent');

  function openDetailModal(item, el) {
    if (!annDetailModalEl) return;

    annDetailTitle.textContent = item.title;
    annDetailContent.textContent = item.content;
    annDetailMeta.innerHTML = `
      <span><i class="bi bi-person-fill"></i>${escapeHtml(item.author)}</span>
      <span><i class="bi bi-clock"></i>${escapeHtml(item.date)}</span>
    `;

    new bootstrap.Modal(annDetailModalEl).show();

    if (
      el &&
      (el.classList.contains('bell-item-unread') ||
        el.classList.contains('ann-list-item-unread'))
    ) {
      fetch('index.php?page=ajax_announcement_mark_read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'announcement_id=' + item.id,
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) {
            el.classList.remove('bell-item-unread', 'ann-list-item-unread');
            const dot = el.querySelector(
              '.bell-item-dot, .ann-list-unread-dot'
            );
            if (dot) dot.remove();
            const count = data.unread_count || 0;
            if (count > 0) {
              bellBadge.textContent = count > 99 ? '99+' : count;
              bellBadge.style.display = 'flex';
            } else {
              bellBadge.style.display = 'none';
            }
          }
        })
        .catch(() => {});
    }
  }

  // ─── USER AVATAR DROPDOWN ────────────────────────────────────────────

  const userAvatarBtn = document.getElementById('userAvatarBtn');
  const userDropdown = document.getElementById('userDropdown');

  if (userAvatarBtn) {
    userAvatarBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      const isOpen = userDropdown.style.display !== 'none';
      closeAllDropdowns();
      if (!isOpen) openDropdown(userDropdown);
    });
  }

  // ─── CHANGE PASSWORD ─────────────────────────────────────────────────

  const changePasswordNavBtn = document.getElementById('changePasswordNavBtn');
  const cpSubmitBtn = document.getElementById('cpSubmitBtn');
  const cpAlert = document.getElementById('changePasswordAlert');
  const cpOldPassword = document.getElementById('cpOldPassword');
  const cpNewPassword = document.getElementById('cpNewPassword');
  const cpConfirmPassword = document.getElementById('cpConfirmPassword');

  function getPasswordStrengthErrors(pw) {
    const errors = [];
    if (pw.length < 8) errors.push('At least 8 characters');
    if (!/[A-Z]/.test(pw)) errors.push('One uppercase letter');
    if (!/[a-z]/.test(pw)) errors.push('One lowercase letter');
    if (!/[0-9]/.test(pw)) errors.push('One number');
    if (!/[\W_]/.test(pw)) errors.push('One special character');
    return errors;
  }

  function updateStrengthBar(pw) {
    const bar = document.getElementById('cpStrengthBar');
    const label = document.getElementById('cpStrengthLabel');
    if (!bar || !label) return;

    const score = 5 - getPasswordStrengthErrors(pw).length;
    const levels = [
      { label: '', color: 'bg-secondary', width: '0%' },
      { label: 'Very Weak', color: 'bg-danger', width: '20%' },
      { label: 'Weak', color: 'bg-warning', width: '40%' },
      { label: 'Fair', color: 'bg-info', width: '60%' },
      { label: 'Strong', color: 'bg-primary', width: '80%' },
      { label: 'Very Strong', color: 'bg-success', width: '100%' },
    ];

    const level = levels[score];
    bar.className = `progress-bar ${level.color}`;
    bar.style.width = level.width;
    label.textContent = level.label;
    label.className = `form-text ${
      score >= 4 ? 'text-success' : score >= 3 ? 'text-warning' : 'text-danger'
    }`;
  }

  function resetStrengthBar() {
    const bar = document.getElementById('cpStrengthBar');
    const label = document.getElementById('cpStrengthLabel');
    if (bar) {
      bar.className = 'progress-bar bg-secondary';
      bar.style.width = '0%';
    }
    if (label) {
      label.textContent = '';
      label.className = 'form-text';
    }
  }

  // password toggle (show/hide)
  document.querySelectorAll('.cp-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const input = document.getElementById(this.dataset.target);
      if (!input) return;
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      const icon = this.querySelector('i');
      icon.className = isPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
    });
  });

  if (cpNewPassword) {
    cpNewPassword.addEventListener('input', function () {
      if (this.value) {
        updateStrengthBar(this.value);
      } else {
        resetStrengthBar();
      }
    });
  }

  if (changePasswordNavBtn) {
    changePasswordNavBtn.addEventListener('click', function () {
      closeDropdown(userDropdown);
      clearCpForm();
      new bootstrap.Modal(
        document.getElementById('changePasswordModal')
      ).show();
    });
  }

  if (cpSubmitBtn) {
    cpSubmitBtn.addEventListener('click', function () {
      const oldPw = cpOldPassword.value.trim();
      const newPw = cpNewPassword.value.trim();
      const confPw = cpConfirmPassword.value.trim();

      if (!oldPw || !newPw || !confPw) {
        showCpAlert('danger', 'All fields are required.');
        return;
      }

      const strengthErrors = getPasswordStrengthErrors(newPw);
      if (strengthErrors.length > 0) {
        showCpAlert('danger', strengthErrors.join(' · '));
        return;
      }

      cpSubmitBtn.disabled = true;
      cpSubmitBtn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';

      fetch('index.php?page=ajax_change_password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          old_password: oldPw,
          new_password: newPw,
          confirm_password: confPw,
        }),
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) {
            showCpAlert(
              'success',
              data.message || 'Password changed successfully.'
            );
            cpOldPassword.value = '';
            cpNewPassword.value = '';
            cpConfirmPassword.value = '';
            resetStrengthBar();
          } else {
            showCpAlert('danger', data.message || 'Something went wrong.');
          }
        })
        .catch(() => showCpAlert('danger', 'Network error. Please try again.'))
        .finally(() => {
          cpSubmitBtn.disabled = false;
          cpSubmitBtn.innerHTML =
            '<i class="bi bi-floppy-fill me-1"></i> Save Password';
        });
    });
  }

  function showCpAlert(type, message) {
    if (!cpAlert) return;
    cpAlert.className = `alert alert-${type}`;
    cpAlert.textContent = message;
    cpAlert.classList.remove('d-none');
  }

  function clearCpForm() {
    if (cpAlert) {
      cpAlert.className = 'alert d-none';
      cpAlert.textContent = '';
    }
    if (cpOldPassword) cpOldPassword.value = '';
    if (cpNewPassword) cpNewPassword.value = '';
    if (cpConfirmPassword) cpConfirmPassword.value = '';
    resetStrengthBar();
  }

  // ─── HELPERS ─────────────────────────────────────────────────────────

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function escapeAttr(str) {
    return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  // initial count + poll every 60s
  fetchUnreadCount();
  setInterval(fetchUnreadCount, 60000);
});
