<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Fetch dashboard data for initial display
$dashboard_data = [
    'today_sales' => 0,
    'transaction_count' => 0,
    'top_products' => '-'
];

// Only fetch if we have database connection
if (file_exists('includes/db_connect.php')) {
    require_once 'includes/db_connect.php';
    require_once 'includes/sales_functions.php';

    try {
        // Get today's sales
        $today = date('Y-m-d');

        // Get today's sales
        $stmt = $pdo->prepare("
            SELECT SUM(total_amount) as today_sales
            FROM transactions
            WHERE DATE(transaction_date) = ?
        ");
        $stmt->execute([$today]);
        $todaySales = $stmt->fetch(PDO::FETCH_ASSOC);
        $today_sales = $todaySales['today_sales'] ?? 0;

        // Get today's transaction count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as transaction_count
            FROM transactions
            WHERE DATE(transaction_date) = ?
        ");
        $stmt->execute([$today]);
        $transaction_count = $stmt->fetch(PDO::FETCH_ASSOC)['transaction_count'] ?? 0;

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

        $dashboard_data = [
            'today_sales' => (float)$today_sales,
            'transaction_count' => (int)$transaction_count,
            'top_products' => $topProductNames ?: '-'
        ];
    } catch (Exception $e) {
        // If there's an error, keep default values
        error_log("Dashboard data fetch error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Register System</title>
    <link rel="stylesheet" href="css/common.css">
</head>
<body>
    <div class="page-container">
        <header>
            <h1>Cash Register System</h1>
            <div class="header-content">
                <div>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</span>
                </div>
                <div class="header-buttons">
                    <a href="pos_system.html" class="btn btn-primary btn-sm">POS System</a>
                    <a href="product_management.html" class="btn btn-secondary btn-sm">Product Management</a>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="#" class="nav-btn active" onclick="showSection('dashboard')">Dashboard</a></li>
                    <li><a href="#" class="nav-btn" onclick="showSection('products')">Products</a></li>
                    <li><a href="#" class="nav-btn" onclick="showSection('sales')">Sales</a></li>
                    <li><a href="#" class="nav-btn" onclick="showSection('reports')">Reports</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <!-- Dashboard Section -->
            <section id="dashboard" class="section active">
                <h2>Dashboard</h2>
                <div class="dashboard-cards">
                    <div class="card">
                        <h3>Today's Sales</h3>
                        <p class="value">Rp <?php echo number_format($dashboard_data['today_sales']); ?></p>
                    </div>
                    <div class="card">
                        <h3>Number of Transactions</h3>
                        <p class="value"><?php echo $dashboard_data['transaction_count']; ?></p>
                    </div>
                    <div class="card">
                        <h3>Top Selling Items</h3>
                        <p class="value"><?php echo htmlspecialchars($dashboard_data['top_products']); ?></p>
                    </div>
                </div>
            </section>

            <!-- Products Section -->
            <section id="products" class="section">
                <div class="section-header">
                    <h2>Product Management</h2>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <!-- Product data will be loaded here -->
                    </tbody>
                </table>
            </section>

            <!-- Sales Section -->
            <section id="sales" class="section">
                <h2>Sales Transaction</h2>
                <div class="sales-container">
                    <div class="left-panel">
                        <div class="search-box">
                            <input type="text" id="searchProduct" placeholder="Search product...">
                            <button class="btn btn-search">Search</button>
                        </div>

                        <div class="products-list" id="productsList">
                            <!-- Products will be loaded here -->
                        </div>
                    </div>

                    <div class="right-panel">
                        <div class="cart">
                            <h3>Cart</h3>
                            <div class="cart-items" id="cartItems">
                                <!-- Cart items will be displayed here -->
                            </div>
                            <div class="cart-summary">
                                <div class="summary-row">
                                    <span>Subtotal:</span>
                                    <span id="subtotal">Rp 0</span>
                                </div>
                                <div class="summary-row">
                                    <span>Discount:</span>
                                    <span id="discount">Rp 0</span>
                                </div>
                                <div class="summary-row total">
                                    <span>Total:</span>
                                    <span id="total">Rp 0</span>
                                </div>
                            </div>

                            <div class="payment-section">
                                <label for="payment">Payment:</label>
                                <input type="number" id="payment" placeholder="Enter payment amount">
                                <div class="change">
                                    <span>Change:</span>
                                    <span id="change">Rp 0</span>
                                </div>
                                <button class="btn btn-success" onclick="processPayment()">Process Payment</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Reports Section -->
            <section id="reports" class="section">
                <h2>Reports</h2>
                <div class="report-filters">
                    <label for="startDate">Start Date:</label>
                    <input type="date" id="startDate">
                    <label for="endDate">End Date:</label>
                    <input type="date" id="endDate">
                    <button class="btn btn-primary" onclick="generateSalesReport()">Generate Report</button>
                </div>

                <div class="report-content">
                    <canvas id="salesChart"></canvas>
                </div>
            </section>
        </main>
    </div>


    <!-- Load Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/main.js"></script>

    <script>
        // Initialize the report chart when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Set default dates to last 7 days
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - 7);

            document.getElementById('startDate').valueAsDate = startDate;
            document.getElementById('endDate').valueAsDate = endDate;
        });
    </script>
</body>
</html>
