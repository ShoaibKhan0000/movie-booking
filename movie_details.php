<?php
require_once 'config/db.php';
include 'includes/header.php';

$movieId = get_positive_int($_GET, 'id');
if (!$movieId) {
    redirect('/index.php');
}

$movieStmt = $pdo->prepare("SELECT * FROM movies WHERE id = ? AND is_active = 1");
$movieStmt->execute([$movieId]);
$movie = $movieStmt->fetch();

if (!$movie) {
    echo "<div class='alert alert-danger'>Movie not found.</div>";
    include 'includes/footer.php';
    exit;
}

$showStmt = $pdo->prepare("\
    SELECT s.id, s.show_time, s.base_price, t.name AS theater_name, t.location, t.city\
    FROM shows s\
    JOIN theaters t ON t.id = s.theater_id\
    WHERE s.movie_id = ? AND s.show_time >= NOW()\
    ORDER BY s.show_time ASC\
");
$showStmt->execute([$movieId]);
$shows = $showStmt->fetchAll();

$groupedShows = [];
foreach ($shows as $show) {
    $key = $show['theater_name'] . '|' . $show['location'];
    if (!isset($groupedShows[$key])) {
        $groupedShows[$key] = [
            'theater_name' => $show['theater_name'],
            'location' => $show['location'],
            'city' => $show['city'],
            'slots' => [],
        ];
    }
    $groupedShows[$key]['slots'][] = $show;
}
?>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <img src="<?= e($movie['poster_url']) ?>" alt="<?= e($movie['title']) ?> poster" class="img-fluid rounded-4 shadow movie-poster">
    </div>
    <div class="col-lg-8">
        <h1 class="fw-bold mb-2"><?= e($movie['title']) ?></h1>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-danger-subtle text-danger"><?= e($movie['genre']) ?></span>
            <span class="badge bg-secondary"><?= e($movie['duration']) ?></span>
            <span class="badge bg-secondary"><?= e($movie['language']) ?></span>
            <span class="badge bg-warning text-dark">⭐ <?= number_format((float) $movie['rating'], 1) ?></span>
        </div>
        <p class="text-white-50 mb-3"><?= e($movie['description']) ?></p>
        <h5 class="fw-semibold">Base Ticket Price: <span class="text-info">₹<?= number_format((float) $movie['ticket_price'], 0) ?></span></h5>
    </div>
</div>

<h3 class="fw-bold mb-3">Select Theater & Showtime</h3>
<?php if ($groupedShows === []): ?>
    <div class="alert alert-warning">No upcoming shows available for this movie yet.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($groupedShows as $group): ?>
            <div class="col-12">
                <div class="card bg-dark border-secondary shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-1"><?= e($group['theater_name']) ?></h5>
                        <p class="text-white-50 mb-3"><?= e($group['location']) ?>, <?= e($group['city']) ?></p>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($group['slots'] as $slot): ?>
                                <a href="<?= e(app_url('/booking.php?show_id=' . (int) $slot['id'])) ?>" class="btn btn-outline-info">
                                    <?= e(date('d M, h:i A', strtotime($slot['show_time']))) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
