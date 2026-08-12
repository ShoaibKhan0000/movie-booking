<?php
require_once 'includes/app.php';

if (!is_post_request()) {
    redirect('/index.php');
}

$showId = get_positive_int($_POST, 'show_id');
$target = '/booking.php' . ($showId ? '?show_id=' . $showId : '');

redirect($target);
