<?php
$pageTitle = 'Listings Management';
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
    $listingId = (int)($_POST['listing_id'] ?? 0);
    
    try {
        $db = getDBConnection();
        
        switch ($action) {
            case 'approve':
                $stmt = $db->prepare("UPDATE listings SET status = 'active' WHERE id = ?");
                $stmt->execute([$listingId]);
                $message = "Listing approved successfully.";
                $messageType = "success";
                break;
                
            case 'reject':
                $stmt = $db->prepare("UPDATE listings SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$listingId]);
                $message = "Listing rejected successfully.";
                $messageType = "success";
                break;
                
            case 'feature':
                $stmt = $db->prepare("UPDATE listings SET is_featured = 1 WHERE id = ?");
                $stmt->execute([$listingId]);
                $message = "Listing featured successfully.";
                $messageType = "success";
                break;
                
            case 'unfeature':
                $stmt = $db->prepare("UPDATE listings SET is_featured = 0 WHERE id = ?");
                $stmt->execute([$listingId]);
                $message = "Listing unfeatured successfully.";
                $messageType = "success";
                break;
                
            case 'delete':
                $stmt = $db->prepare("DELETE FROM listings WHERE id = ?");
                $stmt->execute([$listingId]);
                $message = "Listing deleted successfully.";
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
$categoryFilter = $_GET['category'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$whereConditions = [];
$params = [];

if ($statusFilter !== 'all') {
    $whereConditions[] = "l.status = ?";
    $params[] = $statusFilter;
}

if ($categoryFilter !== 'all') {
    $whereConditions[] = "l.category_id = ?";
    $params[] = $categoryFilter;
}

if (!empty($searchQuery)) {
    $whereConditions[] = "(l.title LIKE ? OR l.description LIKE ? OR u.username LIKE ?)";
    $searchTerm = "%{$searchQuery}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

try {
    $db = getDBConnection();
    
    // Get categories for filter
    $categories = $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
    
    // Get total count
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM listings l 
        JOIN users u ON l.user_id = u.id 
        {$whereClause}
    ";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($params);
    $totalListings = $countStmt->fetch()['total'];
    $totalPages = ceil($totalListings / $limit);
    
    // Get listings
    $query = "
        SELECT l.*, u.username, u.first_name, u.last_name, c.name as category_name
        FROM listings l
        JOIN users u ON l.user_id = u.id
        LEFT JOIN categories c ON l.category_id = c.id
        {$whereClause}
        ORDER BY l.created_at DESC 
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $listings = $stmt->fetchAll();
    
    // Get statistics
    $stats = $db->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
            COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold,
            COUNT(CASE WHEN is_featured = 1 THEN 1 END) as featured,
            AVG(price) as avg_price
        FROM listings
    ")->fetch();
    
} catch (Exception $e) {
    error_log("Listings management error: " . $e->getMessage());
    $listings = [];
    $categories = [];
    $stats = ['total' => 0, 'active' => 0, 'pending' => 0, 'sold' => 0, 'featured' => 0, 'avg_price' => 0];
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
                <li class="active">
                    <a href="listings.php">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Listings Management</span>
                        <span class="badge"><?php echo $stats['total']; ?></span>
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
                <h1><i class="fas fa-shopping-bag"></i> Listings Management</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="exportListings()">
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
                <div class="stat-card listings">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total']); ?></h3>
                        <p>Total Listings</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #38a169;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['active']); ?></h3>
                        <p>Active Listings</p>
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
                        <p>Featured Listings</p>
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
                                    <option value="sold" <?php echo $statusFilter === 'sold' ? 'selected' : ''; ?>>Sold</option>
                                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category" class="form-control">
                                    <option value="all" <?php echo $categoryFilter === 'all' ? 'selected' : ''; ?>>All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $categoryFilter == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Title, description, seller..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="listings.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Listings Grid -->
            <div class="listings-grid">
                <?php if (empty($listings)): ?>
                    <div class="no-data" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                        <i class="fas fa-shopping-bag" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 1rem;"></i>
                        <h3>No listings found</h3>
                        <p>No listings match your current filters.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($listings as $listing): ?>
                        <div class="listing-card">
                            <div class="listing-image">
                                <?php if ($listing['image_url']): ?>
                                    <img src="<?php echo SITE_URL . '/' . htmlspecialchars($listing['image_url']); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="listing-badges">
                                    <span class="status-badge status-<?php echo $listing['status']; ?>">
                                        <?php echo ucfirst($listing['status']); ?>
                                    </span>
                                    <?php if ($listing['is_featured']): ?>
                                        <span class="featured-badge">
                                            <i class="fas fa-star"></i> Featured
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="listing-content">
                                <h4><?php echo htmlspecialchars($listing['title']); ?></h4>
                                <p class="listing-price">₱<?php echo number_format($listing['price'], 2); ?></p>
                                <p class="listing-description"><?php echo htmlspecialchars(substr($listing['description'], 0, 100)); ?>...</p>
                                
                                <div class="listing-meta">
                                    <div class="seller-info">
                                        <i class="fas fa-user"></i>
                                        <span><?php echo htmlspecialchars($listing['first_name'] . ' ' . $listing['last_name']); ?></span>
                                    </div>
                                    <div class="category-info">
                                        <i class="fas fa-tag"></i>
                                        <span><?php echo htmlspecialchars($listing['category_name'] ?? 'Uncategorized'); ?></span>
                                    </div>
                                    <div class="date-info">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('M j, Y', strtotime($listing['created_at'])); ?></span>
                                    </div>
                                </div>
                                
                                <div class="listing-actions">
                                    <?php if ($listing['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Reject this listing?')">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($listing['is_featured']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="unfeature">
                                            <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                            <button type="submit" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-star-half-alt"></i> Unfeature
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="feature">
                                            <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-star"></i> Feature
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this listing? This action cannot be undone.')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
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
                        <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $statusFilter; ?>&category=<?php echo $categoryFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>&category=<?php echo $categoryFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $statusFilter; ?>&category=<?php echo $categoryFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function exportListings() {
            window.location.href = 'export.php?type=listings';
        }
    </script>
</body>
</html>

<style>
.listings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.listing-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.listing-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.listing-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.listing-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    width: 100%;
    height: 100%;
    background: #f7fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #a0aec0;
    font-size: 3rem;
}

.listing-badges {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: rgba(56, 161, 105, 0.9);
    color: white;
}

.status-pending {
    background: rgba(237, 137, 54, 0.9);
    color: white;
}

.status-sold {
    background: rgba(49, 130, 206, 0.9);
    color: white;
}

.status-rejected {
    background: rgba(229, 62, 62, 0.9);
    color: white;
}

.featured-badge {
    background: rgba(255, 193, 7, 0.9);
    color: #1a202c;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.listing-content {
    padding: 1.5rem;
}

.listing-content h4 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.listing-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #3182ce;
    margin-bottom: 0.75rem;
}

.listing-description {
    color: #718096;
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.listing-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.75rem;
    color: #718096;
}

.listing-meta > div {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.listing-meta i {
    width: 12px;
    text-align: center;
}

.listing-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
}

.filters-form .form-group {
    margin-bottom: 0;
}
</style>
