<?php
require_once 'includes/app.php';

session_unset();
session_destroy();
redirect('login.php');