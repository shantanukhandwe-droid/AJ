<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$page_title = 'Manage Product Images';
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: products.php");
    exit;
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['product_images'])) {
    $upload_dir = __DIR__ . '/../assets/images/products/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $uploaded_files = $_FILES['product_images'];
    $file_count = count($uploaded_files['name']);
    $is_primary = isset($_POST['set_as_primary']) ? true : false;
    
    // If setting as primary, unset all other primary images
    if ($is_primary) {
        $stmt = $conn->prepare("UPDATE product_images SET is_primary = FALSE WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
    }
    
    for ($i = 0; $i < $file_count; $i++) {
        if ($uploaded_files['error'][$i] === 0) {
            $file_name = $uploaded_files['name'][$i];
            $file_tmp = $uploaded_files['tmp_name'][$i];
            $file_size = $uploaded_files['size'][$i];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validate file type
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($file_ext, $allowed_extensions)) {
                $error_message = "Invalid file type. Only JPG, PNG, and WebP are allowed.";
                continue;
            }
            
            // Validate file size (5MB max)
            if ($file_size > 5 * 1024 * 1024) {
                $error_message = "File too large. Maximum size is 5MB.";
                continue;
            }
            
            // Generate unique filename
            $new_filename = 'product_' . $product_id . '_' . time() . '_' . $i . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Save to database
                $db_path = 'assets/images/products/' . $new_filename;
                $alt_text = $_POST['alt_text'] ?? $product['name'];
                $sort_order = (int)($_POST['sort_order'] ?? 0);
                
                // Only first image is primary if set_as_primary is checked
                $is_img_primary = ($is_primary && $i === 0) ? 1 : 0;
                
                $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order, alt_text) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isiss", $product_id, $db_path, $is_img_primary, $sort_order, $alt_text);
                $stmt->execute();
                
                $success_message = "Image(s) uploaded successfully!";
            } else {
                $error_message = "Failed to upload image.";
            }
        }
    }
}

// Handle image deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    $image_id = (int)$_POST['image_id'];
    
    // Get image path before deleting
    $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE id = ? AND product_id = ?");
    $stmt->bind_param("ii", $image_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    
    if ($image) {
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
        $stmt->bind_param("ii", $image_id, $product_id);
        $stmt->execute();
        
        // Delete physical file
        $file_path = __DIR__ . '/../' . $image['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        $success_message = "Image deleted successfully!";
    }
}

// Handle set primary
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_primary'])) {
    $image_id = (int)$_POST['image_id'];
    
    // Unset all primary images
    $stmt = $conn->prepare("UPDATE product_images SET is_primary = FALSE WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    
    // Set selected as primary
    $stmt = $conn->prepare("UPDATE product_images SET is_primary = TRUE WHERE id = ? AND product_id = ?");
    $stmt->bind_param("ii", $image_id, $product_id);
    $stmt->execute();
    
    $success_message = "Primary image updated!";
}

// Fetch existing images
$images_stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
$images_stmt->bind_param("i", $product_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();
$images = $images_result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Product Images — Reverie Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin-style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

<style>
.images-container {
    max-width: 1200px;
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
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
}

.product-info-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
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
}

.upload-area {
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    background: #fafafa;
    margin-bottom: 20px;
    cursor: pointer;
    transition: all 0.3s;
}

.upload-area:hover {
    border-color: #d4af37;
    background: #fff;
}

.upload-area.dragover {
    border-color: #d4af37;
    background: #fffbf0;
}

.upload-icon {
    font-size: 48px;
    color: #ddd;
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.image-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background: white;
    transition: all 0.3s;
}

.image-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.image-preview {
    position: relative;
    aspect-ratio: 1;
    background: #f8f9fa;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.primary-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #d4af37;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.image-actions {
    padding: 15px;
    display: flex;
    gap: 8px;
    justify-content: center;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state svg {
    width: 80px;
    height: 80px;
    margin-bottom: 20px;
    opacity: 0.3;
}

.file-preview {
    display: none;
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.file-preview.active {
    display: block;
}

.file-list {
    list-style: none;
    padding: 0;
    margin: 10px 0 0 0;
}

.file-list li {
    padding: 8px 12px;
    background: white;
    margin-bottom: 8px;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>

<div class="images-container">
    <div class="page-header">
        <div>
            <h1>Manage Product Images</h1>
            <p style="color: #666; margin-top: 5px;">Upload and organize images for this product</p>
        </div>
        <a href="products.php" class="btn btn-secondary">← Back to Products</a>
    </div>

    <?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
    <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="product-info-box">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <p style="color: #666;">Upload high-quality images (1000x1000px recommended)</p>
    </div>

    <!-- Upload Section -->
    <div class="section-card">
        <h3>Upload Images</h3>
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()">
                <div class="upload-icon">📷</div>
                <p><strong>Click to upload</strong> or drag and drop</p>
                <p style="color: #999; font-size: 14px;">JPG, PNG, or WebP (Max 5MB each)</p>
                <input type="file" name="product_images[]" id="fileInput" multiple accept="image/jpeg,image/png,image/webp" style="display: none;">
            </div>

            <div class="file-preview" id="filePreview">
                <strong>Selected files:</strong>
                <ul class="file-list" id="fileList"></ul>
            </div>

            <div class="form-group">
                <label>Alt Text (for SEO)</label>
                <input type="text" name="alt_text" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="set_as_primary" value="1" <?php echo count($images) === 0 ? 'checked' : ''; ?>>
                    Set first image as primary
                </label>
            </div>

            <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>Upload Images</button>
        </form>
    </div>

    <!-- Existing Images -->
    <div class="section-card">
        <h3>Product Images</h3>

        <?php if (count($images) > 0): ?>
        <div class="images-grid">
            <?php foreach ($images as $image): ?>
            <div class="image-card">
                <div class="image-preview">
                    <img src="<?php echo url($image['image_path']); ?>" alt="<?php echo htmlspecialchars($image['alt_text']); ?>">
                    <?php if ($image['is_primary']): ?>
                    <div class="primary-badge">Primary</div>
                    <?php endif; ?>
                </div>
                <div class="image-actions">
                    <?php if (!$image['is_primary']): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                        <input type="hidden" name="set_primary" value="1">
                        <button type="submit" class="btn btn-secondary btn-small">Set Primary</button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                        <input type="hidden" name="delete_image" value="1">
                        <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Delete this image?')">Delete</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
            </svg>
            <h3>No images uploaded yet</h3>
            <p>Upload your first product image using the form above</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const fileInput = document.getElementById('fileInput');
const uploadArea = document.getElementById('uploadArea');
const filePreview = document.getElementById('filePreview');
const fileList = document.getElementById('fileList');
const uploadBtn = document.getElementById('uploadBtn');

// Drag and drop
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    fileInput.files = files;
    updateFileList();
});

// File selection
fileInput.addEventListener('change', updateFileList);

function updateFileList() {
    const files = fileInput.files;
    
    if (files.length > 0) {
        fileList.innerHTML = '';
        
        for (let file of files) {
            const li = document.createElement('li');
            li.innerHTML = `
                <span>${file.name}</span>
                <span style="color: #999;">${(file.size / 1024).toFixed(1)} KB</span>
            `;
            fileList.appendChild(li);
        }
        
        filePreview.classList.add('active');
        uploadBtn.disabled = false;
    } else {
        filePreview.classList.remove('active');
        uploadBtn.disabled = true;
    }
}
</script>

</div><!-- End main-content -->

</body>
</html>
