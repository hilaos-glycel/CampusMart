<?php
/**
 * Update Listing API Endpoint
 * Handles listing updates with image management
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
    $required_fields = ['listing_id', 'title', 'description', 'category', 'condition', 'type', 'price'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit();
        }
    }
    
    $db = getDBConnection();
    $userId = $_SESSION['user_id'];
    $listingId = intval($_POST['listing_id']);
    
    // Verify ownership
    $stmt = $db->prepare("SELECT id, images FROM listings WHERE id = ? AND user_id = ?");
    $stmt->execute([$listingId, $userId]);
    $existingListing = $stmt->fetch();
    
    if (!$existingListing) {
        echo json_encode(['success' => false, 'message' => 'Listing not found or access denied']);
        exit();
    }
    
    // Get category ID
    $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute([$_POST['category']]);
    $category = $stmt->fetch();
    
    if (!$category) {
        echo json_encode(['success' => false, 'message' => 'Invalid category']);
        exit();
    }
    
    // Validate price
    $price = floatval($_POST['price']);
    if ($price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Price must be greater than 0']);
        exit();
    }
    
    // Handle image management
    $currentImages = $existingListing['images'] ? json_decode($existingListing['images'], true) : [];
    $existingImages = isset($_POST['existing_images']) ? $_POST['existing_images'] : [];
    $removedImages = isset($_POST['removed_images']) ? $_POST['removed_images'] : [];
    
    // Remove deleted images from filesystem
    foreach ($removedImages as $removedImage) {
        $filePath = UPLOAD_PATH . 'listings/' . $removedImage;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // Start with existing images that weren't removed
    $finalImages = array_diff($currentImages, $removedImages);
    
    // Handle new image uploads
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $uploadDir = UPLOAD_PATH . 'listings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $imageCount = count($_FILES['images']['name']);
        if (count($finalImages) + $imageCount > 5) {
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
                    $finalImages[] = $fileName;
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
    $images = !empty($finalImages) ? json_encode(array_values($finalImages)) : null;
    
    // Update listing
    $stmt = $db->prepare("
        UPDATE listings 
        SET category_id = ?, title = ?, description = ?, price = ?, type = ?, condition_item = ?, 
            location = ?, rental_period = ?, images = ?, updated_at = NOW()
        WHERE id = ? AND user_id = ?
    ");
    
    $result = $stmt->execute([
        $category['id'],
        $title,
        $description,
        $price,
        $type,
        $condition,
        $location,
        $rentalPeriod,
        $images,
        $listingId,
        $userId
    ]);
    
    if ($result) {
        // Log activity
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, table_name, record_id, new_values, ip_address, user_agent, created_at)
            VALUES (?, 'update', 'listings', ?, ?, ?, ?, NOW())
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
            'message' => 'Listing updated successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update listing']);
    }
    
} catch (Exception $e) {
    error_log("Update listing API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
