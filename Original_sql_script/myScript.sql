CREATE TABLE Customer (
    CustomerID INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    PhoneNo VARCHAR(20),
    Address TEXT
);


CREATE TABLE Category (
    CategoryID INT AUTO_INCREMENT PRIMARY KEY,
    CategoryName VARCHAR(100) UNIQUE NOT NULL
);


CREATE TABLE Supplier (
    SupplierID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Address TEXT,
    PhoneNo VARCHAR(20),
    Email VARCHAR(100) UNIQUE NOT NULL 
);


CREATE TABLE Product (
    ProductID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Price DECIMAL(10,2) NOT NULL,
    Description TEXT,
    StockQty INT NOT NULL DEFAULT 0,
    CategoryID INT,
    SupplierID INT,
    FOREIGN KEY (CategoryID) REFERENCES Category(CategoryID)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    FOREIGN KEY (SupplierID) REFERENCES Supplier(SupplierID)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);


CREATE TABLE Inventory (
    InventoryID INT AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    SupplierID INT NOT NULL,
    StockLevel INT NOT NULL,
    LastRestockDate DATE,
    FOREIGN KEY (ProductID) REFERENCES Product(ProductID)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (SupplierID) REFERENCES Supplier(SupplierID)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);


CREATE TABLE Orders (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT NOT NULL,
    OrderDate DATE NOT NULL,
    TotalAmount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);


CREATE TABLE OrderDetails (
    OrderDetailsID INT AUTO_INCREMENT PRIMARY KEY,
    OrderID INT NOT NULL,
    ProductID INT NOT NULL,
    SubTotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (ProductID) REFERENCES Product(ProductID)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);


CREATE TABLE Payment (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    OrderID INT NOT NULL,
    CustomerID INT NOT NULL,
    PaymentMethod VARCHAR(50) NOT NULL,
    PaymentStatus VARCHAR(30) NOT NULL,
    TransactionDate DATE NOT NULL,
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);



/*Entry of Values*/

INSERT INTO Category (CategoryName) VALUES
('Electronics'),
('Groceries'),
('Clothing'),
('Stationery');


INSERT INTO Supplier (Name, Email, PhoneNo, Address) VALUES
('TechSource Ltd', 'techsource@gmail.com', '08031234567', 'Plot 15 Aba Road, GRA Phase 2, Port Harcourt, Rivers state'),
('FreshFarm Supplies', 'freshfarm@gmail.com', '08039876543', 'Km 8 Owerri–Onitsha Road, Umuokanne, Imo State'),
('StyleHub', 'stylehub@gmail.com', '08123456789', '12 Fashion Line, Ogui Road, Enugu State'),
('OfficeMart', 'officemart@gmail.com', '08098765432', 'Suite 4, Stationery Plaza, Airport Road, Abuja');


INSERT INTO Customer (FirstName, LastName, Email, PhoneNo, Address) VALUES
('Divine', 'Jaja', 'divine@gmail.com', '08111111111', 'Port Harcourt'),
('Blessing', 'Okoro', 'blessing@gmail.com', '08222222222', 'Owerri'),
('Samuel', 'Johnson', 'samuel@gmail.com', '08333333333', 'Uyo');


INSERT INTO Product (Name, Price, Description, StockQty, CategoryID, SupplierID) VALUES
('Laptop', 450000.00, 'HP Pavilion Laptop', 10, 1, 1),
('Smartphone', 280000.00, 'Samsung Galaxy Phone', 15, 1, 1),
('Rice (50kg)', 75000.00, 'Bag of Local Rice', 20, 2, 2),
('T-Shirt', 8000.00, 'Cotton Round Neck', 30, 3, 3),
('Notebook', 1500.00, '200 Pages Exercise Book', 50, 4, 4);


INSERT INTO Inventory (ProductID, SupplierID, StockLevel, LastRestockDate) VALUES
(1, 1, 10, '2025-01-10'),
(2, 1, 15, '2025-01-12'),
(3, 2, 20, '2025-01-15'),
(4, 3, 30, '2025-01-18'),
(5, 4, 50, '2025-01-20');


INSERT INTO Orders (CustomerID, OrderDate, TotalAmount) VALUES
(1, '2025-02-01', 458000.00),
(2, '2025-02-02', 8000.00),
(3, '2025-02-03', 1500.00);


INSERT INTO OrderDetails (OrderID, ProductID, SubTotal) VALUES
(1, 1, 450000.00),
(1, 5, 1500.00),
(2, 4, 8000.00),
(3, 5, 1500.00);


INSERT INTO Payment (OrderID, CustomerID, PaymentMethod, PaymentStatus, TransactionDate) VALUES
(1, 1, 'Bank Transfer', 'Completed', '2025-02-01'),
(2, 2, 'Cash', 'Completed', '2025-02-02'),
(3, 3, 'Card', 'Pending', '2025-02-03');













