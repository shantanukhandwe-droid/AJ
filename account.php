<?php 
require_once 'includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . url('login.php'));
    exit;
}

$customer_id = $_SESSION['customer_id'];
$success = '';
$error = '';

// Handle logout
if (isset($_GET['logout'])) {
    unset($_SESSION['customer_id']);
    unset($_SESSION['customer_name']);
    unset($_SESSION['customer_email']);
    header('Location: ' . url('login.php'));
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = clean_input($_POST['name']);
    $phone = clean_input($_POST['phone']);
    
    $update_query = "UPDATE customers SET name = '$name', phone = '$phone' WHERE id = $customer_id";
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['customer_name'] = $name;
        $success = 'Profile updated successfully!';
    } else {
        $error = 'Failed to update profile.';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $check_query = "SELECT password FROM customers WHERE id = $customer_id";
    $check_result = mysqli_query($conn, $check_query);
    $customer_data = mysqli_fetch_assoc($check_result);
    
    if (password_verify($current_password, $customer_data['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pwd = "UPDATE customers SET password = '$hashed' WHERE id = $customer_id";
                if (mysqli_query($conn, $update_pwd)) {
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'Failed to change password.';
                }
            } else {
                $error = 'Password must be at least 6 characters.';
            }
        } else {
            $error = 'New passwords do not match.';
        }
    } else {
        $error = 'Current password is incorrect.';
    }
}

// Get customer info
$customer_query = "SELECT * FROM customers WHERE id = $customer_id";
$customer_result = mysqli_query($conn, $customer_query);
$customer = mysqli_fetch_assoc($customer_result);

// Get customer's orders by email (works for both logged-in and guest orders)
$customer_email = mysqli_real_escape_string($conn, $customer['email']);
$orders_query = "SELECT * FROM orders 
                 WHERE customer_email = '$customer_email' 
                 ORDER BY created_at DESC";
$orders = mysqli_query($conn, $orders_query);

if (!$orders) {
    die("Query error: " . mysqli_error($conn));
}

$order_count = mysqli_num_rows($orders);

// Calculate total spent
$spent_query = "SELECT SUM(total) as total_spent FROM orders 
                WHERE customer_email = '$customer_email'";
$spent_result = mysqli_query($conn, $spent_query);
$total_spent = mysqli_fetch_assoc($spent_result)['total_spent'] ?? 0;

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

$page_title = 'My Account';
include 'includes/header.php';
?>

<style>
.alert {
  padding: 14px 18px;
  border-radius: 6px;
  margin-bottom: 24px;
  font-size: 14px;
}

.alert-success {
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #6EE7B7;
}

.alert-error {
  background: rgba(220, 38, 38, 0.1);
  border: 1px solid rgba(220, 38, 38, 0.3);
  color: #FCA5A5;
}

.account-page-new {
  background: var(--ink);
  min-height: 100vh;
  padding: 60px 60px 120px;
}

.account-container-new {
  max-width: 1400px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 40px;
}

.account-sidebar-new {
  position: sticky;
  top: 100px;
  align-self: start;
}

.account-user-info {
  background: var(--charcoal);
  border: 1px solid var(--line-subtle);
  border-radius: 8px;
  padding: 24px;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--gold);
  color: var(--ink);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: 600;
  flex-shrink: 0;
}

.user-details h3 {
  font-size: 16px;
  color: #F5F1E8;
  margin-bottom: 4px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
}

.user-details p {
  font-size: 13px;
  color: rgba(245, 241, 232, 0.7);
}

.account-nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  color: rgba(245, 241, 232, 0.7);
  text-decoration: none;
  border-radius: 6px;
  font-size: 14px;
  transition: all 0.2s;
}

.nav-item:hover {
  background: rgba(212, 165, 116, 0.1);
  color: #D4A574;
}

.nav-item.active {
  background: rgba(212, 165, 116, 0.15);
  color: #D4A574;
  font-weight: 500;
}

.nav-logout {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid var(--line-subtle);
  color: #EF4444 !important;
}

.nav-logout:hover {
  background: rgba(239, 68, 68, 0.1) !important;
  color: #EF4444 !important;
}

.account-main-new {
  min-height: 500px;
}

.page-title {
  font-family: 'Italiana', serif;
  font-size: 42px;
  color: #F5F1E8;
  margin-bottom: 32px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: var(--charcoal);
  border: 1px solid var(--line-subtle);
  border-radius: 8px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-value {
  font-size: 32px;
  font-weight: 600;
  color: #F5F1E8;
  line-height: 1;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 13px;
  color: rgba(245, 241, 232, 0.7);
}

.section-card {
  background: var(--charcoal);
  border: 1px solid var(--line-subtle);
  border-radius: 8px;
  padding: 32px;
}

.section-title {
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
  font-size: 20px;
  color: #F5F1E8;
  margin-bottom: 24px;
  font-weight: 600;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.order-item {
  background: var(--ink);
  border: 1px solid var(--line-subtle);
  border-radius: 6px;
  padding: 20px;
}

.order-item-detailed {
  background: var(--charcoal);
  border: 1px solid var(--line-subtle);
  border-radius: 8px;
  padding: 24px;
  margin-bottom: 16px;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--line-subtle);
}

.order-header-row {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 12px;
}

.order-number {
  font-size: 14px;
  font-weight: 600;
  color: #F5F1E8;
  margin-bottom: 4px;
}

.order-date {
  font-size: 12px;
  color: rgba(245, 241, 232, 0.7);
}

.order-amount {
  font-size: 18px;
  font-weight: 600;
  color: #D4A574;
}

.order-footer-row {
  display: flex;
  gap: 12px;
  font-size: 11px;
}

.order-status {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.status-pending { background: rgba(251, 191, 36, 0.15); color: #FCD34D; }
.status-confirmed { background: rgba(59, 130, 246, 0.15); color: #60A5FA; }
.status-shipped { background: rgba(168, 85, 247, 0.15); color: #C084FC; }
.status-delivered { background: rgba(16, 185, 129, 0.15); color: #6EE7B7; }
.status-cancelled { background: rgba(239, 68, 68, 0.15); color: #FCA5A5; }

.order-payment {
  padding: 4px 10px;
  background: rgba(212, 165, 116, 0.1);
  color: #D4A574;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
}

.order-details {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.order-detail-row {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  color: rgba(245, 241, 232, 0.7);
}

.order-detail-row strong {
  color: #F5F1E8;
  font-weight: 600;
}

.btn-view-all {
  display: block;
  text-align: center;
  margin-top: 20px;
  padding: 12px;
  background: transparent;
  border: 1px solid var(--line-warm);
  color: #F5F1E8;
  text-decoration: none;
  border-radius: 6px;
  font-size: 13px;
  transition: all 0.2s;
}

.btn-view-all:hover {
  border-color: #D4A574;
  color: #D4A574;
}

.empty-state,
.empty-state-small {
  text-align: center;
  padding: 60px 20px;
}

.empty-state svg {
  margin: 0 auto 24px;
  opacity: 0.3;
  color: rgba(245, 241, 232, 0.7);
}

.empty-state h3 {
  font-size: 20px;
  color: #F5F1E8;
  margin-bottom: 8px;
}

.empty-state p,
.empty-state-small p {
  color: rgba(245, 241, 232, 0.7);
  font-size: 14px;
  margin-bottom: 20px;
}

.profile-form {
  max-width: 500px;
}

.form-group-new {
  margin-bottom: 24px;
}

.form-group-new label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: #F5F1E8;
  margin-bottom: 8px;
}

.form-group-new input {
  width: 100%;
  padding: 12px 16px;
  background: var(--charcoal);
  border: 1px solid var(--line-warm);
  border-radius: 6px;
  color: #F5F1E8;
  font-size: 15px;
  font-family: 'Inter', sans-serif;
  transition: border-color 0.3s;
}

.form-group-new input:focus {
  outline: none;
  border-color: #D4A574;
}

.form-group-new input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.form-group-new small {
  display: block;
  margin-top: 6px;
  font-size: 12px;
  color: rgba(245, 241, 232, 0.7);
}

@media (max-width: 1024px) {
  .account-page-new { padding: 40px 30px 100px; }
  .account-container-new {
    grid-template-columns: 1fr;
    gap: 32px;
  }
  .account-sidebar-new {
    position: static;
  }
}
</style>

<div class="account-page-new">
  <div class="account-container-new">
    
    <div class="account-sidebar-new">
      <div class="account-user-info">
        <div class="user-avatar"><?php echo strtoupper(substr($customer['name'], 0, 1)); ?></div>
        <div class="user-details">
          <h3><?php echo htmlspecialchars($customer['name']); ?></h3>
          <p><?php echo htmlspecialchars($customer['email']); ?></p>
        </div>
      </div>
      
      <nav class="account-nav">
        <a href="?tab=overview" class="nav-item <?php echo $active_tab === 'overview' ? 'active' : ''; ?>">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
          </svg>
          Overview
        </a>
        <a href="?tab=orders" class="nav-item <?php echo $active_tab === 'orders' ? 'active' : ''; ?>">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
          </svg>
          My Orders
        </a>
        <a href="?tab=profile" class="nav-item <?php echo $active_tab === 'profile' ? 'active' : ''; ?>">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          Edit Profile
        </a>
        <a href="?tab=password" class="nav-item <?php echo $active_tab === 'password' ? 'active' : ''; ?>">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0110 0v4"/>
          </svg>
          Change Password
        </a>
        <a href="?logout=1" class="nav-item nav-logout">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
          </svg>
          Logout
        </a>
      </nav>
    </div>

    <div class="account-main-new">
      <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
      <?php endif; ?>
      
      <?php if ($error): ?>
      <div class="alert alert-error"><?php echo $error; ?></div>
      <?php endif; ?>

      <?php if ($active_tab === 'overview'): ?>
      <div class="tab-content-new">
        <h1 class="page-title">Account Overview</h1>
        
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon" style="background: rgba(212, 165, 116, 0.1); color: #D4A574;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
              </svg>
            </div>
            <div>
              <div class="stat-value"><?php echo $order_count; ?></div>
              <div class="stat-label">Total Orders</div>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
              </svg>
            </div>
            <div>
              <div class="stat-value"><?php echo format_price($total_spent); ?></div>
              <div class="stat-label">Total Spent</div>
            </div>
          </div>
        </div>

        <div class="section-card">
          <h2 class="section-title">Recent Orders</h2>
          <?php if ($order_count > 0): ?>
            <div class="orders-list">
              <?php 
              mysqli_data_seek($orders, 0);
              $count = 0;
              while ($order = mysqli_fetch_assoc($orders) AND $count < 5): 
                $count++;
              ?>
              <div class="order-item">
                <div class="order-header-row">
                  <div>
                    <div class="order-number"><?php echo $order['order_number']; ?></div>
                    <div class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                  </div>
                  <div class="order-amount"><?php echo format_price($order['total']); ?></div>
                </div>
                <div class="order-footer-row">
                  <span class="order-status status-<?php echo $order['order_status']; ?>">
                    <?php echo ucfirst($order['order_status']); ?>
                  </span>
                  <span class="order-payment"><?php echo strtoupper($order['payment_method']); ?></span>
                </div>
              </div>
              <?php endwhile; ?>
            </div>
            <?php if ($order_count > 5): ?>
            <a href="?tab=orders" class="btn-view-all">View All Orders</a>
            <?php endif; ?>
          <?php else: ?>
            <div class="empty-state-small">
              <p>No orders yet</p>
              <a href="<?php echo url('shop.php'); ?>" class="btn-primary">Start Shopping</a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <?php elseif ($active_tab === 'orders'): ?>
      <div class="tab-content-new">
        <h1 class="page-title">My Orders</h1>
        
        <?php if ($order_count > 0): ?>
          <div class="orders-list">
            <?php 
            mysqli_data_seek($orders, 0);
            while ($order = mysqli_fetch_assoc($orders)): 
            ?>
            <div class="order-item-detailed">
              <div class="order-header">
                <div>
                  <div class="order-number"><?php echo $order['order_number']; ?></div>
                  <div class="order-date">Placed on <?php echo date('M d, Y \a\t H:i', strtotime($order['created_at'])); ?></div>
                </div>
                <div class="order-status status-<?php echo $order['order_status']; ?>">
                  <?php echo ucfirst($order['order_status']); ?>
                </div>
              </div>
              <div class="order-details">
                <div class="order-detail-row">
                  <span>Total Amount:</span>
                  <strong><?php echo format_price($order['total']); ?></strong>
                </div>
                <div class="order-detail-row">
                  <span>Payment:</span>
                  <span><?php echo strtoupper($order['payment_method']); ?></span>
                </div>
                <div class="order-detail-row">
                  <span>Delivery Address:</span>
                  <span><?php echo htmlspecialchars($order['customer_address']); ?>, <?php echo htmlspecialchars($order['customer_city']); ?></span>
                </div>
              </div>
            </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
              <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <h3>No Orders Yet</h3>
            <p>Start shopping to see your orders here</p>
            <a href="<?php echo url('shop.php'); ?>" class="btn-primary">Browse Products</a>
          </div>
        <?php endif; ?>
      </div>

      <?php elseif ($active_tab === 'profile'): ?>
      <div class="tab-content-new">
        <h1 class="page-title">Edit Profile</h1>
        
        <form method="POST" class="profile-form">
          <div class="form-group-new">
            <label>Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
          </div>
          
          <div class="form-group-new">
            <label>Email Address</label>
            <input type="email" value="<?php echo htmlspecialchars($customer['email']); ?>" disabled>
            <small>Email cannot be changed</small>
          </div>
          
          <div class="form-group-new">
            <label>Phone Number</label>
            <input type="tel" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>">
          </div>
          
          <button type="submit" name="update_profile" class="btn-primary">Save Changes</button>
        </form>
      </div>

      <?php elseif ($active_tab === 'password'): ?>
      <div class="tab-content-new">
        <h1 class="page-title">Change Password</h1>
        
        <form method="POST" class="profile-form">
          <div class="form-group-new">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
          </div>
          
          <div class="form-group-new">
            <label>New Password</label>
            <input type="password" name="new_password" required minlength="6">
            <small>Must be at least 6 characters</small>
          </div>
          
          <div class="form-group-new">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required>
          </div>
          
          <button type="submit" name="change_password" class="btn-primary">Change Password</button>
        </form>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
