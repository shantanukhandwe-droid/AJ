<?php 
require_once 'config.php';
require_once 'includes/db.php';

// Get product from URL (accept both id and slug)
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product_slug = isset($_GET['slug']) ? clean_input($_GET['slug']) : '';

// Fetch product details with variant information
$stmt = $conn->prepare("
    SELECT p.*, 
           COUNT(DISTINCT pv.id) as variant_count,
           COUNT(DISTINCT pi.id) as image_count
    FROM products p
    LEFT JOIN product_variants pv ON p.id = pv.product_id AND pv.is_active = TRUE
    LEFT JOIN product_images pi ON p.id = pi.product_id
    WHERE (p.id = ? OR p.slug = ?)
    GROUP BY p.id
");
$stmt->bind_param("is", $product_id, $product_slug);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: " . url('shop.php'));
    exit;
}

// Extract the actual product ID for subsequent queries
$product_id = $product['id'];
$page_title = $product['name'];
$discount = calculate_discount($product['original_price'], $product['price']);

// Fetch product images
$images_stmt = $conn->prepare("
    SELECT image_path, is_primary, alt_text 
    FROM product_images 
    WHERE product_id = ? 
    ORDER BY is_primary DESC, sort_order ASC
");
$images_stmt->bind_param("i", $product_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();
$images = $images_result->fetch_all(MYSQLI_ASSOC);

// Fetch available options (sizes, colors, materials)
$options = [];
if ($product['has_variants']) {
    $options_stmt = $conn->prepare("
        SELECT DISTINCT option_type, option_value, display_order 
        FROM product_options 
        WHERE product_id = ? 
        ORDER BY option_type, display_order
    ");
    $options_stmt->bind_param("i", $product_id);
    $options_stmt->execute();
    $options_result = $options_stmt->get_result();
    
    while ($option = $options_result->fetch_assoc()) {
        $options[$option['option_type']][] = $option['option_value'];
    }
}

// Fetch all variants for this product (for JavaScript)
$variants = [];
if ($product['has_variants']) {
    $variants_stmt = $conn->prepare("
        SELECT id, sku, size, color, material, stock_quantity, price_adjustment, is_active
        FROM product_variants 
        WHERE product_id = ? AND is_active = TRUE
    ");
    $variants_stmt->bind_param("i", $product_id);
    $variants_stmt->execute();
    $variants_result = $variants_stmt->get_result();
    $variants = $variants_result->fetch_all(MYSQLI_ASSOC);
}

// Get related products (same category)
$related_query = "SELECT * FROM products WHERE category = '{$product['category']}' AND id != {$product_id} LIMIT 4";
$related_result = mysqli_query($conn, $related_query);

include 'includes/header.php';
?>

<div class="product-page">
  <div class="product-container">
    <div class="product-gallery">
      <div class="product-main-image">
        <?php if (count($images) > 0): ?>
        <img id="main-image" src="<?php echo url('assets/images/products/' . $images[0]['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <?php elseif ($product['image_main']): ?>
        <img id="main-image" src="<?php echo url('assets/images/products/' . $product['image_main']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <?php else: ?>
        <div style="aspect-ratio: 1; background: var(--charcoal); display: flex; align-items: center; justify-content: center;">
          <svg width="200" height="200" viewBox="0 0 100 100">
            <defs><radialGradient id="pdpg" cx="35%" cy="30%"><stop offset="0%" stop-color="#FFE8C2"/><stop offset="50%" stop-color="#D4A574"/><stop offset="100%" stop-color="#6B4A18"/></radialGradient></defs>
            <circle cx="50" cy="50" r="30" stroke="url(#pdpg)" stroke-width="2" fill="none"/>
          </svg>
        </div>
        <?php endif; ?>
      </div>
      
      <?php if (count($images) > 1 || ($product['image_hover'] && count($images) == 0)): ?>
      <div class="product-thumbnails">
        <?php if (count($images) > 0): ?>
          <?php foreach ($images as $img): ?>
          <img src="<?php echo url('assets/images/products/' . $img['image_path']); ?>" 
               onclick="document.getElementById('main-image').src=this.src; document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active')); this.classList.add('active');"
               class="thumbnail <?php echo $img['is_primary'] ? 'active' : ''; ?>">
          <?php endforeach; ?>
        <?php elseif ($product['image_hover']): ?>
          <img src="<?php echo url('assets/images/products/' . $product['image_main']); ?>" 
               onclick="document.getElementById('main-image').src=this.src; document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active')); this.classList.add('active');"
               class="thumbnail active">
          <img src="<?php echo url('assets/images/products/' . $product['image_hover']); ?>" 
               onclick="document.getElementById('main-image').src=this.src; document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active')); this.classList.add('active');"
               class="thumbnail">
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="product-details">
      <div class="product-breadcrumb">
        <a href="<?php echo url('shop.php'); ?>">Shop</a> / 
        <a href="<?php echo url('shop.php?category=' . $product['category']); ?>"><?php echo ucfirst($product['category']); ?></a> / 
        <?php echo $product['name']; ?>
      </div>

      <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
      
      <div class="product-rating">
        <span class="stars">★★★★★</span>
        <span class="rating-text">4.9 (127 reviews)</span>
      </div>

      <div class="product-pricing">
        <span class="product-price-current" id="displayPrice"><?php echo format_price($product['price']); ?></span>
        <?php if ($product['original_price'] && $discount > 0): ?>
        <span class="product-price-old"><?php echo format_price($product['original_price']); ?></span>
        <span class="product-discount"><?php echo $discount; ?>% off</span>
        <?php endif; ?>
      </div>

      <div id="stockIndicator">
        <?php if (!$product['has_variants']): ?>
          <?php if ($product['stock'] == 0): ?>
          <div class="product-stock-low" style="background: var(--charcoal); color: #c62828;">Out of Stock</div>
          <?php elseif ($product['stock'] < 10): ?>
          <div class="product-stock-low">Only <?php echo $product['stock']; ?> left in stock</div>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <?php if ($product['has_variants'] && (count($options) > 0)): ?>
      <!-- Size Selection -->
      <?php if (!empty($options['size'])): ?>
      <div class="product-variants">
        <label class="variant-label">Select Size</label>
        <div class="variant-options" id="sizeOptions">
          <?php foreach ($options['size'] as $size): ?>
          <button class="variant-btn" data-option-type="size" data-option-value="<?php echo htmlspecialchars($size); ?>">
            <?php echo htmlspecialchars($size); ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Color Selection -->
      <?php if (!empty($options['color'])): ?>
      <div class="product-variants">
        <label class="variant-label">Select Color</label>
        <div class="variant-options" id="colorOptions">
          <?php foreach ($options['color'] as $color): ?>
          <button class="variant-btn" data-option-type="color" data-option-value="<?php echo htmlspecialchars($color); ?>">
            <?php echo htmlspecialchars($color); ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Material Selection -->
      <?php if (!empty($options['material'])): ?>
      <div class="product-variants">
        <label class="variant-label">Select Material</label>
        <div class="variant-options" id="materialOptions">
          <?php foreach ($options['material'] as $material): ?>
          <button class="variant-btn" data-option-type="material" data-option-value="<?php echo htmlspecialchars($material); ?>">
            <?php echo htmlspecialchars($material); ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
      <?php endif; ?>

      <!-- Quantity Selector -->
      <div class="product-quantity">
        <label class="quantity-label">Quantity</label>
        <div class="quantity-controls">
          <button class="qty-btn" onclick="decreaseQty()">−</button>
          <input type="number" class="qty-input" value="1" min="1" max="<?php echo $product['stock'] > 0 ? $product['stock'] : 99; ?>" id="quantity" readonly>
          <button class="qty-btn" onclick="increaseQty()">+</button>
        </div>
      </div>

      <div class="product-actions">
        <button class="btn-add-cart" id="addToCartBtn" onclick="handleAddToCart()" <?php echo (!$product['has_variants'] && $product['stock'] == 0) ? 'disabled' : ''; ?>>
          Add to bag
        </button>
        <button class="btn-wishlist" 
                id="wishlistBtn"
                data-product-id="<?php echo $product['id']; ?>"
                onclick="toggleWishlist(<?php echo $product['id']; ?>, this)"
                title="Add to wishlist">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
          </svg>
        </button>
      </div>

      <div class="product-description">
        <h3>Description</h3>
        <p><?php echo nl2br(htmlspecialchars($product['description'] ?: 'Handcrafted jewellery. 18k gold plating. Hypoallergenic. Anti-tarnish.')); ?></p>
      </div>

      <div class="product-features">
        <div class="feature">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M3 15l3-9h12l3 9M3 15l9 6 9-6M3 15h18" stroke-linecap="round"/>
          </svg>
          <span>Anti-tarnish 18k gold plating</span>
        </div>
        <div class="feature">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round"/>
          </svg>
          <span>Hypoallergenic & nickel-free</span>
        </div>
        <div class="feature">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-linecap="round"/>
          </svg>
          <span>7-day easy returns</span>
        </div>
        <div class="feature">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round"/>
          </svg>
          <span>Free shipping above ₹999</span>
        </div>
      </div>
    </div>
  </div>

  <?php if (mysqli_num_rows($related_result) > 0): ?>
  <div class="related-products">
    <h2 class="section-title">You might also like</h2>
    <div class="products">
      <?php while ($related = mysqli_fetch_assoc($related_result)): ?>
      <div class="product">
        <a href="<?php echo url('product.php?slug=' . $related['slug']); ?>" style="text-decoration: none; color: inherit;">
          <div class="product-image">
            <?php if ($related['image_main']): ?>
            <img src="<?php echo url('assets/images/products/' . $related['image_main']); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
            <?php else: ?>
            <svg viewBox="0 0 100 100">
              <defs><radialGradient id="pg<?php echo $related['id']; ?>" cx="35%" cy="30%"><stop offset="0%" stop-color="#FFE8C2"/><stop offset="50%" stop-color="#D4A574"/><stop offset="100%" stop-color="#6B4A18"/></radialGradient></defs>
              <circle cx="50" cy="50" r="22" stroke="url(#pg<?php echo $related['id']; ?>)" stroke-width="2" fill="none"/>
            </svg>
            <?php endif; ?>
          </div>
          <div class="product-info">
            <div class="product-name"><?php echo htmlspecialchars($related['name']); ?></div>
            <div class="product-price">
              <span class="price-current"><?php echo format_price($related['price']); ?></span>
            </div>
          </div>
        </a>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<style>
.product-page {
  background: var(--ink);
  min-height: 100vh;
  padding: 60px 60px 120px;
}

.product-container {
  max-width: 1400px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 80px;
  margin-bottom: 120px;
}

.product-gallery {
  position: sticky;
  top: 100px;
  align-self: start;
}

.product-main-image {
  background: var(--charcoal);
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 16px;
  border: 1px solid var(--line-subtle);
}

.product-main-image img {
  width: 100%;
  display: block;
}

.product-thumbnails {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
  gap: 12px;
}

.thumbnail {
  cursor: pointer;
  border-radius: 6px;
  border: 2px solid var(--line-subtle);
  transition: border-color 0.3s;
  width: 100%;
  display: block;
}

.thumbnail.active,
.thumbnail:hover {
  border-color: #D4A574;
}

.product-breadcrumb {
  font-size: 12px;
  color: rgba(245, 241, 232, 0.6);
  margin-bottom: 24px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.product-breadcrumb a {
  color: rgba(245, 241, 232, 0.6);
  text-decoration: none;
  transition: all 0.3s;
  position: relative;
}

.product-breadcrumb a:hover {
  color: #D4A574;
}

.product-title {
  text-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);
  font-family: 'Italiana', serif;
  font-size: clamp(36px, 4vw, 56px);
  color: #F5F1E8;
  margin-bottom: 20px;
  line-height: 1.1;
  font-weight: 400;
  text-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);
}

.product-rating {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 28px;
}

.stars {
  color: #D4A574;
  letter-spacing: 3px;
  font-size: 16px;
}

.rating-text {
  font-size: 14px;
  color: rgba(245, 241, 232, 0.7);
}

.product-pricing {
  display: flex;
  align-items: baseline;
  gap: 16px;
  margin-bottom: 24px;
  padding: 20px 0;
  border-top: 1px solid rgba(245, 241, 232, 0.1);
  border-bottom: 1px solid rgba(245, 241, 232, 0.1);
}

.product-price-current {
  font-family: 'Cormorant Garamond', serif;
  font-size: 42px;
  color: #D4A574;
  font-weight: 600;
  line-height: 1;
}

.product-price-old {
  font-size: 22px;
  color: rgba(245, 241, 232, 0.4);
  text-decoration: line-through;
}

.product-discount {
  font-size: 15px;
  color: #10B981;
  letter-spacing: 0.05em;
  font-weight: 600;
  background: rgba(16, 185, 129, 0.1);
  padding: 4px 10px;
  border-radius: 6px;
}

.product-stock-low {
  background: rgba(220, 38, 38, 0.15);
  color: #EF4444;
  border: 1px solid rgba(220, 38, 38, 0.3);
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  display: inline-block;
  margin-bottom: 28px;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.product-quantity {
  margin-bottom: 24px;
}

.quantity-label {
  display: block;
  font-size: 13px;
  color: #F5F1E8;
  margin-bottom: 12px;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 12px;
}

.qty-btn {
  width: 44px;
  height: 44px;
  background: var(--charcoal);
  color: #F5F1E8;
  border: 1px solid rgba(245, 241, 232, 0.15);
  border-radius: 8px;
  cursor: pointer;
  font-size: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  font-weight: 600;
}

.qty-btn:hover {
  background: var(--gold);
  color: var(--ink);
  border-color: #D4A574;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(212, 165, 116, 0.3);
}

.qty-input {
  width: 70px;
  height: 44px;
  background: var(--charcoal);
  color: #F5F1E8;
  border: 1px solid rgba(245, 241, 232, 0.15);
  border-radius: 8px;
  text-align: center;
  font-size: 18px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
}

.product-variants {
  margin-bottom: 24px;
}

.variant-label {
  display: block;
  font-size: 13px;
  color: #F5F1E8;
  margin-bottom: 12px;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.variant-options {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.variant-btn {
  padding: 10px 20px;
  background: var(--charcoal);
  color: #F5F1E8;
  border: 1px solid var(--line-warm);
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s;
  font-family: 'Inter', sans-serif;
}

.variant-btn:hover,
.variant-btn.active {
  background: var(--gold);
  color: var(--ink);
  border-color: #D4A574;
}

.product-actions {
  display: flex;
  gap: 12px;
  margin-bottom: 40px;
  margin-top: 32px;
}

.btn-add-cart {
  flex: 1;
  background: linear-gradient(135deg, #D4A574 0%, #C89554 100%);
  color: var(--ink);
  border: none;
  padding: 20px 36px;
  font-size: 13px;
  letter-spacing: 0.25em;
  text-transform: uppercase;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(212, 165, 116, 0.3);
}

.btn-add-cart:hover {
  background: linear-gradient(135deg, #E5B785 0%, #D4A574 100%);
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(212, 165, 116, 0.4);
}

.btn-add-cart:disabled {
  background: rgba(31, 41, 55, 0.5);
  color: rgba(245, 241, 232, 0.3);
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-wishlist {
  width: 60px;
  height: 60px;
  background: var(--charcoal);
  border: 1px solid rgba(245, 241, 232, 0.15);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  color: #F5F1E8;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-wishlist:hover {
  background: rgba(212, 165, 116, 0.1);
  border-color: #D4A574;
  color: #D4A574;
  transform: scale(1.05);
}

.btn-wishlist.in-wishlist {
  background: rgba(239, 68, 68, 0.15);
  border-color: #EF4444;
}

.btn-wishlist.in-wishlist svg path {
  fill: #EF4444;
  stroke: #EF4444;
}

.product-description {
  margin-bottom: 32px;
  padding-top: 32px;
  border-top: 1px solid var(--line-subtle);
}

.product-description h3 {
  font-size: 11px;
  letter-spacing: 0.3em;
  text-transform: uppercase;
  color: #D4A574;
  margin-bottom: 12px;
}

.product-description p {
  color: #F5F1E8;
  font-size: 15px;
  line-height: 1.7;
  opacity: 0.8;
}

.product-features {
  display: grid;
  gap: 16px;
}

.feature {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  color: #F5F1E8;
}

.feature svg {
  color: #D4A574;
  flex-shrink: 0;
}

.related-products {
  max-width: 1400px;
  margin: 0 auto;
}

.section-title {
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
  font-family: 'Italiana', serif;
  font-size: clamp(32px, 4vw, 48px);
  color: #F5F1E8;
  margin-bottom: 40px;
  font-weight: 400;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
}

@media (max-width: 1024px) {
  .product-page { padding: 40px 30px 100px; }
  .product-container {
    grid-template-columns: 1fr;
    gap: 40px;
  }
  .product-gallery { position: static; }
}
</style>

<script>
// Product and variants data from PHP
const product = {
    id: <?php echo $product_id; ?>,
    basePrice: <?php echo $product['price']; ?>,
    hasVariants: <?php echo $product['has_variants'] ? 'true' : 'false'; ?>,
    stockQuantity: <?php echo $product['stock'] ?? 0; ?>
};

// Variants data (if applicable)
const variants = <?php echo json_encode($variants); ?>;

// Check stock on page load
window.addEventListener('DOMContentLoaded', function() {
    if (product.hasVariants) {
        // Check if ANY variant has stock
        const hasStock = variants.some(v => v.stock_quantity > 0 && v.is_active);
        
        if (!hasStock) {
            document.getElementById('stockIndicator').innerHTML = '<div class="product-stock-low" style="background: var(--charcoal); color: #c62828;">Out of Stock</div>';
            document.getElementById('addToCartBtn').disabled = true;
        }
    } else {
        // Simple product stock check
        if (product.stockQuantity === 0) {
            document.getElementById('addToCartBtn').disabled = true;
        }
    }
});

// Track selected options
const selectedOptions = {
    size: null,
    color: null,
    material: null
};

// Add click handlers to variant buttons
document.querySelectorAll('.variant-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const optionType = this.dataset.optionType;
        const optionValue = this.dataset.optionValue;
        
        // Toggle active state within same option type
        document.querySelectorAll(`.variant-btn[data-option-type="${optionType}"]`).forEach(b => {
            b.classList.remove('active');
        });
        this.classList.add('active');
        
        // Update selected option
        selectedOptions[optionType] = optionValue;
        
        // Update price and stock based on selected variant
        updateProductInfo();
    });
});

function updateProductInfo() {
    if (!product.hasVariants) return;
    
    // Find matching variant
    const matchingVariant = variants.find(v => {
        return (!selectedOptions.size || v.size === selectedOptions.size) &&
               (!selectedOptions.color || v.color === selectedOptions.color) &&
               (!selectedOptions.material || v.material === selectedOptions.material);
    });
    
    if (matchingVariant) {
        // Update price
        const finalPrice = product.basePrice + parseFloat(matchingVariant.price_adjustment);
        document.getElementById('displayPrice').textContent = '₹' + finalPrice.toFixed(2).replace(/\.00$/, '');
        
        // Update stock indicator
        const stockDiv = document.getElementById('stockIndicator');
        const qtyInput = document.getElementById('quantity');
        
        if (matchingVariant.stock_quantity === 0) {
            stockDiv.innerHTML = '<div class="product-stock-low" style="background: var(--charcoal); color: #c62828;">Out of Stock</div>';
            document.getElementById('addToCartBtn').disabled = true;
            qtyInput.max = 0;
            qtyInput.value = 1;
        } else if (matchingVariant.stock_quantity < 10) {
            stockDiv.innerHTML = `<div class="product-stock-low">Only ${matchingVariant.stock_quantity} left in stock</div>`;
            document.getElementById('addToCartBtn').disabled = false;
            qtyInput.max = matchingVariant.stock_quantity;
            if (parseInt(qtyInput.value) > matchingVariant.stock_quantity) {
                qtyInput.value = matchingVariant.stock_quantity;
            }
        } else {
            stockDiv.innerHTML = '';
            document.getElementById('addToCartBtn').disabled = false;
            qtyInput.max = matchingVariant.stock_quantity;
        }
    }
}

function handleAddToCart() {
    const quantity = parseInt(document.getElementById('quantity').value);
    
    if (product.hasVariants && variants.length > 0) {
        // Find the selected variant
        const matchingVariant = variants.find(v => {
            return (!selectedOptions.size || v.size === selectedOptions.size) &&
                   (!selectedOptions.color || v.color === selectedOptions.color) &&
                   (!selectedOptions.material || v.material === selectedOptions.material);
        });
        
        if (!matchingVariant) {
            alert('Please select all options');
            return;
        }
        
        // Call custom add to cart directly (avoid main.js conflict)
        addProductToCartAPI(product.id, quantity, matchingVariant.id);
    } else {
        // Call custom add to cart directly (avoid main.js conflict)
        addProductToCartAPI(product.id, quantity, null);
    }
}

function increaseQty() {
    const qtyInput = document.getElementById('quantity');
    const currentVal = parseInt(qtyInput.value);
    const maxVal = parseInt(qtyInput.max);
    if (currentVal < maxVal) {
        qtyInput.value = currentVal + 1;
    }
}

function decreaseQty() {
    const qtyInput = document.getElementById('quantity');
    const currentVal = parseInt(qtyInput.value);
    const minVal = parseInt(qtyInput.min);
    if (currentVal > minVal) {
        qtyInput.value = currentVal - 1;
    }
}

// Custom add to cart function (renamed to avoid main.js conflict)
function addProductToCartAPI(productId, quantity, variantId) {
    const data = {
        product_id: productId,
        quantity: quantity
    };
    
    if (variantId) {
        data.variant_id = variantId;
    }
    
    fetch('<?php echo url('api/cart.php?action=add'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid JSON response: ' + text);
            }
        });
    })
    .then(data => {
        if (data.success) {
            // Update cart count in header
            if (data.cart_count) {
                const cartCountEl = document.querySelector('.cart-count');
                if (cartCountEl) {
                    cartCountEl.textContent = data.cart_count;
                }
                
                // Show cart badge if it doesn't exist
                const cartIcon = document.getElementById('cart-icon');
                if (cartIcon) {
                    let badge = cartIcon.querySelector('.cart-badge');
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'cart-badge';
                        cartIcon.appendChild(badge);
                    }
                    badge.textContent = data.cart_count;
                }
            }
            
            // Open cart drawer and load items
            const cartDrawer = document.getElementById('cart-drawer');
            if (cartDrawer) {
                cartDrawer.classList.add('open');
                document.body.style.overflow = 'hidden';
                
                // Load cart items
                fetch('<?php echo url('api/cart.php?action=get'); ?>')
                    .then(res => res.json())
                    .then(cartData => {
                        if (cartData.success && cartData.items) {
                            renderCartItems(cartData);
                        }
                    })
                    .catch(err => console.error('Cart load error:', err));
            }
        } else {
            alert('Error: ' + (data.message || 'Please try again.'));
        }
    })
    .catch(error => {
        console.error('Cart error:', error);
        alert('Error adding to cart. Please try again.');
    });
}

// Render cart items in the drawer
function renderCartItems(data) {
    const cartBody = document.getElementById('cart-body');
    
    if (!cartBody) return;
    
    if (!data.items || data.items.length === 0) {
        cartBody.innerHTML = `
            <div class="cart-empty">
                <svg class="cart-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
                </svg>
                <p style="font-size: 14px; color: rgba(245, 241, 232, 0.7);">Your cart is empty</p>
                <a href="<?php echo url('shop.php'); ?>" style="display: inline-block; margin-top: 16px; color: #D4A574; font-size: 12px; letter-spacing: 0.2em; text-transform: uppercase; text-decoration: none; border-bottom: 1px solid var(--gold);">Continue shopping</a>
            </div>
        `;
        return;
    }
    
    let itemsHTML = '';
    data.items.forEach(item => {
        const imagePath = item.image ? '<?php echo url('assets/images/products/'); ?>' + item.image : '<?php echo url('assets/images/placeholder.svg'); ?>';
        itemsHTML += `
            <div class="cart-item" data-key="${item.cart_key}">
                <div class="cart-item-image">
                    <img src="${imagePath}" alt="${item.name}">
                </div>
                <div class="cart-item-info">
                    <h4>${item.name}</h4>
                    ${item.variant ? `<div class="cart-item-variant">${item.variant}</div>` : ''}
                    <div class="cart-item-price">₹${item.price}</div>
                </div>
                <div class="cart-item-actions">
                    <button class="cart-item-remove" onclick="removeFromCart('${item.cart_key}')">Remove</button>
                    <div class="cart-item-qty">
                        <button>−</button>
                        <span>${item.quantity}</span>
                        <button>+</button>
                    </div>
                </div>
            </div>
        `;
    });
    
    const subtotal = data.total || 0;
    const shippingNote = subtotal >= 999 
        ? 'Free shipping applied'
        : `Add ₹${(999 - subtotal).toFixed(0)} for free shipping`;
    
    cartBody.innerHTML = `
        <div style="max-height: calc(100vh - 280px); overflow-y: auto;">
            ${itemsHTML}
        </div>
        <div class="cart-drawer-footer">
            <div class="cart-subtotal">
                <span>Subtotal</span>
                <span>₹${subtotal}</span>
            </div>
            <div class="cart-shipping-note">${shippingNote}</div>
            <a href="<?php echo url('checkout.php'); ?>" class="cart-checkout-btn">Proceed to checkout</a>
        </div>
    `;
}

function removeFromCart(cartKey) {
    // Placeholder - implement remove functionality
    alert('Remove functionality to be implemented');
}

// ==============================
// WISHLIST FUNCTIONALITY
// ==============================

function toggleWishlist(productId, buttonElement) {
  <?php if (!isset($_SESSION['customer_id'])): ?>
  alert('Please login to use wishlist');
  window.location.href = '<?php echo url("login.php?redirect=product.php?slug=" . $product["slug"]); ?>';
  return;
  <?php endif; ?>
  
  const isInWishlist = buttonElement.classList.contains('in-wishlist');
  const action = isInWishlist ? 'remove' : 'add';
  
  fetch('<?php echo url("api/wishlist.php?action="); ?>' + action, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id: productId })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      // Toggle button state
      buttonElement.classList.toggle('in-wishlist');
      
      // Update heart icon
      const heartPath = buttonElement.querySelector('svg path');
      if (data.in_wishlist) {
        heartPath.setAttribute('fill', '#EF4444');
        heartPath.setAttribute('stroke', '#EF4444');
        buttonElement.title = 'Remove from wishlist';
      } else {
        heartPath.setAttribute('fill', 'none');
        heartPath.setAttribute('stroke', 'currentColor');
        buttonElement.title = 'Add to wishlist';
      }
      
      // Update wishlist badge in header
      const badge = document.querySelector('.wishlist-badge');
      if (data.wishlist_count > 0) {
        if (badge) {
          badge.textContent = data.wishlist_count;
        } else {
          const wishlistIcon = document.getElementById('wishlist-icon');
          if (wishlistIcon && wishlistIcon.parentElement) {
            const newBadge = document.createElement('span');
            newBadge.className = 'wishlist-badge';
            newBadge.textContent = data.wishlist_count;
            wishlistIcon.parentElement.appendChild(newBadge);
          }
        }
      } else if (badge) {
        badge.remove();
      }
      
      // Show toast notification
      showWishlistToast(data.message);
    } else {
      alert(data.message || 'Failed to update wishlist');
    }
  })
  .catch(err => {
    console.error('Error:', err);
    alert('Failed to update wishlist');
  });
}

function showWishlistToast(message) {
  const existing = document.querySelector('.wishlist-toast');
  if (existing) existing.remove();
  
  const toast = document.createElement('div');
  toast.className = 'wishlist-toast';
  toast.textContent = message;
  toast.style.cssText = `
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #1F2937;
    color: #F5F1E8;
    padding: 14px 20px;
    border-radius: 8px;
    border: 1px solid #D4A574;
    font-size: 14px;
    z-index: 10000;
    animation: slideIn 0.3s ease;
  `;
  
  document.body.appendChild(toast);
  
  setTimeout(() => {
    toast.style.animation = 'slideOut 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 2500);
}

// Check if product is in wishlist on page load
document.addEventListener('DOMContentLoaded', function() {
  <?php if (isset($_SESSION['customer_id'])): ?>
  const productId = <?php echo $product['id']; ?>;
  fetch('<?php echo url("api/wishlist.php?action=check&product_id="); ?>' + productId)
    .then(res => res.json())
    .then(data => {
      if (data.in_wishlist) {
        const wishlistBtn = document.getElementById('wishlistBtn');
        if (wishlistBtn) {
          wishlistBtn.classList.add('in-wishlist');
          const heartPath = wishlistBtn.querySelector('svg path');
          if (heartPath) {
            heartPath.setAttribute('fill', '#EF4444');
            heartPath.setAttribute('stroke', '#EF4444');
          }
          wishlistBtn.title = 'Remove from wishlist';
        }
      }
    })
    .catch(err => console.error('Error checking wishlist:', err));
  <?php endif; ?>
});
</script>

<?php include 'includes/footer.php'; ?>
