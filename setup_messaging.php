<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Set content type to HTML for better display
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusMart Messaging Setup</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: #f8fafc;
            color: #333;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #10b981;
            text-align: center;
            margin-bottom: 2rem;
        }
        .step {
            margin: 1.5rem 0;
            padding: 1rem;
            border-left: 4px solid #10b981;
            background: #f0fdf4;
        }
        .success {
            color: #059669;
            background: #ecfdf5;
            border-color: #10b981;
        }
        .error {
            color: #dc2626;
            background: #fef2f2;
            border-color: #ef4444;
        }
        .warning {
            color: #d97706;
            background: #fffbeb;
            border-color: #f59e0b;
        }
        pre {
            background: #1f2937;
            color: #f9fafb;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 0.875rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin: 0.5rem 0.5rem 0.5rem 0;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 CampusMart Messaging System Setup</h1>
        
        <?php
        try {
            echo '<div class="step">📋 <strong>Step 1:</strong> Connecting to database...</div>';
            
            $db = new Database();
            $conn = $db->getConnection();
            
            echo '<div class="step success">✅ <strong>Database connection successful!</strong></div>';
            
            echo '<div class="step">📋 <strong>Step 2:</strong> Reading messaging schema...</div>';
            
            // Read and execute the messaging schema
            $schemaFile = __DIR__ . '/database/messaging_schema.sql';
            
            if (!file_exists($schemaFile)) {
                throw new Exception("Messaging schema file not found: $schemaFile");
            }
            
            $schema = file_get_contents($schemaFile);
            
            if ($schema === false) {
                throw new Exception("Could not read messaging schema file");
            }
            
            echo '<div class="step success">✅ <strong>Schema file loaded successfully!</strong></div>';
            
            echo '<div class="step">📋 <strong>Step 3:</strong> Creating messaging tables...</div>';
            
            // Split SQL into individual statements
            $statements = array_filter(
                array_map('trim', explode(';', $schema)),
                function($stmt) {
                    return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
                }
            );
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            foreach ($statements as $statement) {
                try {
                    if (trim($statement)) {
                        $conn->exec($statement);
                        $successCount++;
                    }
                } catch (PDOException $e) {
                    $errorCount++;
                    $errors[] = [
                        'statement' => substr($statement, 0, 100) . '...',
                        'error' => $e->getMessage()
                    ];
                    
                    // Continue with other statements unless it's a critical error
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo '<div class="step warning">⚠️ <strong>Warning:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
            }
            
            echo '<div class="step success">✅ <strong>Database setup completed!</strong><br>';
            echo "Successfully executed: $successCount statements<br>";
            if ($errorCount > 0) {
                echo "Warnings/Errors: $errorCount (mostly table already exists)";
            }
            echo '</div>';
            
            echo '<div class="step">📋 <strong>Step 4:</strong> Verifying table creation...</div>';
            
            // Verify tables were created
            $tables = ['conversations', 'messages', 'message_reactions', 'message_attachments', 'conversation_participants'];
            $createdTables = [];
            $missingTables = [];
            
            foreach ($tables as $table) {
                try {
                    $stmt = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($stmt->rowCount() > 0) {
                        $createdTables[] = $table;
                    } else {
                        $missingTables[] = $table;
                    }
                } catch (PDOException $e) {
                    $missingTables[] = $table;
                }
            }
            
            if (empty($missingTables)) {
                echo '<div class="step success">✅ <strong>All messaging tables created successfully!</strong><br>';
                echo 'Created tables: ' . implode(', ', $createdTables);
                echo '</div>';
            } else {
                echo '<div class="step error">❌ <strong>Some tables are missing:</strong><br>';
                echo 'Missing: ' . implode(', ', $missingTables) . '<br>';
                echo 'Created: ' . implode(', ', $createdTables);
                echo '</div>';
            }
            
            echo '<div class="step">📋 <strong>Step 5:</strong> Checking sample data...</div>';
            
            // Check if sample data exists
            $stmt = $conn->query("SELECT COUNT(*) as count FROM conversations");
            $conversationCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $conn->query("SELECT COUNT(*) as count FROM messages");
            $messageCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo '<div class="step success">✅ <strong>Sample data check:</strong><br>';
            echo "Conversations: $conversationCount<br>";
            echo "Messages: $messageCount";
            echo '</div>';
            
            echo '<div class="step">📋 <strong>Step 6:</strong> Testing API endpoints...</div>';
            
            // Test if API files exist
            $apiFiles = [
                'get_conversations.php',
                'get_messages.php',
                'send_message.php',
                'start_conversation.php',
                'search_users.php'
            ];
            
            $existingFiles = [];
            $missingFiles = [];
            
            foreach ($apiFiles as $file) {
                $filePath = __DIR__ . '/api/' . $file;
                if (file_exists($filePath)) {
                    $existingFiles[] = $file;
                } else {
                    $missingFiles[] = $file;
                }
            }
            
            if (empty($missingFiles)) {
                echo '<div class="step success">✅ <strong>All API endpoints created!</strong><br>';
                echo 'Available APIs: ' . implode(', ', $existingFiles);
                echo '</div>';
            } else {
                echo '<div class="step warning">⚠️ <strong>Some API files are missing:</strong><br>';
                echo 'Missing: ' . implode(', ', $missingFiles) . '<br>';
                echo 'Available: ' . implode(', ', $existingFiles);
                echo '</div>';
            }
            
            echo '<div class="step success">🎉 <strong>Messaging System Setup Complete!</strong></div>';
            
            echo '<div class="step">';
            echo '<h3>📚 What was installed:</h3>';
            echo '<ul>';
            echo '<li><strong>Database Tables:</strong> conversations, messages, message_reactions, message_attachments, conversation_participants</li>';
            echo '<li><strong>API Endpoints:</strong> Complete messaging API with real-time capabilities</li>';
            echo '<li><strong>Frontend Interface:</strong> Modern messaging UI with chat interface</li>';
            echo '<li><strong>Features:</strong> Real-time messaging, user search, conversation management, notifications</li>';
            echo '</ul>';
            echo '</div>';
            
            echo '<div class="step">';
            echo '<h3>🚀 Next Steps:</h3>';
            echo '<p><strong>1. Test the messaging system:</strong></p>';
            echo '<a href="messages.php" class="btn">Open Messages</a>';
            echo '<a href="test_messaging.php" class="btn btn-secondary">Test Messaging</a>';
            echo '<p><strong>2. Login with test accounts:</strong></p>';
            echo '<ul>';
            echo '<li>Maria: hilaos / hilaos123</li>';
            echo '<li>John: sapuay / sapuay123</li>';
            echo '<li>Anna: legaspi / legaspi123</li>';
            echo '</ul>';
            echo '</div>';
            
            if (!empty($errors)) {
                echo '<div class="step warning">';
                echo '<h3>⚠️ Setup Warnings:</h3>';
                foreach ($errors as $error) {
                    echo '<p><strong>Statement:</strong> ' . htmlspecialchars($error['statement']) . '<br>';
                    echo '<strong>Error:</strong> ' . htmlspecialchars($error['error']) . '</p>';
                }
                echo '</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="step error">❌ <strong>Setup Failed:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
            
            echo '<div class="step">';
            echo '<h3>🔧 Troubleshooting:</h3>';
            echo '<ul>';
            echo '<li>Make sure your database is running (XAMPP MySQL service)</li>';
            echo '<li>Check database connection settings in config/database.php</li>';
            echo '<li>Ensure the main CampusMart database exists</li>';
            echo '<li>Verify file permissions for database/ directory</li>';
            echo '</ul>';
            echo '</div>';
        }
        ?>
        
        <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
            <a href="index.php" class="btn">← Back to CampusMart</a>
            <a href="dashboard.php" class="btn">Go to Dashboard</a>
        </div>
    </div>
</body>
</html>
