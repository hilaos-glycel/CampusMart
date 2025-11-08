<?php
$pageTitle = 'Services Management';
require_once '../config/config.php';
require_once '../includes/admin_auth.php';

// Check admin authentication
requireAdminLogin();
$currentAdmin = getCurrentAdmin();

// Handle actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $serviceId = (int)($_POST['service_id'] ?? 0);
    
    try {
        $db = getDBConnection();
        
        switch ($action) {
            case 'approve':
                $stmt = $db->prepare("UPDATE services SET status = 'active' WHERE id = ?");
                $stmt->execute([$serviceId]);
                $message = "Service approved successfully.";
                $messageType = "success";
                break;
                
            case 'reject':
                $stmt = $db->prepare("UPDATE services SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$serviceId]);
                $message = "Service rejected successfully.";
                $messageType = "success";
                break;
                
            case 'feature':
                $stmt = $db->prepare("UPDATE services SET is_featured = 1 WHERE id = ?");
                $stmt->execute([$serviceId]);
                $message = "Service featured successfully.";
                $messageType = "success";
                break;
                
            case 'unfeature':
                $stmt = $db->prepare("UPDATE services SET is_featured = 0 WHERE id = ?");
                $stmt->execute([$serviceId]);
                $message = "Service unfeatured successfully.";
                $messageType = "success";
                break;
                
            case 'delete':
                $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
                $stmt->execute([$serviceId]);
                $message = "Service deleted successfully.";
                $messageType = "success";
                break;
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Get filters
$statusFilter = $_GET['status'] ?? 'all';
$typeFilter = $_GET['type'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$whereConditions = [];
$params = [];

if ($statusFilter !== 'all') {
    $whereConditions[] = "s.status = ?";
    $params[] = $statusFilter;
}

if ($typeFilter !== 'all') {
    $whereConditions[] = "s.service_type = ?";
    $params[] = $typeFilter;
}

if (!empty($searchQuery)) {
    $whereConditions[] = "(s.title LIKE ? OR s.description LIKE ? OR u.username LIKE ?)";
    $searchTerm = "%{$searchQuery}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

try {
    $db = getDBConnection();
    
    // Check if services table exists, if not create it
    $tableExists = $db->query("SHOW TABLES LIKE 'services'")->fetch();
    if (!$tableExists) {
        $createServicesTable = "
            CREATE TABLE services (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                service_type ENUM('tutoring', 'freelance', 'consultation', 'other') DEFAULT 'other',
                price DECIMAL(10,2),
                duration VARCHAR(100),
                availability TEXT,
                status ENUM('active', 'inactive', 'pending', 'rejected') DEFAULT 'pending',
                is_featured BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";
        $db->exec($createServicesTable);
        
        // Insert sample services
        $sampleServices = [
            [1, 'Math Tutoring', 'Expert math tutoring for all levels', 'tutoring', 500.00, '1 hour', 'Weekdays 3-6 PM', 'active'],
            [2, 'Web Development', 'Custom website development services', 'freelance', 15000.00, 'Project-based', 'Flexible schedule', 'active'],
            [3, 'English Tutoring', 'Improve your English skills', 'tutoring', 400.00, '1 hour', 'Weekends available', 'pending']
        ];
        
        foreach ($sampleServices as $service) {
            $stmt = $db->prepare("
                INSERT INTO services (user_id, title, description, service_type, price, duration, availability, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute($service);
        }
    }
    
    // Get total count
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM services s 
        JOIN users u ON s.user_id = u.id 
        {$whereClause}
    ";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($params);
    $totalServices = $countStmt->fetch()['total'];
    $totalPages = ceil($totalServices / $limit);
    
    // Get services
    $query = "
        SELECT s.*, u.username, u.first_name, u.last_name
        FROM services s
        JOIN users u ON s.user_id = u.id
        {$whereClause}
        ORDER BY s.created_at DESC 
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $services = $stmt->fetchAll();
    
    // Get statistics
    $stats = $db->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
            COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
            COUNT(CASE WHEN is_featured = 1 THEN 1 END) as featured,
            AVG(price) as avg_price
        FROM services
    ")->fetch();
    
} catch (Exception $e) {
    error_log("Services management error: " . $e->getMessage());
    $services = [];
    $stats = ['total' => 0, 'active' => 0, 'pending' => 0, 'rejected' => 0, 'featured' => 0, 'avg_price' => 0];
    $totalPages = 1;
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
                <li class="active">
                    <a href="services.php">
                        <i class="fas fa-briefcase"></i>
                        <span>Services Management</span>
                        <span class="badge"><?php echo $stats['total']; ?></span>
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
                <h1><i class="fas fa-briefcase"></i> Services Management</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="exportServices()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card services">
                    <div class="stat-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total']); ?></h3>
                        <p>Total Services</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #38a169;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['active']); ?></h3>
                        <p>Active Services</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ed8936;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['pending']); ?></h3>
                        <p>Pending Approval</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3182ce;">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['featured']); ?></h3>
                        <p>Featured Services</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-filter"></i> Filters</h3>
                </div>
                <div class="card-content">
                    <form method="GET" class="filters-form">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Service Type</label>
                                <select name="type" class="form-control">
                                    <option value="all" <?php echo $typeFilter === 'all' ? 'selected' : ''; ?>>All Types</option>
                                    <option value="tutoring" <?php echo $typeFilter === 'tutoring' ? 'selected' : ''; ?>>Tutoring</option>
                                    <option value="freelance" <?php echo $typeFilter === 'freelance' ? 'selected' : ''; ?>>Freelance</option>
                                    <option value="consultation" <?php echo $typeFilter === 'consultation' ? 'selected' : ''; ?>>Consultation</option>
                                    <option value="other" <?php echo $typeFilter === 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Title, description, provider..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="services.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Services Grid -->
            <div class="services-grid">
                <?php if (empty($services)): ?>
                    <div class="no-data" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                        <i class="fas fa-briefcase" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 1rem;"></i>
                        <h3>No services found</h3>
                        <p>No services match your current filters.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <div class="service-card">
                            <div class="service-header">
                                <div class="service-type">
                                    <?php
                                    $typeIcons = [
                                        'tutoring' => 'fas fa-graduation-cap',
                                        'freelance' => 'fas fa-laptop-code',
                                        'consultation' => 'fas fa-comments',
                                        'other' => 'fas fa-briefcase'
                                    ];
                                    $icon = $typeIcons[$service['service_type']] ?? 'fas fa-briefcase';
                                    ?>
                                    <i class="<?php echo $icon; ?>"></i>
                                    <span><?php echo ucfirst($service['service_type']); ?></span>
                                </div>
                                
                                <div class="service-badges">
                                    <span class="status-badge status-<?php echo $service['status']; ?>">
                                        <?php echo ucfirst($service['status']); ?>
                                    </span>
                                    <?php if ($service['is_featured']): ?>
                                        <span class="featured-badge">
                                            <i class="fas fa-star"></i> Featured
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="service-content">
                                <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                                <p class="service-price">₱<?php echo number_format($service['price'], 2); ?></p>
                                <p class="service-description"><?php echo htmlspecialchars(substr($service['description'], 0, 120)); ?>...</p>
                                
                                <div class="service-meta">
                                    <div class="provider-info">
                                        <i class="fas fa-user"></i>
                                        <span><?php echo htmlspecialchars($service['first_name'] . ' ' . $service['last_name']); ?></span>
                                    </div>
                                    <div class="duration-info">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo htmlspecialchars($service['duration']); ?></span>
                                    </div>
                                    <div class="date-info">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('M j, Y', strtotime($service['created_at'])); ?></span>
                                    </div>
                                </div>
                                
                                <div class="availability-info">
                                    <i class="fas fa-calendar-check"></i>
                                    <span><?php echo htmlspecialchars($service['availability']); ?></span>
                                </div>
                                
                                <div class="service-actions">
                                    <?php if ($service['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Reject this service?')">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($service['is_featured']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="unfeature">
                                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                            <button type="submit" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-star-half-alt"></i> Unfeature
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="feature">
                                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-star"></i> Feature
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this service? This action cannot be undone.')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $statusFilter; ?>&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $statusFilter; ?>&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function exportServices() {
            window.location.href = 'export.php?type=services';
        }
    </script>
</body>
</html>

<style>
.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.service-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.service-header {
    padding: 1.5rem 1.5rem 0;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.service-type {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #f7fafc;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #4a5568;
}

.service-type i {
    color: #667eea;
}

.service-badges {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-end;
}

.service-content {
    padding: 1.5rem;
}

.service-content h4 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.service-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 0.75rem;
}

.service-description {
    color: #718096;
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.service-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.75rem;
    color: #718096;
}

.service-meta > div {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.service-meta i {
    width: 12px;
    text-align: center;
}

.availability-info {
    background: #f7fafc;
    padding: 0.75rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    color: #4a5568;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.availability-info i {
    color: #667eea;
}

.service-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filters-form .form-group {
    margin-bottom: 0;
}
</style>
