<?php
require_once '../config/db.php';

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("<h3 style='color:red; text-align:center; margin-top:50px;'>Access Denied: Admin Privileges Required.</h3>");
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_movie'])) {
    $title = trim($_POST['title']);
    $genre = trim($_POST['genre']);
    $duration = trim($_POST['duration']);
    $desc = trim($_POST['description']);
    
    $posterName = 'default.jpg';
    if (!empty($_FILES['poster']['name'])) {
        $posterName = time() . '_' . basename($_FILES['poster']['name']);
        $targetPath = "../assets/images/" . $posterName;
        move_uploaded_file($_FILES['poster']['tmp_name'], $targetPath);
    }

    $stmt = $pdo->prepare("INSERT INTO movies (title, genre, duration, poster, description) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $genre, $duration, $posterName, $desc])) {
        $msg = "Movie added successfully!";
    }
}

$movies = $pdo->query("SELECT * FROM movies ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <title>Admin Dashboard - CineTicket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🛠️ Admin Panel</h2>
            <a href="../index.php" class="btn btn-outline-light">Back to Website</a>
        </div>

        <?php if($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

        <div class="card bg-secondary text-white mb-5 p-4 shadow">
            <h4>Add New Movie</h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-4 mb-3"><input type="text" name="title" placeholder="Movie Title" class="form-control" required></div>
                    <div class="col-md-4 mb-3"><input type="text" name="genre" placeholder="Genre (e.g. Action / Sci-Fi)" class="form-control" required></div>
                    <div class="col-md-4 mb-3"><input type="text" name="duration" placeholder="Duration (e.g. 2h 15m)" class="form-control" required></div>
                    <div class="col-md-8 mb-3"><textarea name="description" placeholder="Synopsis / Summary" class="form-control" rows="2" required></textarea></div>
                    <div class="col-md-4 mb-3"><input type="file" name="poster" class="form-control" accept="image/*"></div>
                </div>
                <button type="submit" name="add_movie" class="btn btn-warning fw-bold px-4">Publish Movie</button>
            </form>
        </div>

        <h4>Active Movies Catalog</h4>
        <table class="table table-dark table-striped mt-3 align-middle">
            <thead><tr><th>ID</th><th>Poster</th><th>Title</th><th>Genre</th><th>Duration</th></tr></thead>
            <tbody>
                <?php foreach($movies as $m): ?>
                <tr>
                    <td><?= $m['id'] ?></td>
                    <td><img src="../assets/images/<?= $m['poster'] ?>" width="45" height="60" style="object-fit:cover;" class="rounded" onerror="this.src='https://via.placeholder.com/45x60'"></td>
                    <td class="fw-bold"><?= htmlspecialchars($m['title']) ?></td>
                    <td><span class="badge bg-danger"><?= htmlspecialchars($m['genre']) ?></span></td>
                    <td><?= htmlspecialchars($m['duration']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>