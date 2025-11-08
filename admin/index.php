<?php
$pageTitle = 'Admin Dashboard';
require_once '../config/config.php';
require_once '../includes/admin_auth.php';

// Check admin authentication
requireAdminLogin();
$currentAdmin = getCurrentAdmin();

// Get dashboard statistics
try {
    $db = getDBConnection();
    
    // Users statistics
    $userStats = $db->query("
        SELECT 
            COUNT(*) as total_users,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_users,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_30d
        FROM users
    ")->fetch();
    
    // Listings statistics
    $listingStats = $db->query("
        SELECT 
            COUNT(*) as total_listings,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_listings,
            COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_listings,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_listings_30d
        FROM listings
    ")->fetch();
    
    // Messages statistics
    $messageStats = $db->query("
        SELECT 
            COUNT(*) as total_messages,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as messages_24h,
            COUNT(CASE WHEN message_type = 'image' THEN 1 END) as image_messages,
            COUNT(CASE WHEN message_type = 'video' THEN 1 END) as video_messages
        FROM messages
    ")->fetch();
    
    // Services statistics
    $serviceStats = $db->query("
        SELECT 
            COUNT(*) as total_services,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_services,
            COUNT(CASE WHEN service_type = 'tutoring' THEN 1 END) as tutoring_services,
            COUNT(CASE WHEN service_type = 'freelance' THEN 1 END) as freelance_services
        FROM services
    ")->fetch();
    
    // Recent activities
    $recentUsers = $db->query("
        SELECT id, username, first_name, last_name, email, created_at, status
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    $recentListings = $db->query("
        SELECT l.id, l.title, l.price, l.created_at, l.status, u.username
        FROM listings l
        JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $userStats = $listingStats = $messageStats = $serviceStats = [];
    $recentUsers = $recentListings = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - CampusMart</title>
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-shield-alt"></i> Admin Panel</h2>
                <p>Welcome, <?php echo htmlspecialchars($currentAdmin['name']); ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li class="active">
                    <a href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Users Management</span>
                        <span class="badge"><?php echo $userStats['total_users'] ?? 0; ?></span>
                    </a>
                </li>
                <li>
                    <a href="listings.php">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Listings Management</span>
                        <span class="badge"><?php echo $listingStats['total_listings'] ?? 0; ?></span>
                    </a>
                </li>
                <li>
                    <a href="services.php">
                        <i class="fas fa-briefcase"></i>
                        <span>Services Management</span>
                        <span class="badge"><?php echo $serviceStats['total_services'] ?? 0; ?></span>
                    </a>
                </li>
                <li>
                    <a href="messages.php">
                        <i class="fas fa-comments"></i>
                        <span>Messages Overview</span>
                        <span class="badge"><?php echo $messageStats['total_messages'] ?? 0; ?></span>
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
                <li>
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
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card users">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($userStats['total_users'] ?? 0); ?></h3>
                        <p>Total Users</p>
                        <div class="stat-details">
                            <span class="active"><?php echo $userStats['active_users'] ?? 0; ?> Active</span>
                            <span class="pending"><?php echo $userStats['pending_users'] ?? 0; ?> Pending</span>
                        </div>
                    </div>
                    <div class="stat-trend">
                        <span class="trend-value">+<?php echo $userStats['new_users_30d'] ?? 0; ?></span>
                        <span class="trend-label">This month</span>
                    </div>
                </div>

                <div class="stat-card listings">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($listingStats['total_listings'] ?? 0); ?></h3>
                        <p>Total Listings</p>
                        <div class="stat-details">
                            <span class="active"><?php echo $listingStats['active_listings'] ?? 0; ?> Active</span>
                            <span class="sold"><?php echo $listingStats['sold_listings'] ?? 0; ?> Sold</span>
                        </div>
                    </div>
                    <div class="stat-trend">
                        <span class="trend-value">+<?php echo $listingStats['new_listings_30d'] ?? 0; ?></span>
                        <span class="trend-label">This month</span>
                    </div>
                </div>

                <div class="stat-card messages">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($messageStats['total_messages'] ?? 0); ?></h3>
                        <p>Total Messages</p>
                        <div class="stat-details">
                            <span class="images"><?php echo $messageStats['image_messages'] ?? 0; ?> Images</span>
                            <span class="videos"><?php echo $messageStats['video_messages'] ?? 0; ?> Videos</span>
                        </div>
                    </div>
                    <div class="stat-trend">
                        <span class="trend-value">+<?php echo $messageStats['messages_24h'] ?? 0; ?></span>
                        <span class="trend-label">Last 24h</span>
                    </div>
                </div>

                <div class="stat-card services">
                    <div class="stat-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($serviceStats['total_services'] ?? 0); ?></h3>
                        <p>Total Services</p>
                        <div class="stat-details">
                            <span class="tutoring"><?php echo $serviceStats['tutoring_services'] ?? 0; ?> Tutoring</span>
                            <span class="freelance"><?php echo $serviceStats['freelance_services'] ?? 0; ?> Freelance</span>
                        </div>
                    </div>
                    <div class="stat-trend">
                        <span class="trend-value"><?php echo $serviceStats['active_services'] ?? 0; ?></span>
                        <span class="trend-label">Active</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-plus"></i> Recent Users</h3>
                        <a href="users.php" class="view-all">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if (empty($recentUsers)): ?>
                            <p class="no-data">No recent users found.</p>
                        <?php else: ?>
                            <div class="activity-list">
                                <?php foreach ($recentUsers as $user): ?>
                                    <div class="activity-item">
                                        <div class="activity-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="activity-content">
                                            <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                            <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                                            <small><?php echo htmlspecialchars($user['email']); ?></small>
                                        </div>
                                        <div class="activity-meta">
                                            <span class="status status-<?php echo $user['status']; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                            <small><?php echo date('M j, Y', strtotime($user['created_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-shopping-bag"></i> Recent Listings</h3>
                        <a href="listings.php" class="view-all">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if (empty($recentListings)): ?>
                            <p class="no-data">No recent listings found.</p>
                        <?php else: ?>
                            <div class="activity-list">
                                <?php foreach ($recentListings as $listing): ?>
                                    <div class="activity-item">
                                        <div class="activity-avatar">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <div class="activity-content">
                                            <h4><?php echo htmlspecialchars($listing['title']); ?></h4>
                                            <p>by @<?php echo htmlspecialchars($listing['username']); ?></p>
                                            <small>₱<?php echo number_format($listing['price'], 2); ?></small>
                                        </div>
                                        <div class="activity-meta">
                                            <span class="status status-<?php echo $listing['status']; ?>">
                                                <?php echo ucfirst($listing['status']); ?>
                                            </span>
                                            <small><?php echo date('M j, Y', strtotime($listing['created_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                <div class="actions-grid">
                    <a href="users.php?action=pending" class="action-card">
                        <i class="fas fa-user-clock"></i>
                        <span>Review Pending Users</span>
                        <small><?php echo $userStats['pending_users'] ?? 0; ?> pending</small>
                    </a>
                    <a href="listings.php?status=reported" class="action-card">
                        <i class="fas fa-flag"></i>
                        <span>Review Reported Content</span>
                        <small>Check reports</small>
                    </a>
                    <a href="categories.php" class="action-card">
                        <i class="fas fa-tags"></i>
                        <span>Manage Categories</span>
                        <small>Add/Edit categories</small>
                    </a>
                    <a href="settings.php" class="action-card">
                        <i class="fas fa-cog"></i>
                        <span>System Settings</span>
                        <small>Configure system</small>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        function refreshDashboard() {
            location.reload();
        }

        // Auto-refresh dashboard every 5 minutes
        setInterval(refreshDashboard, 300000);

        // Add smooth transitions
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .dashboard-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in');
            });
        });
    </script>
</body>
</html>
