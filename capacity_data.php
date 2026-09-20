<?php
require_once 'includes/auth_check.php';
requireLogin();
require_once 'config/Database.php';
require_once 'includes/capacity.php';

header('Content-Type: application/json');
$db = (new Database())->connect();
$total = getTotalEmployees($db);
$threshold = 50.0;

$stmt = $db->query("SELECT MIN(start_date) AS min_d, MAX(end_date) AS max_d FROM absences WHERE status = 'approved'");
$range = $stmt->fetch(PDO::FETCH_ASSOC);

$events = [];
if ($range['min_d'] && $range['max_d'] && $total > 0) {
    $period = new DatePeriod(
        new DateTime($range['min_d']),
        new DateInterval('P1D'),
        (new DateTime($range['max_d']))->modify('+1 day')
    );
    foreach ($period as $date) {
        $d = $date->format('Y-m-d');
        $percent = (getAbsentCountOnDate($db, $d) / $total) * 100;
        if ($percent > $threshold) {
            $events[] = [
                'start' => $d,
                'end' => date('Y-m-d', strtotime($d . ' +1 day')),
                'display' => 'background',
                'color' => '#ff4d4d',
            ];
        }
    }
}
echo json_encode($events);