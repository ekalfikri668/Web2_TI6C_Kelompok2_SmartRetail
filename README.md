# SmartRetail - Sistem Informasi Mini Retail Modern

SmartRetail adalah aplikasi manajemen toko ritel berbasis web yang mengimplementasikan pemisahan penuh antara arsitektur Client (Frontend) dan Server (Backend) menggunakan mode REST API murni. Proyek ini dibangun sebagai pemenuhan Tugas Kelompok Akhir Semester pada mata kuliah Pengembangan Aplikasi Web 2.

## Anggota Tim & Peran (Role)
* **[FIKRI HAEKAL]** - [2306700092] -> **System Analyst & Project Manager**
* **[NURUL NURMAWATI]** - [2306700084] -> **Database Engineer**
* **[DIDAN SIHAB ROBBANI]** - [2306700071] -> **Backend Developer**
* **[RAHMATUNNISA PUTRI]** - [2306700091] -> **UI/UX Designer**
* **[GERARDY BAINURRIZKY]** - [2306700093] -> **Frontend Developer**

---

## Tautan Eksternal Proyek
* **Papan Kerja Trello:** [https://trello.com/invite/b/6a2a8a66e6b3c193a74e0f7a/ATTI5086cecad4d388a9e1aa15435d0cd35cE48B0434/kelompok-2-pemweb2]
* **wireframe :** [https://balsamiq.cloud/sb2qg9x/pljki1]
* **Dokumentasi API (Postman Web):** [Masukkan Link Postman Collection jika ada]

---

## Arsitektur Monorepo & Standard Teknologi
Repositori ini dikelola menggunakan struktur Standard Monorepo dengan pembagian direktori sebagai berikut:
* `/frontend` : Aplikasi antarmuka responsif yang mengonsumsi data dari REST API.
* `/backend` : Core Logic & RESTful API murni yang dibangun dengan **CodeIgniter 4** (Tanpa merender HTML/View, mengembalikan format JSON standar).
* `/database` : Berkas skema basis data `.sql`, file Migration, dan rancangan ERD relasional.
* `/docs` : Dokumen formalitas proyek seperti Software Requirements Specification (SRS) dan Postman Collection Docs.

---

## Fitur Utama Aplikasi (Scope SmartRetail)
1. **Autentikasi Keamanan:** Sistem registrasi, login, dan proteksi endpoint API menggunakan stateless token **JWT (JSON Web Token)**.
2. **Manajemen Katalog Produk (CRUD):** Pengelolaan data produk, stok barang, kategori, dan harga (Khusus hak akses Kasir/Admin).
3. **Manajemen Keranjang Belanja:** Proses pemilihan produk dan kalkulasi otomatis item belanjaan di sisi klien.
4. **Modul Transaksi & Pembayaran:** Pemrosesan akhir belanjaan, penyimpanan rekaman transaksi ke database, dan pengembalian respons status standar HTTP JSON.

---
