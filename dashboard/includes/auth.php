<?php
function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/dashboard/login.php');
        exit;
    }
}

function isLoggedIn(): bool {
    
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['user_id']);
}

function login(string $username, string $password): bool {
    $stmt = getDB()->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $username;
        return true;
    }
    return false;
}

function logout(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    session_destroy();
    header('Location: ' . BASE_URL . '/dashboard/login.php');
    exit;
}
