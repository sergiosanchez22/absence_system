<?php

// Counts weekdays (Mon-Fri) between two dates, inclusive.
function countWeekdays(string $startDate, string $endDate): int {
    $count = 0;
    $period = new DatePeriod(
        new DateTime($startDate),
        new DateInterval('P1D'),
        (new DateTime($endDate))->modify('+1 day')
    );
    foreach ($period as $date) {
        $dayOfWeek = (int)$date->format('N'); // 1 = Monday ... 7 = Sunday
        if ($dayOfWeek <= 5) {
            $count++;
        }
    }
    return $count;
}

function calculateHoursMissed(
    bool $isFullDay,
    string $startDate,
    string $endDate,
    float $dailyHours,
    ?string $startTime = null,
    ?string $endTime = null
): float {
    if ($isFullDay) {
        $weekdays = countWeekdays($startDate, $endDate);
        return $weekdays * $dailyHours;
    } else {
        $start = new DateTime($startTime);
        $end = new DateTime($endTime);
        $diffHours = ($end->getTimestamp() - $start->getTimestamp()) / 3600;
        return max(0, round($diffHours, 2));
    }
}