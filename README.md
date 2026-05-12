# 🤖 RAG Chatbot – Kemindo Group

A PHP-based RAG (Retrieval-Augmented Generation) chatbot powered by [Neuron AI](https://neuron-ai.dev), built for Kemindo Group to handle customer inquiries and capture leads automatically.

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)
![Neuron AI](https://img.shields.io/badge/Neuron_AI-3.x-black?style=flat)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green?style=flat)

---

## ✨ Features

### Chatbot
- Answers questions based on your own knowledge base (RAG)
- Multi-language support (Indonesian, English, Chinese)
- Conversation memory within a session
- Customizable persona, tone, language, and response style
- New conversation button to reset session

### Lead Capture
- Automatically detects when a customer needs to be forwarded to sales
- Collects name, company, WhatsApp, email, and intention
- AI-generated conversation summary for the sales team
- Saves leads to MySQL database
- Sends email notification to sales team automatically

**tumben banget kamu baca readme"**

### Dashboard (Login Protected)
- **Overview** – stats, recent ingest logs
- **Ingest** – manage data sources (file, MySQL, URL), manual ingest, auto-ingest scheduler
- **Knowledge Base** – view indexed chunks, clear all
- **Settings Bot** – configure persona, tone, language, topic limits, closing message
- **Leads** – view all leads, update status (New/Contacted/Closed), resend email notification
- **Email Recipients** – manage who receives lead notifications
- **Account** – change username and password

### Infrastructure
- Support 8 LLM providers: Anthropic, OpenAI, Gemini, Mistral, Deepseek, XAI, Ollama, Cohere
- Support 6 embedding providers: Voyage AI, OpenAI, Gemini, Mistral, Cohere, Ollama
- File vector store (local, no external DB needed)
- Auto-ingest via cron job with configurable interval
- MySQL for settings, leads, and dashboard data

---

## 🚀 Quick Start

### 1. Install dependencies
```bash
composer install
```

### 2. Configure environment
```bash
cp .env.example .env
```
Fill in your API keys and database credentials.

### 3. Setup database
Import `rag_chatbot.sql` via phpMyAdmin, then import `rag_chatbot_update.sql`.

Default login: `admin` / `admin123` — **change immediately after login.**

### 4. Add knowledge base documents
Put `.md`, `.txt`, or `.html` files in the `knowledge/` folder.

### 5. Run ingest
```bash
php ingest.php
```
Or use the **Ingest** page in the dashboard.

### 6. Start server
```bash
php -S localhost:8080
```

Open **http://localhost:8080** for the chatbot, **http://localhost:8080/dashboard** for the admin panel.

---

## 📁 Project Structure

```
rag-chatbot/
├── src/
│   ├── Neuron/ChatBot.php      # RAG core class
│   └── LeadManager.php         # Lead saving & email notification
├── dashboard/                  # Admin panel pages
│   ├── includes/               # Auth, DB, header, footer helpers
│   ├── home.php, ingest.php, knowledge.php
│   ├── settings.php, leads.php, recipients.php, account.php
├── api/                        # Internal API endpoints
│   ├── ingest-run.php
│   ├── resend-lead.php
│   └── reset-session.php
├── cron/
│   └── auto-ingest.php         # Cron job for scheduled ingest
├── knowledge/                  # Knowledge base documents
├── storage/vectors/            # Vector store (auto-created)
├── index.html                  # Chatbot UI
├── chat.php                    # Chat API endpoint
├── ingest.php                  # CLI ingest script
├── install.php                 # One-time DB setup
└── .env.example
```

---

## 🔧 LLM & Embeddings Configuration

| Layer | Recommended | Notes |
|---|---|---|
| LLM | Anthropic Claude | Or OpenAI, Gemini, Mistral, etc. |
| Embeddings | Voyage AI | Free 200M tokens/month — partner of Anthropic |
| Vector Store | FileVectorStore | Local file, no DB needed |

---

## 📚 References

- [Neuron AI Docs](https://docs.neuron-ai.dev)
- [Voyage AI](https://www.voyageai.com)
- [Anthropic API](https://docs.anthropic.com)

---

## 📄 License

MIT
