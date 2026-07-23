<?php
// Migration v3 - Add new spesifikasi columns to produk table
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'laptopstore_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
echo "Terhubung ke database.\n";

// Add new columns to produk table
$cols = [
    'baterai'     => 'VARCHAR(150) DEFAULT NULL',
    'berat'       => 'VARCHAR(50) DEFAULT NULL',
    'os'          => 'VARCHAR(100) DEFAULT NULL',
    'konektivitas'=> 'TEXT DEFAULT NULL',
    'kamera'      => 'VARCHAR(150) DEFAULT NULL',
    'resolusi'    => 'VARCHAR(100) DEFAULT NULL',
];

foreach ($cols as $col => $def) {
    $sql = "ALTER TABLE `produk` ADD COLUMN IF NOT EXISTS `$col` $def";
    if ($conn->query($sql)) {
        echo "Kolom `$col` berhasil ditambahkan ke tabel `produk`.\n";
    } else {
        echo "Info `produk`.$col: " . $conn->error . "\n";
    }
}

// Also ensure processor, ram, storage, gpu, layar, garansi columns exist
$existingCols = [
    'processor' => 'VARCHAR(200) DEFAULT NULL',
    'ram'       => 'VARCHAR(100) DEFAULT NULL',
    'storage'   => 'VARCHAR(100) DEFAULT NULL',
    'gpu'       => 'VARCHAR(200) DEFAULT NULL',
    'layar'     => 'VARCHAR(150) DEFAULT NULL',
    'garansi'   => 'VARCHAR(100) DEFAULT NULL',
];

foreach ($existingCols as $col => $def) {
    $sql = "ALTER TABLE `produk` ADD COLUMN IF NOT EXISTS `$col` $def";
    if ($conn->query($sql)) {
        echo "Kolom `$col` sudah siap.\n";
    } else {
        echo "Info `produk`.$col: " . $conn->error . "\n";
    }
}

$conn->close();
echo "Migrasi v3 selesai.\n";
