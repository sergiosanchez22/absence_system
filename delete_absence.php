<?php
require_once 'includes/auth_check.php';
requireLogin();
require_once 'config/Database.php';

$allowedRedirects = [
    'review_requests' => 'review_requests.php',
    'all_absences' => 'all_absences.php',
    'dashboard_employee' => 'dashboard_employee.php',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id !== null) {
        $db = (new Database())->connect();

        if ($_SESSION['role'] === 'director') {
            $reqStmt = $db->prepare("SELECT user_id, hours_missed, status FROM absences WHERE id = ?");
            $reqStmt->execute([$id]);
            $absence = $reqStmt->fetch(PDO::FETCH_ASSOC);

            if ($absence && $absence['status'] === 'approved') {
                $restoreStmt = $db->prepare("UPDATE users SET pto_balance_hours = pto_balance_hours + ? WHERE id = ?");
                $restoreStmt->execute([$absence['hours_missed'], $absence['user_id']]);
            }

            $stmt = $db->prepare("DELETE FROM absences WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            $stmt = $db->prepare(
                "DELETE FROM absences WHERE id = ? AND user_id = ? AND status = 'pending'"
            );
            $stmt->execute([$id, $_SESSION['user_id']]);
        }
    }
}

$requestedRedirect = $_POST['redirect_to'] ?? '';
$destination = $allowedRedirects[$requestedRedirect] ?? null;

if ($destination === null) {
    $destination = $_SESSION['role'] === 'director' ? 'review_requests.php' : 'dashboard_employee.php';
}

header("Location: " . $destination);
exit;