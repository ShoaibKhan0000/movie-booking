<?php
require_once 'config/db.php';
include 'includes/header.php';

// Fetch all available movies
$stmt = $pdo->query("SELECT * FROM movies ORDER BY id DESC");
$movies = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="fw-bold">Now Showing 🎥</h2>
        <p class="text-muted">Explore latest movies and book your tickets instantly.</p>
    </div>
</div>

<div class="row">
    <?php if(count($movies) > 0): ?>
        <?php foreach($movies as $movie): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm movie-card">
                    <img src="assets/images/<?= htmlspecialchars($movie['poster']) ?>" class="card-img-top" alt="<?= htmlspecialchars($movie['title']) ?>" onerror="this.src='https://via.placeholder.com/300x400?text=No+Poster'">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold"><?= htmlspecialchars($movie['title']) ?></h5>
                        <p class="card-text text-muted mb-1"><small><strong>Genre:</strong> <?= htmlspecialchars($movie['genre']) ?></small></p>
                        <p class="card-text text-muted mb-3"><small><strong>Duration:</strong> <?= htmlspecialchars($movie['duration']) ?></small></p>
                        <a href="movie-details.php?id=<?= $movie['id'] ?>" class="btn btn-primary mt-auto w-100">Book Tickets</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">No movies available right now. Please check back later or add movies from Admin Panel.</div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>