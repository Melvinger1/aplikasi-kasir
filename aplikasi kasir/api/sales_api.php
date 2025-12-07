<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/sales_functions.php';
require_once __DIR__ . '/../includes/product_functions.php';

// Handle different request methods
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'processSale':
            $items = $input['items'] ?? $_POST['items'] ?? [];
            $total_amount = $input['total_amount'] ?? $_POST['total_amount'] ?? 0;
            $payment_amount = $input['payment_amount'] ?? $_POST['payment_amount'] ?? 0;
            $change_amount = $input['change_amount'] ?? $_POST['change_amount'] ?? 0;
            $payment_method = $input['payment_method'] ?? $_POST['payment_method'] ?? 'cash';
            $customer_id = $input['customer_id'] ?? $_POST['customer_id'] ?? null;

            if (!empty($items) && $total_amount >= 0 && $payment_amount >= 0) {
                $transaction_id = addSale($items, $total_amount, $payment_amount, $change_amount, $payment_method, $customer_id);

                if ($transaction_id) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Sale processed successfully',
                        'transaction_id' => $transaction_id
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Failed to process sale'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid sale data'
                ]);
            }
            break;

        case 'processPayment':
            $items = $input['items'] ?? $_POST['items'] ?? [];
            $payment_amount = $input['payment_amount'] ?? $_POST['payment_amount'] ?? 0;
            $payment_method = $input['payment_method'] ?? $_POST['payment_method'] ?? 'cash';
            $customer_id = $input['customer_id'] ?? $_POST['customer_id'] ?? null;

            if (!empty($items) && $payment_amount >= 0) {
                $result = processPayment($items, $payment_amount, $payment_method, $customer_id);

                echo json_encode($result);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid payment data'
                ]);
            }
            break;

        case 'getTransactions':
            $transactions = getAllTransactions();
            if ($transactions !== false) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $transactions
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to fetch transactions'
                ]);
            }
            break;

        case 'getTransaction':
            $id = $input['id'] ?? $_POST['id'] ?? '';
            if ($id) {
                $transaction = getTransactionById($id);
                if ($transaction !== false) {
                    echo json_encode([
                        'status' => 'success',
                        'data' => $transaction
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Transaction not found'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Transaction ID not provided'
                ]);
            }
            break;

        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action'
            ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'getTransactions':
            $limit = $_GET['limit'] ?? 50;
            $transactions = getAllTransactions($limit);
            if ($transactions !== false) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $transactions
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to fetch transactions'
                ]);
            }
            break;

        case 'getTopSellingProducts':
            $limit = $_GET['limit'] ?? 10;
            $products = getTopSellingProducts($limit);
            if ($products !== false) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $products
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to fetch top selling products'
                ]);
            }
            break;

        case 'getDashboardData':
            global $pdo;

            try {
                $today = date('Y-m-d');

                // Get today's sales
                $stmt = $pdo->prepare("
                    SELECT SUM(total_amount) as today_sales
                    FROM transactions
                    WHERE DATE(transaction_date) = ?
                ");
                $stmt->execute([$today]);
                $todaySales = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalSales = $todaySales['today_sales'] ?? 0;

                // Get today's transaction count
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as transaction_count
                    FROM transactions
                    WHERE DATE(transaction_date) = ?
                ");
                $stmt->execute([$today]);
                $transactionCount = $stmt->fetch(PDO::FETCH_ASSOC)['transaction_count'] ?? 0;

                // Get top selling products
                $stmt = $pdo->prepare("
                    SELECT p.name as product_name, SUM(ti.quantity) as total_sold
                    FROM transaction_items ti
                    JOIN products p ON ti.product_id = p.id
                    JOIN transactions t ON ti.transaction_id = t.id
                    WHERE DATE(t.transaction_date) = ?
                    GROUP BY ti.product_id
                    ORDER BY total_sold DESC
                    LIMIT 3
                ");
                $stmt->execute([$today]);
                $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $topProductNames = '';
                if (!empty($topProducts)) {
                    $names = array_column($topProducts, 'product_name');
                    $topProductNames = implode(', ', $names);
                }

                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'today_sales' => (float)$totalSales,
                        'transaction_count' => (int)$transactionCount,
                        'top_products' => $topProductNames ?: '-'
                    ]
                ]);
            } catch(PDOException $e) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to fetch dashboard data: ' . $e->getMessage()
                ]);
            }
            break;

        case 'getSalesReport':
            $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $end_date = $_GET['end_date'] ?? date('Y-m-d');

            $report = getSalesReport($start_date, $end_date);
            if ($report !== false) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $report
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to fetch sales report'
                ]);
            }
            break;

        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action'
            ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}

// Add final fallback in case nothing is output
if (ob_get_level()) {
    ob_end_flush();
}
?>