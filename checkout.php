<?php
require_once 'config/db.php';
include 'includes/header.php';

require_login();

$reservationToken = trim((string) ($_GET['reservation'] ?? ''));
if (!preg_match('/^[a-f0-9]{64}$/', $reservationToken)) {
    set_flash('warning', 'Invalid reservation token.');
    redirect('/index.php');
}

$stmt = $pdo->prepare("\
    SELECT b.id, b.booking_reference, b.reservation_token, b.seats, b.seat_count, b.base_amount, b.gst_amount, b.total_amount, b.expires_at,\
           m.title, t.name AS theater_name, t.location, s.show_time\
    FROM bookings b\
    JOIN shows s ON s.id = b.show_id\
    JOIN movies m ON m.id = s.movie_id\
    JOIN theaters t ON t.id = s.theater_id\
    WHERE b.reservation_token = ? AND b.user_id = ? AND b.status = 'PENDING'\
");
$stmt->execute([$reservationToken, (int) $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking || strtotime($booking['expires_at']) <= time()) {
    set_flash('warning', 'Reservation expired. Please select seats again.');
    redirect('/index.php');
}

$razorpayKeyId = getenv('RAZORPAY_KEY_ID') ?: '';
?>

<div class="row justify-content-center" data-page="checkout">
    <div class="col-lg-8">
        <div class="card bg-dark border-secondary shadow-sm p-4" id="checkout-root" data-verify-api="<?= e(app_url('/api/verify_payment.php')) ?>" data-reservation-token="<?= e($booking['reservation_token']) ?>" data-ticket-base="<?= e(app_url('/ticket.php')) ?>" data-amount="<?= (int) round(((float) $booking['total_amount']) * 100) ?>" data-key-id="<?= e($razorpayKeyId) ?>" data-booking-ref="<?= e($booking['booking_reference'] ?: '') ?>">
            <h3 class="fw-bold mb-3">Checkout</h3>
            <p class="mb-1"><strong>Movie:</strong> <?= e($booking['title']) ?></p>
            <p class="mb-1"><strong>Theater:</strong> <?= e($booking['theater_name']) ?> · <?= e($booking['location']) ?></p>
            <p class="mb-1"><strong>Showtime:</strong> <?= e(date('d M Y, h:i A', strtotime($booking['show_time']))) ?></p>
            <p class="mb-3"><strong>Seats:</strong> <?= e($booking['seats']) ?></p>

            <div class="card bg-black border-secondary p-3 mb-3">
                <div class="d-flex justify-content-between"><span>Base Amount</span><strong>₹<?= number_format((float) $booking['base_amount'], 2) ?></strong></div>
                <div class="d-flex justify-content-between"><span>GST (18%)</span><strong>₹<?= number_format((float) $booking['gst_amount'], 2) ?></strong></div>
                <hr class="border-secondary">
                <div class="d-flex justify-content-between"><span class="fw-semibold">Total Payable</span><strong class="text-success">₹<?= number_format((float) $booking['total_amount'], 2) ?></strong></div>
            </div>

            <p class="text-warning small">Reservation expires at: <?= e(date('d M Y, h:i A', strtotime($booking['expires_at']))) ?></p>

            <?php if ($razorpayKeyId === ''): ?>
                <div class="alert alert-warning">Set <code>RAZORPAY_KEY_ID</code> and <code>RAZORPAY_KEY_SECRET</code> in environment to enable live Razorpay checkout.</div>
            <?php endif; ?>

            <button id="pay-now-btn" class="btn btn-danger btn-lg w-100" <?= $razorpayKeyId === '' ? 'disabled' : '' ?>>Pay with Razorpay</button>
            <p id="checkout-error" class="text-danger mt-2 mb-0 small"></p>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<?php include 'includes/footer.php'; ?>
