<?php 
$page_title = 'Search Results';
include 'includes/header.php';

$search_query = isset($_GET['q']) ? clean_input($_GET['q']) : '';
$products = [];
$result_count = 0;

if ($search_query) {
    $query = "SELECT * FROM products WHERE 
        name LIKE '%$search_query%' OR 
        description LIKE '%$search_query%' OR 
        category LIKE '%$search_query%' 
        ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    $result_count = mysqli_num_rows($result);
}
?>

<div class="search-page">
  <div class="search-header">
    <div class="search-header-inner">
      <form method="GET" class="search-form-main">
        <input type="text" 
               name="q" 
               value="<?php echo htmlspecialchars($search_query); ?>" 
               placeholder="Search for earrings, necklaces, rings..." 
               autofocus>
        <button type="submit">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
          </svg>
        </button>
      </form>

      <?php if ($search_query): ?>
      <div class="search-results-count">
        <?php echo $result_count; ?> result<?php echo $result_count !== 1 ? 's' : ''; ?> for "<?php echo htmlspecialchars($search_query); ?>"
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="search-content">
    <?php if (!$search_query): ?>
      <div class="search-empty">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
          <path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
        </svg>
        <h2>Search our collection</h2>
        <p>Type in the search box above to find products</p>
      </div>

    <?php elseif ($result_count === 0): ?>
      <div class="search-empty">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
          <path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
        </svg>
        <h2>No results found</h2>
        <p>Try searching for "earrings", "gold", or "rings"</p>
        <a href="<?php echo url('shop.php'); ?>" class="btn-primary" style="margin-top: 24px;">Browse all products</a>
      </div>

    <?php else: ?>
      <div class="products">
        <?php while ($product = mysqli_fetch_assoc($result)): 
          $discount = calculate_discount($product['original_price'], $product['price']);
        ?>
        <div class="product">
          <a href="<?php echo url('product.php?slug=' . $product['slug']); ?>" style="text-decoration: none; color: inherit;">
            <div class="product-image">
              <?php if ($product['is_bestseller']): ?>
              <span class="badge bestseller">Bestseller</span>
              <?php elseif ($product['is_new']): ?>
              <span class="badge new">New</span>
              <?php endif; ?>
              
              <div class="wishlist">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
              </div>
              
              <?php if ($product['image_main']): ?>
              <img src="<?php echo url('assets/images/products/' . $product['image_main']); ?>" 
                   alt="<?php echo htmlspecialchars($product['name']); ?>"
                   <?php if ($product['image_hover']): ?>
                   data-hover="<?php echo url('assets/images/products/' . $product['image_hover']); ?>"
                   <?php endif; ?>>
              <?php else: ?>
              <svg viewBox="0 0 100 100">
                <defs><radialGradient id="pg<?php echo $product['id']; ?>" cx="35%" cy="30%"><stop offset="0%" stop-color="#FFE8C2"/><stop offset="50%" stop-color="#D4A574"/><stop offset="100%" stop-color="#6B4A18"/></radialGradient></defs>
                <circle cx="50" cy="50" r="22" stroke="url(#pg<?php echo $product['id']; ?>)" stroke-width="2" fill="none"/>
              </svg>
              <?php endif; ?>
              
              <div class="quick-add" onclick="event.preventDefault(); addToCart(<?php echo $product['id']; ?>);">Add to bag</div>
            </div>
          </a>
          
          <div class="product-info">
            <div class="product-category"><?php echo ucfirst($product['category']); ?></div>
            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
            <div class="product-price">
              <span class="price-current"><?php echo format_price($product['price']); ?></span>
              <?php if ($product['original_price'] && $discount > 0): ?>
              <span class="price-old"><?php echo format_price($product['original_price']); ?></span>
              <span class="price-off"><?php echo $discount; ?>% off</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
.search-page {
  background: var(--ink);
  min-height: 100vh;
  padding: 40px 60px 120px;
}

.search-header {
  max-width: 1600px;
  margin: 0 auto 60px;
}

.search-form-main {
  display: flex;
  max-width: 600px;
  margin: 0 auto 24px;
  background: var(--charcoal);
  border: 1px solid var(--line-warm);
  border-radius: 8px;
  overflow: hidden;
  transition: border-color 0.3s;
}

.search-form-main:focus-within {
  border-color: #D4A574;
}

.search-form-main input {
  flex: 1;
  background: transparent;
  border: none;
  padding: 16px 20px;
  color: #F5F1E8;
  font-size: 16px;
  font-family: 'Inter', sans-serif;
  outline: none;
}

.search-form-main input::placeholder {
  color: rgba(245, 241, 232, 0.7);
  opacity: 0.6;
}

.search-form-main button {
  background: transparent;
  border: none;
  padding: 16px 20px;
  color: #D4A574;
  cursor: pointer;
  transition: color 0.3s;
}

.search-form-main button:hover {
  color: #F5F1E8;
}

.search-results-count {
  text-align: center;
  font-size: 14px;
  color: rgba(245, 241, 232, 0.7);
  letter-spacing: 0.05em;
}

.search-content {
  max-width: 1600px;
  margin: 0 auto;
}

.search-empty {
  text-align: center;
  padding: 100px 20px;
  color: rgba(245, 241, 232, 0.7);
}

.search-empty svg {
  margin: 0 auto 24px;
  opacity: 0.3;
}

.search-empty h2 {
  font-family: 'Italiana', serif;
  font-size: 32px;
  color: #F5F1E8;
  margin-bottom: 12px;
}

.search-empty p {
  font-size: 15px;
  color: rgba(245, 241, 232, 0.7);
}

@media (max-width: 1024px) {
  .search-page { padding: 30px 30px 100px; }
}
</style>

<?php include 'includes/footer.php'; ?>
