<?php
/**
 * Customer Registration Handler
 */

require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    // Get and sanitize input
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($firstName)) $errors[] = "First name is required";
    if (empty($lastName)) $errors[] = "Last name is required";
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit();
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT UserID FROM Users WHERE Email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit();
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Insert into Customer table
    $stmt = $pdo->prepare("
        INSERT INTO Customer (FirstName, LastName, Email, PhoneNo, Address) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$firstName, $lastName, $email, $phone, $address]);
    $customerID = $pdo->lastInsertId();
    
    // Hash password and insert into Users table
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO Users (Email, Password, Role, ReferenceID) 
        VALUES (?, ?, 'customer', ?)
    ");
    $stmt->execute([$email, $hashedPassword, $customerID]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful! Redirecting to login...'
    ]);
    
} catch(Exception $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Registration Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Registration failed. Please try again.'
    ]);
}
?>