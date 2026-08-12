<?php
require_once 'includes/app.php';

$movieId = get_positive_int($_GET, 'id');
$target = 'movie-details.php' . ($movieId ? '?id=' . $movieId : '');

header('Location: ' . $target, true, 301);
exit;