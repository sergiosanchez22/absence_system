<?php
require_once 'includes/auth_check.php';
requireRole('employee');
require_once 'config/Database.php';
require_once 'includes/hours_calc.php';

$error = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $type = $_POST['type'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    $dayType = $_POST['day_type'] ?? 'full';
    $isFullDay = ($dayType === 'full');
    $startTime = $_POST['start_time'] ?? null;
    $endTime = $_POST['end_time'] ?? null;

    if (empty($startDate) || empty($endDate) || empty($type) || empty($reason)) {
        $error = "All fields are required.";
    } elseif (strtotime($endDate) < strtotime($startDate)) {
        $error = "End date cannot be before start date.";
    } elseif (!$isFullDay) {
        if (empty($startTime) || empty($endTime)) {
            $error = "Please provide both a leave time and return time for a partial day.";
        } elseif ($startDate !== $endDate) {
            $error = "Partial-day requests must have the same start and end date.";
        } elseif (strtotime($endTime) <= strtotime($startTime)) {
            $error = "Return time must be after leave time.";
        }
    }

    if (empty($error)) {
        $db = (new Database())->connect();

        $userStmt = $db->prepare("SELECT standard_daily_hours FROM users WHERE id = ?");
        $userStmt->execute([$_SESSION['user_id']]);
        $dailyHours = (float)$userStmt->fetchColumn();

        $hoursMissed = calculateHoursMissed($isFullDay, $startDate, $endDate, $dailyHours, $startTime, $endTime);

        $stmt = $db->prepare(
            "INSERT INTO absences (user_id, start_date, end_date, reason, type, is_full_day, start_time, end_time, hours_missed)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $_SESSION['user_id'], $startDate, $endDate, $reason, $type,
            $isFullDay ? 1 : 0, $startTime, $endTime, $hoursMissed
        ]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Submit Absence Request</title></head>
<body>
    <h2>Submit Absence Request</h2>

    <?php if ($success): ?>
        <p style="color:green;">Request submitted! <a href="dashboard_employee.php">Back to dashboard</a></p>
    <?php else: ?>
        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Start date: <input type="date" name="start_date" required></label><br><br>
            <label>End date: <input type="date" name="end_date" required></label><br><br>
            <label>Type:
                <select name="type" required>
                    <option value="sick">Sick</option>
                    <option value="vacation">Vacation</option>
                    <option value="personal">Personal</option>
                    <option value="other">Other</option>
                </select>
            </label><br><br>

            <label><input type="radio" name="day_type" value="full" checked onchange="togglePartial()"> Full day(s)</label>
            <label><input type="radio" name="day_type" value="partial" onchange="togglePartial()"> Partial day</label><br><br>

            <div id="partial_fields" style="display:none;">
                <label>Leaving at: <input type="time" name="start_time"></label>
                <label>Returning at: <input type="time" name="end_time"></label>
                <p><em>Note: for a partial day, use the same date for start and end date above.</em></p>
            </div>

            <label>Reason:<br><textarea name="reason" rows="4" cols="40" required></textarea></label><br><br>
            <button type="submit">Submit</button>
        </form>

        <script>
        function togglePartial() {
            const isPartial = document.querySelector('input[name="day_type"]:checked').value === 'partial';
            document.getElementById('partial_fields').style.display = isPartial ? 'block' : 'none';
        }
        </script>
    <?php endif; ?>
</body>
</html>