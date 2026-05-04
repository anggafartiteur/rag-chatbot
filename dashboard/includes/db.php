<?php
function getDB(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $name = $_ENV['DB_NAME'] ?? 'rag_chatbot';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';

    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    return $pdo;
}

function getSetting(string $key, string $default = ''): string {
    $stmt = getDB()->prepare("SELECT `value` FROM bot_settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? (string)$row['value'] : $default;
}

function getAllSettings(): array {
    $rows = getDB()->query("SELECT `key`, `value` FROM bot_settings")->fetchAll();
    $out = [];
    foreach ($rows as $r) $out[$r['key']] = $r['value'];
    return $out;
}

function saveSetting(string $key, string $value): void {
    $stmt = getDB()->prepare("INSERT INTO bot_settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=?, `updated_at`=NOW()");
    $stmt->execute([$key, $value, $value]);
}
