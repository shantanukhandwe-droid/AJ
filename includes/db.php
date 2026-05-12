<?php
// Site configuration
require_once __DIR__ . '/../config.php';

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'reverie');

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to sanitize input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Helper function to get setting value
function get_setting($key) {
    global $conn;
    $key = clean_input($key);
    $query = "SELECT setting_value FROM settings WHERE setting_key = '$key'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['setting_value'];
    }
    return null;
}

// Helper function to format price
function format_price($amount) {
    return '₹' . number_format($amount, 0);
}

// Helper function to calculate discount
function calculate_discount($original, $current) {
    if ($original <= $current) return 0;
    return round((($original - $current) / $original) * 100);
}

// Cart functions
function get_cart() {
    return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
}

function add_to_cart($product_id, $quantity = 1, $variant = null) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $cart_key = $product_id . ($variant ? '_' . $variant : '');
    
    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cart_key] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'variant' => $variant
        ];
    }
}

function remove_from_cart($cart_key) {
    if (isset($_SESSION['cart'][$cart_key])) {
        unset($_SESSION['cart'][$cart_key]);
    }
}

function update_cart_quantity($cart_key, $quantity) {
    if (isset($_SESSION['cart'][$cart_key])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$cart_key]);
        } else {
            $_SESSION['cart'][$cart_key]['quantity'] = $quantity;
        }
    }
}

function get_cart_count() {
    $cart = get_cart();
    $count = 0;
    foreach ($cart as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

function get_cart_total() {
    global $conn;
    $cart = get_cart();
    $total = 0;
    
    foreach ($cart as $item) {
        $product_id = clean_input($item['product_id']);
        $query = "SELECT price FROM products WHERE id = '$product_id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $product = mysqli_fetch_assoc($result);
            $total += $product['price'] * $item['quantity'];
        }
    }
    
    return $total;
}

function clear_cart() {
    $_SESSION['cart'] = [];
}

// Generate unique order number
function generate_order_number() {
    return 'REV' . date('Ymd') . rand(1000, 9999);
}
?>
