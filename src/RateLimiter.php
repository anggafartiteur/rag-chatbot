<?php

namespace App;

use PDO;

class RateLimiter
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Cek apakah request boleh dilanjutkan.
     * Return true = boleh, false = kena limit.
     */
    public function check(string $key, int $maxRequests, int $windowMinutes): bool
    {
        $ip      = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $lockKey = $key . ':' . $ip;
        $window  = date('Y-m-d H:i:s', strtotime("-{$windowMinutes} minutes"));

        // Hapus record lama
        $this->pdo->prepare("DELETE FROM rate_limits WHERE `key` = ? AND created_at < ?")
            ->execute([$lockKey, $window]);

        // Hitung request dalam window
        $count = (int)$this->pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE `key` = ?")
            ->execute([$lockKey]) ?
            $this->pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE `key` = ?")->execute([$lockKey]) : 0;

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE `key` = ?");
        $stmt->execute([$lockKey]);
        $count = (int)$stmt->fetchColumn();

        if ($count >= $maxRequests) {
            return false;
        }

        // Catat request ini
        $this->pdo->prepare("INSERT INTO rate_limits (`key`, created_at) VALUES (?, NOW())")
            ->execute([$lockKey]);

        return true;
    }

    /**
     * Ambil sisa request yang boleh dilakukan
     */
    public function remaining(string $key, int $maxRequests, int $windowMinutes): int
    {
        $ip      = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $lockKey = $key . ':' . $ip;
        $window  = date('Y-m-d H:i:s', strtotime("-{$windowMinutes} minutes"));

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE `key` = ? AND created_at >= ?");
        $stmt->execute([$lockKey, $window]);
        $count = (int)$stmt->fetchColumn();

        return max(0, $maxRequests - $count);
    }

    /**
     * Cek apakah IP ada di whitelist
     */
    public static function isWhitelisted(string $ip): bool
    {
        $whitelist = array_filter(array_map('trim', explode(',', $_ENV['RATE_LIMIT_WHITELIST'] ?? '')));
        return in_array($ip, $whitelist);
    }
}
