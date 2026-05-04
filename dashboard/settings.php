<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$pageTitle = 'Settings Bot';
$db = getDB();

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['bot_name','persona','business_info','language','tone','length','format','topic_limit','out_of_topic','unknown','closing'];
    foreach ($fields as $f) saveSetting($f, trim($_POST[$f] ?? ''));
    header('Location: settings.php?flash=success:Settings berhasil disimpan.'); exit;
}

if (isset($_GET['flash'])) $flash = $_GET['flash'];
$s = getAllSettings();

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($flash): [$ft,$fm] = explode(':', $flash, 2); ?>
<div class="alert alert-<?= $ft==='success'?'success':'danger' ?> alert-dismissible fade show py-2 small">
    <?= htmlspecialchars($fm) ?><button class="btn-close btn-sm" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="page-title">Settings Bot</div>
<div class="page-subtitle">Konfigurasi persona, gaya jawaban, dan batasan chatbot</div>

<form method="POST">
<div class="row g-3">

    <!-- Persona -->
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-robot me-2"></i>Persona & Identitas</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nama Bot</label>
                        <input type="text" name="bot_name" class="form-control form-control-sm"
                            value="<?= htmlspecialchars($s['bot_name']??'') ?>"
                            placeholder="cth: Brew, Asisten Kopi">
                        <div class="form-text">Kosongkan untuk default.</div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Role / Persona</label>
                        <input type="text" name="persona" class="form-control form-control-sm"
                            value="<?= htmlspecialchars($s['persona']??'') ?>"
                            placeholder="cth: customer service kedai kopi yang ramah dan berpengetahuan">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Informasi Bisnis</label>
                        <textarea name="business_info" class="form-control form-control-sm" rows="2"
                            placeholder="cth: Kopi Nusantara, kedai kopi specialty di Bandung, buka sejak 2019."><?= htmlspecialchars($s['business_info']??'') ?></textarea>
                        <div class="form-text">Konteks bisnis yang selalu disertakan.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gaya Jawaban -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-chat-text me-2"></i>Gaya Jawaban</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Bahasa Respons</label>
                    <select name="language" class="form-select form-select-sm">
                        <option value=""       <?= ($s['language']??'')==''  ?'selected':'' ?>>Ikuti bahasa pengguna (default)</option>
                        <option value="id"     <?= ($s['language']??'')=='id'?'selected':'' ?>>Selalu Bahasa Indonesia</option>
                        <option value="en"     <?= ($s['language']??'')=='en'?'selected':'' ?>>Selalu Bahasa Inggris</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tone / Gaya Komunikasi</label>
                    <select name="tone" class="form-select form-select-sm">
                        <option value=""         <?= ($s['tone']??'')==''        ?'selected':'' ?>>Default (natural)</option>
                        <option value="friendly" <?= ($s['tone']??'')=='friendly'?'selected':'' ?>>Ramah & Santai</option>
                        <option value="formal"   <?= ($s['tone']??'')=='formal'  ?'selected':'' ?>>Formal & Profesional</option>
                        <option value="casual"   <?= ($s['tone']??'')=='casual'  ?'selected':'' ?>>Kasual (gaul)</option>
                        <option value="concise"  <?= ($s['tone']??'')=='concise' ?'selected':'' ?>>Singkat & To the point</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Panjang Jawaban</label>
                    <select name="length" class="form-select form-select-sm">
                        <option value=""       <?= ($s['length']??'')==''     ?'selected':'' ?>>Default</option>
                        <option value="short"  <?= ($s['length']??'')=='short'?'selected':'' ?>>Singkat (1-2 kalimat)</option>
                        <option value="medium" <?= ($s['length']??'')=='medium'?'selected':'' ?>>Sedang (paragraf)</option>
                        <option value="detail" <?= ($s['length']??'')=='detail'?'selected':'' ?>>Detail & lengkap</option>
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label">Format Output</label>
                    <select name="format" class="form-select form-select-sm">
                        <option value=""          <?= ($s['format']??'')==''         ?'selected':'' ?>>Default</option>
                        <option value="bullets"   <?= ($s['format']??'')=='bullets'  ?'selected':'' ?>>Selalu poin-poin</option>
                        <option value="paragraph" <?= ($s['format']??'')=='paragraph'?'selected':'' ?>>Selalu paragraf</option>
                        <option value="mixed"     <?= ($s['format']??'')=='mixed'    ?'selected':'' ?>>Campuran</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Batasan -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-shield-check me-2"></i>Batasan & Fokus</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Batasan Topik</label>
                    <input type="text" name="topic_limit" class="form-control form-control-sm"
                        value="<?= htmlspecialchars($s['topic_limit']??'') ?>"
                        placeholder="cth: hanya seputar menu, harga, dan operasional kedai">
                    <div class="form-text">Kosongkan jika tidak dibatasi.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Respons Jika di Luar Topik</label>
                    <input type="text" name="out_of_topic" class="form-control form-control-sm"
                        value="<?= htmlspecialchars($s['out_of_topic']??'') ?>"
                        placeholder="cth: Maaf, saya hanya bisa membantu soal Kopi Nusantara.">
                </div>
                <div class="mb-3">
                    <label class="form-label">Jika Tidak Ada di Knowledge Base</label>
                    <select name="unknown" class="form-select form-select-sm">
                        <option value="honest"  <?= ($s['unknown']??'honest')=='honest' ?'selected':'' ?>>Jujur bahwa tidak tahu</option>
                        <option value="general" <?= ($s['unknown']??'')=='general'?'selected':'' ?>>Boleh jawab dari pengetahuan umum</option>
                        <option value="redirect"<?= ($s['unknown']??'')=='redirect'?'selected':'' ?>>Arahkan ke kontak terkait</option>
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label">Pesan Penutup / Disclaimer</label>
                    <input type="text" name="closing" class="form-control form-control-sm"
                        value="<?= htmlspecialchars($s['closing']??'') ?>"
                        placeholder="cth: Untuk info lebih lanjut hubungi WA 0812-xxxx-xxxx.">
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2 me-2"></i>Simpan Settings
        </button>
    </div>
</div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
