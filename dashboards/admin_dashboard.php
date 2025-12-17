<?php
require_once '../config/db.php';
requireLogin();

if (!hasRole('admin')) {
    header("Location: /inventory-system/public/login.html");
    exit();
}

// Fetch statistics
try {
    $stats = [];
    
    // Total customers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Customer");
    $stats['customers'] = $stmt->fetch()['count'];
    
    // Total suppliers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Supplier");
    $stats['suppliers'] = $stmt->fetch()['count'];
    
    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Product");
    $stats['products'] = $stmt->fetch()['count'];
    
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Orders");
    $stats['orders'] = $stmt->fetch()['count'];
    
    // Recent users
    $stmt = $pdo->query("
        SELECT u.*, 
               CASE 
                   WHEN u.Role = 'customer' THEN CONCAT(c.FirstName, ' ', c.LastName)
                   WHEN u.Role = 'supplier' THEN s.Name
                   ELSE 'Admin'
               END as Name
        FROM Users u
        LEFT JOIN Customer c ON u.ReferenceID = c.CustomerID AND u.Role = 'customer'
        LEFT JOIN Supplier s ON u.ReferenceID = s.SupplierID AND u.Role = 'supplier'
        ORDER BY u.CreatedAt DESC
        LIMIT 10
    ");
    $recentUsers = $stmt->fetchAll();
    
} catch(Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $stats = ['customers' => 0, 'suppliers' => 0, 'products' => 0, 'orders' => 0];
    $recentUsers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Inventory Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>📦 Inventory</h2>
                <span class="role-badge">Admin</span>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#" class="nav-item active">
                    <span>📊</span> Dashboard
                </a>
                <a href="#" class="nav-item">
                    <span>👥</span> Customers
                </a>
                <a href="#" class="nav-item">
                    <span>🏢</span> Suppliers
                </a>
                <a href="#" class="nav-item">
                    <span>📦</span> Products
                </a>
                <a href="#" class="nav-item">
                    <span>🛒</span> Orders
                </a>
                <a href="#" class="nav-item">
                    <span>📊</span> Inventory
                </a>
                <a href="#" class="nav-item">
                    <span>💳</span> Payments
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
                <h1>Dashboard Overview</h1>
                <div class="user-info">
                    <div class="user-avatar">A</div>
                    <div>
                        <div><strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></div>
                        <div style="font-size: 12px; color: #6b7280;">Administrator</div>
                    </div>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value"><?php echo $stats['customers']; ?></div>
                    <div class="stat-label">Total Customers</div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-icon">🏢</div>
                    <div class="stat-value"><?php echo $stats['suppliers']; ?></div>
                    <div class="stat-label">Total Suppliers</div>
                </div>
                
                <div class="stat-card yellow">
                    <div class="stat-icon">📦</div>
                    <div class="stat-value"><?php echo $stats['products']; ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                
                <div class="stat-card red">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-value"><?php echo $stats['orders']; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
            
            <!-- Recent Users -->
            <div class="content-card">
                <h3>Recent User Registrations</h3>
                
                <div class="search-bar">
                    <input type="text" placeholder="Search users..." 
                           onkeyup="searchTable(this.value, 'usersTable')">
                    <button onclick="exportTableToCSV('usersTable', 'users.csv')">Export CSV</button>
                </div>
                
                <table class="data-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?php echo $user['UserID']; ?></td>
                            <td><?php echo htmlspecialchars($user['Name']); ?></td>
                            <td><?php echo htmlspecialchars($user['Email']); ?></td>
                            <td><span style="text-transform: capitalize;"><?php echo $user['Role']; ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['CreatedAt'])); ?></td>
                            <td>
                                <?php if ($user['IsActive']): ?>
                                    <span style="color: #10b981; font-weight: 600;">Active</span>
                                <?php else: ?>
                                    <span style="color: #ef4444; font-weight: 600;">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="action-btn view">View</button>
                                <button class="action-btn edit">Edit</button>
                                <button class="action-btn delete" 
                                        onclick="if(confirmDelete('<?php echo htmlspecialchars($user['Name']); ?>')) alert('Delete functionality would be implemented here')">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/validation.js"></script>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>