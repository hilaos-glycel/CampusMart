<?php
/**
 * Registration API Endpoint
 * Handles new user registration
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
    $required_fields = ['student_id', 'username', 'email', 'password', 'confirm_password', 'first_name', 'last_name', 'course', 'year_level'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit();
        }
    }
    
    // Validate password confirmation
    if ($_POST['password'] !== $_POST['confirm_password']) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit();
    }
    
    // Validate password strength
    if (strlen($_POST['password']) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
        exit();
    }
    
    // Sanitize input data
    $data = [
        'student_id' => sanitizeInput($_POST['student_id']),
        'username' => sanitizeInput($_POST['username']),
        'email' => sanitizeInput($_POST['email']),
        'password' => $_POST['password'],
        'first_name' => sanitizeInput($_POST['first_name']),
        'last_name' => sanitizeInput($_POST['last_name']),
        'course' => sanitizeInput($_POST['course']),
        'year_level' => sanitizeInput($_POST['year_level']),
        'bio' => sanitizeInput($_POST['bio'] ?? '')
    ];
    
    // Attempt registration
    $auth = new Auth();
    $result = $auth->register($data);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Registration API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
