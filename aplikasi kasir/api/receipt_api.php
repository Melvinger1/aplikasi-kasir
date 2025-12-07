<?php
require_once __DIR__ . '/../includes/receipt_functions.php';

header('Content-Type: application/json');

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $transaction_id = $_GET['transaction_id'] ?? '';
    $format = $_GET['format'] ?? 'html'; // html or text

    if (!$transaction_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Transaction ID is required'
        ]);
        exit;
    }

    if ($format === 'html') {
        $receipt = generateReceiptHTML($transaction_id);
    } else if ($format === 'text') {
        $receipt = generateReceiptText($transaction_id);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid format. Use "html" or "text"'
        ]);
        exit;
    }

    if ($receipt !== false) {
        // For HTML format, return the receipt directly instead of in JSON
        if ($format === 'html') {
            header('Content-Type: text/html');
            echo $receipt;
        } else {
            echo json_encode([
                'status' => 'success',
                'data' => $receipt
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to generate receipt'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>