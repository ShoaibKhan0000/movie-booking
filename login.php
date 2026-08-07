<?php
require_once 'config/db.php';
include 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-lg bg-dark text-white border-secondary">
            <div class="card-header bg-black text-white border-secondary">
                <h4 class="mb-0 fw-bold">User Login</h4>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- Form level autocomplete off -->
                <form action="login.php" method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <!-- Added autocomplete="new-email" -->
                        <input type="email" name="email" class="form-control bg-secondary text-white border-0" autocomplete="new-email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <!-- Added autocomplete="new-password" -->
                        <input type="password" name="password" class="form-control bg-secondary text-white border-0" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>