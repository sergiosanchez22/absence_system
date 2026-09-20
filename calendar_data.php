<?php
require_once 'includes/auth_check.php';
requireLogin(); // both roles can view; only director needs edit rights, which we're not adding yet
require_once 'config/Database.php';

header('Content-Type: application/json');

$db = (new Database())->connect();
$stmt = $db->query(
    "SELECT absences.id, absences.start_date, absences.end_date, absences.type,
            users.name AS employee_name
     FROM absences
     JOIN users ON absences.user_id = users.id
     WHERE absences.status = 'approved'"
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$colors = [
    'sick' => '#e74c3c',
    'vacation' => '#3498db',
    'personal' => '#9b59b6',
    'other' => '#95a5a6',
];

$events = [];
foreach ($rows as $row) {
    // FullCalendar's 'end' is exclusive, so add one day to make the last day fully shown
    $endExclusive = date('Y-m-d', strtotime($row['end_date'] . ' +1 day'));

    $events[] = [
        'title' => $row['employee_name'] . ' (' . ucfirst($row['type']) . ')',
        'start' => $row['start_date'],
        'end' => $endExclusive,
        'color' => $colors[$row['type']] ?? '#95a5a6',
    ];
}

echo json_encode($events);