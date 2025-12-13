<?php
// pages/student/my_scores.php
session_start();

// 1. Security Check
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    header('Location: ../login.php'); 
    exit;
}

require_once __DIR__ . '/../../database.php';
$db = (new Database())->connect();
$userId = (int)$_SESSION['user_id'];

// 2. Fetch Data
// Improved query to get ranking and quiz details
$sql = "SELECT s.session_id, s.score, s.created_at AS scored_at,
               se.session_code, se.created_at AS session_created, q.title AS quiz_title,
               (SELECT COUNT(*) + 1 FROM scores sc WHERE sc.session_id = s.session_id AND sc.score > s.score) AS rank
        FROM scores s
        LEFT JOIN sessions se ON se.id = s.session_id
        LEFT JOIN quizzes q ON q.id = se.quiz_id
        WHERE s.student_id = :uid
        ORDER BY s.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute([':uid' => $userId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper for page title
$pageTitle = "My Scores";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Scores | Student Dashboard</title>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Professional CSS -->
    <link rel="stylesheet" href="../../assets/css/dashboard-pro.css">
    <link rel="stylesheet" href="../../assets/css/mobileres.css">

</head>
<body>

    <!-- === SIDEBAR === -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-university"></i> Quiz System
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="join.php"><i class="fas fa-gamepad"></i> Join Quiz</a></li>
            <li><a href="../student/history.php"><i class="fas fa-history"></i> History</a></li>
            <!-- Active Page -->
            <li><a href="my_scores.php" class="active"><i class="fas fa-star"></i> My Scores</a></li>
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
            
            <!-- Page Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div>
                    <h2 style="color: var(--text-main); margin-bottom: 5px;">Performance History</h2>
                    <p style="color: #64748b; font-size: 0.9rem;">View your ranks and scores across all sessions.</p>
                </div>
                
                <a href="my_scores_export.php" class="btn-outline" style="padding: 10px 15px; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
                    <i class="fas fa-download" style="color: #2563eb;"></i> Export CSV
                </a>
            </div>

            <!-- Scores Table -->
            <div class="card-table">
                <div class="table-responsive">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Quiz Details</th>
                                <th>Session Code</th>
                                <th>Your Score</th>
                                <th>Rank</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rows)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 50px; color: #94a3b8;">
                                        <i class="fas fa-folder-open" style="font-size: 2.5rem; margin-bottom: 10px; display: block; color: #e2e8f0;"></i>
                                        You have not participated in any scored sessions yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <!-- Date -->
                                        <td style="color: #64748b; font-size: 0.9rem;">
                                            <?= date('M j, Y', strtotime($r['session_created'] ?? $r['scored_at'])) ?>
                                            <br>
                                            <small><?= date('g:i A', strtotime($r['session_created'] ?? $r['scored_at'])) ?></small>
                                        </td>

                                        <!-- Quiz Title -->
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-main);">
                                                <?= htmlspecialchars($r['quiz_title'] ?? 'Unknown Quiz') ?>
                                            </div>
                                        </td>

                                        <!-- Session Code -->
                                        <td>
                                            <span style="font-family: monospace; background: #f1f5f9; padding: 4px 8px; border-radius: 4px; border: 1px solid #e2e8f0; color: var(--text-main);">
                                                <?= htmlspecialchars($r['session_code'] ?? '—') ?>
                                            </span>
                                        </td>

                                        <!-- Score -->
                                        <td>
                                            <span style="font-weight: 700; color: var(--accent);">
                                                <?= $r['score'] !== null ? (int)$r['score'] : '0' ?> pts
                                            </span>
                                        </td>

                                        <!-- Rank (With Icons) -->
                                        <td>
                                            <?php 
                                            $rank = (int)$r['rank'];
                                            if ($rank === 1) {
                                                echo '<span style="color: #fbbf24; font-weight: 700;"><i class="fas fa-trophy"></i> 1st</span>';
                                            } elseif ($rank === 2) {
                                                echo '<span style="color: #9ca3af; font-weight: 700;"><i class="fas fa-medal"></i> 2nd</span>';
                                            } elseif ($rank === 3) {
                                                echo '<span style="color: #b45309; font-weight: 700;"><i class="fas fa-medal"></i> 3rd</span>';
                                            } else {
                                                echo '<span style="color: #64748b;">#' . $rank . '</span>';
                                            }
                                            ?>
                                        </td>

                                        <!-- Actions -->
                                        <td>
                                            <div style="display: flex; gap: 8px;">
                                                <!-- Link to Leaderboard (Assuming you have a student-viewable leaderboard) -->
                                                <a href="../session/leaderboard.php?code=<?= urlencode($r['session_code']) ?>" class="btn-icon btn-edit" title="View Leaderboard">
                                                    <i class="fas fa-list-ol"></i>
                                                </a>
                                                
                                                <!-- Export Single Result -->
                                                <a href="my_scores_export.php?session_id=<?= (int)$r['session_id'] ?>" class="btn-icon btn-edit" title="Download Result" style="color: #059669; background: #ecfdf5; border-color: #d1fae5;">
                                                    <i class="fas fa-file-download"></i>
                                                </a>
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