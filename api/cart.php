<?php
// Suppress all errors/warnings from appearing in output
error_reporting(0);
ini_set('display_errors', 0);

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';

// Clear any output buffers to ensure clean JSON
if (ob_get_level()) ob_end_clean();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {
    case 'add':
        $data = json_decode(file_get_contents('php://input'), true);
        $product_id = (int)($data['product_id'] ?? 0);
        $quantity = (int)($data['quantity'] ?? 1);
        $variant_id = isset($data['variant_id']) ? (int)$data['variant_id'] : null;
        $variant_details = $data['variant_details'] ?? null;
        
        if ($product_id > 0 && $quantity > 0) {
            // Create unique cart key (product_id + variant_id)
            $cart_key = $product_id . ($variant_id ? '_' . $variant_id : '');
            
            // Check if item already in cart
            if (isset($_SESSION['cart'][$cart_key])) {
                $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$cart_key] = [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'variant_id' => $variant_id,
                    'variant_details' => $variant_details,
                    'added_at' => time()
                ];
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Product added to cart',
                'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        }
        break;
        
    case 'update':
        $data = json_decode(file_get_contents('php://input'), true);
        $cart_key = $data['cart_key'] ?? '';
        $quantity = (int)($data['quantity'] ?? 0);
        
        if (isset($_SESSION['cart'][$cart_key])) {
            if ($quantity > 0) {
                $_SESSION['cart'][$cart_key]['quantity'] = $quantity;
                echo json_encode([
                    'success' => true,
                    'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
                ]);
            } else {
                unset($_SESSION['cart'][$cart_key]);
                echo json_encode([
                    'success' => true,
                    'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
        }
        break;
        
    case 'remove':
        $cart_key = $_GET['key'] ?? '';
        if (isset($_SESSION['cart'][$cart_key])) {
            unset($_SESSION['cart'][$cart_key]);
            echo json_encode([
                'success' => true,
                'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found']);
        }
        break;
        
    case 'get':
        $cart_items = [];
        $total = 0;
        
        foreach ($_SESSION['cart'] as $cart_key => $item) {
            // Fetch product details
            $product_id = $item['product_id'];
            $variant_id = $item['variant_id'] ?? null;
            
            $query = "SELECT * FROM products WHERE id = $product_id";
            $result = mysqli_query($conn, $query);
            $product = mysqli_fetch_assoc($result);
            
            if ($product) {
                $price = $product['price'];
                $name = $product['name'];
                $image = $product['image_main'];
                $variant_text = '';
                
                // If variant exists, get variant details
                if ($variant_id) {
                    $variant_query = "SELECT * FROM product_variants WHERE id = $variant_id";
                    $variant_result = mysqli_query($conn, $variant_query);
                    $variant = mysqli_fetch_assoc($variant_result);
                    
                    if ($variant) {
                        $price += $variant['price_adjustment'];
                        $variant_parts = [];
                        if ($variant['size']) $variant_parts[] = $variant['size'];
                        if ($variant['color']) $variant_parts[] = $variant['color'];
                        if ($variant['material']) $variant_parts[] = $variant['material'];
                        $variant_text = implode(' / ', $variant_parts);
                    }
                }
                
                $subtotal = $price * $item['quantity'];
                $total += $subtotal;
                
                $cart_items[] = [
                    'cart_key' => $cart_key,
                    'product_id' => $product_id,
                    'name' => $name,
                    'image' => $image,
                    'price' => $price,
                    'quantity' => $item['quantity'],
                    'variant' => $variant_text,
                    'subtotal' => $subtotal
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'items' => $cart_items,
            'total' => $total,
            'count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
        ]);
        break;
        
    case 'clear':
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true, 'message' => 'Cart cleared']);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
exit;
