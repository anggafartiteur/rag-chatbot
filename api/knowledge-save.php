<?php
session_start();
require_once dirname(__DIR__) . '/vendor/autoload.php';
use Dotenv\Dotenv;
use App\Neuron\ChatBot;
use NeuronAI\RAG\DataLoader\FileDataLoader;
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true);
$filename = basename(trim($input['filename'] ?? ''));
$content  = $input['content'] ?? '';
$runIngest = (bool)($input['ingest'] ?? false);

// Validasi
if (!$filename) {
    echo json_encode(['success' => false, 'message' => 'Nama file tidak boleh kosong.']);
    exit;
}

// Pastikan ekstensi .md atau .txt
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if (!in_array($ext, ['md', 'txt', 'html'])) {
    echo json_encode(['success' => false, 'message' => 'Ekstensi file harus .md, .txt, atau .html']);
    exit;
}

$knowledgeDir = dirname(__DIR__) . '/knowledge';
if (!is_dir($knowledgeDir)) mkdir($knowledgeDir, 0755, true);

$filePath = $knowledgeDir . '/' . $filename;
file_put_contents($filePath, $content);

// Jalankan ingest jika diminta
$ingestResult = null;
if ($runIngest) {
    // Override vector store path
    $_ENV['VECTOR_STORE_DIR'] = dirname(__DIR__) . '/storage/vectors';

    try {
        require_once dirname(__DIR__) . '/src/Neuron/ChatBot.php';

        $bot  = ChatBot::make();
        $docs = FileDataLoader::for($filePath)->getDocuments();
        $bot->addDocuments($docs);
        $ingestResult = count($docs) . ' chunks berhasil di-iadndex.';
    } catch (Exception $e) {
        $ingestResult = 'File disimpan, tapi ingest gagal: ' . $e->getMessage();
    }
}

echo json_encode([
    'success' => true,
    'message' => 'File berhasil disimpan.' . ($ingestResult ? ' ' . $ingestResult : ''),
]);
