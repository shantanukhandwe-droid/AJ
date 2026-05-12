<?php 
require_once 'auth.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM products WHERE id = $id");
    header('Location: products.php?deleted=1');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = clean_input($_POST['name']);
    $slug = clean_input($_POST['slug']);
    $category = clean_input($_POST['category']);
    $description = clean_input($_POST['description']);
    $price = (float)$_POST['price'];
    $original_price = $_POST['original_price'] ? (float)$_POST['original_price'] : null;
    $stock = (int)$_POST['stock'];
    $image_main = clean_input($_POST['image_main']);
    $image_hover = clean_input($_POST['image_hover']);
    $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    
    $discount = $original_price ? calculate_discount($original_price, $price) : 0;
    
    if ($id > 0) {
        // Update
        $query = "UPDATE products SET 
            name = '$name',
            slug = '$slug',
            category = '$category',
            description = '$description',
            price = $price,
            original_price = " . ($original_price ? $original_price : 'NULL') . ",
            discount_percent = $discount,
            stock = $stock,
            image_main = " . ($image_main ? "'$image_main'" : 'NULL') . ",
            image_hover = " . ($image_hover ? "'$image_hover'" : 'NULL') . ",
            is_bestseller = $is_bestseller,
            is_new = $is_new
            WHERE id = $id";
    } else {
        // Insert
        $query = "INSERT INTO products (name, slug, category, description, price, original_price, discount_percent, stock, image_main, image_hover, is_bestseller, is_new) 
            VALUES ('$name', '$slug', '$category', '$description', $price, " . ($original_price ? $original_price : 'NULL') . ", $discount, $stock, " . ($image_main ? "'$image_main'" : 'NULL') . ", " . ($image_hover ? "'$image_hover'" : 'NULL') . ", $is_bestseller, $is_new)";
    }
    
    mysqli_query($conn, $query);
    header('Location: products.php?saved=1');
    exit;
}

// Get product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id = $edit_id");
    $edit_product = mysqli_fetch_assoc($result);
}

// Get all products
$products_query = "SELECT * FROM products ORDER BY created_at DESC";
$products = mysqli_query($conn, $products_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Products — Reverie Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin-style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="top-bar">
        <h1>Products</h1>
        <div class="admin-info">
            <span>Welcome, <?php echo htmlspecialchars($admin_username); ?></span>
            <a href="?logout=1" class="btn-logout">Logout</a>
        </div>
    </div>

    <?php if (isset($_GET['saved'])): ?>
    <div style="background: #D1FAE5; border: 1px solid #10B981; color: #065F46; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px;">
        Product saved successfully!
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
    <div style="background: #FEE2E2; border: 1px solid #EF4444; color: #991B1B; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px;">
        Product deleted successfully!
    </div>
    <?php endif; ?>

    <div class="content-section">
        <div class="section-header">
            <h2><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h2>
            <?php if ($edit_product): ?>
            <a href="products.php" class="btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>

        <form method="POST">
            <?php if ($edit_product): ?>
            <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Slug (URL-friendly) *</label>
                    <input type="text" name="slug" value="<?php echo $edit_product ? htmlspecialchars($edit_product['slug']) : ''; ?>" required placeholder="e.g. circle-hoops">
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="earrings" <?php echo ($edit_product && $edit_product['category'] === 'earrings') ? 'selected' : ''; ?>>Earrings</option>
                        <option value="necklaces" <?php echo ($edit_product && $edit_product['category'] === 'necklaces') ? 'selected' : ''; ?>>Necklaces</option>
                        <option value="rings" <?php echo ($edit_product && $edit_product['category'] === 'rings') ? 'selected' : ''; ?>>Rings</option>
                        <option value="bangles" <?php echo ($edit_product && $edit_product['category'] === 'bangles') ? 'selected' : ''; ?>>Bangles</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Stock Quantity *</label>
                    <input type="number" name="stock" value="<?php echo $edit_product ? $edit_product['stock'] : '0'; ?>" required min="0">
                </div>

                <div class="form-group">
                    <label>Sale Price (₹) *</label>
                    <input type="number" step="0.01" name="price" value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Original Price (₹)</label>
                    <input type="number" step="0.01" name="original_price" value="<?php echo $edit_product ? $edit_product['original_price'] : ''; ?>" placeholder="For showing discount">
                </div>

                <div class="form-group">
                    <label>Main Image Filename</label>
                    <input type="text" name="image_main" value="<?php echo $edit_product ? htmlspecialchars($edit_product['image_main']) : ''; ?>" placeholder="e.g. circle-hoops.jpg">
                    <small style="color: #6B7280; font-size: 11px; margin-top: 4px;">Upload to /assets/images/products/</small>
                </div>

                <div class="form-group">
                    <label>Hover Image Filename</label>
                    <input type="text" name="image_hover" value="<?php echo $edit_product ? htmlspecialchars($edit_product['image_hover']) : ''; ?>" placeholder="e.g. circle-hoops-hover.jpg">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
            </div>

            <div style="display: flex; gap: 20px; margin-top: 16px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_bestseller" <?php echo ($edit_product && $edit_product['is_bestseller']) ? 'checked' : ''; ?>>
                    <span>Mark as Bestseller</span>
                </label>

                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_new" <?php echo ($edit_product && $edit_product['is_new']) ? 'checked' : ''; ?>>
                    <span>Mark as New</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                </button>
                <?php if ($edit_product): ?>
                <a href="products.php" class="btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="content-section">
        <div class="section-header">
            <h2>All Products</h2>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Variants</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = mysqli_fetch_assoc($products)): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                        </td>
                        <td><?php echo ucfirst($product['category']); ?></td>
                        <td>
                            <?php echo format_price($product['price']); ?>
                            <?php if ($product['original_price']): ?>
                            <br><small style="color: #9CA3AF; text-decoration: line-through;"><?php echo format_price($product['original_price']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $product['stock']; ?></td>
                        <td style="text-align: center;">
                            <?php if ($product['has_variants']): ?>
                            <span style="display: inline-block; padding: 4px 10px; background: #DBEAFE; color: #1E40AF; border-radius: 12px; font-size: 11px; font-weight: 600;">YES</span>
                            <?php else: ?>
                            <span style="display: inline-block; padding: 4px 10px; background: #F3F4F6; color: #6B7280; border-radius: 12px; font-size: 11px; font-weight: 600;">NO</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($product['is_bestseller']): ?>
                            <span class="badge badge-delivered">Bestseller</span>
                            <?php endif; ?>
                            <?php if ($product['is_new']): ?>
                            <span class="badge badge-confirmed">New</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space: nowrap;">
                            <a href="?edit=<?php echo $product['id']; ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 12px; margin-right: 4px;">Edit</a>
                            <a href="manage-variants.php?product_id=<?php echo $product['id']; ?>" class="btn-info" style="padding: 6px 12px; font-size: 12px; margin-right: 4px; background: #3B82F6; color: white; text-decoration: none; display: inline-block; border-radius: 4px;">Variants</a>
                            <a href="manage-images.php?product_id=<?php echo $product['id']; ?>" class="btn-warning" style="padding: 6px 12px; font-size: 12px; margin-right: 4px; background: #F59E0B; color: white; text-decoration: none; display: inline-block; border-radius: 4px;">Images</a>
                            <a href="?delete=<?php echo $product['id']; ?>" class="btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="return confirm('Delete this product?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
