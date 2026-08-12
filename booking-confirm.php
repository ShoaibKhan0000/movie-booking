<?php
require_once 'config/db.php';
require_once 'includes/app.php';

require_login();

if (!is_post_request()) {
    redirect('index.php');
}

$userId = (int) $_SESSION['user_id'];
$showId = get_positive_int($_POST, 'show_id');
$selectedSeats = normalize_seat_list($_POST['seats'] ?? []);

if (!$showId || $selectedSeats === []) {
    set_flash('warning', 'Please select at least one valid seat.');
    redirect('select-seats.php?show_id=' . (int) $showId);
}

try {
    $pdo->beginTransaction();

    $showStmt = $pdo->prepare("SELECT id, price FROM shows WHERE id = ? FOR UPDATE");
    $showStmt->execute([$showId]);
    $show = $showStmt->fetch();

    if (!$show) {
        throw new RuntimeException('Selected show no longer exists.');
    }

    $bookedStmt = $pdo->prepare("SELECT seats_booked FROM bookings WHERE show_id = ? FOR UPDATE");
    $bookedStmt->execute([$showId]);
    $bookedRows = $bookedStmt->fetchAll(PDO::FETCH_COLUMN);

    $alreadyBooked = [];
    foreach ($bookedRows as $seatRow) {
        $alreadyBooked = array_merge($alreadyBooked, normalize_seat_list(explode(',', (string) $seatRow)));
    }

    $conflicts = array_intersect($selectedSeats, $alreadyBooked);
    if ($conflicts !== []) {
        $pdo->rollBack();
        set_flash('danger', 'Some seats were just booked by another user. Please choose different seats.');
        redirect('select-seats.php?show_id=' . $showId);
    }

    $seatCount = count($selectedSeats);
    $price = (float) $show['price'];
    $total = $seatCount * $price;
    $seatsCsv = implode(',', $selectedSeats);

    $insertStmt = $pdo->prepare("INSERT INTO bookings (user_id, show_id, seats_booked, total_amount) VALUES (?, ?, ?, ?)");
    $insertStmt->execute([$userId, $showId, $seatsCsv, $total]);
    $bookingId = (int) $pdo->lastInsertId();

    $pdo->commit();
    set_flash('success', 'Booking confirmed successfully!');
    redirect('ticket.php?id=' . $bookingId);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Booking failed: ' . $e->getMessage());
    set_flash('danger', 'Booking failed. Please try again.');
    redirect('select-seats.php?show_id=' . (int) $showId);
}
