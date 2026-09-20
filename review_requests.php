<?php
require_once 'includes/auth_check.php';
requireRole('director');
require_once 'config/Database.php';
require_once 'includes/capacity.php';

$db = (new Database())->connect();
$stmt = $db->query(
    "SELECT absences.*, users.name AS employee_name
     FROM absences
     JOIN users ON absences.user_id = users.id
     WHERE absences.status = 'pending'
     ORDER BY absences.submitted_at ASC"
);
$pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><title>Review Absence Requests</title></head>
<body>
    <h2>Pending Requests</h2>
    <a href="dashboard_director.php">Back to dashboard</a> | <a href="logout.php">Log out</a>

    <table border="1" cellpadding="6">
        <tr>
            <th>Employee</th><th>Start</th><th>End</th><th>Type</th><th>Reason</th><th>Action</th>
        </tr>
        <?php foreach ($pending as $req): ?>
            <?php $capacity = wouldExceedCapacity($db, $req['start_date'], $req['end_date']); ?>
<td>
    <?php if ($capacity['exceeds']): ?>
        <span style="color:red; font-weight:bold;">⚠️ Would exceed 50% staff absent:</span>
        <ul>
        <?php foreach ($capacity['days'] as $day): ?>
            <li><?= $day['date'] ?> — <?= $day['count'] ?>/<?= $day['total'] ?> (<?= $day['percent'] ?>%)</li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</td>
        <tr>
            <td><?= htmlspecialchars($req['employee_name']) ?></td>
            <td><?= htmlspecialchars($req['start_date']) ?></td>
            <td><?= htmlspecialchars($req['end_date']) ?></td>
            <td><?= htmlspecialchars($req['type']) ?></td>
            <td><?= htmlspecialchars($req['reason']) ?></td>
            <td>
                <form method="POST" action="update_status.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $req['id'] ?>">
                    <input type="hidden" name="decision" value="approved">
                    <button type="submit">Approve</button>
                </form>
                <form method="POST" action="update_status.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $req['id'] ?>">
                    <input type="hidden" name="decision" value="denied">
                    <button type="submit">Deny</button>
                </form>
                <form method="POST" action="delete_absence.php" onsubmit="return confirm('Delete this request?');" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $req['id'] ?>">
                    <input type="hidden" name="redirect_to" value="review_requests">
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($pending)): ?>
        <tr><td colspan="6">No pending requests.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>