<?php
require_once __DIR__ . '/db_connect.php';

// Function to get all products
function getAllProducts() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM products ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return false;
    }
}

// Function to get a product by ID
function getProductById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return false;
    }
}

// Function to add a new product
function addProduct($name, $price, $stock, $category = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, stock, category) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$name, $price, $stock, $category]);
        return $result;
    } catch(PDOException $e) {
        return false;
    }
}

// Function to update a product
function updateProduct($id, $name, $price, $stock, $category = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, stock = ?, category = ? WHERE id = ?");
        $result = $stmt->execute([$name, $price, $stock, $category, $id]);
        return $result;
    } catch(PDOException $e) {
        return false;
    }
}

// Function to delete a product
function deleteProduct($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $result = $stmt->execute([$id]);
        return $result;
    } catch(PDOException $e) {
        return false;
    }
}

// Function to update product stock
function updateProductStock($id, $newStock) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $result = $stmt->execute([$newStock, $id]);
        return $result;
    } catch(PDOException $e) {
        return false;
    }
}
?>