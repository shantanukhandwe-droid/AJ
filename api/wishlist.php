<?php
// Wishlist API - Add/Remove items from wishlist
session_start();
require_once '../includes/db.php';

// Ensure clean JSON output
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to use wishlist']);
    exit;
}

$customer_id = (int)$_SESSION['customer_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($action === 'add') {
    // ADD TO WISHLIST
    $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit;
    }
    
    // Check if already in wishlist
    $check = mysqli_query($conn, "SELECT id FROM wishlist WHERE customer_id = $customer_id AND product_id = $product_id");
    
    if (mysqli_num_rows($check) > 0) {
        echo json_encode(['success' => false, 'message' => 'Already in wishlist', 'in_wishlist' => true]);
        exit;
    }
    
    // Add to wishlist
    $insert = "INSERT INTO wishlist (customer_id, product_id) VALUES ($customer_id, $product_id)";
    
    if (mysqli_query($conn, $insert)) {
        // Get wishlist count
        $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM wishlist WHERE customer_id = $customer_id");
        $count = mysqli_fetch_assoc($count_result)['count'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Added to wishlist',
            'wishlist_count' => $count,
            'in_wishlist' => true
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
    }
    
} elseif ($action === 'remove') {
    // REMOVE FROM WISHLIST
    $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit;
    }
    
    $delete = "DELETE FROM wishlist WHERE customer_id = $customer_id AND product_id = $product_id";
    
    if (mysqli_query($conn, $delete)) {
        // Get wishlist count
        $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM wishlist WHERE customer_id = $customer_id");
        $count = mysqli_fetch_assoc($count_result)['count'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Removed from wishlist',
            'wishlist_count' => $count,
            'in_wishlist' => false
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
    }
    
} elseif ($action === 'check') {
    // CHECK IF PRODUCT IS IN WISHLIST
    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'in_wishlist' => false]);
        exit;
    }
    
    $check = mysqli_query($conn, "SELECT id FROM wishlist WHERE customer_id = $customer_id AND product_id = $product_id");
    $in_wishlist = mysqli_num_rows($check) > 0;
    
    echo json_encode(['success' => true, 'in_wishlist' => $in_wishlist]);
    
} elseif ($action === 'count') {
    // GET WISHLIST COUNT
    $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM wishlist WHERE customer_id = $customer_id");
    $count = mysqli_fetch_assoc($count_result)['count'];
    
    echo json_encode(['success' => true, 'count' => $count]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
