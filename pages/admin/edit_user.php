<?php
session_start();

// 1. Security Check
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../classes/User.php';
$userModel = new User();

// 2. CSRF Helper
$csrfPath = __DIR__ . '/../../includes/csrf.php';
if (file_exists($csrfPath)) {
    require_once $csrfPath;
} else {
    function csrf_check($token) { return true; }
    function csrf_field() { return '<input type="hidden" name="csrf_token" value="token">'; }
}

if (!isset($_GET['id'])) {
    header('Location: manage_users.php');
    exit;
}

$id = (int)$_GET['id'];
$user = $userModel->findById($id);

if (!$user) exit('User not found.');

$errors = [];

// FIX: Use unique variable names to avoid conflict with header.php
$edit_name = $user['name'];
$edit_email = $user['email'];
$edit_role = $user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token']) && !csrf_check($_POST['csrf_token'])) die('Invalid CSRF token');
    
    // Update these variables so the form shows the new (submitted) data if there's an error
    $edit_name = trim($_POST['name'] ?? '');
    $edit_email = trim($_POST['email'] ?? '');
    $edit_role = in_array($_POST['role'] ?? 'student', ['admin', 'teacher', 'student']) ? $_POST['role'] : 'student';

    if ($edit_name === '') $errors['name'] = 'Name is required';
    if (!filter_var($edit_email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email required';

    if (empty($errors)) {
        if ($userModel->updateUser($id, $edit_name, $edit_email, $edit_role)) {
            
            // Notification Logic
            require_once __DIR__ . '/../../includes/notifications.php';
            if (function_exists('createNotification')) {
                createNotification($id, "Account Updated", "Your account details were updated by an admin.");
            }

            header('Location: manage_users.php');
            exit;
        } else {
            $errors['general'] = 'Database update failed.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | Admin Panel</title>
    
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
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>


<i class="mobile-menu-btn fas fa-bars"></i>

        <main class="main-content">
        
        <!-- This include was overwriting $name. Now we use $edit_name below, so it's safe. -->
        <?php include __DIR__ . '/../../includes/header.php'; ?>

        <div class="container">
            
            <div style="margin-bottom: 20px;">
                <a href="manage_users.php" style="text-decoration: none; color: #64748b; font-size: 0.9rem;">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>

            <div class="form-card">
                
                <div style="border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 25px;">
                    <h2 style="margin-bottom: 5px;">Edit User Profile</h2>
                    <p style="color: #64748b; margin: 0;">Update information for ID #<?= $id ?></p>
                </div>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert" style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                        <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <!-- FIX: Use $edit_name instead of $name -->
                        <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($edit_name) ?>" placeholder="e.g. John Doe">
                        <?php if (isset($errors['name'])): ?>
                            <p style="color: #ef4444; font-size: 0.85rem; margin-top: 5px;"><?= $errors['name'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <!-- FIX: Use $edit_email instead of $email -->
                        <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($edit_email) ?>" placeholder="e.g. user@school.com">
                        <?php if (isset($errors['email'])): ?>
                            <p style="color: #ef4444; font-size: 0.85rem; margin-top: 5px;"><?= $errors['email'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">System Role</label>
                        <select name="role" class="form-select">
                            <!-- FIX: Use $edit_role -->
                            <option value="student" <?= $edit_role === 'student' ? 'selected' : '' ?>>Student</option>
                            <option value="teacher" <?= $edit_role === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                            <option value="admin" <?= $edit_role === 'admin' ? 'selected' : '' ?>>Administrator</option>
                        </select>
                        <p style="font-size: 0.85rem; color: #64748b; margin-top: 8px;">
                            <i class="fas fa-info-circle"></i> Admins have full access to all settings.
                        </p>
                    </div>

                    <div style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center;">
                        <a href="manage_users.php" style="color: #64748b; text-decoration: none; font-weight: 500;">Cancel</a>
                        
                        <button type="submit" class="btn-primary" style="padding: 12px 30px;">
                            Save Changes
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </main>
    <script src="../../assets/js/nav.js"></script>
</body>
</html>