<?php
require_once 'includes/auth_check.php';
requireLogin();
require_once 'config/Database.php';

$error = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($newPassword) < 8) {
        $error = "New password must be at least 8 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New password and confirmation do not match.";
    } elseif ($newPassword === $currentPassword) {
        $error = "New password must be different from your current password.";
    } else {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $update->execute([$newHash, $_SESSION['user_id']]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Change Password</title></head>
<body>
    <a href="<?= $_SESSION['role'] === 'director' ? 'dashboard_director.php' : 'dashboard_employee.php' ?>">Back to dashboard</a>
    <h2>Change Password</h2>

    <?php if ($success): ?>
        <p style="color:green;">Password updated successfully.</p>
    <?php else: ?>
        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Current password: <input type="password" name="current_password" required></label><br><br>
            <label>New password: <input type="password" name="new_password" required minlength="8"></label><br><br>
            <label>Confirm new password: <input type="password" name="confirm_password" required minlength="8"></label><br><br>
            <button type="submit">Update Password</button>
        </form>
    <?php endif; ?>
</body>
</html>