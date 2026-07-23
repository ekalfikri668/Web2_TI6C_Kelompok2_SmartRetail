<?php
// Database migration helper for laptopstore_db
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'laptopstore_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

echo "Berhasil terhubung ke database. Mulai migrasi...\n";

// 1. Alter chat table
$alterChat = "ALTER TABLE `chat` 
    ADD COLUMN IF NOT EXISTS `is_edited` TINYINT(1) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `is_deleted` TINYINT(1) DEFAULT 0";
if ($conn->query($alterChat)) {
    echo "Tabel `chat` berhasil diperbarui.\n";
} else {
    echo "Gagal memperbarui tabel `chat`: " . $conn->error . "\n";
}

// 2. Alter review table
$alterReview = "ALTER TABLE `review` 
    ADD COLUMN IF NOT EXISTS `balasan_admin` TEXT DEFAULT NULL";
if ($conn->query($alterReview)) {
    echo "Tabel `review` berhasil diperbarui.\n";
} else {
    echo "Gagal memperbarui tabel `review`: " . $conn->error . "\n";
}

// 3. Create produk_warna_stok table
$createWarnaStok = "CREATE TABLE IF NOT EXISTS `produk_warna_stok` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_produk` INT(11) NOT NULL,
    `warna` VARCHAR(50) NOT NULL,
    `stok` INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if ($conn->query($createWarnaStok)) {
    echo "Tabel `produk_warna_stok` berhasil dibuat.\n";
} else {
    echo "Gagal membuat tabel `produk_warna_stok`: " . $conn->error . "\n";
}

// 4. Create produk_foto table
$createProdukFoto = "CREATE TABLE IF NOT EXISTS `produk_foto` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_produk` INT(11) NOT NULL,
    `foto` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if ($conn->query($createProdukFoto)) {
    echo "Tabel `produk_foto` berhasil dibuat.\n";
} else {
    echo "Gagal membuat tabel `produk_foto`: " . $conn->error . "\n";
}

$conn->close();
echo "Migrasi selesai.\n";
