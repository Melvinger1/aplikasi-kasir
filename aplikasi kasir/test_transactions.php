<?php
// Test script to check transaction data in the database

require_once 'includes/db_connect.php';

echo "<h2>Checking Transaction Data</h2>";

try {
    // Check how many transactions exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM transactions");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total transactions: {$count['count']}</p>";
    
    if ($count['count'] > 0) {
        // Get all transactions with their dates
        $stmt = $pdo->query("SELECT id, transaction_date, total_amount FROM transactions ORDER BY transaction_date DESC LIMIT 10");
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Recent Transactions:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Date</th><th>Amount</th></tr>";
        foreach ($transactions as $transaction) {
            echo "<tr>";
            echo "<td>{$transaction['id']}</td>";
            echo "<td>{$transaction['transaction_date']}</td>";
            echo "<td>Rp " . number_format($transaction['total_amount']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No transactions found.</p>";
    }
    
    // Test a query with today's date
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM transactions WHERE DATE(transaction_date) = ?");
    $stmt->execute([$today]);
    $todayCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Transactions for today ({$today}): {$todayCount['count']}</p>";
    
    // Test the getSalesReport function directly
    require_once 'includes/sales_functions.php';
    $report = getSalesReport($today, $today);
    echo "<p>getSalesReport for today returned: " . (is_array($report) ? count($report) . " records" : "false") . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<a href='index.php' style='color: #3498db; text-decoration: none; font-weight: bold;'>Go back to Dashboard</a>";
?>