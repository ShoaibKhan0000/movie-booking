<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/app.php';

require_login();

if (!is_post_request()) {
    json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$rawInput = file_get_contents('php://input');
$parsed = json_decode($rawInput ?: '', true);
$payload = is_array($parsed) ? $parsed : $_POST;

$showId = get_positive_int($payload, 'show_id');
$selectedSeats = normalize_seat_list((array) ($payload['seats'] ?? []));

if (!$showId || $selectedSeats === []) {
    json_response(['success' => false, 'message' => 'Select a valid show and seats.'], 422);
}

if (count($selectedSeats) > 8) {
    json_response(['success' => false, 'message' => 'Maximum 8 seats are allowed per booking.'], 422);
}

$userId = (int) $_SESSION['user_id'];

try {
    $pdo->beginTransaction();
    cleanup_expired_holds($pdo);

    $showStmt = $pdo->prepare("\
        SELECT s.id, s.base_price\
        FROM shows s\
        WHERE s.id = ?\
        FOR UPDATE\
    ");
    $showStmt->execute([$showId]);
    $show = $showStmt->fetch();

    if (!$show) {
        throw new RuntimeException('Show not available.');
    }

    $placeholders = implode(',', array_fill(0, count($selectedSeats), '?'));
    $seatCheckParams = array_merge([$showId], $selectedSeats);

    $seatCheckStmt = $pdo->prepare("\
        SELECT seat_no\
        FROM booking_seats\
        WHERE show_id = ? AND seat_no IN ($placeholders)\
        FOR UPDATE\
    ");
    $seatCheckStmt->execute($seatCheckParams);
    $occupied = $seatCheckStmt->fetchAll(PDO::FETCH_COLUMN);

    if ($occupied !== []) {
        $message = 'These seats are already booked: ' . implode(', ', $occupied);
        throw new RuntimeException($message);
    }

    $seatCount = count($selectedSeats);
    $baseAmount = round(((float) $show['base_price']) * $seatCount, 2);
    $gstAmount = round($baseAmount * 0.18, 2);
    $totalAmount = round($baseAmount + $gstAmount, 2);
    $reservationToken = bin2hex(random_bytes(32));
    $expiresAt = (new DateTimeImmutable('+10 minutes'))->format('Y-m-d H:i:s');

    $insertBooking = $pdo->prepare("\
        INSERT INTO bookings (reservation_token, user_id, show_id, seats, seat_count, base_amount, gst_amount, total_amount, status, expires_at)\
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', ?)\
    ");
    $insertBooking->execute([
        $reservationToken,
        $userId,
        $showId,
        implode(',', $selectedSeats),
        $seatCount,
        $baseAmount,
        $gstAmount,
        $totalAmount,
        $expiresAt,
    ]);

    $bookingId = (int) $pdo->lastInsertId();
    $seatInsert = $pdo->prepare("INSERT INTO booking_seats (booking_id, show_id, seat_no, status) VALUES (?, ?, ?, 'HELD')");

    foreach ($selectedSeats as $seatNo) {
        $seatInsert->execute([$bookingId, $showId, $seatNo]);
    }

    $pdo->commit();

    json_response([
        'success' => true,
        'reservation_token' => $reservationToken,
        'checkout_url' => app_url('/checkout.php?reservation=' . $reservationToken),
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $status = str_contains($e->getMessage(), 'already booked') ? 409 : 500;
    error_log('process_booking error: ' . $e->getMessage());
    json_response(['success' => false, 'message' => $e->getMessage() ?: 'Unable to reserve seats.'], $status);
}
