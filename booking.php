<?php
require_once 'config/db.php';
include 'includes/header.php';

require_login();

$showId = get_positive_int($_GET, 'show_id');
if (!$showId) {
    redirect('/index.php');
}

$showStmt = $pdo->prepare("\
    SELECT s.id, s.show_time, s.base_price, m.id AS movie_id, m.title, m.genre, m.poster_url, t.name AS theater_name, t.location\
    FROM shows s\
    JOIN movies m ON m.id = s.movie_id\
    JOIN theaters t ON t.id = s.theater_id\
    WHERE s.id = ?\
");
$showStmt->execute([$showId]);
$show = $showStmt->fetch();

if (!$show) {
    echo "<div class='alert alert-danger'>Invalid show selected.</div>";
    include 'includes/footer.php';
    exit;
}
?>

<div class="row g-4" data-page="booking" data-show-id="<?= (int) $show['id'] ?>" data-seat-api="<?= e(app_url('/api/get_seats.php')) ?>" data-booking-api="<?= e(app_url('/api/process_booking.php')) ?>" data-checkout-url="<?= e(app_url('/checkout.php')) ?>">
    <div class="col-lg-4">
        <div class="card bg-dark border-secondary shadow-sm h-100">
            <img src="<?= e($show['poster_url']) ?>" class="movie-poster rounded-top" alt="<?= e($show['title']) ?> poster">
            <div class="card-body">
                <h4 class="mb-1"><?= e($show['title']) ?></h4>
                <p class="text-white-50 mb-1"><?= e($show['genre']) ?></p>
                <p class="text-white-50 mb-1"><?= e($show['theater_name']) ?> · <?= e($show['location']) ?></p>
                <p class="text-white-50 mb-3"><?= e(date('d M Y, h:i A', strtotime($show['show_time']))) ?></p>
                <h5>Base Price: <span class="text-info">₹<?= number_format((float) $show['base_price'], 2) ?></span></h5>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card bg-dark border-secondary shadow-sm p-4">
            <h4 class="fw-bold mb-2">Select Your Seats</h4>
            <div class="cinema-screen-wrap mb-3"><div class="cinema-screen">Cinema Screen</div></div>
            <div id="seat-grid" class="seat-grid mb-3"></div>
            <div class="d-flex gap-3 small text-white-50 mb-4">
                <span><span class="seat seat-sm available me-1"></span>Available</span>
                <span><span class="seat seat-sm selected me-1"></span>Selected</span>
                <span><span class="seat seat-sm occupied me-1"></span>Occupied</span>
            </div>

            <div class="card bg-black border-secondary p-3 mb-3">
                <p class="mb-1">Seats: <strong id="selected-seats-display">None</strong></p>
                <p class="mb-1">Base: <strong id="base-price">₹0.00</strong></p>
                <p class="mb-1">GST (18%): <strong id="gst-price">₹0.00</strong></p>
                <h5 class="mb-0">Total: <strong id="total-price" class="text-success">₹0.00</strong></h5>
            </div>

            <button id="reserve-seats-btn" class="btn btn-danger btn-lg w-100" disabled>Continue to Checkout</button>
            <p id="seat-error" class="text-danger mt-2 small mb-0"></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
