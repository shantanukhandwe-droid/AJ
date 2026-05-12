<?php 
require_once 'auth.php';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'whatsapp_number' => clean_input($_POST['whatsapp_number']),
        'razorpay_key_id' => clean_input($_POST['razorpay_key_id']),
        'razorpay_key_secret' => clean_input($_POST['razorpay_key_secret']),
        'shipping_fee_threshold' => (float)$_POST['shipping_fee_threshold'],
        'shipping_fee' => (float)$_POST['shipping_fee'],
        'cod_enabled' => isset($_POST['cod_enabled']) ? '1' : '0',
        'razorpay_enabled' => isset($_POST['razorpay_enabled']) ? '1' : '0'
    ];
    
    foreach ($settings as $key => $value) {
        $value_escaped = is_numeric($value) ? $value : "'$value'";
        mysqli_query($conn, "UPDATE settings SET setting_value = $value_escaped WHERE setting_key = '$key'");
    }
    
    header('Location: settings.php?saved=1');
    exit;
}

// Get current settings
$settings_query = "SELECT * FROM settings";
$settings_result = mysqli_query($conn, $settings_query);
$settings = [];
while ($row = mysqli_fetch_assoc($settings_result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings — Reverie Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin-style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="top-bar">
        <h1>Settings</h1>
        <div class="admin-info">
            <span>Welcome, <?php echo htmlspecialchars($admin_username); ?></span>
            <a href="?logout=1" class="btn-logout">Logout</a>
        </div>
    </div>

    <?php if (isset($_GET['saved'])): ?>
    <div style="background: #D1FAE5; border: 1px solid #10B981; color: #065F46; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px;">
        Settings saved successfully!
    </div>
    <?php endif; ?>

    <form method="POST">
        <div class="content-section">
            <div class="section-header">
                <h2>WhatsApp Integration</h2>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>WhatsApp Number (with country code, no +)</label>
                    <input type="text" name="whatsapp_number" value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>" placeholder="919876543210">
                    <small style="color: #6B7280; font-size: 11px; margin-top: 4px;">Example: 919876543210 for +91 9876543210</small>
                </div>
            </div>
        </div>

        <div class="content-section">
            <div class="section-header">
                <h2>Razorpay Payment Gateway</h2>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="razorpay_enabled" <?php echo ($settings['razorpay_enabled'] ?? 0) ? 'checked' : ''; ?>>
                    <span>Enable Razorpay payments</span>
                </label>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Razorpay Key ID</label>
                    <input type="text" name="razorpay_key_id" value="<?php echo htmlspecialchars($settings['razorpay_key_id'] ?? ''); ?>" placeholder="rzp_test_xxxxx">
                </div>

                <div class="form-group">
                    <label>Razorpay Key Secret</label>
                    <input type="password" name="razorpay_key_secret" value="<?php echo htmlspecialchars($settings['razorpay_key_secret'] ?? ''); ?>" placeholder="Enter secret key">
                </div>
            </div>
        </div>

        <div class="content-section">
            <div class="section-header">
                <h2>Shipping & Payment Settings</h2>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="cod_enabled" <?php echo ($settings['cod_enabled'] ?? 1) ? 'checked' : ''; ?>>
                    <span>Enable Cash on Delivery (COD)</span>
                </label>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Free Shipping Threshold (₹)</label>
                    <input type="number" step="0.01" name="shipping_fee_threshold" value="<?php echo htmlspecialchars($settings['shipping_fee_threshold'] ?? '999'); ?>">
                    <small style="color: #6B7280; font-size: 11px; margin-top: 4px;">Orders above this amount get free shipping</small>
                </div>

                <div class="form-group">
                    <label>Shipping Fee (₹)</label>
                    <input type="number" step="0.01" name="shipping_fee" value="<?php echo htmlspecialchars($settings['shipping_fee'] ?? '0'); ?>">
                    <small style="color: #6B7280; font-size: 11px; margin-top: 4px;">Charged if order is below threshold</small>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Save Settings</button>
        </div>
    </form>
</div>

</body>
</html>
