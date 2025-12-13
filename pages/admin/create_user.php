<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once __DIR__ . '/../../classes/User.php';
$userModel = new User();

$csrfPath = __DIR__ . '/../../includes/csrf.php';
if (file_exists($csrfPath)) require_once $csrfPath;

$errors = [];
$name=''; $email=''; $role='student';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !csrf_check($_POST['csrf_token'])) die('Invalid CSRF token');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pwd = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? 'student',['admin','teacher','student'])?$_POST['role']:'student';
    if ($name==='') $errors['name']='Required';
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors['email']='Valid email required';
    if (strlen($pwd) < 6) $errors['password']='Password min 6';
    if (empty($errors)) {
        $uid = $userModel->register($name,$email,$pwd,$role);
        if ($uid) header('Location: manage_users.php'); else $errors['general']='Failed';
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Create User</title></head>
<body>
<h1>Create User</h1>
<?php if (!empty($errors['general'])): ?><p style="color:red"><?= htmlspecialchars($errors['general']) ?></p><?php endif; ?>
<form method="post" action="">
    <?= csrf_field() ?>
    <label>Name: <input type="text" name="name" value="<?= htmlspecialchars($name) ?>"></label><br>
    <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($email) ?>"></label><br>
    <label>Password: <input type="password" name="password"></label><br>
    <label>Role:
        <select name="role">
            <option value="student" <?= $role==='student'?'selected':'' ?>>Student</option>
            <option value="teacher" <?= $role==='teacher'?'selected':'' ?>>Teacher</option>
            <option value="admin" <?= $role==='admin'?'selected':'' ?>>Admin</option>
        </select>
    </label><br>
    <button type="submit">Create</button>
</form>
<p><a href="manage_users.php">Back</a></p>
</body>
</html>