<?php 
require_once 'includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['customer_id'])) {
    header('Location: ' . url('account.php'));
    exit;
}

$error = '';
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'login';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM customers WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) === 1) {
        $customer = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $customer['password'])) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            $_SESSION['customer_email'] = $customer['email'];
            
            header('Location: ' . url('shop.php'));
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    } else {
        $error = 'Invalid email or password';
    }
}

// Handle signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $check_query = "SELECT id FROM customers WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = 'Email already registered. Please login instead.';
        $active_tab = 'login';
    } else {
        $insert_query = "INSERT INTO customers (name, email, phone, password) 
                        VALUES ('$name', '$email', '$phone', '$password')";
        
        if (mysqli_query($conn, $insert_query)) {
            $customer_id = mysqli_insert_id($conn);
            
            $_SESSION['customer_id'] = $customer_id;
            $_SESSION['customer_name'] = $name;
            $_SESSION['customer_email'] = $email;
            
            header('Location: ' . url('shop.php'));
            exit;
        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
}

$page_title = 'Login';
include 'includes/header.php';
?>

<div class="auth-page">
  <div class="auth-container">
    <h1 class="auth-title">Welcome</h1>
    
    <?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="auth-tabs">
      <button class="tab-btn <?php echo $active_tab === 'login' ? 'active' : ''; ?>" onclick="switchTab('login')">Login</button>
      <button class="tab-btn <?php echo $active_tab === 'signup' ? 'active' : ''; ?>" onclick="switchTab('signup')">Sign Up</button>
    </div>

    <!-- Login Tab -->
    <div class="tab-content <?php echo $active_tab === 'login' ? 'active' : ''; ?>" id="login-tab">
      <form method="POST" class="auth-form">
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" required placeholder="your@email.com">
        </div>
        
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required placeholder="Enter your password">
        </div>

        <button type="submit" name="login" class="btn-primary">Login</button>
        
        <p class="form-footer">
          Don't have an account? <a href="#" onclick="switchTab('signup'); return false;">Sign up</a>
        </p>
      </form>
    </div>

    <!-- Signup Tab -->
    <div class="tab-content <?php echo $active_tab === 'signup' ? 'active' : ''; ?>" id="signup-tab">
      <form method="POST" class="auth-form">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" required placeholder="John Doe">
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" required placeholder="your@email.com">
        </div>

        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" name="phone" required placeholder="9876543210">
        </div>
        
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required placeholder="Create a password">
        </div>

        <button type="submit" name="signup" class="btn-primary">Create Account</button>
        
        <p class="form-footer">
          Already have an account? <a href="#" onclick="switchTab('login'); return false;">Login</a>
        </p>
      </form>
    </div>
  </div>
</div>

<style>
.alert {
  padding: 14px 18px;
  border-radius: 6px;
  margin-bottom: 24px;
  font-size: 14px;
}

.alert-error {
  background: rgba(220, 38, 38, 0.1);
  border: 1px solid rgba(220, 38, 38, 0.3);
  color: #FCA5A5;
}

.auth-page {
  background: var(--ink);
  min-height: 100vh;
  padding: 80px 60px 120px;
}

.auth-container {
  max-width: 480px;
  margin: 0 auto;
}

.auth-title {
  font-family: 'Italiana', serif;
  font-size: 48px;
  color: #F5F1E8;
  margin-bottom: 32px;
  text-align: center;
}

.auth-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 32px;
  background: var(--charcoal);
  padding: 6px;
  border-radius: 8px;
}

.tab-btn {
  flex: 1;
  padding: 12px 16px;
  background: transparent;
  border: none;
  color: rgba(245, 241, 232, 0.7);
  font-size: 13px;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.3s;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
}

.tab-btn:hover {
  color: #F5F1E8;
}

.tab-btn.active {
  background: var(--gold);
  color: var(--ink);
}

.tab-content {
  display: none;
  background: var(--charcoal);
  padding: 40px 36px;
  border-radius: 8px;
  border: 1px solid var(--line-subtle);
}

.tab-content.active {
  display: block;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.auth-form .form-group {
  display: flex;
  flex-direction: column;
}

.auth-form label {
  font-size: 13px;
  color: #F5F1E8;
  margin-bottom: 8px;
  letter-spacing: 0.05em;
  font-weight: 500;
}

.auth-form input {
  background: var(--ink);
  border: 1px solid var(--line-warm);
  color: #F5F1E8;
  padding: 14px 16px;
  font-size: 15px;
  border-radius: 6px;
  font-family: 'Inter', sans-serif;
  transition: border-color 0.3s;
}

.auth-form input:focus {
  outline: none;
  border-color: #D4A574;
}

.auth-form input::placeholder {
  color: rgba(245, 241, 232, 0.7);
  opacity: 0.6;
}

.form-footer {
  text-align: center;
  font-size: 13px;
  color: rgba(245, 241, 232, 0.7);
  margin-top: 8px;
}

.form-footer a {
  color: #D4A574;
  text-decoration: none;
  font-weight: 500;
}

.form-footer a:hover {
  text-decoration: underline;
}

@media (max-width: 640px) {
  .auth-page { padding: 60px 30px 100px; }
  .tab-content { padding: 32px 24px; }
}
</style>

<script>
function switchTab(tab) {
  const url = new URL(window.location);
  url.searchParams.set('tab', tab);
  window.history.pushState({}, '', url);
  
  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
  
  document.querySelector('.tab-btn:nth-child(' + (tab === 'login' ? '1' : '2') + ')').classList.add('active');
  document.getElementById(tab + '-tab').classList.add('active');
}
</script>

<?php include 'includes/footer.php'; ?>
