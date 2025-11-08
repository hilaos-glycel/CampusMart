<?php
$pageTitle = 'Product Details';
require_once 'includes/header.php';

// Get product ID from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    header('Location: marketplace.php');
    exit();
}

// Fetch product details
try {
    $db = getDBConnection();
    
    $stmt = $db->prepare("
        SELECT 
            l.*,
            c.name as category_name,
            c.slug as category_slug,
            CONCAT(u.first_name, ' ', u.last_name) as seller_name,
            u.username as seller_username,
            u.student_id as seller_student_id,
            u.email as seller_email,
            u.phone as seller_phone,
            u.rating as seller_rating,
            u.total_reviews as seller_total_reviews
        FROM listings l
        LEFT JOIN categories c ON l.category_id = c.id
        LEFT JOIN users u ON l.user_id = u.id
        WHERE l.id = ? AND l.status = 'active'
    ");
    
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: marketplace.php');
        exit();
    }
    
    // Process images
    $images = $product['images'] ? json_decode($product['images'], true) : [];
    
    // Update view count
    $updateStmt = $db->prepare("UPDATE listings SET views = views + 1 WHERE id = ?");
    $updateStmt->execute([$productId]);
    
} catch (Exception $e) {
    error_log("Product details error: " . $e->getMessage());
    header('Location: marketplace.php');
    exit();
}
?>

<main style="padding-top: 100px;">
    <div class="container">
        <!-- Breadcrumb -->
        <nav style="margin-bottom: 2rem;">
            <a href="marketplace.php" style="color: #28a745; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Marketplace
            </a>
        </nav>

        <div class="product-details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; max-width: 1200px;">
            <!-- Product Images -->
            <div class="product-images">
                <?php if (!empty($images)): ?>
                    <div class="main-image" style="margin-bottom: 1rem;">
                        <img id="mainImage" src="uploads/listings/<?php echo htmlspecialchars($images[0]); ?>" 
                             alt="<?php echo htmlspecialchars($product['title']); ?>"
                             style="width: 100%; height: 400px; object-fit: cover; border-radius: 10px; border: 1px solid #ddd;">
                    </div>
                    
                    <?php if (count($images) > 1): ?>
                        <div class="image-thumbnails" style="display: flex; gap: 10px; overflow-x: auto;">
                            <?php foreach ($images as $index => $image): ?>
                                <img src="uploads/listings/<?php echo htmlspecialchars($image); ?>" 
                                     alt="Product image <?php echo $index + 1; ?>"
                                     onclick="changeMainImage('uploads/listings/<?php echo htmlspecialchars($image); ?>')"
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; cursor: pointer; border: 2px solid <?php echo $index === 0 ? '#28a745' : '#ddd'; ?>;">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-image" style="width: 100%; height: 400px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid #ddd;">
                        <div style="text-align: center; color: #666;">
                            <i class="fas fa-image" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <p>No image available</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <h1 style="font-size: 2rem; margin-bottom: 1rem; color: #333;">
                    <?php echo htmlspecialchars($product['title']); ?>
                </h1>

                <div class="price-section" style="margin-bottom: 2rem;">
                    <div class="price" style="font-size: 2.5rem; font-weight: bold; color: #28a745; margin-bottom: 0.5rem;">
                        ₱<?php echo number_format($product['price'], 2); ?>
                    </div>
                    
                    <div class="product-meta" style="display: flex; gap: 1rem; align-items: center;">
                        <span class="condition-badge" style="background: #e9ecef; padding: 6px 12px; border-radius: 15px; font-size: 14px; font-weight: 600;">
                            <?php echo htmlspecialchars($product['condition_item']); ?>
                        </span>
                        
                        <span class="type-badge" style="background: #007bff; color: white; padding: 6px 12px; border-radius: 15px; font-size: 14px; font-weight: 600;">
                            For <?php echo ucfirst($product['type']); ?>
                        </span>
                        
                        <span style="color: #666; font-size: 14px;">
                            <i class="fas fa-eye"></i> <?php echo $product['views']; ?> views
                        </span>
                    </div>
                </div>

                <!-- Description -->
                <div class="description" style="margin-bottom: 2rem;">
                    <h3 style="color: #333; margin-bottom: 1rem;">Description</h3>
                    <p style="line-height: 1.6; color: #666;">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </p>
                </div>

                <!-- Product Details -->
                <div class="details" style="margin-bottom: 2rem;">
                    <h3 style="color: #333; margin-bottom: 1rem;">Details</h3>
                    <div class="details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <strong>Category:</strong><br>
                            <span style="color: #007bff;"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        </div>
                        
                        <div>
                            <strong>Condition:</strong><br>
                            <?php echo htmlspecialchars($product['condition_item']); ?>
                        </div>
                        
                        <?php if ($product['location']): ?>
                        <div>
                            <strong>Location:</strong><br>
                            <?php echo htmlspecialchars($product['location']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($product['type'] === 'rent' && $product['rental_period']): ?>
                        <div>
                            <strong>Rental Period:</strong><br>
                            <?php echo htmlspecialchars($product['rental_period']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <strong>Posted:</strong><br>
                            <?php echo date('M j, Y', strtotime($product['created_at'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Seller Info -->
                <div class="seller-info" style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                    <h3 style="color: #333; margin-bottom: 1rem;">Seller Information</h3>
                    
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div class="seller-avatar" style="width: 50px; height: 50px; background: #28a745; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2rem;">
                            <?php echo strtoupper(substr($product['seller_name'], 0, 1)); ?>
                        </div>
                        
                        <div>
                            <div style="font-weight: 600; font-size: 1.1rem;">
                                <?php echo htmlspecialchars($product['seller_name']); ?>
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">
                                @<?php echo htmlspecialchars($product['seller_username']); ?> • <?php echo htmlspecialchars($product['seller_student_id']); ?>
                            </div>
                            
                            <?php if ($product['seller_rating'] > 0): ?>
                            <div style="color: #ffc107; font-size: 0.9rem;">
                                <i class="fas fa-star"></i> <?php echo number_format($product['seller_rating'], 1); ?>
                                (<?php echo $product['seller_total_reviews']; ?> reviews)
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons" style="display: flex; gap: 1rem;">
                    <?php if (isLoggedIn() && $_SESSION['user_id'] != $product['user_id']): ?>
                        <button onclick="contactSeller()" style="flex: 1; background: #28a745; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-comment"></i> Contact Seller
                        </button>
                        
                        <button onclick="addToWishlist(<?php echo $product['id']; ?>)" style="background: white; color: #28a745; border: 2px solid #28a745; padding: 15px 20px; border-radius: 8px; cursor: pointer;">
                            <i class="fas fa-heart"></i>
                        </button>
                    <?php elseif (isLoggedIn() && $_SESSION['user_id'] == $product['user_id']): ?>
                        <button onclick="editListing(<?php echo $product['id']; ?>)" style="flex: 1; background: #007bff; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-edit"></i> Edit Listing
                        </button>
                    <?php else: ?>
                        <a href="login.php" style="flex: 1; background: #28a745; color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-size: 1.1rem; font-weight: 600; text-align: center; display: block;">
                            <i class="fas fa-sign-in-alt"></i> Login to Contact
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <div class="related-products" style="margin-top: 4rem;">
            <h3 style="color: #333; margin-bottom: 2rem;">Related Products</h3>
            <div id="relatedProducts" class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                <!-- Related products will be loaded here -->
            </div>
        </div>
    </div>
</main>

<style>
@media (max-width: 768px) {
    .product-details {
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
    border-color: #28a745 !important;
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
        img.style.borderColor = img.src.includes(imageSrc.split('/').pop()) ? '#28a745' : '#ddd';
    });
}

function contactSeller() {
    <?php if (isLoggedIn()): ?>
        // Redirect to messages with seller
        window.location.href = `messages.php?user_id=<?php echo $product['user_id']; ?>`;
    <?php else: ?>
        window.location.href = 'login.php';
    <?php endif; ?>
}

function addToWishlist(productId) {
    <?php if (isLoggedIn()): ?>
        fetch('api/add_to_wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Added to wishlist!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding to wishlist');
        });
    <?php else: ?>
        window.location.href = 'login.php';
    <?php endif; ?>
}

function editListing(productId) {
    window.location.href = `post-item.php?id=${productId}`;
}

// Load related products
document.addEventListener('DOMContentLoaded', function() {
    fetch(`api/get_listings.php?category=<?php echo urlencode($product['category_slug']); ?>&limit=4`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.listings) {
                const relatedProducts = data.listings.filter(p => p.id != <?php echo $productId; ?>);
                
                if (relatedProducts.length > 0) {
                    const html = relatedProducts.map(product => `
                        <div class="product-card" onclick="window.location.href='product-details.php?id=${product.id}'" style="background: white; border-radius: 10px; padding: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); cursor: pointer;">
                            <h4 style="margin: 0 0 10px 0; font-size: 16px;">${product.title}</h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 10px;">${product.description.substring(0, 80)}...</p>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #28a745; font-weight: bold;">₱${parseFloat(product.price).toLocaleString()}</span>
                                <span style="color: #666; font-size: 12px;">${product.condition_item}</span>
                            </div>
                        </div>
                    `).join('');
                    
                    document.getElementById('relatedProducts').innerHTML = html;
                } else {
                    document.querySelector('.related-products').style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error loading related products:', error);
            document.querySelector('.related-products').style.display = 'none';
        });
});
</script>

<?php require_once 'includes/footer.php'; ?>
