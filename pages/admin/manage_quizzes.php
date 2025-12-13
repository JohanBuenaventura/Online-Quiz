<?php
session_start();

// 1. Security Check (Admin Only)
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../classes/Quiz.php';
$quizModel = new Quiz();
$quizzes = $quizModel->getAllQuizzes();

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
    <title>Manage All Quizzes | Admin Panel</title>
    
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
            <li><a href="manage_quizzes.php" class="active"><i class="fas fa-layer-group"></i> All Quizzes</a></li>
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
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div>
                    <h2 style="color: var(--text-main); margin-bottom: 5px;">Global Quiz Manager</h2>
                    <p style="color: #64748b; font-size: 0.9rem;">Monitor and moderate all quizzes created by teachers.</p>
                </div>
                <div style="background: white; padding: 8px 15px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.9rem; color: #64748b;">
                    <strong><?= count($quizzes) ?></strong> Total Quizzes
                </div>
            </div>

            <div class="card-table">
                <div class="table-responsive">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th width="5%">ID</th>
                                <th width="35%">Quiz Title</th>
                                <th width="25%">Creator (Teacher)</th>
                                <th width="15%">Status</th>
                                <th width="20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($quizzes)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">
                                        No quizzes found in the system.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($quizzes as $q): ?>
                                    <tr>
                                        <td style="color: #94a3b8;">#<?= (int)$q['id'] ?></td>
                                        
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-main);">
                                                <?= htmlspecialchars($q['title']) ?>
                                            </div>
                                        </td>

                                        <td>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <div style="width: 28px; height: 28px; background: #e0f2fe; color: #0369a1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                                                    <i class="fas fa-chalkboard-teacher"></i>
                                                </div>
                                                <span><?= htmlspecialchars($q['teacher_name'] ?? 'Unknown') ?></span>
                                            </div>
                                        </td>

                                        <td>
                                            <?php if ($q['is_published']): ?>
                                                <span class="status-badge status-success">
                                                    <i class="fas fa-check-circle"></i> Published
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge status-warning">
                                                    <i class="fas fa-eye-slash"></i> Draft
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="action-group">
                                                <a href="../teacher/manage_questions.php?quiz_id=<?= (int)$q['id'] ?>" class="btn-icon btn-edit" title="Inspect Questions">
                                                    <i class="fas fa-search"></i>
                                                </a>

                                                <form method="post" action="delete_quiz.php" style="display:inline;" onsubmit="return confirm('ADMIN WARNING: This will permanently delete this quiz and all student results associated with it. Continue?');">
                                                    <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn-icon btn-delete btn-delete-form" title="Force Delete Quiz">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
    <script src="../../assets/js/nav.js"></script>

</body>
</html>