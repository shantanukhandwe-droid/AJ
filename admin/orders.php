<?php 
require_once 'auth.php';

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = clean_input($_POST['order_status']);
    mysqli_query($conn, "UPDATE orders SET order_status = '$new_status' WHERE id = $order_id");
    header('Location: orders.php?updated=1');
    exit;
}

// Get order details for modal
$view_order = null;
if (isset($_GET['view'])) {
    $view_id = (int)$_GET['view'];
    $order_query = "SELECT * FROM orders WHERE id = $view_id";
    $view_order = mysqli_fetch_assoc(mysqli_query($conn, $order_query));
    
    if ($view_order) {
        $items_query = "SELECT * FROM order_items WHERE order_id = $view_id";
        $order_items = mysqli_query($conn, $items_query);
    }
}

// Get all orders
$orders_query = "SELECT * FROM orders ORDER BY created_at DESC";
$orders = mysqli_query($conn, $orders_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders — Reverie Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin-style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="top-bar">
        <h1>Orders</h1>
        <div class="admin-info">
            <span>Welcome, <?php echo htmlspecialchars($admin_username); ?></span>
            <a href="?logout=1" class="btn-logout">Logout</a>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
    <div style="background: #D1FAE5; border: 1px solid #10B981; color: #065F46; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px;">
        Order status updated successfully!
    </div>
    <?php endif; ?>

    <div class="content-section">
        <div class="section-header">
            <h2>All Orders</h2>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                    <tr>
                        <td><strong><?php echo $order['order_number']; ?></strong></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                        <td><?php echo format_price($order['total']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $order['payment_method']; ?>">
                                <?php echo strtoupper($order['payment_method']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="order_status" onchange="this.form.submit()" style="padding: 4px 8px; font-size: 11px; border-radius: 4px;">
                                    <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $order['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="shipped" <?php echo $order['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <a href="?view=<?php echo $order['id']; ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 12px;">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($view_order): ?>
<div class="modal-overlay" onclick="window.location='orders.php'">
    <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 700px; background: white; border-radius: 8px; padding: 32px; position: relative;">
        <a href="orders.php" style="position: absolute; top: 16px; right: 16px; font-size: 24px; color: #9CA3AF; text-decoration: none;">&times;</a>
        
        <h2 style="margin-bottom: 24px; font-size: 24px;">Order Details</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #E5E7EB;">
            <div>
                <div style="font-size: 12px; color: #6B7280; margin-bottom: 4px;">Order Number</div>
                <div style="font-weight: 600;"><?php echo $view_order['order_number']; ?></div>
            </div>
            <div>
                <div style="font-size: 12px; color: #6B7280; margin-bottom: 4px;">Order Date</div>
                <div><?php echo date('M d, Y H:i', strtotime($view_order['created_at'])); ?></div>
            </div>
            <div>
                <div style="font-size: 12px; color: #6B7280; margin-bottom: 4px;">Payment Method</div>
                <div><span class="badge badge-<?php echo $view_order['payment_method']; ?>"><?php echo strtoupper($view_order['payment_method']); ?></span></div>
            </div>
            <div>
                <div style="font-size: 12px; color: #6B7280; margin-bottom: 4px;">Order Status</div>
                <div><span class="badge badge-<?php echo $view_order['order_status']; ?>"><?php echo ucfirst($view_order['order_status']); ?></span></div>
            </div>
        </div>

        <div style="margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #E5E7EB;">
            <h3 style="font-size: 16px; margin-bottom: 12px;">Customer Information</h3>
            <div style="font-size: 14px; color: #374151; line-height: 1.8;">
                <div><strong><?php echo htmlspecialchars($view_order['customer_name']); ?></strong></div>
                <div><?php echo htmlspecialchars($view_order['customer_email']); ?></div>
                <div><?php echo htmlspecialchars($view_order['customer_phone']); ?></div>
                <div style="margin-top: 8px;">
                    <?php echo nl2br(htmlspecialchars($view_order['customer_address'])); ?><br>
                    <?php echo htmlspecialchars($view_order['customer_city']); ?>, <?php echo htmlspecialchars($view_order['customer_state']); ?> - <?php echo htmlspecialchars($view_order['customer_pincode']); ?>
                </div>
            </div>
        </div>

        <div style="margin-bottom: 24px;">
            <h3 style="font-size: 16px; margin-bottom: 12px;">Order Items</h3>
            <table style="width: 100%; font-size: 14px;">
                <?php while ($item = mysqli_fetch_assoc($order_items)): ?>
                <tr style="border-bottom: 1px solid #F3F4F6;">
                    <td style="padding: 8px 0;">
                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                        <?php if ($item['variant_info']): ?>
                        <br><small style="color: #6B7280;"><?php echo htmlspecialchars($item['variant_info']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right; padding: 8px 0;">×<?php echo $item['quantity']; ?></td>
                    <td style="text-align: right; padding: 8px 0;"><?php echo format_price($item['price']); ?></td>
                    <td style="text-align: right; padding: 8px 0;"><strong><?php echo format_price($item['price'] * $item['quantity']); ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div style="text-align: right; font-size: 14px;">
            <div style="display: flex; justify-content: flex-end; gap: 60px; margin-bottom: 8px;">
                <span>Subtotal:</span>
                <span><?php echo format_price($view_order['subtotal']); ?></span>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 60px; margin-bottom: 8px;">
                <span>Shipping:</span>
                <span><?php echo $view_order['shipping_fee'] > 0 ? format_price($view_order['shipping_fee']) : 'Free'; ?></span>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 60px; font-size: 18px; font-weight: 600; padding-top: 12px; border-top: 2px solid #E5E7EB;">
                <span>Total:</span>
                <span><?php echo format_price($view_order['total']); ?></span>
            </div>
        </div>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
    overflow-y: auto;
}
.modal-content {
    margin: auto;
}
</style>
<?php endif; ?>

</body>
</html>
