<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/cek_login.php';
require_once __DIR__ . '/../config/api.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addressId     = $_POST['address_id'] ?? 0;
    $paymentMethod = $_POST['metode_pembayaran'] ?? 'Transfer Bank';

    // If no address selected, try to use a mock address ID
    if (!$addressId) {
        // Try session mock addresses
        if (!empty($_SESSION['mock_addresses'])) {
            $addressId = $_SESSION['mock_addresses'][0]['id'] ?? 1;
        } else {
            $addressId = 1; // Default mock address ID
        }
    }

    $cartItemIds = $_POST['cart_item_ids'] ?? '';

    $params = [
        'id_alamat'          => (int)$addressId,
        'metode_pembayaran'  => $paymentMethod
    ];

    if (!empty($cartItemIds)) {
        $params['cart_item_ids'] = $cartItemIds;
    }

    // Call API to create order
    $response = apiRequest('POST', '/orders', $params);

    if ($response['success'] && isset($response['data']['id_pesanan'])) {
        $orderId = $response['data']['id_pesanan'];
        $_SESSION['payment_method_' . $orderId] = $paymentMethod;
        // Clear session cart after successful order
        $_SESSION['session_cart'] = [];
        header("Location: pembayaran.php?order_id=" . $orderId);
        exit;
    } else {
        // Fallback: mock order creation if API is offline
        $mockOrderId = 'MOCK-' . date('Ymd') . '-' . rand(1000, 9999);

        // Build items snapshot from session_cart for payment page
        $cartSnapshot = [];
        if (!empty($_SESSION['session_cart'])) {
            foreach ($_SESSION['session_cart'] as $item) {
                $cartSnapshot[] = $item;
            }
        }

        $totalHarga = 0;
        foreach ($cartSnapshot as $item) {
            $totalHarga += ($item['harga'] ?? 0) * ($item['jumlah'] ?? 1);
        }

        // Save mock order to session
        $_SESSION['mock_orders_list'][$mockOrderId] = [
            'order_id'          => $mockOrderId,
            'id_pesanan'        => $mockOrderId,
            'address_id'        => $addressId,
            'metode_pembayaran' => $paymentMethod,
            'tanggal'           => date('Y-m-d H:i:s'),
            'status'            => 'Menunggu pembayaran',
            'total_harga'       => $totalHarga + 45000,
            'items'             => $cartSnapshot
        ];

        $_SESSION['payment_method_' . $mockOrderId] = $paymentMethod;

        // Clear session cart after mock order
        $_SESSION['session_cart'] = [];

        header("Location: pembayaran.php?order_id=" . $mockOrderId);
        exit;
    }
} else {
    header("Location: checkout.php");
    exit;
}
