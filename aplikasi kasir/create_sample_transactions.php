<?php
// Script to create sample transactions for testing dashboard functionality

require_once 'includes/db_connect.php';
require_once 'includes/sales_functions.php';

echo "<h2>Creating Sample Transactions for Dashboard Testing</h2>";

try {
    // Get all products to use for sample transactions
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        echo "<p>No products found. Please initialize the database first.</p>";
        exit;
    }

    // Create a few sample transactions
    for ($i = 0; $i < 3; $i++) {
        // Select random products for each transaction
        $items = [];
        $numItems = rand(1, min(3, count($products))); // 1-3 items per transaction

        for ($j = 0; $j < $numItems; $j++) {
            $product = $products[array_rand($products)];
            $quantity = rand(1, 3); // 1-3 of each product
            $items[] = [
                'id' => $product['id'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }

        // Calculate total
        $total_amount = 0;
        foreach ($items as $item) {
            $total_amount += ($item['price'] * $item['quantity']);
        }

        // Add 10% to payment amount as "extra" to have change
        $payment_amount = $total_amount * 1.1;

        // Create transaction with today's date
        $transaction_id = addSale($items, $total_amount, $payment_amount, $payment_amount - $total_amount, 'cash');

        if ($transaction_id) {
            echo "<p>Created transaction #{$transaction_id} with {$numItems} items, total: Rp " . number_format($total_amount) . "</p>";
        } else {
            echo "<p>Failed to create transaction.</p>";
        }
    }

    echo "<p>Sample transactions created successfully!</p>";
    echo "<a href='index.php' style='color: #3498db; text-decoration: none; font-weight: bold;'>Go back to Dashboard</a>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>