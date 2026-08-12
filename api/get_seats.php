<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/app.php';

$showId = get_positive_int($_GET, 'show_id');
if (!$showId) {
    json_response(['success' => false, 'message' => 'Invalid show ID.'], 422);
}

try {
    $pdo->beginTransaction();
    cleanup_expired_holds($pdo);

    $stmt = $pdo->prepare("\
        SELECT bs.seat_no\
        FROM booking_seats bs\
        JOIN bookings b ON b.id = bs.booking_id\
        WHERE bs.show_id = ?\
          AND (bs.status = 'CONFIRMED' OR (bs.status = 'HELD' AND b.status = 'PENDING' AND b.expires_at > NOW()))\
        ORDER BY bs.seat_no ASC\
    ");
    $stmt->execute([$showId]);
    $seats = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $pdo->commit();
    json_response(['success' => true, 'occupied_seats' => $seats]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('get_seats error: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Unable to fetch seats.'], 500);
}
