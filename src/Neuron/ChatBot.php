<?php

namespace App\Neuron;

use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\Cohere\Cohere;
use NeuronAI\Providers\Deepseek\Deepseek;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\Mistral\Mistral;
use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Providers\XAI\XAI;
use NeuronAI\RAG\Embeddings\CohereEmbeddingsProvider;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\Embeddings\GeminiEmbeddingsProvider;
use NeuronAI\RAG\Embeddings\MistralEmbeddingsProvider;
use NeuronAI\RAG\Embeddings\OllamaEmbeddingsProvider;
use NeuronAI\RAG\Embeddings\OpenAIEmbeddingsProvider;
use NeuronAI\RAG\Embeddings\VoyageEmbeddingsProvider;
use NeuronAI\RAG\RAG;
use NeuronAI\RAG\VectorStore\FileVectorStore;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;

class ChatBot extends RAG
{
    protected array $settings = [];

    public function withSettings(array $settings): static
    {
        $this->settings = $settings;
        return $this;
    }

    protected function provider(): AIProviderInterface
    {
        $llm = strtolower($_ENV['LLM_PROVIDER'] ?? 'openai');

        return match ($llm) {
            'anthropic' => new Anthropic(
                key: $_ENV['ANTHROPIC_API_KEY'],
                model: $_ENV['ANTHROPIC_MODEL'] ?? 'claude-haiku-4-5-20251001',
            ),
            'gemini' => new Gemini(
                key: $_ENV['GEMINI_API_KEY'],
                model: $_ENV['GEMINI_MODEL'] ?? 'gemini-2.0-flash',
            ),
            'mistral' => new Mistral(
                key: $_ENV['MISTRAL_API_KEY'],
                model: $_ENV['MISTRAL_MODEL'] ?? 'mistral-small-latest',
            ),
            'deepseek' => new Deepseek(
                key: $_ENV['DEEPSEEK_API_KEY'],
                model: $_ENV['DEEPSEEK_MODEL'] ?? 'deepseek-chat',
            ),
            'xai' => new XAI(
                key: $_ENV['XAI_API_KEY'],
                model: $_ENV['XAI_MODEL'] ?? 'grok-3-mini',
            ),
            'ollama' => new Ollama(
                url: $_ENV['OLLAMA_URL'] ?? 'http://localhost:11434',
                model: $_ENV['OLLAMA_MODEL'] ?? 'llama3.2',
            ),
            'cohere' => new Cohere(
                key: $_ENV['COHERE_API_KEY'],
                model: $_ENV['COHERE_MODEL'] ?? 'command-r',
            ),
            default => new OpenAI(
                key: $_ENV['OPENAI_API_KEY'],
                model: $_ENV['OPENAI_MODEL'] ?? 'gpt-4o-mini',
            ),
        };
    }

    public function instructions(): string
    {
        $s = $this->settings;

        // ── Background ──────────────────────────────────────
        $background = [];

        $persona      = $s['persona']      ?? '';
        $botName      = $s['bot_name']     ?? '';
        $businessInfo = $s['business_info'] ?? '';

        if ($persona || $botName) {
            $who = $botName ? "bernama {$botName}" : '';
            $background[] = "Kamu adalah asisten AI {$who} yang berperan sebagai " . ($persona ?: 'asisten yang membantu') . ".";
        } else {
            $background[] = "Kamu adalah asisten AI yang ramah dan membantu.";
        }

        if ($businessInfo) {
            $background[] = "Konteks bisnis: {$businessInfo}";
        }

        $background[] = "Gunakan informasi dari knowledge base yang tersedia untuk menjawab pertanyaan.";
        $background[] = "Jika user menanyakan harga, negosiasi harga, ketersediaan produk di luar list, atau permintaan khusus lainnya, kumpulkan informasi berikut secara natural satu per satu: nama lengkap, nomor WhatsApp, alamat email, nama perusahaan, dan kebutuhan spesifik mereka.";
        $background[] = "Setelah semua informasi terkumpul, konfirmasi ke user bahwa informasi mereka akan diteruskan ke tim sales Kemindo dan tim sales akan menghubungi mereka segera.";
        $background[] = "Jangan menjanjikan harga atau ketersediaan produk secara spesifik. Arahkan selalu ke tim sales untuk negosiasi.";

        $unknownBehavior = $s['unknown'] ?? 'honest';
        if ($unknownBehavior === 'honest') {
            $background[] = "Jika informasi tidak tersedia di knowledge base, katakan dengan jujur bahwa kamu tidak tahu.";
        } elseif ($unknownBehavior === 'general') {
            $background[] = "Jika informasi tidak ada di knowledge base, kamu boleh menjawab dari pengetahuan umum, tapi beritahu bahwa itu bukan dari knowledge base.";
        } elseif ($unknownBehavior === 'redirect') {
            $background[] = "Jika informasi tidak ada di knowledge base, arahkan pengguna untuk menghubungi tim atau kontak terkait.";
        }

        $topicLimit = $s['topic_limit'] ?? '';
        if ($topicLimit) {
            $background[] = "Kamu hanya boleh menjawab pertanyaan yang berkaitan dengan: {$topicLimit}.";
        }

        $outOfTopic = $s['out_of_topic'] ?? '';
        if ($outOfTopic) {
            $background[] = "Jika pertanyaan di luar topik, jawab dengan: \"{$outOfTopic}\"";
        }

        $lang = $s['language'] ?? '';
        if ($lang === 'id') {
            $background[] = "Selalu jawab dalam Bahasa Indonesia, apapun bahasa yang digunakan pengguna.";
        } elseif ($lang === 'en') {
            $background[] = "Always respond in English, regardless of the language used by the user.";
        } else {
            $background[] = "Jawab selalu dalam bahasa yang sama dengan pertanyaan pengguna.";
        }

        // ── Steps ───────────────────────────────────────────
        $steps = [
            "Baca pertanyaan pengguna dengan seksama.",
            "Cari informasi relevan dari knowledge base.",
            "Susun jawaban yang informatif berdasarkan konteks yang ditemukan.",
        ];

        // ── Output ──────────────────────────────────────────
        $output = [];

        $tone = $s['tone'] ?? '';
        match ($tone) {
            'friendly' => $output[] = "Gunakan gaya bahasa yang ramah, hangat, dan bersahabat.",
            'formal'   => $output[] = "Gunakan gaya bahasa yang formal dan profesional.",
            'casual'   => $output[] = "Gunakan gaya bahasa yang santai dan kasual.",
            'concise'  => $output[] = "Jawab sesingkat mungkin, langsung ke inti.",
            default    => null,
        };

        $length = $s['length'] ?? '';
        match ($length) {
            'short'  => $output[] = "Berikan jawaban singkat, maksimal 1-2 kalimat.",
            'medium' => $output[] = "Berikan jawaban dalam 1-2 paragraf yang cukup.",
            'detail' => $output[] = "Berikan jawaban yang detail, lengkap, dan menyeluruh.",
            default  => $output[] = "Jawab secara ringkas namun lengkap.",
        };

        $format = $s['format'] ?? '';
        match ($format) {
            'bullets'   => $output[] = "Selalu gunakan format poin-poin (bullet points) dalam jawabanmu.",
            'paragraph' => $output[] = "Selalu gunakan format paragraf prosa, hindari bullet points.",
            'mixed'     => $output[] = "Gunakan format yang paling sesuai dengan konteks — paragraf atau poin-poin.",
            default     => $output[] = "Gunakan poin-poin jika perlu untuk kejelasan.",
        };

        $closing = $s['closing'] ?? '';
        if ($closing) {
            $output[] = "Tambahkan pesan berikut di akhir setiap jawaban: \"{$closing}\"";
        }

        return (string) new SystemPrompt(
            background: $background,
            steps: $steps,
            output: $output,
        );
    }

    protected function embeddings(): EmbeddingsProviderInterface
    {
        $provider = strtolower($_ENV['EMBEDDINGS_PROVIDER'] ?? 'voyage');

        return match ($provider) {
            'openai'  => new OpenAIEmbeddingsProvider(
                key: $_ENV['OPENAI_API_KEY'],
                model: $_ENV['OPENAI_EMBEDDING_MODEL'] ?? 'text-embedding-3-small',
            ),
            'gemini'  => new GeminiEmbeddingsProvider(
                key: $_ENV['GEMINI_API_KEY'],
                model: $_ENV['GEMINI_EMBEDDING_MODEL'] ?? 'text-embedding-004',
            ),
            'mistral' => new MistralEmbeddingsProvider(
                key: $_ENV['MISTRAL_API_KEY'],
                model: $_ENV['MISTRAL_EMBEDDING_MODEL'] ?? 'mistral-embed',
            ),
            'cohere'  => new CohereEmbeddingsProvider(
                key: $_ENV['COHERE_API_KEY'],
                model: $_ENV['COHERE_EMBEDDING_MODEL'] ?? 'embed-v4.0',
            ),
            'ollama'  => new OllamaEmbeddingsProvider(
                url: $_ENV['OLLAMA_URL'] ?? 'http://localhost:11434',
                model: $_ENV['OLLAMA_EMBEDDING_MODEL'] ?? 'nomic-embed-text',
            ),
            default   => new VoyageEmbeddingsProvider(
                key: $_ENV['VOYAGE_API_KEY'],
                model: $_ENV['VOYAGE_EMBEDDING_MODEL'] ?? 'voyage-3-lite',
            ),
        };
    }

    protected function vectorStore(): VectorStoreInterface
    {
        $dir  = $_ENV['VECTOR_STORE_DIR']  ?? __DIR__ . '/../../storage/vectors';
        $name = $_ENV['VECTOR_STORE_NAME'] ?? 'knowledge_base';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return new FileVectorStore(
            directory: $dir,
            name: $name,
        );
    }
}
