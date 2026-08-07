<?php
require_once 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['seats'])) {
    $user_id    = $_SESSION['user_id'];
    $show_id    = $_POST['show_id'];
    $seats      = implode(',', $_POST['seats']);
    $seat_count = count($_POST['seats']);
    $price      = $_POST['ticket_price'];
    $total      = $seat_count * $price;

    // Database Me Booking Save Karna
    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, show_id, seats_booked, total_amount) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$user_id, $show_id, $seats, $total])) {
        $booking_id = $pdo->lastInsertId();
        header("Location: ticket.php?id=" . $booking_id);
        exit;
    } else {
        echo "<div class='alert alert-danger'>Booking failed. Please try again.</div>";
    }
} else {
    header("Location: index.php");
    exit;
}
?>