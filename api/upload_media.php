<?php
/**
 * Upload Media API
 * Handles image and video uploads for messaging
 */

if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once dirname(__DIR__) . '/config/config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $currentUser = getCurrentUser();
    $userId = $currentUser['id'];
    
    // Check if file was uploaded
    if (!isset($_FILES['media']) || $_FILES['media']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        exit;
    }
    
    $file = $_FILES['media'];
    $originalName = $file['name'];
    $tmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $mimeType = $file['type'];
    
    // Validate file size (max 50MB)
    $maxSize = 50 * 1024 * 1024; // 50MB
    if ($fileSize > $maxSize) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 50MB']);
        exit;
    }
    
    // Determine file type and validate
    $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedVideoTypes = ['video/mp4', 'video/webm', 'video/quicktime'];
    
    $fileType = null;
    $uploadDir = null;
    
    if (in_array($mimeType, $allowedImageTypes)) {
        $fileType = 'image';
        $uploadDir = 'uploads/messages/images/';
    } elseif (in_array($mimeType, $allowedVideoTypes)) {
        $fileType = 'video';
        $uploadDir = 'uploads/messages/videos/';
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only images (JPG, PNG, GIF, WebP) and videos (MP4, WebM, MOV) are allowed']);
        exit;
    }
    
    // Generate unique filename
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $filename = uniqid('media_' . time() . '_') . '.' . strtolower($extension);
    $filePath = $uploadDir . $filename;
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($tmpName, $filePath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
        exit;
    }
    
    // Generate thumbnail for videos and large images
    $thumbnailPath = null;
    if ($fileType === 'video') {
        $thumbnailPath = generateVideoThumbnail($filePath, $filename);
    } elseif ($fileType === 'image') {
        $thumbnailPath = generateImageThumbnail($filePath, $filename);
    }
    
    // Save to database
    $db = getDBConnection();
    
    $insertQuery = "
        INSERT INTO media_uploads (user_id, filename, original_name, file_path, file_size, mime_type, file_type, thumbnail_path)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";
    
    $stmt = $db->prepare($insertQuery);
    $result = $stmt->execute([
        $userId,
        $filename,
        $originalName,
        $filePath,
        $fileSize,
        $mimeType,
        $fileType,
        $thumbnailPath
    ]);
    
    if ($result) {
        $mediaId = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'media_id' => $mediaId,
                'filename' => $filename,
                'original_name' => $originalName,
                'file_path' => $filePath,
                'file_url' => SITE_URL . '/' . $filePath,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'file_type' => $fileType,
                'thumbnail_url' => $thumbnailPath ? SITE_URL . '/' . $thumbnailPath : null
            ]
        ]);
    } else {
        // Delete uploaded file if database insert failed
        unlink($filePath);
        if ($thumbnailPath && file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }
        
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save file information']);
    }
    
} catch (Exception $e) {
    error_log("Upload media error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred',
        'error' => $e->getMessage()
    ]);
}

/**
 * Generate thumbnail for video files
 */
function generateVideoThumbnail($videoPath, $filename) {
    $thumbnailDir = 'uploads/messages/thumbnails/';
    if (!file_exists($thumbnailDir)) {
        mkdir($thumbnailDir, 0755, true);
    }
    
    $thumbnailName = 'thumb_' . pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
    $thumbnailPath = $thumbnailDir . $thumbnailName;
    
    // Try to generate thumbnail using FFmpeg (if available)
    $ffmpegPath = 'ffmpeg'; // Adjust path as needed
    $command = "{$ffmpegPath} -i \"{$videoPath}\" -ss 00:00:01 -vframes 1 -y \"{$thumbnailPath}\" 2>/dev/null";
    
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($thumbnailPath)) {
        return $thumbnailPath;
    }
    
    // Fallback: create a simple placeholder
    return createPlaceholderThumbnail($thumbnailPath, 'video');
}

/**
 * Generate thumbnail for image files
 */
function generateImageThumbnail($imagePath, $filename) {
    $thumbnailDir = 'uploads/messages/thumbnails/';
    if (!file_exists($thumbnailDir)) {
        mkdir($thumbnailDir, 0755, true);
    }
    
    $thumbnailName = 'thumb_' . pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
    $thumbnailPath = $thumbnailDir . $thumbnailName;
    
    // Get image info
    $imageInfo = getimagesize($imagePath);
    if (!$imageInfo) {
        return null;
    }
    
    $originalWidth = $imageInfo[0];
    $originalHeight = $imageInfo[1];
    $mimeType = $imageInfo['mime'];
    
    // Skip thumbnail generation for small images
    if ($originalWidth <= 300 && $originalHeight <= 300) {
        return null;
    }
    
    // Calculate thumbnail dimensions (max 300x300)
    $maxSize = 300;
    $ratio = min($maxSize / $originalWidth, $maxSize / $originalHeight);
    $thumbWidth = intval($originalWidth * $ratio);
    $thumbHeight = intval($originalHeight * $ratio);
    
    // Create source image
    $sourceImage = null;
    switch ($mimeType) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($imagePath);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($imagePath);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($imagePath);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $sourceImage = imagecreatefromwebp($imagePath);
            }
            break;
    }
    
    if (!$sourceImage) {
        return null;
    }
    
    // Create thumbnail
    $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
    
    // Preserve transparency for PNG and GIF
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefill($thumbnail, 0, 0, $transparent);
    }
    
    // Resize image
    imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $originalWidth, $originalHeight);
    
    // Save thumbnail
    $success = imagejpeg($thumbnail, $thumbnailPath, 85);
    
    // Clean up
    imagedestroy($sourceImage);
    imagedestroy($thumbnail);
    
    return $success ? $thumbnailPath : null;
}

/**
 * Create placeholder thumbnail
 */
function createPlaceholderThumbnail($thumbnailPath, $type) {
    $width = 300;
    $height = 200;
    
    $image = imagecreatetruecolor($width, $height);
    $backgroundColor = imagecolorallocate($image, 240, 240, 240);
    $textColor = imagecolorallocate($image, 100, 100, 100);
    
    imagefill($image, 0, 0, $backgroundColor);
    
    $text = $type === 'video' ? '▶ VIDEO' : '📷 IMAGE';
    $font = 5; // Built-in font
    
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2;
    
    imagestring($image, $font, $x, $y, $text, $textColor);
    
    $success = imagejpeg($image, $thumbnailPath, 85);
    imagedestroy($image);
    
    return $success ? $thumbnailPath : null;
}
?>
