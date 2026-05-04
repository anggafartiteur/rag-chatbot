<?php
/**
 * install.php — Jalankan sekali untuk setup database
 * Hapus file ini setelah selesai install!
 */
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$name = $_ENV['DB_NAME'] ?? 'rag_chatbot';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

try {
    // Buat database jika belum ada
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$name}`");

    // Tabel users
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `username`   VARCHAR(100) NOT NULL UNIQUE,
        `password`   VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Tabel bot_settings (key-value)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `bot_settings` (
        `key`        VARCHAR(100) PRIMARY KEY,
        `value`      TEXT,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Tabel ingest_sources
    $pdo->exec("CREATE TABLE IF NOT EXISTS `ingest_sources` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `type`       ENUM('file','mysql','url') NOT NULL,
        `label`      VARCHAR(255) NOT NULL,
        `config`     JSON NOT NULL,
        `enabled`    TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Tabel ingest_log
    $pdo->exec("CREATE TABLE IF NOT EXISTS `ingest_log` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `source`     VARCHAR(100),
        `chunks`     INT DEFAULT 0,
        `status`     ENUM('success','error') DEFAULT 'success',
        `message`    TEXT,
        `ran_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Tabel auto_ingest_schedule
    $pdo->exec("CREATE TABLE IF NOT EXISTS `auto_ingest_schedule` (
        `id`              INT AUTO_INCREMENT PRIMARY KEY,
        `enabled`         TINYINT(1) DEFAULT 0,
        `interval_unit`   ENUM('minutes','hours','days') DEFAULT 'hours',
        `interval_value`  INT DEFAULT 24,
        `last_run`        TIMESTAMP NULL,
        `next_run`        TIMESTAMP NULL
    )");

    // Insert default schedule row
    $pdo->exec("INSERT IGNORE INTO `auto_ingest_schedule` (`id`) VALUES (1)");

    // Insert default admin user (password: admin123)
    $defaultPass = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO `users` (`username`, `password`) VALUES (?, ?)");
    $stmt->execute(['admin', $defaultPass]);

    // Insert default bot settings
    $defaults = [
        'bot_name' => '', 'persona' => '', 'business_info' => '',
        'language' => '', 'tone' => '', 'length' => '', 'format' => '',
        'topic_limit' => '', 'out_of_topic' => '', 'unknown' => 'honest', 'closing' => '',
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO `bot_settings` (`key`, `value`) VALUES (?, ?)");
    foreach ($defaults as $k => $v) $stmt->execute([$k, $v]);

    echo "<h2 style='font-family:sans-serif;color:green'>✅ Instalasi berhasil!</h2>";
    echo "<p style='font-family:sans-serif'>Database <strong>{$name}</strong> sudah siap.</p>";
    echo "<p style='font-family:sans-serif'>Login: <strong>admin</strong> / <strong>admin123</strong> — segera ganti password!</p>";
    echo "<p style='font-family:sans-serif;color:red'><strong>Hapus file install.php sekarang!</strong></p>";
    echo "<p style='font-family:sans-serif'><a href='dashboard/'>Ke Dashboard →</a></p>";

} catch (PDOException $e) {
    echo "<h2 style='font-family:sans-serif;color:red'>❌ Error</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
