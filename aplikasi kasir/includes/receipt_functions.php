<?php
require_once 'db_connect.php';

// Function to generate a receipt as HTML
function generateReceiptHTML($transaction_id) {
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
        $stmt->execute([$transaction_id]);
        $transaction_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($transaction_items)) {
            return false;
        }
        
        // Get the first row for transaction info (all rows have the same transaction info)
        $transaction = $transaction_items[0];
        
        // Generate HTML receipt
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Receipt - #' . $transaction_id . '</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    max-width: 400px; 
                    margin: 0 auto; 
                    padding: 20px;
                    font-size: 14px;
                }
                .receipt-header { 
                    text-align: center; 
                    border-bottom: 2px solid #333; 
                    padding-bottom: 10px; 
                    margin-bottom: 15px;
                }
                .receipt-title { 
                    font-size: 18px; 
                    font-weight: bold; 
                    margin-bottom: 5px;
                }
                .receipt-subtitle { 
                    font-size: 12px; 
                    color: #666;
                }
                .receipt-info { 
                    margin-bottom: 15px; 
                    font-size: 12px;
                }
                .items-table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-bottom: 15px;
                }
                .items-table th, .items-table td { 
                    border-bottom: 1px solid #ddd; 
                    padding: 5px 0;
                    text-align: left;
                }
                .items-table th { 
                    font-weight: bold; 
                }
                .total-row { 
                    font-weight: bold; 
                    border-top: 2px solid #333;
                    margin-top: 10px;
                    padding-top: 10px;
                }
                .receipt-footer { 
                    text-align: center; 
                    margin-top: 20px; 
                    font-size: 12px; 
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="receipt-header">
                <div class="receipt-title">TOKO SERBA ADA</div>
                <div class="receipt-subtitle">Jl. Raya No. 123, Jakarta</div>
                <div class="receipt-subtitle">Telp: 021-123456</div>
            </div>
            
            <div class="receipt-info">
                <div><strong>Receipt #: </strong>' . $transaction_id . '</div>
                <div><strong>Date: </strong>' . date('d/m/Y H:i:s', strtotime($transaction['transaction_date'])) . '</div>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';
        
        $total = 0;
        foreach ($transaction_items as $item) {
            $item_total = $item['price'] * $item['quantity'];
            $total += $item_total;
            
            $html .= '
                    <tr>
                        <td>' . htmlspecialchars($item['product_name']) . '</td>
                        <td>' . $item['quantity'] . '</td>
                        <td>Rp ' . number_format($item['price']) . '</td>
                        <td>Rp ' . number_format($item_total) . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
            
            <div class="total-row">
                <div style="display: flex; justify-content: space-between;">
                    <span>Total:</span>
                    <span>Rp ' . number_format($transaction['total_amount']) . '</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Payment:</span>
                    <span>Rp ' . number_format($transaction['payment_amount']) . '</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Change:</span>
                    <span>Rp ' . number_format($transaction['change_amount']) . '</span>
                </div>
            </div>
            
            <div class="receipt-footer">
                <div>Payment Method: ' . ucfirst($transaction['payment_method']) . '</div>
                <div>Thank you for your purchase!</div>
                <div>Please keep this receipt for your records</div>
            </div>
        </body>
        </html>';
        
        return $html;
    } catch(PDOException $e) {
        return false;
    }
}

// Function to generate receipt as plain text (for printing)
function generateReceiptText($transaction_id) {
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
        $stmt->execute([$transaction_id]);
        $transaction_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($transaction_items)) {
            return false;
        }
        
        $transaction = $transaction_items[0];
        
        $receipt = "CASH REGISTER SYSTEM\n";
        $receipt .= "Jl. Contoh No. 123, Kota\n";
        $receipt .= "Telp: (021) 12345678\n";
        $receipt .= "================================\n";
        $receipt .= "Receipt #: " . $transaction_id . "\n";
        $receipt .= "Date: " . date('d/m/Y H:i:s', strtotime($transaction['transaction_date'])) . "\n";
        $receipt .= "================================\n";
        
        foreach ($transaction_items as $item) {
            $item_total = $item['price'] * $item['quantity'];
            $receipt .= sprintf(
                "%-15s %2dx %8s = %8s\n",
                substr($item['product_name'], 0, 15),
                $item['quantity'],
                'Rp' . number_format($item['price']),
                'Rp' . number_format($item_total)
            );
        }
        
        $receipt .= "================================\n";
        $receipt .= sprintf("%-25s %8s\n", "Total:", "Rp" . number_format($transaction['total_amount']));
        $receipt .= sprintf("%-25s %8s\n", "Payment:", "Rp" . number_format($transaction['payment_amount']));
        $receipt .= sprintf("%-25s %8s\n", "Change:", "Rp" . number_format($transaction['change_amount']));
        $receipt .= "Payment Method: " . ucfirst($transaction['payment_method']) . "\n";
        $receipt .= "================================\n";
        $receipt .= "Thank you for your purchase!\n";
        $receipt .= "Please keep this receipt for your records\n";
        
        return $receipt;
    } catch(PDOException $e) {
        return false;
    }
}
?>