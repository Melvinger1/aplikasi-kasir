<?php
// Test the API endpoint directly

// Set appropriate headers
header('Content-Type: application/json');

require_once 'includes/db_connect.php';
require_once 'includes/sales_functions.php';

// Test with today's date
$today = date('Y-m-d');
$report = getSalesReport($today, $today);

if ($report !== false) {
    echo json_encode([
        'status' => 'success',
        'data' => $report,
        'test_date' => $today,
        'result_count' => count($report)
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch sales report',
        'test_date' => $today
    ]);
}
?>