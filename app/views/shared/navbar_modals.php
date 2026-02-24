<!-- change password modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-key-fill"></i>
                    Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="changePasswordAlert" class="alert d-none mb-3"></div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-lock-fill"></i> Current Password
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="cpOldPassword" placeholder="Enter current password">
                        <button type="button" class="btn btn-outline-secondary cp-toggle" data-target="cpOldPassword">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-lock-fill"></i> New Password
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="cpNewPassword" placeholder="Min. 8 chars, uppercase, lowercase, number & special">
                        <button type="button" class="btn btn-outline-secondary cp-toggle" data-target="cpNewPassword">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                    <div class="progress mt-2" style="height: 5px;">
                        <div class="progress-bar bg-secondary" id="cpStrengthBar" style="width: 0%; transition: width 0.3s ease;"></div>
                    </div>
                    <small id="cpStrengthLabel" class="form-text"></small>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-lock-fill"></i> Confirm New Password
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="cpConfirmPassword" placeholder="Repeat new password">
                        <button type="button" class="btn btn-outline-secondary cp-toggle" data-target="cpConfirmPassword">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="cpSubmitBtn">
                    <i class="bi bi-floppy-fill"></i> Save Password
                </button>
            </div>
        </div>
    </div>
</div>

<!-- announcement detail modal -->
<div class="modal fade" id="annDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="annDetailTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="ann-detail-meta mb-3" id="annDetailMeta"></div>
                <div class="ann-detail-content" id="annDetailContent"></div>
            </div>
        </div>
    </div>
</div>

<!-- announcement list modal -->
<div class="modal fade" id="annListModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-megaphone-fill me-2"></i>All Announcements
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="annListBody">
                <div class="d-flex justify-content-center align-items-center py-5">
                    <span class="spinner-border spinner-border-sm me-2"></span> Loading...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- bell modal (mobile only) -->
<div class="modal fade" id="bellModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-bell-fill me-2"></i>Notifications
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="bell-dropdown-header border-0">
                    <span>Unread</span>
                    <button type="button" class="btn btn-link p-0" id="markAllReadBtnModal">Mark all as read</button>
                </div>
                <div class="bell-dropdown-body" id="bellNotifListModal">
                    <div class="bell-empty-state" id="bellEmptyStateModal">
                        <i class="bi bi-bell-slash"></i>
                        <p>No new notifications</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="#" class="btn btn-sm btn-outline-primary" id="bellModalViewAllBtn">
                    <i class="bi bi-megaphone-fill me-1"></i> View all announcements
                </a>
            </div>
        </div>
    </div>
</div>