<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
use Dotenv\Dotenv;
use App\Neuron\ChatBot;
use NeuronAI\RAG\DataLoader\FileDataLoader;
use NeuronAI\RAG\DataLoader\StringDataLoader;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

header('Content-Type: application/json');

// Auth check — harus dari session dashboard atau internal
session_start();
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Koneksi DB
$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$name = $_ENV['DB_NAME'] ?? 'rag_chatbot';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$pdo  = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

// ── Rate Limiting ────────────────────────────────────────
$ip        = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$whitelist = array_filter(array_map('trim', explode(',', $_ENV['RATE_LIMIT_WHITELIST'] ?? '')));

if (!in_array($ip, $whitelist)) {
    $maxRequests   = (int)($_ENV['RATE_LIMIT_INGEST_MAX']    ?? 5);
    $windowMinutes = (int)($_ENV['RATE_LIMIT_INGEST_WINDOW'] ?? 10);

    $limiter = new \App\RateLimiter($pdo);
    if (!$limiter->check('ingest', $maxRequests, $windowMinutes)) {
        echo json_encode([
            'success' => false,
            'message' => "Terlalu banyak permintaan. Tunggu {$windowMinutes} menit.",
        ]);
        exit;
    }
}
// ── End Rate Limiting ────────────────────────────────────

$sources = $pdo->query("SELECT * FROM ingest_sources WHERE enabled=1")->fetchAll();

if (empty($sources)) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada sumber aktif. Tambahkan sumber di halaman Ingest.']);
    exit;
}



// Override VECTOR_STORE_DIR ke path absolut dari root project
$_ENV['VECTOR_STORE_DIR'] = dirname(__DIR__) . '/storage/vectors';

$bot = ChatBot::make();
$totalChunks = 0;
$messages    = [];

foreach ($sources as $src) {
    $cfg   = json_decode($src['config'], true);
    $label = $src['label'];
    $count = 0;

    try {
        if ($src['type'] === 'file') {
            $path = dirname(__DIR__) . '/' . ltrim($cfg['path'] ?? './knowledge', './');
            if (!file_exists($path)) throw new Exception("Path tidak ditemukan: {$path}");
            $docs = FileDataLoader::for($path)->getDocuments();
            $bot->addDocuments($docs);
            $count = count($docs);

        } elseif ($src['type'] === 'mysql') {
            $table   = $cfg['table']        ?? '';
            $colT    = $cfg['col_title']    ?? '';
            $colC    = $cfg['col_content']  ?? '';
            $where   = $cfg['where']        ?? '';
            $sql     = "SELECT `{$colT}`, `{$colC}` FROM `{$table}`" . ($where ? " WHERE {$where}" : "");
            $rows    = $pdo->query($sql)->fetchAll();
            foreach ($rows as $row) {
                $text = ($colT && $row[$colT] ? $row[$colT] . "\n" : '') . ($row[$colC] ?? '');
                if (trim($text)) {
                    $docs = StringDataLoader::for($text)->getDocuments();
                    $bot->addDocuments($docs);
                    $count += count($docs);
                }
            }

        } elseif ($src['type'] === 'url') {
            foreach ($cfg['urls'] ?? [] as $url) {
                $html = @file_get_contents($url);
                if (!$html) throw new Exception("Gagal fetch: {$url}");
                $text = strip_tags($html);
                $text = preg_replace('/\s+/', ' ', $text);
                $docs = StringDataLoader::for($text)->getDocuments();
                $bot->addDocuments($docs);
                $count += count($docs);
            }
        }

        $totalChunks += $count;
        $messages[]   = "✅ {$label}: {$count} chunks";
        $pdo->prepare("INSERT INTO ingest_log (source,chunks,status) VALUES (?,?,'success')")->execute([$label, $count]);

    } catch (Exception $e) {
        $messages[] = "❌ {$label}: " . $e->getMessage();
        $pdo->prepare("INSERT INTO ingest_log (source,chunks,status,message) VALUES (?,0,'error',?)")->execute([$label, $e->getMessage()]);
    }
}

// Update last_run
$pdo->prepare("UPDATE auto_ingest_schedule SET last_run=NOW() WHERE id=1")->execute();

echo json_encode([
    'success' => true,
    'message' => implode('<br>', $messages) . "<br><strong>Total: {$totalChunks} chunks</strong>",
]);