<?php
require_once '../config/config.php';
require_once '../includes/admin_auth.php';

// Logout admin
$adminAuth = getAdminAuth();
$result = $adminAuth->logout();

// Redirect to login page
header('Location: login.php?logout=1');
exit();
?>
