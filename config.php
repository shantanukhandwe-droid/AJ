<?php
// Site configuration
define('BASE_PATH', ''); // Empty for Railway root domain

// Helper function for URLs
function url($path = '') {
    return BASE_PATH . ($path ? '/' . ltrim($path, '/') : '');
}
?>
