<?php
require_once 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$show_id = $_GET['show_id'] ?? null;
if (!$show_id) { 
    header("Location: index.php"); 
    exit; 
}

// Fetch Show & Movie Details
$stmt = $pdo->prepare("SELECT s.*, m.title FROM shows s JOIN movies m ON s.movie_id = m.id WHERE s.id = ?");
$stmt->execute([$show_id]);
$show = $stmt->fetch();

if (!$show) {
    echo "<div class='alert alert-danger'>Show not found.</div>";
    include 'includes/footer.php';
    exit;
}

// Fetch Already Booked Seats
$bookedStmt = $pdo->prepare("SELECT seats_booked FROM bookings WHERE show_id = ?");
$bookedStmt->execute([$show_id]);
$bookedRows = $bookedStmt->fetchAll(PDO::FETCH_COLUMN);

$alreadyBookedSeats = [];
foreach ($bookedRows as $row) {
    $seats = explode(',', $row);
    $alreadyBookedSeats = array_merge($alreadyBookedSeats, $seats);
}
?>

<div class="row justify-content-center">
    <div class="col-md-9 text-center">
        <h3 class="fw-bold text-white"><?= htmlspecialchars($show['title']) ?></h3>
        <p class="text-muted">Showtime: <?= date('d M Y, h:i A', strtotime($show['show_time'])) ?> | Ticket Price: ₹<?= $show['price'] ?></p>

        <div class="bg-secondary text-white p-2 mb-4 rounded shadow-sm fw-bold">CINEMA SCREEN THIS WAY 🎬</div>

        <form action="booking-confirm.php" method="POST" id="seatForm">
            <input type="hidden" name="show_id" value="<?= $show['id'] ?>">
            <input type="hidden" name="ticket_price" id="ticketPrice" value="<?= $show['price'] ?>">
            
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                <?php 
                $rows = ['A', 'B', 'C', 'D'];
                foreach($rows as $row):
                    for($i = 1; $i <= 10; $i++):
                        $seatNo = $row . $i;
                        $isBooked = in_array($seatNo, $alreadyBookedSeats);
                ?>
                    <input type="checkbox" name="seats[]" value="<?= $seatNo ?>" id="seat_<?= $seatNo ?>" class="btn-check seat-checkbox" <?= $isBooked ? 'disabled' : '' ?> onchange="calculateTotal()">
                    <label class="btn <?= $isBooked ? 'btn-danger opacity-50' : 'btn-outline-success' ?> px-3 py-2" for="seat_<?= $seatNo ?>"><?= $seatNo ?></label>
                <?php 
                    endfor;
                    echo "<div class='w-100 my-1'></div>";
                endforeach; 
                ?>
            </div>

            <div class="card bg-dark text-white p-3 mb-4 border-secondary shadow-sm">
                <h5>Selected Seats: <span id="selectedSeatsCount" class="text-warning">None</span></h5>
                <h4 class="mb-0">Total Amount: <span class="text-success">₹<span id="totalPrice">0</span></span></h4>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold" id="bookBtn" disabled>Proceed to Payment</button>
        </form>
    </div>
</div>

<script>
function calculateTotal() {
    const checkboxes = document.querySelectorAll('.seat-checkbox:checked');
    const price = parseFloat(document.getElementById('ticketPrice').value);
    const selectedSeats = Array.from(checkboxes).map(cb => cb.value);
    
    document.getElementById('selectedSeatsCount').innerText = selectedSeats.length > 0 ? selectedSeats.join(', ') : 'None';
    document.getElementById('totalPrice').innerText = (selectedSeats.length * price).toFixed(2);
    document.getElementById('bookBtn').disabled = selectedSeats.length === 0;
}
</script>

<?php include 'includes/footer.php'; ?>