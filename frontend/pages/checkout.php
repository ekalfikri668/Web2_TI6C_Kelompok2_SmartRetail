<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/cek_login.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/navbar.php';

// Fetch Cart Data
$cartApi = apiRequest('GET', '/cart');
$cartItems = [];
if ($cartApi['success'] && isset($cartApi['data']['items']) && is_array($cartApi['data']['items']) && !empty($cartApi['data']['items'])) {
    $cartItems = array_map(function($item) {
        return [
            'id' => $item['id_detail'] ?? $item['id'] ?? null,
            'product_id' => $item['id_produk'] ?? $item['product_id'] ?? null,
            'nama_produk' => $item['nama_produk'] ?? '',
            'brand' => $item['nama_brand'] ?? $item['brand'] ?? 'Laptop',
            'harga' => $item['harga'] ?? 0,
            'jumlah' => $item['jumlah'] ?? 0,
            'warna' => $item['warna'] ?? 'Standar',
            'tipe' => $item['tipe'] ?? 'Standar',
            'foto' => $item['gambar'] ?? $item['foto'] ?? 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=200&q=80'
        ];
    }, $cartApi['data']['items']);
} elseif (isset($_SESSION['session_cart']) && is_array($_SESSION['session_cart']) && !empty($_SESSION['session_cart'])) {
    $cartItems = array_values($_SESSION['session_cart']);
}
// else $cartItems stays empty — will redirect to keranjang.php below

$cartItems = array_map(function($item) {
    $id = $item['id'] ?? $item['id_detail'] ?? $item['product_id'] ?? rand(100, 999);
    $item['id'] = $id;
    return $item;
}, $cartItems);

// Filter cart items by checked IDs if present
$selectedIdsStr = $_GET['ids'] ?? '';
if (!empty($selectedIdsStr)) {
    $selectedIds = explode(',', $selectedIdsStr);
    $cartItems = array_values(array_filter($cartItems, function($item) use ($selectedIds) {
        // Use loose comparison — session_cart int IDs vs string IDs from URL
        return in_array((string)($item['id'] ?? ''), $selectedIds, false)
            || in_array($item['id'], $selectedIds, false);
    }));
}
// If filtering resulted in empty but we have items, show all
if (empty($cartItems) && !empty($selectedIdsStr)) {
    // Re-fetch all from session_cart as fallback
    if (!empty($_SESSION['session_cart'])) {
        $cartItems = array_values($_SESSION['session_cart']);
    }
}

// Redirect if cart is empty
if (empty($cartItems)) {
    header("Location: keranjang.php");
    exit;
}

// Fetch Addresses
$addressApi = apiRequest('GET', '/address');
$addresses = [];
if ($addressApi['success'] && isset($addressApi['data']) && is_array($addressApi['data'])) {
    $addresses = $addressApi['data'];
} else {
    // Mock addresses fallback
    $addresses = [
        [
            'id' => 1,
            'penerima' => 'Budi Santoso',
            'no_hp' => '08123456789',
            'alamat_lengkap' => 'Jl. Merdeka No. 45, RT 02 / RW 05, Gambir',
            'kota' => 'Jakarta Pusat',
            'kode_pos' => '10110',
            'is_utama' => 1
        ]
    ];
}

// Handle Add Address Submission
$addressError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_address') {
    $penerima = trim($_POST['penerima'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat_lengkap'] ?? '');
    $kota = trim($_POST['kota'] ?? '');
    $kode_pos = trim($_POST['kode_pos'] ?? '');

    if (empty($penerima) || empty($no_hp) || empty($alamat) || empty($kota) || empty($kode_pos)) {
        $addressError = "Semua kolom alamat wajib diisi.";
    } else {
        $response = apiRequest('POST', '/address', [
            'nama_penerima' => $penerima,
            'no_hp' => $no_hp,
            'alamat_lengkap' => $alamat,
            'kota' => $kota,
            'kode_pos' => $kode_pos
        ]);

        if ($response['success']) {
            header("Location: checkout.php?msg=address_added");
            exit;
        } else {
            // Mock addition if api is down
            $addresses[] = [
                'id' => count($addresses) + 1,
                'penerima' => $penerima,
                'no_hp' => $no_hp,
                'alamat_lengkap' => $alamat,
                'kota' => $kota,
                'kode_pos' => $kode_pos,
                'is_utama' => 0
            ];
            $_SESSION['mock_addresses'] = $addresses;
            header("Location: checkout.php?msg=address_added");
            exit;
        }
    }
}

// Merge mock addresses if exists
if (isset($_SESSION['mock_addresses'])) {
    $addresses = $_SESSION['mock_addresses'];
}

$totalBarang = 0;
foreach($cartItems as $item) {
    $totalBarang += $item['harga'] * $item['jumlah'];
}
$ongkosKirim = 45000; // Flat tech shipping rate
$totalBayar = $totalBarang + $ongkosKirim;
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fa-solid fa-credit-card text-tech-blue me-2"></i>Checkout Pesanan</h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'address_added'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>Alamat pengiriman baru berhasil ditambahkan!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form id="checkoutForm" action="proses_checkout.php" method="POST">
        <input type="hidden" name="cart_item_ids" value="<?= htmlspecialchars($selectedIdsStr) ?>">
        <div class="row g-4">
            <!-- Left Panel -->
            <div class="col-lg-8">
                <!-- Address Section -->
                <div class="card border-light shadow-sm p-4 rounded-4 bg-white mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="m-0 font-weight-bold"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Alamat Pengiriman</h5>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                            <i class="fa-solid fa-plus me-1"></i> Tambah Alamat
                        </button>
                    </div>

                    <?php if (empty($addresses)): ?>
                        <div class="alert alert-warning">
                            Belum ada alamat pengiriman. Silakan tambah alamat baru terlebih dahulu.
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($addresses as $index => $addr): 
                                $isUtama = $addr['is_utama'] ?? 0;
                                $penerimaName = $addr['nama_penerima'] ?? $addr['penerima'] ?? '';
                                $addrId = $addr['id_alamat'] ?? $addr['id'] ?? $index;
                            ?>
                                <div class="border rounded-4 p-3 position-relative <?= $isUtama ? 'border-primary bg-light-subtle' : '' ?>">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="address_id" id="addr-<?= $addrId ?>" value="<?= $addrId ?>" <?= $isUtama ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100" for="addr-<?= $addrId ?>">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <strong><?= htmlspecialchars($penerimaName) ?></strong>
                                                <span class="text-muted">(<?= htmlspecialchars($addr['no_hp']) ?>)</span>
                                                <?php if($isUtama): ?>
                                                    <span class="badge bg-primary fs-7">Utama</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-secondary mb-0" style="font-size: 0.9rem;">
                                                <?= htmlspecialchars($addr['alamat_lengkap']) ?>, <?= htmlspecialchars($addr['kota']) ?> - <?= htmlspecialchars($addr['kode_pos']) ?>
                                            </p>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Products Summary Review -->
                <div class="card border-light shadow-sm p-4 rounded-4 bg-white mb-4">
                    <h5 class="mb-3 font-weight-bold"><i class="fa-solid fa-box text-primary me-2"></i>Detail Barang</h5>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach($cartItems as $item): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= htmlspecialchars($item['foto']) ?>" class="img-fluid rounded-3 border" style="width: 55px; height: 55px; object-fit: cover; object-position: center;">
                                    <div>
                                        <h6 class="mb-0 font-weight-bold" style="font-size: 0.95rem;"><?= htmlspecialchars($item['nama_produk']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($item['jumlah']) ?> x Rp <?= number_format($item['harga'], 0, ',', '.') ?> | Warna: <?= htmlspecialchars($item['warna'] ?? 'Standar') ?> | Tipe: <?= htmlspecialchars($item['tipe'] ?? 'Standar') ?></small>
                                    </div>
                                </div>
                                <span class="font-weight-bold">Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card border-light shadow-sm p-4 rounded-4 bg-white">
                    <h5 class="mb-3 font-weight-bold"><i class="fa-solid fa-wallet text-primary me-2"></i>Metode Pembayaran</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input me-3" type="radio" name="metode_pembayaran" id="payBank" value="Transfer Bank" checked>
                                    <label class="form-check-label w-100" for="payBank">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Transfer Bank</strong>
                                                <div class="text-muted small">BCA, Mandiri, BNI, BRI</div>
                                            </div>
                                            <i class="fa-solid fa-money-bill-transfer fs-4 text-secondary"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input me-3" type="radio" name="metode_pembayaran" id="payQris" value="QRIS">
                                    <label class="form-check-label w-100" for="payQris">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>QRIS (Bayar Instan)</strong>
                                                <div class="text-muted small">Gopay, OVO, ShopeePay, Dana</div>
                                            </div>
                                            <i class="fa-solid fa-qrcode fs-4 text-secondary"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel (Bill Calculation) -->
            <div class="col-lg-4">
                <div class="card border-light shadow-sm p-4 rounded-4 bg-white sticky-top" style="top: 90px; z-index: 1;">
                    <h5 class="mb-4 font-weight-bold">Rincian Pembayaran</h5>
                    <div class="d-flex justify-content-between mb-2 text-secondary" style="font-size: 0.95rem;">
                        <span>Total Harga Barang:</span>
                        <span>Rp <?= number_format($totalBarang, 0, ',', '.') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 text-secondary" style="font-size: 0.95rem;">
                        <span>Ongkos Kirim (Flat):</span>
                        <span>Rp <?= number_format($ongkosKirim, 0, ',', '.') ?></span>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="font-weight-bold text-dark fs-5">Total Pembayaran:</span>
                        <span class="font-weight-bold text-primary fs-4">Rp <?= number_format($totalBayar, 0, ',', '.') ?></span>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3 btn-tech-primary">
                        <i class="fa-solid fa-bag-shopping me-2"></i>Buat Pesanan Sekarang
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Add Address -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="addAddressModalLabel">Tambah Alamat Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="checkout.php" method="POST">
                <input type="hidden" name="action" value="add_address">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="penerima" class="form-label font-weight-bold text-dark">Nama Penerima</label>
                        <input type="text" class="form-control" id="penerima" name="penerima" placeholder="Nama Penerima" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_hp" class="form-label font-weight-bold text-dark">Nomor HP Penerima</label>
                        <input type="tel" class="form-control" id="no_hp" name="no_hp" placeholder="Nomor HP" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat_lengkap" class="form-label font-weight-bold text-dark">Alamat Lengkap</label>
                        <textarea class="form-control" id="alamat_lengkap" name="alamat_lengkap" rows="3" placeholder="Nama Jalan, Gedung, No. Rumah, RT/RW, Dusun" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kota" class="form-label font-weight-bold text-dark">Kota / Kabupaten</label>
                            <input type="text" class="form-control" id="kota" name="kota" placeholder="Kota" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="kode_pos" class="form-label font-weight-bold text-dark">Kode Pos</label>
                            <input type="text" class="form-control" id="kode_pos" name="kode_pos" placeholder="Kode Pos" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-tech-primary">Simpan Alamat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
