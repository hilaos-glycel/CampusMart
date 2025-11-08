<?php
/**
 * Update User Profile API Endpoint
 * Updates user profile information
 */

header('Content-Type: application/json');
require_once dirname(__DIR__) . '/includes/auth.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

try {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }
    
    $userId = $_SESSION['user_id'];
    
    // Prepare update data
    $updateData = [];
    if (isset($_POST['course'])) $updateData['course'] = sanitizeInput($_POST['course']);
    if (isset($_POST['year_level'])) $updateData['year_level'] = sanitizeInput($_POST['year_level']);
    if (isset($_POST['bio'])) $updateData['bio'] = sanitizeInput($_POST['bio']);
    if (isset($_POST['phone'])) $updateData['phone'] = sanitizeInput($_POST['phone']);
    
    // Update profile using Auth class
    $auth = new Auth();
    $result = $auth->updateProfile($userId, $updateData);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Update profile API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
