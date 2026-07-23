<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/cek_login.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/navbar.php';

// Fetch products from API
$productApi = apiRequest('GET', '/products');
$products = [];
if ($productApi['success'] && isset($productApi['data']) && is_array($productApi['data'])) {
    $products = array_slice($productApi['data'], 0, 8); // Display latest 8 products on home
} else {
    // Elegant fallback data when the REST API is offline
    $products = [
        [
            'id' => 1,
            'nama_produk' => 'ROG Strix G16 Gaming Laptop',
            'brand' => 'Asus',
            'harga' => 24999000,
            'stok' => 5,
            'rating' => 4.8,
            'foto' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=400&q=80',
            'kategori' => 'Laptop'
        ],
        [
            'id' => 2,
            'nama_produk' => 'Apple Watch Series 8 GPS',
            'brand' => 'Apple',
            'harga' => 6499000,
            'stok' => 12,
            'rating' => 4.7,
            'foto' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=400&q=80',
            'kategori' => 'Smartwatch'
        ],
        [
            'id' => 3,
            'nama_produk' => 'Logitech G502 Hero High Performance',
            'brand' => 'Logitech',
            'harga' => 849000,
            'stok' => 25,
            'rating' => 4.9,
            'foto' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=400&q=80',
            'kategori' => 'Mouse'
        ],
        [
            'id' => 4,
            'nama_produk' => 'Xiaomi Smart Camera C300 2K',
            'brand' => 'Xiaomi',
            'harga' => 599000,
            'stok' => 8,
            'rating' => 4.6,
            'foto' => 'https://images.unsplash.com/photo-1557324218-8f35035b6c31?auto=format&fit=crop&w=400&q=80',
            'kategori' => 'CCTV'
        ],
        [
            'id' => 5,
            'nama_produk' => 'HP Pavilion 14 Ryzen 5',
            'brand' => 'HP',
            'harga' => 9799000,
            'stok' => 4,
            'rating' => 4.5,
            'foto' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=400&q=80',
            'kategori' => 'Laptop'
        ],
        [
            'id' => 6,
            'nama_produk' => 'Razer DeathAdder Essential',
            'brand' => 'Razer',
            'harga' => 299000,
            'stok' => 15,
            'rating' => 4.7,
            'foto' => 'https://images.unsplash.com/photo-1629429408209-1f912961dbd8?auto=format&fit=crop&w=400&q=80',
            'kategori' => 'Mouse'
        ]
    ];
}

// Normalize product fields so both 'id' and 'id_produk' exist
$products = array_map(function($p) {
    $id = $p['id_produk'] ?? $p['id'] ?? 0;
    $p['id'] = $id;
    $p['id_produk'] = $id;
    $p['nama_produk'] = $p['nama_produk'] ?? $p['nama'] ?? 'Produk Tech';
    $p['harga'] = (float)($p['harga'] ?? 0);
    $p['stok'] = (int)($p['stok'] ?? 10);
    $p['rating'] = (float)($p['rating'] ?? 5.0);
    $p['brand'] = $p['nama_brand'] ?? $p['brand'] ?? 'Tech';
    $p['kategori'] = $p['nama_kategori'] ?? $p['kategori'] ?? 'Aksesoris';
    $p['foto'] = $p['gambar'] ?? $p['foto'] ?? 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=400&q=80';
    return $p;
}, $products);

// Categories list
$categories = [
    ['name' => 'Laptop', 'icon' => 'fa-laptop', 'id' => 1],
    ['name' => 'Smartwatch', 'icon' => 'fa-clock', 'id' => 2],
    ['name' => 'CCTV', 'icon' => 'fa-video', 'id' => 3],
    ['name' => 'Mouse', 'icon' => 'fa-mouse', 'id' => 4],
    ['name' => 'Smart TV', 'icon' => 'fa-tv', 'id' => 5],
];

// Brands list
$brands = [
    'Laptop' => ['Asus', 'Acer', 'HP', 'Lenovo', 'Samsung'],
    'Smartwatch' => ['Apple', 'Huawei', 'Garmin'],
    'Mouse' => ['Logitech', 'Rexus', 'Razer'],
    'CCTV' => ['Xiaomi', 'Imou', 'Ezviz']
];
?>

<div class="container my-4">
    <!-- Hero Slider -->
    <div id="heroCarousel" class="carousel slide shadow-sm mb-5" data-bs-ride="carousel" style="border-radius: 20px; overflow: hidden;">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="tech-hero d-flex align-items-center">
                    <div class="row align-items-center w-100 px-5">
                        <div class="col-md-7 tech-hero-content text-start">
                            <h1>Upgrade Gaming Terhebat<br><span>ROG Strix Edition</span></h1>
                            <p class="lead text-muted mt-3 mb-4">Rasakan kekuatan prosesor generasi terbaru dan grafis RTX termutakhir untuk performa tanpa batas.</p>
                            <a href="produk.php?brand=Asus" class="btn btn-tech-primary btn-lg">Beli Sekarang</a>
                        </div>
                        <div class="col-md-5 d-none d-md-block text-center position-relative">
                            <img src="https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=500&q=80" alt="Laptop Gaming" class="img-fluid rounded-4 shadow-lg border border-secondary" style="max-height: 320px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="tech-hero d-flex align-items-center" style="background: linear-gradient(135deg, #090d16 0%, #1e1b4b 100%);">
                    <div class="row align-items-center w-100 px-5">
                        <div class="col-md-7 tech-hero-content text-start">
                            <span class="badge bg-info mb-3 px-3 py-2 fs-6">NEW ARRIVAL</span>
                            <h1>Gaya & Produktivitas<br><span>Apple Watch Series 8</span></h1>
                            <p class="lead text-muted mt-3 mb-4">Pantau kesehatan harian Anda dengan sensor canggih dan asisten pintar di pergelangan tangan.</p>
                            <a href="produk.php?brand=Apple" class="btn btn-tech-primary btn-lg">Telusuri Gadget</a>
                        </div>
                        <div class="col-md-5 d-none d-md-block text-center">
                            <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=500&q=80" alt="Smartwatch" class="img-fluid rounded-4 shadow-lg border border-secondary" style="max-height: 320px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Categories Section -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0">Kategori Pilihan</h3>
            <a href="produk.php" class="text-primary text-decoration-none font-weight-bold">Lihat Semua <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row row-cols-2 row-cols-md-5 g-3">
            <?php foreach ($categories as $cat): ?>
                <div class="col">
                    <a href="produk.php?category=<?= htmlspecialchars($cat['name']) ?>" class="category-badge-item">
                        <i class="fa-solid <?= $cat['icon'] ?>"></i>
                        <span><?= htmlspecialchars($cat['name']) ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Brands Grid Section -->
    <div class="mb-5 py-4 bg-white rounded-4 border border-light shadow-sm">
        <div class="container px-4">
            <h4 class="mb-4">Brand Terpopuler</h4>
            <div class="row align-items-center g-3 text-center">
                <?php 
                $uniqueBrands = ['Asus', 'Acer', 'HP', 'Lenovo', 'Samsung', 'Apple', 'Huawei', 'Garmin', 'Logitech', 'Rexus', 'Razer', 'Xiaomi'];
                foreach (array_slice($uniqueBrands, 0, 8) as $brand):
                ?>
                    <div class="col-6 col-md-3 col-lg-3">
                        <a href="produk.php?brand=<?= $brand ?>" class="btn btn-light w-100 py-3 font-weight-bold text-dark border-light shadow-sm" style="transition: all 0.2s ease;">
                            <i class="fa-solid fa-tags text-tech-blue me-2"></i><?= $brand ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Dynamic Products Grid -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="m-0">Produk Unggulan</h3>
                <p class="text-muted mb-0">Temukan barang teknologi kualitas premium dengan penawaran terbaik</p>
            </div>
            <a href="produk.php" class="text-primary text-decoration-none font-weight-bold">Lihat Produk Lainnya <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
        
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($products as $prod): ?>
                <div class="col d-flex align-items-stretch">
                    <div class="tech-card w-100">
                        <div class="img-wrapper">
                            <span class="badge-tech"><?= htmlspecialchars($prod['brand']) ?></span>
                            <img src="<?= htmlspecialchars($prod['foto']) ?>" alt="<?= htmlspecialchars($prod['nama_produk']) ?>" loading="lazy">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted fw-semibold" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.4px"><?= htmlspecialchars($prod['kategori'] ?? 'Aksesoris') ?></small>
                                <div class="text-warning" style="font-size:0.78rem;">
                                    <i class="fa-solid fa-star"></i> <span><?= number_format($prod['rating'], 1) ?></span>
                                </div>
                            </div>
                            <h6 class="card-title mb-2">
                                <a href="detail_produk.php?id=<?= $prod['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($prod['nama_produk']) ?></a>
                            </h6>
                            <p class="fw-bold mb-3" style="color:var(--tech-blue);font-size:1rem;">Rp <?= number_format($prod['harga'], 0, ',', '.') ?></p>
                            <div class="mt-auto d-grid gap-2">
                                <a href="detail_produk.php?id=<?= $prod['id'] ?>" class="btn btn-outline-secondary btn-sm rounded-3">
                                    <i class="fa-solid fa-eye me-1"></i> Detail
                                </a>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-outline-primary btn-sm flex-fill add-to-cart-btn" data-id="<?= $prod['id'] ?>">
                                        <i class="fa-solid fa-cart-plus me-1"></i> Keranjang
                                    </button>
                                    <button class="btn btn-sm flex-fill btn-tech-primary buy-now-btn" data-id="<?= $prod['id'] ?>">
                                        Beli
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Specification Selection Modal (same as produk.php) -->
<div class="modal fade" id="specSelectModal" tabindex="-1" aria-labelledby="specSelectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow-lg">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="specSelectModalLabel">Pilih Spesifikasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex gap-3 mb-4">
          <img src="" id="modalProdImg" class="img-thumbnail rounded-3" style="width: 90px; height: 90px; object-fit: cover; object-position: center;">
          <div>
            <h6 class="fw-bold mb-1" id="modalProdName">-</h6>
            <h5 class="text-primary fw-bold mb-1" id="modalProdPrice">-</h5>
            <small class="text-muted" id="modalProdStock">-</small>
          </div>
        </div>

        <form id="specSelectForm">
          <div class="mb-4" id="modalSpecContainer">
            <label class="form-label fw-bold text-dark mb-2">Spesifikasi / Varian</label>
            <select class="form-select" id="modalSpecSelect" name="tipe"></select>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold text-dark mb-2">Warna</label>
            <div class="d-flex gap-2" id="modalColorPicker">
              <input type="radio" class="btn-check" name="warna" id="modalColor1" value="Black" checked>
              <label class="btn btn-outline-dark px-3 py-1 btn-sm" for="modalColor1">Black</label>
              <input type="radio" class="btn-check" name="warna" id="modalColor2" value="Blue">
              <label class="btn btn-outline-dark px-3 py-1 btn-sm" for="modalColor2">Blue</label>
              <input type="radio" class="btn-check" name="warna" id="modalColor3" value="White">
              <label class="btn btn-outline-dark px-3 py-1 btn-sm" for="modalColor3">White</label>
              <input type="radio" class="btn-check" name="warna" id="modalColor4" value="Pink">
              <label class="btn btn-outline-dark px-3 py-1 btn-sm" for="modalColor4">Pink</label>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold text-dark mb-2">Jumlah</label>
            <div class="input-group" style="width: 130px;">
              <button class="btn btn-outline-secondary btn-sm" type="button" id="modalQtyMinus">-</button>
              <input type="text" class="form-control text-center bg-white form-control-sm" id="modalQtyInput" value="1" readonly>
              <button class="btn btn-outline-secondary btn-sm" type="button" id="modalQtyPlus">+</button>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary rounded-3 btn-tech-primary px-4" id="modalConfirmBtn">Konfirmasi</button>
      </div>
    </div>
  </div>
</div>

<!-- Cart interaction with spec select modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySpecs = {
        'Laptop':     ['8GB RAM | 512GB SSD', '16GB RAM | 512GB SSD', '32GB RAM | 1TB SSD'],
        'Smartwatch': ['Sport Band (40mm)', 'Sport Band (44mm)', 'Leather Band (44mm)'],
        'CCTV':       ['Standard (No SD Card)', 'Bundle 64GB MicroSD', 'Bundle 128GB MicroSD'],
        'Mouse':      ['Standard Wired', 'Wireless HyperSpeed Edition'],
        'Smart TV':   ['43-inch Full HD', '55-inch Ultra HD 4K', '65-inch Neo QLED 4K']
    };

    const productsList = <?= json_encode(array_values($products)) ?>;
    let selectedProduct = null;
    let targetAction = 'add';

    // Quantity handlers
    $('#modalQtyMinus').on('click', function() {
        let val = parseInt($('#modalQtyInput').val()) || 1;
        if (val > 1) $('#modalQtyInput').val(val - 1);
    });
    $('#modalQtyPlus').on('click', function() {
        let val = parseInt($('#modalQtyInput').val()) || 1;
        if (selectedProduct && val < selectedProduct.stok) $('#modalQtyInput').val(val + 1);
    });

    // Open modal when add/buy clicked
    $('.add-to-cart-btn, .buy-now-btn').on('click', function(e) {
        e.preventDefault();

        const productId = $(this).data('id');
        targetAction = $(this).hasClass('add-to-cart-btn') ? 'add' : 'buy';
        selectedProduct = productsList.find(p => (p.id == productId || p.id_produk == productId));
        if (!selectedProduct) return;

        $('#modalProdName').text(selectedProduct.nama_produk);
        $('#modalProdPrice').text('Rp ' + Number(selectedProduct.harga).toLocaleString('id-ID'));
        $('#modalProdStock').text('Stok: ' + selectedProduct.stok + ' unit');
        $('#modalProdImg').attr('src', selectedProduct.foto || '');
        $('#modalQtyInput').val('1');
        $('#modalColor1').prop('checked', true);

        const specs = categorySpecs[selectedProduct.kategori] || ['Standar'];
        const $select = $('#modalSpecSelect');
        $select.empty();
        specs.forEach(s => $select.append(`<option value="${s}">${s}</option>`));

        const modalEl = document.getElementById('specSelectModal');
        let modalInst = bootstrap.Modal.getInstance(modalEl);
        if (!modalInst) modalInst = new bootstrap.Modal(modalEl);
        modalInst.show();
    });

    // Confirm selection and add to cart
    $('#modalConfirmBtn').off('click').on('click', function() {
        if (!selectedProduct) return;
        const count = $('#modalQtyInput').val();
        const color = $('input[name="warna"]:checked').val() || 'Standar';
        const type  = $('#modalSpecSelect').val() || 'Standar';

        $.ajax({
            url: 'helper_cart.php',
            method: 'POST',
            data: { action: 'add', product_id: selectedProduct.id, jumlah: count, warna: color, tipe: type },
            dataType: 'json',
            success: function(res) {
                const modalEl = document.getElementById('specSelectModal');
                const modalInst = bootstrap.Modal.getInstance(modalEl);
                if (modalInst) modalInst.hide();

                if (targetAction === 'buy') {
                    window.location.href = 'keranjang.php';
                } else {
                    showAlert('Berhasil', 'Produk berhasil ditambahkan ke keranjang!', 'success', function() {
                        location.reload();
                    });
                }
            },
            error: function() {
                const modalEl = document.getElementById('specSelectModal');
                const modalInst = bootstrap.Modal.getInstance(modalEl);
                if (modalInst) modalInst.hide();

                if (targetAction === 'buy') {
                    window.location.href = 'keranjang.php';
                } else {
                    showAlert('Berhasil', 'Produk berhasil ditambahkan ke keranjang!', 'success', function() {
                        location.reload();
                    });
                }
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
