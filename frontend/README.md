# LaptopStore / Miniritail Frontend

Aplikasi frontend toko laptop modern (Miniritail) yang dibangun menggunakan **PHP Native**, **Bootstrap 5**, **AdminLTE 3**, dan **jQuery AJAX**. Aplikasi ini terintegrasi sepenuhnya dengan REST API Backend menggunakan PHP cURL.

---

## 📌 Fitur Utama

### 🛒 Fitur Client (Pembeli)
* **Beranda Premium:** Slider promo interaktif, navigasi kategori, dan daftar produk unggulan.
* **Katalog & Filter:** Pencarian produk secara fleksibel berdasarkan Kategori atau Brand.
* **Detail Produk:** Informasi spesifikasi laptop secara mendalam (Processor, RAM, GPU, Storage, dll) beserta ulasan pembeli.
* **Keranjang Belanja:** Manajemen keranjang belanja dinamis tanpa reload halaman (menggunakan AJAX).
* **Checkout & Pembayaran:** Proses pengisian alamat pengiriman, pemilihan kurir, dan unggah bukti transfer pembayaran.
* **Riwayat Pesanan:** Memantau status pengiriman barang dari penjual secara real-time.
* **Fitur Chat:** Obrolan langsung (*live chat*) dengan admin toko.

### 🛡️ Fitur Admin (Manajemen)
* **Dashboard Utama:** Grafik penjualan interaktif (menggunakan Chart.js) serta metrik total pendapatan, pesanan, dan pelanggan.
* **CRUD Inventaris:** Pengelolaan penuh data produk ritel (tambah, edit, hapus, upload foto produk) menggunakan modal pop-up bootstrap.
* **Verifikasi Transaksi:** Memproses pembayaran masuk (verifikasi bukti transfer) dan memanajemen pengiriman barang.
* **Review & Laporan:** Mengontrol ulasan dari pembeli dan mencetak laporan penjualan bulanan.

---

## 📂 Struktur Folder Proyek

```bash
web2_TIC_kelompok2_miniritail/
├── admin/                  # Dashboard & modul manajemen (AdminLTE)
│   ├── dashboard.php       # Grafik penjualan & statistik
│   ├── produk.php          # CRUD produk & spesifikasi detail
│   ├── kategori.php        # Manajemen kategori ritel
│   └── ...
├── assets/                 # File aset statis (CSS kustom, JS, Images)
├── config/
│   └── api.php             # Jantung integrasi cURL REST API & Otentikasi Bearer Token
├── includes/               # Layout modular (Navbar, Sidebar, Footer)
├── pages/                  # Fitur dan antarmuka pembeli (Client-side)
│   ├── home.php            # Landing page pembeli
│   ├── keranjang.php       # Keranjang belanja pembeli
│   ├── checkout.php        # Formulir pengiriman & checkout
│   └── ...
├── index.php               # Entry point utama (Redirect ke Halaman Utama Pembeli)
└── README.md               # Dokumentasi proyek (File Ini)
```

---

## 🔌 Integrasi REST API

Semua interaksi data pada aplikasi ini dijembatani oleh cURL Helper di [config/api.php](config/api.php).

### Konfigurasi Endpoint
Secara default, aplikasi frontend diarahkan untuk memanggil REST API backend lokal pada port `8000`:
```php
define('BASE_API_URL', 'http://localhost:8000/api');
```

Setiap request ke backend secara otomatis menyisipkan Authorization Bearer Token dari session user (`$_SESSION['token']`) untuk otentikasi keamanan transaksi.

---

## 🔄 Fitur Mock Data (Offline Mode)

Aplikasi frontend ini dilengkapi dengan mekanisme **Mock Data Fallback**. Jika server REST API Backend di port `8000` sedang mati atau offline:
1. Aplikasi **tidak akan error / crash**.
2. Frontend akan otomatis beralih menampilkan data tiruan (*mock data*) yang tertanam di dalam baris kode PHP.
3. Anda tetap dapat mendemonstrasikan, menguji, dan merancang antarmuka pengguna tanpa perlu menyalakan database atau server backend terlebih dahulu.

---

## 🚀 Cara Menjalankan

1. Salin folder proyek ke direktori XAMPP Anda:
   ```bash
   C:\xampp\htdocs\web2_TIC_kelompok2_miniritail
   ```
2. Jalankan Apache melalui **XAMPP Control Panel**.
3. Pastikan REST API Backend Anda berjalan di `http://localhost:8000` (atau biarkan offline jika ingin menggunakan *Mock Mode*).
4. Buka browser Anda dan akses:
   ```url
   http://localhost/web2_TIC_kelompok2_miniritail
   ```
