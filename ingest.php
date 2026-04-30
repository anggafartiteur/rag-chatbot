#!/usr/bin/env php
<?php
/**
 * Script: ingest.php
 * ==================
 * Gunakan script ini untuk memuat dokumen ke dalam vector store.
 * Jalankan sekali (atau setiap kali kamu update knowledge base).
 *
 * Cara pakai:
 *   php ingest.php                     -> load semua file dari folder /knowledge
 *   php ingest.php path/to/file.txt    -> load file spesifik
 *   php ingest.php path/to/folder      -> load semua file dari folder tertentu
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Neuron\ChatBot;
use NeuronAI\RAG\DataLoader\FileDataLoader;
use NeuronAI\RAG\DataLoader\StringDataLoader;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== RAG Chatbot - Document Ingestion ===\n\n";

// Tentukan sumber dokumen
$target = $argv[1] ?? __DIR__ . '/knowledge';

$bot = ChatBot::make();
$totalDocs = 0;

if (is_file($target)) {
    // Load satu file
    echo "📄 Memuat file: {$target}\n";
    $documents = FileDataLoader::for($target)->getDocuments();
    $bot->addDocuments($documents);
    $totalDocs += count($documents);
    echo "   ✅ {$totalDocs} chunks berhasil di-index\n";

} elseif (is_dir($target)) {
    // Load semua file dari folder
    echo "📁 Memuat semua dokumen dari: {$target}\n\n";

    $supportedExtensions = ['txt', 'md', 'html'];
    $files = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, $supportedExtensions)) {
                $files[] = $file->getPathname();
            }
        }
    }

    if (empty($files)) {
        echo "⚠️  Tidak ada file .txt, .md, atau .html ditemukan di folder tersebut.\n";
        echo "    Tambahkan dokumen ke folder /knowledge lalu jalankan ulang.\n";
        exit(1);
    }

    foreach ($files as $filePath) {
        echo "📄 Memuat: " . basename($filePath) . "\n";
        try {
            $documents = FileDataLoader::for($filePath)->getDocuments();
            $bot->addDocuments($documents);
            $count = count($documents);
            $totalDocs += $count;
            echo "   ✅ {$count} chunks\n";
        } catch (Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "❌ Target tidak valid: {$target}\n";
    exit(1);
}

echo "\n✅ Selesai! Total {$totalDocs} chunks berhasil dimuat ke vector store.\n";
echo "   Sekarang kamu bisa menjalankan chatbot.\n\n";
