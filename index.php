<?php
$pageTitle = 'Home';
require_once 'includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to CampusMart</h1>
            <p class="hero-subtitle">The Exclusive JH Cerilles State College Student Marketplace</p>
            <p class="hero-description">
                A secure and centralized platform for JH Cerilles State College Pagadian City Extension Campus students to buy, sell, rent products, 
                and offer services like tutoring and freelance work.
            </p>
            <div class="hero-buttons">
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-primary">Join Now</a>
                <?php else: ?>
                    <a href="post-item.php" class="btn btn-primary">Post Item</a>
                <?php endif; ?>
                <a href="marketplace.php" class="btn btn-secondary">Browse Marketplace</a>
            </div>
        </div>
        <div class="hero-image">
            <i class="fas fa-store fa-5x"></i>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2>Why Choose CampusMart?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Verified Students Only</h3>
                    <p>Secure platform exclusive to JH students with ID verification for safe transactions.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Buy & Sell</h3>
                    <p>Post items for sale or browse through various categories like school supplies, clothing, and gadgets.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Rent & Services</h3>
                    <p>Rent items or offer services like tutoring and freelance work to fellow students.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Community Focused</h3>
                    <p>Build stronger connections within the JH student community through trusted transactions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <h2>Popular Categories</h2>
            <div class="categories-grid">
                <div class="category-card" onclick="location.href='marketplace.php?category=school-supplies'">
                    <i class="fas fa-book"></i>
                    <h4>School Supplies</h4>
                    <p>Textbooks, stationery, and study materials</p>
                </div>
                <div class="category-card" onclick="location.href='marketplace.php?category=clothing'">
                    <i class="fas fa-tshirt"></i>
                    <h4>Clothing</h4>
                    <p>Fashion items and accessories</p>
                </div>
                <div class="category-card" onclick="location.href='marketplace.php?category=electronics'">
                    <i class="fas fa-laptop"></i>
                    <h4>Electronics</h4>
                    <p>Gadgets, laptops, and tech accessories</p>
                </div>
                <div class="category-card" onclick="location.href='services.php?category=tutoring'">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h4>Tutoring</h4>
                    <p>Academic help from fellow students</p>
                </div>
                <div class="category-card" onclick="location.href='services.php?category=freelance'">
                    <i class="fas fa-tools"></i>
                    <h4>Freelance</h4>
                    <p>Design, writing, and technical services</p>
                </div>
                <div class="category-card" onclick="location.href='marketplace.php?type=rent'">
                    <i class="fas fa-home"></i>
                    <h4>Rentals</h4>
                    <p>Temporary use items and equipment</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Listings Section -->
    <section class="recent-listings" style="padding: 4rem 0; background: #f8f9fa;">
        <div class="container">
            <div style="text-align: center; margin-bottom: 3rem;">
                <h2>Recent Listings</h2>
                <p style="color: #666;">Check out the latest items posted by JH students</p>
            </div>
            <div id="recentListingsGrid" class="product-grid">
                <!-- Recent listings will be loaded here -->
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="marketplace.php" class="btn btn-primary">View All Listings</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>About CampusMart</h2>
                    <h3>Purpose</h3>
                    <p>
                        CampusMart provides JH Cerilles State College Pagadian City Extension Campus students with a secure and centralized platform for buying, selling, 
                        renting products, and offering services such as tutoring and freelance work. Unlike scattered 
                        postings on social media or informal groups, this system ensures that only verified students 
                        can participate, making transactions safer, more organized, and exclusive to the school community.
                    </p>
                    
                    <h3>Key Features</h3>
                    <ul>
                        <li>Verified student accounts through student ID login</li>
                        <li>Product posting (items for sale or rent, with details and images)</li>
                        <li>Service offering (tutoring and freelance services)</li>
                        <li>Category browsing and searching</li>
                        <li>Safe and trusted environment limited to the JH community</li>
                    </ul>
                </div>
                <div class="about-image">
                    <i class="fas fa-university fa-4x"></i>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
// Load recent listings on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRecentListings();
});

function loadRecentListings() {
    fetch(siteUrl + '/api/get_listings.php?limit=6&sort=newest')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRecentListings(data.listings);
            }
        })
        .catch(error => {
            console.error('Error loading recent listings:', error);
        });
}

function displayRecentListings(listings) {
    const grid = document.getElementById('recentListingsGrid');
    
    if (listings.length === 0) {
        grid.innerHTML = '<p style="text-align: center; color: #666; grid-column: 1 / -1;">No listings available yet.</p>';
        return;
    }
    
    grid.innerHTML = listings.map(listing => `
        <div class="product-card" onclick="viewListing(${listing.id})">
            <div class="product-image">
                ${listing.images && listing.images.length > 0 
                    ? `<img src="${siteUrl}/uploads/listings/${listing.images[0]}" alt="${listing.title}" style="width: 100%; height: 100%; object-fit: cover;">` 
                    : `<i class="fas fa-box"></i>`
                }
            </div>
            <div class="product-info">
                <div class="product-title">${listing.title}</div>
                <div class="product-price">${formatPrice(listing.price)}${listing.type === 'rent' ? (listing.rental_period ? '/' + listing.rental_period : '/month') : ''}</div>
                <div class="product-description">${listing.description.substring(0, 80)}${listing.description.length > 80 ? '...' : ''}</div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                    <span class="product-seller" style="font-size: 0.85rem;">${listing.seller_name}</span>
                    <span style="background: ${listing.type === 'sale' ? '#667eea' : '#28a745'}; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem;">
                        ${listing.type === 'sale' ? 'For Sale' : 'For Rent'}
                    </span>
                </div>
            </div>
        </div>
    `).join('');
}

function viewListing(listingId) {
    window.location.href = `marketplace.php?view=${listingId}`;
}

function formatPrice(price) {
    return '₱' + parseFloat(price).toLocaleString('en-PH', {minimumFractionDigits: 2});
}
</script>

<?php require_once 'includes/footer.php'; ?>
