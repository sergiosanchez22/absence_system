<?php
require_once 'includes/auth_check.php';
requireRole('director');
require_once 'config/Database.php';

$db = (new Database())->connect();
$stmt = $db->query("SELECT * FROM users ORDER BY role, name");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = $_SESSION['user_delete_error'] ?? '';
unset($_SESSION['user_delete_error']);
?>
<!DOCTYPE html>
<html>
<head><title>Manage Users</title></head>
<body>
    <a href="dashboard_director.php">Back to dashboard</a> | <a href="register_user.php">Register new user</a>
    <h2>Manage Users</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="6">
        <tr>
            <th>Name</th><th>Email</th><th>Role</th><th>Department</th><th>Status</th><th>Action</th>
        </tr>
        <?php foreach ($users as $u): ?>
<tr>
    <td><?= htmlspecialchars($u['name']) ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>
    <td><?= htmlspecialchars($u['role']) ?></td>
    <td><?= htmlspecialchars($u['department']) ?></td>
    <td><?= $u['is_active'] ? 'Active' : 'Inactive' ?></td>
    <td>
        <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
            <form method="POST" action="toggle_user_active.php" style="display:inline;">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <?php if ($u['is_active']): ?>
                    <button type="submit" onclick="return confirm('Deactivate this user?');">Deactivate</button>
                <?php else: ?>
                    <button type="submit">Reactivate</button>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <em>(you)</em>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
    </table>
</body>
</html>