<?php 
$page_title = 'Order Confirmed';
include 'includes/header.php';

$order_number = isset($_GET['order']) ? clean_input($_GET['order']) : '';

if (!$order_number) {
    header('Location: ' . url('index.php'));
    exit;
}

// Fetch order details
$order_query = "SELECT * FROM orders WHERE order_number = '$order_number'";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) === 0) {
    header('Location: ' . url('index.php'));
    exit;
}

$order = mysqli_fetch_assoc($order_result);
?>

<div class="success-page">
  <div class="success-container">
    <div class="success-icon">
      <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>

    <h1 class="success-title">Order confirmed</h1>
    <p class="success-subtitle">Thank you for your order!</p>

    <div class="order-details">
      <div class="order-detail-row">
        <span class="order-detail-label">Order number</span>
        <span class="order-detail-value"><?php echo $order['order_number']; ?></span>
      </div>
      <div class="order-detail-row">
        <span class="order-detail-label">Order total</span>
        <span class="order-detail-value"><?php echo format_price($order['total']); ?></span>
      </div>
      <div class="order-detail-row">
        <span class="order-detail-label">Payment method</span>
        <span class="order-detail-value"><?php echo $order['payment_method'] === 'cod' ? 'Cash on delivery' : 'Razorpay'; ?></span>
      </div>
    </div>

    <div class="success-message">
      <p>We've sent an order confirmation to <strong><?php echo $order['customer_email']; ?></strong></p>
      <p>Your order will be processed within 24 hours. We'll notify you when it ships.</p>
    </div>

    <div class="success-actions">
      <a href="<?php echo url('shop.php'); ?>" class="btn-secondary">Continue shopping</a>
      <a href="https://wa.me/<?php echo get_setting('whatsapp_number'); ?>?text=Hi!%20My%20order%20number%20is%20<?php echo $order['order_number']; ?>" 
         target="_blank" 
         class="btn-whatsapp">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
        Contact us
      </a>
    </div>
  </div>
</div>

<style>
.success-page {
  background: var(--ink);
  min-height: 100vh;
  padding: 100px 60px 120px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.success-container {
  max-width: 600px;
  text-align: center;
}

.success-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 32px;
  color: #D4A574;
}

.success-title {
  font-family: 'Italiana', serif;
  font-size: 56px;
  color: #F5F1E8;
  margin-bottom: 8px;
}

.success-subtitle {
  font-size: 18px;
  color: rgba(245, 241, 232, 0.7);
  margin-bottom: 48px;
}

.order-details {
  background: var(--charcoal);
  border-radius: 8px;
  padding: 32px;
  margin-bottom: 32px;
  border: 1px solid var(--line-subtle);
}

.order-detail-row {
  display: flex;
  justify-content: space-between;
  padding: 14px 0;
  border-bottom: 1px solid var(--line-subtle);
}

.order-detail-row:last-child {
  border-bottom: none;
}

.order-detail-label {
  font-size: 13px;
  color: rgba(245, 241, 232, 0.7);
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.order-detail-value {
  font-size: 15px;
  color: #F5F1E8;
  font-weight: 500;
}

.success-message {
  margin-bottom: 40px;
}

.success-message p {
  color: #F5F1E8;
  font-size: 15px;
  line-height: 1.6;
  margin-bottom: 8px;
  opacity: 0.8;
}

.success-message strong {
  color: #D4A574;
  font-weight: 500;
}

.success-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-secondary {
  padding: 16px 32px;
  background: transparent;
  border: 1px solid var(--gold);
  color: #D4A574;
  text-decoration: none;
  font-size: 12px;
  letter-spacing: 0.25em;
  text-transform: uppercase;
  font-weight: 500;
  transition: all 0.3s;
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-secondary:hover {
  background: var(--gold);
  color: var(--ink);
}

.btn-whatsapp {
  padding: 16px 32px;
  background: #25D366;
  color: white;
  text-decoration: none;
  font-size: 12px;
  letter-spacing: 0.25em;
  text-transform: uppercase;
  font-weight: 500;
  transition: all 0.3s;
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-whatsapp:hover {
  background: #20BA5A;
  transform: translateY(-2px);
}

@media (max-width: 640px) {
  .success-page { padding: 80px 30px 100px; }
  .success-title { font-size: 40px; }
  .order-details { padding: 24px 20px; }
  .success-actions {
    flex-direction: column;
  }
  .btn-secondary, .btn-whatsapp {
    width: 100%;
    justify-content: center;
  }
}
</style>

<?php include 'includes/footer.php'; ?>
