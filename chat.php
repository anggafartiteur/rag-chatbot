<?php
/**
 * API Endpoint: chat.php
 * ======================
 * Menerima POST request dengan JSON body:
 *   { "message": "pertanyaan kamu" }
 *
 * Mengembalikan JSON:
 *   { "reply": "jawaban dari AI", "error": null }
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Neuron\ChatBot;
use NeuronAI\Chat\Messages\UserMessage;
use Dotenv\Dotenv;

// Load env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// CORS headers (untuk development)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Parse input
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message tidak boleh kosong']);
    exit;
}

try {
    $response = ChatBot::make()
        ->chat(new UserMessage($userMessage))
        ->getMessage();

    echo json_encode([
        'reply' => $response->getContent(),
        'error' => null,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'reply' => null,
        'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
    ]);
}