<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
use Dotenv\Dotenv;
use App\LeadManager;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true);
$leadId = (int)($input['lead_id'] ?? 0);

if (!$leadId) {
    echo json_encode(['success' => false, 'message' => 'Lead ID tidak valid.']);
    exit;
}

try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $name = $_ENV['DB_NAME'] ?? 'rag_chatbot';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    $pdo  = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $lead = $pdo->prepare("SELECT * FROM leads WHERE id = ?")->execute([$leadId])
        ? $pdo->prepare("SELECT * FROM leads WHERE id = ?") : null;

    $stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
    $stmt->execute([$leadId]);
    $lead = $stmt->fetch();

    if (!$lead) {
        echo json_encode(['success' => false, 'message' => 'Lead tidak ditemukan.']);
        exit;
    }

    // Cek ada recipients aktif
    $recipients = $pdo->query("SELECT * FROM email_recipients WHERE active = 1")->fetchAll();
    if (empty($recipients)) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada penerima email aktif. Tambahkan di halaman Recipients.']);
        exit;
    }

    $leadData = [
        'nama'       => $lead['nama'],
        'whatsapp'   => $lead['whatsapp'],
        'email'      => $lead['email'],
        'perusahaan' => $lead['perusahaan'],
        'intensi'    => $lead['intensi'],
    ];

    $manager = new LeadManager($pdo);
    $manager->sendEmail($leadData, $lead['summary'] ?? '', (int)$lead['id']);

    echo json_encode(['success' => true, 'message' => 'Email berhasil dikirim ke ' . count($recipients) . ' penerima.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal kirim email: ' . $e->getMessage()]);
}
