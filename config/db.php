<?php
/**
 * Database Configuration
 * PDO connection with error handling
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventory_management');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Create PDO instance
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Check if user has required role
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /inventory-system/public/login.html");
        exit();
    }
}

/**
 * Redirect to appropriate dashboard based on role
 */
function redirectToDashboard() {
    if (!isLoggedIn()) {
        return;
    }
    
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: /inventory-system/dashboards/admin_dashboard.php");
            break;
        case 'customer':
            header("Location: /inventory-system/dashboards/customer_dashboard.php");
            break;
        case 'supplier':
            header("Location: /inventory-system/dashboards/supplier_dashboard.php");
            break;
    }
    exit();
}
?>