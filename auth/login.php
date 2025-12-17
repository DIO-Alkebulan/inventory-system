<?php
/**
 * Universal Login Handler
 * Handles authentication for all user types
 */

require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    // Get and sanitize input
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit();
    }
    
    // Fetch user from database
    $stmt = $pdo->prepare("
        SELECT UserID, Email, Password, Role, ReferenceID, IsActive 
        FROM Users 
        WHERE Email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Verify user exists and password is correct
    if (!$user || !password_verify($password, $user['Password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit();
    }
    
    // Check if account is active
    if (!$user['IsActive']) {
        echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Please contact support.']);
        exit();
    }
    
    // Update last login time
    $stmt = $pdo->prepare("UPDATE Users SET LastLogin = NOW() WHERE UserID = ?");
    $stmt->execute([$user['UserID']]);
    
    // Set session variables
    $_SESSION['user_id'] = $user['UserID'];
    $_SESSION['email'] = $user['Email'];
    $_SESSION['role'] = $user['Role'];
    $_SESSION['reference_id'] = $user['ReferenceID'];
    
    // Get user name based on role
    $userName = '';
    if ($user['Role'] === 'customer') {
        $stmt = $pdo->prepare("SELECT FirstName, LastName FROM Customer WHERE CustomerID = ?");
        $stmt->execute([$user['ReferenceID']]);
        $profile = $stmt->fetch();
        $userName = $profile['FirstName'] . ' ' . $profile['LastName'];
    } elseif ($user['Role'] === 'supplier') {
        $stmt = $pdo->prepare("SELECT Name FROM Supplier WHERE SupplierID = ?");
        $stmt->execute([$user['ReferenceID']]);
        $profile = $stmt->fetch();
        $userName = $profile['Name'];
    } else {
        $userName = 'Admin';
    }
    
    $_SESSION['user_name'] = $userName;
    
    // Determine redirect URL
    $redirectUrl = '';
    switch ($user['Role']) {
        case 'admin':
            $redirectUrl = '/inventory-system/dashboards/admin_dashboard.php';
            break;
        case 'customer':
            $redirectUrl = '/inventory-system/dashboards/customer_dashboard.php';
            break;
        case 'supplier':
            $redirectUrl = '/inventory-system/dashboards/supplier_dashboard.php';
            break;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'redirect' => $redirectUrl,
        'role' => $user['Role']
    ]);
    
} catch(Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Login failed. Please try again.'
    ]);
}
?>