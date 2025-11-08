<?php
/**
 * Logout API Endpoint
 * Handles user logout
 */

require_once dirname(__DIR__) . '/includes/auth.php';

try {
    $auth = new Auth();
    $result = $auth->logout();
    
    // Redirect to home page
    header('Location: ' . SITE_URL . '/index.php?logout=1');
    exit();
    
} catch (Exception $e) {
    error_log("Logout API error: " . $e->getMessage());
    header('Location: ' . SITE_URL . '/index.php');
    exit();
}
?>
