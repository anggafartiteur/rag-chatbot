<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$pageTitle = 'Leads';
$db = getDB();

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $db->prepare("UPDATE leads SET status=? WHERE id=?")->execute([$_POST['status'], (int)$_POST['id']]);
    header('Location: leads.php'); exit;
}

$status  = $_GET['status'] ?? '';
$where   = $status ? "WHERE status = '{$status}'" : '';
$leads   = $db->query("SELECT * FROM leads {$where} ORDER BY created_at DESC")->fetchAll();
$counts  = $db->query("SELECT status, COUNT(*) as c FROM leads GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$total   = array_sum($counts);

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-start justify-content-between mb-1">
    <div>
        <div class="page-title">Leads</div>
        <div class="page-subtitle">Potential customers dari chatbot</div>
    </div>
    <a href="recipients.php" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-envelope me-1"></i>Kelola Penerima Email
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <a href="leads.php" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-value"><?= $total ?></div>
                <div class="stat-label">Total Leads</div>
            </div>
        </a>
    </div>
    <div class="col-sm-3">
        <a href="leads.php?status=new" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-value text-warning"><?= $counts['new'] ?? 0 ?></div>
                <div class="stat-label">New</div>
            </div>
        </a>
    </div>
    <div class="col-sm-3">
        <a href="leads.php?status=contacted" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-value text-info"><?= $counts['contacted'] ?? 0 ?></div>
                <div class="stat-label">Contacted</div>
            </div>
        </a>
    </div>
    <div class="col-sm-3">
        <a href="leads.php?status=closed" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-value text-success"><?= $counts['closed'] ?? 0 ?></div>
                <div class="stat-label">Closed</div>
            </div>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($leads)): ?>
            <div class="p-5 text-center text-secondary">
                <i class="bi bi-people" style="font-size:32px"></i>
                <div class="mt-2 small">Belum ada leads. Leads akan muncul otomatis saat chatbot mengumpulkan data customer.</div>
            </div>
        <?php else: ?>
        <table class="table table-sm mb-0">
            <thead><tr>
                <th style="font-size:12px">Nama</th>
                <th style="font-size:12px">Perusahaan</th>
                <th style="font-size:12px">Kontak</th>
                <th style="font-size:12px">Intensi</th>
                <th style="font-size:12px">Status</th>
                <th style="font-size:12px">Waktu</th>
                <th style="font-size:12px">Aksi</th>
            </tr></thead>
            <tbody>
            <?php foreach ($leads as $lead): ?>
            <tr>
                <td style="font-size:13px"><?= htmlspecialchars($lead['nama'] ?? '-') ?></td>
                <td style="font-size:13px"><?= htmlspecialchars($lead['perusahaan'] ?? '-') ?></td>
                <td style="font-size:12px">
                    <?php if ($lead['whatsapp']): ?>
                        <a href="https://wa.me/<?= htmlspecialchars($lead['whatsapp']) ?>" target="_blank" class="text-success text-decoration-none">
                            <i class="bi bi-whatsapp"></i> <?= htmlspecialchars($lead['whatsapp']) ?>
                        </a><br>
                    <?php endif; ?>
                    <?php if ($lead['email']): ?>
                        <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="text-primary text-decoration-none" style="font-size:11px">
                            <i class="bi bi-envelope"></i> <?= htmlspecialchars($lead['email']) ?>
                        </a>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;max-width:200px">
                    <?= htmlspecialchars(mb_substr($lead['intensi'] ?? '-', 0, 80)) ?>
                    <?= strlen($lead['intensi'] ?? '') > 80 ? '…' : '' ?>
                </td>
                <td>
                    <?php
                    $badges = ['new' => 'warning', 'contacted' => 'info', 'closed' => 'success'];
                    $badge  = $badges[$lead['status']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?= $badge ?>"><?= ucfirst($lead['status']) ?></span>
                </td>
                <td style="font-size:11px;color:#555570"><?= date('d M Y H:i', strtotime($lead['created_at'])) ?></td>
                <td>
                    <button class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:11px"
                        data-bs-toggle="modal" data-bs-target="#leadModal<?= $lead['id'] ?>">
                        Detail
                    </button>
                </td>
            </tr>

            <!-- Modal Detail -->
            <div class="modal fade" id="leadModal<?= $lead['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content" style="background:#13131a;border-color:#2a2a38">
                        <div class="modal-header" style="border-color:#2a2a38">
                            <h5 class="modal-title small fw-semibold">
                                <?= htmlspecialchars($lead['nama'] ?? 'Lead') ?> –
                                <?= htmlspecialchars($lead['perusahaan'] ?? '') ?>
                            </h5>
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <div class="form-label">WhatsApp</div>
                                    <div style="font-size:14px">
                                        <?php if ($lead['whatsapp']): ?>
                                            <a href="https://wa.me/<?= htmlspecialchars($lead['whatsapp']) ?>" target="_blank" class="text-success">
                                                <i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($lead['whatsapp']) ?>
                                            </a>
                                        <?php else: ?>-<?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-label">Email</div>
                                    <div style="font-size:14px">
                                        <?php if ($lead['email']): ?>
                                            <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="text-primary">
                                                <?= htmlspecialchars($lead['email']) ?>
                                            </a>
                                        <?php else: ?>-<?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-label">Intensi</div>
                                    <div style="font-size:14px;color:#c0c0d8"><?= htmlspecialchars($lead['intensi'] ?? '-') ?></div>
                                </div>
                                <div class="col-12">
                                    <div class="form-label">Summary</div>
                                    <div style="font-size:13px;color:#c0c0d8;background:#0f0f13;padding:12px;border-radius:8px;line-height:1.7">
                                        <?= nl2br(htmlspecialchars($lead['summary'] ?? '-')) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <!-- Update Status -->
                                <form method="POST" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?= $lead['id'] ?>">
                                    <label class="form-label mb-0 small">Update Status:</label>
                                    <select name="status" class="form-select form-select-sm" style="width:auto">
                                        <option value="new"       <?= $lead['status']==='new'?'selected':''       ?>>New</option>
                                        <option value="contacted" <?= $lead['status']==='contacted'?'selected':'' ?>>Contacted</option>
                                        <option value="closed"    <?= $lead['status']==='closed'?'selected':''    ?>>Closed</option>
                                    </select>
                                    <button class="btn btn-sm btn-primary">Simpan</button>
                                </form>
                                <!-- Resend Email -->
                                <button class="btn btn-sm btn-outline-info" onclick="resendEmail(<?= $lead['id'] ?>, this)">
                                    <i class="bi bi-envelope-arrow-up me-1"></i>Kirim Email ke Sales
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="leadToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<?php
$extraJs = <<<JS
async function resendEmail(leadId, btn) {
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengirim...';

    try {
        const res  = await fetch('../api/resend-lead.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ lead_id: leadId }),
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'danger');
    } catch(e) {
        showToast('Gagal menghubungi server.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

function showToast(message, type) {
    const toast   = document.getElementById('leadToast');
    const toastMsg = document.getElementById('toastMsg');
    toast.className = 'toast align-items-center border-0 text-bg-' + type;
    toastMsg.textContent = message;
    bootstrap.Toast.getOrCreateInstance(toast, { delay: 4000 }).show();
}
JS;
require_once __DIR__ . '/includes/footer.php';
?>