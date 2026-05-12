<?php
// Reset Admin Password Script
// Place this in /reverie/ folder and visit: http://localhost/reverie/reset-admin.php

require_once 'includes/db.php';

$new_password = 'admin123';
$hashed = password_hash($new_password, PASSWORD_DEFAULT);

echo "<h2>Password Reset Tool</h2>";
echo "<p>New password: <strong>$new_password</strong></p>";
echo "<p>Hash generated: <code>$hashed</code></p>";

// Update the admin user
$query = "UPDATE admin_users SET password = '$hashed' WHERE username = 'admin'";
if (mysqli_query($conn, $query)) {
    echo "<p style='color: green; font-weight: bold;'>✓ Admin password updated successfully!</p>";
    echo "<p>You can now login with:</p>";
    echo "<ul>";
    echo "<li>Username: <strong>admin</strong></li>";
    echo "<li>Password: <strong>admin123</strong></li>";
    echo "</ul>";
    echo "<p><a href='admin/login.php'>Go to Admin Login →</a></p>";
} else {
    echo "<p style='color: red;'>✗ Error: " . mysqli_error($conn) . "</p>";
}

echo "<hr>";
echo "<p style='color: red;'><strong>IMPORTANT:</strong> Delete this file after use for security!</p>";
?>
