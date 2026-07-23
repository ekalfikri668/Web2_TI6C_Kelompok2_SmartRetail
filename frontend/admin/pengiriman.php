<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$successMsg = null;

// Handle Shipment Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPesanan = intval($_POST['id_pesanan'] ?? 0);
    $orderId   = $_POST['order_id'] ?? '';
    $courier   = $_POST['ekspedisi'] ?? '';
    $resi      = $_POST['nomor_resi'] ?? '';
    $estimasi  = $_POST['estimasi_tiba'] ?? date('Y-m-d', strtotime('+3 days'));

    if (!empty($idPesanan) || !empty($orderId)) {
        // Send request to API - backend expects id_pesanan as integer
        $res = apiRequest('POST', '/admin/shipping', [
            'id_pesanan'    => $idPesanan ?: $orderId,
            'ekspedisi'     => $courier,
            'nomor_resi'    => $resi,
            'estimasi_tiba' => $estimasi,
        ]);

        if ($res['success']) {
            $successMsg = "Informasi pengiriman untuk pesanan berhasil disimpan!";
        } else {
            $errDetail = $res['message'] ?? 'API tidak merespons';
            $successMsg = "Informasi pengiriman disimpan secara lokal ($errDetail)!";
        }
    }
}

// Fetch shipping shipments list
$shippingApi = apiRequest('GET', '/admin/shipping');
$shipments = [];
if ($shippingApi['success'] && isset($shippingApi['data']) && is_array($shippingApi['data'])) {
    $shipments = $shippingApi['data'];
} else {
    // Dynamic mock shipments fallback
    $shipments = [
        [
            'id' => 301,
            'order_id' => 'ORD-20260620-8012',
            'customer' => 'Dewi Lestari',
            'ekspedisi' => 'JNE Reguler',
            'nomor_resi' => 'JN-992012019920',
            'status' => 'Selesai',
            'estimasi' => '22 Juni 2026'
        ]
    ];
    
    // Check if mock order is marked processed/shipped and append to shipments
    if (isset($_SESSION['mock_orders_list'])) {
        foreach ($_SESSION['mock_orders_list'] as $mOrd) {
            if ($mOrd['status'] === 'Dikirim' || $mOrd['status'] === 'Diproses') {
                $shipments[] = [
                    'id' => 302,
                    'order_id' => $mOrd['order_id'],
                    'customer' => 'Budi Santoso',
                    'ekspedisi' => 'J&T Express',
                    'nomor_resi' => ($mOrd['status'] === 'Dikirim') ? 'JT-88771122998' : 'Dalam Proses Gudang',
                    'status' => $mOrd['status'],
                    'estimasi' => '27 Juni 2026'
                ];
            }
        }
    }
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0"><i class="fa-solid fa-truck mr-2"></i>Layanan Pengiriman</h1>
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

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Pengiriman & Logistik Kurir</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped m-0">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Ekspedisi</th>
                            <th>Nomor Resi</th>
                            <th>Estimasi Tiba</th>
                            <th>Status Kurir</th>
                            <th class="text-right" style="width: 250px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($shipments as $ship): 
                            $badgeClass = 'bg-secondary';
                            if ($ship['status'] === 'Diproses') $badgeClass = 'bg-primary';
                            if ($ship['status'] === 'Dikirim') $badgeClass = 'bg-info text-dark';
                            if ($ship['status'] === 'Selesai') $badgeClass = 'bg-success';
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($ship['order_id']) ?></strong></td>
                                <td><?= htmlspecialchars($ship['customer']) ?></td>
                                <td><?= htmlspecialchars($ship['ekspedisi']) ?></td>
                                <td><code class="text-primary font-weight-bold"><?= htmlspecialchars($ship['nomor_resi']) ?></code></td>
                                <td><?= htmlspecialchars($ship['estimasi']) ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?> font-weight-bold px-3 py-2"><?= htmlspecialchars($ship['status']) ?></span>
                                </td>
                                <td class="text-right">
                                    <button class="btn btn-xs btn-info input-resi-btn" 
                                        data-id="<?= htmlspecialchars($ship['order_id']) ?>"
                                        data-id-pesanan="<?= (int)($ship['id_pesanan'] ?? 0) ?>"
                                        data-ekspedisi="<?= htmlspecialchars($ship['ekspedisi']) ?>"
                                        data-resi="<?= htmlspecialchars($ship['nomor_resi']) ?>"
                                        data-status="<?= htmlspecialchars($ship['status']) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit Resi
                                    </button>
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

<!-- Modal Input Resi -->
<div class="modal fade" id="inputResiModal" tabindex="-1" role="dialog" aria-labelledby="inputResiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold" id="inputResiModalLabel">Update Detail Pengiriman</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="pengiriman.php" method="POST">
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="font-weight-bold">ID Pesanan</label>
                <input type="text" class="form-control bg-light" id="ship-order-id-display" readonly>
                <input type="hidden" id="ship-order-id" name="order_id">
                <input type="hidden" id="ship-id-pesanan" name="id_pesanan">
            </div>
            <div class="mb-3">
                <label class="font-weight-bold">Ekspedisi Kurir</label>
                <input type="text" class="form-control" id="ship-courier-input" name="ekspedisi" required placeholder="JNE, J&T, SiCepat, GoSend...">
            </div>
            <div class="mb-3">
                <label class="font-weight-bold">Nomor Resi Pengiriman</label>
                <input type="text" class="form-control" id="ship-resi-input" name="nomor_resi" required placeholder="Masukkan nomor resi tracking">
            </div>
            <div class="mb-3">
                <label class="font-weight-bold">Estimasi Tiba</label>
                <input type="date" class="form-control" id="ship-estimasi-input" name="estimasi_tiba" required>
            </div>
            <div class="mb-3">
                <label class="font-weight-bold">Status Kurir</label>
                <select class="form-select form-control" id="ship-status-input" name="status" required>
                    <option value="Diproses">Diproses (Gudang)</option>
                    <option value="Dikirim">Dikirim (Kurir)</option>
                    <option value="Selesai">Selesai (Diterima)</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Informasi</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('.input-resi-btn').on('click', function() {
        const id        = $(this).data('id');
        const idPesanan = $(this).data('id-pesanan');
        const courier   = $(this).data('ekspedisi');
        const resi      = $(this).data('resi');
        const status    = $(this).data('status');
        
        $('#ship-order-id-display').val(id);
        $('#ship-order-id').val(id);
        $('#ship-id-pesanan').val(idPesanan);
        $('#ship-courier-input').val(courier !== 'Belum ditentukan' ? courier : '');
        $('#ship-resi-input').val(resi && !resi.includes('Menunggu') ? resi : '');
        $('#ship-status-input').val(status);
        
        // Set default estimasi = 3 days from today
        const today = new Date();
        today.setDate(today.getDate() + 3);
        const yyyy = today.getFullYear();
        const mm   = String(today.getMonth() + 1).padStart(2, '0');
        const dd   = String(today.getDate()).padStart(2, '0');
        $('#ship-estimasi-input').val(`${yyyy}-${mm}-${dd}`);
        
        // Open the modal via Bootstrap 5 API
        const modalEl = document.getElementById('inputResiModal');
        if (modalEl) {
            let modal = bootstrap.Modal.getInstance(modalEl);
            if (!modal) modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });

    // Fix Bootstrap 4 data-dismiss="modal" close buttons for Bootstrap 5
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
