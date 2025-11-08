<?php
/**
 * Create Listing API Endpoint
 * Handles new listing creation with image upload
 */

header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/config.php';

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
    
    // Validate required fields
    $required_fields = ['title', 'description', 'category', 'condition', 'type', 'price'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit();
        }
    }
    
    $db = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    // Get category ID - try by slug first, then by name as fallback
    $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute([$_POST['category']]);
    $category = $stmt->fetch();
    
    // If not found by slug, try by name (case insensitive)
    if (!$category) {
        $stmt = $db->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?) OR LOWER(REPLACE(name, ' ', '')) = LOWER(?)");
        $stmt->execute([$_POST['category'], $_POST['category']]);
        $category = $stmt->fetch();
    }
    
    // If still not found, try to find any category that matches
    if (!$category) {
        $stmt = $db->prepare("SELECT id FROM categories LIMIT 1");
        $stmt->execute();
        $category = $stmt->fetch();
        
        if (!$category) {
            echo json_encode(['success' => false, 'message' => 'No categories found. Please run the database setup.']);
            exit();
        }
    }
    
    // Validate price
    $price = floatval($_POST['price']);
    if ($price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Price must be greater than 0']);
        exit();
    }
    
    // Handle image uploads
    $uploadedImages = [];
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $uploadDir = UPLOAD_PATH . 'listings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $imageCount = count($_FILES['images']['name']);
        if ($imageCount > 5) {
            echo json_encode(['success' => false, 'message' => 'Maximum 5 images allowed']);
            exit();
        }
        
        for ($i = 0; $i < $imageCount; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['images']['tmp_name'][$i];
                $originalName = $_FILES['images']['name'][$i];
                $fileSize = $_FILES['images']['size'][$i];
                
                // Validate file size
                if ($fileSize > MAX_FILE_SIZE) {
                    echo json_encode(['success' => false, 'message' => 'Image file too large (max 5MB)']);
                    exit();
                }
                
                // Validate file type
                $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (!in_array($fileExtension, ALLOWED_IMAGE_TYPES)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid image type']);
                    exit();
                }
                
                // Generate unique filename
                $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmpName, $filePath)) {
                    $uploadedImages[] = $fileName;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
                    exit();
                }
            }
        }
    }
    
    // Prepare listing data
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $condition = sanitizeInput($_POST['condition']);
    $type = sanitizeInput($_POST['type']);
    $location = !empty($_POST['location']) ? sanitizeInput($_POST['location']) : null;
    $rentalPeriod = ($type === 'rent' && !empty($_POST['rental_period'])) ? sanitizeInput($_POST['rental_period']) : null;
    $images = !empty($uploadedImages) ? json_encode($uploadedImages) : null;
    
    // Check if condition_item column exists, if not use condition
    $columnName = 'condition_item';
    try {
        $checkStmt = $db->query("DESCRIBE listings");
        $columns = $checkStmt->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('condition_item', $columns)) {
            if (in_array('condition', $columns)) {
                $columnName = 'condition';
            } else {
                // Add the missing column
                $db->exec("ALTER TABLE listings ADD COLUMN condition_item ENUM('New', 'Like New', 'Good', 'Fair', 'Poor') NOT NULL DEFAULT 'Good' AFTER type");
                $columnName = 'condition_item';
            }
        }
    } catch (Exception $e) {
        // If we can't check, try with condition_item first
        $columnName = 'condition_item';
    }
    
    // Insert listing
    $stmt = $db->prepare("
        INSERT INTO listings (user_id, category_id, title, description, price, type, {$columnName}, location, rental_period, images, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    
    $result = $stmt->execute([
        $userId,
        $category['id'],
        $title,
        $description,
        $price,
        $type,
        $condition,
        $location,
        $rentalPeriod,
        $images
    ]);
    
    if ($result) {
        $listingId = $db->lastInsertId();
        
        // Log activity
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, table_name, record_id, new_values, ip_address, user_agent, created_at)
            VALUES (?, 'create', 'listings', ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId,
            $listingId,
            json_encode(['title' => $title, 'type' => $type, 'price' => $price]),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Listing created successfully',
            'listing_id' => $listingId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create listing']);
    }
    
} catch (Exception $e) {
    error_log("Create listing API error: " . $e->getMessage());
    http_response_code(500);
    
    // Provide more specific error messages for debugging
    $errorMessage = 'Server error occurred';
    
    // Check for common database issues
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        $errorMessage = 'Database connection failed. Please check if MySQL is running.';
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        $errorMessage = 'Database access denied. Please check database credentials.';
    } elseif (strpos($e->getMessage(), "Table") !== false && strpos($e->getMessage(), "doesn't exist") !== false) {
        $errorMessage = 'Database table missing. Please run the setup script.';
    } elseif (strpos($e->getMessage(), 'Column') !== false && strpos($e->getMessage(), 'Unknown column') !== false) {
        $errorMessage = 'Database schema mismatch. Please update your database.';
    } elseif (strpos($e->getMessage(), 'uploads') !== false) {
        $errorMessage = 'File upload directory issue. Please check folder permissions.';
    }
    
    // In development, show the actual error
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        $errorMessage .= ' Debug: ' . $e->getMessage();
    }
    
    echo json_encode(['success' => false, 'message' => $errorMessage]);
}
?>
