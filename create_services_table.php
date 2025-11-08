<?php
/**
 * Create Services Table
 * Creates the services table and related structures
 */

require_once 'config/config.php';

echo "<h1>🔧 Creating Services Table</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Check if services table already exists
    $stmt = $db->query("SHOW TABLES LIKE 'services'");
    if ($stmt->fetch()) {
        echo "<p style='color: orange;'>⚠️ Services table already exists</p>";
    } else {
        echo "<p>Creating services table...</p>";
        
        // Create services table
        $createServicesSQL = "
            CREATE TABLE services (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                title VARCHAR(200) NOT NULL,
                description TEXT NOT NULL,
                category ENUM('tutoring', 'freelance', 'academic', 'technical', 'creative', 'other') NOT NULL,
                subject_skill VARCHAR(100),
                price_per_hour DECIMAL(8,2) NOT NULL,
                availability TEXT,
                location VARCHAR(100),
                images JSON,
                status ENUM('active', 'inactive') DEFAULT 'active',
                views INT DEFAULT 0,
                rating DECIMAL(3,2) DEFAULT 0.00,
                total_reviews INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";
        
        $db->exec($createServicesSQL);
        echo "<p style='color: green;'>✅ Services table created successfully</p>";
    }
    
    // Create indexes for better performance
    echo "<p>Creating indexes...</p>";
    
    try {
        $db->exec("CREATE INDEX idx_services_user_id ON services(user_id)");
        echo "<p>✅ Created index on user_id</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "<p style='color: orange;'>⚠️ Index on user_id already exists</p>";
        } else {
            throw $e;
        }
    }
    
    try {
        $db->exec("CREATE INDEX idx_services_category ON services(category)");
        echo "<p>✅ Created index on category</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "<p style='color: orange;'>⚠️ Index on category already exists</p>";
        } else {
            throw $e;
        }
    }
    
    try {
        $db->exec("CREATE INDEX idx_services_status ON services(status)");
        echo "<p>✅ Created index on status</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "<p style='color: orange;'>⚠️ Index on status already exists</p>";
        } else {
            throw $e;
        }
    }
    
    // Create some sample services
    echo "<h3>Creating Sample Services</h3>";
    
    // Get a test user
    $stmt = $db->query("SELECT id, first_name, last_name FROM users LIMIT 1");
    $testUser = $stmt->fetch();
    
    if ($testUser) {
        echo "<p>Using test user: {$testUser['first_name']} {$testUser['last_name']} (ID: {$testUser['id']})</p>";
        
        $sampleServices = [
            [
                'title' => 'Math Tutoring for Engineering Students',
                'description' => 'Experienced tutor offering comprehensive math support for engineering students. Specializing in Calculus, Linear Algebra, and Differential Equations. I have helped over 50 students improve their grades.',
                'category' => 'tutoring',
                'subject_skill' => 'Mathematics, Calculus, Linear Algebra',
                'price_per_hour' => 250.00,
                'availability' => 'Monday-Friday 2PM-6PM, Weekends flexible',
                'location' => 'Main Campus'
            ],
            [
                'title' => 'Web Development Services',
                'description' => 'Professional web development services for students and small businesses. I can create responsive websites, web applications, and provide technical consulting.',
                'category' => 'freelance',
                'subject_skill' => 'HTML, CSS, JavaScript, PHP, MySQL',
                'price_per_hour' => 400.00,
                'availability' => 'Evenings and weekends, flexible schedule',
                'location' => 'Online or Main Campus'
            ],
            [
                'title' => 'Graphic Design and Logo Creation',
                'description' => 'Creative graphic design services including logo design, posters, flyers, and digital artwork. Perfect for student organizations and projects.',
                'category' => 'creative',
                'subject_skill' => 'Photoshop, Illustrator, Logo Design',
                'price_per_hour' => 300.00,
                'availability' => 'Monday, Wednesday, Friday 1PM-5PM',
                'location' => 'Main Campus'
            ]
        ];
        
        foreach ($sampleServices as $service) {
            // Check if service already exists
            $stmt = $db->prepare("SELECT id FROM services WHERE title = ?");
            $stmt->execute([$service['title']]);
            
            if (!$stmt->fetch()) {
                $stmt = $db->prepare("
                    INSERT INTO services (user_id, title, description, category, subject_skill, price_per_hour, availability, location, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
                ");
                
                $result = $stmt->execute([
                    $testUser['id'],
                    $service['title'],
                    $service['description'],
                    $service['category'],
                    $service['subject_skill'],
                    $service['price_per_hour'],
                    $service['availability'],
                    $service['location']
                ]);
                
                if ($result) {
                    $serviceId = $db->lastInsertId();
                    echo "<p style='color: green;'>✅ Created service: {$service['title']} (ID: {$serviceId})</p>";
                } else {
                    echo "<p style='color: red;'>❌ Failed to create service: {$service['title']}</p>";
                }
            } else {
                echo "<p style='color: orange;'>⚠️ Service already exists: {$service['title']}</p>";
            }
        }
    } else {
        echo "<p style='color: orange;'>⚠️ No users found to create sample services</p>";
    }
    
    // Final verification
    echo "<h3>Verification</h3>";
    $stmt = $db->query("SELECT COUNT(*) as total FROM services");
    $total = $stmt->fetch()['total'];
    echo "<p><strong>Total services in database:</strong> {$total}</p>";
    
    if ($total > 0) {
        $stmt = $db->query("
            SELECT s.id, s.title, s.category, s.price_per_hour, 
                   CONCAT(u.first_name, ' ', u.last_name) as provider_name
            FROM services s
            LEFT JOIN users u ON s.user_id = u.id
            ORDER BY s.created_at DESC
        ");
        $services = $stmt->fetchAll();
        
        echo "<h4>All Services:</h4>";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Title</th><th>Category</th><th>Price/Hour</th><th>Provider</th></tr>";
        
        foreach ($services as $service) {
            echo "<tr>";
            echo "<td>{$service['id']}</td>";
            echo "<td>{$service['title']}</td>";
            echo "<td>" . ucfirst($service['category']) . "</td>";
            echo "<td>₱" . number_format($service['price_per_hour'], 2) . "</td>";
            echo "<td>{$service['provider_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2 style='color: green;'>🎉 Services Table Setup Complete!</h2>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Visit <a href='post-service.php' target='_blank'>post-service.php</a> to test service posting</li>";
    echo "<li>Visit <a href='marketplace.php?type=services' target='_blank'>marketplace.php?type=services</a> to view services</li>";
    echo "<li>Test the complete service posting workflow</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h2 { color: #27ae60; }
h3 { color: #3498db; }
h4 { color: #e67e22; }
table { width: 100%; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f8f9fa; font-weight: bold; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
