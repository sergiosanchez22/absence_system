<?php
require_once 'includes/auth_check.php';
requireRole('director');
require_once 'config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $decision = $_POST['decision'] ?? '';

    if (in_array($decision, ['approved', 'denied'], true) && $id !== null) {
        $db = (new Database())->connect();

        $reqStmt = $db->prepare("SELECT user_id, hours_missed, status FROM absences WHERE id = ?");
        $reqStmt->execute([$id]);
        $absence = $reqStmt->fetch(PDO::FETCH_ASSOC);

        if ($absence) {
            $stmt = $db->prepare(
                "UPDATE absences SET status = ?, decided_at = NOW(), decided_by = ? WHERE id = ?"
            );
            $stmt->execute([$decision, $_SESSION['user_id'], $id]);

            if ($decision === 'approved' && $absence['status'] !== 'approved') {
                $balanceStmt = $db->prepare("UPDATE users SET pto_balance_hours = pto_balance_hours - ? WHERE id = ?");
                $balanceStmt->execute([$absence['hours_missed'], $absence['user_id']]);
            }
        }
    }
}

header("Location: review_requests.php");
exit;