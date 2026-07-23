<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/cek_login.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/navbar.php';

// Prepare filters from GET params
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$brand = $_GET['brand'] ?? '';
$sort = $_GET['sort'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Build request query params
$queryParams = [];
if (!empty($search)) $queryParams['search'] = $search;
if (!empty($category)) $queryParams['category'] = $category;
if (!empty($brand)) $queryParams['brand'] = $brand;
if (!empty($sort)) $queryParams['sort'] = $sort;
if (!empty($min_price)) $queryParams['min_price'] = $min_price;
if (!empty($max_price)) $queryParams['max_price'] = $max_price;

// Call API
$productApi = apiRequest('GET', '/products', $queryParams);
$products = [];
if ($productApi['success'] && isset($productApi['data']) && is_array($productApi['data'])) {
    $products = $productApi['data'];
} else {
    // Dynamic mock filters if API is down
    $allMockProducts = [
        ['id' => 1, 'nama_produk' => 'ROG Strix G16 Gaming Laptop', 'brand' => 'Asus', 'harga' => 24999000, 'stok' => 5, 'rating' => 4.8, 'foto' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=400&q=80', 'kategori' => 'Laptop'],
        ['id' => 2, 'nama_produk' => 'Apple Watch Series 8 GPS', 'brand' => 'Apple', 'harga' => 6499000, 'stok' => 12, 'rating' => 4.7, 'foto' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=400&q=80', 'kategori' => 'Smartwatch'],
        ['id' => 3, 'nama_produk' => 'Logitech G502 Hero High Performance', 'brand' => 'Logitech', 'harga' => 849000, 'stok' => 25, 'rating' => 4.9, 'foto' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=400&q=80', 'kategori' => 'Mouse'],
        ['id' => 4, 'nama_produk' => 'Xiaomi Smart Camera C300 2K', 'brand' => 'Xiaomi', 'harga' => 599000, 'stok' => 8, 'rating' => 4.6, 'foto' => 'https://images.unsplash.com/photo-1557324218-8f35035b6c31?auto=format&fit=crop&w=400&q=80', 'kategori' => 'CCTV'],
        ['id' => 5, 'nama_produk' => 'HP Pavilion 14 Ryzen 5', 'brand' => 'HP', 'harga' => 9799000, 'stok' => 4, 'rating' => 4.5, 'foto' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=400&q=80', 'kategori' => 'Laptop'],
        ['id' => 6, 'nama_produk' => 'Razer DeathAdder Essential', 'brand' => 'Razer', 'harga' => 299000, 'stok' => 15, 'rating' => 4.7, 'foto' => 'https://images.unsplash.com/photo-1629429408209-1f912961dbd8?auto=format&fit=crop&w=400&q=80', 'kategori' => 'Mouse'],
        ['id' => 7, 'nama_produk' => 'Acer Nitro V15 RTX 2050', 'brand' => 'Acer', 'harga' => 10999000, 'stok' => 3, 'rating' => 4.4, 'foto' => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?auto=format&fit=crop&w=400&q=80', 'kategori' => 'Laptop'],
        ['id' => 8, 'nama_produk' => 'Ezviz H6c Smart Home Camera 1080P', 'brand' => 'Ezviz', 'harga' => 389000, 'stok' => 20, 'rating' => 4.5, 'foto' => 'https://images.unsplash.com/photo-1557324218-8f35035b6c31?auto=format&fit=crop&w=400&q=80', 'kategori' => 'CCTV']
    
            ];

    // Filter local mock array for high-fidelity experience
    $products = array_filter($allMockProducts, function($item) use ($search, $category, $brand, $min_price, $max_price) {
        if (!empty($search) && stripos($item['nama_produk'], $search) === false) return false;
        if (!empty($category) && strcasecmp($item['kategori'], $category) !== 0) return false;
        if (!empty($brand) && strcasecmp($item['brand'], $brand) !== 0) return false;
        if (!empty($min_price) && $item['harga'] < $min_price) return false;
        if (!empty($max_price) && $item['harga'] > $max_price) return false;
        return true;
    });

    if ($sort === 'cheap') {
        usort($products, fn($a, $b) => $a['harga'] <=> $b['harga']);
    } elseif ($sort === 'expensive') {
        usort($products, fn($a, $b) => $b['harga'] <=> $a['harga']);
    }
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
$categories = ['Laptop', 'Smartwatch', 'CCTV', 'Mouse', 'Smart TV'];
// Unique brands list
$brands = ['Asus', 'Acer', 'HP', 'Lenovo', 'Samsung', 'Apple', 'Huawei', 'Garmin', 'Logitech', 'Rexus', 'Razer', 'Xiaomi', 'Imou', 'Ezviz'];
?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 col-md-4 mb-4">
            <!-- Mobile Toggle Button -->
            <button class="btn btn-tech-primary w-100 d-md-none mb-3 rounded-4 py-2 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                <i class="fa-solid fa-sliders me-2"></i> Tampilkan Filter
            </button>

            <!-- Collapsible Filters -->
            <div class="collapse d-md-block" id="filterCollapse">
                <div class="card border-light shadow-sm p-4 rounded-4 bg-white">
                    <h5 class="mb-4 text-uppercase font-weight-bold d-none d-md-block" style="font-size: 1.1rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-sliders text-tech-blue me-2"></i>Filter Pencarian
                    </h5>
                    
                    <form action="produk.php" method="GET">
                        <!-- Text Search -->
                        <div class="mb-3">
                            <label class="form-label font-weight-bold text-secondary">Cari Produk</label>
                            <input type="text" class="form-control" name="search" placeholder="Masukkan nama..." value="<?= htmlspecialchars($search) ?>">
                        </div>

                        <!-- Category Filter -->
                        <div class="mb-3">
                            <label class="form-label font-weight-bold text-secondary">Kategori</label>
                            <select class="form-select" name="category">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Brand Filter -->
                        <div class="mb-3">
                            <label class="form-label font-weight-bold text-secondary">Brand</label>
                            <select class="form-select" name="brand">
                                <option value="">Semua Brand</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= htmlspecialchars($b) ?>" <?= $brand === $b ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($b) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Filter -->
                        <div class="mb-4">
                            <label class="form-label font-weight-bold text-secondary">Harga</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-light text-muted">Rp</span>
                                <input type="number" class="form-control" name="min_price" placeholder="Minimal" value="<?= htmlspecialchars($min_price) ?>">
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">Rp</span>
                                <input type="number" class="form-control" name="max_price" placeholder="Maksimal" value="<?= htmlspecialchars($max_price) ?>">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-tech-primary">Terapkan Filter</button>
                            <a href="produk.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9 col-md-8">
            <!-- Filter Bar & Sorting -->
            <div class="card border-light shadow-sm p-3 mb-4 rounded-4 bg-white">
                <div class="row align-items-center justify-content-between">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <p class="m-0 text-muted">Menampilkan <strong><?= count($products) ?></strong> produk untuk Anda</p>
                    </div>
                    <div class="col-md-5 col-lg-4 text-md-end">
                        <div class="d-flex align-items-center gap-2">
                            <label class="text-nowrap text-muted font-weight-bold small">Urutkan:</label>
                            <select class="form-select form-select-sm" id="sortSelect">
                                <option value="" <?= empty($sort) ? 'selected' : '' ?>>Default</option>
                                <option value="cheap" <?= $sort === 'cheap' ? 'selected' : '' ?>>Harga Termurah</option>
                                <option value="expensive" <?= $sort === 'expensive' ? 'selected' : '' ?>>Harga Tertinggi</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products List -->
            <?php if (empty($products)): ?>
                <div class="text-center py-5 bg-white border rounded-4 shadow-sm">
                    <i class="fa-solid fa-magnifying-glass-minus text-muted mb-3" style="font-size: 3.5rem;"></i>
                    <h5>Produk Tidak Ditemukan</h5>
                    <p class="text-muted">Coba ubah kata kunci pencarian atau bersihkan filter Anda.</p>
                    <a href="produk.php" class="btn btn-tech-primary btn-sm px-4">Lihat Semua Produk</a>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3 g-4">
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
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Specification Selection Modal -->
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
          <!-- Specification Options -->
          <div class="mb-4" id="modalSpecContainer">
            <label class="form-label fw-bold text-dark mb-2">Spesifikasi / Varian</label>
            <select class="form-select" id="modalSpecSelect" name="tipe"></select>
          </div>

          <!-- Color Picker -->
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

          <!-- Quantity -->
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dynamic Sorting redirect
    $('#sortSelect').on('change', function() {
        const sortVal = $(this).val();
        const urlParams = new URLSearchParams(window.location.search);
        if (sortVal) {
            urlParams.set('sort', sortVal);
        } else {
            urlParams.delete('sort');
        }
        window.location.search = urlParams.toString();
    });

    // Specifications options mapper
    const categorySpecs = {
        'Laptop': ['8GB RAM | 512GB SSD', '16GB RAM | 512GB SSD', '32GB RAM | 1TB SSD'],
        'Smartwatch': ['Sport Band (40mm)', 'Sport Band (44mm)', 'Leather Band (44mm)'],
        'CCTV': ['Standard (No SD Card)', 'Bundle 64GB MicroSD', 'Bundle 128GB MicroSD'],
        'Mouse': ['Standard Wired', 'Wireless HyperSpeed Edition'],
        'Smart TV': ['43-inch Full HD', '55-inch Ultra HD 4K', '65-inch Neo QLED 4K']
    };

    // Keep track of products in JS
    const productsList = <?= json_encode(array_values($products)) ?>;
    let selectedProduct = null;
    let targetAction = 'add'; // 'add' or 'buy'

    // Add Quantity handlers inside modal
    $('#modalQtyMinus').on('click', function() {
        let val = parseInt($('#modalQtyInput').val()) || 1;
        if (val > 1) $('#modalQtyInput').val(val - 1);
    });
    $('#modalQtyPlus').on('click', function() {
        let val = parseInt($('#modalQtyInput').val()) || 1;
        if (selectedProduct && val < selectedProduct.stok) {
            $('#modalQtyInput').val(val + 1);
        }
    });

    // Trigger modal for add/buy click
    $('.add-to-cart-btn, .buy-now-btn').on('click', function(e) {
        e.preventDefault();

        const productId = $(this).data('id');
        targetAction = $(this).hasClass('add-to-cart-btn') ? 'add' : 'buy';
        
        // Find product
        selectedProduct = productsList.find(p => (p.id == productId || p.id_produk == productId));
        if (!selectedProduct) return;

        // Populate Modal Details
        $('#modalProdName').text(selectedProduct.nama_produk);
        $('#modalProdPrice').text('Rp ' + Number(selectedProduct.harga).toLocaleString('id-ID'));
        $('#modalProdStock').text('Stok: ' + selectedProduct.stok + ' unit');
        $('#modalProdImg').attr('src', selectedProduct.foto || '');
        $('#modalQtyInput').val('1');

        // Populate spec select options
        const specs = categorySpecs[selectedProduct.kategori] || ['Standar'];
        const $select = $('#modalSpecSelect');
        $select.empty();
        specs.forEach(s => {
            $select.append(`<option value="${s}">${s}</option>`);
        });

        // Set default color
        $('#modalColor1').prop('checked', true);

        // Open Modal
        const modalEl = document.getElementById('specSelectModal');
        let modalInst = bootstrap.Modal.getInstance(modalEl);
        if (!modalInst) modalInst = new bootstrap.Modal(modalEl);
        modalInst.show();
    });

    // Confirm button inside modal
    $('#modalConfirmBtn').off('click').on('click', function() {
        if (!selectedProduct) return;

        const count = $('#modalQtyInput').val();
        const color = $('input[name="warna"]:checked').val() || 'Standar';
        const type = $('#modalSpecSelect').val() || 'Standar';

        $.ajax({
            url: 'helper_cart.php',
            method: 'POST',
            data: {
                action: 'add',
                product_id: selectedProduct.id,
                jumlah: count,
                warna: color,
                tipe: type
            },
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
