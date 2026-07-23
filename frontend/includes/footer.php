<?php
$js_path = (strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? '../assets/' : 'assets/';
?>
<footer class="bg-tech-dark text-white pt-5 pb-4 mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-lg-4 mb-4">
                <h5 class="text-uppercase mb-3 font-weight-bold">
                    <i class="fa-solid fa-laptop text-tech-blue me-2"></i>Laptop<span class="text-tech-blue">Store</span>
                </h5>
                <p class="text-muted" style="font-size: 0.9rem;">
                    LaptopStore adalah retail e-commerce terpercaya penyedia produk IT premium. Kami menawarkan ragam laptop gaming, bisnis, smartwatch berkualitas tinggi, dan perlengkapan IT lainnya dengan garansi resmi.
                </p>
                <div class="mt-3">
                    <a href="#" class="btn btn-outline-light btn-floating m-1 text-white border-secondary" style="font-size: 1rem;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-light btn-floating m-1 text-white border-secondary" style="font-size: 1rem;"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="btn btn-outline-light btn-floating m-1 text-white border-secondary" style="font-size: 1rem;"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="btn btn-outline-light btn-floating m-1 text-white border-secondary" style="font-size: 1rem;"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="col-md-2 col-lg-2 mx-auto mb-4">
                <h6 class="text-uppercase mb-3 font-weight-bold text-tech-blue">Belanja</h6>
                <ul class="list-unstyled" style="font-size: 0.9rem;">
                    <li class="my-2"><a href="#" class="text-muted text-decoration-none hover-link">Laptop Gaming</a></li>
                    <li class="my-2"><a href="#" class="text-muted text-decoration-none hover-link">Laptop Tipis</a></li>
                    <li class="my-2"><a href="#" class="text-muted text-decoration-none hover-link">Smartwatch</a></li>
                    <li class="my-2"><a href="#" class="text-muted text-decoration-none hover-link">CCTV Keamanan</a></li>
                </ul>
            </div>

            <div class="col-md-2 col-lg-2 mx-auto mb-4">
                <h6 class="text-uppercase mb-3 font-weight-bold text-tech-blue">Bantuan</h6>
                <ul class="list-unstyled" style="font-size: 0.9rem;">
                    <li class="my-2"><a href="#" class="text-muted text-decoration-none hover-link">Status Pengiriman</a></li>
                    <li class="my-2"><a href="#" class="text-muted text-decoration-none hover-link">Cara Pembayaran</a></li>
                    <li class="my-2"><a href="#" class="text-muted text-decoration-none hover-link">Kebijakan Garansi</a></li>
                    <li class="my-2"><a href="#" class="text-muted text-decoration-none hover-link">Hubungi Kami</a></li>
                </ul>
            </div>

            <div class="col-md-4 col-lg-3 mx-auto mb-md-0 mb-4">
                <h6 class="text-uppercase mb-3 font-weight-bold text-tech-blue">Kontak</h6>
                <ul class="list-unstyled" style="font-size: 0.9rem; color: #94a3b8;">
                    <li class="my-2"><i class="fas fa-home mr-3 me-2"></i> Jl. Teknologi No.102, Jakarta, Indonesia</li>
                    <li class="my-2"><i class="fas fa-envelope mr-3 me-2"></i> support@laptopstore.co.id</li>
                    <li class="my-2"><i class="fas fa-phone mr-3 me-2"></i> +62 21 8899 7788</li>
                    <li class="my-2"><i class="fas fa-print mr-3 me-2"></i> +62 21 8899 7799</li>
                </ul>
            </div>
        </div>
        
        <hr class="mb-4 border-secondary">
        <div class="row align-items-center">
            <div class="col-md-7 col-lg-8 text-center text-md-start">
                <p class="text-muted mb-0" style="font-size: 0.85rem;">
                    Copyright &copy; 2026 <strong>LaptopStore</strong>. All rights reserved. Made for TIC Kelompok 2.
                </p>
            </div>
            <div class="col-md-5 col-lg-4 text-center text-md-end">
                <div class="d-inline-flex gap-3 text-muted" style="font-size: 1.25rem;">
                    <i class="fa-brands fa-cc-visa"></i>
                    <i class="fa-brands fa-cc-mastercard"></i>
                    <i class="fa-solid fa-qrcode"></i>
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Custom JavaScript -->
<script src="<?= $js_path ?>js/main.js"></script>
</body>
</html>
