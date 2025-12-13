<?php
session_start();

// 1. Security Check (Admin Only)
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../classes/User.php';
$userModel = new User();
$users = $userModel->getAllUsers();

// 2. CSRF Helper
$csrfPath = __DIR__ . '/../../includes/csrf.php';
if (file_exists($csrfPath)) {
    require_once $csrfPath;
} else {
    function csrf_field() { return '<input type="hidden" name="csrf_token" value="token">'; }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin Panel</title>
    
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
            <li><a href="manage_users.php" class="active"><i class="fas fa-users-cog"></i> Manage Users</a></li>
            <li><a href="manage_quizzes.php"><i class="fas fa-layer-group"></i> All Quizzes</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> System Reports</a></li>
        </ul>
        <div class="sidebar-footer">
           <a href="../logout.php" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-sign-out-alt"></i> Logout
        </div>
    </nav>

<i class="mobile-menu-btn fas fa-bars"></i>

        <main class="main-content">
        
        <?php include __DIR__ . '/../../includes/header.php'; ?>

        <div class="container">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h2 style="color: var(--text-main); margin-bottom: 5px;">User Management</h2>
                    <p style="color: #64748b; font-size: 0.9rem;">Total Users: <strong><?= count($users) ?></strong></p>
                </div>
                
                <a href="create_user.php" class="btn-primary" style="text-decoration: none; padding: 10px; border-radius: 10px;" >
                    <i class="fas fa-user-plus"></i> Create New User
                </a>
            </div>

            <div class="card-table">
                <div class="table-responsive">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th width="5%">ID</th>
                                <th width="35%">User Profile</th>
                                <th width="25%">Email Address</th>
                                <th width="15%">Role</th>
                                <th width="20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td style="color: #94a3b8;">#<?= (int)$u['id'] ?></td>
                                    
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar-small">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($u['name']) ?></div>
                                                <div style="font-size: 0.8rem; color: #94a3b8;">Joined: <?= date('M d, Y', strtotime($u['created_at'])) ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    
                                    <td>
                                        <?php 
                                            $role = $u['role'];
                                            $badgeClass = 'role-student';
                                            if($role === 'admin') $badgeClass = 'role-admin';
                                            if($role === 'teacher') $badgeClass = 'role-teacher';
                                        ?>
                                        <span class="role-badge <?= $badgeClass ?>">
                                            <?= ucfirst($role) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="action-group">
                                            <a href="edit_user.php?id=<?= (int)$u['id'] ?>" class="btn-icon btn-edit" title="Edit User">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>

                                            <form method="post" action="delete_user.php" onsubmit="return confirm('Are you sure you want to delete this user? This cannot be undone.');" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn-icon btn-delete btn-delete-form" title="Delete User">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
    <script src="../../assets/js/nav.js"></script>

</body>
</html>