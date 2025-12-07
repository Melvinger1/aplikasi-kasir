<?php
// First, let's see if the API is even being hit
error_log("=== PRODUCT API LOADED ===");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);

// Log headers (using a more compatible approach)
$headerLog = [];
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0) {
        $header = str_replace('_', '-', substr($key, 5));
        $header = ucwords(strtolower($header), '-');
        $headerLog[$header] = $value;
    }
}
error_log("Headers: " . print_r($headerLog, true));

// Log raw input
$rawInput = file_get_contents('php://input');
error_log("Raw Input: " . $rawInput);

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/product_functions.php';

// Log request for debugging
error_log("Product API Request - Method: " . $_SERVER['REQUEST_METHOD']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    error_log("Product API Request - Raw Input: " . $rawInput);
    $input = json_decode($rawInput, true);
    error_log("Product API Request - Decoded Input: " . print_r($input, true));
}

// Handle different request methods
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET requests
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'getProducts':
            $products = getAllProducts();
            if ($products !== false) {
                echo json_encode(['status' => 'success', 'data' => $products]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to fetch products']);
            }
            break;

        case 'getProduct':
            $id = $_GET['id'] ?? '';
            if ($id) {
                $product = getProductById($id);
                if ($product !== false) {
                    echo json_encode(['status' => 'success', 'data' => $product]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Product not found']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Product ID not provided']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST requests
    // Important: We need to handle both JSON and form data properly
    $input = json_decode(file_get_contents('php://input'), true);

    // Log what we received
    error_log("Product API - Raw input: " . file_get_contents('php://input'));
    error_log("Product API - Decoded input: " . print_r($input, true));
    error_log("Product API - POST data: " . print_r($_POST, true));

    // Try to get action from both sources
    $action = '';
    if ($input && isset($input['action'])) {
        $action = $input['action'];
    } elseif (isset($_POST['action'])) {
        $action = $_POST['action'];
    }

    error_log("Product API - Processing action: " . $action);

    switch ($action) {
        case 'addProduct':
            // Get data from JSON input or POST data
            $name = '';
            $price = 0;
            $stock = 0;
            $category = '';

            if ($input) {
                $name = $input['name'] ?? '';
                $price = floatval($input['price'] ?? 0);
                $stock = intval($input['stock'] ?? 0);
                $category = $input['category'] ?? '';
            } else {
                $name = $_POST['name'] ?? '';
                $price = floatval($_POST['price'] ?? 0);
                $stock = intval($_POST['stock'] ?? 0);
                $category = $_POST['category'] ?? '';
            }

            error_log("Product API - AddProduct params: name='$name', price='$price', stock='$stock', category='$category'");

            // Validate inputs
            if (empty(trim($name))) {
                error_log("Product API - AddProduct validation failed: Empty name");
                echo json_encode(['status' => 'error', 'message' => 'Product name is required']);
                exit;
            }

            if (!is_numeric($price) || $price < 0) {
                error_log("Product API - AddProduct validation failed: Invalid price - '$price'");
                echo json_encode(['status' => 'error', 'message' => 'Price must be a non-negative number']);
                exit;
            }

            if (!is_numeric($stock) || $stock < 0) {
                error_log("Product API - AddProduct validation failed: Invalid stock - '$stock'");
                echo json_encode(['status' => 'error', 'message' => 'Stock must be a non-negative number']);
                exit;
            }

            try {
                error_log("Product API - Calling addProduct function");
                $result = addProduct($name, $price, $stock, $category);
                error_log("Product API - AddProduct result: " . ($result ? 'success' : 'failure'));

                if ($result) {
                    echo json_encode(['status' => 'success', 'message' => 'Product added successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to add product']);
                }
            } catch (Exception $e) {
                error_log("Product API - AddProduct exception: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;

        case 'updateProduct':
            // Get data from JSON input or POST data
            $id = '';
            $name = '';
            $price = 0;
            $stock = 0;
            $category = '';

            if ($input) {
                $id = $input['id'] ?? '';
                $name = $input['name'] ?? '';
                $price = floatval($input['price'] ?? 0);
                $stock = intval($input['stock'] ?? 0);
                $category = $input['category'] ?? '';
            } else {
                $id = $_POST['id'] ?? '';
                $name = $_POST['name'] ?? '';
                $price = floatval($_POST['price'] ?? 0);
                $stock = intval($_POST['stock'] ?? 0);
                $category = $_POST['category'] ?? '';
            }

            error_log("Product API - UpdateProduct params: id='$id', name='$name', price='$price', stock='$stock', category='$category'");

            // Validate inputs
            if (empty($id)) {
                error_log("Product API - UpdateProduct validation failed: Empty ID");
                echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
                exit;
            }

            if (empty(trim($name))) {
                error_log("Product API - UpdateProduct validation failed: Empty name");
                echo json_encode(['status' => 'error', 'message' => 'Product name is required']);
                exit;
            }

            if (!is_numeric($price) || $price < 0) {
                error_log("Product API - UpdateProduct validation failed: Invalid price - '$price'");
                echo json_encode(['status' => 'error', 'message' => 'Price must be a non-negative number']);
                exit;
            }

            if (!is_numeric($stock) || $stock < 0) {
                error_log("Product API - UpdateProduct validation failed: Invalid stock - '$stock'");
                echo json_encode(['status' => 'error', 'message' => 'Stock must be a non-negative number']);
                exit;
            }

            try {
                error_log("Product API - Calling updateProduct function with id=$id");
                $result = updateProduct($id, $name, $price, $stock, $category);
                error_log("Product API - UpdateProduct result: " . ($result ? 'success' : 'failure'));

                if ($result) {
                    echo json_encode(['status' => 'success', 'message' => 'Product updated successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update product']);
                }
            } catch (Exception $e) {
                error_log("Product API - UpdateProduct exception: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;

        case 'deleteProduct':
            // Get data from JSON input or POST data
            $id = '';

            if ($input) {
                $id = $input['id'] ?? '';
            } else {
                $id = $_POST['id'] ?? '';
            }

            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
                exit;
            }

            try {
                $result = deleteProduct($id);
                if ($result) {
                    echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to delete product']);
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>