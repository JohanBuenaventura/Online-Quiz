<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. Security Check
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php'); exit;
}

$msg = $_SESSION['admin_msg'] ?? null; unset($_SESSION['admin_msg']);
$err = $_SESSION['admin_err'] ?? null; unset($_SESSION['admin_err']);

// Page Title for Header
$pageTitle = "System Maintenance";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance | Admin Panel</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/dashboard-pro.css">
    <link rel="stylesheet" href="../../assets/css/mobileres.css">
</head>
<body>

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-shield-alt"></i> Admin Panel
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
            <li><a href="manage_quizzes.php"><i class="fas fa-layer-group"></i> All Quizzes</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> System Reports</a></li>
            <li><a href="maintenance.php" class="active"><i class="fas fa-tools"></i> Maintenance</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
<i class="mobile-menu-btn fas fa-bars"></i>

        <main class="main-content">
        
        <?php include __DIR__ . '/../../includes/header.php'; ?>

        <div class="container">
            
            <!-- Page Header -->
            <div style="margin-bottom: 25px;">
                <h2 style="color: var(--text-main); margin-bottom: 5px;">System Utilities</h2>
                <p style="color: #64748b; font-size: 0.9rem;">Perform administrative tasks to keep the platform healthy.</p>
            </div>

            <!-- Alerts -->
            <?php if ($msg): ?>
                <div class="alert" style="background: #ecfdf5; border: 1px solid #bbf7d0; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($err): ?>
                <div class="alert" style="background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($err) ?>
                </div>
            <?php endif; ?>

            <!-- Maintenance Grid -->
            <div class="action-grid">

                <!-- 1. Database Backup -->
                <div class="action-card">
                    <div class="card-icon" style="background: #eff6ff; color: #2563eb;">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="card-text">
                        <h3>Backup Database</h3>
                        <p>Create a full SQL dump of the current database state.</p>
                        <form method="post" action="maintenance_action.php" style="margin-top: 15px;">
                            <input type="hidden" name="action" value="backup_db">
                            <button class="btn-primary" type="submit" style="width: auto; padding: 8px 15px; font-size: 0.9rem;">
                                <i class="fas fa-download"></i> Download SQL
                            </button>
                        </form>
                    </div>
                </div>

                <!-- 2. Clear Cache -->
                <div class="action-card">
                    <div class="card-icon" style="background: #fff7ed; color: #ea580c;">
                        <i class="fas fa-broom"></i>
                    </div>
                    <div class="card-text">
                        <h3>Clear Cache</h3>
                        <p>Remove temporary uploads and cached session files.</p>
                        <form method="post" action="maintenance_action.php" style="margin-top: 15px;">
                            <input type="hidden" name="action" value="clear_cache">
                            <button class="btn-outline" type="submit" style="padding: 8px 15px; font-size: 0.9rem;">
                                <i class="fas fa-recycle"></i> Run Cleanup
                            </button>
                        </form>
                    </div>
                </div>

                <!-- 3. View Logs -->
                <div class="action-card">
                    <div class="card-icon" style="background: #f1f5f9; color: #475569;">
                        <i class="fas fa-file-code"></i>
                    </div>
                    <div class="card-text">
                        <h3>System Logs</h3>
                        <p>Inspect recent PHP error logs for debugging.</p>
                        <form method="get" action="maintenance_action.php" style="margin-top: 15px;">
                            <input type="hidden" name="action" value="view_logs">
                            <button class="btn-outline" type="submit" style="padding: 8px 15px; font-size: 0.9rem;">
                                <i class="fas fa-search"></i> View Logs
                            </button>
                        </form>
                    </div>
                </div>

                <!-- 4. Cleanup Backups -->
                <div class="action-card" style="border-color: #fee2e2;">
                    <div class="card-icon" style="background: #fef2f2; color: #dc2626;">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <div class="card-text">
                        <h3>Prune Backups</h3>
                        <p>Permanently delete backup files older than 30 days.</p>
                        <form method="post" action="maintenance_action.php" style="margin-top: 15px;" onsubmit="return confirm('Are you sure? This will delete old backup files.');">
                            <input type="hidden" name="action" value="cleanup_backups">
                            <button class="btn-outline" type="submit" style="padding: 8px 15px; font-size: 0.9rem; color: #dc2626; border-color: #fca5a5;">
                                <i class="fas fa-exclamation-circle"></i> Delete Old Files
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </main>
    <script src="../../assets/js/nav.js"></script>
</body>
</html>