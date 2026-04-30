# 🤖 RAG Chatbot – Neuron AI + PHP

Chatbot sederhana berbasis **RAG (Retrieval-Augmented Generation)** menggunakan framework [Neuron AI](https://neuron-ai.dev) dan PHP. Chatbot ini bisa menjawab pertanyaan berdasarkan knowledge base kustom yang kamu berikan.

---

## 📁 Struktur Project

```
rag-chatbot/
├── src/
│   └── Neuron/
│       └── ChatBot.php        # Kelas RAG utama (Neuron AI)
├── knowledge/
│   └── produk-info.md         # Contoh dokumen knowledge base
├── storage/
│   └── vectors/               # Folder vector store (auto-dibuat)
├── index.html                 # UI chat
├── chat.php                   # API endpoint
├── ingest.php                 # Script untuk load dokumen
├── composer.json
├── .env.example
└── README.md
```

---

## 🚀 Cara Setup

### 1. Install Dependencies

```bash
composer install
```

### 2. Konfigurasi Environment

```bash
cp .env.example .env
```

Edit file `.env` dan isi API key kamu:

```env
# Pilih provider LLM
LLM_PROVIDER=openai          # atau: anthropic

# OpenAI (dibutuhkan untuk embeddings)
OPENAI_API_KEY=sk-xxxxxxxxxxxx
OPENAI_MODEL=gpt-4o-mini
OPENAI_EMBEDDING_MODEL=text-embedding-3-small

# Anthropic (opsional, jika mau pakai Claude)
ANTHROPIC_API_KEY=sk-ant-xxxxx
ANTHROPIC_MODEL=claude-3-5-haiku-20241022
```

> **Catatan:** OpenAI API key **selalu dibutuhkan** karena digunakan untuk embeddings, bahkan jika kamu pakai Anthropic sebagai LLM.

### 3. Tambahkan Dokumen Knowledge Base

Taruh file `.txt`, `.md`, atau `.html` di folder `knowledge/`. Contoh sudah tersedia di `knowledge/produk-info.md`.

### 4. Index Dokumen ke Vector Store

```bash
php ingest.php
```

Atau untuk file/folder spesifik:

```bash
php ingest.php path/to/file.md
php ingest.php path/to/folder/
```

### 5. Jalankan PHP Server

```bash
php -S localhost:8080
```

Buka browser ke: **http://localhost:8080**

---

## 🔧 Cara Kerja

```
Pertanyaan User
      │
      ▼
 [Neuron RAG]
      │
      ├─ 1. Ubah pertanyaan jadi vektor (embeddings)
      ├─ 2. Cari dokumen relevan di vector store (similarity search)
      ├─ 3. Tambahkan konteks dokumen ke prompt
      └─ 4. Kirim ke LLM → dapat jawaban yang grounded
```

---

## 🧩 Komponen

| Komponen | Default | Alternatif |
|---|---|---|
| **LLM** | OpenAI GPT-4o-mini | Anthropic Claude |
| **Embeddings** | OpenAI text-embedding-3-small | VoyageAI |
| **Vector Store** | FileVectorStore (lokal) | Pinecone, PostgreSQL |

---

## 📝 Menambah Dokumen Baru

1. Tambahkan file ke folder `knowledge/`
2. Jalankan ulang: `php ingest.php`
3. Chatbot langsung bisa menjawab tentang konten baru

---

## 💡 Tips

- **FileVectorStore** cocok untuk development dan demo. Untuk production, gunakan Pinecone atau PostgreSQL.
- Pastikan dokumen dibuat dalam format yang jelas dan terstruktur agar RAG lebih akurat.
- Jika jawaban kurang relevan, coba perbanyak dan perjelas isi dokumen knowledge base.

---

## 📚 Referensi

- [Neuron AI Documentation](https://docs.neuron-ai.dev)
- [Neuron AI GitHub](https://github.com/neuron-core/neuron-ai)
