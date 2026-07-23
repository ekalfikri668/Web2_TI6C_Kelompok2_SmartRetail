<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/cek_login.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/navbar.php';

// Fetch Cart Data
$cartApi = apiRequest('GET', '/cart');
$cartItems = [];
if ($cartApi['success'] && isset($cartApi['data']['items']) && is_array($cartApi['data']['items']) && !empty($cartApi['data']['items'])) {
    $cartItems = array_map(function($item) {
        return [
            'id' => $item['id_detail'] ?? $item['id'] ?? null,
            'product_id' => $item['id_produk'] ?? $item['product_id'] ?? null,
            'nama_produk' => $item['nama_produk'] ?? '',
            'brand' => $item['nama_brand'] ?? $item['brand'] ?? 'Laptop',
            'harga' => $item['harga'] ?? 0,
            'jumlah' => $item['jumlah'] ?? 0,
            'warna' => $item['warna'] ?? 'Standar',
            'tipe'  => $item['tipe'] ?? 'Standar',
            'foto' => $item['gambar'] ?? $item['foto'] ?? 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=200&q=80'
        ];
    }, $cartApi['data']['items']);
} elseif (isset($_SESSION['session_cart']) && is_array($_SESSION['session_cart']) && !empty($_SESSION['session_cart'])) {
    $cartItems = array_values($_SESSION['session_cart']);
}
// else $cartItems stays empty — will show "Keranjang Kosong" UI

$cartItems = array_map(function($item) {
    $id = $item['id'] ?? $item['id_detail'] ?? $item['product_id'] ?? rand(100, 999);
    $item['id'] = $id;
    return $item;
}, $cartItems);

$totalHarga = 0;
foreach($cartItems as $item) {
    $totalHarga += $item['harga'] * $item['jumlah'];
}
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fa-solid fa-cart-shopping text-tech-blue me-2"></i>Keranjang Belanja</h2>
    
    <?php if (empty($cartItems)): ?>
        <div class="card border-light shadow-sm p-5 text-center bg-white rounded-4">
            <i class="fa-solid fa-cart-arrow-down text-muted mb-3" style="font-size: 4rem;"></i>
            <h4>Keranjang Anda Kosong</h4>
            <p class="text-muted">Sepertinya Anda belum menambahkan produk apapun ke keranjang belanja.</p>
            <div class="mt-4">
                <a href="produk.php" class="btn btn-tech-primary btn-lg">Mulai Belanja</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Items Column -->
            <div class="col-lg-8">
                <div class="card border-light shadow-sm p-4 rounded-4 bg-white table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-secondary" style="font-size: 0.9rem;">
                                <th scope="col" style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="checkAll" checked>
                                </th>
                                <th scope="col" colspan="2">Produk</th>
                                <th scope="col">Harga</th>
                                <th scope="col" style="width: 140px;">Jumlah</th>
                                <th scope="col">Subtotal</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): 
                                $subtotal = $item['harga'] * $item['jumlah'];
                            ?>
                                <tr id="cart-row-<?= $item['id'] ?>" class="cart-item-row" data-price="<?= $item['harga'] ?>" data-id="<?= $item['id'] ?>">
                                    <td>
                                        <input type="checkbox" class="form-check-input item-checkbox" data-id="<?= $item['id'] ?>" checked>
                                    </td>
                                    <td style="width: 80px;">
                                         <img src="<?= htmlspecialchars($item['foto']) ?>" class="img-fluid rounded-3" alt="" style="width: 70px; height: 70px; object-fit: cover; object-position: center;">
                                    </td>
                                    <td>
                                        <h6 class="mb-1 font-weight-bold"><?= htmlspecialchars($item['nama_produk']) ?></h6>
                                        <small class="text-muted">Brand: <?= htmlspecialchars($item['brand']) ?> | Warna: <?= htmlspecialchars($item['warna'] ?? 'Standar') ?> | Tipe: <?= htmlspecialchars($item['tipe'] ?? 'Standar') ?></small>
                                    </td>
                                    <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary qty-adjust-btn" data-action="minus" data-id="<?= $item['id'] ?>">-</button>
                                            <input type="text" class="form-control text-center qty-input bg-white" id="qty-<?= $item['id'] ?>" value="<?= $item['jumlah'] ?>" readonly>
                                            <button class="btn btn-outline-secondary qty-adjust-btn" data-action="plus" data-id="<?= $item['id'] ?>">+</button>
                                        </div>
                                    </td>
                                    <td><strong class="item-subtotal">Rp <?= number_format($subtotal, 0, ',', '.') ?></strong></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-danger border-0 delete-cart-btn" data-id="<?= $item['id'] ?>" data-bs-toggle="tooltip" title="Hapus Produk">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Column -->
            <div class="col-lg-4">
                <div class="card border-light shadow-sm p-4 rounded-4 bg-white sticky-top" style="top: 90px; z-index: 1;">
                    <h5 class="mb-4 font-weight-bold">Ringkasan Belanja</h5>
                    <div class="d-flex justify-content-between mb-3 text-secondary" style="font-size: 0.95rem;">
                        <span>Total Barang:</span>
                        <span id="summary-total-qty"><?= array_sum(array_column($cartItems, 'jumlah')) ?> unit</span>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="font-weight-bold text-dark fs-5">Total Harga:</span>
                        <span class="font-weight-bold text-primary fs-4" id="summary-total-price">Rp <?= number_format($totalHarga, 0, ',', '.') ?></span>
                    </div>
                    <div class="d-grid">
                        <button id="btnCheckout" class="btn btn-primary btn-lg py-3 btn-tech-primary">
                            Lanjut ke Checkout <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Local helper: format Rupiah
    function formatRupiah(value) {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }

    // Handle Quantity Adjustments (Plus / Minus)
    $('.qty-adjust-btn').on('click', function() {
        const action = $(this).data('action');
        const cartId = $(this).data('id');
        const $input = $('#qty-' + cartId);
        const $row   = $('#cart-row-' + cartId);
        const price  = parseFloat($row.data('price'));

        let val = parseInt($input.val()) || 1;
        if (action === 'plus') {
            val++;
        } else if (action === 'minus') {
            if (val > 1) val--;
        }
        $input.val(val);

        // Update Subtotal immediately
        $row.find('.item-subtotal').text(formatRupiah(price * val));
        calculateCartTotals();

        // Send API Request
        $.ajax({
            url: 'helper_cart.php',
            method: 'POST',
            data: { action: 'update', cart_id: cartId, jumlah: val },
            dataType: 'json',
            success: function(res) {
                if (!res.success && typeof showAlert === 'function') {
                    showAlert('Gagal', res.message || 'Gagal mengubah jumlah produk.', 'error');
                }
            },
            error: function() { console.log('Mock cart updated to ' + val); }
        });
    });

    // Handle Item Deletions
    $('.delete-cart-btn').on('click', function() {
        const cartId = $(this).data('id');

        if (typeof Swal === 'undefined') {
            if (!confirm('Hapus produk ini dari keranjang?')) return;
            doDeleteCart(cartId);
            return;
        }

        Swal.fire({
            title: 'Hapus Produk?',
            text: 'Apakah Anda yakin ingin menghapus produk ini dari keranjang belanja Anda?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) doDeleteCart(cartId);
        });
    });

    function doDeleteCart(cartId) {
        $('#cart-row-' + cartId).fadeOut('slow', function() {
            $(this).remove();
            calculateCartTotals();
            if ($('.cart-item-row').length === 0) location.reload();
        });
        $.ajax({
            url: 'helper_cart.php',
            method: 'POST',
            data: { action: 'delete', cart_id: cartId },
            dataType: 'json',
            error: function() { console.log('Mock deleted cart item ' + cartId); }
        });
    }

    // Check All / Uncheck All
    $('#checkAll').on('change', function() {
        $('.item-checkbox').prop('checked', $(this).is(':checked'));
        calculateCartTotals();
    });

    // Individual Checkbox change
    $(document).on('change', '.item-checkbox', function() {
        const total   = $('.item-checkbox').length;
        const checked = $('.item-checkbox:checked').length;
        $('#checkAll').prop('checked', total === checked);
        calculateCartTotals();
    });

    // Checkout button click
    $('#btnCheckout').on('click', function(e) {
        e.preventDefault();
        const selectedIds = [];
        $('.item-checkbox:checked').each(function() {
            const id = $(this).data('id');
            if (id !== undefined && id !== '') selectedIds.push(id);
        });
        
        // Fallback: if checkAll was not triggered, take all rows
        if (selectedIds.length === 0) {
            $('.cart-item-row').each(function() {
                const id = $(this).data('id');
                if (id !== undefined && id !== '') selectedIds.push(id);
            });
        }
        
        if (selectedIds.length === 0) {
            showAlert('Peringatan', 'Keranjang Anda kosong atau belum ada produk yang dipilih.', 'warning');
            return;
        }
        
        window.location.href = 'checkout.php?ids=' + selectedIds.join(',');
    });

    // Calculate totals
    function calculateCartTotals() {
        let total    = 0;
        let totalQty = 0;
        $('.cart-item-row').each(function() {
            const $row      = $(this);
            const isChecked = $row.find('.item-checkbox').is(':checked');
            if (isChecked) {
                const price = parseFloat($row.data('price'));
                const qty   = parseInt($row.find('.qty-input').val()) || 0;
                total    += price * qty;
                totalQty += qty;
            }
        });
        $('#summary-total-qty').text(totalQty + ' unit');
        $('#summary-total-price').text(formatRupiah(total));
        if (totalQty === 0) {
            $('#btnCheckout').addClass('disabled').attr('disabled', true);
        } else {
            $('#btnCheckout').removeClass('disabled').removeAttr('disabled');
        }
    }

    calculateCartTotals();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
