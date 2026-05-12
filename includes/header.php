<?php
require_once __DIR__ . '/db.php';
$cart_count = get_cart_count();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? $page_title . ' — Reverie' : 'Reverie — Modern Jewellery, Timeless Craft'; ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Italiana&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css">
<style>
/* Account Dropdown */
.account-dropdown {
  position: relative;
}

.account-trigger {
  position: relative;
  display: block;
}

.login-indicator {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 6px;
  height: 6px;
  background: var(--gold);
  border-radius: 50%;
  border: 1.5px solid var(--ink);
}

.account-dropdown-menu {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  background: var(--charcoal);
  border: 1px solid var(--line-warm);
  border-radius: 8px;
  min-width: 220px;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s ease;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  z-index: 1000;
}

.account-dropdown:hover .account-dropdown-menu {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-header {
  padding: 16px 18px;
  border-bottom: 1px solid var(--line-subtle);
}

.dropdown-name {
  font-size: 14px;
  font-weight: 500;
  color: #F5F1E8;
  margin-bottom: 4px;
}

.dropdown-email {
  font-size: 12px;
  color: rgba(245, 241, 232, 0.7);
}

.dropdown-divider {
  height: 1px;
  background: var(--line-subtle);
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 18px;
  color: #F5F1E8;
  text-decoration: none;
  font-size: 13px;
  transition: background 0.2s;
}

.dropdown-item:hover {
  background: rgba(212, 165, 116, 0.1);
}

.dropdown-item svg {
  opacity: 0.6;
}

.dropdown-logout {
  color: #EF4444;
}

.dropdown-logout:hover {
  background: rgba(239, 68, 68, 0.1);
}

.wishlist-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background: #EF4444;
  color: white;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
}
</style>
</head>
<body>

<!-- Announcement Bar -->
<div class="announcement">
  <div class="announcement-track">
    <span>Complimentary shipping above ₹999</span>
    <span>Cash on delivery available</span>
    <span>7-day returns</span>
    <span>Crafted in India</span>
    <span>Complimentary shipping above ₹999</span>
    <span>Cash on delivery available</span>
    <span>7-day returns</span>
    <span>Crafted in India</span>
  </div>
</div>

<!-- Navigation -->
<nav>
  <div class="nav-inner">
    <ul class="nav-links">
      <li><a href="<?php echo url('shop.php'); ?>" class="<?php echo $current_page == 'shop' ? 'active' : ''; ?>">Shop</a></li>
      <li><a href="<?php echo url('shop.php?category=new'); ?>" class="<?php echo isset($_GET['category']) && $_GET['category'] == 'new' ? 'active' : ''; ?>">New In</a></li>
      <li><a href="#story">Atelier</a></li>
    </ul>
    <div>
      <a href="<?php echo url(); ?>" style="text-decoration: none;">
        <div class="logo">REVERIE</div>
        <div class="logo-tagline">Fine Jewellery</div>
      </a>
    </div>
    <div class="nav-icons">
      <a href="<?php echo url('search.php'); ?>">
        <svg class="nav-icon" id="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
      </a>
      <div class="account-dropdown">
        <a href="<?php echo url('account.php'); ?>" class="account-trigger">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          </svg>
          <?php if (isset($_SESSION['customer_id'])): ?>
          <span class="login-indicator"></span>
          <?php endif; ?>
        </a>
        
        <?php if (isset($_SESSION['customer_id'])): ?>
        <div class="account-dropdown-menu">
          <div class="dropdown-header">
            <div class="dropdown-name"><?php echo htmlspecialchars($_SESSION['customer_name']); ?></div>
            <div class="dropdown-email"><?php echo htmlspecialchars($_SESSION['customer_email']); ?></div>
          </div>
          <div class="dropdown-divider"></div>
          <a href="<?php echo url('account.php'); ?>" class="dropdown-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            My Account
          </a>
          <a href="<?php echo url('account.php?tab=orders'); ?>" class="dropdown-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            Track Orders
          </a>
          <div class="dropdown-divider"></div>
          <a href="<?php echo url('account.php?logout=1'); ?>" class="dropdown-item dropdown-logout">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
            </svg>
            Logout
          </a>
        </div>
        <?php endif; ?>
      </div>
      <div style="position: relative;">
        <a href="<?php echo url('wishlist.php'); ?>" style="text-decoration: none; display: block;" title="My Wishlist">
          <svg class="nav-icon" id="wishlist-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
          </svg>
        </a>
        <?php 
        if (isset($_SESSION['customer_id'])) {
          $wl_count_q = mysqli_query($conn, "SELECT COUNT(*) as count FROM wishlist WHERE customer_id = " . (int)$_SESSION['customer_id']);
          $wl_count = mysqli_fetch_assoc($wl_count_q)['count'];
          if ($wl_count > 0): 
        ?>
        <span class="wishlist-badge"><?php echo $wl_count; ?></span>
        <?php 
          endif;
        }
        ?>
      </div>
      <div style="position: relative;">
        <svg class="nav-icon" id="cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
        </svg>
        <?php if ($cart_count > 0): ?>
        <span class="cart-badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- WhatsApp Floating Button -->
<?php $whatsapp = get_setting('whatsapp_number'); if ($whatsapp): ?>
<a href="https://wa.me/<?php echo $whatsapp; ?>?text=Hi!%20I'm%20interested%20in%20Reverie%20jewellery" 
   class="whatsapp-float" 
   target="_blank" 
   rel="noopener">
  <svg viewBox="0 0 24 24" fill="currentColor">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
  </svg>
</a>
<?php endif; ?>

<script src="<?php echo BASE_PATH; ?>/assets/js/main.js"></script>
