<?php
/**
 * Setup Media Messaging System
 * Add support for emojis, images, and videos in messages
 */

require_once 'config/config.php';

echo "<h1>🚀 Setting Up Media Messaging System</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Add media columns to messages table
    echo "<h3>1. Adding media support to messages table...</h3>";
    
    // Check if columns already exist
    $stmt = $db->query("DESCRIBE messages");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $columnsToAdd = [
        'message_type' => "ALTER TABLE messages ADD COLUMN message_type ENUM('text', 'image', 'video', 'emoji') DEFAULT 'text'",
        'media_url' => "ALTER TABLE messages ADD COLUMN media_url VARCHAR(500) NULL",
        'media_filename' => "ALTER TABLE messages ADD COLUMN media_filename VARCHAR(255) NULL",
        'media_size' => "ALTER TABLE messages ADD COLUMN media_size INT NULL",
        'media_mime_type' => "ALTER TABLE messages ADD COLUMN media_mime_type VARCHAR(100) NULL",
        'thumbnail_url' => "ALTER TABLE messages ADD COLUMN thumbnail_url VARCHAR(500) NULL"
    ];
    
    foreach ($columnsToAdd as $columnName => $sql) {
        if (!in_array($columnName, $columns)) {
            $db->exec($sql);
            echo "<p style='color: green;'>✅ Added {$columnName} column</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ {$columnName} column already exists</p>";
        }
    }
    
    // Create media_uploads table for tracking uploaded files
    echo "<h3>2. Creating media_uploads table...</h3>";
    $mediaUploadsSQL = "
        CREATE TABLE IF NOT EXISTS media_uploads (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size INT NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            file_type ENUM('image', 'video') NOT NULL,
            thumbnail_path VARCHAR(500) NULL,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_uploads (user_id, upload_date),
            INDEX idx_file_type (file_type)
        )
    ";
    
    $db->exec($mediaUploadsSQL);
    echo "<p style='color: green;'>✅ Media uploads table created</p>";
    
    // Create uploads directory structure
    echo "<h3>3. Creating upload directories...</h3>";
    $directories = [
        'uploads',
        'uploads/messages',
        'uploads/messages/images',
        'uploads/messages/videos',
        'uploads/messages/thumbnails'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
            echo "<p style='color: green;'>✅ Created directory: {$dir}</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ Directory already exists: {$dir}</p>";
        }
    }
    
    // Create .htaccess for uploads security
    $htaccessContent = "# Prevent direct access to PHP files in uploads
<Files *.php>
    Order Deny,Allow
    Deny from all
</Files>

# Allow common media types
<FilesMatch \"\.(jpg|jpeg|png|gif|webp|mp4|webm|mov)$\">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Set proper MIME types
AddType image/webp .webp
AddType video/mp4 .mp4
AddType video/webm .webm
AddType video/quicktime .mov
";
    
    file_put_contents('uploads/.htaccess', $htaccessContent);
    echo "<p style='color: green;'>✅ Created security .htaccess file</p>";
    
    // Verify table structure
    echo "<h3>4. Verifying table structure...</h3>";
    $stmt = $db->query("DESCRIBE messages");
    $messageColumns = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th>Column</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    foreach ($messageColumns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Default']}</td></tr>";
    }
    echo "</table>";
    
    // Check media_uploads table
    $stmt = $db->query("SHOW TABLES LIKE 'media_uploads'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ media_uploads table verified</p>";
    } else {
        echo "<p style='color: red;'>❌ media_uploads table missing</p>";
    }
    
    echo "<h2 style='color: green;'>🎉 Media Messaging Setup Complete!</h2>";
    echo "<p>The system now supports:</p>";
    echo "<ul>";
    echo "<li>✅ Text messages with emojis</li>";
    echo "<li>✅ Image uploads (JPG, PNG, GIF, WebP)</li>";
    echo "<li>✅ Video uploads (MP4, WebM, MOV)</li>";
    echo "<li>✅ Media thumbnails and previews</li>";
    echo "<li>✅ Secure file storage</li>";
    echo "</ul>";
    
    echo "<p><a href='messages.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Enhanced Messaging</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h2 { color: #27ae60; }
h3 { color: #3498db; }
table { width: 100%; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f8f9fa; font-weight: bold; }
</style>
