<?php
/**
 * Get Services API Endpoint
 * Retrieves tutoring and freelance services with filtering and pagination
 */

// Set content type header only if headers haven't been sent
if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once dirname(__DIR__) . '/config/config.php';

try {
    $db = getDBConnection();
    
    // Get parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : SERVICES_PER_PAGE;
    $offset = ($page - 1) * $limit;
    
    $category = $_GET['category'] ?? '';
    $subject = $_GET['subject'] ?? '';
    $priceMin = isset($_GET['price_min']) ? floatval($_GET['price_min']) : 0;
    $priceMax = isset($_GET['price_max']) ? floatval($_GET['price_max']) : 0;
    $availability = $_GET['availability'] ?? '';
    $search = $_GET['search'] ?? '';
    $sort = $_GET['sort'] ?? 'newest';
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    // Build WHERE clause
    $whereConditions = ['s.status = "active"'];
    $params = [];
    
    if ($category && $category !== 'all') {
        $whereConditions[] = 's.category = ?';
        $params[] = $category;
    }
    
    if ($subject) {
        $whereConditions[] = 's.subject_skill = ?';
        $params[] = $subject;
    }
    
    if ($priceMin > 0) {
        $whereConditions[] = 's.price_per_hour >= ?';
        $params[] = $priceMin;
    }
    
    if ($priceMax > 0) {
        $whereConditions[] = 's.price_per_hour <= ?';
        $params[] = $priceMax;
    }
    
    if ($availability) {
        $whereConditions[] = 's.availability LIKE ?';
        $params[] = '%' . $availability . '%';
    }
    
    if ($search) {
        $whereConditions[] = '(s.title LIKE ? OR s.description LIKE ? OR s.subject_skill LIKE ?)';
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($userId > 0) {
        $whereConditions[] = 's.user_id = ?';
        $params[] = $userId;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Build ORDER BY clause
    $orderBy = 's.created_at DESC';
    switch ($sort) {
        case 'oldest':
            $orderBy = 's.created_at ASC';
            break;
        case 'price_low':
            $orderBy = 's.price_per_hour ASC';
            break;
        case 'price_high':
            $orderBy = 's.price_per_hour DESC';
            break;
        case 'rating':
            $orderBy = 's.rating DESC, s.total_reviews DESC';
            break;
        case 'popular':
            $orderBy = 's.views DESC';
            break;
        case 'title':
            $orderBy = 's.title ASC';
            break;
    }
    
    // Get total count
    $countSql = "
        SELECT COUNT(*) as total
        FROM services s
        LEFT JOIN users u ON s.user_id = u.id
        WHERE $whereClause
    ";
    
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch()['total'];
    
    // Get services
    $sql = "
        SELECT 
            s.id,
            s.title,
            s.description,
            s.category,
            s.subject_skill,
            s.price_per_hour,
            s.availability,
            s.location,
            s.images,
            s.views,
            s.rating,
            s.total_reviews,
            s.created_at,
            CONCAT(u.first_name, ' ', u.last_name) as provider_name,
            u.student_id as provider_student_id,
            u.course as provider_course,
            u.year_level as provider_year
        FROM services s
        LEFT JOIN users u ON s.user_id = u.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll();
    
    // Process services
    foreach ($services as &$service) {
        $service['images'] = $service['images'] ? json_decode($service['images'], true) : [];
        $service['price_per_hour'] = floatval($service['price_per_hour']);
        $service['views'] = intval($service['views']);
        $service['rating'] = $service['rating'] ? floatval($service['rating']) : null;
        $service['total_reviews'] = intval($service['total_reviews']);
    }
    
    echo json_encode([
        'success' => true,
        'services' => $services,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => intval($totalCount),
            'pages' => ceil($totalCount / $limit)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get services API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
