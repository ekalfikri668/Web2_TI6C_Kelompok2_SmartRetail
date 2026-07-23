<?php
require_once __DIR__ . '/cek_login.php';

// Check if user role is admin
$isAdmin = false;
if (isset($_SESSION['user']) && isset($_SESSION['user']['role'])) {
    if ($_SESSION['user']['role'] === 'admin') {
        $isAdmin = true;
    }
}

if (!$isAdmin) {
    // Redirect to home page
    $current_path = $_SERVER['REQUEST_URI'];
    if (strpos($current_path, '/admin/') !== false) {
        $home_url = '../pages/home.php';
    } elseif (strpos($current_path, '/pages/') !== false) {
        $home_url = 'home.php';
    } else {
        $home_url = 'pages/home.php';
    }
    
    header("Location: " . $home_url . "?error=unauthorized");
    exit;
}
