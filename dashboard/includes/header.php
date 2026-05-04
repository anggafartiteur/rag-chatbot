<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> – RAG Chatbot</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #0f0f13; min-height: 100vh; }
        .sidebar {
            width: 240px; min-height: 100vh;
            background: #13131a;
            border-right: 1px solid #2a2a38;
            position: fixed; top: 0; left: 0;
            display: flex; flex-direction: column;
            z-index: 100;
        }
        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid #2a2a38;
            display: flex; align-items: center; gap: 10px;
        }
        .sidebar-brand .icon {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .sidebar-brand span { font-weight: 600; font-size: 15px; color: #f0f0f5; }
        .sidebar-brand small { display: block; font-size: 11px; color: #555570; }
        .nav-section-label {
            font-size: 10px; font-weight: 700; letter-spacing: 0.1em;
            text-transform: uppercase; color: #444460;
            padding: 16px 20px 6px;
        }
        .sidebar .nav-link {
            color: #8888a8; font-size: 14px;
            padding: 9px 20px; border-radius: 0;
            display: flex; align-items: center; gap: 10px;
            transition: all 0.15s;
        }
        .sidebar .nav-link:hover  { color: #c0c0d8; background: #1e1e2e; }
        .sidebar .nav-link.active { color: #fff; background: #1e1e30; border-left: 3px solid #6366f1; }
        .sidebar .nav-link i { font-size: 16px; width: 18px; text-align: center; }
        .sidebar-footer {
            margin-top: auto;
            padding: 16px 20px;
            border-top: 1px solid #2a2a38;
            font-size: 12px; color: #444460;
        }
        .main-content {
            margin-left: 240px;
            min-height: 100vh;
            padding: 32px;
        }
        .page-title { font-size: 22px; font-weight: 600; color: #f0f0f5; margin-bottom: 4px; }
        .page-subtitle { font-size: 13px; color: #555570; margin-bottom: 28px; }
        .card {
            background: #13131a;
            border: 1px solid #2a2a38;
            border-radius: 12px;
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid #2a2a38;
            padding: 14px 20px;
            font-weight: 600; font-size: 14px; color: #c0c0d8;
        }
        .form-control, .form-select {
            background: #0f0f13; border-color: #2a2a38; color: #e2e2e8;
        }
        .form-control:focus, .form-select:focus {
            background: #0f0f13; border-color: #6366f1; color: #e2e2e8;
            box-shadow: 0 0 0 0.2rem rgba(99,102,241,.2);
        }
        .form-label { color: #a0a0b8; font-size: 13px; }
        .form-text  { color: #444460; font-size: 11px; }
        .table { color: #c0c0d8; }
        .table > :not(caption) > * > * { background: transparent; border-color: #2a2a38; }
        .table tbody tr:hover td { background: #1a1a24; }
        .badge-source { font-size: 11px; }
        .stat-card { padding: 20px; border-radius: 12px; background: #13131a; border: 1px solid #2a2a38; }
        .stat-card .stat-value { font-size: 28px; font-weight: 700; color: #f0f0f5; }
        .stat-card .stat-label { font-size: 12px; color: #555570; margin-top: 2px; }
        .btn-primary { background: linear-gradient(135deg,#6366f1,#8b5cf6); border: none; }
        .btn-primary:hover { opacity: 0.9; }
        .alert-dark-info { background: #1a1a30; border-color: #3a3a60; color: #a0a0d0; }
        /* Mobile responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="icon">🤖</div>
        <div>
            <span>RAG Chatbot</span>
            <small>Dashboard</small>
        </div>
    </div>

    <div class="nav-section-label">Menu</div>
    <nav class="nav flex-column">
        <a href="<?= BASE_URL ?>/dashboard/home.php" class="nav-link <?= $currentPage==='home'?'active':'' ?>">
            <i class="bi bi-speedometer2"></i> Overview
        </a>
        <a href="<?= BASE_URL ?>/dashboard/ingest.php" class="nav-link <?= $currentPage==='ingest'?'active':'' ?>">
            <i class="bi bi-database-add"></i> Ingest
        </a>
        <a href="<?= BASE_URL ?>/dashboard/knowledge.php" class="nav-link <?= $currentPage==='knowledge'?'active':'' ?>">
            <i class="bi bi-journals"></i> Knowledge Base
        </a>
        <a href="<?= BASE_URL ?>/dashboard/settings.php" class="nav-link <?= $currentPage==='settings'?'active':'' ?>">
            <i class="bi bi-gear-fill"></i> Settings Bot
        </a>
    </nav>

    <div class="nav-section-label">Lainnya</div>
    <nav class="nav flex-column">
        <a href="<?= BASE_URL ?>/index.html" target="_blank" class="nav-link">
            <i class="bi bi-chat-dots"></i> Buka Chatbot
        </a>
        <a href="<?= BASE_URL ?>/dashboard/account.php" class="nav-link <?= $currentPage==='account'?'active':'' ?>">
            <i class="bi bi-person-circle"></i> Akun
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center justify-content-between">
            <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
            <a href="<?= BASE_URL ?>/dashboard/logout.php" class="text-danger text-decoration-none" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="main-content">
