<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/api.php';

header('Content-Type: application/json');

if (!isset($_SESSION['token']) || !isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid.']);
    exit;
}

if (!isset($_FILES['foto_profil']) || $_FILES['foto_profil']['error'] !== UPLOAD_ERR_OK) {
    $errCode = $_FILES['foto_profil']['error'] ?? -1;
    echo json_encode(['success' => false, 'message' => 'Gagal menerima file. Kode error: ' . $errCode]);
    exit;
}

$file = $_FILES['foto_profil'];
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 2 * 1024 * 1024; // 2MB

// Validate type using finfo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP.']);
    exit;
}

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB.']);
    exit;
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$userId = $_SESSION['user']['id_pembeli'] ?? time();
$filename = 'profil_' . $userId . '_' . time() . '.' . strtolower($ext);

$uploadDir = __DIR__ . '/../uploads/profil/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Remove old photo if exists
$oldPhoto = $_SESSION['user']['foto_profil'] ?? '';
if (!empty($oldPhoto)) {
    $oldPath = $uploadDir . basename($oldPhoto);
    if (file_exists($oldPath)) {
        @unlink($oldPath);
    }
}

$targetPath = $uploadDir . $filename;
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file ke server.']);
    exit;
}

// Build relative URL for web access
$photoUrl = '../uploads/profil/' . $filename;

// Update in database via API (using a workaround - direct DB update since we don't have multipart API)
$db = mysqli_connect('localhost', 'root', '', 'laptopstore_db');
if ($db) {
    $safeFile = mysqli_real_escape_string($db, $filename);
    $safeId   = intval($userId);
    mysqli_query($db, "UPDATE pembeli SET foto_profil = '$safeFile' WHERE id_pembeli = $safeId");
    mysqli_close($db);
}

// Update session
$_SESSION['user']['foto_profil'] = $filename;

echo json_encode([
    'success'  => true,
    'message'  => 'Foto profil berhasil diperbarui!',
    'foto_url' => $photoUrl
]);
