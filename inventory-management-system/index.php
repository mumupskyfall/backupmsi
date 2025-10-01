<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$db = getDB();

// Statistik Dashboard
$stats = [];

// Total produk aktif
$stmt = $db->query("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
$stats['total_products'] = $stmt->fetch()['total'];

// Total kategori
$stmt = $db->query("SELECT COUNT(*) as total FROM categories");
$stats['total_categories'] = $stmt->fetch()['total'];

// Total nilai inventori
$stmt = $db->query("SELECT SUM(quantity * price) as total FROM products WHERE status = 'active'");
$stats['inventory_value'] = $stmt->fetch()['total'] ?? 0;

// Produk stok rendah
$stmt = $db->query("SELECT COUNT(*) as total FROM products WHERE quantity <= min_stock AND status = 'active'");
$stats['low_stock_count'] = $stmt->fetch()['total'];

// Produk dengan stok rendah (detail)
$stmt = $db->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.quantity <= p.min_stock AND p.status = 'active' 
    ORDER BY (p.min_stock - p.quantity) DESC 
    LIMIT 10
");
$low_stock_products = $stmt->fetchAll();

// Transaksi terakhir
$stmt = $db->query("
    SELECT t.*, p.name as product_name, p.sku 
    FROM transactions t 
    JOIN products p ON t.product_id = p.id 
    ORDER BY t.transaction_date DESC 
    LIMIT 10
");
$recent_transactions = $stmt->fetchAll();

// Nilai inventori per kategori
$stmt = $db->query("
    SELECT 
        c.name as category_name,
        COUNT(p.id) as product_count,
        SUM(p.quantity) as total_items,
        SUM(p.quantity * p.price) as total_value
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active'
    GROUP BY c.id, c.name
    ORDER BY total_value DESC
");
$category_values = $stmt->fetchAll();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
            </div>
            
            <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
            <?php endif; ?>
            
            <!-- Statistik Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 7h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v3H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zM10 4h4v3h-4V4zm10 16H4V9h16v11z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_products']); ?></h3>
                        <p>Total Produk</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M4 4h16v16H4V4zm2 2v12h12V6H6z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_categories']); ?></h3>
                        <p>Kategori</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo formatRupiah($stats['inventory_value']); ?></h3>
                        <p>Nilai Inventori</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 18c-3.86-1-7-5.07-7-9V8.3l7-3.11v14.82z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['low_stock_count']); ?></h3>
                        <p>Stok Rendah</p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <!-- Produk Stok Rendah -->
                <div class="card">
                    <div class="card-header">
                        <h2>⚠️ Produk Stok Rendah</h2>
                    </div>
                    <div class="card-body">
                        <?php if (count($low_stock_products) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>SKU</th>
                                        <th>Nama Produk</th>
                                        <th>Kategori</th>
                                        <th>Stok</th>
                                        <th>Min. Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($low_stock_products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td class="text-danger">
                                            <strong><?php echo number_format($product['quantity']); ?></strong>
                                        </td>
                                        <td><?php echo number_format($product['min_stock']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">Tidak ada produk dengan stok rendah</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Nilai Inventori per Kategori -->
                <div class="card">
                    <div class="card-header">
                        <h2>📊 Nilai Inventori per Kategori</h2>
                    </div>
                    <div class="card-body">
                        <?php if (count($category_values) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Kategori</th>
                                        <th>Produk</th>
                                        <th>Total Item</th>
                                        <th>Nilai Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($category_values as $cat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                                        <td><?php echo number_format($cat['product_count']); ?></td>
                                        <td><?php echo number_format($cat['total_items']); ?></td>
                                        <td><strong><?php echo formatRupiah($cat['total_value']); ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">Tidak ada data inventori</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Transaksi Terakhir -->
            <div class="card">
                <div class="card-header">
                    <h2>📝 Transaksi Terakhir</h2>
                </div>
                <div class="card-body">
                    <?php if (count($recent_transactions) > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>SKU</th>
                                    <th>Produk</th>
                                    <th>Tipe</th>
                                    <th>Jumlah</th>
                                    <th>Ref</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_transactions as $trans): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($trans['transaction_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($trans['sku']); ?></td>
                                    <td><?php echo htmlspecialchars($trans['product_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $trans['transaction_type'] === 'in' ? 'success' : 'danger'; ?>">
                                            <?php echo $trans['transaction_type'] === 'in' ? 'Masuk' : 'Keluar'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($trans['quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($trans['reference_number'] ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">Belum ada transaksi</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
