<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/api.php';

// Fetch categories from API dynamically
$categoryApi = apiRequest('GET', '/categories');
$navbarCategories = [];
if ($categoryApi['success'] && isset($categoryApi['data']) && is_array($categoryApi['data']) && !empty($categoryApi['data'])) {
    foreach ($categoryApi['data'] as $cat) {
        if (is_string($cat)) {
            $name = trim($cat);
            if (!empty($name)) {
                $navbarCategories[] = ['id' => $name, 'nama_kategori' => $name];
            }
        } elseif (is_array($cat)) {
            $name = $cat['nama_kategori'] ?? $cat['nama'] ?? $cat['kategori'] ?? $cat['name'] ?? $cat['category_name'] ?? '';
            $id   = $cat['id_kategori'] ?? $cat['id'] ?? $name;
            if (!empty($name)) {
                $navbarCategories[] = ['id' => $id, 'nama_kategori' => $name];
            }
        }
    }
}

if (empty($navbarCategories)) {
    $navbarCategories = [
        ['id' => 1, 'nama_kategori' => 'Laptop'],
        ['id' => 2, 'nama_kategori' => 'Smartwatch'],
        ['id' => 3, 'nama_kategori' => 'CCTV'],
        ['id' => 4, 'nama_kategori' => 'Mouse'],
        ['id' => 5, 'nama_kategori' => 'Smart TV']
    ];
}

// Fetch Cart Count if logged in
$cartCount = 0;
if (isset($_SESSION['token'])) {
    $cartApi = apiRequest('GET', '/cart');
    if ($cartApi['success'] && isset($cartApi['data'])) {
        // API returns a cart object with an 'items' array, NOT a flat array of carts
        if (is_array($cartApi['data']) && isset($cartApi['data']['items'])) {
            $cartCount = count($cartApi['data']['items']);
        } elseif (is_array($cartApi['data']) && !isset($cartApi['data']['items'])) {
            // Fallback if API returns flat array
            $cartCount = count($cartApi['data']);
        }
    }
}

// Fetch Notifications Count if logged in
$notifCount = 0;
$notifList  = [];
if (isset($_SESSION['token'])) {
    $notifApi = apiRequest('GET', '/notifications');
    if ($notifApi['success'] && isset($notifApi['data']) && is_array($notifApi['data'])) {
        $notifList  = array_slice($notifApi['data'], 0, 5);
        foreach ($notifApi['data'] as $n) {
            if (($n['status_baca'] ?? 'sudah') === 'belum') $notifCount++;
        }
    } else {
        // Mock fallback notifikasi pengguna
        $notifList = [
            ['id' => 1, 'judul' => 'Pesanan Berhasil Dibuat', 'isi' => 'Pesanan #ORD-20260624-0001 telah dibuat. Silakan lakukan pembayaran.', 'tipe' => 'pesanan', 'status_baca' => 'belum', 'tanggal' => '2026-06-24 10:16:00'],
            ['id' => 2, 'judul' => 'Pesanan Selesai', 'isi' => 'Pesanan #ORD-20260620-0002 telah selesai. Terima kasih!', 'tipe' => 'pesanan', 'status_baca' => 'sudah', 'tanggal' => '2026-06-22 18:00:00'],
        ];
        $notifCount = 1; // 1 belum dibaca
    }
}

// Helper to determine root paths
$is_in_pages   = (strpos($_SERVER['REQUEST_URI'], '/pages/') !== false);
$path_prefix   = $is_in_pages ? '' : 'pages/';
$home_path     = $is_in_pages ? 'home.php' : 'pages/home.php';
$produk_path   = $is_in_pages ? 'produk.php' : 'pages/produk.php';
$keranjang_path= $is_in_pages ? 'keranjang.php' : 'pages/keranjang.php';
$chat_path     = $is_in_pages ? 'chat.php' : 'pages/chat.php';
$profil_path   = $is_in_pages ? 'profil.php' : 'pages/profil.php';
$pesanan_path  = $is_in_pages ? 'profil.php?tab=histori' : 'pages/profil.php?tab=histori';
$notif_path    = $is_in_pages ? 'notifikasi.php' : 'pages/notifikasi.php';
$login_path    = $is_in_pages ? 'login.php' : 'pages/login.php';
$register_path = $is_in_pages ? 'register.php' : 'pages/register.php';
$css_path      = $is_in_pages ? '../assets/' : 'assets/';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartRetail - Premium Technology Marketplace</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- jQuery (loaded early so inline scripts can use $ ) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- Bootstrap 5 Bundle JS (early load for modal support) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS (early load so showAlert works in inline scripts) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Global showAlert utility (available to ALL inline page scripts) -->
    <script>
    window.showAlert = function(title, message, icon, callback) {
        icon = icon || 'info';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: message,
                icon: icon,
                confirmButtonColor: '#2563eb'
            }).then(function() {
                if (typeof callback === 'function') callback();
            });
        } else {
            alert(title + ': ' + message);
            if (typeof callback === 'function') callback();
        }
    };
    window.formatRupiah = function(value) {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    };
    </script>
    <!-- Custom Style -->
    <link href="<?= $css_path ?>css/style.css?v=<?= time() ?>" rel="stylesheet">
    <style>
        /* Notification dropdown styles */
        .notif-dropdown-menu {
            width: 360px;
            max-height: 450px;
            overflow-y: auto;
            padding: 0;
        }
        .notif-dropdown-menu .notif-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 14px 16px;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .notif-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: inherit;
        }
        .notif-item:hover { background: #f8fafc; color: inherit; }
        .notif-item.unread { background: #eff6ff; border-left: 3px solid #3b82f6; }
        .notif-item.unread:hover { background: #dbeafe; }
        .notif-icon-badge {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .notif-icon-badge.pesanan   { background: #dbeafe; color: #1d4ed8; }
        .notif-icon-badge.pengiriman{ background: #d1fae5; color: #065f46; }
        .notif-icon-badge.pembayaran{ background: #fef9c3; color: #92400e; }
        .notif-icon-badge.promo     { background: #fce7f3; color: #9d174d; }
        .notif-item-title {
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 2px;
            color: #1e293b;
        }
        .notif-item-body {
            font-size: 0.76rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .notif-item-time {
            font-size: 0.7rem;
            color: #94a3b8;
            white-space: nowrap;
        }
        .notif-footer-link {
            display: block;
            text-align: center;
            padding: 10px;
            font-size: 0.82rem;
            color: #3b82f6;
            font-weight: 600;
            text-decoration: none;
            border-top: 1px solid #e2e8f0;
        }
        .notif-footer-link:hover { background: #f0f7ff; color: #1d4ed8; }
        .bell-shake {
            animation: bellRing 0.5s ease-in-out;
        }
        @keyframes bellRing {
            0%,100%{transform:rotate(0)}
            20%{transform:rotate(-12deg)}
            40%{transform:rotate(12deg)}
            60%{transform:rotate(-8deg)}
            80%{transform:rotate(8deg)}
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-tech sticky-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="<?= $home_path ?>">
            <i class="fa-solid fa-laptop text-tech-blue me-2"></i>Smart<span>Retail</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Search Bar -->
            <form class="d-flex mx-auto col-lg-5 my-2 my-lg-0" action="<?= $produk_path ?>" method="GET">
                <div class="input-group">
                    <input class="form-control border-end-0 bg-light" type="search" name="search" placeholder="Cari laptop, smartwatch, CCTV..." aria-label="Search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn btn-light border-start-0 border text-secondary" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </form>

            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $home_path ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $produk_path ?>">Produk</a>
                </li>
                
                <!-- Category Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Kategori
                    </a>
                    <ul class="dropdown-menu border-0 shadow-lg mt-2">
                        <li><a class="dropdown-menu-item dropdown-item py-2 font-weight-bold" href="<?= $produk_path ?>">Semua Kategori</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach($navbarCategories as $cat): ?>
                            <li><a class="dropdown-menu-item dropdown-item py-2" href="<?= $produk_path ?>?category=<?= urlencode($cat['nama_kategori']) ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <!-- User Icons & Buttons -->
                <?php if (isset($_SESSION['token'])): ?>
                    <!-- Keranjang -->
                    <li class="nav-item px-1 position-relative">
                        <a class="nav-link" href="<?= $keranjang_path ?>" data-bs-toggle="tooltip" title="Keranjang Belanja">
                            <i class="fa-solid fa-cart-shopping fs-5"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                    <?= $cartCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Notifikasi Bell -->
                    <li class="nav-item px-1 dropdown position-relative" id="notif-nav-item">
                        <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notifBellBtn" data-bs-auto-close="outside">
                            <i class="fa-solid fa-bell fs-5" id="bellIcon"></i>
                            <?php if ($notifCount > 0): ?>
                                <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-danger" id="notif-badge" style="font-size: 0.65rem;">
                                    <?= $notifCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 notif-dropdown-menu p-0">
                            <div class="notif-header">
                                <span><i class="fa-solid fa-bell me-2"></i>Notifikasi</span>
                                <a href="#" class="text-white text-decoration-none small" id="markAllReadBtn" style="font-size:0.75rem;">
                                    <i class="fa-solid fa-check-double me-1"></i>Tandai Semua Dibaca
                                </a>
                            </div>
                            <div id="notif-list-container">
                                <?php if (empty($notifList)): ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fa-regular fa-bell-slash mb-2" style="font-size:2rem;"></i>
                                        <p class="mb-0 small">Tidak ada notifikasi</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($notifList as $notif):
                                        $isUnread = ($notif['status_baca'] ?? 'sudah') === 'belum';
                                        $tipe = $notif['tipe'] ?? 'info';
                                        $icon = match($tipe) {
                                            'pesanan'    => 'fa-receipt',
                                            'pengiriman' => 'fa-truck',
                                            'pembayaran' => 'fa-credit-card',
                                            'promo'      => 'fa-tag',
                                            default      => 'fa-bell'
                                        };
                                        $waktu = date('d M, H:i', strtotime($notif['tanggal'] ?? 'now'));
                                    ?>
                                        <a href="<?= $notif_path ?>" class="notif-item <?= $isUnread ? 'unread' : '' ?> d-flex align-items-start gap-2" data-notif-id="<?= $notif['id'] ?? 0 ?>">
                                            <div class="notif-icon-badge <?= $tipe ?>">
                                                <i class="fa-solid <?= $icon ?>" style="font-size:0.85rem;"></i>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="notif-item-title"><?= htmlspecialchars($notif['judul']) ?></span>
                                                    <span class="notif-item-time ms-2"><?= $waktu ?></span>
                                                </div>
                                                <div class="notif-item-body"><?= htmlspecialchars($notif['isi']) ?></div>
                                            </div>
                                            <?php if ($isUnread): ?>
                                                <span class="flex-shrink-0 mt-1" style="width:8px;height:8px;background:#3b82f6;border-radius:50%;display:inline-block;"></span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <a href="<?= $notif_path ?>" class="notif-footer-link">
                                <i class="fa-solid fa-list me-1"></i> Lihat Semua Notifikasi
                            </a>
                        </div>
                    </li>
                    
                    <!-- Chat -->
                    <li class="nav-item px-1">
                        <a class="nav-link" href="<?= $chat_path ?>" data-bs-toggle="tooltip" title="Chat Admin">
                            <i class="fa-solid fa-comments fs-5"></i>
                        </a>
                    </li>

                    <!-- User Profile Dropdown -->
                    <li class="nav-item dropdown px-1">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-circle-user fs-4 me-2"></i>
                            <span><?= htmlspecialchars($_SESSION['user']['nama_pembeli'] ?? 'User') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2">
                            <li><a class="dropdown-item py-2" href="<?= $profil_path ?>"><i class="fa-solid fa-user me-2 text-primary"></i>Profil Saya</a></li>
                            <li><a class="dropdown-item py-2" href="<?= $pesanan_path ?>"><i class="fa-solid fa-receipt me-2 text-info"></i>Pesanan Saya</a></li>
                            <li><a class="dropdown-item py-2" href="<?= $notif_path ?>"><i class="fa-solid fa-bell me-2 text-warning"></i>Notifikasi
                                <?php if ($notifCount > 0): ?>
                                    <span class="badge bg-danger ms-1" style="font-size:0.65rem;"><?= $notifCount ?></span>
                                <?php endif; ?>
                            </a></li>
                            <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                                <li><a class="dropdown-item py-2" href="<?= (strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? '../admin/dashboard.php' : 'admin/dashboard.php' ?>"><i class="fa-solid fa-user-shield me-2 text-danger"></i>Dashboard Admin</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="javascript:void(0);" onclick="confirmLogout('<?= (strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? '' : 'pages/' ?>')"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-tech-secondary me-2 w-100 mb-2 mb-lg-0" href="<?= $login_path ?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-tech-primary w-100" href="<?= $register_path ?>">Daftar</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (isset($_SESSION['token']) && !empty($_SESSION['token'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmLogout(prefix) {
    Swal.fire({
        title: 'Yakin logout?',
        text: "Anda akan keluar dari sesi saat ini.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Keluar!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Hapus token yang tersimpan di browser client
            localStorage.removeItem('token');
            sessionStorage.removeItem('token');

            // Redirect ke login.php dengan parameter logout
            window.location.href = prefix + 'login.php?logout=1';
        }
    });
}

// Bell icon animation on load if there are unread notifs
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($notifCount > 0): ?>
    const bellIcon = document.getElementById('bellIcon');
    if (bellIcon) {
        bellIcon.classList.add('bell-shake');
        setTimeout(() => bellIcon.classList.remove('bell-shake'), 600);
        setInterval(() => {
            bellIcon.classList.add('bell-shake');
            setTimeout(() => bellIcon.classList.remove('bell-shake'), 600);
        }, 10000);
    }
    <?php endif; ?>

    // Mark all read
    const markAllBtn = document.getElementById('markAllReadBtn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            fetch('<?= $is_in_pages ? "helper_notif.php" : "pages/helper_notif.php" ?>?action=read_all')
                .then(r => r.json())
                .then(data => {
                    document.querySelectorAll('.notif-item.unread').forEach(el => {
                        el.classList.remove('unread');
                        const dot = el.querySelector('span[style*="border-radius:50%"]');
                        if (dot) dot.remove();
                    });
                    const badge = document.getElementById('notif-badge');
                    if (badge) badge.remove();
                }).catch(() => {
                    // offline fallback - still update UI
                    document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
                    const badge = document.getElementById('notif-badge');
                    if (badge) badge.remove();
                });
        });
    }
});
</script>
<?php endif; ?>
