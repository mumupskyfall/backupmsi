<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                <a href="index.php">
                    <span class="icon">🏠</span>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-divider">Inventori</li>
            
            <li class="<?php echo $current_page === 'products.php' ? 'active' : ''; ?>">
                <a href="products.php">
                    <span class="icon">📦</span>
                    <span>Produk</span>
                </a>
            </li>
            
            <li class="<?php echo $current_page === 'categories.php' ? 'active' : ''; ?>">
                <a href="categories.php">
                    <span class="icon">📂</span>
                    <span>Kategori</span>
                </a>
            </li>
            
            <li class="nav-divider">Transaksi</li>
            
            <li class="<?php echo $current_page === 'transactions.php' ? 'active' : ''; ?>">
                <a href="transactions.php">
                    <span class="icon">📝</span>
                    <span>Transaksi Stok</span>
                </a>
            </li>
            
            <li class="<?php echo $current_page === 'suppliers.php' ? 'active' : ''; ?>">
                <a href="suppliers.php">
                    <span class="icon">🏢</span>
                    <span>Supplier</span>
                </a>
            </li>
            
            <li class="nav-divider">Laporan</li>
            
            <li class="<?php echo $current_page === 'reports.php' ? 'active' : ''; ?>">
                <a href="reports.php">
                    <span class="icon">📊</span>
                    <span>Laporan</span>
                </a>
            </li>
            
            <?php if (isAdmin()): ?>
            <li class="nav-divider">Pengaturan</li>
            
            <li class="<?php echo $current_page === 'users.php' ? 'active' : ''; ?>">
                <a href="users.php">
                    <span class="icon">👥</span>
                    <span>Manajemen User</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>
