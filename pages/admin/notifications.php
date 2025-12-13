<?php
session_start();

// 1. Security Check
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../../login.php'); exit;
}

require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../includes/notifications.php';

$uid = $_SESSION['user_id'];
$rows = getUnreadNotifications($uid);

// Helper function for "Time Ago" (e.g., "2 hours ago")
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year', 'm' => 'month', 'w' => 'week',
        'd' => 'day',  'h' => 'hour',  'i' => 'minute', 's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Admin Panel</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/dashboard-pro.css">
    <link rel="stylesheet" href="../../assets/css/mobileres.css">
</head>
<body>

    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-shield-alt"></i> Admin Panel
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
            <li><a href="manage_quizzes.php"><i class="fas fa-layer-group"></i> All Quizzes</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> System Reports</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

<i class="mobile-menu-btn fas fa-bars"></i>

        <main class="main-content">
        
        <?php include __DIR__ . '/../../includes/header.php'; ?>

        <div class="container">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div>
                    <h2 style="color: var(--text-main); margin-bottom: 5px;">Notifications</h2>
                    <p style="color: #64748b; font-size: 0.9rem;">Recent system activity and alerts.</p>
                </div>
                
                <?php if (!empty($rows)): ?>
                    <form method="post" action="mark_read.php"> <button class="btn-outline" style="font-size: 0.85rem;">
                            <i class="fas fa-check-double"></i> Mark all as read
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="notif-feed">
                <?php if (empty($rows)): ?>
                    
                    <div class="empty-notif">
                        <i class="far fa-bell-slash"></i>
                        <h3>All caught up!</h3>
                        <p>You have no new notifications at this time.</p>
                    </div>

                <?php else: ?>
                    <?php foreach ($rows as $n): ?>
                        <?php 
                            // Determine Icon & Color based on Title keyword
                            $title = strtolower($n['title']);
                            $icon = 'fa-bell';
                            $colorClass = 'icon-gray';

                            if (strpos($title, 'quiz') !== false) {
                                $icon = 'fa-file-signature';
                                $colorClass = 'icon-purple';
                            } elseif (strpos($title, 'user') !== false || strpos($title, 'account') !== false) {
                                $icon = 'fa-user-edit';
                                $colorClass = 'icon-blue';
                            } elseif (strpos($title, 'system') !== false) {
                                $icon = 'fa-server';
                                $colorClass = 'icon-green';
                            }
                        ?>
                        
                        <div class="notif-item unread"> <div class="notif-icon-box <?= $colorClass ?>">
                                <i class="fas <?= $icon ?>"></i>
                            </div>

                            <div class="notif-content">
                                <h4><?= htmlspecialchars($n['title']) ?></h4>
                                
                                <p><?= nl2br($n['message']) ?></p> <div class="notif-time">
                                    <i class="far fa-clock"></i> 
                                    <?= time_elapsed_string($n['created_at']) ?>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </main>
    <script src="../../assets/js/nav.js"></script>

</body>
</html>