<?php
$pageTitle = 'Messages Overview';
require_once '../config/config.php';
require_once '../includes/admin_auth.php';

// Check admin authentication
requireAdminLogin();
$currentAdmin = getCurrentAdmin();

// Get filter parameters
$searchTerm = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

try {
    $db = getDBConnection();
    
    // Build search query
    $searchCondition = '';
    $params = [];
    
    if (!empty($searchTerm)) {
        $searchCondition = "WHERE u1.username LIKE :search OR u2.username LIKE :search OR m.message LIKE :search";
        $params[':search'] = "%$searchTerm%";
    }
    
    // Get total count
    $countQuery = "
        SELECT COUNT(DISTINCT m.id) as total
        FROM messages m
        LEFT JOIN users u1 ON m.sender_id = u1.id
        LEFT JOIN users u2 ON m.receiver_id = u2.id
        $searchCondition
    ";
    $stmt = $db->prepare($countQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totalMessages = $stmt->fetch()['total'];
    $totalPages = ceil($totalMessages / $perPage);
    
    // Get messages
    $query = "
        SELECT 
            m.id,
            m.sender_id,
            m.receiver_id,
            m.message,
            m.message_type,
            m.is_read,
            m.created_at,
            u1.username as sender_username,
            u1.first_name as sender_first_name,
            u1.last_name as sender_last_name,
            u2.username as receiver_username,
            u2.first_name as receiver_first_name,
            u2.last_name as receiver_last_name
        FROM messages m
        LEFT JOIN users u1 ON m.sender_id = u1.id
        LEFT JOIN users u2 ON m.receiver_id = u2.id
        $searchCondition
        ORDER BY m.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll();
    
    // Get statistics
    $stats = $db->query("
        SELECT 
            COUNT(*) as total_messages,
            COUNT(CASE WHEN is_read = 0 THEN 1 END) as unread_messages,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as messages_24h,
            COUNT(CASE WHEN message_type = 'image' THEN 1 END) as image_messages,
            COUNT(CASE WHEN message_type = 'video' THEN 1 END) as video_messages
        FROM messages
    ")->fetch();
    
} catch (Exception $e) {
    error_log("Admin messages error: " . $e->getMessage());
    $messages = [];
    $totalMessages = 0;
    $totalPages = 1;
    $stats = [
        'total_messages' => 0,
        'unread_messages' => 0,
        'messages_24h' => 0,
        'image_messages' => 0,
        'video_messages' => 0
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
                <li class="active">
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
                <h1><i class="fas fa-comments"></i> Messages Overview</h1>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #17a2b8 0%, #00d4ff 100%);">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_messages']); ?></h3>
                        <p>Total Messages</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['unread_messages']); ?></h3>
                        <p>Unread Messages</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['messages_24h']); ?></h3>
                        <p>Last 24 Hours</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #e83e8c 0%, #f5576c 100%);">
                        <i class="fas fa-image"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['image_messages'] + $stats['video_messages']); ?></h3>
                        <p>Media Messages</p>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="admin-form" style="margin-bottom: 2rem;">
                <form method="GET" action="" style="display: flex; gap: 1rem; align-items: center;">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <input type="text" name="search" class="form-control" placeholder="Search messages, users..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if (!empty($searchTerm)): ?>
                        <a href="messages.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Messages Table -->
            <div class="admin-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Message</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem; color: #718096;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                    No messages found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <tr>
                                    <td>#<?php echo $message['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($message['sender_first_name'] . ' ' . $message['sender_last_name']); ?></strong><br>
                                        <small>@<?php echo htmlspecialchars($message['sender_username']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($message['receiver_first_name'] . ' ' . $message['receiver_last_name']); ?></strong><br>
                                        <small>@<?php echo htmlspecialchars($message['receiver_username']); ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                        $messageText = htmlspecialchars($message['message']);
                                        echo strlen($messageText) > 50 ? substr($messageText, 0, 50) . '...' : $messageText;
                                        ?>
                                    </td>
                                    <td>
                                        <span class="status" style="background: <?php 
                                            echo $message['message_type'] === 'image' ? '#e9d8fd' : 
                                                ($message['message_type'] === 'video' ? '#fed7d7' : '#e2e8f0'); 
                                        ?>; color: <?php 
                                            echo $message['message_type'] === 'image' ? '#553c9a' : 
                                                ($message['message_type'] === 'video' ? '#822727' : '#4a5568'); 
                                        ?>;">
                                            <i class="fas fa-<?php 
                                                echo $message['message_type'] === 'image' ? 'image' : 
                                                    ($message['message_type'] === 'video' ? 'video' : 'comment'); 
                                            ?>"></i>
                                            <?php echo ucfirst($message['message_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status <?php echo $message['is_read'] ? 'status-active' : 'status-pending'; ?>">
                                            <?php echo $message['is_read'] ? 'Read' : 'Unread'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-danger" onclick="deleteMessage(<?php echo $message['id']; ?>)" title="Delete Message">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function deleteMessage(messageId) {
            if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
                // Implement delete functionality via AJAX
                fetch('../api/admin/delete_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ message_id: messageId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Message deleted successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the message');
                });
            }
        }
    </script>
</body>
</html>
