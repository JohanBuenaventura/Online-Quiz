<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$name = htmlspecialchars($_SESSION['user_name'] ?? 'Guest');
$role = htmlspecialchars($_SESSION['user_role'] ?? 'guest');

// Use the title set in the page, or default to 'Dashboard'
$headerTitle = isset($pageTitle) ? $pageTitle : 'Dashboard';
?>

<header class="top-header">

    <div class="header-title">
        <h2><?= htmlspecialchars($headerTitle) ?></h2>
        <!-- <p>Welcome back, <strong><?= $name ?></strong></p> -->
    </div>

    <div class="header-right">
        
        <?php if (!empty($_SESSION['user_id'])): ?>
        <div id="notifArea" class="notif-container">
            <button id="notifBell" class="notif-bell" aria-label="Notifications">
                <i class="fas fa-bell"></i>
            </button>
            <span id="notifCount" class="notif-count" style="display:none">0</span>

            <div id="notifDropdown" class="notif-dropdown" aria-hidden="true">
                <div class="notif-header">
                    <span>Notifications</span>
                    <a href="/../pages/admin/notifications.php" style="font-size: 0.8rem; color: var(--accent);">View All</a>
                </div>
                
                <div id="notifList" class="notif-list">
                    <div class="notif-item">Loading...</div>
                </div>

                <div class="notif-actions">
                    <button id="markReadBtn" class="btn-mark-read">Mark all as read</button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="user-profile">
            <span style="text-transform: capitalize; margin-right: 10px; font-size: 0.9rem;">
                <?= $name ?>
            </span>
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
        </div>

    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bell = document.getElementById('notifBell');
        const dropdown = document.getElementById('notifDropdown');

        if(bell && dropdown) {
            bell.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('show');
            });

            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });
        }
    });

    
</script>




<script src="../../assets/js/notifications.js"></script>