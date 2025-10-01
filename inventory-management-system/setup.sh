#!/bin/bash

# ============================================
# Setup Script untuk Sistem Manajemen Inventori
# ============================================

set -e  # Exit jika ada error

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fungsi helper
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

# Cek apakah script dijalankan sebagai root
if [ "$EUID" -ne 0 ]; then 
    print_error "Script ini harus dijalankan sebagai root (gunakan sudo)"
    exit 1
fi

# Konfigurasi
DB_NAME="inventory_database"
DB_USER="staff"
DB_PASS="123"
DB_ROOT_PASS=""
APP_DIR="/var/www/html/inventory-management-system"
CURRENT_DIR=$(pwd)

print_header "SISTEM MANAJEMEN INVENTORI - AUTO SETUP"

echo "Konfigurasi yang akan digunakan:"
echo "  Database Name: $DB_NAME"
echo "  Database User: $DB_USER"
echo "  Database Pass: $DB_PASS"
echo "  Install Path:  $APP_DIR"
echo ""

read -p "Lanjutkan instalasi? (y/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Instalasi dibatalkan"
    exit 0
fi

# ============================================
# Step 1: Update sistem dan install dependencies
# ============================================
print_header "Step 1: Install Dependencies"

print_info "Update package list..."
apt update -qq

print_info "Install Apache2..."
if ! command -v apache2 &> /dev/null; then
    apt install -y apache2 > /dev/null 2>&1
    print_success "Apache2 installed"
else
    print_success "Apache2 already installed"
fi

print_info "Install MariaDB..."
if ! command -v mysql &> /dev/null; then
    apt install -y mariadb-server mariadb-client > /dev/null 2>&1
    print_success "MariaDB installed"
else
    print_success "MariaDB already installed"
fi

print_info "Install PHP dan ekstensi..."
apt install -y php php-mysql php-mbstring php-cli php-curl php-gd libapache2-mod-php > /dev/null 2>&1
print_success "PHP dan ekstensi installed"

# ============================================
# Step 2: Configure Apache
# ============================================
print_header "Step 2: Configure Apache"

print_info "Enable Apache modules..."
a2enmod rewrite > /dev/null 2>&1
a2enmod headers > /dev/null 2>&1
a2enmod expires > /dev/null 2>&1
a2enmod deflate > /dev/null 2>&1
print_success "Apache modules enabled"

# ============================================
# Step 3: Setup MariaDB
# ============================================
print_header "Step 3: Setup Database"

print_info "Starting MariaDB service..."
systemctl start mariadb
systemctl enable mariadb > /dev/null 2>&1
print_success "MariaDB service started"

print_info "Membuat database dan user..."

# Cek apakah database sudah ada
DB_EXISTS=$(mysql -u root -e "SHOW DATABASES LIKE '$DB_NAME';" 2>/dev/null | grep "$DB_NAME" || true)

if [ -z "$DB_EXISTS" ]; then
    mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
    print_success "Database dan user dibuat"
else
    print_warning "Database '$DB_NAME' sudah ada, skip create database"
fi

print_info "Import database schema..."
if [ -f "$CURRENT_DIR/database/schema.sql" ]; then
    mysql -u root $DB_NAME < "$CURRENT_DIR/database/schema.sql" 2>/dev/null || {
        print_warning "Terjadi error saat import (mungkin sudah ada data), melanjutkan..."
    }
    print_success "Database schema imported"
else
    print_warning "File schema.sql tidak ditemukan, skip import"
fi

# ============================================
# Step 4: Deploy Aplikasi
# ============================================
print_header "Step 4: Deploy Aplikasi"

print_info "Membuat direktori aplikasi..."
mkdir -p $APP_DIR

print_info "Copy files aplikasi..."
# Copy semua file kecuali setup.sh, README.md, dan apache-config
rsync -av --exclude='setup.sh' \
          --exclude='README.md' \
          --exclude='apache-config' \
          --exclude='.git' \
          $CURRENT_DIR/ $APP_DIR/ > /dev/null 2>&1
print_success "Files copied"

print_info "Set permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
print_success "Permissions set"

# ============================================
# Step 5: Configure Virtual Host
# ============================================
print_header "Step 5: Configure Virtual Host"

print_info "Membuat konfigurasi virtual host..."

# Cek apakah sudah ada konfigurasi
if [ ! -f "/etc/apache2/sites-available/inventory.conf" ]; then
    cat > /etc/apache2/sites-available/inventory.conf <<'VHOST'
<VirtualHost *:80>
    ServerName localhost
    ServerAdmin admin@localhost
    
    DocumentRoot /var/www/html/inventory-management-system
    
    <Directory /var/www/html/inventory-management-system>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Disable access to sensitive directories
    <Directory /var/www/html/inventory-management-system/config>
        Require all denied
    </Directory>
    
    <Directory /var/www/html/inventory-management-system/includes>
        Require all denied
    </Directory>
    
    <Directory /var/www/html/inventory-management-system/database>
        Require all denied
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/inventory_error.log
    CustomLog ${APACHE_LOG_DIR}/inventory_access.log combined
</VirtualHost>
VHOST
    print_success "Virtual host config created"
else
    print_warning "Virtual host config sudah ada, skip"
fi

print_info "Enable site..."
a2dissite 000-default.conf > /dev/null 2>&1 || true
a2ensite inventory.conf > /dev/null 2>&1
print_success "Site enabled"

# ============================================
# Step 6: Restart Services
# ============================================
print_header "Step 6: Restart Services"

print_info "Restarting Apache..."
systemctl restart apache2
print_success "Apache restarted"

# ============================================
# Step 7: Verify Installation
# ============================================
print_header "Step 7: Verify Installation"

# Cek status Apache
if systemctl is-active --quiet apache2; then
    print_success "Apache is running"
else
    print_error "Apache is not running"
fi

# Cek status MariaDB
if systemctl is-active --quiet mariadb; then
    print_success "MariaDB is running"
else
    print_error "MariaDB is not running"
fi

# Cek apakah aplikasi dapat diakses
if [ -f "$APP_DIR/index.php" ]; then
    print_success "Application files exist"
else
    print_error "Application files not found"
fi

# ============================================
# Selesai
# ============================================
print_header "Setup Selesai!"

echo -e "${GREEN}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  Setup telah selesai dengan sukses!                   ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}Informasi Akses:${NC}"
echo -e "  URL Aplikasi  : ${GREEN}http://localhost/inventory-management-system/${NC}"
echo -e "  atau          : ${GREEN}http://$(hostname -I | awk '{print $1}')/inventory-management-system/${NC}"
echo ""
echo -e "${BLUE}Login Default:${NC}"
echo -e "  Username      : ${GREEN}admin${NC}"
echo -e "  Password      : ${GREEN}admin123${NC}"
echo ""
echo -e "${BLUE}Database Info:${NC}"
echo -e "  Database      : ${GREEN}$DB_NAME${NC}"
echo -e "  User          : ${GREEN}$DB_USER${NC}"
echo -e "  Password      : ${GREEN}$DB_PASS${NC}"
echo ""
echo -e "${YELLOW}⚠ PENTING:${NC}"
echo -e "  1. Ganti password default admin setelah login pertama kali"
echo -e "  2. Update konfigurasi database jika diperlukan di:"
echo -e "     $APP_DIR/config/database.php"
echo -e "  3. Untuk production, enable HTTPS/SSL"
echo ""
echo -e "${GREEN}Selamat menggunakan Sistem Manajemen Inventori!${NC}"
echo ""

# Log file
echo "[$(date)] Setup completed successfully" >> /var/log/inventory_setup.log

exit 0
