-- ============================================
-- INVENTORY MANAGEMENT SYSTEM - DATABASE SCHEMA
-- Complete SQL Script for Setup
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS inventory_management;
USE inventory_management;

-- Drop existing tables (be careful in production!)
DROP TABLE IF EXISTS Payment;
DROP TABLE IF EXISTS OrderDetails;
DROP TABLE IF EXISTS Orders;
DROP TABLE IF EXISTS Inventory;
DROP TABLE IF EXISTS Product;
DROP TABLE IF EXISTS Users;
DROP TABLE IF EXISTS Supplier;
DROP TABLE IF EXISTS Customer;

-- ============================================
-- 1. CUSTOMER TABLE
-- ============================================
CREATE TABLE Customer (
    CustomerID INT PRIMARY KEY AUTO_INCREMENT,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    PhoneNo VARCHAR(20),
    Address TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_email (Email),
    INDEX idx_customer_name (LastName, FirstName)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. SUPPLIER TABLE
-- ============================================
CREATE TABLE Supplier (
    SupplierID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    PhoneNo VARCHAR(20),
    ContactInfo VARCHAR(100),
    Address TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_supplier_email (Email),
    INDEX idx_supplier_name (Name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. USERS TABLE (Authentication)
-- ============================================
CREATE TABLE Users (
    UserID INT PRIMARY KEY AUTO_INCREMENT,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('customer', 'supplier', 'admin') NOT NULL,
    ReferenceID INT,
    IsActive BOOLEAN DEFAULT TRUE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    LastLogin TIMESTAMP NULL,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_email (Email),
    INDEX idx_users_role (Role),
    INDEX idx_users_reference (ReferenceID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. PRODUCT TABLE
-- ============================================
CREATE TABLE Product (
    ProductID INT PRIMARY KEY AUTO_INCREMENT,
    ProductName VARCHAR(100) NOT NULL,
    Description TEXT,
    Category VARCHAR(50),
    Price DECIMAL(10, 2) NOT NULL,
    SupplierID INT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (SupplierID) REFERENCES Supplier(SupplierID) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_product_name (ProductName),
    INDEX idx_product_category (Category),
    INDEX idx_product_supplier (SupplierID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. INVENTORY TABLE
-- ============================================
CREATE TABLE Inventory (
    InventoryID INT PRIMARY KEY AUTO_INCREMENT,
    ProductID INT NOT NULL,
    Quantity INT NOT NULL DEFAULT 0,
    ReorderLevel INT DEFAULT 10,
    LastUpdated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ProductID) REFERENCES Product(ProductID) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_inventory_product (ProductID),
    INDEX idx_inventory_quantity (Quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. ORDERS TABLE
-- ============================================
CREATE TABLE Orders (
    OrderID INT PRIMARY KEY AUTO_INCREMENT,
    CustomerID INT NOT NULL,
    OrderDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    TotalAmount DECIMAL(10, 2) NOT NULL,
    Status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    ShippingAddress TEXT,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_orders_customer (CustomerID),
    INDEX idx_orders_status (Status),
    INDEX idx_orders_date (OrderDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. ORDER DETAILS TABLE
-- ============================================
CREATE TABLE OrderDetails (
    OrderDetailID INT PRIMARY KEY AUTO_INCREMENT,
    OrderID INT NOT NULL,
    ProductID INT NOT NULL,
    Quantity INT NOT NULL,
    UnitPrice DECIMAL(10, 2) NOT NULL,
    Subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (ProductID) REFERENCES Product(ProductID) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_orderdetails_order (OrderID),
    INDEX idx_orderdetails_product (ProductID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. PAYMENT TABLE
-- ============================================
CREATE TABLE Payment (
    PaymentID INT PRIMARY KEY AUTO_INCREMENT,
    OrderID INT NOT NULL,
    PaymentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Amount DECIMAL(10, 2) NOT NULL,
    PaymentMethod ENUM('cash', 'card', 'bank_transfer', 'mobile_money') NOT NULL,
    Status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    TransactionReference VARCHAR(100),
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_payment_order (OrderID),
    INDEX idx_payment_status (Status),
    INDEX idx_payment_date (PaymentDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Insert Default Admin User
-- Password: Admin@123 (hashed with PASSWORD_DEFAULT)
INSERT INTO Users (Email, Password, Role, ReferenceID, IsActive) 
VALUES ('admin@inventory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, TRUE);

-- Insert Sample Customers
INSERT INTO Customer (FirstName, LastName, Email, PhoneNo, Address) VALUES
('John', 'Doe', 'john.doe@email.com', '+234 801 234 5678', '123 Main Street, Lagos, Nigeria'),
('Jane', 'Smith', 'jane.smith@email.com', '+234 802 345 6789', '456 Oak Avenue, Abuja, Nigeria'),
('Michael', 'Johnson', 'michael.j@email.com', '+234 803 456 7890', '789 Pine Road, Port Harcourt, Nigeria');

-- Create user accounts for sample customers
INSERT INTO Users (Email, Password, Role, ReferenceID) VALUES
('john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 1),
('jane.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 2),
('michael.j@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 3);

-- Insert Sample Suppliers
INSERT INTO Supplier (Name, Email, PhoneNo, ContactInfo, Address) VALUES
('Tech Supplies Ltd', 'info@techsupplies.com', '+234 810 111 2222', 'info@techsupplies.com', '12 Industrial Estate, Lagos'),
('Office Equipment Co', 'sales@officeequip.com', '+234 811 222 3333', 'sales@officeequip.com', '45 Business District, Abuja'),
('Electronics Hub', 'contact@electrohub.com', '+234 812 333 4444', 'contact@electrohub.com', '78 Tech Park, Port Harcourt');

-- Create user accounts for sample suppliers
INSERT INTO Users (Email, Password, Role, ReferenceID) VALUES
('info@techsupplies.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supplier', 1),
('sales@officeequip.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supplier', 2),
('contact@electrohub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supplier', 3);

-- Insert Sample Products
INSERT INTO Product (ProductName, Description, Category, Price, SupplierID) VALUES
('Laptop Dell XPS 15', 'High-performance laptop with 16GB RAM', 'Electronics', 850000.00, 1),
('Office Chair Premium', 'Ergonomic office chair with lumbar support', 'Furniture', 45000.00, 2),
('Wireless Mouse', 'Bluetooth wireless mouse', 'Electronics', 5000.00, 1),
('Printer HP LaserJet', 'Black and white laser printer', 'Electronics', 125000.00, 3),
('Desk Lamp LED', 'Adjustable LED desk lamp', 'Furniture', 8500.00, 2),
('USB Flash Drive 64GB', 'High-speed USB 3.0 flash drive', 'Electronics', 3500.00, 3),
('Notebook A4', 'Professional hardcover notebook', 'Stationery', 1500.00, 2),
('Keyboard Mechanical', 'RGB mechanical gaming keyboard', 'Electronics', 35000.00, 1);

-- Insert Inventory for Products
INSERT INTO Inventory (ProductID, Quantity, ReorderLevel) VALUES
(1, 15, 5),
(2, 30, 10),
(3, 50, 15),
(4, 8, 3),
(5, 25, 10),
(6, 100, 20),
(7, 200, 50),
(8, 12, 5);

-- Insert Sample Orders
INSERT INTO Orders (CustomerID, TotalAmount, Status, ShippingAddress) VALUES
(1, 855000.00, 'delivered', '123 Main Street, Lagos, Nigeria'),
(2, 90000.00, 'shipped', '456 Oak Avenue, Abuja, Nigeria'),
(3, 128500.00, 'processing', '789 Pine Road, Port Harcourt, Nigeria');

-- Insert Order Details
INSERT INTO OrderDetails (OrderID, ProductID, Quantity, UnitPrice, Subtotal) VALUES
(1, 1, 1, 850000.00, 850000.00),
(1, 3, 1, 5000.00, 5000.00),
(2, 2, 2, 45000.00, 90000.00),
(3, 4, 1, 125000.00, 125000.00),
(3, 5, 1, 8500.00, 8500.00);

-- Insert Sample Payments
INSERT INTO Payment (OrderID, Amount, PaymentMethod, Status, TransactionReference) VALUES
(1, 855000.00, 'bank_transfer', 'completed', 'TXN-2024-001'),
(2, 90000.00, 'card', 'completed', 'TXN-2024-002'),
(3, 128500.00, 'mobile_money', 'pending', 'TXN-2024-003');

-- ============================================
-- CREATE VIEWS (Optional - for reporting)
-- ============================================

-- View: Product Stock Status
CREATE OR REPLACE VIEW vw_product_stock_status AS
SELECT 
    p.ProductID,
    p.ProductName,
    p.Category,
    p.Price,
    s.Name AS SupplierName,
    i.Quantity AS StockLevel,
    i.ReorderLevel,
    CASE 
        WHEN i.Quantity = 0 THEN 'Out of Stock'
        WHEN i.Quantity < i.ReorderLevel THEN 'Low Stock'
        ELSE 'In Stock'
    END AS StockStatus
FROM Product p
LEFT JOIN Supplier s ON p.SupplierID = s.SupplierID
LEFT JOIN Inventory i ON p.ProductID = i.ProductID;

-- View: Order Summary
CREATE OR REPLACE VIEW vw_order_summary AS
SELECT 
    o.OrderID,
    CONCAT(c.FirstName, ' ', c.LastName) AS CustomerName,
    c.Email AS CustomerEmail,
    o.OrderDate,
    o.TotalAmount,
    o.Status,
    COUNT(od.OrderDetailID) AS TotalItems,
    p.Status AS PaymentStatus
FROM Orders o
INNER JOIN Customer c ON o.CustomerID = c.CustomerID
LEFT JOIN OrderDetails od ON o.OrderID = od.OrderID
LEFT JOIN Payment p ON o.OrderID = p.OrderID
GROUP BY o.OrderID;

-- View: Supplier Performance
CREATE OR REPLACE VIEW vw_supplier_performance AS
SELECT 
    s.SupplierID,
    s.Name AS SupplierName,
    s.Email,
    COUNT(DISTINCT p.ProductID) AS TotalProducts,
    SUM(i.Quantity) AS TotalStockQuantity,
    COALESCE(SUM(od.Quantity * od.UnitPrice), 0) AS TotalRevenue
FROM Supplier s
LEFT JOIN Product p ON s.SupplierID = p.SupplierID
LEFT JOIN Inventory i ON p.ProductID = i.ProductID
LEFT JOIN OrderDetails od ON p.ProductID = od.ProductID
GROUP BY s.SupplierID;

-- ============================================
-- CREATE STORED PROCEDURES (Optional)
-- ============================================

DELIMITER //

-- Procedure: Process Order
CREATE PROCEDURE sp_process_order(
    IN p_customer_id INT,
    IN p_product_id INT,
    IN p_quantity INT,
    OUT p_order_id INT
)
BEGIN
    DECLARE v_price DECIMAL(10,2);
    DECLARE v_subtotal DECIMAL(10,2);
    DECLARE v_stock INT;
    
    -- Check stock availability
    SELECT Quantity INTO v_stock FROM Inventory WHERE ProductID = p_product_id;
    
    IF v_stock >= p_quantity THEN
        -- Get product price
        SELECT Price INTO v_price FROM Product WHERE ProductID = p_product_id;
        SET v_subtotal = v_price * p_quantity;
        
        -- Create order
        INSERT INTO Orders (CustomerID, TotalAmount, Status) 
        VALUES (p_customer_id, v_subtotal, 'pending');
        SET p_order_id = LAST_INSERT_ID();
        
        -- Add order details
        INSERT INTO OrderDetails (OrderID, ProductID, Quantity, UnitPrice, Subtotal)
        VALUES (p_order_id, p_product_id, p_quantity, v_price, v_subtotal);
        
        -- Update inventory
        UPDATE Inventory 
        SET Quantity = Quantity - p_quantity 
        WHERE ProductID = p_product_id;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Insufficient stock';
    END IF;
END //

DELIMITER ;

-- ============================================
-- GRANT PRIVILEGES (Adjust as needed)
-- ============================================
-- GRANT ALL PRIVILEGES ON inventory_management.* TO 'your_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ============================================
-- VERIFICATION QUERIES
-- ============================================
-- Run these to verify setup

-- Check tables created
SHOW TABLES;

-- Check sample data
SELECT 'Customers' AS TableName, COUNT(*) AS RecordCount FROM Customer
UNION ALL
SELECT 'Suppliers', COUNT(*) FROM Supplier
UNION ALL
SELECT 'Products', COUNT(*) FROM Product
UNION ALL
SELECT 'Orders', COUNT(*) FROM Orders
UNION ALL
SELECT 'Users', COUNT(*) FROM Users;

-- ============================================
-- SETUP COMPLETE
-- ============================================
-- Default Admin Credentials:
-- Email: admin@inventory.com
-- Password: Admin@123
-- 
-- Sample User Credentials (all use same password):
-- Password: Admin@123
-- ============================================