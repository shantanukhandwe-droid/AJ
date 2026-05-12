<?php 
$page_title = 'Modern Jewellery, Timeless Craft';
include 'includes/header.php';

// Fetch featured products (bestsellers + new)
$featured_query = "SELECT * FROM products WHERE is_bestseller = 1 OR is_new = 1 ORDER BY is_bestseller DESC, created_at DESC LIMIT 8";
$featured_result = mysqli_query($conn, $featured_query);
?>

<!-- Hero -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-tag">
      <span class="overline">Edit No. 07 · Spring 2026</span>
    </div>
    <h1 class="display hero-title">
      Quiet<br>luxury, <em>loud</em><br>statement
    </h1>
    <p class="hero-subtitle">
      Minimalist jewellery for women who don't need to announce themselves. Each piece sculpted in India, designed to move between boardrooms and candlelit dinners with equal grace.
    </p>
    <div class="hero-cta">
      <a href="<?php echo url('shop.php'); ?>" class="btn-primary">
        Shop the edit
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M1 7h12m0 0L8 2m5 5L8 12" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </a>
      <a href="#story" class="btn-ghost">The atelier</a>
    </div>
  </div>

  <div class="hero-visual">
    <div class="hero-decor hero-decor-1">◆</div>
    <div class="hero-decor hero-decor-2">◆</div>

    <div class="hero-price-tag">
      <span class="overline">From</span>
      <div class="price">₹499</div>
    </div>

    <div class="hero-frame">
      <svg class="hero-jewel" viewBox="0 0 300 300" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <radialGradient id="goldC" cx="35%" cy="30%">
            <stop offset="0%" stop-color="#FFE8C2"/>
            <stop offset="40%" stop-color="#D4A574"/>
            <stop offset="100%" stop-color="#6B4A18"/>
          </radialGradient>
        </defs>
        <!-- Chain -->
        <path d="M 60 90 Q 150 60 240 90" stroke="#D4A574" stroke-width="1" fill="none" opacity="0.7"/>
        <!-- Minimalist circle pendant -->
        <circle cx="150" cy="175" r="58" fill="none" stroke="url(#goldC)" stroke-width="4"/>
        <!-- Inner detail -->
        <circle cx="150" cy="175" r="38" fill="none" stroke="url(#goldC)" stroke-width="1" opacity="0.5"/>
        <!-- Central diamond -->
        <path d="M150 155 L140 175 L150 198 L160 175 Z" fill="url(#goldC)" stroke="#4A3318" stroke-width="0.5"/>
        <!-- Top connector -->
        <circle cx="150" cy="117" r="4" fill="url(#goldC)"/>
        <!-- Subtle sparkles -->
        <g fill="#E8C89A" opacity="0.8">
          <circle cx="90" cy="80" r="1.2"/>
          <circle cx="120" cy="66" r="1.2"/>
          <circle cx="180" cy="66" r="1.2"/>
          <circle cx="210" cy="80" r="1.2"/>
        </g>
      </svg>
    </div>

    <div class="hero-rating">
      <div>
        <div class="rating-stars">★★★★★</div>
      </div>
      <div class="rating-text">
        <strong>4.9</strong>
        12,000+ reviews
      </div>
    </div>
  </div>
</section>

<!-- Trust Strip -->
<div class="trust-strip">
  <div class="trust-inner">
    <div class="trust-item">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round"/>
        <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9" stroke-linecap="round"/>
      </svg>
      <span>Free shipping above ₹999</span>
    </div>
    <div class="trust-item">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-linecap="round"/>
      </svg>
      <span>7-day returns</span>
    </div>
    <div class="trust-item">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round"/>
      </svg>
      <span>Cash on delivery</span>
    </div>
    <div class="trust-item">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
        <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
      </svg>
      <span>4.9★ rating</span>
    </div>
  </div>
</div>

<!-- Marquee -->
<div class="marquee">
  <div class="marquee-track">
    <span>Handcrafted</span>
    <span>Hypoallergenic</span>
    <span>Anti-tarnish</span>
    <span>Timeless</span>
    <span>Handcrafted</span>
    <span>Hypoallergenic</span>
    <span>Anti-tarnish</span>
    <span>Timeless</span>
  </div>
</div>

<!-- Featured Products -->
<section class="products-section" id="products">
  <div class="products-inner">
    <div class="products-head">
      <div>
        <span class="overline">The collection</span>
        <h2 class="display section-title">Worn by<br><em>thousands</em></h2>
      </div>
    </div>

    <div class="products">
      <?php while ($product = mysqli_fetch_assoc($featured_result)): 
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

    <div style="text-align: center; margin-top: 90px;">
      <a href="<?php echo url('shop.php'); ?>" class="btn-primary">
        View the full edit
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M1 7h12m0 0L8 2m5 5L8 12" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </a>
    </div>
  </div>
</section>

<!-- Story -->
<section class="story" id="story">
  <div class="story-inner">
    <div class="story-content">
      <span class="overline">The atelier</span>
      <h2 class="display story-title">Jewellery designed to <em class="serif">disappear</em> — until it doesn't.</h2>
      <p class="story-text">
        Reverie is jewellery for women who've moved past more. Past heavier. Past louder. Pieces so well-made they feel invisible on your skin, and so considered they earn the second glance across the room.
      </p>
      <p class="story-text">
        Designed by women. Crafted by artisans. Kept intentionally small — we release eight pieces a season, not eighty. Each one has to earn its place.
      </p>
    </div>
  </div>
</section>

<style>
.trust-strip {
  background: var(--charcoal);
  border-top: 1px solid var(--line-subtle);
  border-bottom: 1px solid var(--line-subtle);
  padding: 20px 60px;
}

.trust-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 32px;
}

.trust-item {
  display: flex;
  align-items: center;
  gap: 10px;
  justify-content: center;
}

.trust-item svg {
  color: #D4A574;
  flex-shrink: 0;
}

.trust-item span {
  font-size: 13px;
  color: #F5F1E8;
  letter-spacing: 0.05em;
}

@media (max-width: 768px) {
  .trust-strip { padding: 16px 20px; }
  .trust-inner {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  .trust-item { font-size: 11px; }
  .trust-item svg { width: 16px; height: 16px; }
}
</style>

<?php include 'includes/footer.php'; ?>
