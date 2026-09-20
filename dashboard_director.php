<?php
require_once 'includes/auth_check.php';
requireRole('director');
?>
<h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h2>

<a href="review_requests.php">Review pending requests</a> |
<a href="calendar.php">View Calendar</a> |
<a href="all_absences.php">View all absences</a> |
<a href="register_user.php">Register new user</a> |
<a href="manage_users.php">Manage users</a> |
<a href="change_password.php">Change password</a> |
<a href="logout.php">Log out</a>