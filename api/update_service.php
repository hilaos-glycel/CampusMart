<?php
/**
 * Update Service API Endpoint
 * Handles service updates with image upload
 */

// Set content type header only if headers haven't been sent
if (!headers_sent()) {
    header('Content-Type: application/json');
}
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
    $required_fields = ['service_id', 'title', 'description', 'category', 'price_per_hour'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit();
        }
    }
    
    $db = getDBConnection();
    $userId = $_SESSION['user_id'];
    $serviceId = intval($_POST['service_id']);
    
    // Verify ownership
    $stmt = $db->prepare("SELECT id FROM services WHERE id = ? AND user_id = ?");
    $stmt->execute([$serviceId, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Service not found or access denied']);
        exit();
    }
    
    // Validate price
    $pricePerHour = floatval($_POST['price_per_hour']);
    if ($pricePerHour <= 0) {
        echo json_encode(['success' => false, 'message' => 'Price per hour must be greater than 0']);
        exit();
    }
    
    // Handle image uploads
    $uploadedImages = [];
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $uploadDir = UPLOAD_PATH . 'services/';
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
    
    // Prepare service data
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $category = sanitizeInput($_POST['category']);
    $subjectSkill = !empty($_POST['subject_skill']) ? sanitizeInput($_POST['subject_skill']) : null;
    $location = !empty($_POST['location']) ? sanitizeInput($_POST['location']) : null;
    $availability = !empty($_POST['availability']) ? sanitizeInput($_POST['availability']) : null;
    
    // Handle images - combine existing, new, and removed images
    $stmt = $db->prepare("SELECT images FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $existingService = $stmt->fetch();
    $existingImages = $existingService['images'] ? json_decode($existingService['images'], true) : [];
    
    // Handle removed images
    $removedImages = [];
    if (!empty($_POST['removed_images'])) {
        $removedImages = explode(',', $_POST['removed_images']);
        // Remove files from filesystem
        foreach ($removedImages as $removedImage) {
            $filePath = UPLOAD_PATH . 'services/' . $removedImage;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        // Filter out removed images from existing images
        $existingImages = array_filter($existingImages, function($img) use ($removedImages) {
            return !in_array($img, $removedImages);
        });
    }
    
    // Combine existing images (after removal) with new uploaded images
    $finalImages = array_merge($existingImages, $uploadedImages);
    $images = !empty($finalImages) ? json_encode(array_values($finalImages)) : null;
    
    // Update service
    $stmt = $db->prepare("
        UPDATE services 
        SET title = ?, description = ?, category = ?, subject_skill = ?, price_per_hour = ?, 
            availability = ?, location = ?, images = ?, updated_at = NOW()
        WHERE id = ? AND user_id = ?
    ");
    
    $result = $stmt->execute([
        $title,
        $description,
        $category,
        $subjectSkill,
        $pricePerHour,
        $availability,
        $location,
        $images,
        $serviceId,
        $userId
    ]);
    
    if ($result) {
        // Log activity
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, table_name, record_id, new_values, ip_address, user_agent, created_at)
            VALUES (?, 'update', 'services', ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId,
            $serviceId,
            json_encode(['title' => $title, 'category' => $category, 'price_per_hour' => $pricePerHour]),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Service updated successfully',
            'service_id' => $serviceId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update service']);
    }
    
} catch (Exception $e) {
    error_log("Update service API error: " . $e->getMessage());
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
    }
    
    // In development, show the actual error
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        $errorMessage .= ' Debug: ' . $e->getMessage();
    }
    
    echo json_encode(['success' => false, 'message' => $errorMessage]);
}
?>
