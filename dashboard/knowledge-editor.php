<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$pageTitle = 'Knowledge Editor';

$knowledgeDir = dirname(__DIR__) . '/knowledge';
if (!is_dir($knowledgeDir)) mkdir($knowledgeDir, 0755, true);

// Ambil daftar file
$files = [];
foreach (scandir($knowledgeDir) as $f) {
    if ($f === '.' || $f === '..') continue;
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    if (in_array($ext, ['md', 'txt', 'html'])) {
        $files[] = [
            'name'     => $f,
            'size'     => filesize($knowledgeDir . '/' . $f),
            'modified' => filemtime($knowledgeDir . '/' . $f),
        ];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-title">Knowledge Editor</div>
<div class="page-subtitle">Buat, edit, dan kelola file knowledge base langsung dari browser</div>

<div class="row g-3">

    <!-- File List -->
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-folder2-open me-2"></i>File</span>
                <button class="btn btn-sm btn-primary py-0 px-2" id="btnNewFile" style="font-size:12px">
                    <i class="bi bi-plus-lg me-1"></i>Baru
                </button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($files)): ?>
                    <div class="p-3 text-center text-secondary small">Belum ada file.</div>
                <?php else: ?>
                <ul class="list-unstyled mb-0" id="fileList">
                    <?php foreach ($files as $file): ?>
                    <li class="file-item" data-filename="<?= htmlspecialchars($file['name']) ?>">
                        <button class="btn w-100 text-start px-3 py-2 border-0 rounded-0 file-btn file-load-btn" data-filename="<?= htmlspecialchars($file['name']) ?>" style="font-size:13px;color:#c0c0d8">
                            <i class="bi bi-file-text me-2 text-primary" style="font-size:12px"></i>
                            <?= htmlspecialchars($file['name']) ?>
                            <div class="text-secondary" style="font-size:10px;margin-top:2px">
                                <?= number_format($file['size'] / 1024, 1) ?> KB ·
                                <?= date('d M Y', $file['modified']) ?>
                            </div>
                        </button>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Editor -->
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <i class="bi bi-pencil-square text-primary"></i>
                    <input type="text" id="filenameInput" class="form-control form-control-sm"
                        placeholder="nama-file.md" style="max-width:220px;font-size:13px"
                        value="">
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-danger" id="btnDelete" style="display:none">
                        <i class="bi bi-trash me-1"></i>Hapus
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="btnSave">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                    <button class="btn btn-sm btn-primary" id="btnSaveIngest">
                        <i class="bi bi-database-add me-1"></i>Simpan & Ingest
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <textarea id="editor" style="width:100%;min-height:520px;display:none"></textarea>
                <div id="editorPlaceholder" class="p-5 text-center text-secondary">
                    <i class="bi bi-file-earmark-plus" style="font-size:40px;opacity:.3"></i>
                    <div class="mt-3 small">Pilih file di sebelah kiri atau klik <strong>+ Baru</strong> untuk membuat file baru</div>
                </div>
            </div>
        </div>

        <!-- Toast -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
            <div id="editorToast" class="toast align-items-center border-0" role="alert" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body" id="editorToastMsg"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SimpleMDE -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.css">
<script src="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.js"></script>

<style>
.file-btn:hover { background: #1e1e30 !important; }
.file-btn.active { background: #1e1e30 !important; border-left: 3px solid #6366f1 !important; }
.CodeMirror { background: #0f0f13 !important; color: #d4d4e0 !important; border: none !important; min-height: 500px; }
.CodeMirror-scroll { min-height: 500px; }
.editor-toolbar { background: #13131a !important; border-color: #2a2a38 !important; }
.editor-toolbar a { color: #8888a8 !important; }
.editor-toolbar a:hover, .editor-toolbar a.active { background: #2a2a38 !important; color: #fff !important; }
.editor-toolbar i.separator { border-color: #2a2a38 !important; }
.CodeMirror-cursor { border-color: #6366f1 !important; }
.editor-preview { background: #1a1a24 !important; color: #d4d4e0 !important; }
.editor-preview-side { background: #1a1a24 !important; border-color: #2a2a38 !important; color: #d4d4e0 !important; }
</style>

<?php
$extraJs = <<<JS
let simplemde = null;
let currentFile = null;

// Event delegation for file load buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.file-load-btn')) {
        const btn = e.target.closest('.file-load-btn');
        const filename = btn.getAttribute('data-filename');
        if (filename) loadFile(filename);
    }
});

// Button event listeners
document.getElementById('btnNewFile')?.addEventListener('click', newFile);
document.getElementById('btnDelete')?.addEventListener('click', deleteFile);
document.getElementById('btnSave')?.addEventListener('click', function() { saveFile(false); });
document.getElementById('btnSaveIngest')?.addEventListener('click', function() { saveFile(true); });

function initEditor(content = '') {
    document.getElementById('editorPlaceholder').style.display = 'none';
    document.getElementById('editor').style.display = 'block';

    if (simplemde) {
        simplemde.toTextArea();
        simplemde = null;
    }

    simplemde = new SimpleMDE({
        element: document.getElementById('editor'),
        initialValue: content,
        spellChecker: false,
        autosave: { enabled: false },
        toolbar: [
            'bold','italic','heading','|',
            'quote','unordered-list','ordered-list','|',
            'link','table','|',
            'preview','side-by-side','fullscreen','|',
            'guide'
        ],
    });
}

function newFile() {
    currentFile = null;
    document.getElementById('filenameInput').value = '';
    document.getElementById('btnDelete').style.display = 'none';
    document.querySelectorAll('.file-btn').forEach(b => b.classList.remove('active'));
    initEditor(`# Judul Dokumen\n\n## Bagian 1\n\nIsi konten di sini...\n`);
    document.getElementById('filenameInput').focus();
}

async function loadFile(filename) {
    try {
        const res  = await fetch('../knowledge/' + encodeURIComponent(filename));
        const text = await res.text();
        currentFile = filename;
        document.getElementById('filenameInput').value = filename;
        document.getElementById('btnDelete').style.display = 'inline-flex';
        document.querySelectorAll('.file-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('[data-filename="' + filename + '"] .file-btn')?.classList.add('active');
        initEditor(text);
    } catch(e) {
        showToast('Gagal memuat file.', 'danger');
    }
}

async function saveFile(withIngest) {
    const filename = document.getElementById('filenameInput').value.trim();
    const content  = simplemde ? simplemde.value() : '';

    if (!filename) {
        showToast('Nama file tidak boleh kosong.', 'danger');
        return;
    }

    const btn = withIngest ? document.getElementById('btnSaveIngest') : document.getElementById('btnSave');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + (withIngest ? 'Menyimpan & Ingest...' : 'Menyimpan...');

    try {
        const res  = await fetch('../api/knowledge-save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ filename, content, ingest: withIngest }),
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'danger');
        if (data.success) {
            currentFile = filename;
            document.getElementById('btnDelete').style.display = 'inline-flex';
            // Reload halaman untuk update file list
            setTimeout(() => location.reload(), 1500);
        }
    } catch(e) {
        showToast('Gagal menyimpan file.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

async function deleteFile() {
    if (!currentFile) return;
    if (!confirm('Hapus file "' + currentFile + '"? Aksi ini tidak bisa dibatalkan.')) return;

    try {
        const res  = await fetch('../api/knowledge-delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ filename: currentFile }),
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'danger');
        if (data.success) setTimeout(() => location.reload(), 1000);
    } catch(e) {
        showToast('Gagal menghapus file.', 'danger');
    }
}

function showToast(message, type) {
    const toast    = document.getElementById('editorToast');
    const toastMsg = document.getElementById('editorToastMsg');
    toast.className = 'toast align-items-center border-0 text-bg-' + type;
    toastMsg.textContent = message;
    bootstrap.Toast.getOrCreateInstance(toast, { delay: 3000 }).show();
}
JS;
require_once __DIR__ . '/includes/footer.php';
?>
