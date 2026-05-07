<?php
session_start();
$_SESSION['chat_history'] = [];
$_SESSION['lead_saved']   = false;
header('Content-Type: application/json');
echo json_encode(['success' => true]);
