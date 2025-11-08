<?php
if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once dirname(__DIR__) . '/config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $currentUser = getCurrentUser();
    $userId = $currentUser['id'];
    
    // Get search query
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if (strlen($query) < 2) {
        echo json_encode(['success' => true, 'users' => []]);
        exit;
    }
    
    $db = getDBConnection();
    
    // Search users by name or email (excluding current user)
    $searchQuery = "
        SELECT 
            id,
            CONCAT(first_name, ' ', last_name) as name,
            email,
            first_name,
            last_name,
            course,
            year_level
        FROM users 
        WHERE id != ? 
            AND status = 'active'
            AND (
                CONCAT(first_name, ' ', last_name) LIKE ? OR
                email LIKE ? OR
                first_name LIKE ? OR
                last_name LIKE ?
            )
        ORDER BY 
            CASE 
                WHEN CONCAT(first_name, ' ', last_name) LIKE ? THEN 1
                WHEN first_name LIKE ? OR last_name LIKE ? THEN 2
                WHEN email LIKE ? THEN 3
                ELSE 4
            END,
            first_name ASC
        LIMIT 10
    ";
    
    $searchTerm = '%' . $query . '%';
    $exactSearch = $query . '%';
    
    $stmt = $db->prepare($searchQuery);
    $stmt->execute([
        $userId,           // id != ?
        $searchTerm,       // CONCAT(...) LIKE ?
        $searchTerm,       // email LIKE ?
        $searchTerm,       // first_name LIKE ?
        $searchTerm,       // last_name LIKE ?
        $exactSearch,      // CONCAT(...) LIKE ? (ORDER BY)
        $exactSearch,      // first_name LIKE ? (ORDER BY)
        $exactSearch,      // last_name LIKE ? (ORDER BY)
        $exactSearch       // email LIKE ? (ORDER BY)
    ]);
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $formattedUsers = [];
    foreach ($users as $user) {
        $formattedUsers[] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'course' => $user['course'],
            'year_level' => $user['year_level']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'users' => $formattedUsers
    ]);
    
} catch (Exception $e) {
    error_log("Error in search_users.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
