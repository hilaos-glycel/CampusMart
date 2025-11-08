<?php
$pageTitle = 'Dashboard';
require_once 'config/config.php';

// Require login BEFORE including header
requireLogin();

require_once 'includes/header.php';

$currentUser = getCurrentUser();
?>

<main class="dashboard">
    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1><i class="fas fa-tachometer-alt"></i> Student Dashboard</h1>
            <p id="welcomeMessage">Welcome back, <?php echo htmlspecialchars($currentUser['first_name']); ?>! Manage your listings and track your activity.</p>
        </div>

        <!-- Dashboard Navigation -->
        <div class="dashboard-nav">
            <button onclick="showDashboardSection('overview')" id="overviewTab" class="active">
                <i class="fas fa-chart-line"></i> Overview
            </button>
            <button onclick="showDashboardSection('myListings')" id="myListingsTab">
                <i class="fas fa-list"></i> My Listings
            </button>
            <button onclick="showDashboardSection('purchases')" id="purchasesTab">
                <i class="fas fa-shopping-cart"></i> Purchases
            </button>
            <button onclick="showDashboardSection('messages')" id="messagesTab">
                <i class="fas fa-comments"></i> Messages
            </button>
            <button onclick="showDashboardSection('wishlist')" id="wishlistTab">
                <i class="fas fa-heart"></i> Wishlist
            </button>
            <button onclick="showDashboardSection('profile')" id="profileTab">
                <i class="fas fa-user"></i> Profile
            </button>
        </div>

        <!-- Overview Section -->
        <div id="overviewSection" class="dashboard-section active">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
                <!-- Stats Cards -->
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3>Total Earnings</h3>
                    <p style="font-size: 2rem; font-weight: bold; color: #28a745; margin: 1rem 0;" id="totalEarnings">₱0.00</p>
                    <p id="completedSales">From 0 completed sales</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3>Active Listings</h3>
                    <p style="font-size: 2rem; font-weight: bold; color: #667eea; margin: 1rem 0;" id="activeListings">0</p>
                    <p>Items currently for sale/rent</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #fd7e14 0%, #f8c291 100%);">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Total Views</h3>
                    <p style="font-size: 2rem; font-weight: bold; color: #fd7e14; margin: 1rem 0;" id="totalViews">0</p>
                    <p>Views on your listings</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Rating</h3>
                    <p style="font-size: 2rem; font-weight: bold; color: #e74c3c; margin: 1rem 0;" id="userRating">0.0 ⭐</p>
                    <p id="totalReviews">Based on 0 reviews</p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                <h3 style="margin-bottom: 1.5rem; color: #333;"><i class="fas fa-clock"></i> Recent Activity</h3>
                <div id="recentActivity">
                    <p style="text-align: center; color: #666; padding: 2rem;">Loading recent activity...</p>
                </div>
            </div>
        </div>

        <!-- My Listings Section -->
        <div id="myListingsSection" class="dashboard-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h3><i class="fas fa-list"></i> My Listings</h3>
                <a href="post-item.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Post New Item
                </a>
            </div>
            
            <div class="product-grid" id="myListingsGrid">
                <p style="text-align: center; color: #666; grid-column: 1 / -1;">Loading your listings...</p>
            </div>
        </div>

        <!-- Purchases Section -->
        <div id="purchasesSection" class="dashboard-section">
            <h3><i class="fas fa-shopping-cart"></i> My Purchases</h3>
            <div id="purchasesGrid" class="product-grid">
                <p style="text-align: center; color: #666; grid-column: 1 / -1;">Loading your purchases...</p>
            </div>
        </div>

        <!-- Messages Section -->
        <div id="messagesSection" class="dashboard-section">
            <h3><i class="fas fa-comments"></i> Messages</h3>
            <div style="background: white; border-radius: 10px; padding: 2rem; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                <div id="messagesList">
                    <p style="text-align: center; color: #666;">Loading messages...</p>
                </div>
            </div>
        </div>

        <!-- Wishlist Section -->
        <div id="wishlistSection" class="dashboard-section">
            <h3><i class="fas fa-heart"></i> My Wishlist</h3>
            <div id="wishlistGrid" class="product-grid">
                <p style="text-align: center; color: #666; grid-column: 1 / -1;">Loading your wishlist...</p>
            </div>
        </div>

        <!-- Profile Section -->
        <div id="profileSection" class="dashboard-section">
            <div style="max-width: 600px;">
                <h3><i class="fas fa-user"></i> Profile Settings</h3>
                
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                    <form id="profileForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-group">
                            <label for="profileName">Full Name</label>
                            <input type="text" id="profileName" value="<?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="profileStudentId">Student ID</label>
                            <input type="text" id="profileStudentId" value="<?php echo htmlspecialchars($currentUser['student_id']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="profileEmail">Email</label>
                            <input type="email" id="profileEmail" value="<?php echo htmlspecialchars($currentUser['email']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="profileCourse">Course/Program</label>
                            <input type="text" id="profileCourse" name="course" value="">
                        </div>
                        
                        <div class="form-group">
                            <label for="profileYear">Year Level</label>
                            <select id="profileYear" name="year_level">
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                                <option value="Graduate">Graduate Student</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="profileBio">Bio (Optional)</label>
                            <textarea id="profileBio" name="bio" rows="3" placeholder="Tell other students about yourself..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="profilePhone">Phone (Optional)</label>
                            <input type="tel" id="profilePhone" name="phone" placeholder="Your contact number">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
let dashboardData = {};

// Load dashboard data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    loadUserProfile();
});

function showDashboardSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.dashboard-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.dashboard-nav button').forEach(button => {
        button.classList.remove('active');
    });
    
    // Show selected section and activate tab
    document.getElementById(sectionName + 'Section').classList.add('active');
    document.getElementById(sectionName + 'Tab').classList.add('active');
    
    // Load specific section data
    loadSectionData(sectionName);
}

function loadDashboardData() {
    fetch(`${siteUrl}/api/get_dashboard_stats.php`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardStats(data.stats);
                loadRecentActivity();
            }
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
        });
}

function updateDashboardStats(stats) {
    document.getElementById('totalEarnings').textContent = formatPrice(stats.total_earnings || 0);
    document.getElementById('completedSales').textContent = `From ${stats.completed_sales || 0} completed sales`;
    document.getElementById('activeListings').textContent = stats.active_listings || 0;
    document.getElementById('totalViews').textContent = stats.total_views || 0;
    document.getElementById('userRating').textContent = `${(stats.rating || 0).toFixed(1)} ⭐`;
    document.getElementById('totalReviews').textContent = `Based on ${stats.total_reviews || 0} reviews`;
}

function loadSectionData(sectionName) {
    switch(sectionName) {
        case 'myListings':
            loadMyListings();
            break;
        case 'purchases':
            loadPurchases();
            break;
        case 'messages':
            loadMessages();
            break;
        case 'wishlist':
            loadWishlist();
            break;
    }
}

function loadMyListings() {
    fetch(`${siteUrl}/api/get_listings.php?user_id=${currentUser.id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMyListings(data.listings);
            }
        })
        .catch(error => {
            console.error('Error loading listings:', error);
        });
}

function displayMyListings(listings) {
    const grid = document.getElementById('myListingsGrid');
    
    if (listings.length === 0) {
        grid.innerHTML = '<p style="text-align: center; color: #666; grid-column: 1 / -1;">You haven\'t posted any items yet. <a href="post-item.php">Post your first item</a>!</p>';
        return;
    }
    
    grid.innerHTML = listings.map(listing => `
        <div class="product-card">
            <div class="product-image">
                ${listing.images && listing.images.length > 0 
                    ? `<img src="${siteUrl}/uploads/listings/${listing.images[0]}" alt="${listing.title}" style="width: 100%; height: 100%; object-fit: cover;">` 
                    : `<i class="fas fa-box"></i>`
                }
            </div>
            <div class="product-info">
                <div class="product-title">${listing.title}</div>
                <div class="product-price">${formatPrice(listing.price)}${listing.type === 'rent' ? (listing.rental_period ? '/' + listing.rental_period : '/month') : ''}</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin: 1rem 0; font-size: 0.9rem;">
                    <div><strong>Views:</strong> ${listing.views}</div>
                    <div><strong>Status:</strong> 
                        <span style="color: ${listing.status === 'active' ? '#28a745' : listing.status === 'sold' ? '#667eea' : '#fd7e14'};">
                            ${listing.status.charAt(0).toUpperCase() + listing.status.slice(1)}
                        </span>
                    </div>
                    <div><strong>Posted:</strong> ${new Date(listing.created_at).toLocaleDateString()}</div>
                    <div><strong>Type:</strong> ${listing.type === 'sale' ? 'Sale' : 'Rent'}</div>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button class="btn btn-secondary" style="flex: 1; padding: 0.5rem; font-size: 0.8rem;" onclick="editListing(${listing.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn" style="flex: 1; padding: 0.5rem; font-size: 0.8rem; background: #e74c3c; color: white;" onclick="deleteListing(${listing.id})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function loadRecentActivity() {
    // Placeholder for recent activity
    document.getElementById('recentActivity').innerHTML = `
        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem 0; border-bottom: 1px solid #e1e5e9;">
            <div style="width: 40px; height: 40px; background: #667eea; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <strong>Welcome to CampusMart!</strong>
                <p style="color: #666; margin: 0;">Start by posting your first item or browsing the marketplace.</p>
                <small style="color: #888;">Just now</small>
            </div>
        </div>
    `;
}

function loadPurchases() {
    document.getElementById('purchasesGrid').innerHTML = '<p style="text-align: center; color: #666; grid-column: 1 / -1;">No purchases yet.</p>';
}

function loadMessages() {
    document.getElementById('messagesList').innerHTML = '<p style="text-align: center; color: #666;">No messages yet.</p>';
}

function loadWishlist() {
    document.getElementById('wishlistGrid').innerHTML = '<p style="text-align: center; color: #666; grid-column: 1 / -1;">Your wishlist is empty.</p>';
}

function loadUserProfile() {
    fetch(`${siteUrl}/api/get_user_profile.php`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                document.getElementById('profileCourse').value = user.course || '';
                document.getElementById('profileYear').value = user.year_level || '';
                document.getElementById('profileBio').value = user.bio || '';
                document.getElementById('profilePhone').value = user.phone || '';
            }
        })
        .catch(error => {
            console.error('Error loading user profile:', error);
        });
}

// Profile form submission
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`${siteUrl}/api/update_profile.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Profile updated successfully!', 'success');
        } else {
            showNotification(data.message || 'Failed to update profile', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating profile:', error);
        showNotification('An error occurred', 'error');
    });
});

function editListing(listingId) {
    window.location.href = `post-item.php?edit=${listingId}`;
}

function deleteListing(listingId) {
    if (confirm('Are you sure you want to delete this listing?')) {
        fetch(`${siteUrl}/api/delete_listing.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                listing_id: listingId,
                csrf_token: csrfToken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Listing deleted successfully', 'success');
                loadMyListings(); // Reload listings
            } else {
                showNotification(data.message || 'Failed to delete listing', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting listing:', error);
            showNotification('An error occurred', 'error');
        });
    }
}

function formatPrice(price) {
    return '₱' + parseFloat(price).toLocaleString('en-PH', {minimumFractionDigits: 2});
}

function showNotification(message, type) {
    // Simple notification system
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.cssText = 'position: fixed; top: 100px; right: 20px; z-index: 9999; max-width: 300px;';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>

<?php require_once 'includes/footer.php'; ?>
