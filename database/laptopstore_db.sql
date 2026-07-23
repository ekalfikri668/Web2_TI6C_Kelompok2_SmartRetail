-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 22 Jul 2026 pada 10.21
-- Versi server: 10.4.32-MariaDB-log
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laptopstore_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `nama_admin` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `nama_admin`, `email`, `password`, `foto`, `role`) VALUES
(1, 'Admin SmartRetail', 'admin@smartretail.com', 'admin123', NULL, 'admin');

-- --------------------------------------------------------

--
-- Struktur dari tabel `alamat`
--

CREATE TABLE `alamat` (
  `id_alamat` int(11) NOT NULL,
  `id_pembeli` int(11) DEFAULT NULL,
  `nama_penerima` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `is_utama` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `alamat`
--

INSERT INTO `alamat` (`id_alamat`, `id_pembeli`, `nama_penerima`, `no_hp`, `alamat_lengkap`, `kota`, `kode_pos`, `is_utama`) VALUES
(1, 1, 'Budi Santoso', '081234567890', 'Jl. Merdeka No. 45, RT 02/RW 05, Gambir', 'Jakarta Pusat', '10110', 0),
(2, 2, 'Dewi Lestari', '089988776655', 'Jl. Sudirman No. 12, Blok B', 'Jakarta Selatan', '12190', 1),
(4, 1, 'gerar', '085721568056', 'jhdthgh', 'subang', '41256', 0),
(6, 7, 'aim', '081317274556', 'sgjsgjsbdhsssssssssss', 'subang', '41254', 1),
(7, 7, 'aim', '085432456785', 'yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy', 'subang', '41254', 0),
(8, 7, 'aim', '085432456785', 'yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy', 'subang', '41254', 0),
(9, 7, 'gerardy', '085724567865', 'tttttttttttttttttttttttttttttttttttttttt', 'subang', '41254', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `brand`
--

CREATE TABLE `brand` (
  `id_brand` int(11) NOT NULL,
  `nama_brand` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `brand`
--

INSERT INTO `brand` (`id_brand`, `nama_brand`, `logo`, `deskripsi`) VALUES
(1, 'Asus', 'https://upload.wikimedia.org/wikipedia/commons/2/2e/ASUS_Logo.svg', 'ASUS adalah produsen perangkat keras komputer multinasional asal Taiwan.'),
(2, 'Apple', 'https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg', 'Apple Inc. adalah perusahaan teknologi multinasional Amerika.'),
(3, 'Logitech', 'https://upload.wikimedia.org/wikipedia/commons/1/17/Logitech_logo.svg', 'Logitech International S.A. adalah produsen perlengkapan komputer.'),
(4, 'Xiaomi', 'https://upload.wikimedia.org/wikipedia/commons/a/ae/Xiaomi_logo_%282021-%29.svg', 'Xiaomi Corporation adalah perusahaan elektronik asal Tiongkok.'),
(5, 'HP', 'https://upload.wikimedia.org/wikipedia/commons/a/ad/HP_logo_2012.svg', 'HP Inc. adalah perusahaan teknologi informasi asal Amerika.'),
(6, 'Razer', 'https://upload.wikimedia.org/wikipedia/commons/e/ea/Razer_logo.svg', 'Razer Inc. adalah perusahaan perangkat keras game asal Amerika.'),
(7, 'Samsung', 'https://upload.wikimedia.org/wikipedia/commons/2/24/Samsung_Logo.svg', 'Samsung Electronics adalah produsen elektronik terbesar dunia asal Korea.'),
(8, 'LG', 'https://upload.wikimedia.org/wikipedia/commons/b/bf/LG_logo_%282015%29.svg', 'LG Electronics adalah perusahaan elektronik multinasional asal Korea.'),
(9, 'Sony', 'https://upload.wikimedia.org/wikipedia/commons/c/ca/Sony_logo.svg', 'Sony Corporation adalah konglomerat multinasional asal Jepang.'),
(10, 'Lenovo', 'https://upload.wikimedia.org/wikipedia/commons/b/b8/Lenovo_logo_2015.svg', 'Lenovo Group Limited adalah perusahaan teknologi multinasional asal Tiongkok.'),
(12, 'Ger', NULL, NULL),
(13, 'Ger', NULL, '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `chat`
--

CREATE TABLE `chat` (
  `id_chat` int(11) NOT NULL,
  `id_pembeli` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `pesan` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `pengirim` varchar(50) DEFAULT NULL,
  `waktu` datetime DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_edited` tinyint(1) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `chat`
--

INSERT INTO `chat` (`id_chat`, `id_pembeli`, `id_admin`, `pesan`, `gambar`, `pengirim`, `waktu`, `is_read`, `is_edited`, `is_deleted`) VALUES
(1, 1, NULL, 'p', NULL, 'user', '2026-07-07 14:26:16', 0, 0, 0),
(2, 1, NULL, 'p', NULL, 'user', '2026-07-07 14:26:20', 0, 0, 0),
(3, 1, 1, 'apa guys', NULL, 'admin', '2026-07-07 14:28:32', 0, 0, 0),
(4, 1, NULL, 'p', NULL, 'user', '2026-07-07 14:31:50', 0, 0, 0),
(5, 1, NULL, 'oi', NULL, 'user', '2026-07-08 03:41:47', 0, 0, 0),
(6, 6, NULL, 'p', NULL, 'user', '2026-07-08 03:42:51', 0, 0, 0),
(7, 6, 1, 'oi', NULL, 'admin', '2026-07-08 03:55:08', 0, 0, 0),
(8, 1, 1, 'siap', NULL, 'admin', '2026-07-08 08:05:42', 0, 0, 0),
(9, 1, NULL, 'knjn', NULL, 'user', '2026-07-08 09:08:07', 0, 0, 0),
(10, 7, NULL, 'info rtp', NULL, 'user', '2026-07-08 10:01:03', 0, 0, 0),
(11, 7, 1, 'ewe', NULL, 'admin', '2026-07-08 10:01:34', 0, 0, 0),
(12, 7, NULL, 'gas', NULL, 'user', '2026-07-08 10:02:12', 0, 0, 0),
(13, 1, NULL, 'EWEAN YU', NULL, 'user', '2026-07-08 17:20:59', 0, 0, 0),
(14, 1, NULL, 'ewe', NULL, 'user', '2026-07-08 17:21:04', 0, 0, 0),
(15, 1, NULL, 'ppp', NULL, 'user', '2026-07-22 05:23:27', 0, 0, 0),
(16, 1, 1, 'oi', NULL, 'admin', '2026-07-22 05:41:05', 0, 0, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_keranjang`
--

CREATE TABLE `detail_keranjang` (
  `id_detail` int(11) NOT NULL,
  `id_keranjang` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `warna` varchar(50) DEFAULT NULL,
  `tipe` varchar(100) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_keranjang`
--

INSERT INTO `detail_keranjang` (`id_detail`, `id_keranjang`, `id_produk`, `warna`, `tipe`, `jumlah`, `subtotal`) VALUES
(6, 2, 2, 'Black', 'Standar', 1, 9799000.00),
(14, 1, 2, 'Black', 'Standar', 1, 9799000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `warna` varchar(50) DEFAULT NULL,
  `tipe` varchar(100) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `harga` decimal(12,2) DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_produk`, `warna`, `tipe`, `jumlah`, `harga`, `subtotal`) VALUES
(2, 2, 9, NULL, NULL, 2, 1299000.00, 2598000.00),
(3, 3, 4, NULL, NULL, 1, 7499000.00, 7499000.00),
(4, 4, 11, NULL, NULL, 1, 12999000.00, 12999000.00),
(7, 7, 4, 'Black', 'Standar', 1, 7499000.00, 7499000.00),
(8, 8, 2, 'Black', 'Standar', 1, 9799000.00, 9799000.00),
(10, 10, 2, 'Black', 'Standar', 1, 9799000.00, 9799000.00),
(12, 12, 2, 'White', 'Standar', 1, 9799000.00, 9799000.00),
(13, 12, 3, 'Pink', 'Standar', 1, 22499000.00, 22499000.00),
(15, 13, 6, 'Pink', 'Standar', 1, 2999000.00, 2999000.00),
(16, 14, 3, 'Blue', 'Standar', 1, 22499000.00, 22499000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `gambar`, `deskripsi`) VALUES
(1, 'Laptopp', 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=100&q=80', ''),
(2, 'Smartwatch', 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=100&q=80', 'Jam tangan pintar pendukung aktivitas dan olahraga.'),
(3, 'CCTP', 'https://images.unsplash.com/photo-1557324218-8f35035b6c31?auto=format&fit=crop&w=100&q=80', ''),
(4, 'Mouse', 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=100&q=80', 'Mouse gaming dan produktivitas ergonomis.'),
(5, 'Smart TV', 'https://images.unsplash.com/photo-1593359677879-a4bb92f4834c?auto=format&fit=crop&w=100&q=80', 'Televisi pintar layar lebar dengan konektivitas internet.'),
(8, 'leptop', NULL, NULL),
(9, 'Kontol', NULL, '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int(11) NOT NULL,
  `id_pembeli` int(11) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `keranjang`
--

INSERT INTO `keranjang` (`id_keranjang`, `id_pembeli`, `tanggal`) VALUES
(1, 1, '2026-07-02 01:55:52'),
(2, 6, '2026-07-07 20:42:39'),
(3, 7, '2026-07-08 02:28:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int(11) NOT NULL,
  `id_pembeli` int(11) DEFAULT NULL,
  `judul` varchar(200) DEFAULT NULL,
  `isi` text DEFAULT NULL,
  `tipe` varchar(50) DEFAULT 'info',
  `status_baca` varchar(20) DEFAULT 'belum',
  `tanggal` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id_notifikasi`, `id_pembeli`, `judul`, `isi`, `tipe`, `status_baca`, `tanggal`) VALUES
(1, 1, 'Pesanan Berhasil Dibuat', 'Pesanan Anda #ORD-20260624-0001 telah berhasil dibuat. Silakan lakukan pembayaran segera.', 'pesanan', 'sudah', '2026-06-24 10:16:00'),
(2, 1, 'Pesanan Selesai', 'Pesanan #ORD-20260620-0002 telah selesai. Terima kasih telah berbelanja di SmartRetail!', 'pesanan', 'sudah', '2026-06-22 18:00:00'),
(3, 2, 'Pesanan Sedang Dikirim', 'Pesanan Anda #ORD-20260625-0003 sedang dalam pengiriman via J&T Express. Resi: JT-881234567890', 'pengiriman', 'belum', '2026-06-25 12:00:00'),
(4, 3, 'Pesanan Sedang Diproses', 'Pesanan Anda #ORD-20260626-0004 sedang diproses oleh tim kami. Estimasi pengiriman 1-2 hari kerja.', 'pesanan', 'belum', '2026-06-26 17:00:00'),
(5, 1, 'Pesanan Dibuat', 'Pesanan dengan ID #5 berhasil dibuat. Silakan lakukan pembayaran senilai Rp 24.999.000.', 'info', 'sudah', '2026-07-02 08:56:54'),
(6, 1, 'Pembayaran Dikirim', 'Bukti pembayaran untuk pesanan ID #5 telah diunggah. Menunggu verifikasi dari admin.', 'info', 'sudah', '2026-07-02 08:57:46'),
(7, 1, 'Pembayaran Diterima', 'Pembayaran untuk pesanan ID #5 telah disetujui. Pesanan Anda sedang diproses.', 'info', 'sudah', '2026-07-02 10:01:03'),
(8, 1, 'Pesanan Dibuat', 'Pesanan dengan ID #6 berhasil dibuat. Silakan lakukan pembayaran senilai Rp 24.999.000.', 'info', 'sudah', '2026-07-02 13:45:52'),
(9, 1, 'Pembayaran Dikirim', 'Bukti pembayaran untuk pesanan ID #6 telah diunggah. Menunggu verifikasi dari admin.', 'info', 'sudah', '2026-07-02 13:45:59'),
(10, 1, 'Pesan dari Admin', 'Customer service telah membalas chat Anda.', 'info', 'sudah', '2026-07-07 14:28:32'),
(11, 1, 'Pesanan Dibuat', 'Pesanan dengan ID #7 berhasil dibuat. Silakan lakukan pembayaran senilai Rp 7.499.000.', 'info', 'sudah', '2026-07-07 14:32:14'),
(12, 1, 'Pembayaran Dikirim', 'Bukti pembayaran untuk pesanan ID #7 telah diunggah. Menunggu verifikasi dari admin.', 'info', 'sudah', '2026-07-07 14:32:40'),
(13, 1, 'Pesanan Dibuat', 'Pesanan dengan ID #8 berhasil dibuat. Silakan lakukan pembayaran senilai Rp 9.799.000.', 'info', 'sudah', '2026-07-08 03:33:53'),
(14, 1, 'Pembayaran Dikirim', 'Bukti pembayaran untuk pesanan ID #8 telah diunggah. Menunggu verifikasi dari admin.', 'info', 'sudah', '2026-07-08 03:34:14'),
(15, 1, 'Pembayaran Dikirim', 'Bukti pembayaran untuk pesanan ID #8 telah diunggah. Menunggu verifikasi dari admin.', 'info', 'sudah', '2026-07-08 03:34:31'),
(16, 1, 'Pesanan Dibuat', 'Pesanan dengan ID #9 berhasil dibuat. Silakan lakukan pembayaran senilai Rp 24.999.000.', 'info', 'sudah', '2026-07-08 03:36:47'),
(17, 1, 'Pembayaran Dikirim', 'Bukti pembayaran untuk pesanan ID #9 telah diunggah. Menunggu verifikasi dari admin.', 'info', 'sudah', '2026-07-08 03:37:48'),
(18, 6, 'Pesan dari Admin', 'Customer service telah membalas chat Anda.', 'info', 'belum', '2026-07-08 03:55:08'),
(19, 1, 'Status Pesanan Diperbarui', 'Status pesanan Anda dengan ID #9 telah diperbarui menjadi \"Diproses\".', 'info', 'sudah', '2026-07-08 04:02:26'),
(20, 1, 'Pembayaran Diterima', 'Pembayaran untuk pesanan ID #9 telah disetujui. Pesanan Anda sedang diproses.', 'pembayaran', 'sudah', '2026-07-08 04:03:55'),
(21, 1, 'Pembayaran Ditolak', 'Pembayaran untuk pesanan ID #7 ditolak. Silakan unggah bukti pembayaran yang valid.', 'pembayaran', 'sudah', '2026-07-08 04:04:03'),
(22, 1, 'Pesan dari Admin', 'Customer service telah membalas chat Anda.', 'info', 'sudah', '2026-07-08 08:05:42'),
(23, 1, 'Pesanan Dibuat', 'Pesanan dengan ID #10 berhasil dibuat. Silakan lakukan pembayaran senilai Rp 9.799.000.', 'info', 'sudah', '2026-07-08 08:13:26'),
(24, 1, 'Pembayaran Dikirim', 'Bukti pembayaran untuk pesanan ID #10 telah diunggah. Menunggu verifikasi dari admin.', 'info', 'sudah', '2026-07-08 08:13:34'),
(25, 1, 'Pesanan Dibuat', 'Pesanan dengan ID #11 berhasil dibuat. Silakan lakukan pembayaran senilai Rp 24.999.000.', 'info', 'sudah', '2026-07-08 08:14:09'),
(26, 1, 'Pembayaran Dikirim', 'Bukti pembayaran untuk pesanan ID #11 telah diunggah. Menunggu verifikasi dari admin.', 'info', 'sudah', '2026-07-08 08:18:37'),
(27, 1, 'Status Pesanan Diperbarui', 'Status pesanan Anda dengan ID #11 telah diperbarui menjadi \"Dikirim\".', 'pesanan', 'sudah', '2026-07-08 08:26:12'),
(28, 1, 'Pesanan Dibuat', 'Pesanan dengan ID #12 berhasil dibuat. Silakan lakukan pembayaran senilai Rp 32.298.000.', 'info', 'sudah', '2026-07-08 08:33:55'),
(29, 1, 'Pembayaran Diterima', 'Pembayaran untuk pesanan ID #11 telah disetujui. Pesanan Anda sedang diproses.', 'pembayaran', 'sudah', '2026-07-08 08:37:34'),
(30, 1, 'Pembayaran Diterima', 'Pembayaran untuk pesanan ID #11 telah disetujui. Pesanan Anda sedang diproses.', 'pembayaran', 'sudah', '2026-07-08 08:37:56'),
(31, 1, 'Status Pesanan Diperbarui', 'Status pesanan Anda dengan ID #12 telah diperbarui menjadi \"Diproses\".', 'pesanan', 'sudah', '2026-07-08 08:38:29'),
(32, 1, 'Status Pesanan Diperbarui', 'Status pesanan Anda dengan ID #12 telah diperbarui menjadi \"Selesai\".', 'pesanan', 'sudah', '2026-07-08 08:50:10'),
(33, 1, 'Pesanan Dibuat', 'Pesanan dengan ID #13 berhasil dibuat. Silakan lakukan pembayaran senilai Rp 27.998.000.', 'info', 'sudah', '2026-07-08 09:02:52'),
(34, 1, 'Pembayaran Dikirim', 'Bukti pembayaran untuk pesanan ID #13 telah diunggah. Menunggu verifikasi dari admin.', 'info', 'sudah', '2026-07-08 09:03:01'),
(35, 1, 'Status Pesanan Diperbarui', 'Status pesanan Anda dengan ID #13 telah diperbarui menjadi \"Dikirim\".', 'pesanan', 'sudah', '2026-07-08 09:03:29'),
(36, 1, 'Status Pesanan Diperbarui', 'Status pesanan Anda dengan ID #13 telah diperbarui menjadi \"Diproses\".', 'pesanan', 'sudah', '2026-07-08 09:03:57'),
(37, 7, 'Pesanan Dibuat', 'Pesanan dengan ID #14 berhasil dibuat. Silakan lakukan pembayaran senilai Rp 22.499.000.', 'info', 'belum', '2026-07-08 09:39:59'),
(38, 7, 'Pembayaran Dikirim', 'Bukti pembayaran untuk pesanan ID #14 telah diunggah. Menunggu verifikasi dari admin.', 'info', 'belum', '2026-07-08 09:40:07'),
(39, 7, 'Status Pesanan Diperbarui', 'Status pesanan Anda dengan ID #14 telah diperbarui menjadi \"Dikirim\".', 'pesanan', 'belum', '2026-07-08 09:42:08'),
(40, 7, 'Pesan dari Admin', 'Customer service telah membalas chat Anda.', 'info', 'belum', '2026-07-08 10:01:34'),
(41, 1, 'Status Pesanan Diperbarui', 'Status pesanan Anda dengan ID #13 telah diperbarui menjadi \"Dikirim\".', 'pesanan', 'sudah', '2026-07-22 05:36:25'),
(42, 1, 'Status Pesanan Diperbarui', 'Status pesanan Anda dengan ID #13 telah diperbarui menjadi \"Selesai\".', 'pesanan', 'sudah', '2026-07-22 05:37:19'),
(43, 1, 'Pesan dari Admin', 'Customer service telah membalas chat Anda.', 'info', 'sudah', '2026-07-22 05:41:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi_admin`
--

CREATE TABLE `notifikasi_admin` (
  `id_notifikasi` int(11) NOT NULL,
  `judul` varchar(200) DEFAULT NULL,
  `isi` text DEFAULT NULL,
  `tipe` enum('pesanan','pembayaran','pengiriman','keranjang','ulasan','umum') NOT NULL DEFAULT 'umum',
  `id_referensi` int(11) DEFAULT NULL,
  `status_baca` varchar(20) DEFAULT 'belum',
  `tanggal` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi_admin`
--

INSERT INTO `notifikasi_admin` (`id_notifikasi`, `judul`, `isi`, `tipe`, `id_referensi`, `status_baca`, `tanggal`) VALUES
(1, 'Pembayaran Baru Diterima', 'Budi Santoso melakukan pembayaran untuk pesanan #ORD-20260620-0002 via Transfer Bank BCA sebesar Rp 1.743.000', 'pembayaran', 1, 'sudah', '2026-06-20 15:00:00'),
(2, 'Pesanan Baru Masuk', 'Dewi Lestari membuat pesanan baru #ORD-20260625-0003 - Apple Watch Series 9 GPS senilai Rp 7.499.000', 'pesanan', 3, 'sudah', '2026-06-25 09:30:00'),
(3, 'Pembayaran Baru Diterima', 'Dewi Lestari melakukan pembayaran untuk pesanan #ORD-20260625-0003 via GoPay sebesar Rp 7.599.000', 'pembayaran', 2, 'sudah', '2026-06-25 10:00:00'),
(4, 'Pesanan Baru Masuk', 'Andi Wijaya membuat pesanan baru #ORD-20260626-0004 - Samsung 55\" Neo QLED senilai Rp 12.999.000', 'pesanan', 4, 'sudah', '2026-06-26 16:45:00'),
(5, 'Pembayaran Baru Diterima', 'Andi Wijaya melakukan pembayaran untuk pesanan #ORD-20260626-0004 via Transfer Bank Mandiri sebesar Rp 12.999.000', 'pembayaran', 3, 'sudah', '2026-06-26 17:00:00'),
(6, 'Chat Baru dari Pelanggan', 'Budi Santoso: \"Halo, apakah produk ROG Strix masih tersedia? Saya tertarik membelinya hari ini.\"', '', 1, 'sudah', '2026-06-27 08:15:00'),
(7, 'Pesanan Baru Masuk', 'Budi Santoso membuat pesanan baru #ORD-20260624-0001 - ASUS ROG Strix G16 senilai Rp 24.999.000', 'pesanan', 1, 'sudah', '2026-06-24 10:15:00'),
(8, 'Produk Dimasukkan ke Keranjang', 'Riko Pratama menambahkan Samsung 55\" Neo QLED 4K ke keranjang belanja', 'keranjang', 5, 'sudah', '2026-06-27 14:30:00'),
(9, 'Pesanan Baru Masuk', 'Pembeli Budi Santoso telah melakukan checkout pesanan baru dengan ID #5 senilai Rp 24.999.000.', 'pesanan', 5, 'sudah', '2026-07-02 08:56:55'),
(10, 'Bukti Pembayaran Masuk', 'Pembeli Budi Santoso telah mengunggah bukti pembayaran untuk pesanan ID #5.', 'pembayaran', 5, 'sudah', '2026-07-02 08:57:46'),
(11, 'Pesanan Baru Masuk', 'Pembeli Budi Santoso telah melakukan checkout pesanan baru dengan ID #6 senilai Rp 24.999.000.', 'pesanan', 6, 'sudah', '2026-07-02 13:45:52'),
(12, 'Bukti Pembayaran Masuk', 'Pembeli Budi Santoso telah mengunggah bukti pembayaran untuk pesanan ID #6.', 'pembayaran', 6, 'sudah', '2026-07-02 13:45:59'),
(13, 'Pesan Baru Masuk', 'Anda mendapatkan pesan baru dari Budi Santoso.', '', 1, 'sudah', '2026-07-07 14:26:16'),
(14, 'Pesan Baru Masuk', 'Anda mendapatkan pesan baru dari Budi Santoso.', '', 1, 'sudah', '2026-07-07 14:26:20'),
(15, 'Pesan Baru Masuk', 'Anda mendapatkan pesan baru dari Budi Santoso.', '', 1, 'sudah', '2026-07-07 14:31:50'),
(16, 'Pesanan Baru Masuk', 'Pembeli Budi Santoso telah melakukan checkout pesanan baru dengan ID #7 senilai Rp 7.499.000.', 'pesanan', 7, 'sudah', '2026-07-07 14:32:14'),
(17, 'Bukti Pembayaran Masuk', 'Pembeli Budi Santoso telah mengunggah bukti pembayaran untuk pesanan ID #7.', 'pembayaran', 7, 'sudah', '2026-07-07 14:32:40'),
(18, 'Pesanan Baru Masuk', 'Pembeli Budi Santoso telah melakukan checkout pesanan baru dengan ID #8 senilai Rp 9.799.000.', 'pesanan', 8, 'sudah', '2026-07-08 03:33:53'),
(19, 'Bukti Pembayaran Masuk', 'Pembeli Budi Santoso telah mengunggah bukti pembayaran untuk pesanan ID #8.', 'pembayaran', 8, 'sudah', '2026-07-08 03:34:14'),
(20, 'Bukti Pembayaran Masuk', 'Pembeli Budi Santoso telah mengunggah bukti pembayaran untuk pesanan ID #8.', 'pembayaran', 8, 'sudah', '2026-07-08 03:34:31'),
(21, 'Pesanan Baru Masuk', 'Pembeli Budi Santoso telah melakukan checkout pesanan baru dengan ID #9 senilai Rp 24.999.000.', 'pesanan', 9, 'sudah', '2026-07-08 03:36:47'),
(22, 'Bukti Pembayaran Masuk', 'Pembeli Budi Santoso telah mengunggah bukti pembayaran untuk pesanan ID #9.', 'pembayaran', 9, 'sudah', '2026-07-08 03:37:48'),
(23, 'Pesan Baru Masuk', 'Anda mendapatkan pesan baru dari Budi Santoso.', '', 1, 'sudah', '2026-07-08 03:41:47'),
(24, 'Pesan Baru Masuk', 'Anda mendapatkan pesan baru dari gerardy.', '', 6, 'sudah', '2026-07-08 03:42:55'),
(25, 'Pesanan Baru Masuk', 'Pembeli Budi Santoso telah melakukan checkout pesanan baru dengan ID #10 senilai Rp 9.799.000.', 'pesanan', 10, 'sudah', '2026-07-08 08:13:26'),
(26, 'Bukti Pembayaran Masuk', 'Pembeli Budi Santoso telah mengunggah bukti pembayaran untuk pesanan ID #10.', 'pembayaran', 10, 'sudah', '2026-07-08 08:13:34'),
(27, 'Pesanan Baru Masuk', 'Pembeli Budi Santoso telah melakukan checkout pesanan baru dengan ID #11 senilai Rp 24.999.000.', 'pesanan', 11, 'sudah', '2026-07-08 08:14:09'),
(28, 'Bukti Pembayaran Masuk', 'Pembeli Budi Santoso telah mengunggah bukti pembayaran untuk pesanan ID #11.', 'pembayaran', 11, 'sudah', '2026-07-08 08:18:37'),
(29, 'Pesanan Baru Masuk', 'Pembeli Budi Santosaaa telah melakukan checkout pesanan baru dengan ID #12 senilai Rp 32.298.000.', 'pesanan', 12, 'sudah', '2026-07-08 08:33:55'),
(30, 'Pesanan Baru Masuk', 'Pembeli Budi Santosaaa telah melakukan checkout pesanan baru dengan ID #13 senilai Rp 27.998.000.', 'pesanan', 13, 'sudah', '2026-07-08 09:02:52'),
(31, 'Bukti Pembayaran Masuk', 'Pembeli Budi Santosaaa telah mengunggah bukti pembayaran untuk pesanan ID #13.', 'pembayaran', 13, 'sudah', '2026-07-08 09:03:01'),
(32, 'Pesan Baru Masuk', 'Anda mendapatkan pesan baru dari Budi Santosaaa.', '', 1, 'sudah', '2026-07-08 09:08:08'),
(33, 'Pesanan Baru Masuk', 'Pembeli aim telah melakukan checkout pesanan baru dengan ID #14 senilai Rp 22.499.000.', 'pesanan', 14, 'sudah', '2026-07-08 09:39:59'),
(34, 'Bukti Pembayaran Masuk', 'Pembeli aim telah mengunggah bukti pembayaran untuk pesanan ID #14.', 'pembayaran', 14, 'sudah', '2026-07-08 09:40:08'),
(35, 'Pesan Baru Masuk', 'Anda mendapatkan pesan baru dari aim.', '', 7, 'sudah', '2026-07-08 10:01:03'),
(36, 'Pesan Baru Masuk', 'Anda mendapatkan pesan baru dari aim.', '', 7, 'sudah', '2026-07-08 10:02:12'),
(37, 'Pesan Baru Masuk', 'Anda mendapatkan pesan baru dari Budi Santosaaa.', '', 1, 'sudah', '2026-07-08 17:20:59'),
(38, 'Pesan Baru Masuk', 'Anda mendapatkan pesan baru dari Budi Santosaaa.', '', 1, 'sudah', '2026-07-08 17:21:04'),
(39, 'Pesan Baru Masuk', 'Anda mendapatkan pesan baru dari Budi Santosaaa.', '', 1, 'sudah', '2026-07-22 05:23:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_pesanan` int(11) DEFAULT NULL,
  `metode` varchar(50) DEFAULT NULL,
  `jumlah_bayar` decimal(12,2) DEFAULT NULL,
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `tanggal_bayar` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_pesanan`, `metode`, `jumlah_bayar`, `bukti_bayar`, `tanggal_bayar`, `status`) VALUES
(1, 2, 'Transfer Bank BCA', 1743000.00, NULL, '2026-06-20 15:00:00', 'Lunas'),
(2, 3, 'GoPay', 7599000.00, NULL, '2026-06-25 10:00:00', 'Lunas'),
(3, 4, 'Transfer Bank Mandiri', 12999000.00, NULL, '2026-06-26 17:00:00', 'Lunas'),
(4, 5, 'QRIS', 24999000.00, 'uploads/payments/1782982666_6e0a1c0c65cb17fdc89d.jpg', '2026-07-02 08:57:46', 'Disetujui'),
(5, 6, 'QRIS', 24999000.00, 'uploads/payments/qris-auto-verified.png', '2026-07-02 13:45:58', 'Lunas'),
(6, 7, 'Transfer Bank', 7499000.00, 'uploads/payments/1783434760_a2f3f424ed0c674aa2c4.jpg', '2026-07-07 14:32:40', 'Ditolak'),
(8, 8, 'QRIS', 9799000.00, 'uploads/payments/qris-auto-verified.png', '2026-07-08 03:34:31', 'Lunas'),
(9, 9, 'Transfer Bank', 24999000.00, 'uploads/payments/1783481868_98d23ce8a5ab3b036f28.jpg', '2026-07-08 03:37:48', 'Disetujui'),
(10, 10, 'QRIS', 9799000.00, 'uploads/payments/qris-auto-verified.png', '2026-07-08 08:13:34', 'Lunas'),
(11, 11, 'Transfer Bank', 24999000.00, 'uploads/payments/1783498717_fd946918bb0e9671d2e2.jpg', '2026-07-08 08:18:37', 'Disetujui'),
(12, 13, 'QRIS', 27998000.00, 'uploads/payments/qris-auto-verified.png', '2026-07-08 09:03:01', 'Lunas'),
(13, 14, 'QRIS', 22499000.00, 'uploads/payments/qris-auto-verified.png', '2026-07-08 09:40:07', 'Lunas');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembeli`
--

CREATE TABLE `pembeli` (
  `id_pembeli` int(11) NOT NULL,
  `nama_pembeli` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `tanggal_daftar` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'aktif',
  `halaman_utama` varchar(100) DEFAULT 'home.php'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembeli`
--

INSERT INTO `pembeli` (`id_pembeli`, `nama_pembeli`, `email`, `password`, `no_hp`, `foto_profil`, `tanggal_daftar`, `status`, `halaman_utama`) VALUES
(1, 'Budi Santosaaa', 'budi54@gmail.com', 'budi123', '81234567890', NULL, '2026-06-01', 'aktif', 'pesanan.php'),
(2, 'Dewi Lestari', 'dewi@gmail.com', 'dewi123', '089988776655', NULL, '2026-06-10', 'aktif', 'home.php'),
(3, 'Andi Wijaya', 'andi@outlook.com', 'andi123', '082211443355', NULL, '2026-06-15', 'nonaktif', 'home.php'),
(5, 'Riko Pratama', 'riko@gmail.com', 'riko123', '085599887766', NULL, '2026-06-20', 'aktif', 'home.php'),
(6, 'gerardy', 'gerardybainurizzky@gmail.com', '$2y$10$AyMNiSiGd.HwaNF7.NlnxORT6QvFhLpc2O8O8fgbOJU9ii8tanhBK', '085721568054', NULL, '2026-07-08', 'aktif', 'home.php'),
(7, 'aim fatur', 'aim12@outlook.com', '$2y$10$QFG9ukBhu81.j7sar/8g.ukcC8RPNX6hc.JI89A/DCfrQ.ss82Uvi', '87998765434', NULL, '2026-07-08', 'aktif', 'home.php');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengiriman`
--

CREATE TABLE `pengiriman` (
  `id_pengiriman` int(11) NOT NULL,
  `id_pesanan` int(11) DEFAULT NULL,
  `ekspedisi` varchar(100) DEFAULT NULL,
  `nomor_resi` varchar(100) DEFAULT NULL,
  `status_pengiriman` varchar(50) DEFAULT NULL,
  `estimasi_tiba` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengiriman`
--

INSERT INTO `pengiriman` (`id_pengiriman`, `id_pesanan`, `ekspedisi`, `nomor_resi`, `status_pengiriman`, `estimasi_tiba`) VALUES
(1, 2, 'JNE Reguler', 'JN-992012019920', 'Terkirim', '2026-06-22'),
(2, 3, 'J&T Express', 'JT-881234567890', 'Dalam Perjalanan', '2026-06-28'),
(3, 4, 'SiCepat REG', 'SC-771122334455', 'Sedang Diproses', '2026-06-30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_pembeli` int(11) DEFAULT NULL,
  `id_alamat` int(11) DEFAULT NULL,
  `tanggal_pesanan` datetime DEFAULT NULL,
  `total_harga` decimal(12,2) DEFAULT NULL,
  `status_pesanan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_pembeli`, `id_alamat`, `tanggal_pesanan`, `total_harga`, `status_pesanan`) VALUES
(1, 1, 1, '2026-06-24 10:15:30', 25044000.00, 'Menunggu pembayaran'),
(2, 1, 1, '2026-06-20 14:22:11', 1743000.00, 'Selesai'),
(3, 2, 2, '2026-06-25 09:30:00', 7599000.00, 'Dikirim'),
(4, 3, NULL, '2026-06-26 16:45:00', 12999000.00, 'Diproses'),
(5, 1, 1, '2026-07-02 08:56:54', 24999000.00, 'Diproses'),
(6, 1, 1, '2026-07-02 13:45:52', 24999000.00, 'Diproses'),
(7, 1, 1, '2026-07-07 14:32:14', 7499000.00, 'Dibatalkan'),
(8, 1, 1, '2026-07-08 03:33:50', 9799000.00, 'Diproses'),
(9, 1, 1, '2026-07-08 03:36:47', 24999000.00, 'Diproses'),
(10, 1, 1, '2026-07-08 08:13:26', 9799000.00, 'Diproses'),
(11, 1, 1, '2026-07-08 08:14:09', 24999000.00, 'Diproses'),
(12, 1, 1, '2026-07-08 08:33:55', 32298000.00, 'Selesai'),
(13, 1, 1, '2026-07-08 09:02:51', 27998000.00, 'Selesai'),
(14, 7, 6, '2026-07-08 09:39:59', 22499000.00, 'Dikirim');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `id_brand` int(11) DEFAULT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `nama_produk` varchar(150) DEFAULT NULL,
  `harga` decimal(12,2) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `processor` varchar(100) DEFAULT NULL,
  `ram` varchar(50) DEFAULT NULL,
  `storage` varchar(50) DEFAULT NULL,
  `gpu` varchar(100) DEFAULT NULL,
  `layar` varchar(50) DEFAULT NULL,
  `garansi` varchar(50) DEFAULT NULL,
  `spesifikasi` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`spesifikasi`)),
  `baterai` varchar(150) DEFAULT NULL,
  `berat` varchar(50) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `konektivitas` text DEFAULT NULL,
  `kamera` varchar(150) DEFAULT NULL,
  `resolusi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `id_brand`, `id_kategori`, `nama_produk`, `harga`, `stok`, `gambar`, `deskripsi`, `processor`, `ram`, `storage`, `gpu`, `layar`, `garansi`, `spesifikasi`, `baterai`, `berat`, `os`, `konektivitas`, `kamera`, `resolusi`) VALUES
(2, 5, 1, 'HP Pavilion 14 Ryzen 5 2024 Edition', 9799000.00, 8, 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=400&q=80', 'Laptop tipis dan ringan bertenaga AMD Ryzen 5 ideal untuk produktivitas harian dan pelajar.', 'AMD Ryzen 5 7530U (2.0GHz - 4.5GHz, 6C/12T)', '8GB DDR4 3200MHz', '512GB NVMe SSD PCIe Gen3', 'AMD Radeon 660M Graphics', '14-inch FHD IPS 250nits Anti-glare', '2 Tahun Garansi Resmi HP', NULL, '', '', '', '', '', ''),
(3, 10, 1, 'Lenovo ThinkPad X1 Carbon Gen 11', 22499000.00, 1, 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?auto=format&fit=crop&w=400&q=80', 'Laptop bisnis premium ultra-tipis dengan layar OLED dan security berlapis untuk profesional.', 'Intel Core i7-1365U (1.8GHz - 5.2GHz, 10C/12T)', '16GB LPDDR5 6400MHz (Soldered)', '512GB SSD PCIe Gen4 Performance', 'Intel Iris Xe Graphics', '14-inch 2.8K OLED 90Hz HDR500', '3 Tahun Garansi Resmi Lenovo', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 2, 2, 'Apple Watch Series 9 GPS 45mm', 7499000.00, 11, 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=400&q=80', 'Apple Watch Series 9 hadir dengan S9 chip baru, fitur Double Tap, dan layar 2000 nits lebih cerah.', 'Apple S9 Dual Core 64-bit', '1GB RAM', '64GB Internal', 'Apple GPU (4-core)', '45mm Always-On Retina LTPO OLED', '1 Tahun Garansi Resmi iBox', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 7, 2, 'Samsung Galaxy Watch 6 Classic 47mm', 5499000.00, 9, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80', 'Galaxy Watch 6 Classic dengan rotating bezel ikonik dan sensor kesehatan paling canggih Samsung.', 'Samsung Exynos W930 Dual Core 1.4GHz', '2GB RAM', '16GB Internal', 'Qualcomm Adreno GPU', '47mm Super AMOLED 60Hz', '1 Tahun Garansi Resmi Samsung', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 4, 2, 'Xiaomi Watch 2 Pro Stainless Steel', 2999000.00, 14, 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?auto=format&fit=crop&w=400&q=80', 'Smartwatch premium Xiaomi dengan Wear OS dan charging cepat 5 menit untuk 20 jam pemakaian.', 'Qualcomm Snapdragon W5+ Gen 1', '2GB RAM', '32GB Internal', 'Qualcomm Adreno GPU', '1.43-inch AMOLED 326ppi', '1 Tahun Garansi Resmi Xiaomi', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 4, 3, 'Xiaomi Smart Camera C300 2K PTZ', 599000.00, 20, 'https://images.unsplash.com/photo-1557324218-8f35035b6c31?auto=format&fit=crop&w=400&q=80', 'Kamera pengawas pintar dengan resolusi ultra-jernih 2K dan pan-tilt 360 derajat via Mi Home.', '2K (2304x1296) Resolution', '360° PTZ Pan/Tilt', 'F1.4 Large Aperture Lens', 'H.265 / H.264 Video Encoding', 'IR Night Vision 10m', '1 Tahun Garansi Resmi Xiaomi', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 9, 3, 'Sony SRG-A40 4K PTZ Remote Camera', 3999000.00, 4, 'https://images.unsplash.com/photo-1624913503273-5f9c4e980dba?auto=format&fit=crop&w=400&q=80', 'Kamera PTZ 4K profesional Sony untuk ruang pertemuan dan studio broadcasting berkualitas tinggi.', '4K 60fps CMOS Sensor', '12x Optical Zoom', 'Pan: ±170°, Tilt: -30°~90°', 'H.265 / H.264 / MJPEG', 'Wide Dynamic Range', '2 Tahun Garansi Resmi Sony', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 3, 4, 'Logitech G502 X Plus Wireless RGB', 1299000.00, 18, 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=400&q=80', 'Mouse gaming wireless terlaris dengan sensor HERO 25K, bobot adjustable, dan RGB LIGHTSYNC.', 'HERO 25K Sensor (100-25600 DPI)', 'LIGHTSPEED Wireless 1ms', '89 Grams adjustable weight', '13 Programmable Buttons', 'LIGHTSYNC RGB LEDs', '2 Tahun Garansi Resmi Logitech', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 6, 4, 'Razer DeathAdder V3 HyperSpeed', 899000.00, 22, 'https://images.unsplash.com/photo-1629429408209-1f912961dbd8?auto=format&fit=crop&w=400&q=80', 'Mouse gaming wireless ergonomis legendaris Razer dengan sensor Focus Pro 30K terbaru dan ultraringan.', 'Focus Pro 30K Optical Sensor', 'HyperSpeed Wireless (4x faster)', '59 Grams Ultra-Light', '6 Programmable Buttons', 'Asymmetric Ergonomic Design', '2 Tahun Garansi Resmi Razer', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 7, 5, 'Samsung 55\" Neo QLED 4K QN85C', 12999000.00, 6, 'https://images.unsplash.com/photo-1593359677879-a4bb92f4834c?auto=format&fit=crop&w=400&q=80', 'Smart TV Samsung Neo QLED 4K dengan Mini LED backlight dan AI-powered upscaling untuk gambar yang memukau.', 'Neo Quantum Processor 4K', '2GB RAM / 8GB Storage', 'Tizen OS 7.0', '4K 144Hz Neo QLED Panel', '55 inch 3840x2160 VRR/ALLM', '2 Tahun Garansi Resmi Samsung', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 8, 5, 'LG 65\" OLED evo C3 4K Smart TV', 17999000.00, 4, 'https://images.unsplash.com/photo-1611532736597-de2d4265fba3?auto=format&fit=crop&w=400&q=80', 'TV OLED terbaik dunia dari LG dengan panel OLED evo generasi baru dan webOS 23 terintegrasi.', 'α9 AI Processor Gen6', 'Dolby Atmos & Vision IQ', 'webOS 23 Smart Platform', 'OLED evo 120Hz G-Sync', '65 inch 3840x2160 Infinite Contrast', '2 Tahun Garansi Resmi LG', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 12, 8, 'GERAR', 9000000.00, 6, 'uploads/products/1784698078_235827b7b622a2fb9191.png', 'sangat bagys', 'uhuy', '16GB', 'uhuy', 'ubhuy', 'UHUY', '2 tahun', NULL, '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk_foto`
--

CREATE TABLE `produk_foto` (
  `id` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `urutan` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk_warna_stok`
--

CREATE TABLE `produk_warna_stok` (
  `id` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `warna` varchar(50) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `review`
--

CREATE TABLE `review` (
  `id_review` int(11) NOT NULL,
  `id_pembeli` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `komentar` text DEFAULT NULL,
  `foto_review` varchar(255) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `balasan_admin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `review`
--

INSERT INTO `review` (`id_review`, `id_pembeli`, `id_produk`, `rating`, `komentar`, `foto_review`, `tanggal`, `balasan_admin`) VALUES
(2, 2, 4, 4, 'Apple Watch Series 9 keren banget! Fitur double tap memudahkan aktivitas sehari-hari. Recommended!', NULL, '2026-06-18', NULL),
(3, 3, 9, 5, 'Logitech G502 X Plus terbaik! Sensor sangat presisi dan RGB-nya indah. Cocok buat gaming serius.', NULL, '2026-06-20', NULL),
(4, 1, 11, 5, 'Samsung Neo QLED luar biasa! Gambar tajam banget, warna akurat. Worth every penny!', NULL, '2026-06-22', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indeks untuk tabel `alamat`
--
ALTER TABLE `alamat`
  ADD PRIMARY KEY (`id_alamat`),
  ADD KEY `id_pembeli` (`id_pembeli`);

--
-- Indeks untuk tabel `brand`
--
ALTER TABLE `brand`
  ADD PRIMARY KEY (`id_brand`);

--
-- Indeks untuk tabel `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id_chat`),
  ADD KEY `id_pembeli` (`id_pembeli`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `detail_keranjang`
--
ALTER TABLE `detail_keranjang`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_keranjang` (`id_keranjang`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD KEY `id_pembeli` (`id_pembeli`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `id_pembeli` (`id_pembeli`);

--
-- Indeks untuk tabel `notifikasi_admin`
--
ALTER TABLE `notifikasi_admin`
  ADD PRIMARY KEY (`id_notifikasi`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_pesanan` (`id_pesanan`);

--
-- Indeks untuk tabel `pembeli`
--
ALTER TABLE `pembeli`
  ADD PRIMARY KEY (`id_pembeli`);

--
-- Indeks untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD PRIMARY KEY (`id_pengiriman`),
  ADD KEY `id_pesanan` (`id_pesanan`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_pembeli` (`id_pembeli`),
  ADD KEY `id_alamat` (`id_alamat`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `id_brand` (`id_brand`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `produk_foto`
--
ALTER TABLE `produk_foto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `produk_warna_stok`
--
ALTER TABLE `produk_warna_stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id_review`),
  ADD KEY `id_pembeli` (`id_pembeli`),
  ADD KEY `id_produk` (`id_produk`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `alamat`
--
ALTER TABLE `alamat`
  MODIFY `id_alamat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `brand`
--
ALTER TABLE `brand`
  MODIFY `id_brand` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `chat`
--
ALTER TABLE `chat`
  MODIFY `id_chat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `detail_keranjang`
--
ALTER TABLE `detail_keranjang`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT untuk tabel `notifikasi_admin`
--
ALTER TABLE `notifikasi_admin`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `pembeli`
--
ALTER TABLE `pembeli`
  MODIFY `id_pembeli` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  MODIFY `id_pengiriman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `produk_foto`
--
ALTER TABLE `produk_foto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `produk_warna_stok`
--
ALTER TABLE `produk_warna_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `review`
--
ALTER TABLE `review`
  MODIFY `id_review` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `alamat`
--
ALTER TABLE `alamat`
  ADD CONSTRAINT `alamat_ibfk_1` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_keranjang`
--
ALTER TABLE `detail_keranjang`
  ADD CONSTRAINT `detail_keranjang_ibfk_1` FOREIGN KEY (`id_keranjang`) REFERENCES `keranjang` (`id_keranjang`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_keranjang_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD CONSTRAINT `pengiriman_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_brand`) REFERENCES `brand` (`id_brand`) ON DELETE CASCADE,
  ADD CONSTRAINT `produk_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk_foto`
--
ALTER TABLE `produk_foto`
  ADD CONSTRAINT `produk_foto_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk_warna_stok`
--
ALTER TABLE `produk_warna_stok`
  ADD CONSTRAINT `produk_warna_stok_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
