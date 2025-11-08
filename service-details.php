<?php
$pageTitle = 'Service Details';
require_once 'includes/header.php';

// Get service ID from URL
$serviceId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($serviceId <= 0) {
    header('Location: marketplace.php?type=services');
    exit();
}

// Fetch service details
try {
    $db = getDBConnection();
    
    $stmt = $db->prepare("
        SELECT 
            s.*,
            CONCAT(u.first_name, ' ', u.last_name) as provider_name,
            u.username as provider_username,
            u.student_id as provider_student_id,
            u.email as provider_email,
            u.phone as provider_phone,
            u.rating as provider_rating,
            u.total_reviews as provider_total_reviews
        FROM services s
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.id = ? AND s.status = 'active'
    ");
    
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();
    
    if (!$service) {
        header('Location: marketplace.php?type=services');
        exit();
    }
    
    // Process images
    $images = $service['images'] ? json_decode($service['images'], true) : [];
    
    // Update view count
    $updateStmt = $db->prepare("UPDATE services SET views = views + 1 WHERE id = ?");
    $updateStmt->execute([$serviceId]);
    
} catch (Exception $e) {
    error_log("Service details error: " . $e->getMessage());
    header('Location: marketplace.php?type=services');
    exit();
}
?>

<main style="padding-top: 100px;">
    <div class="container">
        <!-- Breadcrumb -->
        <nav style="margin-bottom: 2rem;">
            <a href="marketplace.php?type=services" style="color: #28a745; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Services
            </a>
        </nav>

        <div class="service-details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; max-width: 1200px;">
            <!-- Service Images -->
            <div class="service-images">
                <?php if (!empty($images)): ?>
                    <div class="main-image" style="margin-bottom: 1rem;">
                        <img id="mainImage" src="uploads/services/<?php echo htmlspecialchars($images[0]); ?>" 
                             alt="<?php echo htmlspecialchars($service['title']); ?>"
                             style="width: 100%; height: 400px; object-fit: cover; border-radius: 10px; border: 1px solid #ddd;">
                    </div>
                    
                    <?php if (count($images) > 1): ?>
                        <div class="image-thumbnails" style="display: flex; gap: 10px; overflow-x: auto;">
                            <?php foreach ($images as $index => $image): ?>
                                <img src="uploads/services/<?php echo htmlspecialchars($image); ?>" 
                                     alt="Service image <?php echo $index + 1; ?>"
                                     onclick="changeMainImage('uploads/services/<?php echo htmlspecialchars($image); ?>')"
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; cursor: pointer; border: 2px solid <?php echo $index === 0 ? '#28a745' : '#ddd'; ?>;">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-image" style="width: 100%; height: 400px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid #ddd;">
                        <div style="text-align: center; color: #666;">
                            <i class="fas fa-handshake" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <p>No image available</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Service Info -->
            <div class="service-info">
                <h1 style="font-size: 2rem; margin-bottom: 1rem; color: #333;">
                    <?php echo htmlspecialchars($service['title']); ?>
                </h1>

                <div class="price-section" style="margin-bottom: 2rem;">
                    <div class="price" style="font-size: 2.5rem; font-weight: bold; color: #28a745; margin-bottom: 0.5rem;">
                        ₱<?php echo number_format($service['price_per_hour'], 2); ?>/hour
                    </div>
                    
                    <div class="service-meta" style="display: flex; gap: 1rem; align-items: center;">
                        <span class="category-badge" style="background: #17a2b8; color: white; padding: 6px 12px; border-radius: 15px; font-size: 14px; font-weight: 600;">
                            <?php echo ucfirst($service['category']); ?>
                        </span>
                        
                        <?php if ($service['subject_skill']): ?>
                        <span class="skill-badge" style="background: #6f42c1; color: white; padding: 6px 12px; border-radius: 15px; font-size: 14px; font-weight: 600;">
                            <?php echo htmlspecialchars($service['subject_skill']); ?>
                        </span>
                        <?php endif; ?>
                        
                        <span style="color: #666; font-size: 14px;">
                            <i class="fas fa-eye"></i> <?php echo $service['views']; ?> views
                        </span>
                    </div>
                </div>

                <!-- Description -->
                <div class="description" style="margin-bottom: 2rem;">
                    <h3 style="color: #333; margin-bottom: 1rem;">Description</h3>
                    <p style="line-height: 1.6; color: #666;">
                        <?php echo nl2br(htmlspecialchars($service['description'])); ?>
                    </p>
                </div>

                <!-- Service Details -->
                <div class="details" style="margin-bottom: 2rem;">
                    <h3 style="color: #333; margin-bottom: 1rem;">Service Details</h3>
                    <div class="details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <strong>Category:</strong><br>
                            <span style="color: #17a2b8;"><?php echo ucfirst($service['category']); ?></span>
                        </div>
                        
                        <?php if ($service['subject_skill']): ?>
                        <div>
                            <strong>Subject/Skill:</strong><br>
                            <?php echo htmlspecialchars($service['subject_skill']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <strong>Rate:</strong><br>
                            ₱<?php echo number_format($service['price_per_hour'], 2); ?> per hour
                        </div>
                        
                        <?php if ($service['location']): ?>
                        <div>
                            <strong>Location:</strong><br>
                            <?php echo htmlspecialchars($service['location']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <strong>Posted:</strong><br>
                            <?php echo date('M j, Y', strtotime($service['created_at'])); ?>
                        </div>
                        
                        <?php if ($service['rating'] > 0): ?>
                        <div>
                            <strong>Rating:</strong><br>
                            <span style="color: #ffc107;">
                                <i class="fas fa-star"></i> <?php echo number_format($service['rating'], 1); ?>
                                (<?php echo $service['total_reviews']; ?> reviews)
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Availability -->
                <?php if ($service['availability']): ?>
                <div class="availability" style="margin-bottom: 2rem;">
                    <h3 style="color: #333; margin-bottom: 1rem;">Availability</h3>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($service['availability'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Provider Info -->
                <div class="provider-info" style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                    <h3 style="color: #333; margin-bottom: 1rem;">Service Provider</h3>
                    
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div class="provider-avatar" style="width: 50px; height: 50px; background: #17a2b8; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2rem;">
                            <?php echo strtoupper(substr($service['provider_name'], 0, 1)); ?>
                        </div>
                        
                        <div>
                            <div style="font-weight: 600; font-size: 1.1rem;">
                                <?php echo htmlspecialchars($service['provider_name']); ?>
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">
                                @<?php echo htmlspecialchars($service['provider_username']); ?> • <?php echo htmlspecialchars($service['provider_student_id']); ?>
                            </div>
                            
                            <?php if ($service['provider_rating'] > 0): ?>
                            <div style="color: #ffc107; font-size: 0.9rem;">
                                <i class="fas fa-star"></i> <?php echo number_format($service['provider_rating'], 1); ?>
                                (<?php echo $service['provider_total_reviews']; ?> reviews)
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons" style="display: flex; gap: 1rem;">
                    <?php if (isLoggedIn() && $_SESSION['user_id'] != $service['user_id']): ?>
                        <button onclick="contactProvider()" style="flex: 1; background: #17a2b8; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-comment"></i> Contact Provider
                        </button>
                        
                        <button onclick="bookService(<?php echo $service['id']; ?>)" style="flex: 1; background: #28a745; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-calendar-plus"></i> Book Service
                        </button>
                    <?php elseif (isLoggedIn() && $_SESSION['user_id'] == $service['user_id']): ?>
                        <button onclick="editService(<?php echo $service['id']; ?>)" style="flex: 1; background: #007bff; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-edit"></i> Edit Service
                        </button>
                    <?php else: ?>
                        <a href="login.php" style="flex: 1; background: #17a2b8; color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-size: 1.1rem; font-weight: 600; text-align: center; display: block;">
                            <i class="fas fa-sign-in-alt"></i> Login to Contact
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Related Services -->
        <div class="related-services" style="margin-top: 4rem;">
            <h3 style="color: #333; margin-bottom: 2rem;">Related Services</h3>
            <div id="relatedServices" class="services-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <!-- Related services will be loaded here -->
            </div>
        </div>
    </div>
</main>

<style>
@media (max-width: 768px) {
    .service-details {
        grid-template-columns: 1fr !important;
        gap: 2rem !important;
    }
    
    .details-grid {
        grid-template-columns: 1fr !important;
    }
    
    .action-buttons {
        flex-direction: column !important;
    }
}

.image-thumbnails img:hover {
    border-color: #17a2b8 !important;
}

.action-buttons button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
</style>

<script>
function changeMainImage(imageSrc) {
    document.getElementById('mainImage').src = imageSrc;
    
    // Update thumbnail borders
    document.querySelectorAll('.image-thumbnails img').forEach(img => {
        img.style.borderColor = img.src.includes(imageSrc.split('/').pop()) ? '#17a2b8' : '#ddd';
    });
}

function contactProvider() {
    <?php if (isLoggedIn()): ?>
        // Redirect to messages with provider
        window.location.href = `messages.php?user_id=<?php echo $service['user_id']; ?>`;
    <?php else: ?>
        window.location.href = 'login.php';
    <?php endif; ?>
}

function bookService(serviceId) {
    <?php if (isLoggedIn()): ?>
        // Redirect to booking page (you can create this later)
        alert('Booking feature coming soon! For now, please contact the provider directly.');
        contactProvider();
    <?php else: ?>
        window.location.href = 'login.php';
    <?php endif; ?>
}

function editService(serviceId) {
    window.location.href = `post-service.php?edit=${serviceId}`;
}

// Load related services
document.addEventListener('DOMContentLoaded', function() {
    fetch(`api/get_services.php?category=<?php echo urlencode($service['category']); ?>&limit=4`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.services) {
                const relatedServices = data.services.filter(s => s.id != <?php echo $serviceId; ?>);
                
                if (relatedServices.length > 0) {
                    const html = relatedServices.map(service => `
                        <div class="service-card" onclick="window.location.href='service-details.php?id=${service.id}'" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); cursor: pointer;">
                            <h4 style="margin: 0 0 10px 0; font-size: 16px;">${service.title}</h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px;">${service.description.substring(0, 100)}...</p>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #17a2b8; font-weight: bold;">₱${parseFloat(service.price_per_hour).toLocaleString()}/hr</span>
                                <span style="background: #17a2b8; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">${service.category.toUpperCase()}</span>
                            </div>
                        </div>
                    `).join('');
                    
                    document.getElementById('relatedServices').innerHTML = html;
                } else {
                    document.querySelector('.related-services').style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error loading related services:', error);
            document.querySelector('.related-services').style.display = 'none';
        });
});
</script>

<?php require_once 'includes/footer.php'; ?>
