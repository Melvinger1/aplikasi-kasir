-- Cash Register Database Schema
-- Database: cash_register_db

CREATE DATABASE IF NOT EXISTS cash_register_db;
USE cash_register_db;

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customers table (optional, for tracking customer purchases)
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255) UNIQUE,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Transactions table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_amount DECIMAL(10, 2) NOT NULL,
    change_amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'card', 'debit', 'credit', 'e-wallet') DEFAULT 'cash',
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Transaction items table
CREATE TABLE transaction_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert some sample products
INSERT INTO products (name, price, stock, category) VALUES
('Rice (1 kg)', 15000.00, 50, 'Groceries'),
('Eggs (10 pcs)', 12000.00, 30, 'Dairy'),
('Milk (1 liter)', 8000.00, 20, 'Dairy'),
('Bread', 7000.00, 25, 'Bakery'),
('Sugar (1 kg)', 10000.00, 40, 'Groceries'),
('Cooking Oil (1 liter)', 14000.00, 15, 'Groceries'),
('Salt (1 kg)', 5000.00, 35, 'Groceries'),
('Coffee (200g)', 18000.00, 10, 'Beverages');

-- Insert a sample customer
INSERT INTO customers (name, phone, email, address) VALUES
('John Doe', '081234567890', 'john@example.com', 'Jl. Sample Address No. 123');