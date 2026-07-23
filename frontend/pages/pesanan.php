<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/cek_login.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/navbar.php';

// Fetch orders list
$ordersApi = apiRequest('GET', '/orders');
$orders    = [];

if ($ordersApi['success'] && isset($ordersApi['data']) && is_array($ordersApi['data'])) {
    // Normalize API response fields to template field names
    foreach ($ordersApi['data'] as $rawOrder) {
        $orders[] = [
            'order_id' => $rawOrder['id_pesanan']     ?? $rawOrder['order_id'] ?? '—',
            'produk'   => $rawOrder['nama_produk']    ?? $rawOrder['produk']   ?? 'Detail Pesanan',
            'jumlah'   => $rawOrder['jumlah']         ?? 1,
            'total'    => $rawOrder['total_harga']    ?? $rawOrder['total']    ?? 0,
            'tanggal'  => $rawOrder['tanggal_pesanan']?? $rawOrder['tanggal']  ?? date('Y-m-d H:i:s'),
            'status'   => $rawOrder['status_pesanan'] ?? $rawOrder['status']   ?? 'Menunggu Pembayaran',
            'shipping' => $rawOrder['pengiriman']     ?? null,
        ];
    }
} else {
    // Fallback to session mock orders
    if (isset($_SESSION['mock_orders_list'])) {
        foreach ($_SESSION['mock_orders_list'] as $mockOrder) {
            $orders[] = [
                'order_id' => $mockOrder['order_id']          ?? $mockOrder['id_pesanan'] ?? '—',
                'produk'   => $mockOrder['produk']            ?? 'Pesanan Anda',
                'jumlah'   => $mockOrder['jumlah']            ?? 1,
                'total'    => $mockOrder['total_harga']       ?? $mockOrder['total'] ?? 0,
                'tanggal'  => $mockOrder['tanggal']           ?? date('Y-m-d H:i:s'),
                'status'   => $mockOrder['status']            ?? 'Menunggu Pembayaran',
                'shipping' => null,
            ];
        }
    } else {
        $orders = [
            [
                'order_id' => '1',
                'produk'   => 'ASUS ROG Strix G16 Gaming Laptop',
                'jumlah'   => 1,
                'total'    => 24999000,
                'tanggal'  => '2026-06-24 10:15:30',
                'status'   => 'Menunggu pembayaran',
                'shipping' => null
            ],
            [
                'order_id' => '2',
                'produk'   => 'Logitech G502 X Plus Wireless RGB',
                'jumlah'   => 2,
                'total'    => 2598000,
                'tanggal'  => '2026-06-20 14:22:11',
                'status'   => 'Selesai',
                'shipping' => [
                    'ekspedisi'     => 'JNE Reguler',
                    'nomor_resi'    => 'JN-992012019920',
                    'estimasi_tiba' => '2026-06-22',
                    'status'        => 'Terkirim'
                ]
            ],
        ];
    }
}

// Mock shipping data per order_id
$mockShipping = [
    'ORD-20260624-0001' => null,
    'ORD-20260620-0002' => [
        'ekspedisi'    => 'JNE Reguler',
        'nomor_resi'   => 'JN-992012019920',
        'estimasi_tiba'=> '22 Juni 2026',
        'status'       => 'Terkirim'
    ],
    'ORD-20260625-0003' => [
        'ekspedisi'    => 'J&T Express',
        'nomor_resi'   => 'JT-881234567890',
        'estimasi_tiba'=> '28 Juni 2026',
        'status'       => 'Dalam Perjalanan'
    ],
];
?>

<div class="container my-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1"><i class="fa-solid fa-receipt text-primary me-2"></i>Pesanan Saya</h2>
            <p class="text-muted mb-0"><?= count($orders) ?> total pesanan</p>
        </div>
        <a href="produk.php" class="btn btn-outline-primary rounded-3">
            <i class="fa-solid fa-plus me-2"></i>Belanja Lagi
        </a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="card border-0 shadow-sm p-5 text-center rounded-4 bg-white">
            <i class="fa-solid fa-folder-open text-muted mb-3" style="font-size: 4rem;"></i>
            <h4>Belum Ada Pesanan</h4>
            <p class="text-muted">Anda belum melakukan transaksi apapun di SmartRetail.</p>
            <div class="mt-4">
                <a href="produk.php" class="btn btn-primary btn-lg rounded-3">Belanja Sekarang</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Orders List -->
            <div class="col-lg-8">
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($orders as $order):
                        $badgeClass = 'bg-warning text-dark';
                        if ($order['status'] === 'Diproses')   $badgeClass = 'bg-primary';
                        if ($order['status'] === 'Dikirim')    $badgeClass = 'bg-info text-dark';
                        if ($order['status'] === 'Selesai')    $badgeClass = 'bg-success';
                        if ($order['status'] === 'Dibatalkan') $badgeClass = 'bg-danger';

                        $statusIcon = match($order['status']) {
                            'Menunggu pembayaran' => 'fa-clock',
                            'Diproses'            => 'fa-cog',
                            'Dikirim'             => 'fa-truck',
                            'Selesai'             => 'fa-check-circle',
                            'Dibatalkan'          => 'fa-times-circle',
                            default               => 'fa-receipt'
                        };
                    ?>
                        <div class="card border-0 shadow-sm rounded-4 bg-white order-card" 
                             data-order-id="<?= htmlspecialchars($order['order_id']) ?>">
                            <div class="card-body p-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                                    <div>
                                        <span class="text-muted small fw-semibold">ID PESANAN</span>
                                        <h6 class="fw-bold m-0 text-primary"><?= htmlspecialchars($order['order_id']) ?></h6>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-muted small d-block"><?= date('d M Y, H:i', strtotime($order['tanggal'])) ?></span>
                                        <span class="badge <?= $badgeClass ?> px-3 py-2 rounded-pill">
                                            <i class="fa-solid <?= $statusIcon ?> me-1"></i><?= htmlspecialchars($order['status']) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <hr class="my-2">

                                <div class="py-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($order['produk'] ?? 'Detail Pesanan') ?></h6>
                                        <p class="text-muted small mb-0"><?= $order['jumlah'] ?> barang</p>
                                    </div>
                                    <div class="text-end">
                                        <p class="text-muted small mb-0">Total</p>
                                        <strong class="text-primary">Rp <?= number_format($order['total'], 0, ',', '.') ?></strong>
                                    </div>
                                </div>

                                <hr class="my-2">

                                <div class="d-flex flex-wrap justify-content-end gap-2 mt-2">
                                    <?php if ($order['status'] === 'Menunggu pembayaran'): ?>
                                        <a href="pembayaran.php?order_id=<?= urlencode($order['order_id']) ?>" class="btn btn-sm btn-primary rounded-3 px-4">
                                            <i class="fa-solid fa-credit-card me-1"></i>Bayar Sekarang
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($order['status'] === 'Dikirim'): ?>
                                        <button type="button" class="btn btn-sm btn-success rounded-3 px-3 confirm-arrival-btn" data-id="<?= htmlspecialchars($order['order_id']) ?>">
                                            <i class="fa-solid fa-circle-check me-1"></i>Konfirmasi Diterima
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($order['status'] === 'Selesai'): ?>
                                        <a href="detail_produk.php" class="btn btn-sm btn-outline-success rounded-3 px-3">
                                            <i class="fa-solid fa-star me-1"></i>Beri Ulasan
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-4 view-shipping-btn"
                                            data-id="<?= htmlspecialchars($order['order_id']) ?>"
                                            data-status="<?= htmlspecialchars($order['status']) ?>">
                                        <i class="fa-solid fa-truck-ramp-box me-1"></i>Lacak Pesanan
                                    </button>
                                    <a href="profil.php?tab=histori" class="btn btn-sm btn-outline-primary rounded-3 px-3">
                                        <i class="fa-solid fa-list me-1"></i>Histori
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Shipping Timeline Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white sticky-top" style="top: 90px; z-index: 1;" id="shipping-tracker-card">
                    <h5 class="mb-4 fw-bold"><i class="fa-solid fa-map-pin text-primary me-2"></i>Lacak Pesanan</h5>
                    
                    <div id="shipping-details-placeholder" class="text-center py-4">
                        <i class="fa-solid fa-truck text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="text-muted small">Klik <strong>Lacak Pesanan</strong> pada salah satu pesanan untuk melihat status pengiriman.</p>
                    </div>

                    <div id="shipping-details-content" class="d-none">
                        <!-- Order Info -->
                        <div class="bg-primary-subtle p-3 rounded-3 mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">ID Pesanan</small>
                                <strong class="text-primary small" id="ship-order-id">-</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Ekspedisi</small>
                                <span class="fw-semibold small" id="ship-courier">-</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Nomor Resi</small>
                                <strong class="text-primary small" id="ship-resi">-</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Estimasi Tiba</small>
                                <span class="fw-semibold small" id="ship-eta">-</span>
                            </div>
                        </div>

                        <!-- Vertical Timeline -->
                        <div class="timeline-tech">
                            <div class="timeline-item-tech" id="step-created">
                                <div class="timeline-title text-dark">Pesanan Dibuat</div>
                                <div class="timeline-date">Pesanan terdaftar di sistem.</div>
                            </div>
                            <div class="timeline-item-tech" id="step-paid">
                                <div class="timeline-title text-dark">Pembayaran Dikonfirmasi</div>
                                <div class="timeline-date">Dana diverifikasi oleh admin.</div>
                            </div>
                            <div class="timeline-item-tech" id="step-processed">
                                <div class="timeline-title text-dark">Sedang Diproses</div>
                                <div class="timeline-date">Barang sedang disiapkan di gudang.</div>
                            </div>
                            <div class="timeline-item-tech" id="step-shipped">
                                <div class="timeline-title text-dark">Dikirim</div>
                                <div class="timeline-date">Kurir membawa pesanan Anda.</div>
                            </div>
                            <div class="timeline-item-tech" id="step-finished">
                                <div class="timeline-title text-dark">Selesai</div>
                                <div class="timeline-date">Pesanan telah diterima.</div>
                            </div>
                        </div>

                        <!-- Copy Resi Button -->
                        <button class="btn btn-outline-primary btn-sm w-100 mt-3 rounded-3" id="copyResiBtn">
                            <i class="fa-solid fa-copy me-2"></i>Salin Nomor Resi
                        </button>
                    </div>

                    <!-- No Shipping Yet Panel -->
                    <div id="shipping-no-shipping" class="d-none text-center py-4">
                        <i class="fa-solid fa-clock text-warning mb-3" style="font-size: 2.5rem;"></i>
                        <h6 class="fw-bold">Menunggu Pembayaran</h6>
                        <p class="text-muted small">Informasi pengiriman akan tersedia setelah pembayaran dikonfirmasi.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Mock shipping data
const mockShippingData = <?= json_encode($mockShipping) ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Confirm arrival action
    document.querySelectorAll('.confirm-arrival-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.id;
            Swal.fire({
                title: 'Konfirmasi Pesanan Sampai?',
                text: "Apakah Anda yakin barang pesanan ini sudah Anda terima dengan baik?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Sudah Sampai',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?= BASE_API_URL ?>/orders/' + orderId + '/konfirmasi-tiba', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer <?= $_SESSION['token'] ?? '' ?>'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil', 'Penerimaan pesanan berhasil dikonfirmasi.', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', data.message || 'Gagal mengonfirmasi pesanan.', 'error');
                        }
                    })
                    .catch(() => {
                        // Offline mock fallback
                        Swal.fire('Berhasil', 'Konfirmasi pesanan diterima (Simulasi Offline)', 'success').then(() => {
                            location.reload();
                        });
                    });
                }
            });
        });
    });

    // Attach click handler to all track buttons
    document.querySelectorAll('.view-shipping-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.id;
            const status  = this.dataset.status;

            // Highlight selected order card
            document.querySelectorAll('.order-card').forEach(c => c.style.borderLeft = '');
            const parentCard = this.closest('.order-card');
            if (parentCard) parentCard.style.borderLeft = '4px solid #3b82f6';

            // Update tracker header
            document.getElementById('ship-order-id').textContent = orderId;

            // Try API first
            const apiUrl = '../config/api.php';
            // Fallback to mock directly since this is frontend standalone
            renderShipping(orderId, status);
        });
    });

    function renderShipping(orderId, status) {
        const placeholder  = document.getElementById('shipping-details-placeholder');
        const content      = document.getElementById('shipping-details-content');
        const noShipping   = document.getElementById('shipping-no-shipping');

        placeholder.classList.add('d-none');
        content.classList.add('d-none');
        noShipping.classList.add('d-none');

        // Reset timeline
        document.querySelectorAll('.timeline-item-tech').forEach(el => el.classList.remove('active'));

        if (status === 'Menunggu pembayaran') {
            noShipping.classList.remove('d-none');
            document.getElementById('step-created').classList.add('active');
            return;
        }

        const shipping = mockShippingData[orderId];

        content.classList.remove('d-none');
        document.getElementById('ship-order-id').textContent = orderId;

        if (shipping) {
            document.getElementById('ship-courier').textContent = shipping.ekspedisi || '-';
            document.getElementById('ship-resi').textContent    = shipping.nomor_resi || '-';
            document.getElementById('ship-eta').textContent     = shipping.estimasi_tiba || '-';

            // Activate timeline steps based on status
            document.getElementById('step-created').classList.add('active');
            if (['Diproses','Dikirim','Dalam Perjalanan','Terkirim','Selesai'].includes(shipping.status)) {
                document.getElementById('step-paid').classList.add('active');
                document.getElementById('step-processed').classList.add('active');
            }
            if (['Dikirim','Dalam Perjalanan','Terkirim','Selesai'].includes(shipping.status)) {
                document.getElementById('step-shipped').classList.add('active');
            }
            if (['Terkirim','Selesai'].includes(shipping.status)) {
                document.getElementById('step-finished').classList.add('active');
            }
        } else {
            document.getElementById('ship-courier').textContent = '-';
            document.getElementById('ship-resi').textContent    = 'Sedang diproses';
            document.getElementById('ship-eta').textContent     = '-';
            document.getElementById('step-created').classList.add('active');
            document.getElementById('step-paid').classList.add('active');
        }

        // Copy resi button
        const copyBtn = document.getElementById('copyResiBtn');
        if (copyBtn) {
            copyBtn.onclick = function() {
                const resi = document.getElementById('ship-resi').textContent;
                if (resi && resi !== '-' && resi !== 'Sedang diproses') {
                    navigator.clipboard.writeText(resi).then(() => {
                        copyBtn.innerHTML = '<i class="fa-solid fa-check me-2"></i>Tersalin!';
                        copyBtn.classList.add('btn-success');
                        copyBtn.classList.remove('btn-outline-primary');
                        setTimeout(() => {
                            copyBtn.innerHTML = '<i class="fa-solid fa-copy me-2"></i>Salin Nomor Resi';
                            copyBtn.classList.remove('btn-success');
                            copyBtn.classList.add('btn-outline-primary');
                        }, 2000);
                    });
                }
            };
        }
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
