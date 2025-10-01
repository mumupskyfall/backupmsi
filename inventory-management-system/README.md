# Sistem Manajemen Inventori

Aplikasi web berbasis PHP untuk mengelola inventori produk dengan database MariaDB/MySQL dan web server Apache2.

## 📋 Fitur Utama

- **Dashboard Interaktif**: Statistik real-time tentang produk, stok, dan nilai inventori
- **Manajemen Produk**: CRUD lengkap untuk produk dengan kategori
- **Tracking Stok**: Monitor stok rendah dan transaksi masuk/keluar
- **Manajemen Supplier**: Database supplier dengan informasi kontak
- **Laporan**: Laporan inventori dan transaksi
- **Autentikasi User**: Login dengan role admin dan staff
- **Responsive Design**: Tampilan modern dan mobile-friendly

## 🛠️ Teknologi Stack

- **Web Server**: Apache2
- **Database**: MariaDB/MySQL
- **Backend**: PHP 7.4+ dengan PDO
- **Frontend**: HTML5, CSS3, JavaScript
- **Design**: Modern UI dengan custom CSS

## 📦 Struktur Database

- `products` - Data produk inventori
- `categories` - Kategori produk
- `suppliers` - Data supplier
- `transactions` - Riwayat transaksi stok
- `users` - Manajemen user dan autentikasi

## 🚀 Instalasi dan Setup

### Persyaratan Sistem

```bash
- PHP 7.4 atau lebih tinggi
- Apache2
- MariaDB 10.3+ atau MySQL 5.7+
- PHP Extensions: pdo, pdo_mysql, mbstring
```

### Langkah 1: Install Dependencies

```bash
# Update sistem
sudo apt update

# Install Apache2
sudo apt install apache2 -y

# Install MariaDB
sudo apt install mariadb-server mariadb-client -y

# Install PHP dan ekstensi yang diperlukan
sudo apt install php php-mysql php-mbstring php-cli php-curl php-gd -y

# Enable Apache2 modules
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Langkah 2: Setup Database

```bash
# Login ke MariaDB sebagai root
sudo mysql -u root -p

# Atau jika tidak ada password root
sudo mysql
```

Jalankan perintah SQL berikut:

```sql
-- Buat user database
CREATE USER 'staff'@'localhost' IDENTIFIED BY '123';

-- Berikan privileges
GRANT ALL PRIVILEGES ON inventory_db.* TO 'staff'@'localhost';
FLUSH PRIVILEGES;

EXIT;
```

Kemudian import schema database:

```bash
sudo mysql -u root -p < /path/to/database/schema.sql
```

### Langkah 3: Konfigurasi Apache2

Buat virtual host untuk aplikasi:

```bash
sudo nano /etc/apache2/sites-available/inventory.conf
```

Tambahkan konfigurasi berikut:

```apache
<VirtualHost *:80>
    ServerName inventory.local
    ServerAdmin admin@inventory.local
    DocumentRoot /var/www/html/inventory-management-system
    
    <Directory /var/www/html/inventory-management-system>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/inventory_error.log
    CustomLog ${APACHE_LOG_DIR}/inventory_access.log combined
</VirtualHost>
```

Enable site dan restart Apache:

```bash
sudo a2ensite inventory.conf
sudo systemctl restart apache2
```

### Langkah 4: Deploy Aplikasi

```bash
# Copy aplikasi ke document root Apache
sudo cp -r inventory-management-system /var/www/html/

# Set permission yang tepat
sudo chown -R www-data:www-data /var/www/html/inventory-management-system
sudo chmod -R 755 /var/www/html/inventory-management-system
```

### Langkah 5: Konfigurasi Aplikasi

Edit file konfigurasi database:

```bash
sudo nano /var/www/html/inventory-management-system/config/database.php
```

Sesuaikan konfigurasi database:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'inventory_user');
define('DB_PASS', 'password_anda');  // Ganti dengan password yang Anda buat
define('DB_NAME', 'inventory_db');
```

### Langkah 6: Test Aplikasi

Buka browser dan akses:

```
http://localhost/inventory-management-system/
```

atau jika menggunakan virtual host:

```
http://inventory.local/
```

## 🔐 Login Default

```
Username: admin
Password: admin123
```

**⚠️ PENTING**: Segera ubah password default setelah login pertama kali!

## 📁 Struktur Direktori

```
inventory-management-system/
├── assets/
│   └── css/
│       └── style.css          # File CSS utama
├── config/
│   └── database.php           # Konfigurasi database
├── database/
│   └── schema.sql             # Schema dan data sample
├── includes/
│   ├── auth.php               # Autentikasi dan session
│   ├── header.php             # Header template
│   ├── sidebar.php            # Sidebar navigasi
│   └── footer.php             # Footer template
├── index.php                  # Dashboard utama
├── login.php                  # Halaman login
├── logout.php                 # Logout handler
├── products.php               # Manajemen produk
├── categories.php             # Manajemen kategori (coming soon)
├── suppliers.php              # Manajemen supplier (coming soon)
├── transactions.php           # Transaksi stok (coming soon)
├── reports.php                # Laporan (coming soon)
├── users.php                  # Manajemen user (coming soon)
└── README.md                  # Dokumentasi ini
```

## 🔧 Troubleshooting

### Error: "Koneksi database gagal"

Periksa:
- Kredensial database di `config/database.php`
- Service MariaDB berjalan: `sudo systemctl status mariadb`
- User database memiliki privileges yang tepat

### Error: 500 Internal Server Error

Periksa:
- PHP error log: `/var/log/apache2/error.log`
- Permission direktori dan file
- PHP extensions terinstall dengan benar

### Error: Session tidak berfungsi

```bash
# Pastikan direktori session writable
sudo chmod 1733 /var/lib/php/sessions
```

### Halaman blank/kosong

```bash
# Enable PHP error reporting sementara
# Edit php.ini atau tambahkan di awal file PHP:
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 🔒 Keamanan

Untuk production environment, pastikan:

1. **Ganti Password Default**
   - Ubah password admin default segera
   - Gunakan password yang kuat (minimal 12 karakter)

2. **Database Security**
   - Jangan gunakan user root untuk koneksi aplikasi
   - Batasi privileges database user sesuai kebutuhan
   - Backup database secara berkala

3. **File Permissions**
   ```bash
   # File PHP sebaiknya 644
   find /var/www/html/inventory-management-system -type f -name "*.php" -exec chmod 644 {} \;
   
   # Direktori sebaiknya 755
   find /var/www/html/inventory-management-system -type d -exec chmod 755 {} \;
   ```

4. **Disable PHP Error Display**
   ```php
   // Di config/database.php untuk production
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

5. **HTTPS**
   - Gunakan SSL/TLS certificate (Let's Encrypt gratis)
   - Redirect HTTP ke HTTPS

## 📊 Fitur Database Views

Aplikasi ini menggunakan database views untuk reporting:

- **low_stock_products**: View untuk produk dengan stok rendah
- **inventory_value**: View untuk nilai total inventori per kategori

## 🎨 Kustomisasi

### Mengubah Warna Tema

Edit file `assets/css/style.css` bagian `:root`:

```css
:root {
    --primary-color: #2563eb;  /* Warna utama */
    --success-color: #10b981;  /* Warna success */
    --danger-color: #ef4444;   /* Warna danger */
    /* ... */
}
```

### Menambah Fitur Baru

1. Buat file PHP baru di root directory
2. Include `includes/auth.php` untuk proteksi
3. Include `includes/header.php`, `includes/sidebar.php`, dan `includes/footer.php`
4. Tambahkan menu di `includes/sidebar.php`

## 📝 TODO / Roadmap

- [ ] Implementasi halaman Categories (CRUD)
- [ ] Implementasi halaman Suppliers (CRUD)
- [ ] Implementasi halaman Transactions (Input stok masuk/keluar)
- [ ] Implementasi halaman Reports (Laporan PDF/Excel)
- [ ] Implementasi halaman Users Management (admin only)
- [ ] Upload gambar produk
- [ ] Barcode scanner integration
- [ ] Export data ke Excel/CSV
- [ ] Email notification untuk stok rendah
- [ ] Dashboard charts dan grafik
- [ ] API REST untuk mobile app

## 🤝 Kontribusi

Jika ingin berkontribusi ke project ini:

1. Fork repository
2. Buat branch fitur baru
3. Commit perubahan
4. Push ke branch
5. Buat Pull Request

## 📄 Lisensi

Project ini dibuat untuk keperluan edukasi dan dapat digunakan secara bebas.

## 👨‍💻 Author

Dikembangkan menggunakan Cascade AI Assistant

---

**Selamat mencoba! Jika ada pertanyaan atau masalah, jangan ragu untuk menghubungi.**
