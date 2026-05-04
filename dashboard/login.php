<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (isLoggedIn()) { header('Location: ' . BASE_URL . '/dashboard/home.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login($username, $password)) {
        header('Location: ' . BASE_URL . '/dashboard/home.php'); exit;
    }
    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – RAG Chatbot Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #0f0f13; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card {
            width: 100%; max-width: 380px;
            background: #13131a; border: 1px solid #2a2a38; border-radius: 16px;
            padding: 36px 32px;
        }
        .brand-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg,#6366f1,#8b5cf6);
            border-radius: 14px; font-size: 24px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        }
        .form-control { background: #0f0f13; border-color: #2a2a38; color: #e2e2e8; }
        .form-control:focus { background: #0f0f13; border-color: #6366f1; color: #e2e2e8; box-shadow: 0 0 0 0.2rem rgba(99,102,241,.2); }
        .btn-primary { background: linear-gradient(135deg,#6366f1,#8b5cf6); border: none; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand-icon">🤖</div>
    <h4 class="text-center fw-semibold mb-1">RAG Chatbot</h4>
    <p class="text-center text-secondary mb-4" style="font-size:13px">Masuk ke Dashboard</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label small">Username</label>
            <input type="text" name="username" class="form-control" required autofocus
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div class="mb-4">
            <label class="form-label small">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
        </button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
