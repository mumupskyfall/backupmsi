<?php
/**
 * File untuk memeriksa autentikasi user
 * Include file ini di setiap halaman yang memerlukan autentikasi
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fungsi untuk cek apakah user adalah admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fungsi untuk cek permission
function checkPermission($required_role = 'staff') {
    if ($required_role === 'admin' && !isAdmin()) {
        die('Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}

// Set timeout session
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_LIFETIME)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();
?>
