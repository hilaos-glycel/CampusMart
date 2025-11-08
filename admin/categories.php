<?php
$pageTitle = 'Categories Management';
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
    
    try {
        $db = getDBConnection();
        
        switch ($action) {
            case 'create':
                $name = trim($_POST['name'] ?? '');
                $slug = trim($_POST['slug'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $icon = trim($_POST['icon'] ?? '');
                
                if (empty($name)) {
                    throw new Exception("Category name is required.");
                }
                
                if (empty($slug)) {
                    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
                }
                
                $stmt = $db->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $description, $icon]);
                $message = "Category created successfully.";
                $messageType = "success";
                break;
                
            case 'update':
                $categoryId = (int)($_POST['category_id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $slug = trim($_POST['slug'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $icon = trim($_POST['icon'] ?? '');
                
                if (empty($name)) {
                    throw new Exception("Category name is required.");
                }
                
                $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, icon = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $icon, $categoryId]);
                $message = "Category updated successfully.";
                $messageType = "success";
                break;
                
            case 'delete':
                $categoryId = (int)($_POST['category_id'] ?? 0);
                
                // Check if category has listings
                $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM listings WHERE category_id = ?");
                $checkStmt->execute([$categoryId]);
                $listingCount = $checkStmt->fetch()['count'];
                
                if ($listingCount > 0) {
                    throw new Exception("Cannot delete category with existing listings. Move or delete listings first.");
                }
                
                $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$categoryId]);
                $message = "Category deleted successfully.";
                $messageType = "success";
                break;
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "danger";
    }
}

try {
    $db = getDBConnection();
    
    // Get categories with listing counts
    $categories = $db->query("
        SELECT c.*, COUNT(l.id) as listing_count
        FROM categories c
        LEFT JOIN listings l ON c.id = l.category_id
        GROUP BY c.id
        ORDER BY c.name
    ")->fetchAll();
    
    // Get statistics
    $stats = $db->query("
        SELECT 
            COUNT(*) as total_categories,
            COUNT(CASE WHEN c.id IN (SELECT DISTINCT category_id FROM listings WHERE category_id IS NOT NULL) THEN 1 END) as used_categories
        FROM categories c
    ")->fetch();
    
} catch (Exception $e) {
    error_log("Categories management error: " . $e->getMessage());
    $categories = [];
    $stats = ['total_categories' => 0, 'used_categories' => 0];
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
                <li class="active">
                    <a href="categories.php">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                        <span class="badge"><?php echo $stats['total_categories']; ?></span>
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
                <h1><i class="fas fa-tags"></i> Categories Management</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="openCreateModal()">
                        <i class="fas fa-plus"></i> Add Category
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
                <div class="stat-card">
                    <div class="stat-icon" style="background: #667eea;">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_categories']); ?></h3>
                        <p>Total Categories</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #38a169;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['used_categories']); ?></h3>
                        <p>Categories in Use</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ed8936;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_categories'] - $stats['used_categories']); ?></h3>
                        <p>Unused Categories</p>
                    </div>
                </div>
            </div>

            <!-- Categories Grid -->
            <div class="categories-grid">
                <?php if (empty($categories)): ?>
                    <div class="no-data" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                        <i class="fas fa-tags" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 1rem;"></i>
                        <h3>No categories found</h3>
                        <p>Create your first category to get started.</p>
                        <button class="btn btn-primary" onclick="openCreateModal()">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card">
                            <div class="category-icon">
                                <?php if ($category['icon']): ?>
                                    <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
                                <?php else: ?>
                                    <i class="fas fa-tag"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div class="category-content">
                                <h4><?php echo htmlspecialchars($category['name']); ?></h4>
                                <p class="category-slug"><?php echo htmlspecialchars($category['slug']); ?></p>
                                <?php if ($category['description']): ?>
                                    <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                                <?php endif; ?>
                                
                                <div class="category-stats">
                                    <span class="listing-count">
                                        <i class="fas fa-shopping-bag"></i>
                                        <?php echo $category['listing_count']; ?> listings
                                    </span>
                                </div>
                            </div>
                            
                            <div class="category-actions">
                                <button class="btn btn-primary btn-sm" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <?php if ($category['listing_count'] == 0): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this category?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled title="Cannot delete category with listings">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Create/Edit Category Modal -->
    <div class="modal-overlay" id="categoryModal" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle">Add Category</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="category_id" id="categoryId">
                    
                    <div class="form-group">
                        <label for="categoryName">Category Name *</label>
                        <input type="text" id="categoryName" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categorySlug">Slug</label>
                        <input type="text" id="categorySlug" name="slug" class="form-control" placeholder="auto-generated">
                        <small style="color: #718096;">Leave empty to auto-generate from name</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoryIcon">Icon Class</label>
                        <input type="text" id="categoryIcon" name="icon" class="form-control" placeholder="fas fa-tag">
                        <small style="color: #718096;">FontAwesome icon class (e.g., fas fa-book, fas fa-laptop)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoryDescription">Description</label>
                        <textarea id="categoryDescription" name="description" class="form-control" rows="3" placeholder="Optional description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Create Category</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Add Category';
            document.getElementById('formAction').value = 'create';
            document.getElementById('submitBtn').textContent = 'Create Category';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryModal').style.display = 'flex';
            document.getElementById('categoryName').focus();
        }
        
        function editCategory(category) {
            document.getElementById('modalTitle').textContent = 'Edit Category';
            document.getElementById('formAction').value = 'update';
            document.getElementById('submitBtn').textContent = 'Update Category';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categorySlug').value = category.slug;
            document.getElementById('categoryIcon').value = category.icon || '';
            document.getElementById('categoryDescription').value = category.description || '';
            document.getElementById('categoryModal').style.display = 'flex';
            document.getElementById('categoryName').focus();
        }
        
        function closeModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }
        
        // Auto-generate slug from name
        document.getElementById('categoryName').addEventListener('input', function() {
            const name = this.value;
            const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            document.getElementById('categorySlug').placeholder = slug || 'auto-generated';
        });
        
        // Close modal when clicking outside
        document.getElementById('categoryModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>

<style>
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.category-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.category-icon {
    width: 60px;
    height: 60px;
    border-radius: 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    align-self: flex-start;
}

.category-content {
    flex: 1;
}

.category-content h4 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.category-slug {
    font-size: 0.875rem;
    color: #718096;
    font-family: 'Courier New', monospace;
    background: #f7fafc;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    display: inline-block;
    margin-bottom: 0.75rem;
}

.category-description {
    color: #718096;
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.category-stats {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.875rem;
    color: #718096;
}

.listing-count {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.category-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: auto;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
}
</style>
