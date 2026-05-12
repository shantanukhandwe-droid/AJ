<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM admin_users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) === 1) {
        $admin = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — Reverie</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #0A0E14 0%, #1F2937 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.login-container {
    background: #1F2937;
    border: 1px solid rgba(212, 165, 116, 0.2);
    border-radius: 12px;
    padding: 48px 40px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.login-logo {
    text-align: center;
    margin-bottom: 32px;
}

.login-logo h1 {
    font-family: 'Italiana', serif;
    font-size: 32px;
    letter-spacing: 0.4em;
    color: #D4A574;
    font-weight: 400;
    margin-bottom: 8px;
}

.login-logo p {
    font-size: 11px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #9CA3AF;
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-size: 13px;
    color: #F5F1E8;
    margin-bottom: 8px;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 14px 16px;
    background: #0A0E14;
    border: 1px solid rgba(212, 165, 116, 0.3);
    border-radius: 6px;
    color: #F5F1E8;
    font-size: 15px;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #D4A574;
}

.error-message {
    background: rgba(220, 38, 38, 0.1);
    border: 1px solid rgba(220, 38, 38, 0.3);
    color: #FCA5A5;
    padding: 12px 16px;
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 24px;
}

.btn-login {
    width: 100%;
    padding: 16px;
    background: #D4A574;
    color: #0A0E14;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-login:hover {
    background: #E8C89A;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(212, 165, 116, 0.3);
}

.login-footer {
    margin-top: 24px;
    text-align: center;
    font-size: 12px;
    color: #9CA3AF;
}

.login-footer a {
    color: #D4A574;
    text-decoration: none;
}

.login-footer a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>

<div class="login-container">
    <div class="login-logo">
        <h1>REVERIE</h1>
        <p>Admin Panel</p>
    </div>

    <?php if ($error): ?>
    <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required autofocus>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn-login">Sign in</button>
    </form>

    <div class="login-footer">
        <a href="<?php echo url(); ?>">← Back to website</a>
    </div>
</div>

</body>
</html>
