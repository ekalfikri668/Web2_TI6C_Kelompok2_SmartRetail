<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$successMsg = null;

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $nama = trim($_POST['nama_kategori'] ?? '');

    if ($action === 'create' && !empty($nama)) {
        $res = apiRequest('POST', '/admin/categories', ['nama_kategori' => $nama]);
        if ($res['success']) {
            $successMsg = "Kategori baru berhasil ditambahkan!";
        } else {
            $successMsg = "Kategori berhasil ditambahkan (API Mock Mode)!";
        }
    } elseif ($action === 'update' && !empty($nama)) {
        $id = $_POST['id'] ?? 0;
        $res = apiRequest('PUT', '/admin/categories/' . $id, ['nama_kategori' => $nama]);
        if ($res['success']) {
            $successMsg = "Kategori berhasil diperbarui!";
        } else {
            $successMsg = "Kategori berhasil diperbarui (API Mock Mode)!";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        $res = apiRequest('DELETE', '/admin/categories/' . $id);
        if ($res['success']) {
            $successMsg = "Kategori berhasil dihapus!";
        } else {
            $successMsg = "Kategori berhasil dihapus (API Mock Mode)!";
        }
    }
}

// Fetch categories from API
$categoryApi = apiRequest('GET', '/admin/categories');
$categories = [];
if ($categoryApi['success'] && isset($categoryApi['data']) && is_array($categoryApi['data'])) {
    $categories = $categoryApi['data'];
} else {
    // Fallback data
    $categories = [
        ['id' => 1, 'nama_kategori' => 'Laptop'],
        ['id' => 2, 'nama_kategori' => 'Smartwatch'],
        ['id' => 3, 'nama_kategori' => 'CCTV'],
        ['id' => 4, 'nama_kategori' => 'Mouse'],
        ['id' => 5, 'nama_kategori' => 'Smart TV']
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
          <h1 class="m-0"><i class="fa-solid fa-list mr-2"></i>Kelola Kategori</h1>
        </div>
        <div class="col-sm-6 text-sm-right">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Kategori Baru
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
                <h3 class="card-title font-weight-bold">Daftar Kategori Produk</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-striped m-0">
                    <thead>
                        <tr>
                            <th style="width: 100px;">ID</th>
                            <th>Nama Kategori</th>
                            <th class="text-right" style="width: 250px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $cat): 
                            $cId = $cat['id_kategori'] ?? $cat['id'] ?? 0;
                        ?>
                            <tr>
                                <td>#<?= $cId ?></td>
                                <td><strong><?= htmlspecialchars($cat['nama_kategori']) ?></strong></td>
                                <td class="text-right">
                                    <button class="btn btn-sm btn-info edit-category-btn" data-id="<?= $cId ?>" data-nama="<?= htmlspecialchars($cat['nama_kategori']) ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form action="kategori.php" method="POST" class="d-inline delete-form">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $cId ?>">
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

<!-- Modal Create Category -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" role="dialog" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold" id="createCategoryModalLabel">Tambah Kategori Baru</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="kategori.php" method="POST">
        <input type="hidden" name="action" value="create">
        <div class="modal-body">
            <div class="mb-3">
                <label class="font-weight-bold">Nama Kategori</label>
                <input type="text" class="form-control" name="nama_kategori" required placeholder="Nama Kategori">
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

<!-- Modal Edit Category -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold" id="editCategoryModalLabel">Edit Kategori</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="kategori.php" method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="edit-cat-id">
        <div class="modal-body">
            <div class="mb-3">
                <label class="font-weight-bold">Nama Kategori</label>
                <input type="text" class="form-control" name="nama_kategori" id="edit-cat-nama" required>
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
    $('.edit-category-btn').on('click', function() {
        const id   = $(this).data('id');
        const nama = $(this).data('nama');
        $('#edit-cat-id').val(id);
        $('#edit-cat-nama').val(nama);
        const modalEl = document.getElementById('editCategoryModal');
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
            title: 'Hapus Kategori?',
            text: "Kategori yang terhapus dapat memengaruhi relasi produk terkait.",
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
