<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../frontend/config/api.php';

header('Content-Type: application/json');

if (!isset($_SESSION['token'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'read') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID Notifikasi tidak valid.']);
        exit;
    }
    $res = apiRequest('PUT', '/notifications/' . $id . '/read');
    echo json_encode(['success' => $res['success'], 'message' => $res['message']]);
    exit;
}

if ($action === 'read_all') {
    $res = apiRequest('PUT', '/notifications/read-all');
    echo json_encode(['success' => $res['success'], 'message' => $res['message']]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal.']);
