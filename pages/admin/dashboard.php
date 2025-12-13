<?php
session_start();

// 1. Security Check (Admin Only)
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// 2. Include Models
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Quiz.php';

$userModel = new User();
$quizModel = new Quiz();

// 3. Fetch Real Data
$users = $userModel->getAllUsers();
$quizzes = $quizModel->getAllQuizzes();

$total_users = count($users);
$total_quizzes = count($quizzes);

$name = htmlspecialchars($_SESSION['user_name'] ?? 'Administrator');

// 4. Build Dynamic Stats Array
$stats = [
    [
        'label' => 'Total Users', 
        'value' => $total_users, 
        'icon' => 'fa-users', 
        'color' => '#3b82f6' // Blue
    ],
    [
        'label' => 'Total Quizzes', 
        'value' => $total_quizzes, 
        'icon' => 'fa-database', 
        'color' => '#10b981' // Green
    ],
    [
        'label' => 'System Status', 
        'value' => 'Online', // This is usually hardcoded unless you have a health check
        'icon' => 'fa-server', 
        'color' => '#6366f1' // Indigo
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Quiz System</title>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Professional CSS -->
    <link rel="stylesheet" href="../../assets/css/dashboard-pro.css">
    <link rel="stylesheet" href="../../assets/css/mobileres.css">
    
    
    <style>
        /* Admin-specific overrides */
        :root { --accent: #d946ef; } /* Magenta for Admin */
        .stat-card { border-top: 4px solid transparent; }
    </style>
</head>
<body>

    <!-- === SIDEBAR === -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-shield-alt"></i> Admin Panel
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php"><i class="fas fa-home"></i> User Dashboard</a></li>
            <li style="border-top: 1px solid rgba(255,255,255,0.1); margin: 10px 0;"></li>
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
            <li><a href="maintenance.php"><i class="fas fa-tools"></i> Maintenance</a></li>
            <li><a href="manage_quizzes.php"><i class="fas fa-layer-group"></i> All Quizzes</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> System Reports</a></li>
        </ul>

        <div class="sidebar-footer">
            <a href="../logout.php" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- === MAIN CONTENT === -->
<i class="mobile-menu-btn fas fa-bars"></i>

        <main class="main-content">
        
        <?php include __DIR__ . '/../../includes/header.php'; ?>

        <div class="container">
            
            <!-- Dynamic Stats Row -->
            <div class="stats-row">
                <?php foreach ($stats as $stat): ?>
                <div class="stat-card" style="border-top-color: <?= $stat['color'] ?>;">
                    <div class="stat-label">
                        <i class="fas <?= $stat['icon'] ?>" style="color: <?= $stat['color'] ?>; margin-right: 5px;"></i> 
                        <?= $stat['label'] ?>
                    </div>
                    <div class="stat-number"><?= $stat['value'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <h3 style="color: var(--text-main); margin-bottom: 15px;">System Management</h3>

            <!-- Action Grid -->
            <div class="action-grid">
                
                <!-- Manage Users -->
                <a href="manage_users.php" class="action-card">
                    <div class="card-icon" style="color: #3b82f6; background: #eff6ff;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="card-text">
                        <h3>User Management</h3>
                        <p>Add, edit, or ban users. Manage teacher and student roles.</p>
                    </div>
                </a>

                <!-- Manage Quizzes -->
                <a href="manage_quizzes.php" class="action-card">
                    <div class="card-icon" style="color: #10b981; background: #ecfdf5;">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <div class="card-text">
                        <h3>Global Quiz Manager</h3>
                        <p>View all quizzes created by teachers. Audit content moderation.</p>
                    </div>
                </a>

                <!-- Reports -->
                <a href="reports.php" class="action-card">
                    <div class="card-icon" style="color: #8b5cf6; background: #f5f3ff;">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="card-text">
                        <h3>System Reports</h3>
                        <p>View platform usage, activity logs, and performance analytics.</p>
                    </div>
                </a>

            </div>

            <!-- Quick Links / Maintenance -->
            <div style="margin-top: 30px; background: white; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <h4 style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-tools" style="color: #64748b;"></i> Maintenance Tools
                </h4>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <a href="maintenance.php" class="btn-outline" style="font-size: 0.9rem; padding:10px 14px; text-decoration:none; display:inline-block">Clear Cache / Maintenance</a>
                    <a href="maintenance.php" class="btn-outline" style="font-size: 0.9rem; padding:10px 14px; text-decoration:none; display:inline-block">Backup Database</a>
                    <a href="maintenance.php" class="btn-outline" style="font-size: 0.9rem; color: #ef4444; border-color: #fecaca; padding:10px 14px; text-decoration:none; display:inline-block">View Error Logs</a>
                </div>
            </div>

        </div>
    </main>
    <script src="../../assets/js/nav.js"></script>
</body>
</html>