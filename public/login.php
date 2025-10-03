<?php
require_once __DIR__ . '/../includes/auth.php';
if (current_user()) {
    redirect('public/index.php');
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login($_POST['email'], $_POST['password'])) {
        redirect('public/index.php');
    } else {
        $message = 'Invalid credentials. Please try again.';
    }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Welcome back</h1>
                <?php if ($message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <p class="mt-3 mb-0">Need an account? <a href="<?= url_for('public/register.php'); ?>">Register</a></p>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
