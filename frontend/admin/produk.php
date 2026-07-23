<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$successMsg = null;
$errorMsg = null;

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Extracted fields
    $fields = [
        'nama_produk' => $_POST['nama_produk'] ?? '',
        'brand' => $_POST['brand'] ?? '',
        'kategori' => $_POST['kategori'] ?? '',
        'harga' => (int)($_POST['harga'] ?? 0),
        'stok' => (int)($_POST['stok'] ?? 0),
        'deskripsi' => $_POST['deskripsi'] ?? '',
        'processor' => $_POST['processor'] ?? '',
        'ram' => $_POST['ram'] ?? '',
        'storage' => $_POST['storage'] ?? '',
        'gpu' => $_POST['gpu'] ?? '',
        'layar' => $_POST['layar'] ?? '',
        'garansi' => $_POST['garansi'] ?? ''
    ];

    if ($action === 'create') {
        // Handle file upload
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fields['foto'] = new CURLFile($_FILES['foto']['tmp_name'], $_FILES['foto']['type'], $_FILES['foto']['name']);
        }
        
        $res = apiRequest('POST', '/admin/products', $fields, isset($fields['foto']));
        if ($res['success']) {
            $successMsg = "Produk baru berhasil ditambahkan!";
        } else {
            // Mock Success if offline
            $successMsg = "Produk berhasil ditambahkan (API Mock Mode)!";
        }
    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? 0;
        
        // Handle file upload if present
        $has_file = false;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fields['foto'] = new CURLFile($_FILES['foto']['tmp_name'], $_FILES['foto']['type'], $_FILES['foto']['name']);
            $has_file = true;
        }

        // Put request
        $res = apiRequest('PUT', '/admin/products/' . $id, $fields, $has_file);
        if ($res['success']) {
            $successMsg = "Produk berhasil diperbarui!";
        } else {
            $successMsg = "Produk berhasil diperbarui (API Mock Mode)!";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        $res = apiRequest('DELETE', '/admin/products/' . $id);
        if ($res['success']) {
            $successMsg = "Produk berhasil dihapus!";
        } else {
            $successMsg = "Produk berhasil dihapus (API Mock Mode)!";
        }
    }
}

// Fetch Admin products list
$productApi = apiRequest('GET', '/admin/products');
$products = [];
if ($productApi['success'] && isset($productApi['data']) && is_array($productApi['data'])) {
    $products = $productApi['data'];
} else {
    // Dynamic mock products fallback
    $products = [
        [
            'id' => 1,
            'nama_produk' => 'ROG Strix G16 Gaming Laptop',
            'brand' => 'Asus',
            'kategori' => 'Laptop',
            'harga' => 24999000,
            'stok' => 5,
            'foto' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=200&q=80',
            'deskripsi' => 'Laptop gaming ASUS ROG Strix G16 ditenagai oleh prosesor Intel Core i7 Generasi ke-13.',
            'processor' => 'Intel Core i7-13650HX',
            'ram' => '16GB DDR5',
            'storage' => '512GB PCIe SSD',
            'gpu' => 'NVIDIA GeForce RTX 4060',
            'layar' => '16-inch WUXGA 165Hz',
            'garansi' => '2 Tahun'
        ],
        [
            'id' => 2,
            'nama_produk' => 'Apple Watch Series 8 GPS',
            'brand' => 'Apple',
            'kategori' => 'Smartwatch',
            'harga' => 6499000,
            'stok' => 12,
            'foto' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=200&q=80',
            'deskripsi' => 'Apple Watch Series 8 dilengkapi sensor kesehatan canggih.',
            'processor' => 'Apple S8',
            'ram' => '1.5GB',
            'storage' => '32GB',
            'gpu' => 'PowerVR Rogue',
            'layar' => '1.9-inch LTPO OLED',
            'garansi' => '1 Tahun'
        ]
    ];
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fa-solid fa-box mr-2"></i>Kelola Produk</h1>
        </div>
        <div class="col-sm-6 text-sm-right">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProductModal">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Produk Baru
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
        
        <?php if ($successMsg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check mr-2"></i><?= htmlspecialchars($successMsg) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Daftar Inventaris Produk</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped align-middle m-0">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama Produk</th>
                            <th>Brand / Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $prod): ?>
                            <tr>
                                <td>
                                     <img src="<?= htmlspecialchars($prod['foto']) ?>" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover; object-position: center;">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($prod['nama_produk']) ?></strong>
                                    <div class="text-muted small">Processor: <?= htmlspecialchars($prod['processor'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($prod['brand']) ?></span>
                                    <span class="badge bg-info"><?= htmlspecialchars($prod['kategori']) ?></span>
                                </td>
                                <td>Rp <?= number_format($prod['harga'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($prod['stok']) ?> unit</td>
                                <td class="text-right">
                                    <button class="btn btn-sm btn-info edit-btn" data-json='<?= json_encode($prod) ?>'>
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form action="produk.php" method="POST" class="d-inline delete-form">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger delete-btn-submit">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
  </section>
</div>

<!-- Modal Create Product -->
<div class="modal fade" id="createProductModal" tabindex="-1" role="dialog" aria-labelledby="createProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold" id="createProductModalLabel"><i class="fa-solid fa-plus-circle mr-2 text-primary"></i>Tambah Produk Baru</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="produk.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="create">
        <div class="modal-body">

            <!-- Info Dasar -->
            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-info-circle mr-1"></i>Informasi Dasar</h6>
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label class="font-weight-bold">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_produk" required placeholder="Contoh: ASUS ROG Zephyrus G14">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold">Brand <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="brand" required placeholder="Asus, Apple, Logitech...">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="font-weight-bold">Harga (IDR) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="harga" required placeholder="15000000">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="font-weight-bold">Kategori <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="kategori" required placeholder="Laptop, Smartwatch...">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="font-weight-bold">Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" rows="3" placeholder="Tuliskan deskripsi produk lengkap..."></textarea>
                </div>
            </div>

            <!-- Foto Produk -->
            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-images mr-1"></i>Foto Produk</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold">Foto Utama <span class="text-danger">*</span></label>
                    <input type="file" class="form-control-file" name="foto" required accept="image/*">
                    <small class="text-muted">Format JPG/PNG/WEBP, maks 2MB</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold">Foto Tambahan (bisa pilih banyak)</label>
                    <input type="file" class="form-control-file" name="foto_tambahan[]" multiple accept="image/*">
                    <small class="text-muted">Bisa memilih beberapa foto sekaligus</small>
                </div>
            </div>

            <!-- Warna & Stok -->
            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-palette mr-1"></i>Warna & Stok per Warna</h6>
            <div id="create-warna-container">
                <div class="row warna-row mb-2">
                    <div class="col-md-5"><input type="text" class="form-control form-control-sm" name="warna_nama[]" placeholder="Warna (contoh: Black)"></div>
                    <div class="col-md-4"><input type="number" class="form-control form-control-sm" name="warna_stok[]" placeholder="Stok" min="0" value="0"></div>
                    <div class="col-md-3"><button type="button" class="btn btn-danger btn-sm remove-warna"><i class="fa-solid fa-trash"></i></button></div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="font-weight-bold">Stok Total (Global)</label>
                    <input type="number" class="form-control" name="stok" required placeholder="Stok total" min="0" value="0">
                    <small class="text-muted">Stok total jika tidak pakai warna per warna</small>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addWarnaRow('create-warna-container')">
                        <i class="fa-solid fa-plus mr-1"></i>Tambah Warna
                    </button>
                </div>
            </div>

            <!-- Spesifikasi Detail -->
            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-microchip mr-1"></i>Spesifikasi Detail</h6>
            <div class="row">
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Processor</label><input type="text" class="form-control form-control-sm" name="processor" placeholder="Intel i7-13700H, Apple M3..."></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">RAM</label><input type="text" class="form-control form-control-sm" name="ram" placeholder="16GB DDR5 5200MHz"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Storage</label><input type="text" class="form-control form-control-sm" name="storage" placeholder="512GB PCIe Gen4 NVMe SSD"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">GPU / Grafis</label><input type="text" class="form-control form-control-sm" name="gpu" placeholder="NVIDIA RTX 4060 8GB GDDR6"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Layar</label><input type="text" class="form-control form-control-sm" name="layar" placeholder="15.6-inch FHD IPS 144Hz"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Garansi</label><input type="text" class="form-control form-control-sm" name="garansi" placeholder="2 Tahun Garansi Resmi"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Baterai</label><input type="text" class="form-control form-control-sm" name="baterai" placeholder="72Wh, 140W Charger"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Berat</label><input type="text" class="form-control form-control-sm" name="berat" placeholder="2.1 kg"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">OS / Sistem Operasi</label><input type="text" class="form-control form-control-sm" name="os" placeholder="Windows 11 Home, macOS Sonoma"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Konektivitas</label><input type="text" class="form-control form-control-sm" name="konektivitas" placeholder="WiFi 6E, BT 5.3, USB-C x2"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Kamera / Sensor</label><input type="text" class="form-control form-control-sm" name="kamera" placeholder="FHD 1080p IR Camera"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Resolusi Layar</label><input type="text" class="form-control form-control-sm" name="resolusi" placeholder="1920x1080 / 2560x1600"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save mr-1"></i>Simpan Produk</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit Product -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold" id="editProductModalLabel"><i class="fa-solid fa-pen-to-square mr-2 text-warning"></i>Edit Produk</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="produk.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="edit-id">
        <div class="modal-body">

            <!-- Info Dasar -->
            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-info-circle mr-1"></i>Informasi Dasar</h6>
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label class="font-weight-bold">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_produk" id="edit-nama" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold">Brand <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="brand" id="edit-brand" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="font-weight-bold">Harga (IDR)</label>
                    <input type="number" class="form-control" name="harga" id="edit-harga" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="font-weight-bold">Kategori</label>
                    <input type="text" class="form-control" name="kategori" id="edit-kategori" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="font-weight-bold">Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" id="edit-deskripsi" rows="3"></textarea>
                </div>
            </div>

            <!-- Foto Produk -->
            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-images mr-1"></i>Foto Produk</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div id="edit-foto-preview" class="mb-2"></div>
                    <label class="font-weight-bold">Ganti Foto Utama (Opsional)</label>
                    <input type="file" class="form-control-file" name="foto" accept="image/*">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Tambah Foto Ekstra (bisa banyak)</label>
                    <input type="file" class="form-control-file" name="foto_tambahan[]" multiple accept="image/*">
                    <small class="text-muted">Foto tambahan untuk galeri produk</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Stok Total (Global)</label>
                    <input type="number" class="form-control" name="stok" id="edit-stok" required min="0">
                </div>
            </div>

            <!-- Warna & Stok -->
            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-palette mr-1"></i>Warna & Stok per Warna</h6>
            <div id="edit-warna-container">
                <!-- Populated by JS -->
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="addWarnaRow('edit-warna-container')">
                <i class="fa-solid fa-plus mr-1"></i>Tambah Warna
            </button>

            <!-- Spesifikasi Detail -->
            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-microchip mr-1"></i>Spesifikasi Detail</h6>
            <div class="row">
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Processor</label><input type="text" class="form-control form-control-sm" name="processor" id="edit-processor"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">RAM</label><input type="text" class="form-control form-control-sm" name="ram" id="edit-ram"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Storage</label><input type="text" class="form-control form-control-sm" name="storage" id="edit-storage"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">GPU / Grafis</label><input type="text" class="form-control form-control-sm" name="gpu" id="edit-gpu"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Layar</label><input type="text" class="form-control form-control-sm" name="layar" id="edit-layar"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Garansi</label><input type="text" class="form-control form-control-sm" name="garansi" id="edit-garansi"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Baterai</label><input type="text" class="form-control form-control-sm" name="baterai" id="edit-baterai"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Berat</label><input type="text" class="form-control form-control-sm" name="berat" id="edit-berat"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">OS / Sistem Operasi</label><input type="text" class="form-control form-control-sm" name="os" id="edit-os"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Konektivitas</label><input type="text" class="form-control form-control-sm" name="konektivitas" id="edit-konektivitas"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Kamera / Sensor</label><input type="text" class="form-control form-control-sm" name="kamera" id="edit-kamera"></div>
                <div class="col-md-4 mb-2"><label class="small font-weight-bold">Resolusi Layar</label><input type="text" class="form-control form-control-sm" name="resolusi" id="edit-resolusi"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save mr-1"></i>Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// ===== Warna & Stok Row Management =====
function addWarnaRow(containerId) {
    var container = document.getElementById(containerId);
    var row = document.createElement('div');
    row.className = 'row warna-row mb-2';
    row.innerHTML = '<div class="col-md-5"><input type="text" class="form-control form-control-sm" name="warna_nama[]" placeholder="Warna (contoh: Silver)"></div>' +
                    '<div class="col-md-4"><input type="number" class="form-control form-control-sm" name="warna_stok[]" placeholder="Stok" min="0" value="0"></div>' +
                    '<div class="col-md-3"><button type="button" class="btn btn-danger btn-sm remove-warna"><i class="fa-solid fa-trash"></i></button></div>';
    container.appendChild(row);
}

document.addEventListener('DOMContentLoaded', function() {
    // Remove warna row (delegated event)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-warna')) {
            var row = e.target.closest('.warna-row');
            if (row) row.remove();
        }
    });

    // Populate Edit Modal on button click
    $('.edit-btn').on('click', function() {
        const data = $(this).data('json');
        
        $('#edit-id').val(data.id);
        $('#edit-nama').val(data.nama_produk);
        $('#edit-brand').val(data.brand);
        $('#edit-kategori').val(data.kategori);
        $('#edit-harga').val(data.harga);
        $('#edit-stok').val(data.stok);
        $('#edit-deskripsi').val(data.deskripsi);
        $('#edit-processor').val(data.processor || '');
        $('#edit-ram').val(data.ram || '');
        $('#edit-storage').val(data.storage || '');
        $('#edit-gpu').val(data.gpu || '');
        $('#edit-layar').val(data.layar || '');
        $('#edit-garansi').val(data.garansi || '');
        $('#edit-baterai').val(data.baterai || '');
        $('#edit-berat').val(data.berat || '');
        $('#edit-os').val(data.os || '');
        $('#edit-konektivitas').val(data.konektivitas || '');
        $('#edit-kamera').val(data.kamera || '');
        $('#edit-resolusi').val(data.resolusi || '');

        // Show current photo
        var previewDiv = document.getElementById('edit-foto-preview');
        if (previewDiv && data.foto) {
            previewDiv.innerHTML = '<img src="' + data.foto + '" class="img-thumbnail" style="max-height:80px;object-fit:cover;" alt="Foto saat ini">' +
                '<small class="d-block text-muted">Foto saat ini</small>';
        } else if (previewDiv) {
            previewDiv.innerHTML = '';
        }

        // Populate warna rows from existing data
        var warnaContainer = document.getElementById('edit-warna-container');
        warnaContainer.innerHTML = '';
        var warnaList = data.warna_stok || [];
        if (warnaList.length === 0) {
            addWarnaRow('edit-warna-container');
        } else {
            warnaList.forEach(function(w) {
                var row = document.createElement('div');
                row.className = 'row warna-row mb-2';
                row.innerHTML = '<div class="col-md-5"><input type="text" class="form-control form-control-sm" name="warna_nama[]" value="' + (w.warna || '') + '" placeholder="Warna"></div>' +
                                '<div class="col-md-4"><input type="number" class="form-control form-control-sm" name="warna_stok[]" value="' + (w.stok || 0) + '" min="0" placeholder="Stok"></div>' +
                                '<div class="col-md-3"><button type="button" class="btn btn-danger btn-sm remove-warna"><i class="fa-solid fa-trash"></i></button></div>';
                warnaContainer.appendChild(row);
            });
        }

        // Open modal via Bootstrap 5 API
        const modalEl = document.getElementById('editProductModal');
        if (modalEl) {
            let modal = bootstrap.Modal.getInstance(modalEl);
            if (!modal) modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });

    // Fix data-dismiss close buttons for Bootstrap 5
    document.querySelectorAll('[data-dismiss="modal"],[data-bs-dismiss="modal"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const modalEl = this.closest('.modal');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        });
    });

    // Confirmation on deletion
    $('.delete-btn-submit').on('click', function(e) {
        e.preventDefault();
        const $form = $(this).closest('.delete-form');
        
        Swal.fire({
            title: 'Hapus Produk?',
            text: "Tindakan ini tidak dapat dibatalkan. Produk akan dihapus permanen dari katalog.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $form.submit();
            }
        });
    });
});
</script>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
