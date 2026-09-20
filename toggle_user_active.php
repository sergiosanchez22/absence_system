<?php
require_once 'includes/auth_check.php';
requireRole('director');
require_once 'config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $db = (new Database())->connect();

    if ($id !== null && (int)$id !== (int)$_SESSION['user_id']) {
        $stmt = $db->prepare("SELECT role, is_active FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($target) {
            // Guard: can't deactivate the last remaining active director
            if ($target['role'] === 'director' && $target['is_active']) {
                $countStmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'director' AND is_active = TRUE");
                if ((int)$countStmt->fetchColumn() <= 1) {
                    $_SESSION['user_delete_error'] = "Cannot deactivate the only remaining active director.";
                    header("Location: manage_users.php");
                    exit;
                }
            }

            $newStatus = $target['is_active'] ? 0 : 1;
            $update = $db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $update->execute([$newStatus, $id]);
        }
    }
}

header("Location: manage_users.php");
exit;