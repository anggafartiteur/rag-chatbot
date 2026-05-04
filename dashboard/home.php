<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();

$pageTitle = 'Overview';

// Stats
$db = getDB();
$totalChunks  = 0;
$totalSources = (int)$db->query("SELECT COUNT(*) FROM ingest_sources WHERE enabled=1")->fetchColumn();
$lastIngest   = $db->query("SELECT ran_at, chunks, status FROM ingest_log ORDER BY ran_at DESC LIMIT 1")->fetch();
$schedule     = $db->query("SELECT * FROM auto_ingest_schedule WHERE id=1")->fetch();
$recentLogs   = $db->query("SELECT * FROM ingest_log ORDER BY ran_at DESC LIMIT 8")->fetchAll();

// Coba hitung chunks dari vector store
$vectorDir  = dirname(__DIR__) . '/' . ltrim($_ENV['VECTOR_STORE_DIR'] ?? './storage/vectors', './');
$vectorName = $_ENV['VECTOR_STORE_NAME'] ?? 'knowledge_base';
$vectorFile = $vectorDir . '/' . $vectorName . '.json';
if (file_exists($vectorFile)) {
    $data = json_decode(file_get_contents($vectorFile), true);
    $totalChunks = is_array($data) ? count($data) : 0;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-title">Overview</div>
<div class="page-subtitle">Ringkasan status RAG Chatbot kamu</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-value text-primary"><?= number_format($totalChunks) ?></div>
            <div class="stat-label"><i class="bi bi-file-text me-1"></i>Total Chunks Ter-index</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-value text-success"><?= $totalSources ?></div>
            <div class="stat-label"><i class="bi bi-database me-1"></i>Sumber Aktif</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <?php if ($schedule && $schedule['enabled']): ?>
                <div class="stat-value text-warning" style="font-size:18px">
                    Setiap <?= $schedule['interval_value'] ?> <?= $schedule['interval_unit'] ?>
                </div>
                <div class="stat-label"><i class="bi bi-arrow-repeat me-1"></i>Auto-Ingest Aktif</div>
            <?php else: ?>
                <div class="stat-value text-secondary" style="font-size:18px">Nonaktif</div>
                <div class="stat-label"><i class="bi bi-arrow-repeat me-1"></i>Auto-Ingest</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-lightning me-2"></i>Quick Actions</div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="ingest.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-database-add me-2"></i>Kelola Ingest
                </a>
                <a href="knowledge.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-journals me-2"></i>Lihat Knowledge Base
                </a>
                <a href="settings.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-gear me-2"></i>Settings Bot
                </a>
                <a href="../index.html" target="_blank" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-chat-dots me-2"></i>Buka Chatbot
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Logs -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history me-2"></i>Log Ingest Terbaru</div>
            <div class="card-body p-0">
                <?php if (empty($recentLogs)): ?>
                    <div class="p-4 text-center text-secondary small">Belum ada log ingest.</div>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr>
                        <th style="font-size:12px">Sumber</th>
                        <th style="font-size:12px">Chunks</th>
                        <th style="font-size:12px">Status</th>
                        <th style="font-size:12px">Waktu</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                    <tr>
                        <td style="font-size:13px"><?= htmlspecialchars($log['source']) ?></td>
                        <td style="font-size:13px"><?= $log['chunks'] ?></td>
                        <td>
                            <?php if ($log['status'] === 'success'): ?>
                                <span class="badge bg-success">✓ OK</span>
                            <?php else: ?>
                                <span class="badge bg-danger">✗ Error</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#555570"><?= $log['ran_at'] ?></td>
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
