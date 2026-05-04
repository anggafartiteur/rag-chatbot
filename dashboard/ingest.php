<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$pageTitle = 'Ingest';
$db = getDB();

// Handle form actions
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_source') {
        $type   = $_POST['type'] ?? '';
        $label  = trim($_POST['label'] ?? '');
        $config = [];
        if ($type === 'file')  $config = ['path' => trim($_POST['path'] ?? './knowledge')];
        if ($type === 'mysql') $config = [
            'table'   => trim($_POST['db_table'] ?? ''),
            'col_title'  => trim($_POST['col_title'] ?? ''),
            'col_content'=> trim($_POST['col_content'] ?? ''),
            'where'   => trim($_POST['db_where'] ?? ''),
        ];
        if ($type === 'url')   $config = ['urls' => array_filter(array_map('trim', explode("\n", $_POST['urls'] ?? '')))];
        $stmt = $db->prepare("INSERT INTO ingest_sources (type,label,config,enabled) VALUES (?,?,?,1)");
        $stmt->execute([$type, $label, json_encode($config)]);
        $flash = 'success:Sumber berhasil ditambahkan.';
    }

    if ($action === 'toggle_source') {
        $id = (int)$_POST['id'];
        $db->prepare("UPDATE ingest_sources SET enabled = 1 - enabled WHERE id=?")->execute([$id]);
        $flash = 'success:Status sumber diperbarui.';
    }

    if ($action === 'delete_source') {
        $db->prepare("DELETE FROM ingest_sources WHERE id=?")->execute([(int)$_POST['id']]);
        $flash = 'success:Sumber dihapus.';
    }

    if ($action === 'save_schedule') {
        $enabled = isset($_POST['auto_enabled']) ? 1 : 0;
        $unit    = $_POST['interval_unit']  ?? 'hours';
        $value   = max(1, (int)($_POST['interval_value'] ?? 24));
        // Hitung next_run
        $next = $enabled ? date('Y-m-d H:i:s', strtotime("+{$value} {$unit}")) : null;
        $stmt = $db->prepare("UPDATE auto_ingest_schedule SET enabled=?, interval_unit=?, interval_value=?, next_run=? WHERE id=1");
        $stmt->execute([$enabled, $unit, $value, $next]);
        $flash = 'success:Jadwal auto-ingest disimpan.';
    }

    header('Location: ingest.php?flash=' . urlencode($flash)); exit;
}

if (isset($_GET['flash'])) $flash = $_GET['flash'];

$sources  = $db->query("SELECT * FROM ingest_sources ORDER BY id DESC")->fetchAll();
$schedule = $db->query("SELECT * FROM auto_ingest_schedule WHERE id=1")->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($flash): [$ftype,$fmsg] = explode(':', $flash, 2); ?>
<div class="alert alert-<?= $ftype==='success'?'success':'danger' ?> alert-dismissible fade show py-2 small">
    <?= htmlspecialchars($fmsg) ?>
    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="page-title">Ingest</div>
<div class="page-subtitle">Kelola sumber data dan jadwal pengindeksan knowledge base</div>

<div class="row g-3">
    <!-- Sumber Data -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-database me-2"></i>Sumber Data</span>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSourceModal">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Sumber
                </button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($sources)): ?>
                    <div class="p-4 text-center text-secondary small">Belum ada sumber data. Tambahkan sumber untuk mulai ingest.</div>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr>
                        <th style="font-size:12px">Label</th>
                        <th style="font-size:12px">Tipe</th>
                        <th style="font-size:12px">Konfigurasi</th>
                        <th style="font-size:12px">Status</th>
                        <th style="font-size:12px">Aksi</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($sources as $src):
                        $cfg = json_decode($src['config'], true);
                    ?>
                    <tr>
                        <td style="font-size:13px"><?= htmlspecialchars($src['label']) ?></td>
                        <td>
                            <?php $typeColors = ['file'=>'primary','mysql'=>'warning','url'=>'info']; ?>
                            <span class="badge bg-<?= $typeColors[$src['type']]??'secondary' ?> badge-source">
                                <?= strtoupper($src['type']) ?>
                            </span>
                        </td>
                        <td style="font-size:12px;color:#777790">
                            <?php if ($src['type']==='file'):  ?>📁 <?= htmlspecialchars($cfg['path']??'') ?>
                            <?php elseif ($src['type']==='mysql'): ?>🗄️ <?= htmlspecialchars($cfg['table']??'') ?>
                            <?php elseif ($src['type']==='url'): ?>🌐 <?= count($cfg['urls']??[]) ?> URL
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($src['enabled']): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="toggle_source">
                                <input type="hidden" name="id" value="<?= $src['id'] ?>">
                                <button class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:11px">
                                    <?= $src['enabled'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                </button>
                            </form>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Hapus sumber ini?')">
                                <input type="hidden" name="action" value="delete_source">
                                <input type="hidden" name="id" value="<?= $src['id'] ?>">
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

    <!-- Manual Ingest -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-play-circle me-2"></i>Manual Ingest</div>
            <div class="card-body">
                <p class="text-secondary small mb-3">Jalankan ingest sekarang dari semua sumber yang aktif.</p>
                <button class="btn btn-primary" id="btnRunIngest">
                    <i class="bi bi-play-fill me-2"></i>Jalankan Ingest Sekarang
                </button>
                <div id="ingestResult" class="mt-3 small d-none"></div>
            </div>
        </div>
    </div>

    <!-- Auto Ingest -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-arrow-repeat me-2"></i>Auto-Ingest</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="save_schedule">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="auto_enabled" id="autoEnabled"
                            <?= $schedule['enabled'] ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="autoEnabled">Aktifkan auto-ingest</label>
                    </div>
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-4">
                            <label class="form-label">Setiap</label>
                            <input type="number" name="interval_value" class="form-control form-control-sm"
                                value="<?= $schedule['interval_value'] ?? 24 ?>" min="1" max="999">
                        </div>
                        <div class="col-5">
                            <label class="form-label">Unit</label>
                            <select name="interval_unit" class="form-select form-select-sm">
                                <option value="minutes" <?= ($schedule['interval_unit']??'')=='minutes'?'selected':'' ?>>Menit</option>
                                <option value="hours"   <?= ($schedule['interval_unit']??'hours')=='hours'?'selected':'' ?>>Jam</option>
                                <option value="days"    <?= ($schedule['interval_unit']??'')=='days'?'selected':'' ?>>Hari</option>
                            </select>
                        </div>
                    </div>
                    <?php if ($schedule['last_run']): ?>
                        <div class="text-secondary small mb-2">
                            <i class="bi bi-clock me-1"></i>Terakhir: <?= $schedule['last_run'] ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($schedule['next_run'] && $schedule['enabled']): ?>
                        <div class="text-secondary small mb-3">
                            <i class="bi bi-clock-fill me-1"></i>Berikutnya: <?= $schedule['next_run'] ?>
                        </div>
                    <?php endif; ?>
                    <div class="alert alert-dark-info small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Auto-ingest berjalan via <strong>cron job</strong>. Tambahkan ini ke crontab server:<br>
                        <code class="text-warning">* * * * * php <?= dirname(__DIR__) ?>/cron/auto-ingest.php</code>
                    </div>
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-check2 me-1"></i>Simpan Jadwal
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Sumber -->
<div class="modal fade" id="addSourceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:#13131a;border-color:#2a2a38">
            <div class="modal-header" style="border-color:#2a2a38">
                <h5 class="modal-title small fw-semibold">Tambah Sumber Data</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_source">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Label</label>
                        <input type="text" name="label" class="form-control form-control-sm" placeholder="cth: Dokumen Produk" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe Sumber</label>
                        <select name="type" class="form-select form-select-sm" id="sourceType" onchange="switchSourceType(this.value)">
                            <option value="file">📁 File (folder lokal)</option>
                            <option value="mysql">🗄️ MySQL (dari database)</option>
                            <option value="url">🌐 URL (dari website)</option>
                        </select>
                    </div>
                    <!-- File -->
                    <div id="cfg_file">
                        <label class="form-label">Path Folder</label>
                        <input type="text" name="path" class="form-control form-control-sm" value="./knowledge">
                        <div class="form-text">Relatif dari root project.</div>
                    </div>
                    <!-- MySQL -->
                    <div id="cfg_mysql" class="d-none">
                        <div class="mb-2">
                            <label class="form-label">Nama Tabel</label>
                            <input type="text" name="db_table" class="form-control form-control-sm" placeholder="cth: artikel">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col">
                                <label class="form-label">Kolom Judul</label>
                                <input type="text" name="col_title" class="form-control form-control-sm" placeholder="judul">
                            </div>
                            <div class="col">
                                <label class="form-label">Kolom Konten</label>
                                <input type="text" name="col_content" class="form-control form-control-sm" placeholder="konten">
                            </div>
                        </div>
                        <label class="form-label">WHERE (opsional)</label>
                        <input type="text" name="db_where" class="form-control form-control-sm" placeholder="cth: status = 'published'">
                    </div>
                    <!-- URL -->
                    <div id="cfg_url" class="d-none">
                        <label class="form-label">Daftar URL (satu per baris)</label>
                        <textarea name="urls" class="form-control form-control-sm" rows="4"
                            placeholder="https://example.com/halaman-1&#10;https://example.com/halaman-2"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-color:#2a2a38">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraJs = <<<JS
function switchSourceType(type) {
    document.querySelectorAll('[id^="cfg_"]').forEach(el => el.classList.add('d-none'));
    document.getElementById('cfg_' + type).classList.remove('d-none');
}

document.getElementById('btnRunIngest').addEventListener('click', async function() {
    const btn = this;
    const result = document.getElementById('ingestResult');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
    result.className = 'mt-3 small d-none';
    try {
        const res = await fetch('../api/ingest-run.php', { method: 'POST' });
        const data = await res.json();
        result.className = 'mt-3 small alert ' + (data.success ? 'alert-success' : 'alert-danger');
        result.innerHTML = data.message;
    } catch(e) {
        result.className = 'mt-3 small alert alert-danger';
        result.innerHTML = 'Gagal menghubungi server.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-play-fill me-2"></i>Jalankan Ingest Sekarang';
    }
});
JS;
require_once __DIR__ . '/includes/footer.php';
?>
