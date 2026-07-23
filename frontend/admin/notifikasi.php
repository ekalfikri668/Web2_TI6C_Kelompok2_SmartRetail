<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Fetch admin notifications from API
$notifApi = apiRequest('GET', '/admin/notifications');
$allNotifs = [];
if ($notifApi['success'] && isset($notifApi['data']) && is_array($notifApi['data'])) {
    $allNotifs = $notifApi['data'];
} else {
    // Mock fallback data yang realistis
    $allNotifs = [
        ['id' => 8, 'judul' => 'Produk Dimasukkan ke Keranjang', 'isi' => 'Riko Pratama menambahkan Samsung 55" Neo QLED 4K ke keranjang belanja', 'tipe' => 'keranjang', 'id_referensi' => 5, 'status_baca' => 'belum', 'tanggal' => '2026-06-27 14:30:00'],
        ['id' => 6, 'judul' => 'Chat Baru dari Pelanggan', 'isi' => 'Budi Santoso: "Halo, apakah produk ROG Strix masih tersedia? Saya tertarik membelinya hari ini."', 'tipe' => 'chat', 'id_referensi' => 1, 'status_baca' => 'belum', 'tanggal' => '2026-06-27 08:15:00'],
        ['id' => 5, 'judul' => 'Pembayaran Baru Diterima', 'isi' => 'Andi Wijaya melakukan pembayaran untuk pesanan #ORD-20260626-0004 via Transfer Bank Mandiri sebesar Rp 12.999.000', 'tipe' => 'pembayaran', 'id_referensi' => 3, 'status_baca' => 'belum', 'tanggal' => '2026-06-26 17:00:00'],
        ['id' => 4, 'judul' => 'Pesanan Baru Masuk', 'isi' => 'Andi Wijaya membuat pesanan baru #ORD-20260626-0004 - Samsung 55" Neo QLED 4K senilai Rp 12.999.000', 'tipe' => 'pesanan', 'id_referensi' => 4, 'status_baca' => 'belum', 'tanggal' => '2026-06-26 16:45:00'],
        ['id' => 3, 'judul' => 'Pembayaran Baru Diterima', 'isi' => 'Dewi Lestari melakukan pembayaran untuk pesanan #ORD-20260625-0003 via GoPay sebesar Rp 7.599.000', 'tipe' => 'pembayaran', 'id_referensi' => 2, 'status_baca' => 'sudah', 'tanggal' => '2026-06-25 10:00:00'],
        ['id' => 2, 'judul' => 'Pesanan Baru Masuk', 'isi' => 'Dewi Lestari membuat pesanan baru #ORD-20260625-0003 - Apple Watch Series 9 GPS senilai Rp 7.499.000', 'tipe' => 'pesanan', 'id_referensi' => 3, 'status_baca' => 'sudah', 'tanggal' => '2026-06-25 09:30:00'],
        ['id' => 1, 'judul' => 'Pembayaran Baru Diterima', 'isi' => 'Budi Santoso melakukan pembayaran untuk pesanan #ORD-20260620-0002 via Transfer Bank BCA sebesar Rp 1.743.000', 'tipe' => 'pembayaran', 'id_referensi' => 1, 'status_baca' => 'sudah', 'tanggal' => '2026-06-20 15:00:00'],
        ['id' => 7, 'judul' => 'Pesanan Baru Masuk', 'isi' => 'Budi Santoso membuat pesanan baru #ORD-20260624-0001 - ASUS ROG Strix G16 senilai Rp 24.999.000', 'tipe' => 'pesanan', 'id_referensi' => 1, 'status_baca' => 'sudah', 'tanggal' => '2026-06-24 10:15:00'],
    ];
}

$filterTipe  = $_GET['tipe'] ?? 'semua';
$filteredNotifs = $filterTipe === 'semua' ? $allNotifs : array_filter($allNotifs, fn($n) => ($n['tipe'] ?? '') === $filterTipe);
$unreadCount = count(array_filter($allNotifs, fn($n) => ($n['status_baca'] ?? 'sudah') === 'belum'));

$tipeConfig = [
    'pesanan'    => ['icon' => 'fas fa-receipt',      'color' => 'primary', 'hex' => '#1d4ed8', 'bg' => '#dbeafe', 'label' => 'Pesanan Baru'],
    'pembayaran' => ['icon' => 'fas fa-money-bill-wave','color' => 'success', 'hex' => '#065f46', 'bg' => '#d1fae5', 'label' => 'Pembayaran'],
    'chat'       => ['icon' => 'fas fa-comment-dots', 'color' => 'purple',  'hex' => '#6d28d9', 'bg' => '#ede9fe', 'label' => 'Chat'],
    'keranjang'  => ['icon' => 'fas fa-shopping-cart','color' => 'warning', 'hex' => '#92400e', 'bg' => '#fef9c3', 'label' => 'Keranjang'],
    'info'       => ['icon' => 'fas fa-info-circle',  'color' => 'info',    'hex' => '#1e40af', 'bg' => '#e0e7ff', 'label' => 'Info'],
];

// Count by type
$typeSummary = [];
foreach ($allNotifs as $n) {
    $t = $n['tipe'] ?? 'info';
    if (!isset($typeSummary[$t])) $typeSummary[$t] = ['total' => 0, 'unread' => 0];
    $typeSummary[$t]['total']++;
    if (($n['status_baca'] ?? 'sudah') === 'belum') $typeSummary[$t]['unread']++;
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-bell mr-2 text-warning"></i>Notifikasi Admin</h1>
        </div>
        <div class="col-sm-6 text-sm-right">
          <span class="text-muted small">Dashboard / Notifikasi</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">

      <!-- Summary Cards Row -->
      <div class="row mb-4">
        <?php
        $summaryItems = [
            ['key' => 'pesanan',    'label' => 'Pesanan Baru',   'icon' => 'fa-receipt',       'color' => 'primary'],
            ['key' => 'pembayaran', 'label' => 'Pembayaran',     'icon' => 'fa-money-bill-wave','color' => 'success'],
            ['key' => 'chat',       'label' => 'Chat Masuk',     'icon' => 'fa-comment-dots',  'color' => 'purple'],
            ['key' => 'keranjang',  'label' => 'Keranjang',      'icon' => 'fa-shopping-cart', 'color' => 'warning'],
        ];
        foreach ($summaryItems as $item):
            $unread = $typeSummary[$item['key']]['unread'] ?? 0;
            $total  = $typeSummary[$item['key']]['total']  ?? 0;
        ?>
        <div class="col-6 col-md-3">
          <div class="small-box bg-<?= $item['color'] === 'purple' ? 'indigo' : $item['color'] ?>" style="<?= $item['color'] === 'purple' ? 'background:#7c3aed !important;' : '' ?>">
            <div class="inner">
              <h3><?= $unread ?> <sup style="font-size:0.5em;">Baru</sup></h3>
              <p><?= $item['label'] ?></p>
            </div>
            <div class="icon"><i class="fas <?= $item['icon'] ?>"></i></div>
            <a href="?tipe=<?= $item['key'] ?>" class="small-box-footer">
              Lihat semua (<?= $total ?>) <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Filter & Actions Row -->
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="d-flex flex-wrap gap-2">
          <a href="?tipe=semua" class="btn btn-sm <?= $filterTipe === 'semua' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">
            Semua <span class="badge badge-light ml-1"><?= count($allNotifs) ?></span>
          </a>
          <?php foreach ($tipeConfig as $key => $cfg):
            if (!isset($typeSummary[$key])) continue;
          ?>
          <a href="?tipe=<?= $key ?>" class="btn btn-sm <?= $filterTipe === $key ? 'btn-'.$cfg['color'] : 'btn-outline-secondary' ?> rounded-pill px-3">
            <i class="<?= $cfg['icon'] ?> mr-1"></i><?= $cfg['label'] ?>
            <span class="badge badge-light ml-1"><?= $typeSummary[$key]['total'] ?></span>
          </a>
          <?php endforeach; ?>
        </div>
        <?php if ($unreadCount > 0): ?>
        <button class="btn btn-sm btn-outline-success rounded-pill px-3" id="markAllReadAdminBtn">
          <i class="fas fa-check-double mr-1"></i>Tandai Semua Dibaca (<?= $unreadCount ?>)
        </button>
        <?php endif; ?>
      </div>

      <!-- Notifications Table -->
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title font-weight-bold">
            <i class="fas fa-list mr-1"></i> 
            <?= $filterTipe === 'semua' ? 'Semua Notifikasi' : 'Notifikasi: ' . ($tipeConfig[$filterTipe]['label'] ?? $filterTipe) ?>
          </h3>
          <?php if ($unreadCount > 0): ?>
          <div class="card-tools">
            <span class="badge badge-danger"><?= $unreadCount ?> Belum Dibaca</span>
          </div>
          <?php endif; ?>
        </div>
        <div class="card-body p-0">
          <?php if (empty($filteredNotifs)): ?>
            <div class="text-center py-5 text-muted">
              <i class="fas fa-check-circle d-block mb-3" style="font-size:3rem;color:#d1d5db;"></i>
              <h5>Tidak ada notifikasi</h5>
              <p class="small">Tidak ada notifikasi untuk kategori ini.</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover m-0">
                <thead class="thead-light">
                  <tr>
                    <th style="width:40px;">#</th>
                    <th style="width:120px;">Tipe</th>
                    <th>Judul & Detail</th>
                    <th style="width:140px;">Waktu</th>
                    <th style="width:100px;" class="text-center">Status</th>
                    <th style="width:80px;" class="text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($filteredNotifs as $notif):
                    $notifId = $notif['id'] ?? $notif['id_notifikasi'] ?? 0;
                    $tipe    = $notif['tipe'] ?? 'info';
                    $cfg     = $tipeConfig[$tipe] ?? $tipeConfig['info'];
                    $isUnread= ($notif['status_baca'] ?? 'sudah') === 'belum';
                    $waktu   = date('d M Y, H:i', strtotime($notif['tanggal'] ?? 'now'));
                  ?>
                  <tr id="admin-notif-row-<?= $notifId ?>" class="<?= $isUnread ? 'table-active font-weight-bold' : '' ?>">
                    <td class="text-muted small">#<?= $notifId ?></td>
                    <td>
                      <span class="badge rounded-pill px-2 py-1" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['hex'] ?>;font-size:0.72rem;">
                        <i class="<?= $cfg['icon'] ?> mr-1"></i><?= $cfg['label'] ?>
                      </span>
                    </td>
                    <td>
                      <div class="font-weight-<?= $isUnread ? 'bold' : 'normal' ?>" style="font-size:0.9rem;">
                        <?= htmlspecialchars($notif['judul']) ?>
                        <?php if ($isUnread): ?>
                          <span class="badge badge-danger ml-1" style="font-size:0.6rem;">Baru</span>
                        <?php endif; ?>
                      </div>
                      <div class="text-muted small mt-1"><?= htmlspecialchars($notif['isi']) ?></div>
                      <!-- Quick action links based on type -->
                      <div class="mt-1">
                        <?php if ($tipe === 'pesanan' || $tipe === 'pembayaran'): ?>
                          <a href="pesanan.php" class="badge badge-light border mr-1" style="font-size:0.68rem;">
                            <i class="fas fa-receipt mr-1"></i>Lihat Pesanan
                          </a>
                          <a href="pembayaran.php" class="badge badge-light border" style="font-size:0.68rem;">
                            <i class="fas fa-wallet mr-1"></i>Konfirmasi Bayar
                          </a>
                        <?php elseif ($tipe === 'chat'): ?>
                          <a href="chat.php" class="badge badge-light border" style="font-size:0.68rem;">
                            <i class="fas fa-reply mr-1"></i>Balas Chat
                          </a>
                        <?php elseif ($tipe === 'keranjang'): ?>
                          <a href="pelanggan.php" class="badge badge-light border" style="font-size:0.68rem;">
                            <i class="fas fa-user mr-1"></i>Lihat Pelanggan
                          </a>
                        <?php endif; ?>
                      </div>
                    </td>
                    <td>
                      <small class="text-muted"><?= $waktu ?></small>
                    </td>
                    <td class="text-center">
                      <?php if ($isUnread): ?>
                        <span class="badge badge-warning">Belum Dibaca</span>
                      <?php else: ?>
                        <span class="badge badge-secondary">Dibaca</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <?php if ($isUnread): ?>
                        <button class="btn btn-xs btn-success mark-read-admin-btn" data-id="<?= $notifId ?>" title="Tandai Dibaca">
                          <i class="fas fa-check"></i>
                        </button>
                      <?php else: ?>
                        <span class="text-muted small">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
        <div class="card-footer text-muted small">
          Total: <?= count($filteredNotifs) ?> notifikasi | <?= $unreadCount ?> belum dibaca
        </div>
      </div>

    </div>
  </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark single notif as read
    document.querySelectorAll('.mark-read-admin-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id   = this.dataset.id;
            const row  = document.getElementById('admin-notif-row-' + id);
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            // Try API via admin helper, fallback to UI update
            fetch('helper_notif_admin.php?action=read&id=' + id)
                .finally(() => {
                    if (row) {
                        row.classList.remove('table-active', 'font-weight-bold');
                        const badgeNew = row.querySelector('.badge-danger');
                        if (badgeNew) badgeNew.remove();
                        const statusBadge = row.querySelector('.badge-warning');
                        if (statusBadge) {
                            statusBadge.className = 'badge badge-secondary';
                            statusBadge.textContent = 'Dibaca';
                        }
                    }
                    this.closest('td').innerHTML = '<span class="text-muted small">—</span>';
                    
                    // Update unread count badge
                    updateUnreadCounts();
                });
        });
    });

    // Mark ALL as read
    const markAllBtn = document.getElementById('markAllReadAdminBtn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            if (!confirm('Tandai semua notifikasi sebagai sudah dibaca?')) return;
            fetch('helper_notif_admin.php?action=read_all').finally(() => {
                document.querySelectorAll('tr.table-active').forEach(row => {
                    row.classList.remove('table-active', 'font-weight-bold');
                    const badgeNew = row.querySelector('.badge-danger');
                    if (badgeNew) badgeNew.remove();
                    const statusBadge = row.querySelector('.badge-warning');
                    if (statusBadge) {
                        statusBadge.className = 'badge badge-secondary';
                        statusBadge.textContent = 'Dibaca';
                    }
                });
                document.querySelectorAll('.mark-read-admin-btn').forEach(btn => {
                    btn.closest('td').innerHTML = '<span class="text-muted small">—</span>';
                });
                markAllBtn.remove();
            });
        });
    }

    function updateUnreadCounts() {
        const remaining = document.querySelectorAll('.mark-read-admin-btn').length;
        const badge = document.querySelector('.badge.badge-danger');
        if (badge) badge.textContent = remaining + ' Belum Dibaca';
        const cardBadge = document.querySelector('.card-tools .badge-danger');
        if (cardBadge) {
            if (remaining === 0) cardBadge.remove();
            else cardBadge.textContent = remaining + ' Belum Dibaca';
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
