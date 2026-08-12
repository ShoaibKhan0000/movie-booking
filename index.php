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
                    <img src="assets/images/<?= e($movie['poster']) ?>" class="card-img-top" alt="<?= e($movie['title']) ?>" onerror="this.src='https://via.placeholder.com/300x400?text=No+Poster'">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold"><?= e($movie['title']) ?></h5>
                        <p class="card-text text-muted mb-1"><small><strong>Genre:</strong> <?= e($movie['genre']) ?></small></p>
                        <p class="card-text text-muted mb-3"><small><strong>Duration:</strong> <?= e($movie['duration']) ?></small></p>
                        <a href="movie-details.php?id=<?= (int) $movie['id'] ?>" class="btn btn-primary mt-auto w-100">Book Tickets</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card p-4 text-center">
                <h5 class="mb-2">No movies available right now</h5>
                <p class="text-muted mb-0">Please check back later or add movies from the admin panel.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>