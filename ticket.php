<?php
require_once 'config/db.php';
include 'includes/header.php';

require_login();

$booking_id = get_positive_int($_GET, 'id');
if (!$booking_id) {
    redirect('my-bookings.php');
}

$stmt = $pdo->prepare("
    SELECT b.*, s.show_time, s.price, m.title, m.genre, m.poster, u.name as user_name 
    FROM bookings b
    JOIN shows s ON b.show_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN users u ON b.user_id = u.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$ticket = $stmt->fetch();

if (!$ticket) {
    echo "<div class='alert alert-danger'>Invalid Booking Reference.</div>";
    include 'includes/footer.php';
    exit;
}

$booking_ref = "CT-" . str_pad($ticket['id'], 6, '0', STR_PAD_LEFT);
$verify_url  = "http://localhost/movie-booking/ticket.php?id=" . $ticket['id'];
$qr_data    = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verify_url);
?>

<style>
@media print {
    nav, footer, .no-print { display: none !important; }
    body { background-color: #fff !important; color: #000 !important; }
    .ticket-card { border: 2px solid #000 !important; background: #fff !important; color: #000 !important; box-shadow: none !important; }
}
.ticket-card { background: #121212; color: #fff; border-radius: 12px; border: 1px solid #333; }
</style>

<div class="row justify-content-center my-4">
    <div class="col-md-8">
        <div class="card ticket-card shadow-lg p-3">
            <div class="row g-0 align-items-center">
                <div class="col-md-4 text-center">
                    <img src="assets/images/<?= e($ticket['poster']) ?>" class="img-fluid rounded-start p-2" style="max-height: 280px;" onerror="this.src='https://via.placeholder.com/200x300?text=CineTicket'">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="card-title fw-bold text-warning mb-0"><?= e($ticket['title']) ?></h3>
                            <span class="badge bg-danger"><?= e($ticket['genre']) ?></span>
                        </div>
                        <p class="text-muted small">Booking ID: <strong><?= $booking_ref ?></strong></p>
                        <hr class="border-secondary">
                        <div class="row text-white-50">
                            <div class="col-6 mb-2"><small>Name:</small><br><strong class="text-white"><?= e($ticket['user_name']) ?></strong></div>
                            <div class="col-6 mb-2"><small>Seats:</small><br><strong class="text-success"><?= e($ticket['seats_booked']) ?></strong></div>
                            <div class="col-6"><small>Showtime:</small><br><strong class="text-white"><?= date('d M Y, h:i A', strtotime($ticket['show_time'])) ?></strong></div>
                            <div class="col-6"><small>Amount Paid:</small><br><strong class="text-warning">₹<?= number_format($ticket['total_amount'], 2) ?></strong></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end mt-3">
                            <div class="text-center">
                                <img src="<?= $qr_data ?>" alt="QR Code" class="img-thumbnail bg-white" style="width: 100px;">
                            </div>
                            <div class="no-print">
                                <button type="button" onclick="window.print()" class="btn btn-warning fw-bold px-4">🖨️ Print Ticket</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>