<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['token'])) {
    $current_path = $_SERVER['REQUEST_URI'];
    if (strpos($current_path, '/admin/') !== false) {
        $_SESSION['redirect_url'] = $current_path;
        header("Location: ../pages/login.php");
        exit;
    } else {
        
    }
}
