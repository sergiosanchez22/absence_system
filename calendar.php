<?php
require_once 'includes/auth_check.php';
requireLogin();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Absence Calendar</title>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <style>
        #calendar { max-width: 1000px; margin: 20px auto; }
    </style>
</head>
<body>
    <?php if ($_SESSION['role'] === 'director'): ?>
        <a href="dashboard_director.php">Back to dashboard</a>
    <?php else: ?>
        <a href="dashboard_employee.php">Back to dashboard</a>
    <?php endif; ?>

    <div id="calendar"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                eventSources: [
    { url: 'calendar_data.php' },
    { url: 'capacity_data.php' }
],
                height: 'auto',
            });
            calendar.render();
        });
    </script>
</body>
</html>