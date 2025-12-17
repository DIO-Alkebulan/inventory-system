<?php
require_once '../config/db.php';
requireLogin();

if (!hasRole('supplier')) {
    header("Location: /inventory-system/public/login.html");
    exit();
}

// Fetch supplier data
try {
    $stmt = $pdo->prepare("
        SELECT * FROM Supplier WHERE SupplierID = ?
    ");
    $stmt->execute([$_SESSION['reference_id']]);
    $supplier = $stmt->fetch();
    
    // Fetch supplier products
    $stmt = $pdo->prepare("
        SELECT p.*, 
               COALESCE(i.Quantity, 0) as StockLevel
        FROM Product p
        LEFT JOIN Inventory i ON p.ProductID = i.ProductID
        WHERE p.SupplierID = ?
        ORDER BY p.CreatedAt DESC
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['reference_id']]);
    $products = $stmt->fetchAll();
    
    // Count total products
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Product WHERE SupplierID = ?");
    $stmt->execute([$_SESSION['reference_id']]);
    $productCount = $stmt->fetch()['count'];
    
} catch(Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $products = [];
    $productCount = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard - Inventory Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>📦 Inventory</h2>
                <span class="role-badge">Supplier</span>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#" class="nav-item active">
                    <span>📊</span> Dashboard
                </a>
                <a href="#" class="nav-item">
                    <span>📦</span> My Products
                </a>
                <a href="#" class="nav-item">
                    <span>➕</span> Add Product
                </a>
                <a href="#" class="nav-item">
                    <span>📊</span> Inventory
                </a>
                <a href="#" class="nav-item">
                    <span>👤</span> My Profile
                </a>
                <a href="#" class="nav-item">
                    <span>⚙️</span> Settings
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Welcome, <?php echo htmlspecialchars($supplier['Name']); ?>!</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($supplier['Name'], 0, 1)); ?>
                    </div>
                    <div>
                        <div><strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></div>
                        <div style="font-size: 12px; color: #6b7280;">Supplier</div>
                    </div>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">📦</div>
                    <div class="stat-value"><?php echo $productCount; ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value">
                        <?php 
                        $inStock = array_filter($products, function($p) { return $p['StockLevel'] > 0; });
                        echo count($inStock);
                        ?>
                    </div>
                    <div class="stat-label">In Stock</div>
                </div>
                
                <div class="stat-card yellow">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-value">
                        <?php 
                        $lowStock = array_filter($products, function($p) { return $p['StockLevel'] > 0 && $p['StockLevel'] < 10; });
                        echo count($lowStock);
                        ?>
                    </div>
                    <div class="stat-label">Low Stock</div>
                </div>
                
                <div class="stat-card red">
                    <div class="stat-icon">❌</div>
                    <div class="stat-value">
                        <?php 
                        $outOfStock = array_filter($products, function($p) { return $p['StockLevel'] == 0; });
                        echo count($outOfStock);
                        ?>
                    </div>
                    <div class="stat-label">Out of Stock</div>
                </div>
            </div>
            
            <!-- Business Profile -->
            <div class="content-card">
                <h3>Business Information</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                    <div>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 4px;">Business Name</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($supplier['Name']); ?></p>
                    </div>
                    <div>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 4px;">Email</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($supplier['Email']); ?></p>
                    </div>
                    <div>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 4px;">Phone</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($supplier['PhoneNo']); ?></p>
                    </div>
                    <div>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 4px;">Address</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($supplier['Address']); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Products List -->
            <div class="content-card">
                <h3>Recent Products</h3>
                
                <?php if (empty($products)): ?>
                    <div style="text-align: center; padding: 40px; color: #6b7280;">
                        <p style="font-size: 48px; margin-bottom: 16px;">📦</p>
                        <p style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">No products yet</p>
                        <p>Add your first product to get started</p>
                        <button class="btn-primary" style="margin-top: 20px;">Add Product</button>
                    </div>
                <?php else: ?>
                    <div class="search-bar">
                        <input type="text" placeholder="Search products..." 
                               onkeyup="searchTable(this.value, 'productsTable')">
                        <button>Add New Product</button>
                    </div>
                    
                    <table class="data-table" id="productsTable">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock Level</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>#<?php echo str_pad($product['ProductID'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($product['ProductName']); ?></td>
                                <td><?php echo htmlspecialchars($product['Category'] ?? 'N/A'); ?></td>
                                <td>₦<?php echo number_format($product['Price'], 2); ?></td>
                                <td><?php echo $product['StockLevel']; ?></td>
                                <td>
                                    <?php if ($product['StockLevel'] > 10): ?>
                                        <span style="color: #10b981; font-weight: 600;">In Stock</span>
                                    <?php elseif ($product['StockLevel'] > 0): ?>
                                        <span style="color: #f59e0b; font-weight: 600;">Low Stock</span>
                                    <?php else: ?>
                                        <span style="color: #ef4444; font-weight: 600;">Out of Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="action-btn view">View</button>
                                    <button class="action-btn edit">Edit</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/validation.js"></script>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>