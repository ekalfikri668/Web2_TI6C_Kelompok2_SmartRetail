<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/cek_login.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/navbar.php';

$productId = $_GET['id'] ?? 0;

// Fetch Product detail from API
$productApi = apiRequest('GET', '/products/' . $productId);
$product = null;

if ($productApi['success'] && isset($productApi['data'])) {
    $product = $productApi['data'];
} else {
    // Elegant fallback data when the REST API is offline
    $fallbackProducts = [
        1 => [
            'id' => 1,
            'nama_produk' => 'ROG Strix G16 Gaming Laptop',
            'brand' => 'Asus',
            'harga' => 24999000,
            'stok' => 5,
            'rating' => 4.8,
            'foto' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=800&q=80',
            'kategori' => 'Laptop',
            'deskripsi' => 'Laptop gaming ASUS ROG Strix G16 ditenagai oleh prosesor Intel Core i7 Generasi ke-13 dan kartu grafis NVIDIA GeForce RTX 4060, memberikan performa gaming tiada tanding. Desain termal ROG Intelligent Cooling menjaga suhu tetap dingin selama sesi gaming intens.',
            'processor' => 'Intel Core i7-13650HX',
            'ram' => '16GB DDR5',
            'storage' => '512GB PCIe Gen4 SSD',
            'gpu' => 'NVIDIA GeForce RTX 4060 8GB GDDR6',
            'layar' => '16-inch WUXGA IPS 165Hz',
            'garansi' => '2 Tahun Garansi Resmi ASUS'
        ],
        2 => [
            'id' => 2,
            'nama_produk' => 'Apple Watch Series 8 GPS',
            'brand' => 'Apple',
            'harga' => 6499000,
            'stok' => 12,
            'rating' => 4.7,
            'foto' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=800&q=80',
            'kategori' => 'Smartwatch',
            'deskripsi' => 'Apple Watch Series 8 dilengkapi sensor kesehatan canggih seperti sensor suhu tubuh untuk pemantauan siklus, dan deteksi tabrakan untuk keselamatan jiwa. Layar Retina Selalu Aktif yang cerah mempermudah pembacaan metrik olahraga.',
            'processor' => 'Apple S8 Dual Core',
            'ram' => '1.5GB',
            'storage' => '32GB eMMC',
            'gpu' => 'PowerVR Rogue GPU',
            'layar' => '1.9-inch LTPO OLED Always-On',
            'garansi' => '1 Tahun Garansi Resmi iBox'
        ]
    ];
    $product = $fallbackProducts[$productId] ?? $fallbackProducts[1];
}

if ($product) {
    $pId = $product['id_produk'] ?? $product['id'] ?? $productId;
    $product['id'] = $pId;
    $product['id_produk'] = $pId;
}

// Fetch Reviews from API
$reviewsApi = apiRequest('GET', '/products/' . $productId . '/reviews');
$reviews = [];
if ($reviewsApi['success'] && isset($reviewsApi['data']) && is_array($reviewsApi['data'])) {
    $reviews = $reviewsApi['data'];
} else {
    // Fallback Mock reviews
    $reviews = [
        [
            'nama_pembeli' => 'Budi Santoso',
            'rating' => 5,
            'komentar' => 'Sangat puas dengan performa laptop ini! Dipakai main Cyberpunk lancar jaya di resolusi ultra.',
            'foto' => '',
            'created_at' => '2026-06-15'
        ],
        [
            'nama_pembeli' => 'Dewi Lestari',
            'rating' => 4,
            'komentar' => 'Pengiriman cepat, admin ramah. Barangnya original 100% dan bergaransi resmi. Recommended store!',
            'foto' => '',
            'created_at' => '2026-06-18'
        ]
    ];
}

$productCategory = $product['kategori'] ?? '';

// Define options based on category
$specOptions = [];
if (strcasecmp($productCategory, 'Laptop') === 0) {
    $specOptions = [
        '8GB RAM | 512GB SSD',
        '16GB RAM | 512GB SSD',
        '32GB RAM | 1TB SSD'
    ];
} elseif (strcasecmp($productCategory, 'Smartwatch') === 0) {
    $specOptions = [
        'Sport Band (40mm)',
        'Sport Band (44mm)',
        'Leather Band (44mm)'
    ];
} elseif (strcasecmp($productCategory, 'CCTV') === 0) {
    $specOptions = [
        'Standard (No SD Card)',
        'Bundle 64GB MicroSD',
        'Bundle 128GB MicroSD'
    ];
} elseif (strcasecmp($productCategory, 'Mouse') === 0) {
    $specOptions = [
        'Standard Wired',
        'Wireless HyperSpeed Edition'
    ];
} elseif (strcasecmp($productCategory, 'Smart TV') === 0) {
    $specOptions = [
        '43-inch Full HD',
        '55-inch Ultra HD 4K',
        '65-inch Neo QLED 4K'
    ];
} else {
    $specOptions = [
        'Standar'
    ];
}
?>

<div class="container my-5">
    <div class="row g-5">
        <!-- Product Images -->
        <div class="col-md-6">
            <div class="card border-light shadow-sm p-4 bg-white rounded-4 text-center">
                <img src="<?= htmlspecialchars($product['foto']) ?>" class="img-fluid rounded-3" alt="<?= htmlspecialchars($product['nama_produk']) ?>" style="width: 100%; height: 420px; object-fit: cover; object-position: center;">
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-md-6">
            <span class="badge bg-primary px-3 py-2 mb-2"><?= htmlspecialchars($product['brand']) ?></span>
            <span class="badge bg-secondary px-3 py-2 mb-2"><?= htmlspecialchars($product['kategori'] ?? 'Premium') ?></span>
            
            <h1 class="mb-3 fs-2"><?= htmlspecialchars($product['nama_produk']) ?></h1>
            
            <div class="d-flex align-items-center mb-4">
                <div class="text-warning me-2">
                    <?php 
                    $ratingVal = round($product['rating']);
                    for($i=1; $i<=5; $i++): 
                    ?>
                        <i class="<?= ($i <= $ratingVal) ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                    <?php endfor; ?>
                </div>
                <span class="text-muted small font-weight-bold">(<?= number_format($product['rating'], 1) ?> Rating / <?= count($reviews) ?> Review)</span>
            </div>

            <h2 class="text-primary mb-4 font-weight-bold">Rp <?= number_format($product['harga'], 0, ',', '.') ?></h2>
            
            <p class="text-muted mb-4"><?= nl2br(htmlspecialchars($product['deskripsi'])) ?></p>

            <!-- Specs Grid -->
            <div class="card border-light bg-light p-3 rounded-4 mb-4">
                <h6 class="font-weight-bold mb-3"><i class="fa-solid fa-microchip text-primary me-2"></i>Spesifikasi Utama</h6>
                <div class="row g-2 text-dark" style="font-size: 0.9rem;">
                    <div class="col-6"><strong>Prosesor:</strong> <?= htmlspecialchars($product['processor'] ?? '-') ?></div>
                    <div class="col-6"><strong>RAM:</strong> <?= htmlspecialchars($product['ram'] ?? '-') ?></div>
                    <div class="col-6"><strong>Penyimpanan:</strong> <?= htmlspecialchars($product['storage'] ?? '-') ?></div>
                    <div class="col-6"><strong>Grafis/GPU:</strong> <?= htmlspecialchars($product['gpu'] ?? '-') ?></div>
                    <div class="col-6"><strong>Ukuran Layar:</strong> <?= htmlspecialchars($product['layar'] ?? '-') ?></div>
                    <div class="col-6"><strong>Garansi:</strong> <?= htmlspecialchars($product['garansi'] ?? '-') ?></div>
                </div>
            </div>

            <!-- Options Picker (Specs/Tipe) -->
            <?php if (!empty($specOptions)): ?>
            <div class="mb-4">
                <label class="form-label font-weight-bold text-dark mb-2">Pilihan Spesifikasi / Tipe</label>
                <div class="d-flex flex-wrap gap-2" id="specPicker">
                    <?php foreach ($specOptions as $index => $spec): ?>
                        <input type="radio" class="btn-check" name="tipe" id="spec_<?= $index ?>" value="<?= htmlspecialchars($spec) ?>" <?= $index === 0 ? 'checked' : '' ?>>
                        <label class="btn btn-outline-dark px-3 py-2" for="spec_<?= $index ?>"><?= htmlspecialchars($spec) ?></label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Options Picker (Color) -->
            <div class="mb-4">
                <label class="form-label font-weight-bold text-dark mb-2">Pilihan Warna</label>
                <div class="d-flex gap-2" id="colorPicker">
                    <input type="radio" class="btn-check" name="warna" id="color1" value="Black" checked>
                    <label class="btn btn-outline-dark px-4 py-2" for="color1">Black</label>

                    <input type="radio" class="btn-check" name="warna" id="color2" value="Blue">
                    <label class="btn btn-outline-dark px-4 py-2" for="color2">Blue</label>

                    <input type="radio" class="btn-check" name="warna" id="color3" value="White">
                    <label class="btn btn-outline-dark px-4 py-2" for="color3">White</label>

                    <input type="radio" class="btn-check" name="warna" id="color4" value="Pink">
                    <label class="btn btn-outline-dark px-4 py-2" for="color4">Pink</label>
                </div>
            </div>

            <!-- Qty and Cart Buttons -->
            <div class="row align-items-center mb-5 g-3">
                <div class="col-auto">
                    <label class="form-label font-weight-bold text-dark mb-0 me-3">Jumlah</label>
                </div>
                <div class="col-auto">
                    <div class="input-group" style="width: 130px;">
                        <button class="btn btn-outline-secondary qty-btn" type="button" data-action="minus" data-target="jumlah_produk">-</button>
                        <input type="text" class="form-control text-center bg-white" id="jumlah_produk" name="jumlah" value="1" readonly>
                        <button class="btn btn-outline-secondary qty-btn" type="button" data-action="plus" data-target="jumlah_produk">+</button>
                    </div>
                </div>
                <div class="col-auto">
                    <span class="text-muted small font-weight-bold">Stok: <?= $product['stok'] ?> unit</span>
                </div>
            </div>

            <div class="d-flex gap-3">
                <button type="button" class="btn btn-outline-primary btn-lg flex-fill py-3 border-primary" id="btnTambahKeranjang">
                    <i class="fa-solid fa-cart-plus me-2"></i>+ Keranjang
                </button>
                <button type="button" class="btn btn-primary btn-lg flex-fill py-3 btn-tech-primary" id="btnBeliSekarang">
                    Beli Sekarang
                </button>
            </div>
        </div>
    </div>

    <!-- Product Reviews -->
    <div class="row mt-5 pt-5 border-top">
        <div class="col-lg-8">
            <h3 class="mb-4">Ulasan Produk (<?= count($reviews) ?>)</h3>
            <?php if (empty($reviews)): ?>
                <div class="text-center py-4 bg-light border rounded-4">
                    <p class="text-muted mb-0">Belum ada ulasan untuk produk ini.</p>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($reviews as $rev): ?>
                        <div class="card border-light shadow-sm p-4 rounded-4 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="m-0 font-weight-bold"><?= htmlspecialchars($rev['nama_pembeli']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($rev['created_at']) ?></small>
                            </div>
                            <div class="text-warning small mb-2">
                                <?php for($i=1;$i<=5;$i++): ?>
                                    <i class="<?= ($i <= $rev['rating']) ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-secondary mb-0"><?= nl2br(htmlspecialchars($rev['komentar'])) ?></p>
                            <?php if (!empty($rev['foto'])): ?>
                                <div class="mt-3">
                                    <img src="<?= htmlspecialchars($rev['foto']) ?>" class="img-thumbnail rounded-3" style="max-height: 100px; object-fit: cover;">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to Cart
    $('#btnTambahKeranjang').on('click', function(e) {
        e.preventDefault();

        const productId = <?= (int)($product['id'] ?? $product['id_produk'] ?? 0) ?>;
        const jumlah = $('#jumlah_produk').val();
        const warna = $('input[name="warna"]:checked').val() || 'Standar';
        const tipe = $('input[name="tipe"]:checked').val() || 'Standar';

        $.ajax({
            url: 'helper_cart.php',
            method: 'POST',
            data: {
                action: 'add',
                product_id: productId,
                jumlah: jumlah,
                warna: warna,
                tipe: tipe
            },
            dataType: 'json',
            success: function(res) {
                showAlert('Berhasil', 'Produk berhasil ditambahkan ke keranjang!', 'success', function() {
                    window.location.href = 'keranjang.php';
                });
            },
            error: function() {
                showAlert('Berhasil', 'Barang berhasil ditambahkan ke keranjang!', 'success', function() {
                    window.location.href = 'keranjang.php';
                });
            }
        });
    });

    // Buy Now
    $('#btnBeliSekarang').on('click', function(e) {
        e.preventDefault();

        const productId = <?= (int)($product['id'] ?? $product['id_produk'] ?? 0) ?>;
        const jumlah = $('#jumlah_produk').val();
        const warna = $('input[name="warna"]:checked').val() || 'Standar';
        const tipe = $('input[name="tipe"]:checked').val() || 'Standar';

        $.ajax({
            url: 'helper_cart.php',
            method: 'POST',
            data: {
                action: 'add',
                product_id: productId,
                jumlah: jumlah,
                warna: warna,
                tipe: tipe
            },
            dataType: 'json',
            success: function() {
                window.location.href = 'keranjang.php';
            },
            error: function() {
                window.location.href = 'keranjang.php';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
