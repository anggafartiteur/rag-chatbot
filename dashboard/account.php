<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$pageTitle = 'Akun';
$db = getDB();

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_username') {
        $newUsername = trim($_POST['new_username'] ?? '');
        $password    = $_POST['confirm_password'] ?? '';
        $user = $db->prepare("SELECT password FROM users WHERE id=?");
        $user->execute([$_SESSION['user_id']]);
        $row = $user->fetch();
        if (!password_verify($password, $row['password'])) {
            $flash = 'danger:Password salah.';
        } else {
            $stmt = $db->prepare("UPDATE users SET username=? WHERE id=?");
            $stmt->execute([$newUsername, $_SESSION['user_id']]);
            $_SESSION['username'] = $newUsername;
            $flash = 'success:Username berhasil diubah.';
        }
    }

    if ($action === 'change_password') {
        $oldPass  = $_POST['old_password'] ?? '';
        $newPass  = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_new_password'] ?? '';
        $user = $db->prepare("SELECT password FROM users WHERE id=?");
        $user->execute([$_SESSION['user_id']]);
        $row = $user->fetch();
        if (!password_verify($oldPass, $row['password'])) {
            $flash = 'danger:Password lama salah.';
        } elseif ($newPass !== $confirm) {
            $flash = 'danger:Konfirmasi password tidak cocok.';
        } elseif (strlen($newPass) < 6) {
            $flash = 'danger:Password minimal 6 karakter.';
        } else {
            $stmt = $db->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->execute([password_hash($newPass, PASSWORD_BCRYPT), $_SESSION['user_id']]);
            $flash = 'success:Password berhasil diubah.';
        }
    }

    header('Location: account.php?flash=' . urlencode($flash)); exit;
}

if (isset($_GET['flash'])) $flash = $_GET['flash'];
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($flash): [$ft,$fm] = explode(':', $flash, 2); ?>
<div class="alert alert-<?= $ft==='success'?'success':'danger' ?> alert-dismissible fade show py-2 small">
    <?= htmlspecialchars($fm) ?><button class="btn-close btn-sm" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="page-title">Akun</div>
<div class="page-subtitle">Kelola username dan password kamu</div>

<div class="row g-3" style="max-width:560px">
    <!-- Ganti Username -->
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-person me-2"></i>Ganti Username</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="change_username">
                    <div class="mb-3">
                        <label class="form-label">Username Saat Ini</label>
                        <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($_SESSION['username']??'') ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username Baru</label>
                        <input type="text" name="new_username" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="confirm_password" class="form-control form-control-sm" required>
                    </div>
                    <button class="btn btn-primary btn-sm">Simpan Username</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Ganti Password -->
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-lock me-2"></i>Ganti Password</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="old_password" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="new_password" class="form-control form-control-sm" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_new_password" class="form-control form-control-sm" required>
                    </div>
                    <button class="btn btn-primary btn-sm">Simpan Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
