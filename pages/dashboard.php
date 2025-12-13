<?php
session_start();

// 1. Security Check
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Include Models for Real Data
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Quiz.php';
require_once __DIR__ . '/../database.php'; 

$userModel = new User();
$quizModel = new Quiz();
$db = (new Database())->connect(); 

// 3. Get User Details
$uid = $_SESSION['user_id'];
$name = htmlspecialchars($_SESSION['user_name'] ?? 'User');
$role = $_SESSION['user_role'] ?? 'student';

// 4. Calculate Stats based on Role
$stats = [];

if ($role === 'admin') {
    // === ADMIN VIEW ===
    $allUsers = $userModel->getAllUsers();
    $allQuizzes = $quizModel->getAllQuizzes();

    $studentCount = count(array_filter($allUsers, fn($u) => $u['role'] === 'student'));
    $teacherCount = count(array_filter($allUsers, fn($u) => $u['role'] === 'teacher'));

    $stats = [
        ['label' => 'Total Quizzes', 'value' => count($allQuizzes), 'icon' => 'fa-file-alt'],
        ['label' => 'Total Users',   'value' => count($allUsers),   'icon' => 'fa-users'],
        ['label' => 'Students',      'value' => $studentCount,      'icon' => 'fa-user-graduate'],
        ['label' => 'Teachers',      'value' => $teacherCount,      'icon' => 'fa-chalkboard-teacher'],
    ];

} elseif ($role === 'teacher') {
    $myQuizzes = $quizModel->getQuizzesByTeacher($uid);

    $stmt = $db->prepare("SELECT COUNT(*) FROM sessions WHERE host_teacher_id = :tid");
    $stmt->execute([':tid' => $uid]);
    $totalSessions = (int)$stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM quizzes WHERE teacher_id = :tid AND is_published = 0");
    $stmt->execute([':tid' => $uid]);
    $draftQuizzes = (int)$stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM quizzes WHERE teacher_id = :tid");
    $stmt->execute([':tid' => $uid]);
    $totalQuizzes = (int)$stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM quizzes WHERE teacher_id = :tid AND is_published = 1");
    $stmt->execute([':tid' => $uid]);
    $publishedQuizzes = (int)$stmt->fetchColumn();

    $stats = [
        ['label' => 'Draft Quizzes', 'value' => $draftQuizzes, 'icon' => 'fa-file-alt'],
        ['label' => 'Total Quizzes', 'value' => $totalQuizzes, 'icon' => 'fa-book'],
        ['label' => 'Total Sessions',  'value' => $totalSessions,    'icon' => 'fa-list'],
        ['label' => 'Published Quizzes','value' => $publishedQuizzes, 'icon' => 'fa-book-open'],
    ];

} else {
    $stmt = $db->prepare("SELECT COUNT(*) FROM scores WHERE student_id = ?");
    $stmt->execute([$uid]);
    $quizzesTaken = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT AVG(score) FROM scores WHERE student_id = ?");
    $stmt->execute([$uid]);
    $avgScore = $stmt->fetchColumn();
    $avgDisplay = $avgScore ? round($avgScore, 1) . '%' : '0%';

    $stats = [
        ['label' => 'Quizzes Taken', 'value' => $quizzesTaken, 'icon' => 'fa-check-circle'],
        ['label' => 'Average Score', 'value' => $avgDisplay,   'icon' => 'fa-percentage'],
        ['label' => 'Assignments',   'value' => 0,  'icon' => 'fa-tasks'],
    ];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Online Quiz System</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard-pro.css">
    <link rel="stylesheet" href="../assets/css/mobileres.css">

</head>
<body>

    <!-- ADDED FOR MOBILE SIDEBAR: OVERLAY -->
    <div class="sidebar-overlay"></div>

    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-university"></i> Quiz System
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>

            <?php if ($role === 'teacher' || $role === 'admin'): ?>
                <li><a href="teacher/create_quiz.php"><i class="fas fa-plus-circle"></i> Create Quiz</a></li>
                <li><a href="teacher/manage_quizzes.php"><i class="fas fa-folder-open"></i> My Quizzes</a></li>
                <li><a href="teacher/reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
            <?php endif; ?>
            
            <?php if ($role === 'student'): ?>
                <li><a href="student/join.php"><i class="fas fa-gamepad"></i> Join Quiz</a></li>
                <li><a href="student/history.php"><i class="fas fa-history"></i> History</a></li>
                <li><a href="session/my_scores.php"><i class="fas fa-star"></i> My Scores</a></li>
            <?php endif; ?>

            <li><a href="../pages/account.php"><i class="fas fa-user-cog"></i> Account Settings </a></li>    

            <?php if ($role === 'admin'): ?>
                <li><a href="admin/manage_users.php"><i class="fas fa-users-cog"></i> User Management</a></li>
                <li><a href="admin/manage_quizzes.php"><i class="fas fa-layer-group"></i> Global Quizzes</a></li>
            <?php endif; ?>
        </ul>

        <div class="sidebar-footer">
            <a href="logout.php" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Floating Mobile Menu Button -->
    <i class="mobile-menu-btn fas fa-bars"></i>

        <main class="main-content">

        <?php include __DIR__ . '/../includes/header.php'; ?>

        <div class="container">
            
            <div class="stats-row">
                <?php foreach ($stats as $stat): ?>
                <div class="stat-card">
                    <div class="stat-label">
                        <i class="fas <?= $stat['icon'] ?>" style="margin-right:5px;"></i> <?= $stat['label'] ?>
                    </div>
                    <div class="stat-number"><?= $stat['value'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($role === 'teacher' || $role === 'admin'): ?>
                <h3 style="color: var(--text-main); margin-bottom: 15px;">Instructor Actions</h3>
                <div class="action-grid">
                    <a href="teacher/create_quiz.php" class="action-card">
                        <div class="card-icon"><i class="fas fa-plus"></i></div>
                        <div class="card-text">
                            <h3>Create Quiz</h3>
                            <p>Build a new assessment with multiple choice questions.</p>
                        </div>
                    </a>
                    <a href="teacher/manage_quizzes.php" class="action-card">
                        <div class="card-icon"><i class="fas fa-tasks"></i></div>
                        <div class="card-text">
                            <h3>Manage Quizzes</h3>
                            <p>Edit questions, settings, or view quiz status.</p>
                        </div>
                    </a>
                    <a href="teacher/start_session.php" class="action-card">
                        <div class="card-icon"><i class="fas fa-broadcast-tower"></i></div>
                        <div class="card-text">
                            <h3>Start Live Session</h3>
                            <p>Launch a real-time session and get a join code.</p>
                        </div>
                    </a>
                    <?php if ($role === 'admin'): ?>
                    <a href="admin/dashboard.php" class="action-card" style="border-color: #ef4444;">
                        <div class="card-icon" style="color: #ef4444; background: #fef2f2;"><i class="fas fa-cogs"></i></div>
                        <div class="card-text">
                            <h3>Admin Panel</h3>
                            <p>Manage system users and global configurations.</p>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($role === 'student'): ?>
                <h3 style="color: var(--text-main); margin-bottom: 15px;">Student Area</h3>
                <div class="action-grid">
                    <a href="student/join.php" class="action-card">
                        <div class="card-icon"><i class="fas fa-keyboard"></i></div>
                        <div class="card-text">
                            <h3>Join Quiz</h3>
                            <p>Have a game code? Enter it here to start playing.</p>
                        </div>
                    </a>
                    <a href="student/history.php" class="action-card">
                        <div class="card-icon"><i class="fas fa-history"></i></div>
                        <div class="card-text">
                            <h3>Quiz History</h3>
                            <p>Review your past attempts and check your scores.</p>
                        </div>
                    </a>
                    <a href="session/my_scores.php" class="action-card">
                        <div class="card-icon"><i class="fas fa-trophy"></i></div>
                        <div class="card-text">
                            <h3>Leaderboard</h3>
                            <p>See how you rank against your classmates.</p>
                        </div>
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- ADDED FOR MOBILE SIDEBAR SCRIPT -->
    <script src="../assets/js/nav.js"></script>

</body>
</html>
