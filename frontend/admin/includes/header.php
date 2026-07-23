<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Validate admin role
require_once __DIR__ . '/../../includes/cek_admin.php';
require_once __DIR__ . '/../../config/api.php';

// Fetch admin notifications
$adminNotifApi = apiRequest('GET', '/admin/notifications');
$adminNotifs   = [];
$adminUnread   = 0;
if ($adminNotifApi['success'] && isset($adminNotifApi['data']) && is_array($adminNotifApi['data'])) {
    $adminNotifs = array_slice($adminNotifApi['data'], 0, 8);
    foreach ($adminNotifApi['data'] as $n) {
        if (($n['status_baca'] ?? 'sudah') === 'belum') $adminUnread++;
    }
} else {
    // Mock fallback notifikasi admin
    $adminNotifs = [
        ['id' => 8, 'judul' => 'Produk Dimasukkan ke Keranjang', 'isi' => 'Riko Pratama menambahkan Samsung 55" Neo QLED 4K ke keranjang', 'tipe' => 'keranjang', 'status_baca' => 'belum', 'tanggal' => '2026-06-27 14:30:00'],
        ['id' => 6, 'judul' => 'Chat Baru dari Pelanggan', 'isi' => 'Budi Santoso: "Halo, apakah produk ROG Strix masih tersedia?"', 'tipe' => 'chat', 'status_baca' => 'belum', 'tanggal' => '2026-06-27 08:15:00'],
        ['id' => 5, 'judul' => 'Pembayaran Baru Diterima', 'isi' => 'Andi Wijaya melakukan pembayaran via Transfer Bank Mandiri sebesar Rp 12.999.000', 'tipe' => 'pembayaran', 'status_baca' => 'belum', 'tanggal' => '2026-06-26 17:00:00'],
        ['id' => 4, 'judul' => 'Pesanan Baru Masuk', 'isi' => 'Andi Wijaya membuat pesanan #ORD-20260626-0004 - Samsung Neo QLED senilai Rp 12.999.000', 'tipe' => 'pesanan', 'status_baca' => 'belum', 'tanggal' => '2026-06-26 16:45:00'],
        ['id' => 3, 'judul' => 'Pembayaran Baru Diterima', 'isi' => 'Dewi Lestari melakukan pembayaran via GoPay sebesar Rp 7.599.000', 'tipe' => 'pembayaran', 'status_baca' => 'sudah', 'tanggal' => '2026-06-25 10:00:00'],
        ['id' => 2, 'judul' => 'Pesanan Baru Masuk', 'isi' => 'Dewi Lestari membuat pesanan #ORD-20260625-0003 - Apple Watch Series 9', 'tipe' => 'pesanan', 'status_baca' => 'sudah', 'tanggal' => '2026-06-25 09:30:00'],
    ];
    $adminUnread = 4; // 4 belum dibaca dari mock
}

$tipeAdminConfig = [
    'pesanan'    => ['icon' => 'fa-receipt',     'color' => '#1d4ed8', 'bg' => '#dbeafe'],
    'pembayaran' => ['icon' => 'fa-money-bill',  'color' => '#065f46', 'bg' => '#d1fae5'],
    'chat'       => ['icon' => 'fa-comment',     'color' => '#7c3aed', 'bg' => '#ede9fe'],
    'keranjang'  => ['icon' => 'fa-cart-shopping','color' => '#b45309', 'bg' => '#fef9c3'],
    'info'       => ['icon' => 'fa-circle-info', 'color' => '#1e40af', 'bg' => '#e0e7ff'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - SmartRetail</title>
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE 3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- SweetAlert 2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmAdminLogout() {
        Swal.fire({
            title: 'Yakin logout?',
            text: "Anda akan keluar dari panel admin.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Keluar!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../pages/login.php?logout=1';
            }
        });
    }
    </script>
    <style>
        /* Admin Notification Dropdown */
        .admin-notif-dropdown {
            width: 380px;
            max-height: 480px;
            overflow-y: auto;
            padding: 0;
            border: none !important;
        }
        .admin-notif-header {
            background: linear-gradient(135deg, #1a2332, #2d3748);
            color: white;
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .admin-notif-item {
            padding: 11px 16px;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }
        .admin-notif-item:hover { background: #f8fafc; color: inherit; }
        .admin-notif-item.unread { background: #f0fdf4; border-left: 3px solid #22c55e; }
        .admin-notif-item.unread:hover { background: #dcfce7; }
        .admin-notif-icon {
            width: 38px; height: 38px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .admin-notif-title { font-size: 0.8rem; font-weight: 600; color: #1e293b; margin-bottom: 2px; }
        .admin-notif-body  { font-size: 0.73rem; color: #64748b; line-height: 1.4; }
        .admin-notif-time  { font-size: 0.68rem; color: #94a3b8; white-space: nowrap; flex-shrink: 0; }
        .admin-notif-footer {
            text-align: center; padding: 10px;
            font-size: 0.8rem; color: #3b82f6; font-weight: 600;
            text-decoration: none; display: block;
            border-top: 1px solid #e2e8f0;
        }
        .admin-notif-footer:hover { background: #f0f9ff; color: #1d4ed8; }

        /* Animated badge */
        @keyframes pulse {
            0%,100%{transform:scale(1)}
            50%{transform:scale(1.2)}
        }
        .notif-pulse { animation: pulse 2s infinite; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="../pages/home.php" class="nav-link"><i class="fa-solid fa-house me-1"></i> Ke Halaman Toko</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ms-auto">
      
      <!-- ============================================ -->
      <!-- NOTIFICATION BELL FOR ADMIN -->
      <!-- ============================================ -->
      <li class="nav-item dropdown position-relative mr-2">
        <a class="nav-link" href="#" data-toggle="dropdown" aria-expanded="false" title="Notifikasi Admin">
          <i class="fas fa-bell <?= $adminUnread > 0 ? 'text-warning' : '' ?>"></i>
          <?php if ($adminUnread > 0): ?>
            <span class="badge badge-danger navbar-badge notif-pulse" style="font-size:0.65rem;"><?= $adminUnread ?></span>
          <?php endif; ?>
        </a>
        <div class="dropdown-menu dropdown-menu-right admin-notif-dropdown shadow-lg p-0">
          <!-- Header -->
          <div class="admin-notif-header">
            <span><i class="fas fa-bell mr-2"></i>Notifikasi Admin</span>
            <div class="d-flex align-items-center gap-2">
              <?php if ($adminUnread > 0): ?>
                <span class="badge badge-light" style="font-size:0.7rem;"><?= $adminUnread ?> Baru</span>
              <?php endif; ?>
              <a href="notifikasi.php" class="text-white text-decoration-none" style="font-size:0.72rem;">
                Kelola <i class="fas fa-arrow-right ml-1"></i>
              </a>
            </div>
          </div>

          <!-- Notif Type Summary Bar -->
          <?php
          $typeCounts = ['pesanan' => 0, 'pembayaran' => 0, 'chat' => 0, 'keranjang' => 0];
          foreach ($adminNotifs as $n) {
              $t = $n['tipe'] ?? 'info';
              if (isset($typeCounts[$t]) && ($n['status_baca'] ?? 'sudah') === 'belum') {
                  $typeCounts[$t]++;
              }
          }
          ?>
          <div class="d-flex border-bottom" style="background:#f8fafc;">
            <?php foreach (['pesanan' => ['icon'=>'fa-receipt','label'=>'Pesanan'], 'pembayaran' => ['icon'=>'fa-money-bill','label'=>'Bayar'], 'chat' => ['icon'=>'fa-comment','label'=>'Chat'], 'keranjang' => ['icon'=>'fa-cart-shopping','label'=>'Keranjang']] as $key => $cfg): ?>
            <div class="flex-fill text-center py-2 px-1 border-end" style="font-size:0.7rem;">
                <i class="fa-solid <?= $cfg['icon'] ?> d-block mb-1" style="color:<?= $tipeAdminConfig[$key]['color'] ?>;font-size:1rem;"></i>
                <?= $cfg['label'] ?>
                <?php if ($typeCounts[$key] > 0): ?>
                    <span class="badge badge-danger ml-1" style="font-size:0.6rem;"><?= $typeCounts[$key] ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- Notif Items -->
          <div>
            <?php if (empty($adminNotifs)): ?>
              <div class="text-center text-muted py-4">
                <i class="fas fa-check-circle d-block mb-2" style="font-size:2rem;"></i>
                <small>Semua notifikasi sudah ditangani</small>
              </div>
            <?php else: ?>
              <?php foreach ($adminNotifs as $notif):
                $tipe    = $notif['tipe'] ?? 'info';
                $cfg     = $tipeAdminConfig[$tipe] ?? $tipeAdminConfig['info'];
                $isUnread= ($notif['status_baca'] ?? 'sudah') === 'belum';
                $waktu   = date('d/m H:i', strtotime($notif['tanggal'] ?? 'now'));
              ?>
              <a href="notifikasi.php" class="admin-notif-item <?= $isUnread ? 'unread' : '' ?>">
                <div class="admin-notif-icon" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;">
                  <i class="fa-solid <?= $cfg['icon'] ?>" style="font-size:0.85rem;"></i>
                </div>
                <div class="flex-grow-1" style="min-width:0;">
                  <div class="admin-notif-title"><?= htmlspecialchars($notif['judul']) ?></div>
                  <div class="admin-notif-body" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($notif['isi']) ?></div>
                  <div class="admin-notif-time mt-1"><?= $waktu ?></div>
                </div>
                <?php if ($isUnread): ?>
                  <span style="width:8px;height:8px;background:#22c55e;border-radius:50%;flex-shrink:0;margin-top:4px;"></span>
                <?php endif; ?>
              </a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <a href="notifikasi.php" class="admin-notif-footer">
            <i class="fas fa-list mr-1"></i> Lihat Semua Notifikasi
          </a>
        </div>
      </li>

      <!-- Admin Name -->
      <li class="nav-item">
        <span class="nav-link text-dark font-weight-bold">
            <i class="fa-solid fa-user-tie mr-1"></i> <?= htmlspecialchars($_SESSION['user']['nama_pembeli'] ?? 'Admin') ?>
        </span>
      </li>
      <!-- Logout -->
      <li class="nav-item">
        <a class="nav-link text-danger" href="javascript:void(0);" onclick="confirmAdminLogout()" role="button" title="Logout">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->
