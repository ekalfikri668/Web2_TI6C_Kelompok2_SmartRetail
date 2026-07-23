<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/api.php';

header('Content-Type: application/json');

// Auto-set demo session if not logged in so cart operations work seamlessly
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = 'mock-demo-token-12345';
    $_SESSION['user'] = [
        'nama_pembeli' => 'Budi Santoso',
        'email' => 'budi@gmail.com',
        'role' => 'pembeli',
        'no_hp' => '08123456789'
    ];
}

if (!isset($_SESSION['session_cart'])) {
    $_SESSION['session_cart'] = [];
}

$mockProductCatalog = [
    1 => ['nama_produk' => 'ROG Strix G16 Gaming Laptop', 'brand' => 'Asus', 'harga' => 24999000, 'foto' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=400&q=80'],
    2 => ['nama_produk' => 'Apple Watch Series 8 GPS', 'brand' => 'Apple', 'harga' => 6499000, 'foto' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=400&q=80'],
    3 => ['nama_produk' => 'Logitech G502 Hero High Performance', 'brand' => 'Logitech', 'harga' => 849000, 'foto' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=400&q=80'],
    4 => ['nama_produk' => 'Xiaomi Smart Camera C300 2K', 'brand' => 'Xiaomi', 'harga' => 599000, 'foto' => 'https://images.unsplash.com/photo-1557324218-8f35035b6c31?auto=format&fit=crop&w=400&q=80'],
    5 => ['nama_produk' => 'HP Pavilion 14 Ryzen 5', 'brand' => 'HP', 'harga' => 9799000, 'foto' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=400&q=80'],
    6 => ['nama_produk' => 'Razer DeathAdder Essential', 'brand' => 'Razer', 'harga' => 299000, 'foto' => 'https://images.unsplash.com/photo-1629429408209-1f912961dbd8?auto=format&fit=crop&w=400&q=80'],
    7 => ['nama_produk' => 'Acer Nitro V15 RTX 2050', 'brand' => 'Acer', 'harga' => 10999000, 'foto' => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?auto=format&fit=crop&w=400&q=80'],
    8 => ['nama_produk' => 'Ezviz H6c Smart Home Camera 1080P', 'brand' => 'Ezviz', 'harga' => 389000, 'foto' => 'https://images.unsplash.com/photo-1557324218-8f35035b6c31?auto=format&fit=crop&w=400&q=80']
];

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = (int)($_POST['product_id'] ?? 0);
        $jumlah = (int)($_POST['jumlah'] ?? 1);
        $warna = $_POST['warna'] ?? 'Standar';
        $tipe = $_POST['tipe'] ?? 'Standar';

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'ID Produk tidak valid.']);
            exit;
        }

        // Send API Request
        $res = apiRequest('POST', '/cart', [
            'product_id' => $productId,
            'jumlah' => $jumlah,
            'warna' => $warna,
            'tipe' => $tipe
        ]);

        // Always sync with local session cart as fallback
        $prodInfo = $mockProductCatalog[$productId] ?? [
            'nama_produk' => 'Produk Tech #' . $productId,
            'brand' => 'SmartRetail',
            'harga' => 1500000,
            'foto' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=400&q=80'
        ];

        $foundKey = null;
        foreach ($_SESSION['session_cart'] as $k => $item) {
            if ($item['product_id'] == $productId && ($item['warna'] ?? '') === $warna && ($item['tipe'] ?? '') === $tipe) {
                $foundKey = $k;
                break;
            }
        }

        if ($foundKey !== null) {
            $_SESSION['session_cart'][$foundKey]['jumlah'] += $jumlah;
        } else {
            $newCartId = time() + rand(10, 99);
            $_SESSION['session_cart'][$newCartId] = [
                'id' => $newCartId,
                'product_id' => $productId,
                'nama_produk' => $prodInfo['nama_produk'],
                'brand' => $prodInfo['brand'],
                'harga' => $prodInfo['harga'],
                'jumlah' => $jumlah,
                'warna' => $warna,
                'tipe' => $tipe,
                'foto' => $prodInfo['foto']
            ];
        }

        echo json_encode([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang!'
        ]);
        break;

    case 'update':
        $cartId = $_POST['cart_id'] ?? 0;
        $jumlah = (int)($_POST['jumlah'] ?? 1);

        if (!$cartId) {
            echo json_encode(['success' => false, 'message' => 'ID Item tidak valid.']);
            exit;
        }

        apiRequest('PUT', '/cart/' . $cartId, ['jumlah' => $jumlah]);

        // Update in session_cart (try both int and string key)
        if (isset($_SESSION['session_cart'][(int)$cartId])) {
            $_SESSION['session_cart'][(int)$cartId]['jumlah'] = $jumlah;
        } elseif (isset($_SESSION['session_cart'][$cartId])) {
            $_SESSION['session_cart'][$cartId]['jumlah'] = $jumlah;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Jumlah berhasil diperbarui.'
        ]);
        break;

    case 'delete':
        $cartId = $_POST['cart_id'] ?? 0;

        if (!$cartId) {
            echo json_encode(['success' => false, 'message' => 'ID Item tidak valid.']);
            exit;
        }

        apiRequest('DELETE', '/cart/' . $cartId);

        // Remove from session_cart (try both int and string key)
        if (isset($_SESSION['session_cart'][(int)$cartId])) {
            unset($_SESSION['session_cart'][(int)$cartId]);
        } elseif (isset($_SESSION['session_cart'][$cartId])) {
            unset($_SESSION['session_cart'][$cartId]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Item berhasil dihapus dari keranjang.'
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal.']);
        break;
}
