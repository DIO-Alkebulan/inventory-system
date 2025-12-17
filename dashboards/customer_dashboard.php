<?php
require_once '../config/db.php';
requireLogin();

if (!hasRole('customer')) {
    header("Location: /inventory-system/public/login.html");
    exit();
}

// Fetch customer data
try {
    $stmt = $pdo->prepare("
        SELECT * FROM Customer WHERE CustomerID = ?
    ");
    $stmt->execute([$_SESSION['reference_id']]);
    $customer = $stmt->fetch();
    
    // Fetch customer orders
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(od.OrderDetailID) as ItemCount
        FROM Orders o
        LEFT JOIN OrderDetails od ON o.OrderID = od.OrderID
        WHERE o.CustomerID = ?
        GROUP BY o.OrderID
        ORDER BY o.OrderDate DESC
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['reference_id']]);
    $orders = $stmt->fetchAll();
    
} catch(Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Inventory Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>📦 Inventory</h2>
                <span class="role-badge">Customer</span>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#" class="nav-item active">
                    <span>📊</span> Dashboard
                </a>
                <a href="#" class="nav-item">
                    <span>🛒</span> My Orders
                </a>
                <a href="#" class="nav-item">
                    <span>📦</span> Browse Products
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
                <h1>Welcome, <?php echo htmlspecialchars($customer['FirstName']); ?>!</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($customer['FirstName'], 0, 1)); ?>
                    </div>
                    <div>
                        <div><strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></div>
                        <div style="font-size: 12px; color: #6b7280;">Customer</div>
                    </div>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Profile Card -->
            <div class="content-card">
                <h3>Profile Information</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                    <div>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 4px;">Full Name</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($customer['FirstName'] . ' ' . $customer['LastName']); ?></p>
                    </div>
                    <div>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 4px;">Email</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($customer['Email']); ?></p>
                    </div>
                    <div>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 4px;">Phone</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($customer['PhoneNo']); ?></p>
                    </div>
                    <div>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 4px;">Address</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($customer['Address']); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="content-card">
                <h3>Recent Orders</h3>
                
                <?php if (empty($orders)): ?>
                    <div style="text-align: center; padding: 40px; color: #6b7280;">
                        <p style="font-size: 48px; margin-bottom: 16px;">🛒</p>
                        <p style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">No orders yet</p>
                        <p>Start shopping to see your orders here</p>
                        <button class="btn-primary" style="margin-top: 20px;">Browse Products</button>
                    </div>
                <?php else: ?>
                    <table class="data-table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo str_pad($order['OrderID'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['OrderDate'])); ?></td>
                                <td><?php echo $order['ItemCount']; ?> items</td>
                                <td>₦<?php echo number_format($order['TotalAmount'], 2); ?></td>
                                <td>
                                    <span style="text-transform: capitalize; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;
                                        <?php 
                                        switch($order['Status']) {
                                            case 'delivered':
                                                echo 'background: #d1fae5; color: #065f46;';
                                                break;
                                            case 'shipped':
                                                echo 'background: #dbeafe; color: #1e40af;';
                                                break;
                                            case 'processing':
                                                echo 'background: #fef3c7; color: #92400e;';
                                                break;
                                            case 'cancelled':
                                                echo 'background: #fee2e2; color: #991b1b;';
                                                break;
                                            default:
                                                echo 'background: #f3f4f6; color: #374151;';
                                        }
                                        ?>">
                                        <?php echo $order['Status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn view">View Details</button>
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