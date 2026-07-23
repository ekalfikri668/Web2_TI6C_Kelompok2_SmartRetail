<?php
$currentPage = basename($_SERVER['PHP_SELF']);

// Get unread admin notification count for sidebar badge
if (session_status() === PHP_SESSION_NONE) session_start();
$sidebarNotifCount = $adminUnread ?? 0; // Overridden by actual API data in header.php which runs first
?>
<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="dashboard.php" class="brand-link">
    <i class="fa-solid fa-laptop text-primary mx-3"></i>
    <span class="brand-text font-weight-light">Smart<strong>Retail</strong> Admin</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar Menu -->
    <nav class="mt-3">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <!-- Dashboard -->
        <li class="nav-item">
          <a href="dashboard.php" class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>
        
        <!-- Divider: Produk -->
        <li class="nav-header text-xs font-weight-bold px-3 mt-2" style="font-size:0.7rem;letter-spacing:0.05em;color:rgba(255,255,255,0.4);">PRODUK</li>

        <!-- Produk -->
        <li class="nav-item">
          <a href="produk.php" class="nav-link <?= $currentPage === 'produk.php' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-box"></i>
            <p>Produk</p>
          </a>
        </li>

        <!-- Kategori -->
        <li class="nav-item">
          <a href="kategori.php" class="nav-link <?= $currentPage === 'kategori.php' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-list"></i>
            <p>Kategori</p>
          </a>
        </li>

        <!-- Brand -->
        <li class="nav-item">
          <a href="brand.php" class="nav-link <?= $currentPage === 'brand.php' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-tags"></i>
            <p>Brand</p>
          </a>
        </li>

        <!-- Divider: Transaksi -->
        <li class="nav-header text-xs font-weight-bold px-3 mt-2" style="font-size:0.7rem;letter-spacing:0.05em;color:rgba(255,255,255,0.4);">TRANSAKSI</li>

        <!-- Pesanan -->
        <li class="nav-item">
          <a href="pesanan.php" class="nav-link <?= $currentPage === 'pesanan.php' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-receipt"></i>
            <p>Pesanan</p>
          </a>
        </li>

        <!-- Pembayaran -->
        <li class="nav-item">
          <a href="pembayaran.php" class="nav-link <?= $currentPage === 'pembayaran.php' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-wallet"></i>
            <p>Pembayaran</p>
          </a>
        </li>

        <!-- Pengiriman -->
        <li class="nav-item">
          <a href="pengiriman.php" class="nav-link <?= $currentPage === 'pengiriman.php' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-truck"></i>
            <p>Pengiriman</p>
          </a>
        </li>

        <!-- Divider: Pengguna -->
        <li class="nav-header text-xs font-weight-bold px-3 mt-2" style="font-size:0.7rem;letter-spacing:0.05em;color:rgba(255,255,255,0.4);">PENGGUNA & KOMUNIKASI</li>

        <!-- Kelola Pelanggan -->
        <li class="nav-item">
          <a href="pelanggan.php" class="nav-link <?= $currentPage === 'pelanggan.php' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-users"></i>
            <p>Kelola Pelanggan</p>
          </a>
        </li>

        <!-- Chat -->
        <li class="nav-item">
          <a href="chat.php" class="nav-link <?= $currentPage === 'chat.php' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-comments"></i>
            <p>Pusat Chat
              <span class="badge badge-info right" style="font-size:0.65rem;">Live</span>
            </p>
          </a>
        </li>

        <!-- Review -->
        <li class="nav-item">
          <a href="review.php" class="nav-link <?= $currentPage === 'review.php' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-star"></i>
            <p>Review Produk</p>
          </a>
        </li>

        <!-- Notifikasi Admin - BARU -->
        <li class="nav-item">
          <a href="notifikasi.php" class="nav-link <?= $currentPage === 'notifikasi.php' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-bell"></i>
            <p>Notifikasi
              <span class="badge badge-warning right notif-sidebar-count" style="font-size:0.65rem;"><?= $sidebarNotifCount ?></span>
            </p>
          </a>
        </li>

        <!-- Divider: Laporan -->
        <li class="nav-header text-xs font-weight-bold px-3 mt-2" style="font-size:0.7rem;letter-spacing:0.05em;color:rgba(255,255,255,0.4);">LAPORAN</li>

        <!-- Laporan -->
        <li class="nav-item">
          <a href="laporan.php" class="nav-link <?= $currentPage === 'laporan.php' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-chart-line"></i>
            <p>Laporan</p>
          </a>
        </li>

      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
