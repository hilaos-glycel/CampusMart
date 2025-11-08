<?php
/**
 * Get Listings API Endpoint
 * Retrieves marketplace listings with filtering and pagination
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
    $limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : ITEMS_PER_PAGE;
    $offset = ($page - 1) * $limit;
    
    $category = $_GET['category'] ?? '';
    $type = $_GET['type'] ?? '';
    $priceMin = isset($_GET['price_min']) ? floatval($_GET['price_min']) : 0;
    $priceMax = isset($_GET['price_max']) ? floatval($_GET['price_max']) : 0;
    $location = $_GET['location'] ?? '';
    $search = $_GET['search'] ?? '';
    $sort = $_GET['sort'] ?? 'newest';
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    // Build WHERE clause
    $whereConditions = ['l.status = "active"'];
    $params = [];
    
    if ($category) {
        $whereConditions[] = 'c.slug = ?';
        $params[] = $category;
    }
    
    if ($type) {
        $whereConditions[] = 'l.type = ?';
        $params[] = $type;
    }
    
    if ($priceMin > 0) {
        $whereConditions[] = 'l.price >= ?';
        $params[] = $priceMin;
    }
    
    if ($priceMax > 0) {
        $whereConditions[] = 'l.price <= ?';
        $params[] = $priceMax;
    }
    
    if ($location) {
        $whereConditions[] = 'l.location = ?';
        $params[] = $location;
    }
    
    if ($search) {
        $whereConditions[] = '(l.title LIKE ? OR l.description LIKE ?)';
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($userId > 0) {
        $whereConditions[] = 'l.user_id = ?';
        $params[] = $userId;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Build ORDER BY clause
    $orderBy = 'l.created_at DESC';
    switch ($sort) {
        case 'oldest':
            $orderBy = 'l.created_at ASC';
            break;
        case 'price_low':
            $orderBy = 'l.price ASC';
            break;
        case 'price_high':
            $orderBy = 'l.price DESC';
            break;
        case 'views':
            $orderBy = 'l.views DESC';
            break;
        case 'title':
            $orderBy = 'l.title ASC';
            break;
    }
    
    // Get total count
    $countSql = "
        SELECT COUNT(*) as total
        FROM listings l
        LEFT JOIN categories c ON l.category_id = c.id
        LEFT JOIN users u ON l.user_id = u.id
        WHERE $whereClause
    ";
    
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch()['total'];
    
    // Get listings
    $sql = "
        SELECT 
            l.id,
            l.title,
            l.description,
            l.price,
            l.type,
            l.condition_item,
            l.location,
            l.rental_period,
            l.images,
            l.views,
            l.created_at,
            c.name as category_name,
            c.slug as category_slug,
            CONCAT(u.first_name, ' ', u.last_name) as seller_name,
            u.student_id as seller_student_id,
            u.rating as seller_rating
        FROM listings l
        LEFT JOIN categories c ON l.category_id = c.id
        LEFT JOIN users u ON l.user_id = u.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $listings = $stmt->fetchAll();
    
    // Process listings
    foreach ($listings as &$listing) {
        $listing['images'] = $listing['images'] ? json_decode($listing['images'], true) : [];
        $listing['price'] = floatval($listing['price']);
        $listing['views'] = intval($listing['views']);
        $listing['seller_rating'] = $listing['seller_rating'] ? floatval($listing['seller_rating']) : null;
    }
    
    echo json_encode([
        'success' => true,
        'listings' => $listings,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => intval($totalCount),
            'pages' => ceil($totalCount / $limit)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get listings API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
