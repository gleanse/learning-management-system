<?php
$firstname = $_SESSION['user_firstname'] ?? 'U';
$lastname  = $_SESSION['user_lastname']  ?? '';
$initials  = strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1));
?>

<nav class="navbar top-navbar">
    <div class="container-fluid">

        <!-- left side: hamburger + page title -->
        <div class="d-flex align-items-center gap-2">
            <button class="hamburger-btn d-md-none" id="sidebarToggle" type="button">
                <i class="bi bi-list"></i>
            </button>
            <div class="navbar-brand mb-0">
                <div class="page-icon">
                    <i class="bi <?= htmlspecialchars($page_icon ?? 'bi-house-door-fill') ?>"></i>
                </div>
                <span class="d-none d-sm-inline"><?= htmlspecialchars($page_title ?? 'Learning Management System') ?></span>
            </div>
        </div>

        <!-- right side: bell + user info -->
        <div class="d-flex align-items-center gap-2">

            <!-- bell -->
            <div class="position-relative">
                <button class="bell-btn" id="bellBtn" type="button">
                    <i class="bi bi-bell-fill"></i>
                    <span class="bell-badge" id="bellBadge" style="display:none;">0</span>
                </button>
                <div class="bell-dropdown" id="bellDropdown" style="display:none;">
                    <div class="bell-dropdown-header">
                        <span>Notifications</span>
                        <button type="button" class="btn btn-link p-0" id="markAllReadBtn">Mark all as read</button>
                    </div>
                    <div class="bell-dropdown-body" id="bellNotifList">
                        <div class="bell-empty-state" id="bellEmptyState">
                            <i class="bi bi-bell-slash"></i>
                            <p>No new notifications</p>
                        </div>
                    </div>
                    <div class="bell-dropdown-footer">
                        <a href="index.php?page=my_announcements">View all announcements</a>
                    </div>
                </div>
            </div>

            <!-- user details + avatar -->
            <div class="position-relative">
                <div class="user-info-wrapper" id="userAvatarBtn">
                    <div class="user-details">
                        <span class="user-name"><?= htmlspecialchars($firstname . ' ' . $lastname) ?></span>
                        <span class="user-role">
                            <i class="bi bi-person-badge-fill"></i>
                            <?= ucfirst(htmlspecialchars($_SESSION['user_role'] ?? '')) ?>
                        </span>
                    </div>
                    <div class="user-avatar"><?= $initials ?></div>
                </div>

                <!-- user dropdown -->
                <div class="user-dropdown" id="userDropdown" style="display:none;">
                    <div class="user-dropdown-header">
                        <div class="user-avatar-lg"><?= $initials ?></div>
                        <div>
                            <p class="mb-0" style="font-size:0.875rem; font-weight:700; color:var(--secondary);">
                                <?= htmlspecialchars($firstname . ' ' . $lastname) ?>
                            </p>
                            <p class="mb-0" style="font-size:0.75rem; color:var(--gray-500);">
                                <?= ucfirst(htmlspecialchars($_SESSION['user_role'] ?? '')) ?>
                            </p>
                        </div>
                    </div>
                    <div class="user-dropdown-body">
                        <button type="button" class="user-dropdown-item" id="changePasswordNavBtn">
                            <i class="bi bi-key-fill"></i>
                            Change Password
                        </button>
                        <a href="index.php?page=logout" class="user-dropdown-item user-dropdown-item-danger">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</nav>

<?php require __DIR__ . '/navbar_modals.php'; ?>