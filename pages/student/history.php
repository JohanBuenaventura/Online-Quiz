<?php
session_start();

// 1. Security Check
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../database.php';

$db = (new Database())->connect();
$studentId = (int)$_SESSION['user_id'];

// 2. Fetch Data
$stmt = $db->prepare(
    "SELECT sc.score, sc.created_at AS scored_at, se.session_code AS session_code, q.title AS quiz_title, se.id AS session_id
     FROM scores sc
     JOIN sessions se ON se.id = sc.session_id
     LEFT JOIN quizzes q ON q.id = se.quiz_id
     WHERE sc.student_id = :uid
     ORDER BY sc.created_at DESC"
);
$stmt->execute([':uid' => $studentId]);
$rows = $stmt->fetchAll();

$name = htmlspecialchars($_SESSION['user_name'] ?? 'Student');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz History | Student Dashboard</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/dashboard-pro.css">
    <link rel="stylesheet" href="../../assets/css/mobileres.css">
</head>
<body>

    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-university"></i> Quiz System
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="join.php"><i class="fas fa-gamepad"></i> Join Quiz</a></li>
            <li><a href="history.php" class="active"><i class="fas fa-history"></i> History</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="../logout.php" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>
<i class="mobile-menu-btn fas fa-bars"></i>

        <main class="main-content">
        
        <?php include __DIR__ . '/../../includes/header.php'; ?>

        <div class="container">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div>
                    <h2 style="color: var(--text-main); font-size: 1.5rem;">My Quiz History</h2>
                    <p style="color: #64748b; font-size: 0.9rem;">View your past attempts and scores.</p>
                </div>
                <a href="join.php" class="btn-sm btn-outline-primary" style="background: var(--accent); color: white; padding: 10px 20px;">
                    <i class="fas fa-plus"></i> Join New
                </a>
            </div>

            <div class="card-table">
                <?php if (empty($rows)): ?>
                    
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>No attempts yet</h3>
                        <p>You haven't taken any quizzes yet. Join a session to get started!</p>
                        <a href="join.php" style="color: var(--accent); font-weight: 600; text-decoration: none; margin-top: 10px; display: inline-block;">
                            Join a Quiz &rarr;
                        </a>
                    </div>

                <?php else: ?>
                    
                    <div class="table-responsive">
                        <table class="styled-table">
                            <thead>
                                <tr>
                                    <th>Quiz Title</th>
                                    <th>Session Code</th>
                                    <th>Score</th>
                                    <th>Date Taken</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($r['quiz_title'] ?? 'Untitled Quiz') ?></strong>
                                        </td>
                                        <td>
                                            <span style="font-family: monospace; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">
                                                <?= htmlspecialchars($r['session_code']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="score-badge"><?= (int)$r['score'] ?> pts</span>
                                        </td>
                                        <td>
                                            <?= date('M j, Y • g:i A', strtotime($r['scored_at'])) ?>
                                        </td>
                                        <td>
                                            <a href="../session/leaderboard.php?code=<?= urlencode($r['session_code']) ?>" class="btn-sm btn-outline-primary">
                                                <i class="fas fa-trophy"></i> Leaderboard
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </main>
    <script src="../../assets/js/nav.js"></script>

</body>
</html>