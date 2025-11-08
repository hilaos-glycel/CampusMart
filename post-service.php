<?php
$pageTitle = 'Post Service';
require_once 'config/config.php';

// Require login BEFORE including header
requireLogin();

require_once 'includes/header.php';

$currentUser = getCurrentUser();
$editMode = isset($_GET['edit']) && !empty($_GET['edit']);
$serviceId = $editMode ? intval($_GET['edit']) : 0;

// If editing, get service data
$service = null;
if ($editMode) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("
            SELECT * FROM services 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$serviceId, $currentUser['id']]);
        $service = $stmt->fetch();
        
        if (!$service) {
            header('Location: dashboard.php');
            exit();
        }
        
        $service['images'] = $service['images'] ? json_decode($service['images'], true) : [];
    } catch (Exception $e) {
        error_log("Error fetching service: " . $e->getMessage());
        header('Location: dashboard.php');
        exit();
    }
}
?>

<main style="padding-top: 100px;">
    <div class="container">
        <div class="form-container" style="max-width: 800px;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <i class="fas fa-handshake" style="font-size: 3rem; color: #17a2b8; margin-bottom: 1rem;"></i>
                <h2><?php echo $editMode ? 'Edit Service' : 'Post New Service'; ?></h2>
                <p style="color: #666;">
                    <?php echo $editMode ? 'Update your service details' : 'Offer your skills to fellow JH students'; ?>
                </p>
            </div>

            <form id="serviceForm" enctype="multipart/form-data" style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php if ($editMode): ?>
                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                <?php endif; ?>

                <!-- Service Type Selection -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="category" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Service Type *</label>
                    <select id="category" name="category" required style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 16px;">
                        <option value="">Select service type</option>
                        <option value="tutoring" <?php echo ($service && $service['category'] === 'tutoring') ? 'selected' : ''; ?>>Tutoring</option>
                        <option value="freelance" <?php echo ($service && $service['category'] === 'freelance') ? 'selected' : ''; ?>>Freelance Work</option>
                        <option value="academic" <?php echo ($service && $service['category'] === 'academic') ? 'selected' : ''; ?>>Academic Help</option>
                        <option value="technical" <?php echo ($service && $service['category'] === 'technical') ? 'selected' : ''; ?>>Technical Services</option>
                        <option value="creative" <?php echo ($service && $service['category'] === 'creative') ? 'selected' : ''; ?>>Creative Services</option>
                        <option value="other" <?php echo ($service && $service['category'] === 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <!-- Service Title -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Service Title *</label>
                    <input type="text" id="title" name="title" required 
                           placeholder="e.g., Math Tutoring for Engineering Students"
                           value="<?php echo $service ? htmlspecialchars($service['title']) : ''; ?>"
                           style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 16px;">
                </div>

                <!-- Subject/Skill -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="subject_skill" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Subject/Skill</label>
                    <input type="text" id="subject_skill" name="subject_skill" 
                           placeholder="e.g., Calculus, Web Development, Graphic Design"
                           value="<?php echo $service ? htmlspecialchars($service['subject_skill']) : ''; ?>"
                           style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 16px;">
                </div>

                <!-- Description -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Description *</label>
                    <textarea id="description" name="description" required rows="4" 
                              placeholder="Describe your service, experience, and what students can expect..."
                              style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 16px; resize: vertical;"><?php echo $service ? htmlspecialchars($service['description']) : ''; ?></textarea>
                </div>

                <!-- Price and Availability Row -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <!-- Price per Hour -->
                    <div class="form-group">
                        <label for="price_per_hour" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Price per Hour (₱) *</label>
                        <input type="number" id="price_per_hour" name="price_per_hour" required min="1" step="0.01"
                               placeholder="150.00"
                               value="<?php echo $service ? $service['price_per_hour'] : ''; ?>"
                               style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 16px;">
                    </div>

                    <!-- Location -->
                    <div class="form-group">
                        <label for="location" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Location</label>
                        <input type="text" id="location" name="location" 
                               placeholder="e.g., Main Campus, Online, Student's Place"
                               value="<?php echo $service ? htmlspecialchars($service['location']) : ''; ?>"
                               style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 16px;">
                    </div>
                </div>

                <!-- Availability -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="availability" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Availability</label>
                    <textarea id="availability" name="availability" rows="3" 
                              placeholder="e.g., Monday-Friday 2PM-6PM, Weekends flexible, Available for online sessions"
                              style="width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 16px; resize: vertical;"><?php echo $service ? htmlspecialchars($service['availability']) : ''; ?></textarea>
                </div>

                <!-- Image Upload -->
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label for="images" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Service Images (Optional)</label>
                    <div class="upload-area" style="border: 2px dashed #e1e5e9; border-radius: 8px; padding: 2rem; text-align: center; background: #f8f9fa;">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #17a2b8; margin-bottom: 1rem;"></i>
                        <p style="margin-bottom: 1rem; color: #666;">Upload images of your work, certificates, or examples</p>
                        <input type="file" id="images" name="images[]" multiple accept="image/*" 
                               style="margin-bottom: 1rem;">
                        <p style="font-size: 14px; color: #999;">Max 5 images, 5MB each. Supported: JPG, PNG, GIF</p>
                    </div>

                    <!-- Existing Images (Edit Mode) -->
                    <?php if ($editMode && !empty($service['images'])): ?>
                        <div class="existing-images" style="margin-top: 1rem;">
                            <p style="font-weight: 600; margin-bottom: 0.5rem;">Current Images:</p>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <?php foreach ($service['images'] as $image): ?>
                                    <div style="position: relative;">
                                        <img src="uploads/services/<?php echo htmlspecialchars($image); ?>" 
                                             alt="Service image" 
                                             style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                                        <button type="button" onclick="removeImage('<?php echo htmlspecialchars($image); ?>')"
                                                style="position: absolute; top: -5px; right: -5px; background: #dc3545; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; cursor: pointer;">×</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Submit Buttons -->
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button type="button" onclick="window.history.back()" 
                            style="padding: 12px 30px; border: 2px solid #6c757d; background: white; color: #6c757d; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                            style="padding: 12px 30px; border: none; background: linear-gradient(135deg, #17a2b8, #138496); color: white; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-save"></i> <?php echo $editMode ? 'Update Service' : 'Post Service'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: #17a2b8;
    outline: none;
    box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.1);
}

.upload-area:hover {
    border-color: #17a2b8;
    background: #f0f9ff;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

@media (max-width: 768px) {
    .form-container {
        padding: 0 1rem;
    }
    
    .form-container form {
        padding: 1.5rem;
    }
    
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
document.getElementById('serviceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (<?php echo $editMode ? 'true' : 'false'; ?> ? 'Updating...' : 'Posting...');
    
    const formData = new FormData(this);
    const apiUrl = <?php echo $editMode ? "'api/update_service.php'" : "'api/create_service.php'"; ?>;
    
    fetch(apiUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert(data.message || (<?php echo $editMode ? 'true' : 'false'; ?> ? 'Service updated successfully!' : 'Service posted successfully!'));
            
            // Redirect to service details or dashboard
            if (data.service_id) {
                window.location.href = `service-details.php?id=${data.service_id}`;
            } else {
                window.location.href = 'dashboard.php';
            }
        } else {
            alert('Error: ' + (data.message || 'Failed to post service'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while posting the service');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Image preview functionality
document.getElementById('images').addEventListener('change', function(e) {
    const files = e.target.files;
    if (files.length > 5) {
        alert('Maximum 5 images allowed');
        e.target.value = '';
        return;
    }
    
    // You can add image preview functionality here if needed
});

// Remove existing image function (for edit mode)
function removeImage(imageName) {
    if (confirm('Are you sure you want to remove this image?')) {
        // Hide the image visually
        event.target.parentElement.style.display = 'none';
        
        // Add the image name to a hidden input for removal during update
        let removedImagesInput = document.getElementById('removed_images');
        if (!removedImagesInput) {
            removedImagesInput = document.createElement('input');
            removedImagesInput.type = 'hidden';
            removedImagesInput.name = 'removed_images';
            removedImagesInput.id = 'removed_images';
            document.getElementById('serviceForm').appendChild(removedImagesInput);
        }
        
        let removedImages = removedImagesInput.value ? removedImagesInput.value.split(',') : [];
        if (!removedImages.includes(imageName)) {
            removedImages.push(imageName);
            removedImagesInput.value = removedImages.join(',');
        }
        
        console.log('Marked for removal:', imageName);
    }
}

// Category-specific placeholder updates
document.getElementById('category').addEventListener('change', function() {
    const category = this.value;
    const titleInput = document.getElementById('title');
    const subjectInput = document.getElementById('subject_skill');
    
    // Update placeholders based on category
    switch(category) {
        case 'tutoring':
            titleInput.placeholder = 'e.g., Math Tutoring for Engineering Students';
            subjectInput.placeholder = 'e.g., Calculus, Algebra, Physics';
            break;
        case 'freelance':
            titleInput.placeholder = 'e.g., Web Development Services';
            subjectInput.placeholder = 'e.g., HTML/CSS, JavaScript, PHP';
            break;
        case 'academic':
            titleInput.placeholder = 'e.g., Research Paper Writing Assistance';
            subjectInput.placeholder = 'e.g., Research Methods, Academic Writing';
            break;
        case 'technical':
            titleInput.placeholder = 'e.g., Computer Repair and Maintenance';
            subjectInput.placeholder = 'e.g., Hardware Repair, Software Installation';
            break;
        case 'creative':
            titleInput.placeholder = 'e.g., Graphic Design Services';
            subjectInput.placeholder = 'e.g., Photoshop, Illustrator, Logo Design';
            break;
        default:
            titleInput.placeholder = 'e.g., Your Service Title';
            subjectInput.placeholder = 'e.g., Your Skill or Subject';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
