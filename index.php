<?php
require_once 'config/db.php';
include 'includes/header.php';

$locationsStmt = $pdo->query("SELECT DISTINCT location FROM theaters WHERE is_active = 1 ORDER BY location ASC");
$locations = $locationsStmt->fetchAll(PDO::FETCH_COLUMN);

$moviesStmt = $pdo->query("SELECT id, title, genre, duration, language, rating, ticket_price, poster_url FROM movies WHERE is_active = 1 ORDER BY id ASC LIMIT 10");
$movies = $moviesStmt->fetchAll();
?>

<section class="hero-section rounded-4 overflow-hidden mb-5">
    <div class="hero-overlay p-5 p-lg-6">
        <span class="badge bg-danger-subtle text-danger fw-semibold mb-3">CinePass · Delhi NCR</span>
        <h1 class="display-5 fw-bold mb-3">Book real shows across Delhi NCR cinemas</h1>
        <p class="lead text-white-50 mb-4">Discover trending movies, pick your theater, select seats on a curved screen map, and pay securely with Razorpay.</p>
        <a href="#movie-list" class="btn btn-danger btn-lg rounded-pill px-4 book-hero-btn">Book Now</a>
    </div>
</section>

<section id="movie-list" class="mb-4" data-page="home">
    <div class="row g-3 mb-3">
        <div class="col-md-8">
            <input type="search" id="movie-search" class="form-control form-control-lg bg-dark text-white border-secondary" placeholder="Search movie by title or genre...">
        </div>
        <div class="col-md-4">
            <select id="location-filter" class="form-select form-select-lg bg-dark text-white border-secondary">
                <option value="">All Locations</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?= e($location) ?>"><?= e($location) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row" id="movie-grid" data-api="<?= e(app_url('/api/get_movies.php')) ?>">
        <?php foreach ($movies as $movie): ?>
            <div class="col-md-3 mb-4">
                <article class="movie-card h-100">
                    <img src="<?= e($movie['poster_url']) ?>" alt="<?= e($movie['title']) ?> poster" class="movie-poster" loading="lazy">
                    <div class="p-3">
                        <h3 class="h5 mb-1"><?= e($movie['title']) ?></h3>
                        <p class="small text-secondary mb-1"><?= e($movie['genre']) ?></p>
                        <p class="small text-secondary mb-2"><?= e($movie['duration']) ?> · <?= e($movie['language']) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-info-subtle text-info">₹<?= number_format((float) $movie['ticket_price'], 0) ?></span>
                            <a class="btn btn-danger btn-sm" href="<?= e(app_url('/movie_details.php?id=' . (int) $movie['id'])) ?>">View Shows</a>
                        </div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
