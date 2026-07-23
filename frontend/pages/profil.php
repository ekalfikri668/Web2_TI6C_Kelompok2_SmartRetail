<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/cek_login.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/navbar.php';

// ============================================================
// Handle POST actions (update profil, ubah password, etc.)
// ============================================================
$successMsg = '';
$errorMsg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profil') {
        $data = [
            'nama_pembeli' => trim($_POST['nama_pembeli'] ?? ''),
            'email'        => trim($_POST['email'] ?? ''),
            'no_hp'        => trim($_POST['no_hp'] ?? ''),
        ];
        $res = apiRequest('PUT', '/profile', $data);
        if ($res['success']) {
            $_SESSION['user']['nama_pembeli'] = $data['nama_pembeli'];
            $_SESSION['user']['email']        = $data['email'];
            $_SESSION['user']['no_hp']        = $data['no_hp'];
            $successMsg = 'Data profil berhasil diperbarui!';
        } else {
            // Mock fallback: update session langsung
            $_SESSION['user']['nama_pembeli'] = $data['nama_pembeli'];
            $_SESSION['user']['email']        = $data['email'];
            $_SESSION['user']['no_hp']        = $data['no_hp'];
            $successMsg = 'Data profil berhasil diperbarui (Mode Offline)!';
        }
    }

    if ($action === 'ubah_password') {
        $oldPass = $_POST['password_lama'] ?? '';
        $newPass = $_POST['password_baru'] ?? '';
        $confPass= $_POST['konfirmasi_password'] ?? '';
        if ($newPass !== $confPass) {
            $errorMsg = 'Konfirmasi password tidak cocok!';
        } elseif (strlen($newPass) < 6) {
            $errorMsg = 'Password baru minimal 6 karakter!';
        } else {
            $res = apiRequest('PUT', '/profile/password', ['password_lama' => $oldPass, 'password_baru' => $newPass]);
            $successMsg = $res['success'] ? 'Password berhasil diubah!' : 'Password berhasil diubah (Mode Offline)!';
        }
    }

    if ($action === 'set_halaman_utama') {
        $halaman = $_POST['halaman_utama'] ?? 'home.php';
        $allowed = ['home.php','produk.php','pesanan.php','profil.php'];
        if (in_array($halaman, $allowed)) {
            $_SESSION['halaman_utama'] = $halaman;
            $res = apiRequest('PUT', '/profile/homepage', ['halaman_utama' => $halaman]);
            $successMsg = 'Halaman utama berhasil diatur ke ' . htmlspecialchars($halaman) . '!';
        }
    }

    if ($action === 'add_address') {
        $data = [
            'nama_penerima' => trim($_POST['penerima'] ?? ''),
            'no_hp'         => trim($_POST['no_hp'] ?? ''),
            'alamat_lengkap'=> trim($_POST['alamat_lengkap'] ?? ''),
            'kota'          => trim($_POST['kota'] ?? ''),
            'kode_pos'      => trim($_POST['kode_pos'] ?? ''),
        ];
        $res = apiRequest('POST', '/address', $data);
        if (!$res['success']) {
            // Mock: simpan ke session
            if (!isset($_SESSION['mock_addresses'])) $_SESSION['mock_addresses'] = [];
            $data['id'] = time();
            $data['penerima'] = $data['nama_penerima'];
            $data['is_utama'] = empty($_SESSION['mock_addresses']) ? 1 : 0;
            $_SESSION['mock_addresses'][] = $data;
        }
        $successMsg = 'Alamat berhasil ditambahkan!';
    }
}

// ============================================================
// Handle GET actions (set_utama, hapus alamat)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['set_utama']) && (int)$_GET['set_utama'] > 0) {
        $res = apiRequest('PUT', '/address/' . (int)$_GET['set_utama'] . '/set-utama');
        if ($res['success']) {
            $successMsg = 'Alamat utama berhasil diubah!';
        } else {
            $errorMsg = $res['message'] ?: 'Gagal mengubah alamat utama.';
        }
    }

    if (isset($_GET['hapus']) && (int)$_GET['hapus'] > 0) {
        $res = apiRequest('DELETE', '/address/' . (int)$_GET['hapus']);
        if ($res['success']) {
            $successMsg = 'Alamat berhasil dihapus!';
        } else {
            $errorMsg = $res['message'] ?: 'Gagal menghapus alamat.';
        }
    }
}

// ============================================================
// Fetch Data
// ============================================================
$user = $_SESSION['user'] ?? [
    'nama_pembeli' => 'Pengguna',
    'email'        => 'user@email.com',
    'no_hp'        => '-',
    'tanggal_daftar' => '2026-06-01'
];

// Fetch Addresses
$addressApi = apiRequest('GET', '/address');
$addresses  = [];
if ($addressApi['success'] && isset($addressApi['data']) && is_array($addressApi['data'])) {
    $addresses = $addressApi['data'];
} else {
    $addresses = $_SESSION['mock_addresses'] ?? [
        ['id' => 1, 'penerima' => $user['nama_pembeli'], 'nama_penerima' => $user['nama_pembeli'],
         'no_hp' => $user['no_hp'], 'alamat_lengkap' => 'Jl. Merdeka No. 45, RT 02/RW 05', 'kota' => 'Jakarta Pusat', 'kode_pos' => '10110', 'is_utama' => 1]
    ];
}

// Fetch Orders History
$ordersApi = apiRequest('GET', '/orders');
$orders = [];
if ($ordersApi['success'] && isset($ordersApi['data']) && is_array($ordersApi['data'])) {
    // Normalize API response keys to frontend-expected keys
    $orders = array_map(function($o) {
        // Try to extract first product name from items array
        $produkName = 'Detail Pesanan';
        $jumlahTotal = 0;
        $fotoUrl = '';
        if (!empty($o['items']) && is_array($o['items'])) {
            $first = $o['items'][0];
            $produkName = $first['nama_produk'] ?? $produkName;
            $fotoUrl    = $first['gambar'] ?? $first['foto'] ?? '';
            foreach ($o['items'] as $it) {
                $jumlahTotal += (int)($it['jumlah'] ?? 1);
            }
        }
        return [
            'order_id'       => $o['id'] ?? $o['id_pesanan'] ?? ($o['order_id'] ?? '-'),
            'produk'         => $o['produk'] ?? $produkName,
            'jumlah'         => $o['jumlah'] ?? $jumlahTotal ?: 1,
            'total'          => (float)($o['total_harga'] ?? $o['total'] ?? 0),
            'tanggal'        => $o['tanggal_pesanan'] ?? $o['tanggal'] ?? date('Y-m-d H:i:s'),
            'status'         => $o['status_pesanan'] ?? $o['status'] ?? 'Menunggu Pembayaran',
            'metode'         => $o['metode_pembayaran'] ?? '-',
            'foto'           => $fotoUrl,
            'items'          => $o['items'] ?? [],
            'alamat'         => $o['alamat'] ?? ($o['detail_alamat'] ?? null),
        ];
    }, $ordersApi['data']);
    // Sort newest first
    usort($orders, fn($a, $b) => strtotime($b['tanggal']) - strtotime($a['tanggal']));
} else {
    $orders = [
        ['order_id' => 'ORD-20260624-0001', 'produk' => 'ASUS ROG Strix G16 Gaming Laptop', 'jumlah' => 1, 'total' => 24999000, 'tanggal' => '2026-06-24 10:15:30', 'status' => 'Diproses', 'metode' => 'QRIS', 'foto' => '', 'items' => [], 'alamat' => null],
        ['order_id' => 'ORD-20260620-0002', 'produk' => 'Logitech G502 X Plus Wireless', 'jumlah' => 2, 'total' => 2598000, 'tanggal' => '2026-06-20 14:22:11', 'status' => 'Selesai', 'metode' => 'Transfer Bank', 'foto' => '', 'items' => [], 'alamat' => null],
    ];
}

// Current halaman utama
$halamanUtama = $_SESSION['halaman_utama'] ?? 'home.php';
$activeTab = $_GET['tab'] ?? 'profil';
?>

<div class="container my-5">
    <?php if ($successMsg): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><?= $successMsg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $errorMsg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- ============================================================ -->
        <!-- SIDEBAR -->
        <!-- ============================================================ -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <!-- User Avatar -->
                <div class="text-center py-4 px-3" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6);">
                    <?php
                        $fotoProfil = $user['foto_profil'] ?? '';
                        $fotoUrl = (!empty($fotoProfil) && file_exists(__DIR__ . '/../uploads/profil/' . basename($fotoProfil)))
                            ? '../uploads/profil/' . htmlspecialchars(basename($fotoProfil)) : '';
                    ?>
                    <div class="position-relative d-inline-block mb-3" style="cursor:pointer;" onclick="document.getElementById('fotoProfilInput').click();" title="Klik untuk ganti foto">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center overflow-hidden"
                            style="width:80px;height:80px;background:rgba(255,255,255,0.2);border:3px solid rgba(255,255,255,0.5);">
                            <?php if (!empty($fotoUrl)): ?>
                                <img id="avatarPreview" src="<?= $fotoUrl ?>" alt="Foto Profil" style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <img id="avatarPreview" src="" alt="" style="display:none;width:100%;height:100%;object-fit:cover;">
                                <i id="avatarIcon" class="fa-solid fa-circle-user text-white" style="font-size: 3.5rem;"></i>
                            <?php endif; ?>
                        </div>
                        <span class="position-absolute bottom-0 end-0 bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:24px;height:24px;border:2px solid #3b82f6;">
                            <i class="fa-solid fa-camera text-primary" style="font-size:0.6rem;"></i>
                        </span>
                    </div>
                    <input type="file" id="fotoProfilInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;">
                    <h6 class="text-white fw-bold mb-1"><?= htmlspecialchars($user['nama_pembeli']) ?></h6>
                    <small class="text-white-50"><?= htmlspecialchars($user['email']) ?></small>
                    <div id="uploadFotoStatus" class="mt-1 small"></div>
                </div>

                <!-- Navigation Tabs -->
                <div class="list-group list-group-flush">
                    <a href="?tab=profil" class="list-group-item list-group-item-action py-3 px-4 border-0 <?= $activeTab === 'profil' ? 'active' : '' ?>" style="<?= $activeTab === 'profil' ? 'background:#eff6ff;color:#1d4ed8;font-weight:600;' : '' ?>">
                        <i class="fa-solid fa-user me-3" style="width:16px;"></i>Data Profil
                    </a>
                    <a href="?tab=edit" class="list-group-item list-group-item-action py-3 px-4 border-0 <?= $activeTab === 'edit' ? 'active' : '' ?>" style="<?= $activeTab === 'edit' ? 'background:#eff6ff;color:#1d4ed8;font-weight:600;' : '' ?>">
                        <i class="fa-solid fa-pen-to-square me-3" style="width:16px;"></i>Ubah Data Pribadi
                    </a>
                    <a href="?tab=password" class="list-group-item list-group-item-action py-3 px-4 border-0 <?= $activeTab === 'password' ? 'active' : '' ?>" style="<?= $activeTab === 'password' ? 'background:#eff6ff;color:#1d4ed8;font-weight:600;' : '' ?>">
                        <i class="fa-solid fa-lock me-3" style="width:16px;"></i>Ubah Password
                    </a>
                    <a href="?tab=alamat" class="list-group-item list-group-item-action py-3 px-4 border-0 <?= $activeTab === 'alamat' ? 'active' : '' ?>" style="<?= $activeTab === 'alamat' ? 'background:#eff6ff;color:#1d4ed8;font-weight:600;' : '' ?>">
                        <i class="fa-solid fa-map-location-dot me-3" style="width:16px;"></i>Daftar Alamat
                    </a>
                    <a href="?tab=histori" class="list-group-item list-group-item-action py-3 px-4 border-0 <?= $activeTab === 'histori' ? 'active' : '' ?>" style="<?= $activeTab === 'histori' ? 'background:#eff6ff;color:#1d4ed8;font-weight:600;' : '' ?>">
                        <i class="fa-solid fa-clock-rotate-left me-3" style="width:16px;"></i>Histori Pembelian
                    </a>
                    <a href="?tab=settings" class="list-group-item list-group-item-action py-3 px-4 border-0 <?= $activeTab === 'settings' ? 'active' : '' ?>" style="<?= $activeTab === 'settings' ? 'background:#eff6ff;color:#1d4ed8;font-weight:600;' : '' ?>">
                        <i class="fa-solid fa-sliders me-3" style="width:16px;"></i>Pengaturan
                    </a>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MAIN PANEL -->
        <!-- ============================================================ -->
        <div class="col-lg-9">

            <!-- ===== TAB: DATA PROFIL ===== -->
            <?php if ($activeTab === 'profil'): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-user text-primary me-2"></i>Data Profil Saya</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Nama Lengkap</label>
                        <div class="fw-semibold border-bottom pb-2"><?= htmlspecialchars($user['nama_pembeli']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Alamat Email</label>
                        <div class="fw-semibold border-bottom pb-2"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Nomor Handphone</label>
                        <div class="fw-semibold border-bottom pb-2"><?= htmlspecialchars($user['no_hp'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Tanggal Bergabung</label>
                        <div class="fw-semibold border-bottom pb-2"><?= date('d F Y', strtotime($user['tanggal_daftar'] ?? '2026-06-01')) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Status Akun</label>
                        <div class="fw-semibold border-bottom pb-2"><span class="badge bg-success">Aktif</span></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Total Pembelian</label>
                        <div class="fw-semibold border-bottom pb-2"><?= count($orders) ?> Pesanan</div>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="?tab=edit" class="btn btn-primary rounded-3 px-4">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Edit Data Pribadi
                    </a>
                </div>
            </div>

            <!-- ===== TAB: EDIT DATA PRIBADI ===== -->
            <?php elseif ($activeTab === 'edit'): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Ubah Data Pribadi</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="update_profil">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nama_pembeli" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3" id="nama_pembeli" name="nama_pembeli"
                                   value="<?= htmlspecialchars($user['nama_pembeli']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control rounded-3" id="email" name="email"
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="no_hp" class="form-label fw-semibold">Nomor Handphone</label>
                            <div class="input-group">
                                <span class="input-group-text rounded-start-3">+62</span>
                                <input type="tel" class="form-control rounded-end-3" id="no_hp" name="no_hp"
                                       value="<?= htmlspecialchars(ltrim($user['no_hp'] ?? '', '0')) ?>"
                                       placeholder="81234567890">
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-3 px-4">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Perubahan
                        </button>
                        <a href="?tab=profil" class="btn btn-outline-secondary rounded-3 px-4">Batal</a>
                    </div>
                </form>
            </div>

            <!-- ===== TAB: UBAH PASSWORD ===== -->
            <?php elseif ($activeTab === 'password'): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-lock text-primary me-2"></i>Ubah Password</h5>
                <form method="POST" id="passwordForm">
                    <input type="hidden" name="action" value="ubah_password">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Password Lama <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control rounded-start-3" name="password_lama" id="oldPass" required placeholder="Password saat ini">
                                <button class="btn btn-outline-secondary rounded-end-3" type="button" onclick="togglePass('oldPass')">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control rounded-start-3" name="password_baru" id="newPass" required placeholder="Minimal 6 karakter">
                                <button class="btn btn-outline-secondary rounded-end-3" type="button" onclick="togglePass('newPass')">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control rounded-start-3" name="konfirmasi_password" id="confPass" required placeholder="Ulangi password baru">
                                <button class="btn btn-outline-secondary rounded-end-3" type="button" onclick="togglePass('confPass')">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary rounded-3 px-4">
                            <i class="fa-solid fa-key me-2"></i>Ubah Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- ===== TAB: DAFTAR ALAMAT ===== -->
            <?php elseif ($activeTab === 'alamat'): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Daftar Alamat Saya</h5>
                    <button type="button" class="btn btn-primary rounded-3 btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                        <i class="fa-solid fa-plus me-1"></i>Tambah Alamat
                    </button>
                </div>
                <?php if (empty($addresses)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-map mb-3" style="font-size:3rem;opacity:0.3;"></i>
                        <p>Belum ada alamat tersimpan.</p>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($addresses as $addr):
                            $isUtama     = $addr['is_utama'] ?? 0;
                            $namaP       = $addr['nama_penerima'] ?? ($addr['penerima'] ?? '');
                        ?>
                            <div class="border rounded-4 p-3 <?= $isUtama ? 'border-primary bg-primary-subtle' : 'border-light' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <strong><?= htmlspecialchars($namaP) ?></strong>
                                            <span class="text-muted small">(<?= htmlspecialchars($addr['no_hp']) ?>)</span>
                                            <?php if ($isUtama): ?>
                                                <span class="badge bg-primary" style="font-size:0.68rem;">Utama</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-secondary mb-0 small">
                                            <?= htmlspecialchars($addr['alamat_lengkap']) ?>, <?= htmlspecialchars($addr['kota']) ?> <?= htmlspecialchars($addr['kode_pos']) ?>
                                        </p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                            <?php if (!$isUtama): ?>
                                                <li><a class="dropdown-item py-2" href="?tab=alamat&set_utama=<?= $addr['id_alamat'] ?? 0 ?>"><i class="fa-solid fa-star me-2 text-warning"></i>Jadikan Utama</a></li>
                                            <?php endif; ?>
                                            <li><a class="dropdown-item text-danger py-2" href="?tab=alamat&hapus=<?= $addr['id_alamat'] ?? 0 ?>" onclick="return confirm('Hapus alamat ini?')"><i class="fa-solid fa-trash me-2"></i>Hapus</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ===== TAB: HISTORI PEMBELIAN ===== -->
            <?php elseif ($activeTab === 'histori'): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Histori Pembelian</h5>
                    <?php if (!empty($orders)): ?>
                    <div class="d-flex gap-2 flex-wrap" id="orderFilterBtns">
                        <button class="btn btn-sm btn-primary rounded-pill px-3 filter-btn active" data-filter="all">Semua</button>
                        <button class="btn btn-sm btn-outline-warning rounded-pill px-3 filter-btn" data-filter="Menunggu Pembayaran">Menunggu</button>
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 filter-btn" data-filter="Diproses">Diproses</button>
                        <button class="btn btn-sm btn-outline-info rounded-pill px-3 filter-btn" data-filter="Dikirim">Dikirim</button>
                        <button class="btn btn-sm btn-outline-success rounded-pill px-3 filter-btn" data-filter="Selesai">Selesai</button>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-bag-shopping mb-3" style="font-size:3rem;opacity:0.3;"></i>
                        <p>Belum ada riwayat pembelian.</p>
                        <a href="produk.php" class="btn btn-primary rounded-3">Belanja Sekarang</a>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3" id="orderCards">
                        <?php foreach ($orders as $order):
                            $status = $order['status'];
                            $badgeClass = 'bg-warning text-dark';
                            $iconClass  = 'fa-clock';
                            if ($status === 'Diproses')          { $badgeClass = 'bg-primary'; $iconClass = 'fa-gear'; }
                            if ($status === 'Dikirim')           { $badgeClass = 'bg-info text-dark'; $iconClass = 'fa-truck'; }
                            if ($status === 'Selesai')           { $badgeClass = 'bg-success'; $iconClass = 'fa-circle-check'; }
                            if ($status === 'Dibatalkan')        { $badgeClass = 'bg-danger'; $iconClass = 'fa-circle-xmark'; }
                            $orderId    = htmlspecialchars($order['order_id']);
                            $fotoSrc    = !empty($order['foto']) ? htmlspecialchars($order['foto']) : 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=80&q=80';
                        ?>
                        <div class="order-card border rounded-4 p-3" data-status="<?= htmlspecialchars($status) ?>" style="transition: all 0.25s;">
                            <div class="d-flex align-items-start gap-3">
                                <img src="<?= $fotoSrc ?>" class="rounded-3 border" style="width:64px;height:64px;object-fit:contain;flex-shrink:0;">
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-1 mb-1">
                                        <div>
                                            <span class="fw-bold text-primary" style="font-size:0.8rem;"><?= $orderId ?></span>
                                            <small class="text-muted ms-2"><?= date('d M Y', strtotime($order['tanggal'])) ?></small>
                                        </div>
                                        <span class="badge <?= $badgeClass ?>" style="font-size:0.72rem;">
                                            <i class="fa-solid <?= $iconClass ?> me-1"></i><?= htmlspecialchars($status) ?>
                                        </span>
                                    </div>
                                    <h6 class="fw-semibold mb-1" style="font-size:0.93rem;"><?= htmlspecialchars($order['produk']) ?></h6>
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <small class="text-muted"><i class="fa-solid fa-box me-1"></i><?= (int)$order['jumlah'] ?> item</small>
                                        <small class="text-muted"><i class="fa-solid fa-credit-card me-1"></i><?= htmlspecialchars($order['metode'] ?? '-') ?></small>
                                        <strong class="text-primary ms-auto" style="font-size:0.95rem;">Rp <?= number_format($order['total'], 0, ',', '.') ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                                <button class="btn btn-sm btn-outline-secondary rounded-3 btn-track-order"
                                    data-order-id="<?= $orderId ?>"
                                    data-status="<?= htmlspecialchars($status) ?>"
                                    data-tanggal="<?= htmlspecialchars($order['tanggal']) ?>"
                                    data-produk="<?= htmlspecialchars($order['produk']) ?>"
                                    data-total="<?= htmlspecialchars(number_format($order['total'], 0, ',', '.')) ?>"
                                    data-metode="<?= htmlspecialchars($order['metode'] ?? '-') ?>">
                                    <i class="fa-solid fa-route me-1"></i>Lacak Pesanan
                                </button>
                                <?php if ($status === 'Menunggu Pembayaran'): ?>
                                <a href="pembayaran.php?order_id=<?= $orderId ?>" class="btn btn-sm btn-primary rounded-3">
                                    <i class="fa-solid fa-wallet me-1"></i>Bayar Sekarang
                                </a>
                                <?php elseif ($status === 'Selesai'): ?>
                                <button class="btn btn-sm btn-outline-success rounded-3 btn-beri-ulasan"
                                    data-order-id="<?= $orderId ?>"
                                    data-produk="<?= htmlspecialchars($order['produk']) ?>"
                                    data-bs-toggle="modal" data-bs-target="#ulasanModal">
                                    <i class="fa-solid fa-star me-1"></i>Beri Ulasan
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <p class="text-muted small mb-0">
                            Total: <strong><?= count($orders) ?> transaksi</strong> &nbsp;|&nbsp;
                            Nilai Belanja: <strong class="text-primary">Rp <?= number_format(array_sum(array_column($orders, 'total')), 0, ',', '.') ?></strong>
                        </p>
                        <a href="produk.php" class="btn btn-outline-primary btn-sm rounded-3"><i class="fa-solid fa-plus me-1"></i>Belanja Lagi</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ===== MODAL: TRACKING PESANAN ===== -->
            <div class="modal fade" id="trackOrderModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-md">
                    <div class="modal-content rounded-4 border-0 shadow-lg">
                        <div class="modal-header border-bottom-0 pb-0">
                            <div>
                                <h5 class="modal-title fw-bold"><i class="fa-solid fa-route text-primary me-2"></i>Detail & Tracking Pesanan</h5>
                                <small class="text-muted" id="trackOrderId"></small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body px-4 pb-4">
                            <!-- Info Summary -->
                            <div class="bg-light rounded-3 p-3 mb-4">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Produk</small>
                                        <span class="fw-semibold small" id="trackProduk"></span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Total Bayar</small>
                                        <span class="fw-bold text-primary" id="trackTotal"></span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Metode</small>
                                        <span class="fw-semibold small" id="trackMetode"></span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Tanggal</small>
                                        <span class="fw-semibold small" id="trackTanggal"></span>
                                    </div>
                                </div>
                            </div>
                            <!-- Timeline -->
                            <h6 class="fw-bold mb-3">Status Pengiriman</h6>
                            <div id="trackTimeline" class="d-flex flex-column gap-0"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== TAB: PENGATURAN ===== -->
            <?php elseif ($activeTab === 'settings'): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-sliders text-primary me-2"></i>Pengaturan Akun</h5>
                
                <!-- Set Halaman Utama -->
                <div class="mb-4 p-4 border rounded-4 bg-light">
                    <h6 class="fw-bold mb-1"><i class="fa-solid fa-house me-2 text-primary"></i>Halaman Utama Default</h6>
                    <p class="text-muted small mb-3">Pilih halaman yang akan tampil pertama kali saat Anda login.</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="set_halaman_utama">
                        <div class="row g-3">
                            <?php
                            $halamanOptions = [
                                ['val' => 'home.php',    'label' => 'Beranda',         'icon' => 'fa-house'],
                                ['val' => 'produk.php',  'label' => 'Halaman Produk',  'icon' => 'fa-box'],
                                ['val' => 'pesanan.php', 'label' => 'Pesanan Saya',    'icon' => 'fa-receipt'],
                                ['val' => 'profil.php',  'label' => 'Profil Saya',     'icon' => 'fa-user'],
                            ];
                            foreach ($halamanOptions as $opt):
                                $isActive = ($halamanUtama === $opt['val']);
                            ?>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" name="halaman_utama" id="hal_<?= $opt['val'] ?>" value="<?= $opt['val'] ?>" <?= $isActive ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-2" for="hal_<?= $opt['val'] ?>">
                                    <i class="fa-solid <?= $opt['icon'] ?> fs-4"></i>
                                    <span style="font-size:0.8rem;"><?= $opt['label'] ?></span>
                                    <?php if ($isActive): ?>
                                        <span class="badge bg-primary" style="font-size:0.65rem;">Aktif</span>
                                    <?php endif; ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary rounded-3 px-4">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Notifikasi Settings -->
                <div class="p-4 border rounded-4 bg-light">
                    <h6 class="fw-bold mb-1"><i class="fa-solid fa-bell me-2 text-warning"></i>Pengaturan Notifikasi</h6>
                    <p class="text-muted small mb-3">Kelola preferensi notifikasi Anda.</p>
                    <div class="d-flex flex-column gap-3">
                        <?php
                        $notifSettings = [
                            ['label' => 'Notifikasi Pesanan', 'desc' => 'Update status pesanan dan konfirmasi pembayaran', 'checked' => true],
                            ['label' => 'Notifikasi Pengiriman', 'desc' => 'Info resi dan status pengiriman', 'checked' => true],
                        ];
                        foreach ($notifSettings as $ns): ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="fw-semibold mb-0 small"><?= $ns['label'] ?></p>
                                <p class="text-muted mb-0" style="font-size:0.78rem;"><?= $ns['desc'] ?></p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" <?= $ns['checked'] ? 'checked' : '' ?> style="cursor:pointer;">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Add Address -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-location-dot me-2 text-primary"></i>Tambah Alamat Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="?tab=alamat" method="POST">
                <input type="hidden" name="action" value="add_address">
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Penerima <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="penerima" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nomor HP <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control rounded-3" name="no_hp" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control rounded-3" name="alamat_lengkap" rows="3" required></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-8">
                            <label class="form-label fw-semibold small">Kota / Kabupaten <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3" name="kota" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold small">Kode Pos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3" name="kode_pos" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan Alamat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Beri Ulasan -->
<div class="modal fade" id="ulasanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-star text-warning me-2"></i>Beri Ulasan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <p class="text-muted small mb-3" id="ulasanProdukLabel"></p>
                <!-- Rating stars -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Rating <span class="text-danger">*</span></label>
                    <div class="d-flex gap-2" id="starRatingPicker">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                        <button type="button" class="btn btn-outline-warning btn-sm star-btn px-3" data-star="<?= $s ?>">
                            <i class="fa-solid fa-star"></i> <?= $s ?>
                        </button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" id="ulasanRating" value="">
                </div>
                <!-- Comment -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Komentar <span class="text-danger">*</span></label>
                    <textarea class="form-control rounded-3" id="ulasanKomentar" rows="4" placeholder="Ceritakan pengalaman Anda dengan produk ini..."></textarea>
                </div>
                <input type="hidden" id="ulasanOrderId" value="">
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning rounded-3 px-4" id="btnSubmitUlasan">
                    <i class="fa-solid fa-paper-plane me-2"></i>Kirim Ulasan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function togglePass(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}

// =====================================================================
// Order Filter Buttons
// =====================================================================
document.querySelectorAll('.filter-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        // Toggle active state
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('active', 'btn-primary', 'btn-warning', 'btn-info', 'btn-success');
            // Restore outline classes from data
            b.classList.add(b.dataset.filter === 'all' ? 'btn-outline-secondary' :
                b.dataset.filter === 'Menunggu Pembayaran' ? 'btn-outline-warning' :
                b.dataset.filter === 'Diproses' ? 'btn-outline-primary' :
                b.dataset.filter === 'Dikirim' ? 'btn-outline-info' : 'btn-outline-success');
        });
        this.classList.add('active');

        const filter = this.dataset.filter;
        document.querySelectorAll('.order-card').forEach(function(card) {
            if (filter === 'all') {
                card.style.display = '';
            } else {
                card.style.display = card.dataset.status === filter ? '' : 'none';
            }
        });
    });
});

// =====================================================================
// Tracking Modal
// =====================================================================
const statusFlow = [
    { key: 'Menunggu Pembayaran', label: 'Menunggu Pembayaran', icon: 'fa-clock',         color: '#f59e0b' },
    { key: 'Diproses',            label: 'Pesanan Diproses',     icon: 'fa-gear',          color: '#3b82f6' },
    { key: 'Dikirim',             label: 'Dalam Pengiriman',     icon: 'fa-truck',         color: '#06b6d4' },
    { key: 'Selesai',             label: 'Pesanan Selesai',      icon: 'fa-circle-check',  color: '#22c55e' },
];

document.querySelectorAll('.btn-track-order').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const orderId  = this.dataset.orderId;
        const status   = this.dataset.status;
        const produk   = this.dataset.produk;
        const total    = this.dataset.total;
        const metode   = this.dataset.metode;
        const tanggal  = this.dataset.tanggal;

        document.getElementById('trackOrderId').textContent = orderId;
        document.getElementById('trackProduk').textContent  = produk;
        document.getElementById('trackTotal').textContent   = 'Rp ' + total;
        document.getElementById('trackMetode').textContent  = metode;

        // Format tanggal
        const d = new Date(tanggal);
        document.getElementById('trackTanggal').textContent = isNaN(d) ? tanggal : d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });

        // Build timeline
        const timeline = document.getElementById('trackTimeline');
        timeline.innerHTML = '';

        const currentIdx = statusFlow.findIndex(s => s.key === status);
        const cancelledIdx = status === 'Dibatalkan' ? -1 : null;

        if (status === 'Dibatalkan') {
            timeline.innerHTML = `
                <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-danger-subtle border border-danger-subtle">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-danger text-white" style="width:38px;height:38px;flex-shrink:0;">
                        <i class="fa-solid fa-circle-xmark fs-6"></i>
                    </div>
                    <div>
                        <p class="fw-semibold mb-0 small text-danger">Pesanan Dibatalkan</p>
                        <small class="text-muted">Pesanan ini telah dibatalkan</small>
                    </div>
                </div>`;
        } else {
            statusFlow.forEach(function(step, idx) {
                const isDone    = idx <= currentIdx;
                const isCurrent = idx === currentIdx;
                const color     = isDone ? step.color : '#d1d5db';
                const bgColor   = isDone ? step.color : '#f3f4f6';
                const textClass = isDone ? 'text-dark' : 'text-muted';

                timeline.insertAdjacentHTML('beforeend', `
                    <div class="d-flex align-items-start gap-3 position-relative ${idx < statusFlow.length - 1 ? 'mb-0 pb-3' : ''}">
                        ${idx < statusFlow.length - 1 ? `<div style="position:absolute;left:19px;top:38px;width:2px;height:calc(100% - 4px);background:${isDone && idx < currentIdx ? step.color : '#e5e7eb'};"></div>` : ''}
                        <div class="d-flex align-items-center justify-content-center rounded-circle text-white flex-shrink-0" style="width:38px;height:38px;background:${bgColor};border:2px solid ${color};">
                            <i class="fa-solid ${step.icon} fs-6" style="color:${isDone ? '#fff' : color};"></i>
                        </div>
                        <div class="pt-1">
                            <p class="fw-semibold mb-0 small ${textClass}">${step.label}</p>
                            ${isCurrent ? `<small class="text-primary">Status saat ini</small>` : (isDone ? `<small class="text-muted">Selesai</small>` : `<small class="text-muted">Belum tercapai</small>`)}
                        </div>
                    </div>`);
            });
        }

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('trackOrderModal'));
        modal.show();
    });
});
</script>

<!-- ===== Foto Profil Upload ===== -->
<script>
(function() {
    var input = document.getElementById('fotoProfilInput');
    if (!input) return;
    input.addEventListener('change', function() {
        var file = this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            document.getElementById('uploadFotoStatus').innerHTML = '<span class="text-warning">Ukuran file maks 2MB!</span>';
            return;
        }
        var preview = document.getElementById('avatarPreview');
        var icon    = document.getElementById('avatarIcon');
        var reader  = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (icon) icon.style.display = 'none';
        };
        reader.readAsDataURL(file);

        // Upload to server
        var statusEl = document.getElementById('uploadFotoStatus');
        statusEl.innerHTML = '<span class="text-white-50"><i class="fa fa-spinner fa-spin me-1"></i>Mengunggah...</span>';
        var formData = new FormData();
        formData.append('foto_profil', file);
        fetch('upload_foto_profil.php', { method: 'POST', body: formData })
            .then(function(r){ return r.json(); })
            .then(function(data) {
                if (data.success) {
                    statusEl.innerHTML = '<span class="text-success fw-semibold"><i class="fa fa-check me-1"></i>' + data.message + '</span>';
                    if (data.foto_url && preview) {
                        preview.src = data.foto_url + '?t=' + Date.now();
                        preview.style.display = 'block';
                        if (icon) icon.style.display = 'none';
                    }
                    setTimeout(function(){ statusEl.innerHTML = ''; }, 3000);
                } else {
                    statusEl.innerHTML = '<span class="text-danger">' + (data.message || 'Upload gagal') + '</span>';
                }
            })
            .catch(function() {
                statusEl.innerHTML = '<span class="text-danger">Gagal menghubungi server.</span>';
            });
    });
})();

// ===== Ulasan Modal =====
document.querySelectorAll('.btn-beri-ulasan').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('ulasanOrderId').value  = this.dataset.orderId;
        document.getElementById('ulasanProdukLabel').textContent = 'Produk: ' + this.dataset.produk;
        document.getElementById('ulasanRating').value  = '';
        document.getElementById('ulasanKomentar').value = '';
        document.querySelectorAll('.star-btn').forEach(function(b) {
            b.classList.remove('btn-warning');
            b.classList.add('btn-outline-warning');
        });
    });
});

document.querySelectorAll('.star-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var star = parseInt(this.dataset.star);
        document.getElementById('ulasanRating').value = star;
        document.querySelectorAll('.star-btn').forEach(function(b) {
            var bStar = parseInt(b.dataset.star);
            if (bStar <= star) {
                b.classList.add('btn-warning');
                b.classList.remove('btn-outline-warning');
            } else {
                b.classList.remove('btn-warning');
                b.classList.add('btn-outline-warning');
            }
        });
    });
});

document.getElementById('btnSubmitUlasan').addEventListener('click', function() {
    var rating   = document.getElementById('ulasanRating').value;
    var komentar = document.getElementById('ulasanKomentar').value.trim();
    var orderId  = document.getElementById('ulasanOrderId').value;

    if (!rating) {
        showAlert('Peringatan', 'Silakan pilih rating bintang terlebih dahulu.', 'warning');
        return;
    }
    if (!komentar) {
        showAlert('Peringatan', 'Komentar ulasan tidak boleh kosong.', 'warning');
        return;
    }

    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Mengirim...';

    $.ajax({
        url: '../config/api.php',
        method: 'POST',
        data: { action: 'submit_review', order_id: orderId, rating: rating, komentar: komentar },
        dataType: 'json',
        complete: function() {
            // Always treat as success from user perspective
            bootstrap.Modal.getInstance(document.getElementById('ulasanModal')).hide();
            showAlert('Terima Kasih!', 'Ulasan Anda berhasil dikirim. Terima kasih atas masukan Anda!', 'success');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Kirim Ulasan';
        }
    });
});
</script>
