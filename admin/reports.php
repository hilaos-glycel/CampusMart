<?php
$pageTitle = 'Reports & Analytics';
require_once '../config/config.php';
require_once '../includes/admin_auth.php';

// Check admin authentication
requireAdminLogin();
$currentAdmin = getCurrentAdmin();

try {
    $db = getDBConnection();
    
    // User Analytics
    $userAnalytics = $db->query("
        SELECT 
            COUNT(*) as total_users,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_30d,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_users_7d,
            COUNT(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as active_users_30d,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_users
        FROM users
    ")->fetch();
    
    // Listing Analytics
    $listingAnalytics = $db->query("
        SELECT 
            COUNT(*) as total_listings,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_listings_30d,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_listings_7d,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_listings,
            COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_listings,
            AVG(price) as avg_price,
            MAX(price) as max_price,
            MIN(price) as min_price
        FROM listings
    ")->fetch();
    
    // Message Analytics
    $messageAnalytics = $db->query("
        SELECT 
            COUNT(*) as total_messages,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as messages_30d,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as messages_7d,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 1 END) as messages_24h,
            COUNT(CASE WHEN message_type = 'image' THEN 1 END) as image_messages,
            COUNT(CASE WHEN message_type = 'video' THEN 1 END) as video_messages
        FROM messages
    ")->fetch();
    
    // Top Categories
    $topCategories = $db->query("
        SELECT c.name, COUNT(l.id) as listing_count
        FROM categories c
        LEFT JOIN listings l ON c.id = l.category_id
        GROUP BY c.id, c.name
        ORDER BY listing_count DESC
        LIMIT 10
    ")->fetchAll();
    
    // Top Users by Listings
    $topSellers = $db->query("
        SELECT u.first_name, u.last_name, u.username, COUNT(l.id) as listing_count
        FROM users u
        LEFT JOIN listings l ON u.id = l.user_id
        GROUP BY u.id
        ORDER BY listing_count DESC
        LIMIT 10
    ")->fetchAll();
    
    // Recent Activity
    $recentActivity = $db->query("
        SELECT 'user_registered' as type, CONCAT(first_name, ' ', last_name) as description, created_at
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        UNION ALL
        SELECT 'listing_created' as type, title as description, created_at
        FROM listings 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY created_at DESC
        LIMIT 20
    ")->fetchAll();
    
    // Monthly Growth Data (last 12 months)
    $monthlyGrowth = $db->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(CASE WHEN 'users' THEN 1 END) as users,
            0 as listings
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        UNION ALL
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            0 as users,
            COUNT(*) as listings
        FROM listings 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 12
    ")->fetchAll();
    
} catch (Exception $e) {
    error_log("Reports error: " . $e->getMessage());
    $userAnalytics = $listingAnalytics = $messageAnalytics = [];
    $topCategories = $topSellers = $recentActivity = $monthlyGrowth = [];
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li class="active">
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
                <h1><i class="fas fa-chart-bar"></i> Reports & Analytics</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="exportReport()">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="stats-grid">
                <div class="stat-card users">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($userAnalytics['total_users'] ?? 0); ?></h3>
                        <p>Total Users</p>
                        <div class="stat-details">
                            <span class="active">+<?php echo $userAnalytics['new_users_30d'] ?? 0; ?> this month</span>
                        </div>
                    </div>
                    <div class="stat-trend">
                        <span class="trend-value">+<?php echo $userAnalytics['new_users_7d'] ?? 0; ?></span>
                        <span class="trend-label">This week</span>
                    </div>
                </div>

                <div class="stat-card listings">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($listingAnalytics['total_listings'] ?? 0); ?></h3>
                        <p>Total Listings</p>
                        <div class="stat-details">
                            <span class="active">+<?php echo $listingAnalytics['new_listings_30d'] ?? 0; ?> this month</span>
                        </div>
                    </div>
                    <div class="stat-trend">
                        <span class="trend-value">₱<?php echo number_format($listingAnalytics['avg_price'] ?? 0, 0); ?></span>
                        <span class="trend-label">Avg Price</span>
                    </div>
                </div>

                <div class="stat-card messages">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($messageAnalytics['total_messages'] ?? 0); ?></h3>
                        <p>Total Messages</p>
                        <div class="stat-details">
                            <span class="images"><?php echo $messageAnalytics['image_messages'] ?? 0; ?> images</span>
                            <span class="videos"><?php echo $messageAnalytics['video_messages'] ?? 0; ?> videos</span>
                        </div>
                    </div>
                    <div class="stat-trend">
                        <span class="trend-value">+<?php echo $messageAnalytics['messages_24h'] ?? 0; ?></span>
                        <span class="trend-label">Last 24h</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #38a169;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format(($userAnalytics['active_users_30d'] ?? 0) / max(1, $userAnalytics['total_users'] ?? 1) * 100, 1); ?>%</h3>
                        <p>User Engagement</p>
                        <div class="stat-details">
                            <span class="active"><?php echo $userAnalytics['active_users_30d'] ?? 0; ?> active users</span>
                        </div>
                    </div>
                    <div class="stat-trend">
                        <span class="trend-value"><?php echo $userAnalytics['active_users'] ?? 0; ?></span>
                        <span class="trend-label">Total Active</span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> Growth Trends</h3>
                    </div>
                    <div class="card-content">
                        <canvas id="growthChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-pie"></i> Category Distribution</h3>
                    </div>
                    <div class="card-content">
                        <canvas id="categoryChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Lists -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-tags"></i> Top Categories</h3>
                    </div>
                    <div class="card-content">
                        <?php if (empty($topCategories)): ?>
                            <p class="no-data">No category data available.</p>
                        <?php else: ?>
                            <div class="top-list">
                                <?php foreach ($topCategories as $index => $category): ?>
                                    <div class="top-item">
                                        <div class="rank">#<?php echo $index + 1; ?></div>
                                        <div class="item-content">
                                            <h4><?php echo htmlspecialchars($category['name']); ?></h4>
                                            <p><?php echo $category['listing_count']; ?> listings</p>
                                        </div>
                                        <div class="item-progress">
                                            <div class="progress-bar" style="width: <?php echo min(100, ($category['listing_count'] / max(1, $topCategories[0]['listing_count'])) * 100); ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-tie"></i> Top Sellers</h3>
                    </div>
                    <div class="card-content">
                        <?php if (empty($topSellers)): ?>
                            <p class="no-data">No seller data available.</p>
                        <?php else: ?>
                            <div class="top-list">
                                <?php foreach ($topSellers as $index => $seller): ?>
                                    <?php if ($seller['listing_count'] > 0): ?>
                                        <div class="top-item">
                                            <div class="rank">#<?php echo $index + 1; ?></div>
                                            <div class="item-content">
                                                <h4><?php echo htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']); ?></h4>
                                                <p>@<?php echo htmlspecialchars($seller['username']); ?> • <?php echo $seller['listing_count']; ?> listings</p>
                                            </div>
                                            <div class="item-progress">
                                                <div class="progress-bar" style="width: <?php echo min(100, ($seller['listing_count'] / max(1, $topSellers[0]['listing_count'])) * 100); ?>%"></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Recent Activity (Last 7 Days)</h3>
                </div>
                <div class="card-content">
                    <?php if (empty($recentActivity)): ?>
                        <p class="no-data">No recent activity.</p>
                    <?php else: ?>
                        <div class="activity-timeline">
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="timeline-item">
                                    <div class="timeline-icon">
                                        <?php if ($activity['type'] === 'user_registered'): ?>
                                            <i class="fas fa-user-plus"></i>
                                        <?php else: ?>
                                            <i class="fas fa-plus"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-content">
                                        <p>
                                            <?php if ($activity['type'] === 'user_registered'): ?>
                                                <strong><?php echo htmlspecialchars($activity['description']); ?></strong> registered
                                            <?php else: ?>
                                                New listing: <strong><?php echo htmlspecialchars($activity['description']); ?></strong>
                                            <?php endif; ?>
                                        </p>
                                        <small><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Growth Chart
        const growthCtx = document.getElementById('growthChart').getContext('2d');
        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'New Users',
                    data: [12, 19, 15, 25, 22, 30, 28, 35, 32, 40, 38, 45],
                    borderColor: '#3182ce',
                    backgroundColor: 'rgba(49, 130, 206, 0.1)',
                    tension: 0.4
                }, {
                    label: 'New Listings',
                    data: [8, 15, 12, 20, 18, 25, 22, 28, 25, 32, 30, 35],
                    borderColor: '#38a169',
                    backgroundColor: 'rgba(56, 161, 105, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', array_map(function($cat) { return '"' . addslashes($cat['name']) . '"'; }, array_slice($topCategories, 0, 5))); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_map(function($cat) { return $cat['listing_count']; }, array_slice($topCategories, 0, 5))); ?>],
                    backgroundColor: [
                        '#3182ce',
                        '#38a169',
                        '#ed8936',
                        '#e53e3e',
                        '#805ad5'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        function exportReport() {
            window.location.href = 'export.php?type=report';
        }
    </script>
</body>
</html>

<style>
.top-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.top-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    border-radius: 0.5rem;
    background: #f7fafc;
}

.rank {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #3182ce;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.item-content {
    flex: 1;
}

.item-content h4 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.item-content p {
    font-size: 0.75rem;
    color: #718096;
    margin: 0;
}

.item-progress {
    width: 60px;
    height: 4px;
    background: #e2e8f0;
    border-radius: 2px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: #3182ce;
    border-radius: 2px;
    transition: width 0.3s ease;
}

.activity-timeline {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.timeline-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #718096;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.timeline-content p {
    margin: 0 0 0.25rem 0;
    font-size: 0.875rem;
    color: #2d3748;
}

.timeline-content small {
    color: #718096;
    font-size: 0.75rem;
}

canvas {
    max-height: 300px;
}
</style>
