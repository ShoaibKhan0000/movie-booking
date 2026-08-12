<?php
require_once 'config/db.php';
include 'includes/header.php';

require_login();

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT b.*, s.show_time, m.title, m.poster, m.genre 
    FROM bookings b
    JOIN shows s ON b.show_id = s.id
    JOIN movies m ON s.movie_id = m.id
    WHERE b.user_id = ?
    ORDER BY b.id DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>

<h3 class="fw-bold mb-4 text-white"><i class="fa-solid fa-ticket me-2"></i>My Ticket History</h3>

<?php if(count($bookings) > 0): ?>
    <div class="row">
        <?php foreach($bookings as $b): ?>
            <div class="col-md-6 mb-3">
                <div class="card bg-dark text-white border-secondary shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-warning mb-1"><?= e($b['title']) ?></h5>
                            <p class="text-muted small mb-1">Seats: <strong class="text-success"><?= e($b['seats_booked']) ?></strong></p>
                            <p class="text-muted small mb-0">Showtime: <?= date('d M Y, h:i A', strtotime($b['show_time'])) ?></p>
                        </div>
                        <div>
                            <a href="ticket.php?id=<?= (int) $b['id'] ?>" class="btn btn-outline-warning btn-sm">View Ticket</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">You haven't booked any tickets yet.</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>