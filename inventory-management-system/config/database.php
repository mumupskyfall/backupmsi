<?php
/**
 * Konfigurasi Database untuk Sistem Manajemen Inventori
 * File ini berisi konfigurasi koneksi ke MariaDB/MySQL
 */

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'staff');
define('DB_PASS', '123');
define('DB_NAME', 'inventory_db');
define('DB_CHARSET', 'utf8mb4');

// Konfigurasi Aplikasi
define('APP_NAME', 'Sistem Manajemen Inventori');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/inventory-management-system');

// Konfigurasi Session
define('SESSION_LIFETIME', 3600); // 1 jam dalam detik

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $charset = DB_CHARSET;
    private $conn;
    private $error;

    /**
     * Membuat koneksi database
     */
    public function connect() {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
            ];
            
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
            return $this->conn;
            
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database Connection Error: " . $this->error);
            die("Koneksi database gagal. Silakan periksa konfigurasi.");
        }
    }

    /**
     * Mendapatkan error terakhir
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Menutup koneksi database
     */
    public function disconnect() {
        $this->conn = null;
    }
}

/**
 * Function helper untuk mendapatkan koneksi database
 */
function getDB() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database->connect();
}

/**
 * Function helper untuk sanitasi input
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Function helper untuk format rupiah
 */
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

/**
 * Function helper untuk format tanggal Indonesia
 */
function formatTanggal($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

/**
 * Function helper untuk redirect
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Function untuk set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Function untuk get dan clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
