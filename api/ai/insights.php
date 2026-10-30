<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth_guard.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';

start_eventspace_session();
header('Content-Type: application/json; charset=utf-8');
$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Sign in to view insights.']);
    exit;
}

$preferredVenues = [];
$emailStmt = $conn->prepare('SELECT email FROM users WHERE username = ? LIMIT 1');
if ($emailStmt) {
    $emailStmt->bind_param('s', $user['username']);
    $emailStmt->execute();
    $emailRow = $emailStmt->get_result()->fetch_assoc();
    $emailStmt->close();
    $email = (string) ($emailRow['email'] ?? '');
    if ($email !== '') {
        $venueStmt = $conn->prepare("SELECT e.Venue, COUNT(*) AS total FROM event_rsvps r INNER JOIN Event e ON e.EventID = r.EventID WHERE r.Guest_Email = ? AND r.RSVP_Status = 'Confirmed' GROUP BY e.Venue ORDER BY total DESC LIMIT 3");
        if ($venueStmt) {
            $venueStmt->bind_param('s', $email);
            $venueStmt->execute();
            $venueResult = $venueStmt->get_result();
            while ($venue = $venueResult->fetch_assoc()) {
                $preferredVenues[] = strtolower((string) $venue['Venue']);
            }
            $venueStmt->close();
        }
    }
}

$recommendations = [];
$result = $conn->query(
    "SELECT e.EventID, e.Name, e.Date_OF_Booking, e.Time, e.Venue, e.Budget, e.Capacity,
        COUNT(r.RSVP_ID) AS registrations,
        SUM(CASE WHEN r.RSVP_Status = 'Confirmed' THEN 1 ELSE 0 END) AS confirmed
     FROM Event e
     LEFT JOIN event_rsvps r ON r.EventID = e.EventID
     WHERE e.Date_OF_Booking >= CURDATE()
     GROUP BY e.EventID, e.Name, e.Date_OF_Booking, e.Time, e.Venue, e.Budget, e.Capacity
     ORDER BY confirmed DESC, e.Date_OF_Booking ASC
     LIMIT 6"
);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $confirmed = (int) $row['confirmed'];
        $capacity = $row['Capacity'] === null ? null : (int) $row['Capacity'];
        $fillRate = $capacity && $capacity > 0 ? round(($confirmed / $capacity) * 100) : null;
        $row['confirmed'] = $confirmed;
        $row['registrations'] = (int) $row['registrations'];
        $row['fill_rate'] = $fillRate;
        $row['personal_match'] = in_array(strtolower((string) $row['Venue']), $preferredVenues, true);
        $row['signal'] = $fillRate !== null && $fillRate >= 80 ? 'Popular' : ($fillRate !== null && $fillRate < 20 ? 'Needs promotion' : 'On track');
        $recommendations[] = $row;
    }
}
usort($recommendations, static function (array $left, array $right): int {
    return (int) $right['personal_match'] <=> (int) $left['personal_match'];
});

$sentiment = [];
$sentimentResult = $conn->query("SELECT Sentiment, COUNT(*) AS total FROM event_feedback GROUP BY Sentiment ORDER BY total DESC");
if ($sentimentResult) {
    while ($row = $sentimentResult->fetch_assoc()) {
        $sentiment[] = ['label' => $row['Sentiment'], 'total' => (int) $row['total']];
    }
}

$averageAttendance = 0;
$attendanceResult = $conn->query("SELECT AVG(attendance_count) AS average_attendance FROM (SELECT EventID, COUNT(*) AS attendance_count FROM event_rsvps WHERE RSVP_Status = 'Confirmed' GROUP BY EventID) attendance");
if ($attendanceResult) {
    $averageAttendance = round((float) ($attendanceResult->fetch_assoc()['average_attendance'] ?? 0), 1);
}

echo json_encode([
    'recommendations' => $recommendations,
    'sentiment' => $sentiment,
    'average_attendance' => $averageAttendance,
    'generated_at' => date(DATE_ATOM),
]);
