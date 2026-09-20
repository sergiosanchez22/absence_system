<?php
require_once 'includes/auth_check.php';
requireRole('director');
require_once 'config/Database.php';

$error = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $department = trim($_POST['department'] ?? '');
    $dailyHours = $_POST['standard_daily_hours'] ?? 8;

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields except department are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif (!in_array($role, ['employee', 'director'], true)) {
        $error = "Invalid role.";
    } elseif (!is_numeric($dailyHours) || $dailyHours <= 0 || $dailyHours > 24) {
        $error = "Standard daily hours must be a valid number between 0 and 24.";
    } else {
        $db = (new Database())->connect();

        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = "A user with that email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare(
                "INSERT INTO users (name, email, password_hash, role, department, standard_daily_hours) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $email, $hashed, $role, $department, $dailyHours]);
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Register User</title></head>
<body>
    <a href="dashboard_director.php">Back to dashboard</a>
    <h2>Register New User</h2>

    <?php if ($success): ?>
        <p style="color:green;">User created successfully. <a href="register_user.php">Add another</a></p>
    <?php else: ?>
        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Full name: <input type="text" name="name" required></label><br><br>
            <label>Email: <input type="email" name="email" required></label><br><br>
            <label>Temporary password: <input type="text" name="password" required minlength="8"></label><br><br>
            <label>Department: <input type="text" name="department"></label><br><br>
            <label>Standard daily hours: <input type="number" name="standard_daily_hours" step="0.5" value="8" required></label><br><br>
            <label>Role:
                <select name="role" required>
                    <option value="employee">Employee</option>
                    <option value="director">Director</option>
                </select>
            </label><br><br>
            <button type="submit">Create User</button>
        </form>
    <?php endif; ?>
</body>
</html>