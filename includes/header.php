<?php
require_once dirname(__DIR__) . '/config/config.php';
$currentUser = getCurrentUser();
$isLoggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME . ' - ' . SITE_DESCRIPTION; ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/header_redesigned.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo SITE_URL . '/' . $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Main Header -->
    <header class="main-header">
        <div class="header-container">
            <!-- Left: Logo Section -->
            <div class="logo-section">
                <a href="<?php echo SITE_URL; ?>/index.php" class="logo-link">
                    <div class="logo-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="logo-text">
                        <span class="logo-title">CampusMart</span>
                        <span class="logo-subtitle">JH Cerilles Marketplace</span>
                    </div>
                </a>
            </div>

            <!-- Center: Navigation Links -->
            <nav class="main-navigation">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/index.php" 
                           class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                            <div class="nav-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <span class="nav-text">Home</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/marketplace.php" 
                           class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'marketplace.php' || basename($_SERVER['PHP_SELF']) == 'services.php') ? 'active' : ''; ?>">
                            <div class="nav-icon">
                                <i class="fas fa-store"></i>
                            </div>
                            <span class="nav-text">Marketplace</span>
                        </a>
                    </li>

                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/post-item.php" 
                               class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'post-item.php') ? 'active' : ''; ?>">
                                <div class="nav-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <span class="nav-text">Sell/Rent</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/post-service.php" 
                               class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'post-service.php') ? 'active' : ''; ?>">
                                <div class="nav-icon">
                                    <i class="fas fa-handshake"></i>
                                </div>
                                <span class="nav-text">Offer Service</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/dashboard.php" 
                               class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                                <div class="nav-icon">
                                    <i class="fas fa-tachometer-alt"></i>
                                </div>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <!-- Right: User Profile Section -->
            <div class="user-section">
                <?php if ($isLoggedIn): ?>
                    <div class="user-profile-dropdown">
                        <button class="profile-trigger" id="profileTrigger">
                            <div class="profile-avatar">
                                <img src="<?php echo SITE_URL; ?>/assets/images/default-avatar.png" 
                                     alt="Profile" 
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="avatar-fallback">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="profile-info">
                                <span class="profile-name"><?php echo htmlspecialchars($currentUser['first_name']); ?></span>
                                <i class="fas fa-chevron-down dropdown-arrow"></i>
                            </div>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="profile-dropdown-menu" id="profileDropdown">
                            <!-- User Info Header -->
                            <div class="dropdown-header">
                                <div class="user-avatar-large">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/default-avatar.png" 
                                         alt="Profile" 
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="avatar-large-fallback">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="user-details">
                                    <h4 class="user-full-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h4>
                                    <p class="user-email"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                                </div>
                            </div>

                            <!-- Dropdown Links -->
                            <div class="dropdown-links">
                                <a href="<?php echo SITE_URL; ?>/profile.php" class="dropdown-link">
                                    <i class="fas fa-user-edit"></i>
                                    <span>My Profile</span>
                                </a>
                                
                                <a href="<?php echo SITE_URL; ?>/messages.php" class="dropdown-link">
                                    <i class="fas fa-comments"></i>
                                    <span>Messages</span>
                                    <span class="notification-badge">3</span>
                                </a>
                                
                                <a href="<?php echo SITE_URL; ?>/wishlist.php" class="dropdown-link">
                                    <i class="fas fa-heart"></i>
                                    <span>Wishlist</span>
                                </a>
                                
                                <a href="<?php echo SITE_URL; ?>/settings.php" class="dropdown-link">
                                    <i class="fas fa-cog"></i>
                                    <span>Settings</span>
                                </a>
                                
                                <div class="dropdown-divider"></div>
                                
                                <a href="<?php echo SITE_URL; ?>/api/logout.php" class="dropdown-link logout-link">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="<?php echo SITE_URL; ?>/login.php" class="auth-btn login-btn">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/register.php" class="auth-btn register-btn">
                            <i class="fas fa-user-plus"></i>
                            <span>Register</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>

        <!-- Mobile Navigation -->
        <div class="mobile-navigation" id="mobileNavigation">
            <div class="mobile-nav-content">
                <?php if ($isLoggedIn): ?>
                    <!-- Mobile User Info -->
                    <div class="mobile-user-info">
                        <div class="mobile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="mobile-user-details">
                            <h4><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h4>
                            <p><?php echo htmlspecialchars($currentUser['email']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Mobile Navigation Links -->
                <nav class="mobile-nav-links">
                    <a href="<?php echo SITE_URL; ?>/index.php" class="mobile-nav-link">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                    
                    <a href="<?php echo SITE_URL; ?>/marketplace.php" class="mobile-nav-link">
                        <i class="fas fa-store"></i>
                        <span>Marketplace</span>
                    </a>

                    <?php if ($isLoggedIn): ?>
                        <a href="<?php echo SITE_URL; ?>/post-item.php" class="mobile-nav-link">
                            <i class="fas fa-plus-circle"></i>
                            <span>Sell/Rent</span>
                        </a>
                        
                        <a href="<?php echo SITE_URL; ?>/dashboard.php" class="mobile-nav-link">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>

                        <div class="mobile-divider"></div>

                        <a href="<?php echo SITE_URL; ?>/profile.php" class="mobile-nav-link">
                            <i class="fas fa-user-edit"></i>
                            <span>My Profile</span>
                        </a>
                        
                        <a href="<?php echo SITE_URL; ?>/messages.php" class="mobile-nav-link">
                            <i class="fas fa-comments"></i>
                            <span>Messages</span>
                            <span class="mobile-badge">3</span>
                        </a>
                        
                        <a href="<?php echo SITE_URL; ?>/wishlist.php" class="mobile-nav-link">
                            <i class="fas fa-heart"></i>
                            <span>Wishlist</span>
                        </a>
                        
                        <a href="<?php echo SITE_URL; ?>/settings.php" class="mobile-nav-link">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                        
                        <a href="<?php echo SITE_URL; ?>/api/logout.php" class="mobile-nav-link logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    <?php else: ?>
                        <div class="mobile-divider"></div>
                        
                        <a href="<?php echo SITE_URL; ?>/login.php" class="mobile-nav-link">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                        
                        <a href="<?php echo SITE_URL; ?>/register.php" class="mobile-nav-link">
                            <i class="fas fa-user-plus"></i>
                            <span>Register</span>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>

        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
    </header>

    <!-- Content Spacer -->
    <div class="header-spacer"></div>

    <!-- Notification Container -->
    <div id="notificationContainer" class="notification-container"></div>

    <!-- JavaScript -->
    <script>
        // Global variables
        window.csrfToken = '<?php echo generateCSRFToken(); ?>';
        window.siteUrl = '<?php echo SITE_URL; ?>';
        window.isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        <?php if ($isLoggedIn): ?>
        window.currentUser = <?php echo json_encode($currentUser); ?>;
        <?php endif; ?>

        // Header functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Profile dropdown functionality
            const profileTrigger = document.getElementById('profileTrigger');
            const profileDropdown = document.getElementById('profileDropdown');
            
            if (profileTrigger && profileDropdown) {
                profileTrigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    profileDropdown.classList.toggle('show');
                    profileTrigger.classList.toggle('active');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!profileTrigger.contains(e.target) && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.remove('show');
                        profileTrigger.classList.remove('active');
                    }
                });
            }

            // Mobile menu functionality
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileNavigation = document.getElementById('mobileNavigation');
            const mobileOverlay = document.getElementById('mobileOverlay');

            if (mobileMenuToggle && mobileNavigation && mobileOverlay) {
                mobileMenuToggle.addEventListener('click', function() {
                    mobileMenuToggle.classList.toggle('active');
                    mobileNavigation.classList.toggle('show');
                    mobileOverlay.classList.toggle('show');
                    document.body.classList.toggle('mobile-menu-open');
                });

                mobileOverlay.addEventListener('click', function() {
                    mobileMenuToggle.classList.remove('active');
                    mobileNavigation.classList.remove('show');
                    mobileOverlay.classList.remove('show');
                    document.body.classList.remove('mobile-menu-open');
                });

                // Close mobile menu on link click
                document.querySelectorAll('.mobile-nav-link').forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenuToggle.classList.remove('active');
                        mobileNavigation.classList.remove('show');
                        mobileOverlay.classList.remove('show');
                        document.body.classList.remove('mobile-menu-open');
                    });
                });
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    // Close mobile menu on desktop
                    if (mobileMenuToggle) mobileMenuToggle.classList.remove('active');
                    if (mobileNavigation) mobileNavigation.classList.remove('show');
                    if (mobileOverlay) mobileOverlay.classList.remove('show');
                    document.body.classList.remove('mobile-menu-open');
                }
            });
        });
    </script>
