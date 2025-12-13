<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../classes/User.php';
$userModel = new User();
$user = $userModel->findById((int)$_SESSION['user_id']);
if (!$user) {
    header('Location: ../login.php'); exit;
}

$name = htmlspecialchars($user['name']);
$email = htmlspecialchars($user['email']);
$role = htmlspecialchars($user['role']);
$join_date = htmlspecialchars(date('F j, Y', strtotime($user['created_at'] ?? date('Y-m-d'))));

// Flash messages
$success = $_SESSION['account_success'] ?? null;
$errors = $_SESSION['account_errors'] ?? [];
unset($_SESSION['account_success'], $_SESSION['account_errors']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard-pro.css">
    <link rel="stylesheet" href="../assets/css/mobileres.css">
</head>
<body>

    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-university"></i> Quiz System
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="../pages/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>

            <?php if ($role === 'teacher' || $role === 'admin'): ?>
                <li><a href="teacher/manage_quizzes.php"><i class="fas fa-book"></i> My Quizzes</a></li>
            <?php endif; ?>
            
            <?php if ($role === 'student'): ?>
                <li><a href="student/history.php"><i class="fas fa-history"></i> History</a></li>
            <?php endif; ?>

            <li><a href="account.php" class="active"><i class="fas fa-user-cog"></i> Account Settings</a></li>
        </ul>

        <div class="sidebar-footer">
            <a href="logout.php" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

<i class="mobile-menu-btn fas fa-bars"></i>

        <main class="main-content">
        
        <?php include __DIR__ . '/../includes/header.php'; ?>

        <div class="container">
            
            <div style="margin-bottom: 20px;">
                <a href="../pages/dashboard.php" style="text-decoration: none; color: #64748b; font-size: 0.9rem;">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <form id="accountForm" method="post" action="update_account.php">
                <div class="profile-card">
                    
                    <div class="profile-header">
                        <div class="avatar-large">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="profile-info">
                            <h1><?= $name ?></h1>
                            <span class="role-badge"><?= ucfirst($role) ?> Account</span>
                        </div>
                    </div>

                    <div class="settings-grid">
                        
                        <div class="form-item editable-field">
                            <label>Full Name</label>
                            <div class="value-text"><?= $name ?></div>
                            <input type="text" name="name" class="form-input" value="<?= $name ?>">
                        </div>

                        <div class="form-item editable-field">
                            <label>Email Address</label>
                            <div class="value-text"><?= $email ?></div>
                            <input type="email" name="email" class="form-input" value="<?= $email ?>">
                        </div>

                        <div class="form-item">
                            <label>Account Role</label>
                            <div class="value-text" style="color: #64748b;"><?= ucfirst($role) ?></div>
                        </div>

                        <div class="form-item">
                            <label>Member Since</label>
                            <div class="value-text" style="color: #64748b;"><?= $join_date ?></div>
                        </div>

                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="editBtn">
                            <i class="fas fa-pencil-alt"></i> Edit Profile
                        </button>
                        
                        <button type="submit" class="btn btn-primary" id="saveBtn" style="display: none;">
                            <i class="fas fa-save"></i> Save Changes
                        </button>

                        <button type="button" class="btn btn-outline" id="cancelBtn" style="display: none;">
                            Cancel
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </main>

    <script>
        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const form = document.getElementById('accountForm');
        
        // Only target items with class 'editable-field'
        const editableFields = document.querySelectorAll('.editable-field');

        editBtn.addEventListener('click', () => {
            // Switch to Edit Mode
            editableFields.forEach(field => field.classList.add('edit-mode'));
            
            // Swap Buttons
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-block';
            cancelBtn.style.display = 'inline-block';
        });

        cancelBtn.addEventListener('click', () => {
            // Revert to View Mode
            editableFields.forEach(field => field.classList.remove('edit-mode'));
            
            // Swap Buttons
            editBtn.style.display = 'inline-block';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
        });
    </script>
    <script src="../assets/js/nav.js"></script>

</body>
</html>