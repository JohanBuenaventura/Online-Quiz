<?php
session_start();

// 1. Security Check (Admin Only)
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Quiz.php';

$userModel = new User();
$quizModel = new Quiz();

// 2. Fetch Data
$users = $userModel->getAllUsers();
$quizzes = $quizModel->getAllQuizzes();

// 3. KPI Logic: Calculate Growth
function calculateGrowth($dataArray) {
    $currentMonth = date('Y-m'); // e.g., "2023-10"
    $lastMonth = date('Y-m', strtotime('-1 month')); // e.g., "2023-09"

    $currentCount = 0;
    $lastCount = 0;

    foreach ($dataArray as $item) {
        // Ensure created_at exists
        if (!isset($item['created_at'])) continue;
        
        $dateStr = substr($item['created_at'], 0, 7); // Extract "YYYY-MM"
        
        if ($dateStr === $currentMonth) {
            $currentCount++;
        } elseif ($dateStr === $lastMonth) {
            $lastCount++;
        }
    }

    // Logic to determine percentage
    if ($lastCount == 0) {
        // Avoid division by zero
        if ($currentCount > 0) return ['type' => 'up', 'text' => 'New growth', 'pct' => 100];
        return ['type' => 'neutral', 'text' => 'No change', 'pct' => 0];
    }

    $diff = $currentCount - $lastCount;
    $percent = ($diff / $lastCount) * 100;
    
    return [
        'type' => ($percent >= 0) ? 'up' : 'down',
        'text' => 'vs last month',
        'pct' => round(abs($percent), 1)
    ];
}

// Calculate Trends
$userTrend = calculateGrowth($users);
$quizTrend = calculateGrowth($quizzes);

// Role Counts
$teachers = array_filter($users, function($u){ return $u['role']==='teacher'; });
$students = array_filter($users, function($u){ return $u['role']==='student'; });
$admins = array_filter($users, function($u){ return $u['role']==='admin'; });

// Teacher Trend
$teacherTrend = calculateGrowth($teachers);
$studentTrend = calculateGrowth($students);

// 4. Chart Data Preparation
$quiz_counts = [];
foreach ($quizzes as $q) {
    $t_name = $q['teacher_name'] ?? 'Unknown';
    if (!isset($quiz_counts[$t_name])) {
        $quiz_counts[$t_name] = 0;
    }
    $quiz_counts[$t_name]++;
}
arsort($quiz_counts);

$chartLabels = array_keys($quiz_counts);
$chartData = array_values($quiz_counts);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Reports | Admin Panel</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <link rel="stylesheet" href="../../assets/css/dashboard-pro.css">
    <link rel="stylesheet" href="../../assets/css/mobileres.css">
    

    <style>
        .charts-wrapper {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            position: relative;
        }
        
        /* Dynamic Trend Colors */
        .trend-indicator {
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 5px;
        }
        .trend-up { color: #16a34a; }      /* Green */
        .trend-down { color: #ef4444; }    /* Red */
        .trend-neutral { color: #94a3b8; } /* Grey */

        @media (max-width: 1000px) {
            .charts-wrapper { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-shield-alt"></i> Admin Panel
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
            <li><a href="manage_quizzes.php"><i class="fas fa-layer-group"></i> All Quizzes</a></li>
            <li><a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> System Reports</a></li>
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
                    <h2 style="color: var(--text-main); margin-bottom: 5px;">Analytics Dashboard</h2>
                    <p style="color: #64748b; font-size: 0.9rem;">Real-time platform statistics and reports.</p>
                </div>
                
                <div class="export-buttons" style="display: flex; gap: 10px;">
                    <a href="reports_export.php?type=users" class="btn-outline" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 15px; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
                        <i class="fas fa-file-csv" style="color: #16a34a;"></i> Export Users
                    </a>
                    <a href="reports_export.php?type=quizzes" class="btn-outline" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 15px; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
                        <i class="fas fa-file-csv" style="color: #2563eb;"></i> Export Quizzes
                    </a>
                </div>
            </div>

            <!-- DYNAMIC REPORTS GRID -->
            <div class="reports-grid">
                
                <!-- Total Users -->
                <div class="report-card">
                    <div class="report-icon icon-blue"><i class="fas fa-users"></i></div>
                    <div class="report-data">
                        <h3><?= count($users) ?></h3>
                        <p>Total Users</p>
                        <span class="trend-indicator trend-<?= $userTrend['type'] ?>">
                            <i class="fas fa-arrow-<?= $userTrend['type'] ?>"></i> 
                            <?= $userTrend['pct'] ?>% <?= $userTrend['text'] ?>
                        </span>
                    </div>
                </div>

                <!-- Total Quizzes -->
                <div class="report-card">
                    <div class="report-icon icon-purple"><i class="fas fa-file-alt"></i></div>
                    <div class="report-data">
                        <h3><?= count($quizzes) ?></h3>
                        <p>Total Quizzes</p>
                        <span class="trend-indicator trend-<?= $quizTrend['type'] ?>">
                            <i class="fas fa-arrow-<?= $quizTrend['type'] ?>"></i> 
                            <?= $quizTrend['pct'] ?>% <?= $quizTrend['text'] ?>
                        </span>
                    </div>
                </div>

                <!-- Teachers -->
                <div class="report-card">
                    <div class="report-icon icon-green"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="report-data">
                        <h3><?= count($teachers) ?></h3>
                        <p>Teachers</p>
                        <span class="trend-indicator trend-<?= $teacherTrend['type'] ?>">
                            <i class="fas fa-arrow-<?= $teacherTrend['type'] ?>"></i> 
                            <?= $teacherTrend['pct'] ?>% <?= $teacherTrend['text'] ?>
                        </span>
                    </div>
                </div>

                <!-- Students -->
                <div class="report-card">
                    <div class="report-icon icon-orange"><i class="fas fa-user-graduate"></i></div>
                    <div class="report-data">
                        <h3><?= count($students) ?></h3>
                        <p>Students</p>
                        <span class="trend-indicator trend-<?= $studentTrend['type'] ?>">
                            <i class="fas fa-arrow-<?= $studentTrend['type'] ?>"></i> 
                            <?= $studentTrend['pct'] ?>% <?= $studentTrend['text'] ?>
                        </span>
                    </div>
                </div>

            </div>

            <div class="charts-wrapper">
                <div class="chart-card">
                    <h4 style="margin-bottom: 20px; color: var(--text-main);">Top Quiz Creators</h4>
                    <canvas id="teacherChart"></canvas>
                </div>
                <div class="chart-card">
                    <h4 style="margin-bottom: 20px; color: var(--text-main);">User Distribution</h4>
                    <canvas id="userChart" style="max-height: 300px;"></canvas>
                </div>
            </div>

            <h3 style="color: var(--text-main); margin-bottom: 15px;">Teacher Performance Detail</h3>
            
            <div class="card-table">
                <div class="table-responsive">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Teacher Name</th>
                                <th>Quizzes Created</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            foreach ($quiz_counts as $teacher => $count): 
                            ?>
                                <tr>
                                    <td>#<?= $i++ ?></td>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($teacher) ?></td>
                                    <td><?= (int)$count ?> Quizzes</td>
                                    <td><span class="status-badge status-success">Active</span></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($quiz_counts)): ?>
                                <tr><td colspan="4" style="text-align:center; padding: 20px;">No data available</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <script>
        const teacherLabels = <?= json_encode($chartLabels) ?>;
        const teacherData = <?= json_encode($chartData) ?>;
        const userCounts = [<?= count($teachers) ?>, <?= count($students) ?>, <?= count($admins) ?>];

        const ctx1 = document.getElementById('teacherChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: teacherLabels,
                datasets: [{
                    label: '# of Quizzes',
                    data: teacherData,
                    backgroundColor: '#3a86ff',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        const ctx2 = document.getElementById('userChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Teachers', 'Students', 'Admins'],
                datasets: [{
                    data: userCounts,
                    backgroundColor: ['#10b981', '#f59e0b', '#6366f1'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
    <script src="../../assets/js/nav.js"></script>
</body>
</html>