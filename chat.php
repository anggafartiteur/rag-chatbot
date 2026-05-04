<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Neuron\ChatBot;
use NeuronAI\Chat\Messages\UserMessage;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

$input       = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message tidak boleh kosong']);
    exit;
}

// Load settings dari MySQL
$settings = [];
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $name = $_ENV['DB_NAME'] ?? 'rag_chatbot';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    $pdo  = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $rows = $pdo->query("SELECT `key`, `value` FROM bot_settings")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) $settings[$row['key']] = $row['value'];
} catch (Exception $e) {
    // Jika DB belum tersedia, lanjut dengan settings kosong (default behavior)
}

try {
    $response = ChatBot::make()->withSettings($settings)
        ->chat(new UserMessage($userMessage))
        ->getMessage();

    echo json_encode(['reply' => $response->getContent(), 'error' => null]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['reply' => null, 'error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}