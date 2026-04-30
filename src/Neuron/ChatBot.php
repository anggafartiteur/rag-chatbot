<?php

namespace App\Neuron;

use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\Embeddings\VoyageEmbeddingsProvider;
use NeuronAI\RAG\RAG;
use NeuronAI\RAG\VectorStore\FileVectorStore;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use NeuronAI\Agent\SystemPrompt;

class ChatBot extends RAG
{
    protected function provider(): AIProviderInterface
    {
        $llmProvider = strtolower($_ENV['LLM_PROVIDER'] ?? 'openai');

        if ($llmProvider === 'anthropic') {
            return new Anthropic(
                key: $_ENV['ANTHROPIC_API_KEY'],
                model: $_ENV['ANTHROPIC_MODEL'] ?? 'claude-3-5-haiku-20241022',
            );
        }

        // Default: OpenAI
        return new OpenAI(
            key: $_ENV['OPENAI_API_KEY'],
            model: $_ENV['OPENAI_MODEL'] ?? 'gpt-4o-mini',
        );
    }

    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                "Kamu adalah asisten AI yang ramah dan membantu.",
                "Gunakan informasi dari knowledge base yang tersedia untuk menjawab pertanyaan.",
                "Jika informasi tidak tersedia di knowledge base, katakan dengan jujur bahwa kamu tidak tahu.",
                "Jawab selalu dalam bahasa yang sama dengan pertanyaan pengguna.",
            ],
            steps: [
                "Baca pertanyaan pengguna dengan seksama.",
                "Cari informasi relevan dari knowledge base.",
                "Susun jawaban yang jelas dan informatif berdasarkan konteks yang ditemukan.",
            ],
            output: [
                "Jawab secara ringkas namun lengkap.",
                "Gunakan poin-poin jika perlu untuk kejelasan.",
                "Sebutkan sumber jika relevan.",
            ]
        );
    }

    protected function embeddings(): EmbeddingsProviderInterface
    {
        return new VoyageEmbeddingsProvider(
            key: $_ENV['VOYAGE_API_KEY'],
            model: $_ENV['VOYAGE_EMBEDDING_MODEL'] ?? 'voyage-3-lite',
        );
    }

    protected function vectorStore(): VectorStoreInterface
    {
        $dir = $_ENV['VECTOR_STORE_DIR'] ?? __DIR__ . '/../../storage/vectors';
        $name = $_ENV['VECTOR_STORE_NAME'] ?? 'knowledge_base';

        // Pastikan direktori ada
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return new FileVectorStore(
            directory: $dir,
            name: $name,
        );
    }
}