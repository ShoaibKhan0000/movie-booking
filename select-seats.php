<?php
require_once 'includes/app.php';

$showId = get_positive_int($_GET, 'show_id');
$target = '/booking.php' . ($showId ? '?show_id=' . $showId : '');

redirect($target);
