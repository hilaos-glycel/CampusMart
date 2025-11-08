<?php
/**
 * Test Service Posting System
 * Verify that services can be created and displayed
 */

require_once 'config/config.php';

echo "<h1>🧪 Testing Service Posting System</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Check if services table exists and has proper structure
    echo "<h3>1. Services Table Structure Check</h3>";
    $stmt = $db->query("DESCRIBE services");
    $columns = $stmt->fetchAll();
    
    $requiredColumns = ['id', 'user_id', 'title', 'description', 'category', 'subject_skill', 'price_per_hour', 'availability', 'location', 'images', 'status', 'views', 'rating', 'total_reviews', 'created_at', 'updated_at'];
    $existingColumns = array_column($columns, 'Field');
    
    echo "<p><strong>Existing columns:</strong> " . implode(', ', $existingColumns) . "</p>";
    
    $missingColumns = array_diff($requiredColumns, $existingColumns);
    if (empty($missingColumns)) {
        echo "<p style='color: green;'>✅ All required columns exist</p>";
    } else {
        echo "<p style='color: red;'>❌ Missing columns: " . implode(', ', $missingColumns) . "</p>";
    }
    
    // Check current services count
    echo "<h3>2. Current Services</h3>";
    $stmt = $db->query("SELECT COUNT(*) as total FROM services");
    $total = $stmt->fetch()['total'];
    echo "<p>Total services in database: <strong>{$total}</strong></p>";
    
    if ($total > 0) {
        $stmt = $db->query("
            SELECT 
                s.id, s.title, s.category, s.price_per_hour, s.status,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name
            FROM services s
            LEFT JOIN users u ON s.user_id = u.id
            ORDER BY s.created_at DESC
            LIMIT 5
        ");
        $services = $stmt->fetchAll();
        
        echo "<h4>Recent Services:</h4>";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Title</th><th>Category</th><th>Price/Hour</th><th>Provider</th><th>Status</th></tr>";
        
        foreach ($services as $service) {
            echo "<tr>";
            echo "<td>{$service['id']}</td>";
            echo "<td>{$service['title']}</td>";
            echo "<td>" . ucfirst($service['category']) . "</td>";
            echo "<td>₱" . number_format($service['price_per_hour'], 2) . "</td>";
            echo "<td>{$service['provider_name']}</td>";
            echo "<td>{$service['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test API endpoints
    echo "<h3>3. API Endpoints Test</h3>";
    
    // Test get_services.php
    echo "<h4>Testing get_services.php:</h4>";
    ob_start();
    include 'api/get_services.php';
    $servicesApiOutput = ob_get_clean();
    
    $servicesData = json_decode($servicesApiOutput, true);
    if ($servicesData && $servicesData['success']) {
        echo "<p style='color: green;'>✅ get_services.php working - found " . count($servicesData['services']) . " services</p>";
    } else {
        echo "<p style='color: red;'>❌ get_services.php failed</p>";
        echo "<details><summary>Raw output</summary><pre>" . htmlspecialchars($servicesApiOutput) . "</pre></details>";
    }
    
    // Test create_service.php (check if file exists)
    if (file_exists('api/create_service.php')) {
        echo "<p style='color: green;'>✅ create_service.php exists</p>";
    } else {
        echo "<p style='color: red;'>❌ create_service.php missing</p>";
    }
    
    // Test update_service.php (check if file exists)
    if (file_exists('api/update_service.php')) {
        echo "<p style='color: green;'>✅ update_service.php exists</p>";
    } else {
        echo "<p style='color: red;'>❌ update_service.php missing</p>";
    }
    
    // Check frontend files
    echo "<h3>4. Frontend Files Check</h3>";
    
    if (file_exists('post-service.php')) {
        echo "<p style='color: green;'>✅ post-service.php exists</p>";
    } else {
        echo "<p style='color: red;'>❌ post-service.php missing</p>";
    }
    
    if (file_exists('service-details.php')) {
        echo "<p style='color: green;'>✅ service-details.php exists</p>";
    } else {
        echo "<p style='color: red;'>❌ service-details.php missing</p>";
    }
    
    // Check upload directory
    echo "<h3>5. Upload Directory Check</h3>";
    $serviceUploadDir = 'uploads/services/';
    if (is_dir($serviceUploadDir)) {
        echo "<p style='color: green;'>✅ Service upload directory exists: {$serviceUploadDir}</p>";
        if (is_writable($serviceUploadDir)) {
            echo "<p style='color: green;'>✅ Directory is writable</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Directory exists but may not be writable</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Service upload directory missing: {$serviceUploadDir}</p>";
    }
    
    // Test navigation link
    echo "<h3>6. Navigation Integration</h3>";
    if (strpos(file_get_contents('includes/header.php'), 'post-service.php') !== false) {
        echo "<p style='color: green;'>✅ Navigation link added to header</p>";
    } else {
        echo "<p style='color: red;'>❌ Navigation link missing from header</p>";
    }
    
    // Create a test service if no services exist and user is logged in
    echo "<h3>7. Test Service Creation</h3>";
    
    // Check if we have any users to test with
    $stmt = $db->query("SELECT id, username FROM users LIMIT 1");
    $testUser = $stmt->fetch();
    
    if ($testUser && $total == 0) {
        echo "<p>Creating a test service...</p>";
        
        $stmt = $db->prepare("
            INSERT INTO services (user_id, title, description, category, subject_skill, price_per_hour, availability, location, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        
        $testServiceData = [
            $testUser['id'],
            'Math Tutoring for Engineering Students',
            'Experienced tutor offering comprehensive math support for engineering students. Specializing in Calculus, Linear Algebra, and Differential Equations.',
            'tutoring',
            'Mathematics, Calculus, Linear Algebra',
            250.00,
            'Monday-Friday 2PM-6PM, Weekends flexible',
            'Main Campus'
        ];
        
        if ($stmt->execute($testServiceData)) {
            $testServiceId = $db->lastInsertId();
            echo "<p style='color: green;'>✅ Test service created successfully (ID: {$testServiceId})</p>";
            echo "<p>🔗 <a href='service-details.php?id={$testServiceId}' target='_blank'>View Test Service</a></p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create test service</p>";
        }
    } else if ($total > 0) {
        echo "<p style='color: green;'>✅ Services already exist in database</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No users found to create test service</p>";
    }
    
    echo "<h2>🎯 Summary</h2>";
    echo "<p><strong>Service Posting System Status:</strong></p>";
    echo "<ul>";
    echo "<li>Database structure: " . (empty($missingColumns) ? "✅ Complete" : "❌ Missing columns") . "</li>";
    echo "<li>API endpoints: " . (file_exists('api/create_service.php') ? "✅ Ready" : "❌ Missing") . "</li>";
    echo "<li>Frontend interface: " . (file_exists('post-service.php') ? "✅ Ready" : "❌ Missing") . "</li>";
    echo "<li>Upload directory: " . (is_dir($serviceUploadDir) ? "✅ Ready" : "❌ Missing") . "</li>";
    echo "<li>Navigation: " . (strpos(file_get_contents('includes/header.php'), 'post-service.php') !== false ? "✅ Integrated" : "❌ Missing") . "</li>";
    echo "</ul>";
    
    echo "<h3>🚀 Next Steps:</h3>";
    echo "<ul>";
    echo "<li>Login to your account</li>";
    echo "<li>Click 'Offer Service' in the navigation</li>";
    echo "<li>Fill out the service posting form</li>";
    echo "<li>Test the service creation process</li>";
    echo "<li>Verify services appear in the marketplace Services tab</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h2 { color: #27ae60; }
h3 { color: #3498db; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
h4 { color: #e67e22; }
table { width: 100%; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f8f9fa; font-weight: bold; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
details { margin: 10px 0; }
pre { background: #f8f8f8; padding: 10px; border-radius: 5px; overflow-x: auto; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
