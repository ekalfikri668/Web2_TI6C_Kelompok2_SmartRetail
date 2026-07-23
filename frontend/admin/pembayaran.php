<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$successMsg = null;
$errorMsg   = null;

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_bank_settings'])) {
        $bankName = $_POST['bank_name'] ?? '';
        $accountNumber = $_POST['account_number'] ?? '';
        $accountHolder = $_POST['account_holder'] ?? '';
        $settings = [
            'bank_name' => $bankName,
            'account_number' => $accountNumber,
            'account_holder' => $accountHolder
        ];
        $filePath = __DIR__ . '/../config/payment_settings.json';
        if (file_put_contents($filePath, json_encode($settings, JSON_PRETTY_PRINT))) {
            $successMsg = "Pengaturan rekening bank berhasil diperbarui!";
        } else {
            $errorMsg = "Gagal menyimpan pengaturan rekening bank.";
        }
    } else {
        $paymentId = intval($_POST['payment_id'] ?? 0);
        $action    = $_POST['action'] ?? ''; // 'Disetujui' or 'Ditolak'

        if ($paymentId && in_array($action, ['Disetujui', 'Ditolak'])) {
            $res = apiRequest('PUT', '/admin/payment/' . $paymentId, ['status' => $action]);
            if ($res['success']) {
                $label = ($action === 'Disetujui') ? '✓ Disetujui' : '✗ Ditolak';
                $successMsg = "Pembayaran #$paymentId berhasil diverifikasi: $label";
            } else {
                $errMsg = $res['message'] ?? 'Terjadi kesalahan saat verifikasi.';
                $errorMsg = "Gagal verifikasi pembayaran #$paymentId: $errMsg";
            }
        }
    }
}

// Fetch current bank settings
$bankSettingsFile = __DIR__ . '/../config/payment_settings.json';
$bankName = 'Bank Central Asia (BCA)';
$accountNumber = '882-990-1122';
$accountHolder = 'PT MiniRetail Indonesia';
if (file_exists($bankSettingsFile)) {
    $settings = json_decode(file_get_contents($bankSettingsFile), true);
    if (!empty($settings)) {
        $bankName = $settings['bank_name'] ?? $bankName;
        $accountNumber = $settings['account_number'] ?? $accountNumber;
        $accountHolder = $settings['account_holder'] ?? $accountHolder;
    }
}

// Fetch payment transactions
$paymentApi = apiRequest('GET', '/admin/payment');
$payments = [];
if ($paymentApi['success'] && isset($paymentApi['data']) && is_array($paymentApi['data'])) {
    $payments = $paymentApi['data'];
} else {
    // Mock fallback data
    $payments = [
        [
            'id_pembayaran'  => 201,
            'id_pesanan'     => 'ORD-20260624-9122',
            'nama_pembeli'   => 'Budi Santoso',
            'jumlah_bayar'   => 25044000,
            'metode'         => 'Transfer Bank',
            'bukti_bayar'    => 'https://images.unsplash.com/photo-1554415707-6e8cfc93fe23?auto=format&fit=crop&w=600&q=80',
            'status'         => 'Menunggu Verifikasi',
            'tanggal_bayar'  => '2026-06-24 10:20:15'
        ]
    ];
}

// Base URL for proof images
$baseUrl = str_replace('/api', '/', BASE_API_URL);
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0"><i class="fa-solid fa-wallet mr-2 text-primary"></i>Verifikasi Pembayaran</h1>
    </div>
  </div>

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

        <?php if ($errorMsg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-xmark mr-2"></i><?= htmlspecialchars($errorMsg) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Card Pengaturan Rekening -->
        <div class="card card-info collapsed-card mb-4 shadow-sm">
            <div class="card-header py-2" style="cursor: pointer;" data-card-widget="collapse">
                <h3 class="card-title font-weight-bold" style="font-size: 0.95rem;"><i class="fa-solid fa-gear mr-1"></i>Pengaturan Rekening Transfer Bank</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i> Ubah Rekening
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="pembayaran.php" method="POST">
                    <input type="hidden" name="save_bank_settings" value="1">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="bank_name" class="small text-muted font-weight-bold">Nama Bank & Keterangan</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" value="<?= htmlspecialchars($bankName) ?>" placeholder="Contoh: Bank Central Asia (BCA)" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="account_number" class="small text-muted font-weight-bold">Nomor Rekening</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" value="<?= htmlspecialchars($accountNumber) ?>" placeholder="Contoh: 882-990-1122" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="account_holder" class="small text-muted font-weight-bold">Atas Nama (a.n.)</label>
                            <input type="text" class="form-control" id="account_holder" name="account_holder" value="<?= htmlspecialchars($accountHolder) ?>" placeholder="Contoh: PT MiniRetail Indonesia" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-info btn-sm font-weight-bold px-3">
                        <i class="fa-solid fa-save mr-1"></i> Simpan Pengaturan Rekening
                    </button>
                </form>
            </div>
        </div>

        <div class="card card-primary card-outline">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold mb-0">
                    <i class="fas fa-list mr-1"></i>Daftar Unggahan Bukti Pembayaran
                </h3>
                <span class="badge badge-primary"><?= count($payments) ?> transaksi</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <?php if (empty($payments)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-check-circle d-block mb-3" style="font-size:3rem;color:#d1d5db;"></i>
                        <h5>Tidak ada pembayaran yang perlu diverifikasi</h5>
                    </div>
                <?php else: ?>
                <table class="table table-hover table-striped m-0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Pelanggan</th>
                            <th>Metode</th>
                            <th>Nominal Tagihan</th>
                            <th>Tanggal Kirim</th>
                            <th>Bukti</th>
                            <th class="text-center">Status</th>
                            <th class="text-right" style="width: 220px;">Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($payments as $pay): 
                            // Support both real API fields and mock fields
                            $payId     = $pay['id_pembayaran'] ?? $pay['id'] ?? '-';
                            $orderId   = $pay['id_pesanan'] ?? $pay['order_id'] ?? '-';
                            $customer  = $pay['nama_pembeli'] ?? $pay['customer'] ?? 'Tidak diketahui';
                            $metode    = $pay['metode'] ?? 'Transfer Bank';
                            $jumlah    = $pay['jumlah_bayar'] ?? $pay['total'] ?? 0;
                            $bukti     = $pay['bukti_bayar'] ?? $pay['bukti'] ?? '';
                            $status    = $pay['status'] ?? 'Menunggu Verifikasi';
                            $tanggal   = $pay['tanggal_bayar'] ?? $pay['tanggal'] ?? '-';
                            // Build full URL for proof image
                            $buktiUrl  = (str_starts_with($bukti, 'http')) ? $bukti : $baseUrl . $bukti;
                            $isPending = ($status === 'Menunggu Verifikasi');
                        ?>
                        <tr id="pay-row-<?= $payId ?>">
                            <td>
                                <strong class="text-primary">#<?= htmlspecialchars($payId) ?></strong>
                                <div class="text-muted small">Pesanan: #<?= htmlspecialchars($orderId) ?></div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-2 font-weight-bold text-white"
                                         style="width:32px;height:32px;background:#3b82f6;font-size:0.8rem;flex-shrink:0;">
                                        <?= strtoupper(substr($customer, 0, 1)) ?>
                                    </div>
                                    <?= htmlspecialchars($customer) ?>
                                </div>
                            </td>
                            <td><span class="badge badge-light border"><?= htmlspecialchars($metode) ?></span></td>
                            <td><strong class="text-success">Rp <?= number_format($jumlah, 0, ',', '.') ?></strong></td>
                            <td><small class="text-muted"><?= htmlspecialchars($tanggal) ?></small></td>
                            <td>
                                <?php if (!empty($bukti)): ?>
                                <button class="btn btn-xs btn-outline-info view-slip-btn" 
                                        data-url="<?= htmlspecialchars($buktiUrl) ?>">
                                    <i class="fa-regular fa-image"></i> Lihat Bukti
                                </button>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php 
                                $badgeClass = match($status) {
                                    'Disetujui' => 'badge-success',
                                    'Ditolak'   => 'badge-danger',
                                    default     => 'badge-warning'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
                            </td>
                            <td class="text-right">
                                <?php if ($isPending): ?>
                                <form action="pembayaran.php" method="POST" class="d-inline border-0">
                                    <input type="hidden" name="payment_id" value="<?= $payId ?>">
                                    <input type="hidden" name="action" value="Disetujui">
                                    <button type="submit" class="btn btn-xs btn-success font-weight-bold"
                                            onclick="return confirm('Setujui pembayaran ini?')">
                                        <i class="fa-solid fa-circle-check"></i> Setujui
                                    </button>
                                </form>
                                <form action="pembayaran.php" method="POST" class="d-inline border-0 ml-1">
                                    <input type="hidden" name="payment_id" value="<?= $payId ?>">
                                    <input type="hidden" name="action" value="Ditolak">
                                    <button type="submit" class="btn btn-xs btn-danger font-weight-bold"
                                            onclick="return confirm('Tolak pembayaran ini? Pengguna akan diberitahu.')">
                                        <i class="fa-solid fa-circle-xmark"></i> Tolak
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span class="text-muted small">— Sudah diproses</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

    </div>
  </section>
</div>

<!-- Modal View Slip -->
<div class="modal fade" id="viewSlipModal" tabindex="-1" role="dialog" aria-labelledby="viewSlipModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#1a2332,#2d3748);">
        <h5 class="modal-title font-weight-bold text-white" id="viewSlipModalLabel">
          <i class="fas fa-receipt mr-2"></i>Bukti Transfer Pembayaran
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center p-3">
        <img src="" id="slipImage" class="img-fluid rounded border shadow-sm" style="max-height: 500px;" alt="Bukti Transfer">
        <div class="mt-2">
          <a id="slipDownloadLink" href="#" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
            <i class="fas fa-external-link-alt mr-1"></i>Buka di Tab Baru
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View slip button - populate modal then open it using Bootstrap 5 API
    $(document).on('click', '.view-slip-btn', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        $('#slipImage').attr('src', url);
        $('#slipDownloadLink').attr('href', url);
        // Use Bootstrap 5 Modal API (footer loads Bootstrap 5 Bundle JS)
        const modalEl = document.getElementById('viewSlipModal');
        if (modalEl) {
            let modal = bootstrap.Modal.getInstance(modalEl);
            if (!modal) modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });

    // Also fix close button for Bootstrap 5
    document.querySelectorAll('[data-dismiss="modal"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const modalEl = this.closest('.modal');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        });
    });
});
</script>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
