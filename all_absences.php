<?php
require_once 'includes/auth_check.php';
requireRole('director');
require_once 'config/Database.php';

$db = (new Database())->connect();
$stmt = $db->query(
    "SELECT absences.*, users.name AS employee_name
     FROM absences
     JOIN users ON absences.user_id = users.id
     ORDER BY absences.start_date DESC"
);
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><title>All Absences</title></head>
<body>
    <h2>All Absences</h2>
    <a href="dashboard_director.php">Back to dashboard</a> | <a href="review_requests.php">Pending requests</a> | <a href="logout.php">Log out</a>

    <table border="1" cellpadding="6">
        <tr>
            <th>Employee</th><th>Start</th><th>End</th><th>Type</th><th>Reason</th><th>Status</th><th>Action</th>
        </tr>
        <?php foreach ($all as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['employee_name']) ?></td>
            <td><?= htmlspecialchars($a['start_date']) ?></td>
            <td><?= htmlspecialchars($a['end_date']) ?></td>
            <td><?= htmlspecialchars($a['type']) ?></td>
            <td><?= htmlspecialchars($a['reason']) ?></td>
            <td><?= htmlspecialchars($a['status']) ?></td>
            <td>
                <form method="POST" action="delete_absence.php" onsubmit="return confirm('Delete this absence?');" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <input type="hidden" name="redirect_to" value="all_absences">
                     <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($all)): ?>
        <tr><td colspan="7">No absences on record.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>