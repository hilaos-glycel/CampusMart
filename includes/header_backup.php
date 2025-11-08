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
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo SITE_URL . '/' . $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <!-- Logo Section -->
                <a href="<?php echo SITE_URL; ?>/index.php" class="nav-logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span class="logo-text">CampusMart</span>
                </a>

                <!-- Desktop Navigation Menu -->
                <ul class="nav-menu" id="navMenu">
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/index.php" 
                           class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/marketplace.php" 
                           class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'marketplace.php' || basename($_SERVER['PHP_SELF']) == 'services.php') ? 'active' : ''; ?>">
                            <i class="fas fa-store"></i>
                            <span>Marketplace</span>
                        </a>
                    </li>

                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/post-item.php" 
                               class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'post-item.php') ? 'active' : ''; ?>">
                                <i class="fas fa-plus-circle"></i>
                                <span>Sell/Rent</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/dashboard.php" 
                               class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <!-- User Dropdown -->
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="userDropdown">
                                <i class="fas fa-user-circle"></i>
                                <span><?php echo htmlspecialchars($currentUser['first_name']); ?></span>
                                <i class="fas fa-chevron-down dropdown-arrow"></i>
                            </a>
                            <div class="dropdown-menu" id="userDropdownMenu">
                                <div class="dropdown-header">
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
                                            <div class="user-email"><?php echo htmlspecialchars($currentUser['email']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a href="<?php echo SITE_URL; ?>/profile.php" class="dropdown-item">
                                    <i class="fas fa-user-edit"></i>
                                    <span>My Profile</span>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/messages.php" class="dropdown-item">
                                    <i class="fas fa-comments"></i>
                                    <span>Messages</span>
                                    <span class="badge">3</span>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/wishlist.php" class="dropdown-item">
                                    <i class="fas fa-heart"></i>
                                    <span>Wishlist</span>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/settings.php" class="dropdown-item">
                                    <i class="fas fa-cog"></i>
                                    <span>Settings</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="<?php echo SITE_URL; ?>/api/logout.php" class="dropdown-item logout-item">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/register.php" 
                               class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'register.php') ? 'active' : ''; ?>">
                                <i class="fas fa-user-plus"></i>
                                <span>Register</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/login.php" 
                               class="nav-link login-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : ''; ?>">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Login</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Mobile Hamburger Menu -->
                <button class="hamburger" id="hamburgerBtn" aria-label="Toggle navigation">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
            </div>
        </nav>
    </header>

    <!-- Mobile Navigation Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Notification Container -->
    <div id="notificationContainer" class="notification-container"></div>

    <!-- CSRF Token and Global JavaScript Variables -->
    <script>
        // Global variables
        window.csrfToken = '<?php echo generateCSRFToken(); ?>';
        window.siteUrl = '<?php echo SITE_URL; ?>';
        window.isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        <?php if ($isLoggedIn): ?>
        window.currentUser = <?php echo json_encode($currentUser); ?>;
        <?php endif; ?>

        // Enhanced Navigation Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.getElementById('hamburgerBtn');
            const navMenu = document.getElementById('navMenu');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const userDropdown = document.getElementById('userDropdown');
            const userDropdownMenu = document.getElementById('userDropdownMenu');

            // Hamburger Menu Toggle
            if (hamburger && navMenu) {
                hamburger.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleMobileMenu();
                });

                // Close mobile menu when clicking overlay
                if (mobileOverlay) {
                    mobileOverlay.addEventListener('click', closeMobileMenu);
                }

                // Close mobile menu when clicking nav links
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            closeMobileMenu();
                        }
                    });
                });
            }

            // Desktop Dropdown Menu
            if (userDropdown && userDropdownMenu) {
                let dropdownTimeout;

                userDropdown.addEventListener('mouseenter', function() {
                    clearTimeout(dropdownTimeout);
                    showDropdown();
                });

                userDropdown.parentElement.addEventListener('mouseleave', function() {
                    dropdownTimeout = setTimeout(hideDropdown, 300);
                });

                userDropdownMenu.addEventListener('mouseenter', function() {
                    clearTimeout(dropdownTimeout);
                });

                userDropdownMenu.addEventListener('mouseleave', function() {
                    dropdownTimeout = setTimeout(hideDropdown, 300);
                });

                // Click to toggle on mobile
                userDropdown.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        toggleDropdown();
                    }
                });
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (userDropdownMenu && !userDropdown.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                    hideDropdown();
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    closeMobileMenu();
                }
            });

            // Functions
            function toggleMobileMenu() {
                hamburger.classList.toggle('active');
                navMenu.classList.toggle('active');
                mobileOverlay.classList.toggle('active');
                document.body.classList.toggle('menu-open');
            }

            function closeMobileMenu() {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
                mobileOverlay.classList.remove('active');
                document.body.classList.remove('menu-open');
            }

            function showDropdown() {
                if (userDropdownMenu) {
                    userDropdownMenu.classList.add('show');
                    userDropdown.classList.add('active');
                }
            }

            function hideDropdown() {
                if (userDropdownMenu) {
                    userDropdownMenu.classList.remove('show');
                    userDropdown.classList.remove('active');
                }
            }

            function toggleDropdown() {
                if (userDropdownMenu.classList.contains('show')) {
                    hideDropdown();
                } else {
                    showDropdown();
                }
            }
        });
    </script>
