<?php 
require_once 'includes/db.php';

$cart = get_cart();
if (empty($cart)) {
    header('Location: ' . url('shop.php'));
    exit;
}

$is_logged_in = isset($_SESSION['customer_id']);
$customer = null;
$addresses = [];

if ($is_logged_in) {
    $cid = (int)$_SESSION['customer_id'];
    $cust_q = mysqli_query($conn, "SELECT * FROM customers WHERE id = $cid");
    $customer = mysqli_fetch_assoc($cust_q);
    
    $addr_q = mysqli_query($conn, "SELECT * FROM customer_addresses WHERE customer_id = $cid ORDER BY is_default DESC, id DESC");
    while ($a = mysqli_fetch_assoc($addr_q)) $addresses[] = $a;
}

// Determine current step
$step = isset($_GET['step']) ? $_GET['step'] : 'bag';
if (!$is_logged_in && $step !== 'bag') {
    header('Location: ' . url('login.php?redirect=checkout'));
    exit;
}

$success = '';
$error = '';

// Handle: Add new address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address']) && $is_logged_in) {
    $name = clean_input($_POST['full_name']);
    $phone = clean_input($_POST['phone']);
    $addr = clean_input($_POST['address_line']);
    $city = clean_input($_POST['city']);
    $state = clean_input($_POST['state']);
    $pincode = clean_input($_POST['pincode']);
    $type = clean_input($_POST['address_type']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    if ($is_default) {
        mysqli_query($conn, "UPDATE customer_addresses SET is_default = 0 WHERE customer_id = $cid");
    }
    
    $q = "INSERT INTO customer_addresses (customer_id, full_name, phone, address_line, city, state, pincode, address_type, is_default) 
          VALUES ($cid, '$name', '$phone', '$addr', '$city', '$state', '$pincode', '$type', $is_default)";
    
    if (mysqli_query($conn, $q)) {
        header('Location: ' . url('checkout.php?step=address'));
        exit;
    } else {
        $error = 'Failed to save address';
    }
}

// Handle: Place order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order']) && $is_logged_in) {
    $address_id = (int)$_POST['address_id'];
    $payment_method = clean_input($_POST['payment_method']);
    
    // Get address details
    $addr_result = mysqli_query($conn, "SELECT * FROM customer_addresses WHERE id = $address_id AND customer_id = $cid");
    $addr = mysqli_fetch_assoc($addr_result);
    
    if (!$addr) {
        $error = 'Invalid address selected';
    } else {
        $subtotal = get_cart_total();
        $shipping_threshold = (float)get_setting('shipping_fee_threshold');
        $shipping_fee = $subtotal >= $shipping_threshold ? 0 : (float)get_setting('shipping_fee');
        $total = $subtotal + $shipping_fee;
        $order_number = generate_order_number();
        
        $name = mysqli_real_escape_string($conn, $addr['full_name']);
        $email = mysqli_real_escape_string($conn, $customer['email']);
        $phone = mysqli_real_escape_string($conn, $addr['phone']);
        $address = mysqli_real_escape_string($conn, $addr['address_line']);
        $city = mysqli_real_escape_string($conn, $addr['city']);
        $state = mysqli_real_escape_string($conn, $addr['state']);
        $pincode = mysqli_real_escape_string($conn, $addr['pincode']);
        
        // Check if customer_id column exists
        $col_check = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'customer_id'");
        $has_customer_id = mysqli_num_rows($col_check) > 0;
        
        if ($has_customer_id) {
            $insert = "INSERT INTO orders (order_number, customer_id, customer_name, customer_email, customer_phone, customer_address, customer_city, customer_state, customer_pincode, subtotal, shipping_fee, total, payment_method) 
                       VALUES ('$order_number', $cid, '$name', '$email', '$phone', '$address', '$city', '$state', '$pincode', $subtotal, $shipping_fee, $total, '$payment_method')";
        } else {
            $insert = "INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, customer_address, customer_city, customer_state, customer_pincode, subtotal, shipping_fee, total, payment_method) 
                       VALUES ('$order_number', '$name', '$email', '$phone', '$address', '$city', '$state', '$pincode', $subtotal, $shipping_fee, $total, '$payment_method')";
        }
        
        if (mysqli_query($conn, $insert)) {
            $order_id = mysqli_insert_id($conn);
            
            foreach ($cart as $item) {
                $pid = (int)$item['product_id'];
                $qty = (int)$item['quantity'];
                $variant = $item['variant'] ? "'" . clean_input($item['variant']) . "'" : "NULL";
                $prod_q = mysqli_query($conn, "SELECT name, price FROM products WHERE id = $pid");
                $prod = mysqli_fetch_assoc($prod_q);
                
                mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, product_name, variant_info, price, quantity) 
                                     VALUES ($order_id, $pid, '{$prod['name']}', $variant, {$prod['price']}, $qty)");
            }
            
            clear_cart();
            header('Location: ' . url("order-success.php?order=$order_number"));
            exit;
        } else {
            $error = 'Failed to place order: ' . mysqli_error($conn);
        }
    }
}

$subtotal = get_cart_total();
$shipping_threshold = (float)get_setting('shipping_fee_threshold');
$shipping_fee = $subtotal >= $shipping_threshold ? 0 : (float)get_setting('shipping_fee');
$total = $subtotal + $shipping_fee;
$item_count = count($cart);

$page_title = 'Checkout';
include 'includes/header.php';
?>

<style>
.checkout-wrap { background: var(--ink); min-height: 100vh; padding: 40px 60px 100px; }
.checkout-inner { max-width: 1200px; margin: 0 auto; }

/* Stepper */
.stepper {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-bottom: 48px;
  padding-bottom: 32px;
  border-bottom: 1px solid var(--line-subtle);
}
.step {
  display: flex;
  align-items: center;
  gap: 10px;
  color: rgba(245, 241, 232, 0.7);
  font-size: 12px;
  letter-spacing: 0.3em;
  text-transform: uppercase;
}
.step-num {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 1px solid var(--line-warm);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
}
.step.active { color: #D4A574; }
.step.active .step-num { background: var(--gold); color: var(--ink); border-color: #D4A574; }
.step.completed { color: #F5F1E8; }
.step.completed .step-num { background: var(--charcoal); border-color: #D4A574; color: #D4A574; }
.step-line {
  width: 40px;
  height: 1px;
  background: var(--line-subtle);
}

/* Layout */
.checkout-grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 40px;
}

.checkout-content {
  background: var(--charcoal);
  border: 1px solid var(--line-subtle);
  border-radius: 8px;
  padding: 32px;
}

.checkout-summary {
  background: var(--charcoal);
  border: 1px solid var(--line-subtle);
  border-radius: 8px;
  padding: 28px;
  height: fit-content;
  position: sticky;
  top: 100px;
}

.section-heading {
  font-size: 18px;
  color: #F5F1E8;
  margin-bottom: 24px;
  font-weight: 600;
}

/* Bag Items */
.bag-item {
  display: grid;
  grid-template-columns: 80px 1fr auto;
  gap: 16px;
  padding: 20px 0;
  border-bottom: 1px solid var(--line-subtle);
}
.bag-item:last-child { border-bottom: none; }
.bag-img { width: 80px; height: 80px; border-radius: 6px; overflow: hidden; background: var(--ink); }
.bag-img img { width: 100%; height: 100%; object-fit: cover; }
.bag-info h4 { font-size: 14px; color: #F5F1E8; margin-bottom: 6px; font-weight: 500; }
.bag-info .variant { font-size: 12px; color: rgba(245, 241, 232, 0.7); margin-bottom: 8px; }
.bag-info .price { font-size: 14px; color: #D4A574; font-weight: 600; }
.bag-qty { display: flex; align-items: center; gap: 8px; }
.bag-qty button {
  width: 28px; height: 28px;
  background: var(--ink);
  border: 1px solid var(--line-warm);
  color: #F5F1E8;
  cursor: pointer;
  border-radius: 4px;
}
.bag-qty span { font-size: 14px; min-width: 24px; text-align: center; }

/* Address Cards */
.address-grid { display: grid; gap: 14px; margin-bottom: 24px; }
.addr-card {
  border: 1px solid var(--line-warm);
  border-radius: 8px;
  padding: 18px;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}
.addr-card:has(input:checked) { border-color: #D4A574; background: rgba(212, 165, 116, 0.05); }
.addr-card input[type="radio"] { position: absolute; top: 18px; left: 18px; }
.addr-content { padding-left: 30px; }
.addr-name {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}
.addr-name strong { color: #F5F1E8; font-size: 14px; }
.addr-badge {
  background: rgba(212, 165, 116, 0.1);
  color: #D4A574;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}
.addr-text { color: rgba(245, 241, 232, 0.7); font-size: 13px; line-height: 1.6; }
.addr-phone { color: #F5F1E8; font-size: 13px; margin-top: 6px; }

.btn-add-addr {
  display: block;
  width: 100%;
  padding: 16px;
  background: transparent;
  border: 1px dashed var(--line-warm);
  color: #D4A574;
  text-align: center;
  border-radius: 8px;
  cursor: pointer;
  font-size: 13px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  transition: all 0.2s;
  text-decoration: none;
}
.btn-add-addr:hover { border-color: #D4A574; background: rgba(212, 165, 116, 0.05); }

/* Address Form */
.addr-form {
  background: var(--ink);
  border-radius: 8px;
  padding: 24px;
  margin-bottom: 16px;
}
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
.form-row.full { grid-template-columns: 1fr; }
.fg label { display: block; font-size: 12px; color: rgba(245, 241, 232, 0.7); margin-bottom: 6px; }
.fg input, .fg textarea, .fg select {
  width: 100%;
  padding: 12px 14px;
  background: var(--charcoal);
  border: 1px solid var(--line-warm);
  border-radius: 6px;
  color: #F5F1E8;
  font-size: 14px;
  font-family: inherit;
}
.fg input:focus, .fg textarea:focus { outline: none; border-color: #D4A574; }
.checkbox-row { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.checkbox-row input { width: 16px; height: 16px; }
.checkbox-row label { font-size: 13px; color: #F5F1E8; }

/* Payment Options */
.payment-list { display: grid; gap: 12px; margin-bottom: 24px; }
.pay-option {
  border: 1px solid var(--line-warm);
  border-radius: 8px;
  padding: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: all 0.2s;
}
.pay-option:has(input:checked) { border-color: #D4A574; background: rgba(212, 165, 116, 0.05); }
.pay-option input { width: 18px; height: 18px; }
.pay-info h5 { color: #F5F1E8; font-size: 14px; margin-bottom: 4px; font-weight: 500; }
.pay-info p { color: rgba(245, 241, 232, 0.7); font-size: 12px; }

/* Buttons */
.btn-primary, .btn-continue {
  display: block;
  width: 100%;
  padding: 16px;
  background: var(--gold);
  color: var(--ink);
  border: none;
  border-radius: 6px;
  font-size: 12px;
  letter-spacing: 0.3em;
  text-transform: uppercase;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  text-align: center;
  font-family: inherit;
}
.btn-secondary {
  display: inline-block;
  padding: 12px 24px;
  background: transparent;
  color: #F5F1E8;
  border: 1px solid var(--line-warm);
  border-radius: 6px;
  font-size: 12px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  cursor: pointer;
  text-decoration: none;
  font-family: inherit;
}

/* Summary */
.sum-title { font-size: 16px; color: #F5F1E8; margin-bottom: 20px; font-weight: 600; }
.sum-items { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--line-subtle); }
.sum-item {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  color: rgba(245, 241, 232, 0.7);
  margin-bottom: 8px;
}
.sum-row {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  color: rgba(245, 241, 232, 0.7);
  margin-bottom: 12px;
}
.sum-total {
  display: flex;
  justify-content: space-between;
  font-size: 16px;
  color: #F5F1E8;
  font-weight: 600;
  padding-top: 16px;
  border-top: 1px solid var(--line-subtle);
  margin-top: 12px;
  margin-bottom: 20px;
}

.alert {
  padding: 12px 16px;
  border-radius: 6px;
  margin-bottom: 20px;
  font-size: 14px;
}
.alert-error { background: rgba(220,38,38,0.1); border: 1px solid rgba(220,38,38,0.3); color: #FCA5A5; }

.empty-msg { text-align: center; padding: 40px 20px; color: rgba(245, 241, 232, 0.7); }
.empty-msg p { margin-bottom: 16px; }

@media (max-width: 968px) {
  .checkout-wrap { padding: 30px 20px 80px; }
  .checkout-grid { grid-template-columns: 1fr; }
  .checkout-summary { position: static; }
  .form-row { grid-template-columns: 1fr; }
}
</style>

<div class="checkout-wrap">
  <div class="checkout-inner">

    <!-- Stepper -->
    <div class="stepper">
      <div class="step <?php echo $step==='bag'?'active':($step==='address'||$step==='payment'?'completed':''); ?>">
        <div class="step-num">1</div>BAG
      </div>
      <div class="step-line"></div>
      <div class="step <?php echo $step==='address'?'active':($step==='payment'?'completed':''); ?>">
        <div class="step-num">2</div>ADDRESS
      </div>
      <div class="step-line"></div>
      <div class="step <?php echo $step==='payment'?'active':''; ?>">
        <div class="step-num">3</div>PAYMENT
      </div>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="checkout-grid">
      <div class="checkout-content">

        <?php if ($step === 'bag'): ?>
        <!-- STEP 1: BAG -->
        <h2 class="section-heading"><?php echo $item_count; ?> Item<?php echo $item_count>1?'s':''; ?> in your bag</h2>
        
        <?php foreach ($cart as $item): 
          $prod_q = mysqli_query($conn, "SELECT * FROM products WHERE id = " . (int)$item['product_id']);
          $prod = mysqli_fetch_assoc($prod_q);
          $img_url = !empty($prod['image_main']) ? url('assets/images/products/' . $prod['image_main']) : url('assets/images/placeholder.svg');
          
          // Get variant details if variant_id exists
          $variant_text = '';
          if (!empty($item['variant_id'])) {
              $variant_q = mysqli_query($conn, "SELECT size, color, material FROM product_variants WHERE id = " . (int)$item['variant_id']);
              $variant = mysqli_fetch_assoc($variant_q);
              if ($variant) {
                  $variant_parts = [];
                  if ($variant['size']) $variant_parts[] = $variant['size'];
                  if ($variant['color']) $variant_parts[] = $variant['color'];
                  if ($variant['material']) $variant_parts[] = $variant['material'];
                  $variant_text = implode(' / ', $variant_parts);
              }
          }
        ?>
        <div class="bag-item">
          <div class="bag-img"><img src="<?php echo $img_url; ?>" alt=""></div>
          <div class="bag-info">
            <h4><?php echo htmlspecialchars($prod['name']); ?></h4>
            <?php if ($variant_text): ?>
              <div class="variant"><?php echo htmlspecialchars($variant_text); ?></div>
            <?php endif; ?>
            <div class="price"><?php echo format_price($prod['price']); ?></div>
          </div>
          <div class="bag-qty">
            <span style="color: rgba(245, 241, 232, 0.7); font-size: 12px;">Qty:</span>
            <span><?php echo $item['quantity']; ?></span>
          </div>
        </div>
        <?php endforeach; ?>
        
        <div style="margin-top: 32px;">
          <?php if ($is_logged_in): ?>
            <a href="?step=address" class="btn-primary">Continue to Address</a>
          <?php else: ?>
            <a href="<?php echo url('login.php?redirect=checkout'); ?>" class="btn-primary">Login to Continue</a>
            <p style="text-align:center; margin-top:14px; font-size:13px; color:var(--smoke);">
              You need to login to place an order
            </p>
          <?php endif; ?>
        </div>

        <?php elseif ($step === 'address'): ?>
        <!-- STEP 2: ADDRESS -->
        <h2 class="section-heading">Select Delivery Address</h2>
        
        <?php if (isset($_GET['add'])): ?>
        <!-- Add new address form -->
        <form method="POST" class="addr-form">
          <h3 style="color:var(--bone); font-size:15px; margin-bottom:18px;">Add New Address</h3>
          
          <div class="form-row">
            <div class="fg">
              <label>Full Name *</label>
              <input type="text" name="full_name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
            </div>
            <div class="fg">
              <label>Phone *</label>
              <input type="tel" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" required>
            </div>
          </div>
          
          <div class="form-row full">
            <div class="fg">
              <label>Address (House No, Street, Area) *</label>
              <textarea name="address_line" rows="3" required></textarea>
            </div>
          </div>
          
          <div class="form-row">
            <div class="fg">
              <label>City *</label>
              <input type="text" name="city" required>
            </div>
            <div class="fg">
              <label>Pincode *</label>
              <input type="text" name="pincode" maxlength="6" required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="fg">
              <label>State *</label>
              <input type="text" name="state" required>
            </div>
            <div class="fg">
              <label>Address Type</label>
              <select name="address_type">
                <option value="home">Home</option>
                <option value="work">Work</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>
          
          <div class="checkbox-row">
            <input type="checkbox" name="is_default" id="is_default">
            <label for="is_default">Make this my default address</label>
          </div>
          
          <div style="display:flex; gap:12px;">
            <button type="submit" name="add_address" class="btn-primary" style="flex:1;">Save Address</button>
            <a href="?step=address" class="btn-secondary">Cancel</a>
          </div>
        </form>
        
        <?php else: ?>
        
        <?php if (count($addresses) > 0): ?>
        <form method="POST" action="?step=payment" id="addr-form">
          <div class="address-grid">
            <?php foreach ($addresses as $i => $a): ?>
            <label class="addr-card">
              <input type="radio" name="selected_address" value="<?php echo $a['id']; ?>" <?php echo $a['is_default']||$i===0?'checked':''; ?> required>
              <div class="addr-content">
                <div class="addr-name">
                  <strong><?php echo htmlspecialchars($a['full_name']); ?></strong>
                  <span class="addr-badge"><?php echo strtoupper($a['address_type']); ?></span>
                  <?php if ($a['is_default']): ?><span class="addr-badge" style="background:rgba(16,185,129,0.1);color:#10B981;">DEFAULT</span><?php endif; ?>
                </div>
                <div class="addr-text">
                  <?php echo htmlspecialchars($a['address_line']); ?><br>
                  <?php echo htmlspecialchars($a['city']); ?>, <?php echo htmlspecialchars($a['state']); ?> - <?php echo htmlspecialchars($a['pincode']); ?>
                </div>
                <div class="addr-phone">📞 <?php echo htmlspecialchars($a['phone']); ?></div>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </form>
        <?php else: ?>
          <div class="empty-msg">
            <p>No saved addresses yet</p>
          </div>
        <?php endif; ?>
        
        <a href="?step=address&add=1" class="btn-add-addr">+ Add New Address</a>
        
        <?php if (count($addresses) > 0): ?>
        <div style="margin-top: 24px; display:flex; gap:12px;">
          <a href="?step=bag" class="btn-secondary">Back to Bag</a>
          <button onclick="proceedToPayment()" class="btn-primary" style="flex:1;">Continue to Payment</button>
        </div>
        <script>
          function proceedToPayment() {
            const selected = document.querySelector('input[name="selected_address"]:checked');
            if (!selected) { alert('Please select an address'); return; }
            window.location.href = '?step=payment&addr=' + selected.value;
          }
        </script>
        <?php endif; ?>
        
        <?php endif; ?>

        <?php elseif ($step === 'payment'): ?>
        <!-- STEP 3: PAYMENT -->
        <?php
        $selected_addr_id = isset($_GET['addr']) ? (int)$_GET['addr'] : 0;
        if (!$selected_addr_id) {
            header('Location: ?step=address');
            exit;
        }
        $sel_q = mysqli_query($conn, "SELECT * FROM customer_addresses WHERE id = $selected_addr_id AND customer_id = $cid");
        $sel_addr = mysqli_fetch_assoc($sel_q);
        if (!$sel_addr) {
            header('Location: ?step=address');
            exit;
        }
        ?>
        
        <h2 class="section-heading">Choose Payment Method</h2>
        
        <!-- Selected address summary -->
        <div style="background:var(--ink); border-radius:8px; padding:16px 18px; margin-bottom:24px; border:1px solid var(--line-subtle);">
          <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">
            <strong style="color:var(--bone); font-size:13px;">Deliver to: <?php echo htmlspecialchars($sel_addr['full_name']); ?></strong>
            <a href="?step=address" style="color:var(--gold); font-size:12px; text-decoration:none;">CHANGE</a>
          </div>
          <div style="color:var(--smoke); font-size:13px;">
            <?php echo htmlspecialchars($sel_addr['address_line']); ?>, <?php echo htmlspecialchars($sel_addr['city']); ?>, <?php echo htmlspecialchars($sel_addr['state']); ?> - <?php echo htmlspecialchars($sel_addr['pincode']); ?>
          </div>
        </div>
        
        <form method="POST">
          <input type="hidden" name="address_id" value="<?php echo $selected_addr_id; ?>">
          
          <div class="payment-list">
            <?php if (get_setting('cod_enabled')): ?>
            <label class="pay-option">
              <input type="radio" name="payment_method" value="cod" checked>
              <div class="pay-info">
                <h5>Cash on Delivery</h5>
                <p>Pay when you receive your order</p>
              </div>
            </label>
            <?php endif; ?>
            
            <?php if (get_setting('razorpay_enabled')): ?>
            <label class="pay-option">
              <input type="radio" name="payment_method" value="razorpay">
              <div class="pay-info">
                <h5>Pay Online</h5>
                <p>UPI, Credit/Debit Card, Net Banking</p>
              </div>
            </label>
            <?php endif; ?>
          </div>
          
          <div style="display:flex; gap:12px;">
            <a href="?step=address" class="btn-secondary">Back</a>
            <button type="submit" name="place_order" class="btn-primary" style="flex:1;">
              Place Order · <?php echo format_price($total); ?>
            </button>
          </div>
        </form>

        <?php endif; ?>
      </div>

      <!-- Summary -->
      <div class="checkout-summary">
        <h3 class="sum-title">Price Details (<?php echo $item_count; ?> Item<?php echo $item_count>1?'s':''; ?>)</h3>
        
        <div class="sum-items">
          <?php foreach ($cart as $item): 
            $prod_q = mysqli_query($conn, "SELECT name, price FROM products WHERE id = " . (int)$item['product_id']);
            $prod = mysqli_fetch_assoc($prod_q);
          ?>
          <div class="sum-item">
            <span><?php echo htmlspecialchars($prod['name']); ?> × <?php echo $item['quantity']; ?></span>
            <span><?php echo format_price($prod['price'] * $item['quantity']); ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        
        <div class="sum-row">
          <span>Subtotal</span>
          <span><?php echo format_price($subtotal); ?></span>
        </div>
        <div class="sum-row">
          <span>Shipping</span>
          <span><?php echo $shipping_fee == 0 ? 'FREE' : format_price($shipping_fee); ?></span>
        </div>
        
        <div class="sum-total">
          <span>Total</span>
          <span><?php echo format_price($total); ?></span>
        </div>
        
        <?php if ($shipping_fee > 0): ?>
        <p style="font-size:12px; color:var(--smoke); text-align:center; padding:10px; background:var(--ink); border-radius:6px;">
          Add <?php echo format_price($shipping_threshold - $subtotal); ?> more for FREE shipping
        </p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
