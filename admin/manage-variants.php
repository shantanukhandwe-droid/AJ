<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$page_title = 'Manage Product Variants';
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
if (!$stmt) {
    die("Error preparing product query: " . $conn->error);
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found. Try a different product_id. <a href='products.php'>Back to products</a>");
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'enable_variants') {
        // Enable variants for this product
        $base_sku = strtoupper(preg_replace('/[^A-Z0-9]/', '', $product['name'])) . '-' . $product_id;
        $stmt = $conn->prepare("UPDATE products SET has_variants = TRUE, base_sku = ? WHERE id = ?");
        if (!$stmt) {
            die("Error preparing update query: " . $conn->error);
        }
        $stmt->bind_param("si", $base_sku, $product_id);
        $stmt->execute();
        
        $success_message = "Variants enabled for this product!";
        
        // Refresh product data
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
    }
    
    elseif ($action === 'add_option') {
        $option_type = htmlspecialchars(strip_tags(trim($_POST['option_type'])));
        $option_value = htmlspecialchars(strip_tags(trim($_POST['option_value'])));
        $display_order = (int)$_POST['display_order'];
        
        $stmt = $conn->prepare("INSERT INTO product_options (product_id, option_type, option_value, display_order) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            $error_message = "Error preparing option insert: " . $conn->error;
        } else {
            $stmt->bind_param("issi", $product_id, $option_type, $option_value, $display_order);
            
            if ($stmt->execute()) {
                $success_message = "Option added successfully!";
            } else {
                $error_message = "Error adding option: " . $conn->error;
            }
        }
    }
    
    elseif ($action === 'delete_option') {
        $option_id = (int)$_POST['option_id'];
        $stmt = $conn->prepare("DELETE FROM product_options WHERE id = ? AND product_id = ?");
        $stmt->bind_param("ii", $option_id, $product_id);
        
        if ($stmt->execute()) {
            $success_message = "Option deleted successfully!";
        }
    }
    
    elseif ($action === 'add_variant') {
        $sku = htmlspecialchars(strip_tags(trim($_POST['sku'])));
        $size = !empty($_POST['size']) ? htmlspecialchars(strip_tags(trim($_POST['size']))) : null;
        $color = !empty($_POST['color']) ? htmlspecialchars(strip_tags(trim($_POST['color']))) : null;
        $material = !empty($_POST['material']) ? htmlspecialchars(strip_tags(trim($_POST['material']))) : null;
        $stock_quantity = (int)$_POST['stock_quantity'];
        $price_adjustment = (float)$_POST['price_adjustment'];
        
        $stmt = $conn->prepare("INSERT INTO product_variants (product_id, sku, size, color, material, stock_quantity, price_adjustment) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $error_message = "Error preparing variant insert: " . $conn->error . " - Make sure the product_variants table exists!";
        } else {
            $stmt->bind_param("issssid", $product_id, $sku, $size, $color, $material, $stock_quantity, $price_adjustment);
            
            if ($stmt->execute()) {
                $success_message = "Variant added successfully!";
            } else {
                $error_message = "Error adding variant: " . $conn->error;
            }
        }
    }
    
    elseif ($action === 'update_variant') {
        $variant_id = (int)$_POST['variant_id'];
        $stock_quantity = (int)$_POST['stock_quantity'];
        $price_adjustment = (float)$_POST['price_adjustment'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE product_variants SET stock_quantity = ?, price_adjustment = ?, is_active = ? WHERE id = ? AND product_id = ?");
        $stmt->bind_param("idiii", $stock_quantity, $price_adjustment, $is_active, $variant_id, $product_id);
        
        if ($stmt->execute()) {
            $success_message = "Variant updated successfully!";
        }
    }
    
    elseif ($action === 'delete_variant') {
        $variant_id = (int)$_POST['variant_id'];
        $stmt = $conn->prepare("DELETE FROM product_variants WHERE id = ? AND product_id = ?");
        $stmt->bind_param("ii", $variant_id, $product_id);
        
        if ($stmt->execute()) {
            $success_message = "Variant deleted successfully!";
        }
    }
}

// Fetch existing options
$options = [];
$options_stmt = $conn->prepare("SELECT * FROM product_options WHERE product_id = ? ORDER BY option_type, display_order");
if ($options_stmt) {
    $options_stmt->bind_param("i", $product_id);
    $options_stmt->execute();
    $options_result = $options_stmt->get_result();
    while ($option = $options_result->fetch_assoc()) {
        $options[$option['option_type']][] = $option;
    }
} else {
    // Table might not exist
    echo "<div style='background: #fff3cd; padding: 15px; margin: 20px; border: 1px solid #ffc107; border-radius: 8px;'>";
    echo "<strong>Warning:</strong> Could not fetch options. Error: " . $conn->error;
    echo "<br>The product_options table might not exist. Did you run the SQL migration?";
    echo "</div>";
}

// Fetch existing variants
$variants = [];
$variants_stmt = $conn->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY `size`, `color`, `material`");
if ($variants_stmt) {
    $variants_stmt->bind_param("i", $product_id);
    $variants_stmt->execute();
    $variants_result = $variants_stmt->get_result();
    $variants = $variants_result->fetch_all(MYSQLI_ASSOC);
} else {
    // Table might not exist
    echo "<div style='background: #fff3cd; padding: 15px; margin: 20px; border: 1px solid #ffc107; border-radius: 8px;'>";
    echo "<strong>Warning:</strong> Could not fetch variants. Error: " . $conn->error;
    echo "<br>The product_variants table might not exist. Did you run the SQL migration?";
    echo "</div>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Product Variants — Reverie Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin-style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

<style>
.variants-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #eee;
}

.page-header h1 {
    font-size: 28px;
    color: #333;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #d4af37;
    color: white;
}

.btn-primary:hover {
    background: #c19b2e;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-small {
    padding: 6px 12px;
    font-size: 12px;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.product-info-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.product-info-box h2 {
    font-size: 24px;
    margin-bottom: 10px;
}

.product-info-box p {
    color: #666;
    margin: 5px 0;
}

.section-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 30px;
}

.section-card h3 {
    font-size: 20px;
    margin-bottom: 20px;
    color: #333;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #d4af37;
}

.options-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.option-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #e9ecef;
    border-radius: 20px;
    font-size: 14px;
}

.option-tag button {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    font-size: 16px;
    padding: 0;
}

.variants-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.variants-table th,
.variants-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.variants-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.variants-table tr:hover {
    background: #f8f9fa;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.enable-variants-box {
    text-align: center;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.enable-variants-box h3 {
    font-size: 24px;
    margin-bottom: 15px;
}

.enable-variants-box p {
    color: #666;
    margin-bottom: 30px;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 8px;
    padding: 30px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h3 {
    font-size: 22px;
    margin: 0;
}

.close-modal {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #999;
}
</style>

<div class="variants-container">
    <div class="page-header">
        <div>
            <h1>Manage Product Variants</h1>
            <p style="color: #666; margin-top: 5px;">Configure sizes, colors, and materials for your products</p>
        </div>
        <a href="products.php" class="btn btn-secondary">← Back to Products</a>
    </div>

    <?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
    <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Product Info -->
    <div class="product-info-box">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <p><strong>Price:</strong> ₹<?php echo number_format($product['price'], 2); ?></p>
        <p><strong>SKU:</strong> <?php echo $product['base_sku'] ?? 'Not set'; ?></p>
        <p><strong>Variants Enabled:</strong> <?php echo $product['has_variants'] ? 'Yes' : 'No'; ?></p>
    </div>

    <?php if (!$product['has_variants']): ?>
    <!-- Enable Variants -->
    <div class="section-card">
        <div class="enable-variants-box">
            <h3>Enable Product Variants</h3>
            <p>Add size, color, and material options to this product</p>
            <form method="POST">
                <input type="hidden" name="action" value="enable_variants">
                <button type="submit" class="btn btn-primary">Enable Variants</button>
            </form>
        </div>
    </div>
    <?php else: ?>

    <!-- Manage Options -->
    <div class="section-card">
        <h3>Product Options</h3>
        <p style="color: #666; margin-bottom: 20px;">Define the available sizes, colors, and materials for this product</p>

        <!-- Size Options -->
        <div style="margin-bottom: 30px;">
            <h4>Sizes</h4>
            <?php if (isset($options['size']) && count($options['size']) > 0): ?>
            <div class="options-list">
                <?php foreach ($options['size'] as $option): ?>
                <div class="option-tag">
                    <?php echo htmlspecialchars($option['option_value']); ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_option">
                        <input type="hidden" name="option_id" value="<?php echo $option['id']; ?>">
                        <button type="submit" onclick="return confirm('Delete this option?')">×</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="color: #999;">No sizes added yet</p>
            <?php endif; ?>
            <button class="btn btn-primary btn-small" onclick="showAddOptionModal('size')">+ Add Size</button>
        </div>

        <!-- Color Options -->
        <div style="margin-bottom: 30px;">
            <h4>Colors</h4>
            <?php if (isset($options['color']) && count($options['color']) > 0): ?>
            <div class="options-list">
                <?php foreach ($options['color'] as $option): ?>
                <div class="option-tag">
                    <?php echo htmlspecialchars($option['option_value']); ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_option">
                        <input type="hidden" name="option_id" value="<?php echo $option['id']; ?>">
                        <button type="submit" onclick="return confirm('Delete this option?')">×</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="color: #999;">No colors added yet</p>
            <?php endif; ?>
            <button class="btn btn-primary btn-small" onclick="showAddOptionModal('color')">+ Add Color</button>
        </div>

        <!-- Material Options -->
        <div>
            <h4>Materials</h4>
            <?php if (isset($options['material']) && count($options['material']) > 0): ?>
            <div class="options-list">
                <?php foreach ($options['material'] as $option): ?>
                <div class="option-tag">
                    <?php echo htmlspecialchars($option['option_value']); ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_option">
                        <input type="hidden" name="option_id" value="<?php echo $option['id']; ?>">
                        <button type="submit" onclick="return confirm('Delete this option?')">×</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="color: #999;">No materials added yet</p>
            <?php endif; ?>
            <button class="btn btn-primary btn-small" onclick="showAddOptionModal('material')">+ Add Material</button>
        </div>
    </div>

    <!-- Manage Variants -->
    <div class="section-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">Product Variants</h3>
            <button class="btn btn-primary" onclick="showAddVariantModal()">+ Add Variant</button>
        </div>

        <?php if (count($variants) > 0): ?>
        <table class="variants-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Size</th>
                    <th>Color</th>
                    <th>Material</th>
                    <th>Stock</th>
                    <th>Price Adj.</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($variants as $variant): ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($variant['sku']); ?></code></td>
                    <td><?php echo htmlspecialchars($variant['size'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($variant['color'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($variant['material'] ?? '-'); ?></td>
                    <td><?php echo $variant['stock_quantity']; ?></td>
                    <td>₹<?php echo number_format($variant['price_adjustment'], 2); ?></td>
                    <td>
                        <span class="status-badge <?php echo $variant['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $variant['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-secondary btn-small" onclick='editVariant(<?php echo json_encode($variant); ?>)'>Edit</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete_variant">
                            <input type="hidden" name="variant_id" value="<?php echo $variant['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Delete this variant?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="text-align: center; color: #999; padding: 40px;">No variants added yet. Click "Add Variant" to create your first variant.</p>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</div>

<!-- Add Option Modal -->
<div class="modal" id="addOptionModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="optionModalTitle">Add Option</h3>
            <button class="close-modal" onclick="closeModal('addOptionModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add_option">
            <input type="hidden" name="option_type" id="optionType">
            <div class="form-group">
                <label>Option Value</label>
                <input type="text" name="option_value" class="form-control" required placeholder="e.g., Small, Medium, Large">
            </div>
            <div class="form-group">
                <label>Display Order</label>
                <input type="number" name="display_order" class="form-control" value="0" min="0">
            </div>
            <button type="submit" class="btn btn-primary">Add Option</button>
        </form>
    </div>
</div>

<!-- Add/Edit Variant Modal -->
<div class="modal" id="variantModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="variantModalTitle">Add Variant</h3>
            <button class="close-modal" onclick="closeModal('variantModal')">&times;</button>
        </div>
        <form method="POST" id="variantForm">
            <input type="hidden" name="action" id="variantAction" value="add_variant">
            <input type="hidden" name="variant_id" id="variantId">
            
            <div class="form-group">
                <label>SKU *</label>
                <input type="text" name="sku" id="variantSku" class="form-control" required>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Size</label>
                    <select name="size" id="variantSize" class="form-control">
                        <option value="">- None -</option>
                        <?php if (isset($options['size'])): foreach ($options['size'] as $opt): ?>
                        <option value="<?php echo htmlspecialchars($opt['option_value']); ?>">
                            <?php echo htmlspecialchars($opt['option_value']); ?>
                        </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Color</label>
                    <select name="color" id="variantColor" class="form-control">
                        <option value="">- None -</option>
                        <?php if (isset($options['color'])): foreach ($options['color'] as $opt): ?>
                        <option value="<?php echo htmlspecialchars($opt['option_value']); ?>">
                            <?php echo htmlspecialchars($opt['option_value']); ?>
                        </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Material</label>
                    <select name="material" id="variantMaterial" class="form-control">
                        <option value="">- None -</option>
                        <?php if (isset($options['material'])): foreach ($options['material'] as $opt): ?>
                        <option value="<?php echo htmlspecialchars($opt['option_value']); ?>">
                            <?php echo htmlspecialchars($opt['option_value']); ?>
                        </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Stock Quantity *</label>
                    <input type="number" name="stock_quantity" id="variantStock" class="form-control" required min="0" value="0">
                </div>

                <div class="form-group">
                    <label>Price Adjustment</label>
                    <input type="number" name="price_adjustment" id="variantPrice" class="form-control" step="0.01" value="0.00">
                    <small style="color: #666;">Additional cost (can be negative)</small>
                </div>
            </div>

            <div class="form-group" id="variantActiveGroup" style="display: none;">
                <label>
                    <input type="checkbox" name="is_active" id="variantActive" checked>
                    Active
                </label>
            </div>

            <button type="submit" class="btn btn-primary" id="variantSubmitBtn">Add Variant</button>
        </form>
    </div>
</div>

<script>
function showAddOptionModal(optionType) {
    document.getElementById('optionType').value = optionType;
    document.getElementById('optionModalTitle').textContent = 'Add ' + optionType.charAt(0).toUpperCase() + optionType.slice(1);
    document.getElementById('addOptionModal').classList.add('active');
}

function showAddVariantModal() {
    document.getElementById('variantModalTitle').textContent = 'Add Variant';
    document.getElementById('variantAction').value = 'add_variant';
    document.getElementById('variantSubmitBtn').textContent = 'Add Variant';
    document.getElementById('variantActiveGroup').style.display = 'none';
    document.getElementById('variantForm').reset();
    document.getElementById('variantModal').classList.add('active');
}

function editVariant(variant) {
    document.getElementById('variantModalTitle').textContent = 'Edit Variant';
    document.getElementById('variantAction').value = 'update_variant';
    document.getElementById('variantSubmitBtn').textContent = 'Update Variant';
    document.getElementById('variantActiveGroup').style.display = 'block';
    
    document.getElementById('variantId').value = variant.id;
    document.getElementById('variantSku').value = variant.sku;
    document.getElementById('variantSku').readOnly = true;
    document.getElementById('variantSize').value = variant.size || '';
    document.getElementById('variantColor').value = variant.color || '';
    document.getElementById('variantMaterial').value = variant.material || '';
    document.getElementById('variantStock').value = variant.stock_quantity;
    document.getElementById('variantPrice').value = variant.price_adjustment;
    document.getElementById('variantActive').checked = variant.is_active == 1;
    
    document.getElementById('variantModal').classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    if (modalId === 'variantModal') {
        document.getElementById('variantSku').readOnly = false;
    }
}

// Close modal on outside click
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(this.id);
        }
    });
});
</script>

</div><!-- End main-content -->

</body>
</html>
