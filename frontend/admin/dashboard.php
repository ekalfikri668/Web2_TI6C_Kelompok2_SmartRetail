<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Call Admin Dashboard API
$dashboardApi = apiRequest('GET', '/admin/dashboard');
$stats = [];
if ($dashboardApi['success'] && isset($dashboardApi['data'])) {
    $stats = $dashboardApi['data'];
} else {
    // Dynamic mock stats fallback if API is offline
    $stats = [
        'total_produk' => 124,
        'total_pesanan' => 1840,
        'total_pelanggan' => 450,
        'total_pendapatan' => 545900000,
        'produk_terlaris' => 'ROG Strix G16 Gaming Laptop',
        'sales_chart' => [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            'data' => [120000000, 150000000, 110000000, 190000000, 220000000, 250000000]
        ]
    ];
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Dashboard Utama</h1>
        </div>
        <div class="col-sm-6 text-sm-right">
          <span class="text-muted small font-weight-bold">Home / Dashboard</span>
        </div>
      </div>
    </div>
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- Info boxes -->
      <div class="row">
        <!-- Total Produk -->
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-box"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Produk</span>
              <span class="info-box-number font-weight-bold"><?= number_format($stats['total_produk']) ?></span>
            </div>
          </div>
        </div>

        <!-- Total Pesanan -->
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-shopping-cart"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Pesanan</span>
              <span class="info-box-number font-weight-bold"><?= number_format($stats['total_pesanan']) ?></span>
            </div>
          </div>
        </div>

        <!-- Total Pelanggan -->
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box mb-3">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Pelanggan</span>
              <span class="info-box-number font-weight-bold"><?= number_format($stats['total_pelanggan']) ?></span>
            </div>
          </div>
        </div>

        <!-- Total Pendapatan -->
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Pendapatan</span>
              <span class="info-box-number font-weight-bold">Rp <?= number_format($stats['total_pendapatan'], 0, ',', '.') ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Detail Card Bestseller -->
      <div class="card card-outline card-primary mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-trophy text-warning fs-1 mr-3"></i>
                <div>
                    <h6 class="m-0 text-muted">Produk Terlaris Bulan Ini:</h6>
                    <h5 class="m-0 font-weight-bold text-dark"><?= htmlspecialchars($stats['produk_terlaris']) ?></h5>
                </div>
            </div>
        </div>
      </div>

      <!-- Sales Chart Row -->
      <div class="row">
        <div class="col-12">
          <div class="card card-primary card-outline">
            <div class="card-header">
              <h3 class="card-title font-weight-bold">
                <i class="far fa-chart-bar mr-1"></i> Grafik Penjualan (6 Bulan Terakhir)
              </h3>
            </div>
            <div class="card-body">
              <div class="chart">
                <canvas id="salesChart" style="min-height: 350px; height: 350px; max-height: 350px; max-width: 100%;"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    // Set up Chart.js graph
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($stats['sales_chart']['labels']) ?>,
            datasets: [{
                label: 'Penjualan Bulanan (IDR)',
                data: <?= json_encode($stats['sales_chart']['data']) ?>,
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderColor: 'rgba(0, 123, 255, 0.8)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
