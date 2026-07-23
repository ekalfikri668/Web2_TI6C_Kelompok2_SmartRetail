<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/api.php';

header('Content-Type: application/json');

if (!isset($_SESSION['token'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

$orderId = $_POST['order_id'] ?? '';
$jumlahBayar = $_POST['jumlah_bayar'] ?? 0;

if (empty($orderId)) {
    echo json_encode(['success' => false, 'message' => 'ID Pesanan tidak valid.']);
    exit;
}

// Call API
$numericOrderId = is_numeric($orderId) ? (int)$orderId : $orderId;
$response = apiRequest('POST', '/payment', [
    'id_pesanan'   => $numericOrderId,
    'metode'       => 'QRIS',
    'jumlah_bayar' => (float)$jumlahBayar
]);

if ($response['success']) {
    // Update session mock order status if applicable
    if (isset($_SESSION['mock_orders_list'][$orderId])) {
        $_SESSION['mock_orders_list'][$orderId]['status'] = 'Diproses';
    }
    echo json_encode($response);
} else {
    // Fallback: update session mock order status if applicable to support offline experience
    if (isset($_SESSION['mock_orders_list'][$orderId])) {
        $_SESSION['mock_orders_list'][$orderId]['status'] = 'Diproses';
        echo json_encode([
            'success' => true,
            'message' => 'Pembayaran QRIS berhasil disimulasikan (Offline Mode).'
        ]);
    } else {
        echo json_encode($response);
    }
}
