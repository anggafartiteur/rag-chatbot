<?php
/**
 * cron/auto-ingest.php
 * Jalankan via cron job di server:
 *   * * * * * php /path/to/rag-chatbot/cron/auto-ingest.php >> /tmp/rag-ingest.log 2>&1
 */
require_once dirname(__DIR__) . '/vendor/autoload.php';
use Dotenv\Dotenv;
use App\Neuron\ChatBot;
use NeuronAI\RAG\DataLoader\FileDataLoader;
use NeuronAI\RAG\DataLoader\StringDataLoader;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$name = $_ENV['DB_NAME'] ?? 'rag_chatbot';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$pdo  = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

$schedule = $pdo->query("SELECT * FROM auto_ingest_schedule WHERE id=1")->fetch();

if (!$schedule || !$schedule['enabled']) {
    echo "[" . date('Y-m-d H:i:s') . "] Auto-ingest nonaktif, skip.\n";
    exit;
}

// Cek apakah sudah waktunya
if ($schedule['next_run'] && strtotime($schedule['next_run']) > time()) {
    echo "[" . date('Y-m-d H:i:s') . "] Belum waktunya. Next run: {$schedule['next_run']}\n";
    exit;
}

echo "[" . date('Y-m-d H:i:s') . "] Menjalankan auto-ingest...\n";

$sources     = $pdo->query("SELECT * FROM ingest_sources WHERE enabled=1")->fetchAll();
$bot         = ChatBot::make();
$totalChunks = 0;

foreach ($sources as $src) {
    $cfg   = json_decode($src['config'], true);
    $label = $src['label'];
    $count = 0;

    try {
        if ($src['type'] === 'file') {
            $path = dirname(__DIR__) . '/' . ltrim($cfg['path'] ?? './knowledge', './');
            $docs = FileDataLoader::for($path)->getDocuments();
            $bot->addDocuments($docs);
            $count = count($docs);

        } elseif ($src['type'] === 'mysql') {
            $table = $cfg['table'] ?? '';
            $colT  = $cfg['col_title'] ?? '';
            $colC  = $cfg['col_content'] ?? '';
            $where = $cfg['where'] ?? '';
            $sql   = "SELECT `{$colT}`, `{$colC}` FROM `{$table}`" . ($where ? " WHERE {$where}" : "");
            $rows  = $pdo->query($sql)->fetchAll();
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
                if ($html) {
                    $text = preg_replace('/\s+/', ' ', strip_tags($html));
                    $docs = StringDataLoader::for($text)->getDocuments();
                    $bot->addDocuments($docs);
                    $count += count($docs);
                }
            }
        }

        $totalChunks += $count;
        echo "  ✅ {$label}: {$count} chunks\n";
        $pdo->prepare("INSERT INTO ingest_log (source,chunks,status) VALUES (?,?,'success')")->execute([$label, $count]);

    } catch (Exception $e) {
        echo "  ❌ {$label}: " . $e->getMessage() . "\n";
        $pdo->prepare("INSERT INTO ingest_log (source,chunks,status,message) VALUES (?,0,'error',?)")->execute([$label, $e->getMessage()]);
    }
}

// Hitung next_run
$interval = $schedule['interval_value'] . ' ' . $schedule['interval_unit'];
$nextRun  = date('Y-m-d H:i:s', strtotime("+{$interval}"));
$pdo->prepare("UPDATE auto_ingest_schedule SET last_run=NOW(), next_run=? WHERE id=1")->execute([$nextRun]);

echo "[" . date('Y-m-d H:i:s') . "] Selesai. Total {$totalChunks} chunks. Next run: {$nextRun}\n";
