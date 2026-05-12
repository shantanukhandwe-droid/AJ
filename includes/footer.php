<!-- Footer -->
<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div class="footer-brand">
        <h3>REVERIE</h3>
        <p>Fine jewellery for women who've moved past more. Designed in India, delivered worldwide.</p>
        <div class="footer-social">
          <a href="#" aria-label="Instagram">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="3" y="3" width="18" height="18" rx="5"/>
              <circle cx="12" cy="12" r="4"/>
              <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/>
            </svg>
          </a>
          <a href="#" aria-label="Pinterest">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10"/>
              <path d="M8 21c0-2 1-3 2-6 1-3 0-6 2-7 1.5-.75 3-.5 4.5 1 1.5 1.5 1 4-1 5.5s-3 0-3-1"/>
            </svg>
          </a>
          <a href="https://wa.me/<?php echo get_setting('whatsapp_number'); ?>" aria-label="WhatsApp" target="_blank">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M3 21l1.65-3.8a9 9 0 113.4 2.9L3 21" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Shop</h4>
        <ul>
          <li><a href="<?php echo url('shop.php'); ?>">All Pieces</a></li>
          <li><a href="<?php echo url('shop.php?category=earrings'); ?>">Earrings</a></li>
          <li><a href="<?php echo url('shop.php?category=necklaces'); ?>">Necklaces</a></li>
          <li><a href="<?php echo url('shop.php?category=rings'); ?>">Rings</a></li>
          <li><a href="<?php echo url('shop.php?category=bangles'); ?>">Bangles</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Atelier</h4>
        <ul>
          <li><a href="/about.php">About</a></li>
          <li><a href="/about.php#craft">Craftsmanship</a></li>
          <li><a href="/about.php#materials">Materials</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Care</h4>
        <ul>
          <li><a href="/shipping.php">Shipping</a></li>
          <li><a href="/returns.php">Returns</a></li>
          <li><a href="/care.php">Jewellery care</a></li>
          <li><a href="/contact.php">Contact</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <div>© <?php echo date('Y'); ?> Reverie Fine Jewellery. Crafted in India.</div>
      <div class="payment-methods">
        <span class="payment">UPI</span>
        <span class="payment">VISA</span>
        <span class="payment">MC</span>
        <span class="payment">RUPAY</span>
        <?php if (get_setting('cod_enabled')): ?>
        <span class="payment">COD</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</footer>

<!-- Cart Drawer (Slide-out from right) -->
<div class="cart-drawer" id="cart-drawer">
  <div class="cart-drawer-overlay" id="cart-overlay"></div>
  <div class="cart-drawer-panel">
    <div class="cart-drawer-header">
      <h3>Your cart</h3>
      <button class="cart-close" id="cart-close">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round"/>
        </svg>
      </button>
    </div>
    <div class="cart-drawer-body" id="cart-body">
      <!-- Cart items loaded via AJAX -->
    </div>
  </div>
</div>

</body>
</html>
