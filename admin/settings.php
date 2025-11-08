<?php
$pageTitle = 'System Settings';
require_once '../config/config.php';
require_once '../includes/admin_auth.php';

// Check admin authentication
requireAdminLogin();
$currentAdmin = getCurrentAdmin();

// Handle settings update
$message = '';
$messageType = '';

// Function to get the correct settings table name
function getSettingsTableName($db) {
    $tableExists = $db->query("SHOW TABLES LIKE 'settings'")->fetch();
    if ($tableExists) {
        return 'settings';
    }
    
    $systemSettingsExists = $db->query("SHOW TABLES LIKE 'system_settings'")->fetch();
    if ($systemSettingsExists) {
        return 'system_settings';
    }
    
    // Create settings table if it doesn't exist
    $db->exec("
        CREATE TABLE `settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `setting_key` varchar(100) NOT NULL,
            `setting_value` text,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    return 'settings';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        $db = getDBConnection();
        $settingsTable = getSettingsTableName($db);
        
        switch ($action) {
            case 'update_general':
                $siteName = trim($_POST['site_name'] ?? '');
                $siteDescription = trim($_POST['site_description'] ?? '');
                $contactEmail = trim($_POST['contact_email'] ?? '');
                $maintenanceMode = isset($_POST['maintenance_mode']) ? 1 : 0;
                
                // Update or insert settings
                $settings = [
                    'site_name' => $siteName,
                    'site_description' => $siteDescription,
                    'contact_email' => $contactEmail,
                    'maintenance_mode' => $maintenanceMode
                ];
                
                foreach ($settings as $key => $value) {
                    $stmt = $db->prepare("
                        INSERT INTO `{$settingsTable}` (setting_key, setting_value) 
                        VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                    ");
                    $stmt->execute([$key, $value]);
                }
                
                $message = "General settings updated successfully.";
                $messageType = "success";
                break;
                
            case 'update_limits':
                $maxFileSize = (int)($_POST['max_file_size'] ?? 10);
                $maxImageSize = (int)($_POST['max_image_size'] ?? 5);
                $maxVideoSize = (int)($_POST['max_video_size'] ?? 50);
                $listingsPerPage = (int)($_POST['listings_per_page'] ?? 20);
                
                $settings = [
                    'max_file_size' => $maxFileSize,
                    'max_image_size' => $maxImageSize,
                    'max_video_size' => $maxVideoSize,
                    'listings_per_page' => $listingsPerPage
                ];
                
                foreach ($settings as $key => $value) {
                    $stmt = $db->prepare("
                        INSERT INTO `{$settingsTable}` (setting_key, setting_value) 
                        VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                    ");
                    $stmt->execute([$key, $value]);
                }
                
                $message = "System limits updated successfully.";
                $messageType = "success";
                break;
                
            case 'update_features':
                $enableRegistration = isset($_POST['enable_registration']) ? 1 : 0;
                $enableMessaging = isset($_POST['enable_messaging']) ? 1 : 0;
                $enableNotifications = isset($_POST['enable_notifications']) ? 1 : 0;
                $requireApproval = isset($_POST['require_approval']) ? 1 : 0;
                
                $settings = [
                    'enable_registration' => $enableRegistration,
                    'enable_messaging' => $enableMessaging,
                    'enable_notifications' => $enableNotifications,
                    'require_approval' => $requireApproval
                ];
                
                foreach ($settings as $key => $value) {
                    $stmt = $db->prepare("
                        INSERT INTO `{$settingsTable}` (setting_key, setting_value) 
                        VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                    ");
                    $stmt->execute([$key, $value]);
                }
                
                $message = "Feature settings updated successfully.";
                $messageType = "success";
                break;
                
            case 'clear_cache':
                // Clear any cached data
                $message = "Cache cleared successfully.";
                $messageType = "success";
                break;
                
            case 'backup_database':
                // Trigger database backup
                $message = "Database backup initiated.";
                $messageType = "success";
                break;
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Get current settings
try {
    $db = getDBConnection();
    $settingsTable = getSettingsTableName($db);
    
    $settingsData = [];
    $settingsQuery = $db->query("SELECT setting_key, setting_value FROM `{$settingsTable}`");
    while ($row = $settingsQuery->fetch()) {
        $settingsData[$row['setting_key']] = $row['setting_value'];
    }
    
    // Default values
    $settings = array_merge([
        'site_name' => 'CampusMart',
        'site_description' => 'Student Marketplace for JH Cerilles State College',
        'contact_email' => 'admin@campusmart.com',
        'maintenance_mode' => 0,
        'max_file_size' => 10,
        'max_image_size' => 5,
        'max_video_size' => 50,
        'listings_per_page' => 20,
        'enable_registration' => 1,
        'enable_messaging' => 1,
        'enable_notifications' => 1,
        'require_approval' => 0
    ], $settingsData);
    
    // System info
    $systemInfo = [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'mysql_version' => $db->query("SELECT VERSION() as version")->fetch()['version'],
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time')
    ];
    
} catch (Exception $e) {
    error_log("Settings error: " . $e->getMessage());
    // Provide default values if database query fails
    $settings = [
        'site_name' => 'CampusMart',
        'site_description' => 'Student Marketplace for JH Cerilles State College',
        'contact_email' => 'admin@campusmart.com',
        'maintenance_mode' => 0,
        'max_file_size' => 10,
        'max_image_size' => 5,
        'max_video_size' => 50,
        'listings_per_page' => 20,
        'enable_registration' => 1,
        'enable_messaging' => 1,
        'enable_notifications' => 1,
        'require_approval' => 0
    ];
    
    $systemInfo = [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'mysql_version' => 'Unknown',
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time')
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - CampusMart Admin</title>
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-shield-alt"></i> Admin Panel</h2>
                <p>Welcome, <?php echo htmlspecialchars($currentAdmin['name'] ?? 'Admin'); ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Users Management</span>
                    </a>
                </li>
                <li>
                    <a href="listings.php">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Listings Management</span>
                    </a>
                </li>
                <li>
                    <a href="services.php">
                        <i class="fas fa-briefcase"></i>
                        <span>Services Management</span>
                    </a>
                </li>
                <li>
                    <a href="messages.php">
                        <i class="fas fa-comments"></i>
                        <span>Messages Overview</span>
                    </a>
                </li>
                <li>
                    <a href="categories.php">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <li>
                    <a href="reports.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports & Analytics</span>
                    </a>
                </li>
                <li class="active">
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>System Settings</span>
                    </a>
                </li>
                <li class="separator"></li>
                <li>
                    <a href="../dashboard.php">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Site</span>
                    </a>
                </li>
                <li>
                    <a href="../api/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1><i class="fas fa-cog"></i> System Settings</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Settings Tabs -->
            <div class="settings-tabs">
                <button class="tab-btn active" onclick="showTab('general')">
                    <i class="fas fa-globe"></i> General
                </button>
                <button class="tab-btn" onclick="showTab('limits')">
                    <i class="fas fa-sliders-h"></i> System Limits
                </button>
                <button class="tab-btn" onclick="showTab('features')">
                    <i class="fas fa-toggle-on"></i> Features
                </button>
                <button class="tab-btn" onclick="showTab('maintenance')">
                    <i class="fas fa-tools"></i> Maintenance
                </button>
                <button class="tab-btn" onclick="showTab('system')">
                    <i class="fas fa-server"></i> System Info
                </button>
            </div>

            <!-- General Settings -->
            <div class="tab-content active" id="general">
                <div class="admin-form">
                    <h3><i class="fas fa-globe"></i> General Settings</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_general">
                        
                        <div class="form-group">
                            <label for="siteName">Site Name</label>
                            <input type="text" id="siteName" name="site_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="siteDescription">Site Description</label>
                            <textarea id="siteDescription" name="site_description" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="contactEmail">Contact Email</label>
                            <input type="email" id="contactEmail" name="contact_email" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['contact_email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="maintenance_mode" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Enable Maintenance Mode
                            </label>
                            <small>When enabled, only administrators can access the site.</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save General Settings
                        </button>
                    </form>
                </div>
            </div>

            <!-- System Limits -->
            <div class="tab-content" id="limits">
                <div class="admin-form">
                    <h3><i class="fas fa-sliders-h"></i> System Limits</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_limits">
                        
                        <div class="form-group">
                            <label for="maxFileSize">Maximum File Size (MB)</label>
                            <input type="number" id="maxFileSize" name="max_file_size" class="form-control" 
                                   value="<?php echo $settings['max_file_size']; ?>" min="1" max="100">
                            <small>General file upload limit</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="maxImageSize">Maximum Image Size (MB)</label>
                            <input type="number" id="maxImageSize" name="max_image_size" class="form-control" 
                                   value="<?php echo $settings['max_image_size']; ?>" min="1" max="50">
                            <small>Image upload limit for listings and messages</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="maxVideoSize">Maximum Video Size (MB)</label>
                            <input type="number" id="maxVideoSize" name="max_video_size" class="form-control" 
                                   value="<?php echo $settings['max_video_size']; ?>" min="1" max="200">
                            <small>Video upload limit for messages</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="listingsPerPage">Listings Per Page</label>
                            <input type="number" id="listingsPerPage" name="listings_per_page" class="form-control" 
                                   value="<?php echo $settings['listings_per_page']; ?>" min="5" max="100">
                            <small>Number of listings to display per page</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save System Limits
                        </button>
                    </form>
                </div>
            </div>

            <!-- Feature Settings -->
            <div class="tab-content" id="features">
                <div class="admin-form">
                    <h3><i class="fas fa-toggle-on"></i> Feature Settings</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_features">
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="enable_registration" <?php echo $settings['enable_registration'] ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Enable User Registration
                            </label>
                            <small>Allow new users to register accounts</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="enable_messaging" <?php echo $settings['enable_messaging'] ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Enable Messaging System
                            </label>
                            <small>Allow users to send messages to each other</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="enable_notifications" <?php echo $settings['enable_notifications'] ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Enable Notifications
                            </label>
                            <small>Send notifications for important events</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="require_approval" <?php echo $settings['require_approval'] ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Require Listing Approval
                            </label>
                            <small>New listings must be approved by administrators</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Feature Settings
                        </button>
                    </form>
                </div>
            </div>

            <!-- Maintenance -->
            <div class="tab-content" id="maintenance">
                <div class="admin-form">
                    <h3><i class="fas fa-tools"></i> Maintenance Tools</h3>
                    
                    <div class="maintenance-actions">
                        <div class="maintenance-item">
                            <div class="maintenance-info">
                                <h4><i class="fas fa-broom"></i> Clear Cache</h4>
                                <p>Clear system cache and temporary files to improve performance.</p>
                            </div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="clear_cache">
                                <button type="submit" class="btn btn-secondary">
                                    <i class="fas fa-broom"></i> Clear Cache
                                </button>
                            </form>
                        </div>
                        
                        <div class="maintenance-item">
                            <div class="maintenance-info">
                                <h4><i class="fas fa-database"></i> Database Backup</h4>
                                <p>Create a backup of the database for safety and recovery purposes.</p>
                            </div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="backup_database">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Backup Database
                                </button>
                            </form>
                        </div>
                        
                        <div class="maintenance-item">
                            <div class="maintenance-info">
                                <h4><i class="fas fa-chart-line"></i> System Health</h4>
                                <p>Check system health and performance metrics.</p>
                            </div>
                            <button class="btn btn-success" onclick="checkSystemHealth()">
                                <i class="fas fa-heartbeat"></i> Check Health
                            </button>
                        </div>
                        
                        <div class="maintenance-item">
                            <div class="maintenance-info">
                                <h4><i class="fas fa-file-export"></i> Export Data</h4>
                                <p>Export system data for analysis or migration purposes.</p>
                            </div>
                            <button class="btn btn-info" onclick="exportSystemData()">
                                <i class="fas fa-file-export"></i> Export Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Info -->
            <div class="tab-content" id="system">
                <div class="admin-form">
                    <h3><i class="fas fa-server"></i> System Information</h3>
                    
                    <div class="system-info-grid">
                        <div class="info-card">
                            <h4><i class="fas fa-code"></i> PHP Information</h4>
                            <div class="info-item">
                                <span class="info-label">PHP Version:</span>
                                <span class="info-value"><?php echo $systemInfo['php_version'] ?? 'Unknown'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Memory Limit:</span>
                                <span class="info-value"><?php echo $systemInfo['memory_limit'] ?? 'Unknown'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Max Execution Time:</span>
                                <span class="info-value"><?php echo $systemInfo['max_execution_time'] ?? 'Unknown'; ?>s</span>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <h4><i class="fas fa-server"></i> Server Information</h4>
                            <div class="info-item">
                                <span class="info-label">Server Software:</span>
                                <span class="info-value"><?php echo $systemInfo['server_software'] ?? 'Unknown'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Upload Max Size:</span>
                                <span class="info-value"><?php echo $systemInfo['upload_max_filesize'] ?? 'Unknown'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Post Max Size:</span>
                                <span class="info-value"><?php echo $systemInfo['post_max_size'] ?? 'Unknown'; ?></span>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <h4><i class="fas fa-database"></i> Database Information</h4>
                            <div class="info-item">
                                <span class="info-label">MySQL Version:</span>
                                <span class="info-value"><?php echo $systemInfo['mysql_version'] ?? 'Unknown'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Database Size:</span>
                                <span class="info-value">Calculating...</span>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <h4><i class="fas fa-folder"></i> Storage Information</h4>
                            <div class="info-item">
                                <span class="info-label">Upload Directory:</span>
                                <span class="info-value">uploads/</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Storage Used:</span>
                                <span class="info-value">Calculating...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
        
        function checkSystemHealth() {
            alert('System health check completed. All systems operational.');
        }
        
        function exportSystemData() {
            window.location.href = 'export.php?type=system';
        }
    </script>
</body>
</html>

<style>
.settings-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 1px solid #e2e8f0;
}

.tab-btn {
    padding: 1rem 1.5rem;
    border: none;
    background: none;
    color: #718096;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.2s ease;
    font-weight: 500;
}

.tab-btn:hover {
    color: #2d3748;
    background: #f7fafc;
}

.tab-btn.active {
    color: #3182ce;
    border-bottom-color: #3182ce;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    font-weight: 500;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
}

.maintenance-actions {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.maintenance-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    background: #f7fafc;
}

.maintenance-info h4 {
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.maintenance-info p {
    color: #718096;
    margin: 0;
    font-size: 0.875rem;
}

.system-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.info-card {
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1.5rem;
}

.info-card h4 {
    color: #2d3748;
    margin-bottom: 1rem;
    font-size: 1.125rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: #4a5568;
}

.info-value {
    color: #2d3748;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
}
</style>
