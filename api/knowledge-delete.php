<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true);
$filename = basename(trim($input['filename'] ?? ''));

if (!$filename) {
    echo json_encode(['success' => false, 'message' => 'Nama file tidak valid.']);
    exit;
}

$filePath = dirname(__DIR__) . '/knowledge/' . $filename;

if (!file_exists($filePath)) {
    echo json_encode(['success' => false, 'message' => 'File tidak ditemukan.']);
    exit;
}

unlink($filePath);
echo json_encode(['success' => true, 'message' => 'File berhasil dihapus.']);
