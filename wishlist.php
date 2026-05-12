<?php 
require_once 'config.php';
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . url('login.php?redirect=wishlist'));
    exit;
}

$customer_id = (int)$_SESSION['customer_id'];
$page_title = 'My Wishlist';

// Get wishlist items with product details
$query = "SELECT p.*, w.added_at, w.id as wishlist_id 
          FROM wishlist w 
          JOIN products p ON w.product_id = p.id 
          WHERE w.customer_id = $customer_id 
          ORDER BY w.added_at DESC";
$result = mysqli_query($conn, $query);
$wishlist_items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $wishlist_items[] = $row;
}

include 'includes/header.php';
?>

<style>
.wishlist-page {
  background: var(--ink);
  min-height: 100vh;
  padding: 80px 60px 120px;
}

.wishlist-container {
  max-width: 1400px;
  margin: 0 auto;
}

.wishlist-header {
  text-align: center;
  margin-bottom: 60px;
}

.wishlist-title {
  font-family: 'Italiana', serif;
  font-size: clamp(42px, 5vw, 64px);
  color: #F5F1E8;
  margin-bottom: 12px;
  text-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);
}

.wishlist-count {
  font-size: 16px;
  color: rgba(245, 241, 232, 0.7);
  letter-spacing: 0.05em;
}

/* Wishlist Grid */
.wishlist-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 32px;
  margin-bottom: 60px;
}

/* Product Card */
.wishlist-card {
  position: relative;
  background: var(--charcoal);
  border: 1px solid var(--line-subtle);
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s;
}

.wishlist-card:hover {
  transform: translateY(-4px);
  border-color: var(--gold);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
}

.wishlist-image {
  position: relative;
  aspect-ratio: 1;
  background: linear-gradient(135deg, var(--ink) 0%, var(--charcoal) 100%);
  overflow: hidden;
}

.wishlist-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s;
}

.wishlist-card:hover .wishlist-image img {
  transform: scale(1.08);
}

/* Remove Button */
.remove-wishlist-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 40px;
  height: 40px;
  background: rgba(31, 41, 55, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid var(--line-subtle);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
  z-index: 2;
}

.remove-wishlist-btn:hover {
  background: #EF4444;
  border-color: #EF4444;
  transform: scale(1.1);
}

.remove-wishlist-btn svg {
  width: 20px;
  height: 20px;
  color: #F5F1E8;
}

/* Product Info */
.wishlist-info {
  padding: 20px;
}

.wishlist-category {
  font-size: 10px;
  color: #D4A574;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  margin-bottom: 8px;
  font-weight: 600;
}

.wishlist-name {
  font-family: 'Cormorant Garamond', serif;
  font-size: 20px;
  color: #F5F1E8;
  margin-bottom: 12px;
  font-weight: 500;
  line-height: 1.3;
}

.wishlist-price {
  font-family: 'Cormorant Garamond', serif;
  font-size: 24px;
  color: #D4A574;
  font-weight: 600;
  margin-bottom: 16px;
}

.wishlist-actions {
  display: flex;
  gap: 8px;
}

.btn-view {
  flex: 1;
  padding: 12px 20px;
  background: transparent;
  border: 1px solid var(--gold);
  color: var(--gold);
  text-decoration: none;
  font-size: 11px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  font-weight: 600;
  border-radius: 6px;
  text-align: center;
  transition: all 0.3s;
}

.btn-view:hover {
  background: var(--gold);
  color: var(--ink);
}

.btn-add-cart {
  flex: 1;
  padding: 12px 20px;
  background: var(--gold);
  border: none;
  color: var(--ink);
  font-size: 11px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-add-cart:hover {
  background: #C89554;
  transform: translateY(-2px);
}

/* Empty State */
.wishlist-empty {
  text-align: center;
  padding: 100px 20px;
}

.wishlist-empty svg {
  width: 120px;
  height: 120px;
  color: rgba(245, 241, 232, 0.2);
  margin-bottom: 32px;
}

.wishlist-empty h3 {
  font-size: 28px;
  color: #F5F1E8;
  margin-bottom: 16px;
}

.wishlist-empty p {
  font-size: 16px;
  color: rgba(245, 241, 232, 0.6);
  margin-bottom: 32px;
}

.btn-shop {
  display: inline-block;
  padding: 16px 32px;
  background: var(--gold);
  color: var(--ink);
  text-decoration: none;
  font-size: 12px;
  letter-spacing: 0.25em;
  text-transform: uppercase;
  font-weight: 600;
  border-radius: 6px;
  transition: all 0.3s;
}

.btn-shop:hover {
  background: #C89554;
  transform: translateY(-2px);
}

@media (max-width: 968px) {
  .wishlist-page { padding: 60px 30px 100px; }
  .wishlist-grid { grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 24px; }
}
</style>

<div class="wishlist-page">
  <div class="wishlist-container">
    
    <div class="wishlist-header">
      <h1 class="wishlist-title">My Wishlist</h1>
      <p class="wishlist-count"><?php echo count($wishlist_items); ?> item<?php echo count($wishlist_items) != 1 ? 's' : ''; ?></p>
    </div>

    <?php if (count($wishlist_items) > 0): ?>
    <div class="wishlist-grid">
      <?php foreach ($wishlist_items as $item): 
        $image_url = $item['image_main'] 
          ? url('assets/images/products/' . $item['image_main']) 
          : url('assets/images/placeholder.svg');
      ?>
      
      <div class="wishlist-card" data-wishlist-id="<?php echo $item['wishlist_id']; ?>">
        <div class="wishlist-image">
          <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
          
          <button class="remove-wishlist-btn" onclick="removeFromWishlist(<?php echo $item['id']; ?>, <?php echo $item['wishlist_id']; ?>)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>

        <div class="wishlist-info">
          <div class="wishlist-category"><?php echo strtoupper($item['category']); ?></div>
          <h3 class="wishlist-name"><?php echo htmlspecialchars($item['name']); ?></h3>
          <div class="wishlist-price"><?php echo format_price($item['price']); ?></div>
          
          <div class="wishlist-actions">
            <a href="<?php echo url('product.php?slug=' . $item['slug']); ?>" class="btn-view">View</a>
            <button class="btn-add-cart" onclick="addToCartFromWishlist(<?php echo $item['id']; ?>)">Add to Bag</button>
          </div>
        </div>
      </div>
      
      <?php endforeach; ?>
    </div>
    
    <?php else: ?>
    <!-- Empty State -->
    <div class="wishlist-empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <h3>Your wishlist is empty</h3>
      <p>Save your favorite pieces to view them here later</p>
      <a href="<?php echo url('shop.php'); ?>" class="btn-shop">Explore Products</a>
    </div>
    <?php endif; ?>
    
  </div>
</div>

<script>
// Remove from wishlist
function removeFromWishlist(productId, wishlistId) {
  if (!confirm('Remove this item from your wishlist?')) return;
  
  fetch('<?php echo url('api/wishlist.php?action=remove'); ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id: productId })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      // Remove card with animation
      const card = document.querySelector(`[data-wishlist-id="${wishlistId}"]`);
      card.style.opacity = '0';
      card.style.transform = 'scale(0.9)';
      setTimeout(() => {
        card.remove();
        
        // Update count
        const countEl = document.querySelector('.wishlist-count');
        if (countEl) {
          const newCount = data.wishlist_count;
          countEl.textContent = newCount + ' item' + (newCount != 1 ? 's' : '');
        }
        
        // Show empty state if no items left
        if (data.wishlist_count === 0) {
          location.reload();
        }
      }, 300);
      
      // Update header wishlist count
      updateWishlistBadge(data.wishlist_count);
    } else {
      alert(data.message || 'Failed to remove item');
    }
  })
  .catch(err => {
    console.error('Error:', err);
    alert('Failed to remove item');
  });
}

// Add to cart from wishlist
function addToCartFromWishlist(productId) {
  fetch('<?php echo url('api/cart.php?action=add'); ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id: productId, quantity: 1 })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Added to bag!');
      
      // Update cart badge
      const cartBadge = document.querySelector('.cart-badge');
      if (cartBadge) {
        cartBadge.textContent = data.cart_count;
      } else if (data.cart_count > 0) {
        const cartIcon = document.getElementById('cart-icon');
        const badge = document.createElement('span');
        badge.className = 'cart-badge';
        badge.textContent = data.cart_count;
        cartIcon.parentElement.appendChild(badge);
      }
    } else {
      alert(data.message || 'Failed to add to cart');
    }
  })
  .catch(err => {
    console.error('Error:', err);
    alert('Failed to add to cart');
  });
}

// Update wishlist badge in header
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
</script>

<?php include 'includes/footer.php'; ?>
