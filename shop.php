<?php 
require_once 'config.php';
require_once 'includes/db.php';

$page_title = 'Shop All';

// Get filters
$category = isset($_GET['category']) ? clean_input($_GET['category']) : '';
$sort = isset($_GET['sort']) ? clean_input($_GET['sort']) : 'newest';

// Build query
$query = "SELECT * FROM products WHERE 1=1";
if ($category) {
    $query .= " AND category = '$category'";
}

// Sorting
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY price DESC";
        break;
    case 'popular':
        $query .= " ORDER BY views DESC";
        break;
    default:
        $query .= " ORDER BY created_at DESC";
}

$result = mysqli_query($conn, $query);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

include 'includes/header.php';
?>

<style>
.shop-page {
  background: var(--ink);
  min-height: 100vh;
  padding: 60px 60px 120px;
}

.shop-container {
  max-width: 1600px;
  margin: 0 auto;
}

/* Header */
.shop-header {
  text-align: center;
  margin-bottom: 64px;
}

.shop-title {
  font-family: 'Italiana', serif;
  font-size: clamp(48px, 6vw, 72px);
  color: #F5F1E8;
  margin-bottom: 16px;
  letter-spacing: 0.02em;
}

.shop-subtitle {
  font-size: 16px;
  color: rgba(245, 241, 232, 0.7);
  letter-spacing: 0.05em;
}

/* Grid Layout */
.shop-grid {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 48px;
  align-items: start;
}

/* Sidebar */
.shop-sidebar {
  position: sticky;
  top: 120px;
}

.filter-section {
  margin-bottom: 40px;
}

.filter-title {
  font-size: 12px;
  color: #D4A574;
  letter-spacing: 0.3em;
  text-transform: uppercase;
  margin-bottom: 16px;
  font-weight: 600;
}

.filter-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.filter-item {
  display: block;
  color: rgba(245, 241, 232, 0.7);
  text-decoration: none;
  font-size: 14px;
  padding: 8px 12px;
  border-radius: 6px;
  transition: all 0.3s;
  cursor: pointer;
  border: 1px solid transparent;
}

.filter-item:hover {
  color: #F5F1E8;
  background: rgba(212, 165, 116, 0.05);
  border-color: var(--line-warm);
}

.filter-item.active {
  color: #D4A574;
  background: rgba(212, 165, 116, 0.1);
  border-color: #D4A574;
  font-weight: 500;
}

/* Products */
.products-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--line-subtle);
}

.products-count {
  font-size: 14px;
  color: rgba(245, 241, 232, 0.7);
  letter-spacing: 0.05em;
}

.sort-dropdown {
  display: flex;
  align-items: center;
  gap: 12px;
}

.sort-dropdown label {
  font-size: 12px;
  color: rgba(245, 241, 232, 0.7);
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.sort-dropdown select {
  padding: 8px 16px;
  background: var(--charcoal);
  border: 1px solid var(--line-warm);
  color: #F5F1E8;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
  font-family: inherit;
}

/* Product Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 32px;
}

/* Enhanced Product Card */
.product-card {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  background: var(--charcoal);
  border: 1px solid var(--line-subtle);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}

.product-card:hover {
  transform: translateY(-8px);
  border-color: #D4A574;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}

/* Product Image Container */
.product-image-wrapper {
  position: relative;
  aspect-ratio: 1;
  background: linear-gradient(135deg, var(--ink) 0%, var(--charcoal) 100%);
  overflow: hidden;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.product-card:hover .product-image {
  transform: scale(1.08);
}

/* Badges */
.product-badges {
  position: absolute;
  top: 16px;
  left: 16px;
  display: flex;
  gap: 8px;
  z-index: 2;
}

.badge {
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  backdrop-filter: blur(10px);
}

.badge-bestseller {
  background: rgba(212, 165, 116, 0.95);
  color: var(--ink);
}

.badge-new {
  background: rgba(16, 185, 129, 0.95);
  color: white;
}

/* Wishlist Heart */
.wishlist-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(31, 41, 55, 0.8);
  backdrop-filter: blur(10px);
  border: 1px solid var(--line-subtle);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
  z-index: 2;
}

.wishlist-btn:hover {
  background: var(--gold);
  border-color: #D4A574;
  transform: scale(1.1);
}

.wishlist-btn svg {
  width: 18px;
  height: 18px;
  color: #F5F1E8;
  transition: all 0.3s;
}

.wishlist-btn:hover svg {
  color: var(--ink);
  fill: var(--ink);
}

.wishlist-btn.in-wishlist svg path {
  fill: #EF4444;
  stroke: #EF4444;
}

.wishlist-btn.in-wishlist {
  background: rgba(239, 68, 68, 0.15);
  border-color: #EF4444;
}

/* Quick Actions Overlay */
.quick-actions {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 16px;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
  transform: translateY(100%);
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  gap: 8px;
}

.product-card:hover .quick-actions {
  transform: translateY(0);
}

.quick-view-btn,
.add-to-cart-btn {
  flex: 1;
  padding: 10px;
  border: none;
  border-radius: 6px;
  font-size: 11px;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  font-family: inherit;
}

.quick-view-btn {
  background: rgba(245, 241, 232, 0.1);
  border: 1px solid var(--line-warm);
  color: #F5F1E8;
}

.quick-view-btn:hover {
  background: var(--bone);
  color: var(--ink);
}

.add-to-cart-btn {
  background: var(--gold);
  color: var(--ink);
}

.add-to-cart-btn:hover {
  background: var(--bone);
  transform: translateY(-2px);
}

/* Product Info */
.product-info {
  padding: 20px;
}

.product-category {
  font-size: 10px;
  color: #D4A574;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  margin-bottom: 8px;
  font-weight: 600;
}

.product-name {
  font-family: 'Cormorant Garamond', serif;
  font-size: 20px;
  color: #F5F1E8;
  margin-bottom: 8px;
  font-weight: 500;
  line-height: 1.3;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
}

.product-description {
  font-size: 13px;
  color: rgba(245, 241, 232, 0.7);
  line-height: 1.5;
  margin-bottom: 12px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Rating */
.product-rating {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 12px;
}

.stars {
  color: #D4A574;
  font-size: 12px;
  letter-spacing: 2px;
}

.rating-count {
  font-size: 11px;
  color: rgba(245, 241, 232, 0.7);
}

/* Price */
.product-pricing {
  display: flex;
  align-items: baseline;
  gap: 10px;
}

.price-current {
  font-family: 'Cormorant Garamond', serif;
  font-size: 24px;
  color: #D4A574;
  font-weight: 600;
}

.price-original {
  font-size: 16px;
  color: rgba(245, 241, 232, 0.7);
  text-decoration: line-through;
}

.price-discount {
  font-size: 12px;
  color: #D4A574;
  font-weight: 600;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 80px 20px;
}

.empty-state svg {
  width: 120px;
  height: 120px;
  color: rgba(245, 241, 232, 0.7);
  opacity: 0.3;
  margin-bottom: 24px;
}

.empty-state h3 {
  font-size: 24px;
  color: #F5F1E8;
  margin-bottom: 12px;
}

.empty-state p {
  font-size: 14px;
  color: rgba(245, 241, 232, 0.7);
  margin-bottom: 24px;
}

@media (max-width: 1200px) {
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  }
}

@media (max-width: 968px) {
  .shop-page {
    padding: 40px 30px 100px;
  }
  
  .shop-grid {
    grid-template-columns: 1fr;
    gap: 32px;
  }
  
  .shop-sidebar {
    position: static;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
  }
  
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 24px;
  }
}
</style>

<div class="shop-page">
  <div class="shop-container">
    
    <!-- Header -->
    <div class="shop-header">
      <h1 class="shop-title">The Edit</h1>
      <p class="shop-subtitle">Curated collection of timeless pieces</p>
    </div>

    <div class="shop-grid">
      
      <!-- Sidebar -->
      <aside class="shop-sidebar">
        
        <!-- Category Filter -->
        <div class="filter-section">
          <h3 class="filter-title">Category</h3>
          <div class="filter-list">
            <a href="<?php echo url('shop.php'); ?>" class="filter-item <?php echo !$category ? 'active' : ''; ?>">
              All pieces
            </a>
            <a href="<?php echo url('shop.php?category=earrings'); ?>" class="filter-item <?php echo $category === 'earrings' ? 'active' : ''; ?>">
              Earrings
            </a>
            <a href="<?php echo url('shop.php?category=necklaces'); ?>" class="filter-item <?php echo $category === 'necklaces' ? 'active' : ''; ?>">
              Necklaces
            </a>
            <a href="<?php echo url('shop.php?category=rings'); ?>" class="filter-item <?php echo $category === 'rings' ? 'active' : ''; ?>">
              Rings
            </a>
            <a href="<?php echo url('shop.php?category=bangles'); ?>" class="filter-item <?php echo $category === 'bangles' ? 'active' : ''; ?>">
              Bangles
            </a>
          </div>
        </div>

        <!-- Special Filter -->
        <div class="filter-section">
          <h3 class="filter-title">Filter</h3>
          <div class="filter-list">
            <a href="<?php echo url('shop.php'); ?>" class="filter-item">
              New arrivals
            </a>
            <a href="<?php echo url('shop.php'); ?>" class="filter-item">
              Bestsellers
            </a>
          </div>
        </div>

        <!-- Price Range -->
        <div class="filter-section">
          <h3 class="filter-title">Price Range</h3>
          <div class="filter-list">
            <a href="<?php echo url('shop.php'); ?>" class="filter-item">
              Under ₹999
            </a>
            <a href="<?php echo url('shop.php'); ?>" class="filter-item">
              ₹999 - ₹1,499
            </a>
            <a href="<?php echo url('shop.php'); ?>" class="filter-item">
              ₹1,500+
            </a>
          </div>
        </div>
        
      </aside>

      <!-- Products -->
      <main>
        
        <!-- Products Header -->
        <div class="products-header">
          <div class="products-count">
            <?php echo count($products); ?> PIECES
          </div>
          
          <div class="sort-dropdown">
            <label>Sort by</label>
            <select onchange="window.location.href=this.value">
              <option value="?sort=newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest first</option>
              <option value="?sort=price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
              <option value="?sort=price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
              <option value="?sort=popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
            </select>
          </div>
        </div>

        <!-- Products Grid -->
        <?php if (count($products) > 0): ?>
        <div class="products-grid">
          
          <?php foreach ($products as $product): 
            $discount = calculate_discount($product['original_price'], $product['price']);
            $image_url = $product['image_main'] 
              ? url('assets/images/products/' . $product['image_main']) 
              : url('assets/images/placeholder.svg');
          ?>
          
          <article class="product-card" onclick="window.location.href='<?php echo url('product.php?slug=' . $product['slug']); ?>'">
            
            <!-- Image -->
            <div class="product-image-wrapper">
              <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
              
              <!-- Badges -->
              <div class="product-badges">
                <?php if ($product['is_bestseller']): ?>
                <span class="badge badge-bestseller">Bestseller</span>
                <?php endif; ?>
                <?php if ($product['is_new']): ?>
                <span class="badge badge-new">New</span>
                <?php endif; ?>
              </div>
              
              <!-- Wishlist -->
              <button class="wishlist-btn" 
                      data-product-id="<?php echo $product['id']; ?>"
                      onclick="event.stopPropagation(); toggleWishlist(<?php echo $product['id']; ?>, this)"
                      title="Add to wishlist">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                </svg>
              </button>
              
              <!-- Quick Actions -->
              <div class="quick-actions">
                <button class="quick-view-btn" onclick="event.stopPropagation(); quickView(<?php echo $product['id']; ?>)">
                  Quick View
                </button>
                <button class="add-to-cart-btn" onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>, 1)">
                  Add to Bag
                </button>
              </div>
            </div>

            <!-- Info -->
            <div class="product-info">
              <div class="product-category"><?php echo strtoupper($product['category']); ?></div>
              <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
              
              <?php if ($product['description']): ?>
              <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...</p>
              <?php endif; ?>
              
              <!-- Rating -->
              <div class="product-rating">
                <span class="stars">★★★★★</span>
                <span class="rating-count">(127)</span>
              </div>
              
              <!-- Pricing -->
              <div class="product-pricing">
                <span class="price-current"><?php echo format_price($product['price']); ?></span>
                <?php if ($discount > 0): ?>
                <span class="price-original"><?php echo format_price($product['original_price']); ?></span>
                <span class="price-discount"><?php echo $discount; ?>% off</span>
                <?php endif; ?>
              </div>
            </div>
            
          </article>
          
          <?php endforeach; ?>
          
        </div>
        
        <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
          </svg>
          <h3>No products found</h3>
          <p>Try adjusting your filters or browse all pieces</p>
          <a href="<?php echo url('shop.php'); ?>" style="color: #D4A574; text-decoration: none; font-size: 12px; letter-spacing: 0.2em; text-transform: uppercase;">
            View All Products →
          </a>
        </div>
        <?php endif; ?>
        
      </main>
      
    </div>
  </div>
</div>

<script>
// Wishlist functionality
function toggleWishlist(productId, buttonElement) {
  <?php if (!isset($_SESSION['customer_id'])): ?>
  alert('Please login to use wishlist');
  window.location.href = '<?php echo url("login.php?redirect=shop.php"); ?>';
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
      updateWishlistBadge(data.wishlist_count);
      
      // Show toast notification
      showToast(data.message);
    } else {
      alert(data.message || 'Failed to update wishlist');
    }
  })
  .catch(err => {
    console.error('Error:', err);
    alert('Failed to update wishlist');
  });
}

// Update wishlist badge
function updateWishlistBadge(count) {
  const badge = document.querySelector('.wishlist-badge');
  if (count > 0) {
    if (badge) {
      badge.textContent = count;
    } else {
      const wishlistIcon = document.getElementById('wishlist-icon');
      if (wishlistIcon && wishlistIcon.parentElement) {
        const newBadge = document.createElement('span');
        newBadge.className = 'wishlist-badge';
        newBadge.textContent = count;
        wishlistIcon.parentElement.appendChild(newBadge);
      }
    }
  } else if (badge) {
    badge.remove();
  }
}

// Toast notification
function showToast(message) {
  const existing = document.querySelector('.toast-notification');
  if (existing) existing.remove();
  
  const toast = document.createElement('div');
  toast.className = 'toast-notification';
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

// Add toast animations
const style = document.createElement('style');
style.textContent = `
@keyframes slideIn {
  from { transform: translateX(400px); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}
@keyframes slideOut {
  from { transform: translateX(0); opacity: 1; }
  to { transform: translateX(400px); opacity: 0; }
}
`;
document.head.appendChild(style);

// Check wishlist status on page load
document.addEventListener('DOMContentLoaded', function() {
  <?php if (isset($_SESSION['customer_id'])): ?>
  const productIds = <?php echo json_encode(array_column($products, 'id')); ?>;
  productIds.forEach(productId => {
    fetch('<?php echo url("api/wishlist.php?action=check&product_id="); ?>' + productId)
      .then(res => res.json())
      .then(data => {
        if (data.in_wishlist) {
          const button = document.querySelector(`[data-product-id="${productId}"]`);
          if (button) {
            button.classList.add('in-wishlist');
            const heartPath = button.querySelector('svg path');
            if (heartPath) {
              heartPath.setAttribute('fill', '#EF4444');
              heartPath.setAttribute('stroke', '#EF4444');
            }
            button.title = 'Remove from wishlist';
          }
        }
      })
      .catch(err => console.error('Error checking wishlist:', err));
  });
  <?php endif; ?>
});

function quickView(productId) {
  alert('Quick view coming soon! Product ID: ' + productId);
}
</script>

<?php include 'includes/footer.php'; ?>
