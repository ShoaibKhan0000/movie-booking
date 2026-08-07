<?php
require_once 'config/db.php';
include 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name']);
    $email    = trim($_POST['user_email']);
    $password = $_POST['user_pass'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $insert = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            if ($insert->execute([$name, $email, $hashedPassword])) {
                $success = "Registration successful! You can now <a href='login.php' class='text-warning'>Login</a>.";
            } else {
                $error = "Something went wrong.";
            }
        }
    }
}
?>

<div class="row justify-content-center my-5">
    <div class="col-md-5">
        <div class="card bg-dark text-white border-secondary shadow-lg">
            <div class="card-header bg-black border-secondary text-center py-3">
                <h4 class="fw-bold mb-0 text-danger"><i class="fa-solid fa-user-plus me-2"></i>Create Account</h4>
            </div>
            <div class="card-body p-4">
                <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

                <!-- Dummy Hidden Inputs to Trick Browser Password Managers -->
                <form action="register.php" method="POST" id="regForm" autocomplete="off">
                    <input type="text" style="display:none" aria-hidden="true">
                    <input type="password" style="display:none" aria-hidden="true">

                    <div class="mb-3">
                        <label class="form-label text-white-50">Full Name</label>
                        <input type="text" name="full_name" class="form-control bg-secondary text-white border-0" required autocomplete="none">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white-50">Email Address</label>
                        <input type="email" name="user_email" class="form-control bg-secondary text-white border-0" required autocomplete="none">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white-50">Password</label>
                        <input type="password" name="user_pass" class="form-control bg-secondary text-white border-0" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-danger w-100 fw-bold py-2 mt-2">Create Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill Clear Trigger on Page Load
window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        document.getElementById('regForm').reset();
    }, 50);
});
</script>

<?php include 'includes/footer.php'; ?>