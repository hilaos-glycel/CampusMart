<?php
/**
 * Login API Endpoint
 * Handles user authentication
 */

header('Content-Type: application/json');
require_once dirname(__DIR__) . '/includes/auth.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }
    
    // Validate required fields
    if (empty($_POST['loginId']) || empty($_POST['password'])) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit();
    }
    
    $loginId = sanitizeInput($_POST['loginId']);
    $password = $_POST['password'];
    
    // Attempt login
    $auth = new Auth();
    $result = $auth->login($loginId, $password);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Login API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
