<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$successMsg = null;

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?? '';
    $status = $_POST['status'] ?? '';

    if (!empty($orderId) && !empty($status)) {
        $res = apiRequest('PUT', '/admin/orders/' . $orderId, ['status' => $status]);
        if ($res['success']) {
            $successMsg = "Status pesanan #$orderId berhasil diubah menjadi $status!";
        } else {
            // Mock state updates
            if (isset($_SESSION['mock_orders_list'][$orderId])) {
                $_SESSION['mock_orders_list'][$orderId]['status'] = $status;
            }
            $successMsg = "Status pesanan #$orderId diubah menjadi $status (API Mock Mode)!";
        }
    }
}

// Fetch admin orders
$ordersApi = apiRequest('GET', '/admin/orders');
$orders = [];
if ($ordersApi['success'] && isset($ordersApi['data']) && is_array($ordersApi['data'])) {
    $orders = $ordersApi['data'];
} else {
    // Session mock sync
    if (isset($_SESSION['mock_orders_list'])) {
        $orders = [];
        foreach ($_SESSION['mock_orders_list'] as $mockOrd) {
            $orders[] = [
                'order_id' => $mockOrd['order_id'],
                'customer' => 'Budi Santoso',
                'tanggal' => $mockOrd['tanggal'],
                'total' => 25044000,
                'status' => $mockOrd['status']
            ];
        }
    } else {
        $orders = [
            [
                'order_id' => 'ORD-20260624-9122',
                'customer' => 'Budi Santoso',
                'tanggal' => '2026-06-24 10:15:30',
                'total' => 25044000,
                'status' => 'Menunggu pembayaran'
            ],
            [
                'order_id' => 'ORD-20260620-8012',
                'customer' => 'Dewi Lestari',
                'tanggal' => '2026-06-20 14:22:11',
                'total' => 1743000,
                'status' => 'Selesai'
            ]
        ];
    }
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0"><i class="fa-solid fa-receipt mr-2"></i>Kelola Pesanan</h1>
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
                <h3 class="card-title font-weight-bold">Daftar Transaksi Pelanggan</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped m-0">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Total Belanja</th>
                            <th>Status</th>
                            <th class="text-right" style="width: 320px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): 
                            $badgeClass = 'bg-warning text-dark';
                            if ($order['status'] === 'Diproses') $badgeClass = 'bg-primary';
                            if ($order['status'] === 'Dikirim') $badgeClass = 'bg-info text-dark';
                            if ($order['status'] === 'Selesai') $badgeClass = 'bg-success';
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($order['order_id']) ?></strong></td>
                                <td><?= htmlspecialchars($order['customer']) ?></td>
                                <td><?= htmlspecialchars($order['tanggal']) ?></td>
                                <td>Rp <?= number_format($order['total'], 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?> font-weight-bold px-3 py-2"><?= htmlspecialchars($order['status']) ?></span>
                                </td>
                                <td class="text-right">
                                    <!-- Status update form -->
                                     <form action="pesanan.php" method="POST" class="d-inline-block">
                                         <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id_pesanan'] ?? $order['order_id']) ?>">
                                        <div class="input-group input-group-sm" style="width: 250px;">
                                            <select class="form-select form-control" name="status" required>
                                                <option value="" disabled selected>Ubah Status...</option>
                                                <option value="Menunggu pembayaran" <?= $order['status'] === 'Menunggu pembayaran' ? 'disabled' : '' ?>>Menunggu Pembayaran</option>
                                                <option value="Diproses" <?= $order['status'] === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                                                <option value="Dikirim" <?= $order['status'] === 'Dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                                <option value="Selesai" <?= $order['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                            </select>
                                            <button class="btn btn-primary" type="submit">Update</button>
                                        </div>
                                    </form>
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

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
