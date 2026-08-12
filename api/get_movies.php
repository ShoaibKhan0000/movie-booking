<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/app.php';

$search = trim((string) ($_GET['search'] ?? ''));
$location = trim((string) ($_GET['location'] ?? ''));

$sql = "\
    SELECT DISTINCT m.id, m.title, m.genre, m.duration, m.language, m.rating, m.ticket_price, m.poster_url\
    FROM movies m\
    JOIN shows s ON s.movie_id = m.id\
    JOIN theaters t ON t.id = s.theater_id\
    WHERE m.is_active = 1 AND s.show_time >= NOW()\
";
$params = [];

if ($search !== '') {
    $sql .= " AND (m.title LIKE ? OR m.genre LIKE ? OR m.language LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($location !== '') {
    $sql .= " AND t.location = ?";
    $params[] = $location;
}

$sql .= " ORDER BY m.id ASC LIMIT 10";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movies = $stmt->fetchAll();

json_response(['success' => true, 'movies' => $movies]);
