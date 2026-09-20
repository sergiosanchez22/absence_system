<?php

function getTotalEmployees(PDO $db): int {
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'employee' AND is_active = TRUE");
    return (int)$stmt->fetchColumn();
}

function getAbsentCountOnDate(PDO $db, string $date): int {
    $stmt = $db->prepare(
        "SELECT COUNT(DISTINCT user_id) FROM absences
         WHERE status = 'approved' AND start_date <= ? AND end_date >= ?"
    );
    $stmt->execute([$date, $date]);
    return (int)$stmt->fetchColumn();
}

// Checks every day in a proposed range and flags any day that would cross the threshold
// if this request were approved (existing approved absences + this one).
function wouldExceedCapacity(PDO $db, string $startDate, string $endDate, float $thresholdPercent = 50.0): array {
    $total = getTotalEmployees($db);
    if ($total === 0) {
        return ['exceeds' => false, 'days' => []];
    }

    $exceedingDays = [];
    $period = new DatePeriod(
        new DateTime($startDate),
        new DateInterval('P1D'),
        (new DateTime($endDate))->modify('+1 day') // DatePeriod end is exclusive
    );

    foreach ($period as $date) {
        $d = $date->format('Y-m-d');
        $countIncludingThis = getAbsentCountOnDate($db, $d) + 1;
        $percent = ($countIncludingThis / $total) * 100;

        if ($percent > $thresholdPercent) {
            $exceedingDays[] = [
                'date' => $d,
                'count' => $countIncludingThis,
                'total' => $total,
                'percent' => round($percent, 1),
            ];
        }
    }

    return ['exceeds' => !empty($exceedingDays), 'days' => $exceedingDays];
}