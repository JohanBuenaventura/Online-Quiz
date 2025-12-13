<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403); echo 'Forbidden'; exit;
}

require_once __DIR__ . '/../../database.php';

function redirect_back($msg = null, $err = null) {
    if ($msg) $_SESSION['admin_msg'] = $msg;
    if ($err) $_SESSION['admin_err'] = $err;
    header('Location: maintenance.php'); exit;
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'clear_cache') {
    $tmp = __DIR__ . '/../../uploads/tmp';
    $deleted = 0;
    if (is_dir($tmp)) {
        foreach (glob($tmp . '/*') as $f) {
            if (is_file($f)) { @unlink($f); $deleted++; }
        }
    }
    redirect_back("Cleared {$deleted} temporary files.");

} elseif ($action === 'backup_db') {
    $backdir = __DIR__ . '/../../backups';
    if (!is_dir($backdir)) @mkdir($backdir, 0755, true);
    $db = new Database();
    $reflect = new ReflectionClass($db);
    $host = $reflect->getProperty('host'); $host->setAccessible(true); $host = $host->getValue($db);
    $user = $reflect->getProperty('username'); $user->setAccessible(true); $user = $user->getValue($db);
    $pass = $reflect->getProperty('password'); $pass->setAccessible(true); $pass = $pass->getValue($db);
    $name = $reflect->getProperty('dbname'); $name->setAccessible(true); $name = $name->getValue($db);
    $timestamp = date('Ymd_His');
    $file = $backdir . "/db_backup_{$timestamp}.sql";

    $mysqldump = null;
    if (PHP_OS_FAMILY === 'Windows') {
        $candidates = ['C:\\xampp\\mysql\\bin\\mysqldump.exe'];
        foreach ($candidates as $p) if (file_exists($p)) { $mysqldump = $p; break; }
        if (!$mysqldump) { $which = @shell_exec('where mysqldump 2>NUL'); if ($which) $mysqldump = trim(explode("\n", $which)[0]); }
    } else {
        $which = @shell_exec('which mysqldump 2>/dev/null'); if ($which) $mysqldump = trim($which);
    }

    if ($mysqldump && is_executable($mysqldump) && function_exists('shell_exec')) {
        $passArg = $pass !== '' ? "--password=" . escapeshellarg($pass) : '';
        $cmd = escapeshellarg($mysqldump) . " --host=" . escapeshellarg($host) . " --user=" . escapeshellarg($user) . " {$passArg} " . escapeshellarg($name) . " > " . escapeshellarg($file);
        @shell_exec($cmd);
        if (file_exists($file)) redirect_back("Database backup created: " . basename($file));
        redirect_back(null, 'Backup failed (mysqldump ran but output not created).');
    }

    try {
        $pdo = (new Database())->connect();
        $out = fopen($file, 'w');
        fwrite($out, "-- Backup created at " . date('c') . "\n\n");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
        foreach ($tables as $trow) {
            $table = $trow[0];
            fwrite($out, "DROP TABLE IF EXISTS `{$table}`;\n");
            $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            fwrite($out, $create['Create Table'] . ";\n\n");
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $cols = array_map(function($c){ return "`".$c."`"; }, array_keys($r));
                $vals = array_map(function($v){ if ($v === null) return 'NULL'; return "'".str_replace("'", "''", $v)."'"; }, array_values($r));
                fwrite($out, "INSERT INTO `{$table}` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n");
            }
            fwrite($out, "\n");
        }
        fclose($out);
        if (file_exists($file)) redirect_back("Database backup created: " . basename($file));
        redirect_back(null, 'Backup failed (fallback method).');
    } catch (Exception $e) {
        redirect_back(null, 'Backup error: ' . $e->getMessage());
    }

} elseif ($action === 'view_logs') {
    $elog = ini_get('error_log');
    if (!$elog || !file_exists($elog)) { $guess = 'C:\\xampp\\apache\\logs\\error.log'; if (file_exists($guess)) $elog = $guess; }
    if (!$elog || !file_exists($elog)) { redirect_back(null, 'No error log found'); }
    $size = filesize($elog); $tail = '';
    $fp = fopen($elog, 'r');
    if ($fp) { $seek = $size > 4000 ? -4000 : 0; fseek($fp, $seek, SEEK_END); $tail = stream_get_contents($fp); fclose($fp); }
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Error Log</title><link rel="stylesheet" href="../../assets/css/dashboard-pro.css"></head><body>';
    echo '<main class="main-content"><div class="container">';
    echo '<h1>Error Log (tail)</h1>';
    echo '<pre style="white-space:pre-wrap;background:#111;color:#eee;padding:12px;border-radius:6px;">' . htmlspecialchars($tail) . '</pre>';
    echo '<p><a href="maintenance.php">← Back</a></p>';
    echo '</div></main></body></html>';
    exit;

} elseif ($action === 'cleanup_backups') {
    $backdir = __DIR__ . '/../../backups'; $removed = 0; $kept = 0;
    if (is_dir($backdir)) { foreach (glob($backdir . '/*') as $f) { if (!is_file($f)) continue; if (filemtime($f) < (time() - 60*60*24*30)) { @unlink($f); $removed++; } else { $kept++; } } }
    redirect_back("Cleanup complete. Removed: {$removed}, Kept: {$kept}");

} else {
    redirect_back(null, 'Unknown action');
}
