<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/cek_login.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<style>
.qris-scanner-line {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: rgba(59, 130, 246, 0.8);
    box-shadow: 0 0 10px #3b82f6;
    animation: qrisScan 2.5s linear infinite;
}
@keyframes qrisScan {
    0% { top: 0%; }
    50% { top: 100%; }
    100% { top: 0%; }
}
</style>
<?php

$orderId = $_GET['order_id'] ?? '';

if (empty($orderId)) {
    header("Location: home.php");
    exit;
}

// Fetch Order detail from API
$orderApi   = apiRequest('GET', '/orders/' . $orderId);
$order      = null;
$totalBayar = 0;

// Retrieve payment method (set during checkout, fallback to session or order)
$paymentMethod = $_SESSION['payment_method_' . $orderId] ?? 'Transfer Bank';

if ($orderApi['success'] && isset($orderApi['data']) && is_array($orderApi['data'])) {
    $order         = $orderApi['data'];
    $totalBayar    = (float)($order['total_harga'] ?? $order['total_bayar'] ?? 0);
    // Use order's metode_pembayaran if stored, else fallback to session
    if (!empty($order['metode_pembayaran'])) {
        $paymentMethod = $order['metode_pembayaran'];
    }
} else {
    // Fallback: check session mock orders
    if (isset($_SESSION['mock_orders_list'][$orderId])) {
        $order         = $_SESSION['mock_orders_list'][$orderId];
        $paymentMethod = $order['metode_pembayaran'] ?? $paymentMethod;
        $totalBayar    = (float)($order['total_harga'] ?? 0);
    } else {
        // Still allow page to render with order_id from URL
        $totalBayar = 0;
    }
}

$error   = null;
$success = null;

// Handle Proof of Payment Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_bayar'])) {
    $file = $_FILES['bukti_bayar'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Gagal mengunggah file bukti pembayaran. Pastikan file valid (JPG/PNG, maks 2MB).";
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $error = "Ukuran file terlalu besar. Maksimal 2MB.";
    } else {
        // Build multipart request for the API
        $cfile = new CURLFile($file['tmp_name'], $file['type'], $file['name']);

        // Use numeric order_id when possible (API requires integer id_pesanan)
        $numericOrderId = is_numeric($orderId) ? (int)$orderId : $orderId;

        $response = apiRequest('POST', '/payment', [
            'id_pesanan'   => $numericOrderId,
            'metode'       => $paymentMethod,
            'jumlah_bayar' => $totalBayar,
            'bukti_bayar'  => $cfile
        ], true); // true = multipart/form-data

        if ($response['success']) {
            $success = "Bukti pembayaran berhasil diunggah! Admin kami akan segera melakukan verifikasi.";
            // Update session mock order status if applicable
            if (isset($_SESSION['mock_orders_list'][$orderId])) {
                $_SESSION['mock_orders_list'][$orderId]['status'] = 'Menunggu Verifikasi Pembayaran';
            }
        } else {
            $errMsg = $response['message'] ?? 'Terjadi kesalahan.';
            // Append validation errors if present
            if (!empty($response['data']) && is_array($response['data'])) {
                $errMsg .= ': ' . implode(', ', $response['data']);
            } elseif (!empty($response['raw']['errors']) && is_array($response['raw']['errors'])) {
                $errMsg .= ': ' . implode(', ', $response['raw']['errors']);
            }
            $error = "Gagal mengunggah bukti: " . $errMsg;
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-light shadow-sm p-4 p-md-5 rounded-4 bg-white">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-circle-info text-tech-blue mb-2" style="font-size: 3rem;"></i>
                    <h3 class="font-weight-bold">Instruksi Pembayaran</h3>
                    <p class="text-muted">Selesaikan pembayaran Anda sebelum tagihan kedaluwarsa</p>
                    <div class="bg-light p-3 rounded-4 mt-3">
                        <span class="text-secondary small font-weight-bold">ID PESANAN:</span>
                        <h6 class="font-weight-bold text-dark mb-2"><?= htmlspecialchars($orderId) ?></h6>
                        <?php if ($totalBayar > 0): ?>
                        <span class="text-secondary small font-weight-bold">TOTAL TAGIHAN:</span>
                        <h3 class="font-weight-bold text-primary mb-0">Rp <?= number_format($totalBayar, 0, ',', '.') ?></h3>
                        <?php else: ?>
                        <span class="text-muted small">Total tagihan akan dikonfirmasi oleh admin.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isset($_GET['warn']) && !$error && !$success): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Pesanan dibuat dalam mode offline. <?= htmlspecialchars(urldecode($_GET['warn'])) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="mb-4 pt-2">
                    <h5 class="font-weight-bold mb-3"><i class="fa-solid fa-wallet text-secondary me-2"></i>Metode: <?= htmlspecialchars($paymentMethod) ?></h5>
                    
                    <?php if ($paymentMethod === 'Transfer Bank'): 
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
                        // Clean numeric account number for copying
                        $cleanAccountNumber = preg_replace('/[^0-9]/', '', $accountNumber);
                    ?>
                        <div class="border rounded-4 p-3 bg-light-subtle">
                            <p class="mb-3 text-secondary">Silakan transfer nominal tagihan di atas ke nomor rekening berikut:</p>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <strong class="text-dark"><?= htmlspecialchars($bankName) ?></strong>
                                    <h4 class="font-weight-bold text-tech-blue my-1"><?= htmlspecialchars($accountNumber) ?></h4>
                                    <span class="text-muted small">a.n. <?= htmlspecialchars($accountHolder) ?></span>
                                </div>
                                <button class="btn btn-sm btn-outline-primary border-0 copy-btn" data-clipboard="<?= htmlspecialchars($cleanAccountNumber) ?>" data-bs-toggle="tooltip" title="Salin Rekening">
                                    <i class="fa-regular fa-copy fs-5"></i>
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4 border rounded-4 position-relative overflow-hidden bg-light-subtle">
                            <p class="text-secondary mb-3 small">Pindai kode QRIS di bawah menggunakan GoPay, OVO, Dana, ShopeePay, atau Mobile Banking:</p>
                            <div class="position-relative d-inline-block mb-3 p-1 bg-white border rounded-3" style="max-height: 230px;">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=MiniRetail-Payment-<?= urlencode($orderId) ?>" class="img-fluid rounded shadow-sm" alt="QRIS" style="max-height: 200px; width: auto;">
                                <div class="qris-scanner-line"></div>
                            </div>
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-2 text-warning fw-semibold small" id="qris-status-text">
                                <span class="spinner-grow spinner-grow-sm" role="status"></span>
                                <span>Menunggu scan QRIS...</span>
                            </div>
                            <button class="btn btn-outline-primary btn-sm px-4 rounded-3 mt-1" id="btnSimulateQris">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> Cek Status Pembayaran
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <hr class="my-4">

                <?php if ($paymentMethod !== 'QRIS'): ?>
                    <?php if (!$success): ?>
                    <!-- Proof of Payment Upload Form -->
                    <form action="pembayaran.php?order_id=<?= htmlspecialchars($orderId) ?>" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="bukti_bayar" class="form-label font-weight-bold text-dark">Unggah Bukti Pembayaran</label>
                            <p class="text-muted small mb-2">Format file: JPG, PNG. Maksimal ukuran file: 2MB.</p>
                            <input class="form-control" type="file" id="bukti_bayar" name="bukti_bayar" accept="image/jpeg,image/png,image/gif" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg btn-tech-primary">
                                <i class="fa-solid fa-cloud-arrow-up me-2"></i>Kirim Bukti Pembayaran
                            </button>
                            <a href="profil.php?tab=histori" class="btn btn-outline-secondary">
                                Lihat Riwayat Pesanan
                            </a>
                        </div>
                    </form>
                    <?php else: ?>
                    <!-- Already uploaded — show action buttons -->
                    <div class="d-grid gap-2">
                        <a href="profil.php?tab=histori" class="btn btn-success btn-lg">
                            <i class="fa-solid fa-list-check me-2"></i>Lihat Status Pesanan
                        </a>
                        <a href="home.php" class="btn btn-outline-secondary">
                            Kembali ke Beranda
                        </a>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('.copy-btn').on('click', function() {
        const text = $(this).data('clipboard');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showAlert && showAlert('Disalin', 'Nomor rekening berhasil disalin!', 'success');
            }).catch(() => {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    });

    function fallbackCopy(text) {
        const el = document.createElement('textarea');
        el.value = text;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        alert('Nomor rekening ' + text + ' berhasil disalin!');
    }

    <?php if ($paymentMethod === 'QRIS'): ?>
    // QRIS Automated Simulation
    const autoVerifyTimer = setTimeout(function() {
        triggerQrisPayment();
    }, 4000);

    $('#btnSimulateQris').on('click', function(e) {
        e.preventDefault();
        clearTimeout(autoVerifyTimer);
        triggerQrisPayment();
    });

    function triggerQrisPayment() {
        $('#qris-status-text').html('<span class="spinner-border spinner-border-sm text-primary" role="status"></span> <span class="text-primary small">Memverifikasi pembayaran...</span>');
        
        $.ajax({
            url: 'helper_payment_qris.php',
            method: 'POST',
            data: {
                order_id: '<?= htmlspecialchars($orderId) ?>',
                jumlah_bayar: '<?= $totalBayar ?>'
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#qris-status-text').html('<i class="fa-solid fa-circle-check text-success fs-5"></i> <span class="text-success fw-bold">Pembayaran Berhasil!</span>');
                    showAlert('Pembayaran Berhasil', 'Terima kasih! Pembayaran QRIS Anda telah diverifikasi secara instan.', 'success', function() {
                        window.location.href = 'profil.php?tab=histori';
                    });
                } else {
                    $('#qris-status-text').html('<i class="fa-solid fa-circle-xmark text-danger fs-5"></i> <span class="text-danger">Gagal memproses pembayaran. Coba lagi.</span>');
                    showAlert('Gagal Verifikasi', res.message || 'Gagal memproses pembayaran QRIS.', 'error');
                }
            },
            error: function() {
                // Fallback simulation for offline mode
                $('#qris-status-text').html('<i class="fa-solid fa-circle-check text-success fs-5"></i> <span class="text-success fw-bold">Pembayaran Berhasil!</span>');
                showAlert('Pembayaran Berhasil', 'Pembayaran QRIS diverifikasi berhasil (Offline Mode).', 'success', function() {
                    window.location.href = 'profil.php?tab=histori';
                });
            }
        });
    }
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
