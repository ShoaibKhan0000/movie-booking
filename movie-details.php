<?php
require_once 'includes/app.php';

$movieId = get_positive_int($_GET, 'id');
$target = '/movie_details.php' . ($movieId ? '?id=' . $movieId : '');

redirect($target);
