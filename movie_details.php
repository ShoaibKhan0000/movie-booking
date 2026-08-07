<?php
require_once 'config/db.php';
include 'includes/header.php';

$movie_id = $_GET['id'] ?? null;

if (!$movie_id) {
    header("Location: index.php");
    exit;
}

// Fetch Movie Details
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch();

if (!$movie) {
    echo "<div class='alert alert-danger'>Movie not found.</div>";
    include 'includes/footer.php';
    exit;
}

// Fetch Available Shows
$showStmt = $pdo->prepare("SELECT * FROM shows WHERE movie_id = ? AND show_time >= NOW() ORDER BY show_time ASC");
$showStmt->execute([$movie_id]);
$shows = $showStmt->fetchAll();
?>

<div class="row">
    <div class="col-md-4">
        <img src="assets/images/<?= htmlspecialchars($movie['poster']) ?>" class="img-fluid rounded shadow" onerror="this.src='https://via.placeholder.com/350x500?text=No+Poster'">
    </div>
    <div class="col-md-8">
        <h2><?= htmlspecialchars($movie['title']) ?></h2>
        <p class="badge bg-secondary"><?= htmlspecialchars($movie['genre']) ?></p>
        <p class="text-muted"><strong>Duration:</strong> <?= htmlspecialchars($movie['duration']) ?></p>
        <hr>
        <h5>Synopsis</h5>
        <p><?= htmlspecialchars($movie['description']) ?></p>

        <h4 class="mt-4">Available Showtimes</h4>
        <?php if(count($shows) > 0): ?>
            <div class="list-group mt-3">
                <?php foreach($shows as $show): ?>
                    <a href="select-seats.php?show_id=<?= $show['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>🕒 <?= date('d M Y, h:i A', strtotime($show['show_time'])) ?></strong>
                        </div>
                        <div>
                            <span class="badge bg-success rounded-pill me-2">₹<?= $show['price'] ?></span>
                            <button class="btn btn-sm btn-outline-primary">Select Seats</button>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No upcoming shows scheduled for this movie.</div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>