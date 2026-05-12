<?php
// Site configuration
define('BASE_PATH', '/reverie'); // Change to '/' when deploying to root domain

// Helper function for URLs
function url($path = '') {
    return BASE_PATH . ($path ? '/' . ltrim($path, '/') : '');
}
?>
