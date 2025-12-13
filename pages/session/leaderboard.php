<?php
session_start();
require_once __DIR__ . '/../../classes/Session.php';

$sessionModel = new Session();

$code = $_GET['code'] ?? '';
if ($code === '') exit('Missing code');

// Validate session
$s = $sessionModel->getSessionByCode($code);
if (!$s) exit('Session not found');

// Fetch leaderboard data
$board = $sessionModel->getLeaderboard($s['id']);

// --- STATS LOGIC ---
$totalPlayers = count($board);
$topScore = !empty($board) ? $board[0]['score'] : 0;

// Find "My" Result (if logged in)
$myRank = null;
$myScore = 0;
$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    foreach ($board as $index => $row) {
        if ($row['student_id'] == $userId) {
            $myRank = $index + 1;
            $myScore = $row['score'];
            break;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard | <?= htmlspecialchars($s['session_code']) ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/leaderboard.css">
</head>
<body>

    <div class="leaderboard-container">
        <div class="board-card">
            
            <div class="board-header">
                <i class="fas fa-trophy trophy-icon"></i>
                <h1>Final Standings</h1>
                <p>Session Code: <strong><?= htmlspecialchars($s['session_code']) ?></strong></p>
            </div>

            <div class="stats-container">
                
                <?php if ($myRank): ?>
                    <div class="my-result-card">
                        <div>
                            <span class="my-label">Your Score</span>
                            <div class="my-score"><?= (int)$myScore ?> pts</div>
                        </div>
                        <div class="my-rank-circle">
                            #<?= $myRank ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-title">Participants</div>
                        <div class="stat-number">
                            <i class="fas fa-users" style="color: var(--primary); opacity: 0.7;"></i> 
                            <?= $totalPlayers ?>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-title">Highest Score</div>
                        <div class="stat-number">
                            <i class="fas fa-crown" style="color: #fbbf24;"></i> 
                            <?= (int)$topScore ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ranking-list">
                <?php if (!$board): ?>
                    <div class="empty-state">
                        <i class="far fa-sad-tear" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 10px;"></i>
                        <p>No participants have scored yet.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $rank = 1; 
                    foreach ($board as $row): 
                        // Determine styling for Medals
                        $rankClass = '';
                        $icon = '<span style="font-size: 0.9rem; color: #94a3b8;">#' . $rank . '</span>';
                        
                        if ($rank === 1) { 
                            $rankClass = 'gold'; 
                            $icon = '<i class="fas fa-crown"></i>'; 
                        }
                        elseif ($rank === 2) { 
                            $rankClass = 'silver'; 
                            $icon = '<i class="fas fa-medal"></i>'; 
                        }
                        elseif ($rank === 3) { 
                            $rankClass = 'bronze'; 
                            $icon = '<i class="fas fa-medal"></i>'; 
                        }
                        
                        // Highlight current user
                        $isMe = ($userId && $row['student_id'] == $userId);
                    ?>
                        <div class="rank-item <?= $rankClass ?>" style="<?= $isMe ? 'border-color: var(--primary); background: #eff6ff;' : '' ?>">
                            <div class="rank-pos"><?= $icon ?></div>
                            <div class="rank-name">
                                <?= htmlspecialchars($row['name']) ?>
                                <?php if($isMe): ?>
                                    <span style="font-size: 0.75rem; color: var(--primary); font-weight: 700; margin-left: 6px; text-transform: uppercase;">(You)</span>
                                <?php endif; ?>
                            </div>
                            <div class="rank-score"><?= (int)$row['score'] ?> pts</div>
                        </div>
                    <?php 
                    $rank++;
                    endforeach; 
                    ?>
                <?php endif; ?>
            </div>

            <div class="footer-actions">
                <a href="../../pages/student/history.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to History
                </a>
            </div>

        </div>
    </div>

</body>
</html>