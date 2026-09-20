<?php
require_once 'includes/auth_check.php';
requireRole('employee');
require_once 'config/Database.php';

$db = (new Database())->connect();

$stmt = $db->prepare("SELECT * FROM absences WHERE user_id = ? ORDER BY submitted_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$myAbsences = $stmt->fetchAll(PDO::FETCH_ASSOC);

$balStmt = $db->prepare("SELECT pto_balance_hours FROM users WHERE id = ?");
$balStmt->execute([$_SESSION['user_id']]);
$ptoBalance = $balStmt->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head><title>Employee Dashboard</title></head>
<body>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h2>
    <p><strong>PTO balance:</strong> <?= number_format($ptoBalance, 1) ?> hours</p>

    <a href="submit_absence.php">Submit a new absence request</a> |
    <a href="calendar.php">View absence calendar</a> |
    <a href="change_password.php">Change password</a> |
    <a href="logout.php">Log out</a>

    <h3>My Requests</h3>
    <table border="1" cellpadding="6">
        <tr>
            <th>Start</th><th>End</th><th>Type</th><th>Reason</th><th>Hours</th><th>Status</th><th>Action</th>
        </tr>
        <?php foreach ($myAbsences as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['start_date']) ?></td>
            <td><?= htmlspecialchars($a['end_date']) ?></td>
            <td><?= htmlspecialchars($a['type']) ?></td>
            <td><?= htmlspecialchars($a['reason']) ?></td>
            <td><?= htmlspecialchars($a['hours_missed']) ?></td>
            <td><?= htmlspecialchars($a['status']) ?></td>
            <td>
                <?php if ($a['status'] === 'pending'): ?>
                <form method="POST" action="delete_absence.php" onsubmit="return confirm('Delete this request?');" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <input type="hidden" name="redirect_to" value="dashboard_employee">
                    <button type="submit">Delete</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>