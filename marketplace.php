<?php
$pageTitle = 'Marketplace';
require_once 'includes/header.php';

// Get filter parameters
$category = $_GET['category'] ?? '';
$type = $_GET['type'] ?? 'products'; // Default to products
$search = $_GET['search'] ?? '';
?>

<main style="padding-top: 100px;">
    <div class="container">
        <!-- Header Section -->
        <div style="text-align: center; margin-bottom: 3rem;">
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: #333;">CampusMart</h1>
            <p style="color: #666; font-size: 1.2rem;">Browse products and services by JH students</p>
        </div>

        <!-- Main Tabs: Products vs Services -->
        <div class="main-tabs" style="display: flex; justify-content: center; margin-bottom: 2rem; gap: 1rem;">
            <button class="main-tab <?php echo ($type === 'products' || $type === '') ? 'active' : ''; ?>" 
                    onclick="switchMainTab('products')" id="productsMainTab">
                <i class="fas fa-shopping-bag"></i> Products
            </button>
            <button class="main-tab <?php echo ($type === 'services') ? 'active' : ''; ?>" 
                    onclick="switchMainTab('services')" id="servicesMainTab">
                <i class="fas fa-handshake"></i> Services
            </button>
        </div>

        <!-- Search Bar -->
        <div class="search-bar" style="max-width: 600px; margin: 0 auto 2rem auto; position: relative;">
            <input type="text" id="searchInput" 
                   placeholder="Search for items, books, electronics, tutoring, freelance..." 
                   value="<?php echo htmlspecialchars($search); ?>"
                   style="width: 100%; padding: 15px 60px 15px 20px; border: 2px solid #e1e5e9; border-radius: 50px; font-size: 16px;" />
            <button onclick="searchItems()" 
                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: #28a745; color: white; border: none; padding: 10px 15px; border-radius: 50%; cursor: pointer;">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <!-- Products Section -->
        <div id="productsSection" class="content-section" style="<?php echo ($type === 'services') ? 'display: none;' : ''; ?>">
            <!-- Product Filters -->
            <div class="filters" style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <div class="filter-row" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center; justify-content: center;">
                    <div class="filter-group">
                        <label style="font-weight: 600; margin-bottom: 5px; display: block;">Category:</label>
                        <select id="categoryFilter" onchange="filterProducts()" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="">All Categories</option>
                            <option value="books" <?php echo ($category === 'books') ? 'selected' : ''; ?>>Books</option>
                            <option value="electronics" <?php echo ($category === 'electronics') ? 'selected' : ''; ?>>Electronics</option>
                            <option value="clothing" <?php echo ($category === 'clothing') ? 'selected' : ''; ?>>Clothing</option>
                            <option value="accessories" <?php echo ($category === 'accessories') ? 'selected' : ''; ?>>Accessories</option>
                            <option value="sports" <?php echo ($category === 'sports') ? 'selected' : ''; ?>>Sports</option>
                            <option value="other" <?php echo ($category === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label style="font-weight: 600; margin-bottom: 5px; display: block;">Condition:</label>
                        <select id="conditionFilter" onchange="filterProducts()" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="">Any Condition</option>
                            <option value="new">New</option>
                            <option value="like_new">Like New</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label style="font-weight: 600; margin-bottom: 5px; display: block;">Price Range:</label>
                        <select id="priceFilter" onchange="filterProducts()" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="">Any Price</option>
                            <option value="0-500">₱0 - ₱500</option>
                            <option value="500-1000">₱500 - ₱1,000</option>
                            <option value="1000-2500">₱1,000 - ₱2,500</option>
                            <option value="2500+">₱2,500+</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div id="productsGrid" class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <!-- Products will be loaded here via JavaScript -->
            </div>
        </div>

        <!-- Services Section -->
        <div id="servicesSection" class="content-section" style="<?php echo ($type !== 'services') ? 'display: none;' : ''; ?>">
            <!-- Service Type Tabs -->
            <div class="service-tabs" style="display: flex; justify-content: center; margin-bottom: 2rem; gap: 1rem;">
                <button class="service-tab active" onclick="filterServicesByCategory('all')" id="allServicesTab"
                        style="padding: 12px 24px; border: 2px solid #28a745; background: #28a745; color: white; border-radius: 25px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-th"></i> All Services
                </button>
                <button class="service-tab" onclick="filterServicesByCategory('tutoring')" id="tutoringTab"
                        style="padding: 12px 24px; border: 2px solid #28a745; background: white; color: #28a745; border-radius: 25px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-chalkboard-teacher"></i> Tutoring
                </button>
                <button class="service-tab" onclick="filterServicesByCategory('freelance')" id="freelanceTab"
                        style="padding: 12px 24px; border: 2px solid #28a745; background: white; color: #28a745; border-radius: 25px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-tools"></i> Freelance
                </button>
            </div>

            <!-- Service Filters -->
            <div class="filters" style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <div class="filter-row" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center; justify-content: center;">
                    <div class="filter-group">
                        <label style="font-weight: 600; margin-bottom: 5px; display: block;">Category:</label>
                        <select id="serviceCategoryFilter" onchange="filterServices()" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="">All Categories</option>
                            <option value="tutoring">Tutoring</option>
                            <option value="freelance">Freelance</option>
                            <option value="academic">Academic Help</option>
                            <option value="technical">Technical Services</option>
                            <option value="creative">Creative Services</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label style="font-weight: 600; margin-bottom: 5px; display: block;">Condition:</label>
                        <select id="serviceConditionFilter" onchange="filterServices()" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="">Any Availability</option>
                            <option value="available">Available Now</option>
                            <option value="busy">Busy</option>
                            <option value="weekend">Weekends Only</option>
                            <option value="flexible">Flexible Schedule</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label style="font-weight: 600; margin-bottom: 5px; display: block;">Price Range:</label>
                        <select id="servicePriceFilter" onchange="filterServices()" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="">Any Price</option>
                            <option value="0-200">₱0 - ₱200/hr</option>
                            <option value="200-500">₱200 - ₱500/hr</option>
                            <option value="500-1000">₱500 - ₱1,000/hr</option>
                            <option value="1000+">₱1,000+/hr</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Services Grid -->
            <div id="servicesGrid" class="services-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
                <!-- Services will be loaded here via JavaScript -->
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" style="text-align: center; padding: 40px; display: none;">
            <div class="loading-spinner" style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #28a745; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="margin-top: 15px; color: #666;">Loading...</p>
        </div>

        <!-- No Results State -->
        <div id="noResultsState" style="text-align: center; padding: 40px; display: none;">
            <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
            <h3 style="color: #666; margin-bottom: 10px;">No items found</h3>
            <p style="color: #999;">Try adjusting your search or filters</p>
        </div>
    </div>
</main>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.main-tab {
    padding: 15px 30px;
    border: 2px solid #28a745;
    background: white;
    color: #28a745;
    border-radius: 30px;
    cursor: pointer;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.main-tab.active {
    background: #28a745;
    color: white;
}

.main-tab:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.service-tab {
    transition: all 0.3s ease;
}

.service-tab:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.service-tab.active {
    background: #28a745 !important;
    color: white !important;
}

.product-card, .service-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}

.product-card:hover, .service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.price-tag {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 18px;
}

.condition-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.condition-new { background: #d4edda; color: #155724; }
.condition-like_new { background: #cce5ff; color: #004085; }
.condition-good { background: #fff3cd; color: #856404; }
.condition-fair { background: #f8d7da; color: #721c24; }
</style>

<script>
let currentType = '<?php echo $type ?: "products"; ?>';
let currentServiceCategory = 'all';

// Switch between Products and Services
function switchMainTab(type) {
    currentType = type;
    
    // Update tab appearance
    document.querySelectorAll('.main-tab').forEach(tab => tab.classList.remove('active'));
    document.getElementById(type + 'MainTab').classList.add('active');
    
    // Show/hide sections
    document.getElementById('productsSection').style.display = type === 'products' ? 'block' : 'none';
    document.getElementById('servicesSection').style.display = type === 'services' ? 'block' : 'none';
    
    // Load appropriate content
    if (type === 'products') {
        loadProducts();
    } else {
        loadServices();
    }
    
    // Update URL
    const url = new URL(window.location);
    url.searchParams.set('type', type);
    window.history.pushState({}, '', url);
}

// Search function
function searchItems() {
    const searchTerm = document.getElementById('searchInput').value;
    const url = new URL(window.location);
    url.searchParams.set('search', searchTerm);
    window.history.pushState({}, '', url);
    
    if (currentType === 'products') {
        loadProducts();
    } else {
        loadServices();
    }
}

// Filter products
function filterProducts() {
    loadProducts();
}

// Filter services by category
function filterServicesByCategory(category) {
    currentServiceCategory = category;
    
    // Update tab appearance
    document.querySelectorAll('.service-tab').forEach(tab => tab.classList.remove('active'));
    document.getElementById(category === 'all' ? 'allServicesTab' : category + 'Tab').classList.add('active');
    
    loadServices();
}

// Load products
function loadProducts() {
    showLoading();
    
    const params = new URLSearchParams({
        category: document.getElementById('categoryFilter')?.value || '',
        condition: document.getElementById('conditionFilter')?.value || '',
        price: document.getElementById('priceFilter')?.value || '',
        search: document.getElementById('searchInput').value || ''
    });
    
    fetch(`api/get_listings.php?${params}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success && data.listings && data.listings.length > 0) {
                displayProducts(data.listings);
            } else {
                showNoResults();
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error loading products:', error);
            showNoResults();
        });
}

// Load services
function loadServices() {
    showLoading();
    
    const params = new URLSearchParams({
        category: currentServiceCategory !== 'all' ? currentServiceCategory : '',
        search: document.getElementById('searchInput').value || ''
    });
    
    fetch(`api/get_services.php?${params}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success && data.services.length > 0) {
                displayServices(data.services);
            } else {
                showNoResults();
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error loading services:', error);
            showNoResults();
        });
}

// Display products
function displayProducts(products) {
    const grid = document.getElementById('productsGrid');
    grid.innerHTML = products.map(product => `
        <div class="product-card" onclick="viewProduct(${product.id})">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <h3 style="margin: 0; color: #333; font-size: 18px;">${product.title}</h3>
                <span class="condition-badge condition-${product.condition_item.toLowerCase().replace(' ', '_')}">${product.condition_item}</span>
            </div>
            <p style="color: #666; margin-bottom: 15px; line-height: 1.5;">${product.description.substring(0, 100)}...</p>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="price-tag">₱${parseFloat(product.price).toLocaleString()}</span>
                <div style="text-align: right; color: #888; font-size: 14px;">
                    <div><i class="fas fa-user"></i> ${product.seller_name}</div>
                    <div><i class="fas fa-clock"></i> ${formatDate(product.created_at)}</div>
                </div>
            </div>
        </div>
    `).join('');
}

// Display services
function displayServices(services) {
    const grid = document.getElementById('servicesGrid');
    grid.innerHTML = services.map(service => `
        <div class="service-card" onclick="viewService(${service.id})">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <h3 style="margin: 0; color: #333; font-size: 18px;">${service.title}</h3>
                <span style="background: #17a2b8; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: 600;">
                    ${service.category.toUpperCase()}
                </span>
            </div>
            <p style="color: #666; margin-bottom: 15px; line-height: 1.5;">${service.description.substring(0, 120)}...</p>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="price-tag">₱${parseFloat(service.price_per_hour).toLocaleString()}/hr</span>
                <div style="text-align: right; color: #888; font-size: 14px;">
                    <div><i class="fas fa-user"></i> ${service.provider_name}</div>
                    <div><i class="fas fa-star"></i> ${service.rating || 'New'}</div>
                </div>
            </div>
        </div>
    `).join('');
}

// Utility functions
function showLoading() {
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('noResultsState').style.display = 'none';
    document.getElementById('productsGrid').style.display = 'none';
    document.getElementById('servicesGrid').style.display = 'none';
}

function hideLoading() {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('productsGrid').style.display = 'grid';
    document.getElementById('servicesGrid').style.display = 'grid';
}

function showNoResults() {
    document.getElementById('noResultsState').style.display = 'block';
    document.getElementById('productsGrid').style.display = 'none';
    document.getElementById('servicesGrid').style.display = 'none';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

function viewProduct(id) {
    window.location.href = `product-details.php?id=${id}`;
}

function viewService(id) {
    window.location.href = `service-details.php?id=${id}`;
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    if (currentType === 'products') {
        loadProducts();
    } else {
        loadServices();
    }
});

// Handle search on Enter key
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchItems();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
