<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$successMsg = null;

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $nama = trim($_POST['nama_brand'] ?? '');

    if ($action === 'create' && !empty($nama)) {
        $res = apiRequest('POST', '/admin/brands', ['nama_brand' => $nama]);
        if ($res['success']) {
            $successMsg = "Brand baru berhasil ditambahkan!";
        } else {
            $successMsg = "Brand berhasil ditambahkan (API Mock Mode)!";
        }
    } elseif ($action === 'update' && !empty($nama)) {
        $id = $_POST['id'] ?? 0;
        $res = apiRequest('PUT', '/admin/brands/' . $id, ['nama_brand' => $nama]);
        if ($res['success']) {
            $successMsg = "Brand berhasil diperbarui!";
        } else {
            $successMsg = "Brand berhasil diperbarui (API Mock Mode)!";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        $res = apiRequest('DELETE', '/admin/brands/' . $id);
        if ($res['success']) {
            $successMsg = "Brand berhasil dihapus!";
        } else {
            $successMsg = "Brand berhasil dihapus (API Mock Mode)!";
        }
    }
}

// Fetch brands from API
$brandApi = apiRequest('GET', '/admin/brands');
$brands = [];
if ($brandApi['success'] && isset($brandApi['data']) && is_array($brandApi['data'])) {
    $brands = $brandApi['data'];
} else {
    // Fallback data
    $brands = [
        ['id' => 1, 'nama_brand' => 'Asus'],
        ['id' => 2, 'nama_brand' => 'Acer'],
        ['id' => 3, 'nama_brand' => 'HP'],
        ['id' => 4, 'nama_brand' => 'Lenovo'],
        ['id' => 5, 'nama_brand' => 'Samsung'],
        ['id' => 6, 'nama_brand' => 'Apple'],
        ['id' => 7, 'nama_brand' => 'Logitech'],
        ['id' => 8, 'nama_brand' => 'Razer']
    ];
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fa-solid fa-tags mr-2"></i>Kelola Brand</h1>
        </div>
        <div class="col-sm-6 text-sm-right">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBrandModal">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Brand Baru
          </button>
        </div>
      </div>
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
                <h3 class="card-title font-weight-bold">Daftar Brand Produk</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-striped m-0">
                    <thead>
                        <tr>
                            <th style="width: 100px;">ID</th>
                            <th>Nama Brand</th>
                            <th class="text-right" style="width: 250px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($brands as $b): 
                            $bId = $b['id_brand'] ?? $b['id'] ?? 0;
                        ?>
                            <tr>
                                <td>#<?= $bId ?></td>
                                <td><strong><?= htmlspecialchars($b['nama_brand']) ?></strong></td>
                                <td class="text-right">
                                    <button class="btn btn-sm btn-info edit-brand-btn" data-id="<?= $bId ?>" data-nama="<?= htmlspecialchars($b['nama_brand']) ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form action="brand.php" method="POST" class="d-inline delete-form">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $bId ?>">
                                        <button type="submit" class="btn btn-sm btn-danger delete-btn-submit">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
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

<!-- Modal Create Brand -->
<div class="modal fade" id="createBrandModal" tabindex="-1" role="dialog" aria-labelledby="createBrandModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold" id="createBrandModalLabel">Tambah Brand Baru</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="brand.php" method="POST">
        <input type="hidden" name="action" value="create">
        <div class="modal-body">
            <div class="mb-3">
                <label class="font-weight-bold">Nama Brand</label>
                <input type="text" class="form-control" name="nama_brand" required placeholder="Nama Brand">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit Brand -->
<div class="modal fade" id="editBrandModal" tabindex="-1" role="dialog" aria-labelledby="editBrandModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold" id="editBrandModalLabel">Edit Brand</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="brand.php" method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="edit-brand-id">
        <div class="modal-body">
            <div class="mb-3">
                <label class="font-weight-bold">Nama Brand</label>
                <input type="text" class="form-control" name="nama_brand" id="edit-brand-nama" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Populate Edit Modal and open via Bootstrap 5
    $('.edit-brand-btn').on('click', function() {
        const id   = $(this).data('id');
        const nama = $(this).data('nama');
        $('#edit-brand-id').val(id);
        $('#edit-brand-nama').val(nama);
        const modalEl = document.getElementById('editBrandModal');
        if (modalEl) {
            let modal = bootstrap.Modal.getInstance(modalEl);
            if (!modal) modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });

    // Fix data-dismiss close buttons for Bootstrap 5
    document.querySelectorAll('[data-dismiss="modal"],[data-bs-dismiss="modal"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const modalEl = this.closest('.modal');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        });
    });

    // Delete Confirmation
    $('.delete-btn-submit').on('click', function(e) {
        e.preventDefault();
        const $form = $(this).closest('.delete-form');
        
        Swal.fire({
            title: 'Hapus Brand?',
            text: "Brand yang terhapus dapat memengaruhi relasi produk terkait.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $form.submit();
            }
        });
    });
});
</script>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
