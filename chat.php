<?php
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/vendor/autoload.php';

use App\Neuron\ChatBot;
use App\LeadManager;
use App\RateLimiter;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Chat\Messages\AssistantMessage;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

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

// ── Koneksi DB ───────────────────────────────────────────
$pdo      = null;
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
    // lanjut dengan settings kosong
}

// ── Rate Limiting ────────────────────────────────────────
if ($pdo) {
    $ip        = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $whitelist = array_filter(array_map('trim', explode(',', $_ENV['RATE_LIMIT_WHITELIST'] ?? '')));

    if (!in_array($ip, $whitelist)) {
        $maxRequests   = (int)($settings['rate_limit_chat_max']    ?? $_ENV['RATE_LIMIT_CHAT_MAX']    ?? 20);
        $windowMinutes = (int)($settings['rate_limit_chat_window'] ?? $_ENV['RATE_LIMIT_CHAT_WINDOW'] ?? 10);

        $limiter = new RateLimiter($pdo);
        if (!$limiter->check('chat', $maxRequests, $windowMinutes)) {
            http_response_code(429);
            echo json_encode([
                'reply' => null,
                'error' => "Terlalu banyak permintaan. Silakan tunggu {$windowMinutes} menit sebelum mencoba lagi.",
            ]);
            exit;
        }
    }
}

// ── Session history ──────────────────────────────────────
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}
if (!isset($_SESSION['lead_saved'])) {
    $_SESSION['lead_saved'] = false;
}

$history = &$_SESSION['chat_history'];

// ── Build ChatBot dengan history ─────────────────────────
try {
    $bot = ChatBot::make()->withSettings($settings);

    // Build messages array dengan history (max 10 pesan terakhir)
    $messages = [];
    foreach (array_slice($history, -10) as $h) {
        if ($h['role'] === 'user') {
            $messages[] = new UserMessage($h['content']);
        } else {
            $messages[] = new AssistantMessage($h['content']);
        }
    }
    $messages[] = new UserMessage($userMessage);

    $response  = $bot->chat($messages)->getMessage();
    $replyText = $response->getContent();

    // Simpan ke history
    $history[] = ['role' => 'user',      'content' => $userMessage];
    $history[] = ['role' => 'assistant', 'content' => $replyText];

    // Batasi history 30 pesan terakhir
    if (count($history) > 30) {
        $history = array_slice($history, -30);
    }

    // ── Lead Detection ───────────────────────────────────
    if (!$_SESSION['lead_saved'] && $pdo) {
        $triggerPhrases = [
            // Indonesia
            'tim sales', 'akan menghubungi', 'akan kami teruskan', 'akan diteruskan',
            'akan segera dihubungi', 'akan kami sampaikan', 'kami akan follow up',
            'informasi ini akan', 'detail anda akan', 'tim kami akan',
            // English
            'sales team', 'will contact you', 'will get back to you', 'will reach out',
            'will be forwarded', 'our team will', 'we will follow up', 'forwarded to',
            // Chinese
            '销售团队', '会联系您', '我们会跟进',
        ];

        $replyLower = mb_strtolower($replyText);
        $triggered  = false;
        foreach ($triggerPhrases as $phrase) {
            if (str_contains($replyLower, mb_strtolower($phrase))) {
                $triggered = true;
                break;
            }
        }

        file_put_contents(__DIR__ . '/debug.log',
            date('Y-m-d H:i:s') . ' TRIGGERED: ' . ($triggered ? 'YES' : 'NO') . "\n",
            FILE_APPEND
        );

        if ($triggered) {
            $historyText = '';
            foreach ($history as $h) {
                $role = $h['role'] === 'user' ? 'Customer' : 'Chatbot';
                $historyText .= "{$role}: {$h['content']}\n\n";
            }

            $summaryBot    = ChatBot::make()->withSettings([]);
            $summaryPrompt = <<<PROMPT
Based on this conversation, extract the following in JSON format (no markdown, just raw JSON):
{
  "nama": "full name of customer",
  "whatsapp": "whatsapp number (digits only, no + or spaces)",
  "email": "email address",
  "perusahaan": "company name",
  "intensi": "brief description of what they need in English",
  "summary": "3-5 sentence professional summary in English for the sales team, covering who they are, what they need, and any important details"
}

If a field is not mentioned, use null. Do not guess.

Conversation:
{$historyText}
PROMPT;

            $summaryResponse = $summaryBot->chat(new UserMessage($summaryPrompt))->getMessage();
            $summaryContent  = $summaryResponse->getContent();

            // Parse JSON
            $jsonMatch = null;
            preg_match('/\{.*\}/s', $summaryContent, $jsonMatch);
            if ($jsonMatch) {
                $data = json_decode($jsonMatch[0], true);
                if ($data && json_last_error() === JSON_ERROR_NONE) {
                    $lead = [
                        'nama'       => $data['nama']       ?? null,
                        'whatsapp'   => $data['whatsapp']   ?? null,
                        'email'      => $data['email']      ?? null,
                        'perusahaan' => $data['perusahaan'] ?? null,
                        'intensi'    => $data['intensi']    ?? null,
                    ];
                    $summary = $data['summary'] ?? $summaryContent;

                    $leadManager = new LeadManager($pdo);
                    try {
                        $leadManager->save($lead, $summary, $history);
                        $_SESSION['lead_saved'] = true;
                        $leadSaved = true;
                    } catch (\Exception $e) {
                        error_log('Lead save error: ' . $e->getMessage());
                    }
                    $_SESSION['lead_saved'] = true;
                }
            }
        }
    }

    echo json_encode([
        'reply'      => $replyText,
        'error'      => null,
        'lead_saved' => $leadSaved ?? false,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['reply' => null, 'error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
