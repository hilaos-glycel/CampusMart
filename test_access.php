<?php
/**
 * Simple test script to verify CampusMart is accessible
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusMart Access Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px;
            background: #f5f5f5;
        }
        .success { 
            color: #22543d; 
            background: #f0fff4; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 15px 0;
            border-left: 4px solid #38a169;
        }
        .info { 
            color: #2a4365; 
            background: #ebf8ff; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 15px 0;
            border-left: 4px solid #3182ce;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-primary { background: #3182ce; color: white; }
        .btn-success { background: #38a169; color: white; }
        .btn-secondary { background: #718096; color: white; }
        h1 { color: #2d3748; }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎉 CampusMart Access Test - SUCCESS!</h1>
        
        <div class="success">
            ✅ <strong>Great!</strong> CampusMart is accessible and working properly.
        </div>
        
        <div class="info">
            <h3>📋 System Information:</h3>
            <p><strong>Current URL:</strong> <?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?></p>
            <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
            <p><strong>Script Path:</strong> <?php echo __FILE__; ?></p>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
            <p><strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <div class="info">
            <h3>🔗 Correct URLs to Access CampusMart:</h3>
            <p><strong>Main Site:</strong> <a href="http://localhost/CampusMart/">http://localhost/CampusMart/</a></p>
            <p><strong>Direct Index:</strong> <a href="http://localhost/CampusMart/index.php">http://localhost/CampusMart/index.php</a></p>
            <p><strong>Admin Panel:</strong> <a href="http://localhost/CampusMart/admin/">http://localhost/CampusMart/admin/</a></p>
        </div>
        
        <h3>🚀 Quick Actions:</h3>
        <a href="index.php" class="btn btn-primary">Go to Homepage</a>
        <a href="marketplace.php" class="btn btn-success">Browse Marketplace</a>
        <a href="admin/login.php" class="btn btn-secondary">Admin Login</a>
        
        <div class="info">
            <h3>💡 Common Solutions if you're still getting "Not Found" errors:</h3>
            <ol>
                <li><strong>Check XAMPP:</strong> Make sure Apache is running in XAMPP Control Panel</li>
                <li><strong>Correct URL:</strong> Use <code>http://localhost/CampusMart/</code> (with trailing slash)</li>
                <li><strong>Case Sensitive:</strong> Make sure "CampusMart" has correct capitalization</li>
                <li><strong>Clear Browser Cache:</strong> Press Ctrl+F5 to refresh</li>
                <li><strong>Check htdocs:</strong> Ensure CampusMart folder is in C:\xampp\htdocs\</li>
            </ol>
        </div>
    </div>
</body>
</html>
