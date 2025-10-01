<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$db = getDB();

// Handle form submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
        $name = sanitize($_POST['name']);
        $category_id = $_POST['category_id'] ?? null;
        $sku = sanitize($_POST['sku']);
        $description = sanitize($_POST['description']);
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);
        $min_stock = intval($_POST['min_stock']);
        $status = $_POST['status'];
        
        try {
            if ($_POST['action'] === 'add') {
                $stmt = $db->prepare("
                    INSERT INTO products (name, category_id, sku, description, price, quantity, min_stock, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $category_id, $sku, $description, $price, $quantity, $min_stock, $status]);
                setFlash('success', 'Produk berhasil ditambahkan');
            } else {
                $id = intval($_POST['id']);
                $stmt = $db->prepare("
                    UPDATE products 
                    SET name = ?, category_id = ?, sku = ?, description = ?, price = ?, quantity = ?, min_stock = ?, status = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$name, $category_id, $sku, $description, $price, $quantity, $min_stock, $status, $id]);
                setFlash('success', 'Produk berhasil diupdate');
            }
            redirect('products.php');
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'delete') {
        try {
            $id = intval($_POST['id']);
            $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Produk berhasil dihapus');
            redirect('products.php');
        } catch (PDOException $e) {
            $error = 'Gagal menghapus produk';
        }
    }
}

// Get all categories for dropdown
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Search and filter
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(name LIKE ? OR sku LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category_filter)) {
    $where[] = "category_id = ?";
    $params[] = $category_filter;
}

if (!empty($status_filter)) {
    $where[] = "status = ?";
    $params[] = $status_filter;
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $where_clause
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$products = $stmt->fetchAll();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Manajemen Produk</h1>
                <p>Kelola data produk inventori</p>
            </div>
            
            <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <!-- Search and Filter -->
            <div class="card mb-20">
                <div class="card-body">
                    <form method="GET" action="">
                        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 15px;">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Cari produk (nama, SKU, deskripsi)..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            
                            <select name="category" class="form-control">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                                <option value="discontinued" <?php echo $status_filter === 'discontinued' ? 'selected' : ''; ?>>Dihentikan</option>
                            </select>
                            
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mb-20">
                <button onclick="showAddModal()" class="btn btn-success">+ Tambah Produk</button>
            </div>
            
            <!-- Products Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Min. Stok</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($products) > 0): ?>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($product['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td><?php echo formatRupiah($product['price']); ?></td>
                                        <td>
                                            <span class="<?php echo $product['quantity'] <= $product['min_stock'] ? 'text-danger' : ''; ?>">
                                                <?php echo number_format($product['quantity']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo number_format($product['min_stock']); ?></td>
                                        <td>
                                            <?php
                                            $badge_class = 'info';
                                            if ($product['status'] === 'active') $badge_class = 'success';
                                            if ($product['status'] === 'discontinued') $badge_class = 'danger';
                                            ?>
                                            <span class="badge badge-<?php echo $badge_class; ?>">
                                                <?php echo ucfirst($product['status']); ?>
                                            </span>
                                        </td>
                                        <td class="table-actions">
                                            <button onclick='editProduct(<?php echo json_encode($product); ?>)' 
                                                    class="btn btn-sm btn-primary">Edit</button>
                                            <form method="POST" style="display:inline;" 
                                                  onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data produk</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal Add/Edit Product -->
    <div id="productModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; overflow:auto;">
        <div style="max-width:600px; margin:50px auto; background:white; border-radius:10px; padding:30px;">
            <h2 id="modalTitle">Tambah Produk</h2>
            <form method="POST" action="" id="productForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="productId">
                
                <div class="form-group">
                    <label for="name">Nama Produk *</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="sku">SKU *</label>
                    <input type="text" id="sku" name="sku" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Kategori</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="form-control"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Harga *</label>
                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="quantity">Jumlah Stok *</label>
                    <input type="number" id="quantity" name="quantity" class="form-control" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="min_stock">Minimum Stok *</label>
                    <input type="number" id="min_stock" name="min_stock" class="form-control" min="0" value="10" required>
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="discontinued">Discontinued</option>
                    </select>
                </div>
                
                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    function showAddModal() {
        document.getElementById('modalTitle').textContent = 'Tambah Produk';
        document.getElementById('formAction').value = 'add';
        document.getElementById('productForm').reset();
        document.getElementById('productModal').style.display = 'block';
    }
    
    function editProduct(product) {
        document.getElementById('modalTitle').textContent = 'Edit Produk';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('productId').value = product.id;
        document.getElementById('name').value = product.name;
        document.getElementById('sku').value = product.sku;
        document.getElementById('category_id').value = product.category_id || '';
        document.getElementById('description').value = product.description || '';
        document.getElementById('price').value = product.price;
        document.getElementById('quantity').value = product.quantity;
        document.getElementById('min_stock').value = product.min_stock;
        document.getElementById('status').value = product.status;
        document.getElementById('productModal').style.display = 'block';
    }
    
    function closeModal() {
        document.getElementById('productModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    document.getElementById('productModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    </script>
</body>
</html>
