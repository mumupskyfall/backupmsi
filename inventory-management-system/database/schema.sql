-- Database Schema untuk Sistem Manajemen Inventori
-- Dibuat untuk MariaDB/MySQL

-- Buat database
CREATE DATABASE IF NOT EXISTS inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE inventory_db;

-- Tabel Kategori Produk
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Produk
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category_id INT,
    sku VARCHAR(50) UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    quantity INT NOT NULL DEFAULT 0,
    min_stock INT DEFAULT 10,
    image_url VARCHAR(255),
    status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_sku (sku),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Supplier
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Transaksi (untuk tracking stok masuk/keluar)
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    transaction_type ENUM('in', 'out') NOT NULL,
    quantity INT NOT NULL,
    reference_number VARCHAR(50),
    notes TEXT,
    supplier_id INT,
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(100),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_type (transaction_type),
    INDEX idx_date (transaction_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Users untuk autentikasi sederhana
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data sample
INSERT INTO categories (name, description) VALUES
('Elektronik', 'Barang-barang elektronik dan gadget'),
('Makanan', 'Produk makanan dan minuman'),
('Pakaian', 'Pakaian dan aksesoris fashion'),
('Alat Tulis', 'Perlengkapan kantor dan sekolah'),
('Furniture', 'Perabot rumah dan kantor');

INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES
('PT Elektronik Jaya', 'Budi Santoso', 'budi@elektronik.com', '081234567890', 'Jakarta Selatan'),
('CV Makanan Segar', 'Siti Nurhaliza', 'siti@makanan.com', '081234567891', 'Bandung'),
('Toko Fashion Modern', 'Ahmad Rizki', 'ahmad@fashion.com', '081234567892', 'Surabaya');

INSERT INTO products (name, category_id, sku, description, price, quantity, min_stock) VALUES
('Laptop ASUS ROG', 1, 'ELEC-001', 'Laptop gaming high performance', 15000000.00, 25, 5),
('Mouse Wireless Logitech', 1, 'ELEC-002', 'Mouse ergonomis dengan konektivitas wireless', 250000.00, 150, 20),
('Kopi Arabica Premium', 2, 'FOOD-001', 'Kopi arabica berkualitas tinggi', 85000.00, 200, 30),
('Kemeja Formal Pria', 3, 'CLOT-001', 'Kemeja formal untuk acara resmi', 350000.00, 80, 15),
('Pulpen Pilot G2', 4, 'STAT-001', 'Pulpen gel smooth writing', 15000.00, 500, 100),
('Meja Kantor Minimalis', 5, 'FURN-001', 'Meja kerja modern desain minimalis', 1250000.00, 30, 5);

-- Insert default admin user (password: admin123 - hashed dengan password_hash PHP)
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@inventory.com', 'admin');

-- View untuk laporan stok rendah
CREATE OR REPLACE VIEW low_stock_products AS
SELECT 
    p.id,
    p.name,
    p.sku,
    c.name as category_name,
    p.quantity,
    p.min_stock,
    (p.min_stock - p.quantity) as shortage
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.quantity <= p.min_stock AND p.status = 'active'
ORDER BY shortage DESC;

-- View untuk nilai total inventori
CREATE OR REPLACE VIEW inventory_value AS
SELECT 
    c.name as category_name,
    COUNT(p.id) as product_count,
    SUM(p.quantity) as total_items,
    SUM(p.quantity * p.price) as total_value
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.status = 'active'
GROUP BY c.id, c.name;
