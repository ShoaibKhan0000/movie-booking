<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineTicket - Premium Cinema Experience</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.net/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f1015; color: #e1e1e1; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background-color: #16181f !important; border-bottom: 1px solid #2a2e3d; }
        .movie-card { background: #181a20; border: 1px solid #2a2e3d; transition: transform 0.3s ease; }
        .movie-card:hover { transform: translateY(-5px); border-color: #ff3366; }
        .movie-card img { height: 380px; object-fit: cover; }
        .btn-primary { background-color: #ff3366; border-color: #ff3366; }
        .btn-primary:hover { background-color: #e02e5b; border-color: #e02e5b; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-danger fs-3" href="index.php"><i class="fa-solid fa-film me-2"></i>CineTicket</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link text-white" href="index.php">Movies</a></li>
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <li class="nav-item"><a class="nav-link text-warning fw-bold" href="admin/index.php"><i class="fa-solid fa-user-shield me-1"></i>Admin Panel</a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link text-white" href="my-bookings.php">My Bookings</a></li>
            <li class="nav-item ms-2">
                <a class="btn btn-outline-danger btn-sm px-3" href="logout.php">Logout (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a>
            </li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link text-white" href="login.php">Login</a></li>
            <li class="nav-item ms-2"><a class="btn btn-primary btn-sm px-3" href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container my-4">