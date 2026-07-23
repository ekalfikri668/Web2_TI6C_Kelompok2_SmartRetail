<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/cek_login.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/navbar.php';

// Fetch notifications from API
$notifApi = apiRequest('GET', '/notifications');
$allNotifs = [];
if (isset($notifApi['success']) && $notifApi['success'] && isset($notifApi['data']) && is_array($notifApi['data'])) {
    $allNotifs = $notifApi['data'];
} else {
    // Mock fallback
    $allNotifs = [
        ['id' => 1, 'judul' => 'Pesanan Berhasil Dibuat', 'isi' => 'Pesanan Anda #ORD-20260624-0001 telah berhasil dibuat. Silakan lakukan pembayaran segera untuk memproses pesanan Anda.', 'tipe' => 'pesanan', 'status_baca' => 'belum', 'tanggal' => '2026-06-24 10:16:00'],
        ['id' => 2, 'judul' => 'Pesanan Selesai', 'isi' => 'Pesanan #ORD-20260620-0002 (Logitech G502 X Plus) telah selesai. Terima kasih telah berbelanja di SmartRetail! Jangan lupa berikan ulasan produk.', 'tipe' => 'pesanan', 'status_baca' => 'sudah', 'tanggal' => '2026-06-22 18:00:00'],
        ['id' => 3, 'judul' => 'Pembayaran Dikonfirmasi', 'isi' => 'Pembayaran untuk pesanan #ORD-20260620-0002 senilai Rp 2.598.000 telah dikonfirmasi. Pesanan sedang diproses.', 'tipe' => 'pembayaran', 'status_baca' => 'sudah', 'tanggal' => '2026-06-20 15:30:00'],
        ['id' => 4, 'judul' => 'Promo Spesial Akhir Bulan!', 'isi' => 'Dapatkan diskon hingga 20% untuk laptop gaming pilihan. Berlaku sampai 30 Juni 2026. Jangan lewatkan!', 'tipe' => 'promo', 'status_baca' => 'belum', 'tanggal' => '2026-06-25 09:00:00'],
        ['id' => 5, 'judul' => 'Selamat Datang di SmartRetail!', 'isi' => 'Terima kasih telah mendaftar. Nikmati pengalaman belanja elektronik premium dengan ribuan produk pilihan terbaik.', 'tipe' => 'info', 'status_baca' => 'sudah', 'tanggal' => '2026-06-01 08:00:00'],
    ];
}

$filterTipe  = $_GET['tipe'] ?? 'semua';
$filteredNotifs = $filterTipe === 'semua' ? $allNotifs : array_filter($allNotifs, fn($n) => ($n['tipe'] ?? '') === $filterTipe);

$unreadCount = count(array_filter($allNotifs, fn($n) => ($n['status_baca'] ?? 'sudah') === 'belum'));

$tipeConfig = [
    'pesanan'    => ['icon' => 'fa-receipt',     'color' => '#1d4ed8', 'bg' => '#dbeafe', 'label' => 'Pesanan'],
    'pembayaran' => ['icon' => 'fa-credit-card', 'color' => '#92400e', 'bg' => '#fef9c3', 'label' => 'Pembayaran'],
    'pengiriman' => ['icon' => 'fa-truck',        'color' => '#065f46', 'bg' => '#d1fae5', 'label' => 'Pengiriman'],
    'promo'      => ['icon' => 'fa-tag',          'color' => '#9d174d', 'bg' => '#fce7f3', 'label' => 'Promo'],
    'info'       => ['icon' => 'fa-circle-info',  'color' => '#1e40af', 'bg' => '#e0e7ff', 'label' => 'Info'],
];
?>

<div class="container my-5">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fa-solid fa-bell text-primary me-2"></i>Notifikasi Saya
            </h2>
            <p class="text-muted mb-0">
                <?php if ($unreadCount > 0): ?>
                    <span class="badge bg-danger me-1"><?= $unreadCount ?></span> notifikasi belum dibaca
                <?php else: ?>
                    Semua notifikasi sudah dibaca
                <?php endif; ?>
            </p>
        </div>
        <?php if ($unreadCount > 0): ?>
        <button class="btn btn-outline-primary rounded-3" id="markAllReadPageBtn">
            <i class="fa-solid fa-check-double me-2"></i>Tandai Semua Dibaca
        </button>
        <?php endif; ?>
    </div>

    <!-- Filter Pills -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="?tipe=semua" class="btn btn-sm rounded-pill px-4 <?= $filterTipe === 'semua' ? 'btn-primary' : 'btn-outline-secondary' ?>">
            Semua <span class="badge <?= $filterTipe === 'semua' ? 'bg-white text-primary' : 'bg-secondary' ?> ms-1"><?= count($allNotifs) ?></span>
        </a>
        <?php foreach ($tipeConfig as $key => $cfg): 
            $count = count(array_filter($allNotifs, fn($n) => ($n['tipe'] ?? '') === $key));
            if ($count === 0) continue;
        ?>
        <a href="?tipe=<?= $key ?>" class="btn btn-sm rounded-pill px-4 <?= $filterTipe === $key ? 'btn-primary' : 'btn-outline-secondary' ?>">
            <i class="fa-solid <?= $cfg['icon'] ?> me-1"></i><?= $cfg['label'] ?>
            <span class="badge <?= $filterTipe === $key ? 'bg-white text-primary' : 'bg-secondary' ?> ms-1"><?= $count ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Notifications List -->
    <?php if (empty($filteredNotifs)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <i class="fa-regular fa-bell-slash text-muted mb-3" style="font-size:4rem;"></i>
            <h5 class="text-muted">Tidak ada notifikasi</h5>
            <p class="text-muted small">Belum ada notifikasi untuk kategori ini.</p>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($filteredNotifs as $notif):
                $notifId = $notif['id'] ?? $notif['id_notifikasi'] ?? 0;
                $tipe    = $notif['tipe'] ?? 'info';
                $cfg     = $tipeConfig[$tipe] ?? $tipeConfig['info'];
                $isUnread= ($notif['status_baca'] ?? 'sudah') === 'belum';
                $waktu   = date('d F Y, H:i', strtotime($notif['tanggal'] ?? 'now'));
            ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden notif-card <?= $isUnread ? 'unread-card' : '' ?>"
                 style="<?= $isUnread ? 'border-left: 4px solid #3b82f6 !important;' : '' ?>"
                 id="notif-<?= $notifId ?>">
                <div class="card-body p-4">
                    <div class="d-flex gap-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center"
                             style="width:50px;height:50px;background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;">
                            <i class="fa-solid <?= $cfg['icon'] ?>" style="font-size:1.2rem;"></i>
                        </div>
                        <!-- Content -->
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0" style="font-size:0.95rem;"><?= htmlspecialchars($notif['judul']) ?></h6>
                                    <?php if ($isUnread): ?>
                                        <span class="badge bg-primary" style="font-size:0.62rem;">Baru</span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-muted" style="font-size:0.75rem;white-space:nowrap;"><?= $waktu ?></small>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0 rounded-circle" data-bs-toggle="dropdown" style="width:28px;height:28px;padding:0;">
                                            <i class="fa-solid fa-ellipsis-vertical" style="font-size:0.8rem;"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow" style="min-width:160px;">
                                            <?php if ($isUnread): ?>
                                            <li>
                                                <a class="dropdown-item py-2 small mark-read-btn" href="#" data-id="<?= $notifId ?>">
                                                    <i class="fa-solid fa-check me-2 text-primary"></i>Tandai Dibaca
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <li>
                                                <a class="dropdown-item py-2 small text-danger" href="#"
                                                   onclick="deleteNotif(<?= $notifId ?>); return false;">
                                                    <i class="fa-solid fa-trash me-2"></i>Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted mb-2" style="font-size:0.88rem;line-height:1.5;"><?= htmlspecialchars($notif['isi']) ?></p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;font-size:0.7rem;">
                                    <i class="fa-solid <?= $cfg['icon'] ?> me-1"></i><?= $cfg['label'] ?>
                                </span>
                                <?php if ($tipe === 'pesanan'): ?>
                                    <a href="pesanan.php" class="btn btn-sm btn-outline-primary rounded-3" style="font-size:0.75rem;padding:2px 10px;">
                                        Lihat Pesanan
                                    </a>
                                <?php elseif ($tipe === 'pembayaran'): ?>
                                    <a href="pesanan.php" class="btn btn-sm btn-outline-success rounded-3" style="font-size:0.75rem;padding:2px 10px;">
                                        Cek Pembayaran
                                    </a>
                                <?php elseif ($tipe === 'promo'): ?>
                                    <a href="produk.php" class="btn btn-sm btn-outline-danger rounded-3" style="font-size:0.75rem;padding:2px 10px;">
                                        Lihat Promo
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.unread-card { background: linear-gradient(to right, #eff6ff, #fff) !important; }
.notif-card { transition: transform 0.2s, box-shadow 0.2s; }
.notif-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark single read
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const card = document.getElementById('notif-' + id);
            fetch('helper_notif.php?action=read&id=' + id)
                .finally(() => {
                    if (card) {
                        card.classList.remove('unread-card');
                        card.style.borderLeft = '';
                        const badge = card.querySelector('.badge.bg-primary');
                        if (badge) badge.remove();
                        this.closest('li').remove();
                    }
                });
        });
    });

    // Mark all read
    const markAllBtn = document.getElementById('markAllReadPageBtn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            fetch('helper_notif.php?action=read_all').finally(() => {
                document.querySelectorAll('.unread-card').forEach(el => {
                    el.classList.remove('unread-card');
                    el.style.borderLeft = '';
                });
                document.querySelectorAll('.badge.bg-primary').forEach(el => el.remove());
                markAllBtn.remove();
            });
        });
    }
});

function deleteNotif(id) {
    if (!confirm('Hapus notifikasi ini?')) return;
    const card = document.getElementById('notif-' + id);
    if (card) {
        card.style.opacity = '0';
        card.style.transform = 'translateX(50px)';
        card.style.transition = 'all 0.3s ease';
        setTimeout(() => card.remove(), 300);
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
