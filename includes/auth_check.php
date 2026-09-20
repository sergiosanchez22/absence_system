<?php
session_start();

function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /absence-system/login.php");
        exit;
    }
}

function requireRole(string $role): void {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        die("Access denied.");
    }
}