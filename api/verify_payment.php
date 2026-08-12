<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/app.php';

require_login();

if (!is_post_request()) {
    json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$reservationToken = trim((string) ($payload['reservation_token'] ?? ''));
$paymentId = trim((string) ($payload['razorpay_payment_id'] ?? ''));
$orderId = trim((string) ($payload['razorpay_order_id'] ?? ''));
$signature = trim((string) ($payload['razorpay_signature'] ?? ''));

if (!preg_match('/^[a-f0-9]{64}$/', $reservationToken) || $paymentId === '') {
    json_response(['success' => false, 'message' => 'Invalid payment payload.'], 422);
}

$keySecret = getenv('RAZORPAY_KEY_SECRET') ?: '';

try {
    $pdo->beginTransaction();
    cleanup_expired_holds($pdo);

    $stmt = $pdo->prepare("\
        SELECT id, booking_reference, status, expires_at\
        FROM bookings\
        WHERE reservation_token = ? AND user_id = ?\
        FOR UPDATE\
    ");
    $stmt->execute([$reservationToken, (int) $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        throw new RuntimeException('Booking not found.');
    }

    if ($booking['status'] === 'CONFIRMED') {
        $pdo->commit();
        json_response([
            'success' => true,
            'redirect_url' => app_url('/ticket.php?ref=' . urlencode((string) $booking['booking_reference'])),
        ]);
    }

    if ($booking['status'] !== 'PENDING' || strtotime((string) $booking['expires_at']) <= time()) {
        throw new RuntimeException('Reservation expired. Please book again.');
    }

    if ($keySecret !== '' && $orderId !== '' && $signature !== '') {
        $generatedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);
        if (!hash_equals($generatedSignature, $signature)) {
            throw new RuntimeException('Payment signature verification failed.');
        }
    }

    $bookingReference = $booking['booking_reference'];
    if (!$bookingReference) {
        $bookingReference = 'CP' . date('Ymd') . str_pad((string) $booking['id'], 6, '0', STR_PAD_LEFT);
    }

    $updateBooking = $pdo->prepare("\
        UPDATE bookings\
        SET booking_reference = ?, payment_id = ?, payment_order_id = NULLIF(?, ''), payment_signature = NULLIF(?, ''), status = 'CONFIRMED'\
        WHERE id = ?\
    ");
    $updateBooking->execute([$bookingReference, $paymentId, $orderId, $signature, (int) $booking['id']]);

    $updateSeats = $pdo->prepare("UPDATE booking_seats SET status = 'CONFIRMED' WHERE booking_id = ?");
    $updateSeats->execute([(int) $booking['id']]);

    $pdo->commit();

    json_response([
        'success' => true,
        'redirect_url' => app_url('/ticket.php?ref=' . urlencode($bookingReference)),
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('verify_payment error: ' . $e->getMessage());
    json_response(['success' => false, 'message' => $e->getMessage() ?: 'Payment verification failed.'], 400);
}
