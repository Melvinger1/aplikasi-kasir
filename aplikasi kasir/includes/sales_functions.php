<?php
/**
 * Cash Register System - Sales Functions
 *
 * Fungsi-fungsi untuk manajemen penjualan dan transaksi
 * - Proses pembayaran
 * - Validasi data transaksi
 * - Manajemen database transaksi
 */

require_once 'db_connect.php';

/**
 * Menambahkan transaksi penjualan baru ke database
 *
 * @param array $items Produk-produk dalam transaksi
 * @param float $total_amount Jumlah total pembelian
 * @param float $payment_amount Jumlah pembayaran
 * @param float $change_amount Jumlah kembalian
 * @param string $payment_method Metode pembayaran
 * @param int|null $customer_id ID pelanggan (opsional)
 * @return int|bool ID transaksi jika berhasil, false jika gagal
 */
function addSale($items, $total_amount, $payment_amount, $change_amount, $payment_method = 'cash', $customer_id = null) {
    global $pdo;

    try {
        // Mulai transaksi database
        $pdo->beginTransaction();

        // Masukkan record transaksi
        $stmt = $pdo->prepare("INSERT INTO transactions (customer_id, total_amount, payment_amount, change_amount, payment_method, transaction_date) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$customer_id, $total_amount, $payment_amount, $change_amount, $payment_method]);

        // Dapatkan ID transaksi yang baru saja dimasukkan
        $transaction_id = $pdo->lastInsertId();

        // Masukkan setiap item ke tabel transaction_items
        foreach ($items as $item) {
            $item_id = $item['id'] ?? $item['productId'] ?? null;

            // Validasi bahwa kita memiliki item_id yang valid
            if (!$item_id) {
                throw new Exception("Invalid item ID");
            }

            $item_quantity = $item['quantity'] ?? 1;
            $item_price = $item['price'] ?? 0;

            // Validasi jumlah dan harga
            if (!is_numeric($item_quantity) || !is_numeric($item_price)) {
                throw new Exception("Invalid item quantity or price");
            }

            $itemStmt = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $itemStmt->execute([$transaction_id, $item_id, $item_quantity, $item_price]);

            // Check stock before updating (redundant safety check)
            $checkStockStmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $checkStockStmt->execute([$item_id]);
            $currentStock = $checkStockStmt->fetchColumn();
            if ($currentStock < $item_quantity) {
                throw new Exception("Insufficient stock for product ID: " . $item_id);
            }

            // Update stok produk
            $updateStockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $updateStockStmt->execute([$item_quantity, $item_id]);
        }

        // Commit transaksi
        $pdo->commit();

        return $transaction_id;
    } catch(PDOException $e) {
        // Rollback transaksi jika terjadi error
        $pdo->rollback();
        return false;
    }
}

/**
 * Memvalidasi data pembayaran sebelum diproses
 *
 * @param array $items Produk-produk dalam keranjang
 * @param float $payment_amount Jumlah pembayaran
 * @return array Hasil validasi
 */
function validatePayment($items, $payment_amount) {
    global $pdo;
    $total_amount = 0;

    foreach ($items as $item) {
        // Tangani format dari kedua sumber: 'id' dari index.php dan 'productId' dari pos_system.html
        $item_id = $item['id'] ?? $item['productId'] ?? null;

        // Validasi bahwa kita memiliki item_id yang valid
        if (!$item_id) {
            return [
                'status' => 'error',
                'message' => 'Invalid item ID in cart'
            ];
        }

        // Check if product exists and get stock
        try {
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$item_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                return [
                    'status' => 'error',
                    'message' => 'Product not found for item ID: ' . $item_id
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => 'Database error while checking product'
            ];
        }

        $item_price = $item['price'] ?? 0;
        $item_quantity = $item['quantity'] ?? 1;

        // Validasi harga dan jumlah numerik
        if (!is_numeric($item_price) || !is_numeric($item_quantity)) {
            return [
                'status' => 'error',
                'message' => 'Invalid price or quantity for item ID: ' . $item_id
            ];
        }

        // Check stock availability
        if ($item_quantity > $product['stock']) {
            return [
                'status' => 'error',
                'message' => 'Insufficient stock for item ID: ' . $item_id . '. Available: ' . $product['stock'] . ', Requested: ' . $item_quantity
            ];
        }

        $item_total = $item_price * $item_quantity;

        // Pastikan tidak ada nilai negatif
        if ($item_total < 0) {
            return [
                'status' => 'error',
                'message' => 'Invalid negative price or quantity for item ID: ' . $item_id
            ];
        }

        $total_amount += $item_total;
    }

    // Cek apakah jumlah pembayaran cukup
    if ($payment_amount < $total_amount) {
        return [
            'status' => 'error',
            'message' => 'Insufficient payment amount',
            'required' => $total_amount,
            'provided' => $payment_amount
        ];
    }

    // Hitung kembalian
    $change = $payment_amount - $total_amount;

    return [
        'status' => 'success',
        'total' => $total_amount,
        'change' => $change
    ];
}

/**
 * Memproses pembayaran dan menyimpan transaksi ke database
 *
 * @param array $items Produk-produk dalam transaksi
 * @param float $payment_amount Jumlah pembayaran
 * @param string $payment_method Metode pembayaran
 * @param int|null $customer_id ID pelanggan (opsional)
 * @return array Hasil proses pembayaran
 */
function processPayment($items, $payment_amount, $payment_method = 'cash', $customer_id = null) {
    // Validasi pembayaran terlebih dahulu
    $validation = validatePayment($items, $payment_amount);

    if ($validation['status'] === 'error') {
        return $validation;
    }

    // Tambahkan penjualan ke database
    $transaction_id = addSale(
        $items,
        $validation['total'],
        $payment_amount,
        $validation['change'],
        $payment_method,
        $customer_id
    );

    if ($transaction_id) {
        return [
            'status' => 'success',
            'transaction_id' => $transaction_id,
            'total' => $validation['total'],
            'change' => $validation['change'],
            'message' => 'Payment processed successfully'
        ];
    } else {
        return [
            'status' => 'error',
            'message' => 'Failed to process payment'
        ];
    }
}

/**
 * Mendapatkan semua transaksi
 *
 * @param int $limit Jumlah maksimum transaksi yang dikembalikan
 * @return array|bool Array transaksi jika berhasil, false jika gagal
 */
function getAllTransactions($limit = 50) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT t.*,
                   SUM(ti.quantity * ti.price) as calculated_total
            FROM transactions t
            LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
            GROUP BY t.id
            ORDER BY t.transaction_date DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return false;
    }
}

/**
 * Mendapatkan detail transaksi berdasarkan ID
 *
 * @param int $id ID transaksi
 * @return array|bool Array detail transaksi jika berhasil, false jika gagal
 */
function getTransactionById($id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT t.*,
                   ti.product_id,
                   ti.quantity,
                   ti.price,
                   p.name as product_name
            FROM transactions t
            LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
            LEFT JOIN products p ON ti.product_id = p.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return false;
    }
}

/**
 * Mendapatkan laporan penjualan berdasarkan rentang tanggal
 *
 * @param string $start_date Tanggal awal (format: Y-m-d)
 * @param string $end_date Tanggal akhir (format: Y-m-d)
 * @return array|bool Array laporan jika berhasil, false jika gagal
 */
function getSalesReport($start_date, $end_date) {
    global $pdo;

    try {
        // Ensure proper date format and include full day range
        $start_datetime = $start_date . ' 00:00:00';
        $end_datetime = $end_date . ' 23:59:59';

        $stmt = $pdo->prepare("
            SELECT
                DATE(transaction_date) as date,
                COUNT(*) as transaction_count,
                SUM(total_amount) as daily_total
            FROM transactions
            WHERE transaction_date BETWEEN ? AND ?
            GROUP BY DATE(transaction_date)
            ORDER BY DATE(transaction_date)
        ");
        $stmt->execute([$start_datetime, $end_datetime]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Log query results for debugging
        error_log("Sales report query results count: " . count($result));

        return $result;
    } catch(PDOException $e) {
        error_log("Sales report query error: " . $e->getMessage());
        return false;
    }
}

/**
 * Mendapatkan produk-produk terlaris
 *
 * @param int $limit Jumlah maksimum produk yang dikembalikan
 * @return array|bool Array produk terlaris jika berhasil, false jika gagal
 */
function getTopSellingProducts($limit = 10) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT
                p.name as product_name,
                SUM(ti.quantity) as total_sold
            FROM transaction_items ti
            JOIN products p ON ti.product_id = p.id
            GROUP BY ti.product_id
            ORDER BY total_sold DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return false;
    }
}
?>