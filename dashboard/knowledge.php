<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$pageTitle = 'Knowledge Base';

$vectorDir  = dirname(__DIR__) . '/' . ltrim($_ENV['VECTOR_STORE_DIR'] ?? './storage/vectors', './');
$vectorName = $_ENV['VECTOR_STORE_NAME'] ?? 'knowledge_base';
$vectorFile = $vectorDir . '/' . $vectorName . '.json';

$flash = '';

// Hapus semua
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear_all') {
    if (file_exists($vectorFile)) {
        file_put_contents($vectorFile, json_encode([]));
        $flash = 'success:Semua chunks berhasil dihapus.';
    }
    header('Location: knowledge.php?flash=' . urlencode($flash)); exit;
}

if (isset($_GET['flash'])) $flash = $_GET['flash'];

// Load chunks
$chunks = [];
if (file_exists($vectorFile)) {
    $data = json_decode(file_get_contents($vectorFile), true);
    if (is_array($data)) $chunks = $data;
}

// Pagination
$perPage     = 20;
$totalChunks = count($chunks);
$totalPages  = max(1, ceil($totalChunks / $perPage));
$page        = max(1, min((int)($_GET['page'] ?? 1), $totalPages));
$offset      = ($page - 1) * $perPage;
$pageChunks  = array_slice($chunks, $offset, $perPage);

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($flash): [$ft,$fm] = explode(':', $flash, 2); ?>
<div class="alert alert-<?= $ft==='success'?'success':'danger' ?> alert-dismissible fade show py-2 small">
    <?= htmlspecialchars($fm) ?><button class="btn-close btn-sm" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-start justify-content-between mb-1">
    <div>
        <div class="page-title">Knowledge Base</div>
        <div class="page-subtitle">
            <?= number_format($totalChunks) ?> chunks ter-index
            <?php if ($totalChunks > 0): ?>
                · Halaman <?= $page ?> dari <?= $totalPages ?>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($totalChunks > 0): ?>
    <form method="POST" onsubmit="return confirm('Hapus SEMUA chunks? Kamu perlu ingest ulang.')">
        <input type="hidden" name="action" value="clear_all">
        <button class="btn btn-sm btn-outline-danger">
            <i class="bi bi-trash me-1"></i>Hapus Semua
        </button>
    </form>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($chunks)): ?>
            <div class="p-5 text-center text-secondary">
                <i class="bi bi-inbox" style="font-size:32px"></i>
                <div class="mt-2 small">Belum ada chunks. Jalankan ingest terlebih dahulu.</div>
                <a href="ingest.php" class="btn btn-primary btn-sm mt-3">Ke Halaman Ingest</a>
            </div>
        <?php else: ?>
        <table class="table table-sm mb-0">
            <thead><tr>
                <th style="font-size:12px;width:50px">#</th>
                <th style="font-size:12px">Konten (preview)</th>
                <th style="font-size:12px;width:120px">Sumber</th>
            </tr></thead>
            <tbody>
            <?php foreach ($pageChunks as $i => $chunk):
                $content  = $chunk['document']['content'] ?? $chunk['content'] ?? '';
                $metadata = $chunk['document']['metadata'] ?? $chunk['metadata'] ?? [];
                $source   = $metadata['source'] ?? $metadata['file'] ?? '-';
                $preview  = mb_substr(strip_tags($content), 0, 180);
                if (mb_strlen($content) > 180) $preview .= '…';
            ?>
            <tr>
                <td style="font-size:12px;color:#555570"><?= $offset + $i + 1 ?></td>
                <td style="font-size:13px;line-height:1.5;color:#c0c0d8"><?= htmlspecialchars($preview) ?></td>
                <td style="font-size:11px;color:#555570"><?= htmlspecialchars(basename($source)) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center py-3">
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page<=1?'disabled':'' ?>">
                        <a class="page-link" href="?page=<?= $page-1 ?>">‹</a>
                    </li>
                    <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
                    <li class="page-item <?= $p===$page?'active':'' ?>">
                        <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>">
                        <a class="page-link" href="?page=<?= $page+1 ?>">›</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
