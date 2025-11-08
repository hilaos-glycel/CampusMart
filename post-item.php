<?php
$pageTitle = 'Post Item';
require_once 'config/config.php';

// Require login BEFORE including header
requireLogin();

require_once 'includes/header.php';

$currentUser = getCurrentUser();
$editMode = isset($_GET['edit']) && !empty($_GET['edit']);
$listingId = $editMode ? intval($_GET['edit']) : 0;

// If editing, get listing data
$listing = null;
if ($editMode) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("
            SELECT l.*, c.slug as category_slug 
            FROM listings l 
            LEFT JOIN categories c ON l.category_id = c.id 
            WHERE l.id = ? AND l.user_id = ?
        ");
        $stmt->execute([$listingId, $currentUser['id']]);
        $listing = $stmt->fetch();
        
        if (!$listing) {
            header('Location: dashboard.php');
            exit();
        }
        
        $listing['images'] = $listing['images'] ? json_decode($listing['images'], true) : [];
    } catch (Exception $e) {
        error_log("Error fetching listing: " . $e->getMessage());
        header('Location: dashboard.php');
        exit();
    }
}
?>

<main style="padding-top: 100px;">
    <div class="container">
        <div class="form-container" style="max-width: 800px;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <i class="fas fa-plus-circle" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i>
                <h2><?php echo $editMode ? 'Edit Listing' : 'Post New Item'; ?></h2>
                <p style="color: #666;">
                    <?php echo $editMode ? 'Update your listing details' : 'Share your item with fellow JH students'; ?>
                </p>
            </div>

            <div id="postAlert"></div>

            <form id="postItemForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php if ($editMode): ?>
                    <input type="hidden" name="listing_id" value="<?php echo $listingId; ?>">
                <?php endif; ?>
                
                <!-- Basic Information -->
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    
                    <div class="form-group">
                        <label for="title">Item Title *</label>
                        <input type="text" id="title" name="title" required maxlength="200"
                               placeholder="e.g., MacBook Pro 13-inch 2020, Calculus Textbook"
                               value="<?php echo $listing ? htmlspecialchars($listing['title']) : ''; ?>">
                        <small>Be specific and descriptive to attract more buyers</small>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" required rows="4" maxlength="1000"
                                  placeholder="Describe the item's condition, features, and any important details..."><?php echo $listing ? htmlspecialchars($listing['description']) : ''; ?></textarea>
                        <small>Include condition, age, reason for selling, and any defects</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category *</label>
                            <select id="category" name="category" required>
                                <option value="">Select category</option>
                                <option value="school-supplies" <?php echo ($listing && $listing['category_slug'] === 'school-supplies') ? 'selected' : ''; ?>>School Supplies</option>
                                <option value="electronics" <?php echo ($listing && $listing['category_slug'] === 'electronics') ? 'selected' : ''; ?>>Electronics</option>
                                <option value="clothing" <?php echo ($listing && $listing['category_slug'] === 'clothing') ? 'selected' : ''; ?>>Clothing</option>
                                <option value="furniture" <?php echo ($listing && $listing['category_slug'] === 'furniture') ? 'selected' : ''; ?>>Furniture</option>
                                <option value="books" <?php echo ($listing && $listing['category_slug'] === 'books') ? 'selected' : ''; ?>>Books</option>
                                <option value="sports" <?php echo ($listing && $listing['category_slug'] === 'sports') ? 'selected' : ''; ?>>Sports & Recreation</option>
                                <option value="other" <?php echo ($listing && $listing['category_slug'] === 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="condition">Condition *</label>
                            <select id="condition" name="condition" required>
                                <option value="">Select condition</option>
                                <option value="New" <?php echo ($listing && $listing['condition_item'] === 'New') ? 'selected' : ''; ?>>New</option>
                                <option value="Like New" <?php echo ($listing && $listing['condition_item'] === 'Like New') ? 'selected' : ''; ?>>Like New</option>
                                <option value="Good" <?php echo ($listing && $listing['condition_item'] === 'Good') ? 'selected' : ''; ?>>Good</option>
                                <option value="Fair" <?php echo ($listing && $listing['condition_item'] === 'Fair') ? 'selected' : ''; ?>>Fair</option>
                                <option value="Poor" <?php echo ($listing && $listing['condition_item'] === 'Poor') ? 'selected' : ''; ?>>Poor</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Type -->
                <div class="form-section">
                    <h3><i class="fas fa-tag"></i> Pricing & Type</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Listing Type *</label>
                            <select id="type" name="type" required onchange="toggleRentalFields()">
                                <option value="">Select type</option>
                                <option value="sale" <?php echo ($listing && $listing['type'] === 'sale') ? 'selected' : ''; ?>>For Sale</option>
                                <option value="rent" <?php echo ($listing && $listing['type'] === 'rent') ? 'selected' : ''; ?>>For Rent</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price">Price (₱) *</label>
                            <input type="number" id="price" name="price" required min="0" step="0.01"
                                   placeholder="0.00"
                                   value="<?php echo $listing ? $listing['price'] : ''; ?>">
                            <small id="priceHelp">Enter the selling price or rental rate</small>
                        </div>
                    </div>

                    <div class="form-group" id="rentalPeriodGroup" style="display: none;">
                        <label for="rental_period">Rental Period</label>
                        <select id="rental_period" name="rental_period">
                            <option value="">Select period</option>
                            <option value="per hour" <?php echo ($listing && $listing['rental_period'] === 'per hour') ? 'selected' : ''; ?>>Per Hour</option>
                            <option value="per day" <?php echo ($listing && $listing['rental_period'] === 'per day') ? 'selected' : ''; ?>>Per Day</option>
                            <option value="per week" <?php echo ($listing && $listing['rental_period'] === 'per week') ? 'selected' : ''; ?>>Per Week</option>
                            <option value="per month" <?php echo ($listing && $listing['rental_period'] === 'per month') ? 'selected' : ''; ?>>Per Month</option>
                            <option value="per semester" <?php echo ($listing && $listing['rental_period'] === 'per semester') ? 'selected' : ''; ?>>Per Semester</option>
                        </select>
                    </div>
                </div>

                <!-- Location -->
                <div class="form-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Location</h3>
                    
                    <div class="form-group">
                        <label for="location">Pickup/Meeting Location</label>
                        <select id="location" name="location">
                            <option value="">Select location</option>
                            <option value="Main Campus" <?php echo ($listing && $listing['location'] === 'Main Campus') ? 'selected' : ''; ?>>Main Campus</option>
                            <option value="Pagadian City Proper" <?php echo ($listing && $listing['location'] === 'Pagadian City Proper') ? 'selected' : ''; ?>>Pagadian City Proper</option>
                            <option value="Near Dormitory" <?php echo ($listing && $listing['location'] === 'Near Dormitory') ? 'selected' : ''; ?>>Near Dormitory</option>
                            <option value="Campus Grounds" <?php echo ($listing && $listing['location'] === 'Campus Grounds') ? 'selected' : ''; ?>>Campus Grounds</option>
                            <option value="Engineering Building" <?php echo ($listing && $listing['location'] === 'Engineering Building') ? 'selected' : ''; ?>>Engineering Building</option>
                            <option value="IT Building" <?php echo ($listing && $listing['location'] === 'IT Building') ? 'selected' : ''; ?>>IT Building</option>
                            <option value="Library Area" <?php echo ($listing && $listing['location'] === 'Library Area') ? 'selected' : ''; ?>>Library Area</option>
                            <option value="Sports Complex" <?php echo ($listing && $listing['location'] === 'Sports Complex') ? 'selected' : ''; ?>>Sports Complex</option>
                            <option value="Music Department" <?php echo ($listing && $listing['location'] === 'Music Department') ? 'selected' : ''; ?>>Music Department</option>
                            <option value="Bookstore Area" <?php echo ($listing && $listing['location'] === 'Bookstore Area') ? 'selected' : ''; ?>>Bookstore Area</option>
                        </select>
                        <small>Where buyers can meet you to see/pickup the item</small>
                    </div>
                </div>

                <!-- Images -->
                <div class="form-section">
                    <h3><i class="fas fa-camera"></i> Images</h3>
                    
                    <div class="form-group">
                        <label for="images">Upload Images</label>
                        <input type="file" id="images" name="images[]" multiple accept="image/*" 
                               onchange="previewImages(this)">
                        <small>Upload up to 5 images (JPG, PNG, GIF, WebP - Max 5MB each)</small>
                    </div>

                    <div id="imagePreview" class="image-preview">
                        <?php if ($listing && !empty($listing['images'])): ?>
                            <?php foreach ($listing['images'] as $index => $image): ?>
                                <div class="image-preview-item">
                                    <img src="<?php echo SITE_URL . '/uploads/listings/' . $image; ?>" alt="Current image">
                                    <button type="button" onclick="removeExistingImage(<?php echo $index; ?>)" class="remove-image">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <input type="hidden" name="existing_images[]" value="<?php echo htmlspecialchars($image); ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Submit -->
                <div class="form-section">
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-<?php echo $editMode ? 'save' : 'plus'; ?>"></i> 
                            <?php echo $editMode ? 'Update Listing' : 'Post Item'; ?>
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>

            <!-- Tips -->
            <div style="background: #f8f9fa; padding: 2rem; border-radius: 8px; margin-top: 2rem;">
                <h4 style="color: #333; margin-bottom: 1rem;"><i class="fas fa-lightbulb"></i> Tips for Better Listings</h4>
                <ul style="color: #666; margin: 0; padding-left: 1.5rem;">
                    <li>Use clear, well-lit photos from multiple angles</li>
                    <li>Be honest about the item's condition</li>
                    <li>Include all relevant details and specifications</li>
                    <li>Price competitively by checking similar items</li>
                    <li>Respond promptly to interested buyers</li>
                    <li>Meet in safe, public places on campus</li>
                </ul>
            </div>
        </div>
    </div>
</main>

<script>
let removedImages = [];

document.getElementById('postItemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Add removed images to form data
    removedImages.forEach(image => {
        formData.append('removed_images[]', image);
    });
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<div class="loading"></div> <?php echo $editMode ? "Updating..." : "Posting..."; ?>';
    submitBtn.disabled = true;
    
    const endpoint = <?php echo $editMode ? "'api/update_listing.php'" : "'api/create_listing.php'"; ?>;
    
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('<?php echo $editMode ? "Listing updated successfully!" : "Item posted successfully!"; ?>', 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 2000);
        } else {
            showAlert(data.message || '<?php echo $editMode ? "Failed to update listing" : "Failed to post item"; ?>', 'error');
            resetSubmitButton();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
        resetSubmitButton();
    });
    
    function resetSubmitButton() {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

function toggleRentalFields() {
    const type = document.getElementById('type').value;
    const rentalGroup = document.getElementById('rentalPeriodGroup');
    const priceHelp = document.getElementById('priceHelp');
    
    if (type === 'rent') {
        rentalGroup.style.display = 'block';
        priceHelp.textContent = 'Enter the rental rate for the selected period';
    } else {
        rentalGroup.style.display = 'none';
        priceHelp.textContent = 'Enter the selling price';
    }
}

function previewImages(input) {
    const preview = document.getElementById('imagePreview');
    const existingImages = preview.querySelectorAll('.image-preview-item').length;
    
    if (input.files.length + existingImages > 5) {
        alert('You can upload a maximum of 5 images');
        input.value = '';
        return;
    }
    
    Array.from(input.files).forEach((file, index) => {
        if (file.size > 5242880) { // 5MB
            alert(`File ${file.name} is too large. Maximum size is 5MB.`);
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" onclick="removeNewImage(this)" class="remove-image">
                    <i class="fas fa-times"></i>
                </button>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removeNewImage(button) {
    button.parentElement.remove();
}

function removeExistingImage(index) {
    const item = document.querySelector(`input[name="existing_images[]"]:nth-of-type(${index + 1})`).parentElement;
    const imageName = item.querySelector('input[name="existing_images[]"]').value;
    removedImages.push(imageName);
    item.remove();
}

function showAlert(message, type) {
    const alertDiv = document.getElementById('postAlert');
    alertDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Initialize rental fields on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleRentalFields();
});
</script>

<style>
.form-section {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.form-section h3 {
    color: #333;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #667eea;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

.image-preview {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.image-preview-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    aspect-ratio: 1;
}

.image-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remove-image {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.remove-image:hover {
    background: #dc3545;
}

.alert {
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1rem;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.loading {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<?php require_once 'includes/footer.php'; ?>
