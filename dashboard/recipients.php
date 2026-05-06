<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$pageTitle = 'Penerima Email';
$db = getDB();

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($name && $email) {
            $db->prepare("INSERT IGNORE INTO email_recipients (name, email) VALUES (?,?)")->execute([$name, $email]);
            $flash = 'success:Penerima berhasil ditambahkan.';
        }
    }
    if ($action === 'toggle') {
        $db->prepare("UPDATE email_recipients SET active = 1 - active WHERE id=?")->execute([(int)$_POST['id']]);
        $flash = 'success:Status diperbarui.';
    }
    if ($action === 'delete') {
        $db->prepare("DELETE FROM email_recipients WHERE id=?")->execute([(int)$_POST['id']]);
        $flash = 'success:Penerima dihapus.';
    }
    header('Location: recipients.php?flash=' . urlencode($flash)); exit;
}

if (isset($_GET['flash'])) $flash = $_GET['flash'];
$recipients = $db->query("SELECT * FROM email_recipients ORDER BY id ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($flash): [$ft,$fm] = explode(':', $flash, 2); ?>
<div class="alert alert-<?= $ft==='success'?'success':'danger' ?> alert-dismissible fade show py-2 small">
    <?= htmlspecialchars($fm) ?><button class="btn-close btn-sm" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="page-title">Penerima Email Notifikasi</div>
<div class="page-subtitle">Daftar email yang akan menerima notifikasi setiap ada lead baru</div>

<div class="row g-3">
    <!-- Form Tambah -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-person-plus me-2"></i>Tambah Penerima</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control form-control-sm"
                            placeholder="cth: Sales Team" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control form-control-sm"
                            placeholder="sales@kemindogroup.com" required>
                    </div>
                    <button class="btn btn-primary btn-sm w-100">Tambah</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Daftar -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-people me-2"></i>Daftar Penerima</div>
            <div class="card-body p-0">
                <?php if (empty($recipients)): ?>
                    <div class="p-4 text-center text-secondary small">Belum ada penerima.</div>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr>
                        <th style="font-size:12px">Nama</th>
                        <th style="font-size:12px">Email</th>
                        <th style="font-size:12px">Status</th>
                        <th style="font-size:12px">Aksi</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($recipients as $r): ?>
                    <tr>
                        <td style="font-size:13px"><?= htmlspecialchars($r['name']) ?></td>
                        <td style="font-size:13px"><?= htmlspecialchars($r['email']) ?></td>
                        <td>
                            <?php if ($r['active']): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:11px">
                                    <?= $r['active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                </button>
                            </form>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Hapus penerima ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:11px">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
