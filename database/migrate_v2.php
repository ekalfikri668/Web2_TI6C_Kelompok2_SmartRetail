<?php
// Migration 2 - Additional patches
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'laptopstore_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
echo "Terhubung ke database.\n";

// 1. Check and update notifikasi_admin tipe column to include 'keranjang' and 'ulasan'
$alterNotifAdmin = "ALTER TABLE `notifikasi_admin` 
    MODIFY COLUMN `tipe` ENUM('pesanan','pembayaran','pengiriman','keranjang','ulasan','umum') NOT NULL DEFAULT 'umum'";
if ($conn->query($alterNotifAdmin)) {
    echo "Tabel `notifikasi_admin` kolom `tipe` berhasil diperbarui.\n";
} else {
    echo "Info `notifikasi_admin`.tipe: " . $conn->error . "\n";
}

// 2. Add foto_profil column to pembeli if not exists
$alterPembeli = "ALTER TABLE `pembeli` 
    ADD COLUMN IF NOT EXISTS `foto_profil` VARCHAR(255) DEFAULT NULL";
if ($conn->query($alterPembeli)) {
    echo "Tabel `pembeli` kolom `foto_profil` berhasil ditambahkan.\n";
} else {
    echo "Info `pembeli`.foto_profil: " . $conn->error . "\n";
}

// 3. Add is_edited and is_deleted to chat if not exists
$alterChat = "ALTER TABLE `chat` 
    ADD COLUMN IF NOT EXISTS `is_edited` TINYINT(1) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `is_deleted` TINYINT(1) DEFAULT 0";
if ($conn->query($alterChat)) {
    echo "Tabel `chat` sudah diperbarui.\n";
} else {
    echo "Info `chat`: " . $conn->error . "\n";
}

// 4. Add balasan_admin to review if not exists
$alterReview = "ALTER TABLE `review` 
    ADD COLUMN IF NOT EXISTS `balasan_admin` TEXT DEFAULT NULL";
if ($conn->query($alterReview)) {
    echo "Tabel `review` sudah diperbarui.\n";
} else {
    echo "Info `review`: " . $conn->error . "\n";
}

// 5. Create produk_warna_stok table
$createWarna = "CREATE TABLE IF NOT EXISTS `produk_warna_stok` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_produk` INT(11) NOT NULL,
    `warna` VARCHAR(50) NOT NULL,
    `stok` INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if ($conn->query($createWarna)) {
    echo "Tabel `produk_warna_stok` siap.\n";
} else {
    echo "Info `produk_warna_stok`: " . $conn->error . "\n";
}

// 6. Create produk_foto table
$createFoto = "CREATE TABLE IF NOT EXISTS `produk_foto` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_produk` INT(11) NOT NULL,
    `foto` VARCHAR(255) NOT NULL,
    `urutan` INT(11) DEFAULT 0,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if ($conn->query($createFoto)) {
    echo "Tabel `produk_foto` siap.\n";
} else {
    echo "Info `produk_foto`: " . $conn->error . "\n";
}

// 6b. Add urutan column to produk_foto if it does not exist
$alterProdukFoto = "ALTER TABLE `produk_foto` ADD COLUMN IF NOT EXISTS `urutan` INT(11) DEFAULT 0";
if ($conn->query($alterProdukFoto)) {
    echo "Kolom `urutan` berhasil dipastikan ada di tabel `produk_foto`.\n";
} else {
    echo "Gagal memperbarui `produk_foto` kolom `urutan`: " . $conn->error . "\n";
}

// 7. Add spesifikasi column to produk if not exists
$alterProdukSpek = "ALTER TABLE `produk` 
    ADD COLUMN IF NOT EXISTS `spesifikasi` JSON DEFAULT NULL";
if ($conn->query($alterProdukSpek)) {
    echo "Tabel `produk` kolom `spesifikasi` berhasil ditambahkan.\n";
} else {
    echo "Info `produk`.spesifikasi: " . $conn->error . "\n";
}

$conn->close();
echo "Migrasi v2 selesai.\n";
