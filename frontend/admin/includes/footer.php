  <!-- Main Footer -->
  <footer class="main-footer">
    <div class="float-right d-none d-sm-inline">
      TIC Kelompok 2
    </div>
    <strong>Copyright &copy; 2026 <a href="#">LaptopStore Admin</a>.</strong> All rights reserved.
  </footer>
</div>
<!-- ./wrapper -->

<!-- Required Scripts -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- SweetAlert 2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- Custom Helper Scripts -->
<script>
// Global Bootstrap 4 → Bootstrap 5 Modal Compatibility Shim
// AdminLTE 3 uses Bootstrap 4 data-toggle/data-dismiss attributes,
// but footer loads Bootstrap 5 Bundle which doesn't read those.
document.addEventListener('DOMContentLoaded', function() {
    // Convert data-toggle="modal" → open modal via Bootstrap 5 API
    document.querySelectorAll('[data-toggle="modal"]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            const target = this.getAttribute('data-target') || this.getAttribute('data-bs-target');
            if (!target) return;
            e.preventDefault();
            const modalEl = document.querySelector(target);
            if (modalEl) {
                let modal = bootstrap.Modal.getInstance(modalEl);
                if (!modal) modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        });
    });

    // Convert data-dismiss="modal" → hide modal via Bootstrap 5 API
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
function formatRupiah(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

// Global SweetAlert functions for Admin actions
function showAdminAlert(title, text, icon = 'info', callback = null) {
    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        confirmButtonColor: '#007bff',
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isConfirmed && typeof callback === 'function') {
            callback();
        }
    });
}
</script>
</body>
</html>
