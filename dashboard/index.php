<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
header('Location: ' . BASE_URL . '/dashboard/home.php');
exit;
