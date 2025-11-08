<?php
$pageTitle = 'Navigation Test';
require_once 'includes/header.php';
?>

<main style="padding: 2rem 0;">
    <div class="container">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h1 style="color: #333; margin-bottom: 1rem;">🚀 Enhanced Navigation System</h1>
            <p style="color: #666; font-size: 1.2rem;">Testing the completely redesigned header navigation</p>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #28a745; margin-bottom: 1rem;">✅ Navigation Improvements</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #333; margin-bottom: 0.5rem;">🎨 Enhanced Design</h3>
                    <ul style="color: #666; margin: 0; padding-left: 1.5rem;">
                        <li>Modern gradient logo with hover effects</li>
                        <li>Improved button styling and animations</li>
                        <li>Better spacing and visual hierarchy</li>
                        <li>Professional dropdown with user info</li>
                    </ul>
                </div>
                
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #333; margin-bottom: 0.5rem;">📱 Mobile Experience</h3>
                    <ul style="color: #666; margin: 0; padding-left: 1.5rem;">
                        <li>Slide-out mobile menu from left</li>
                        <li>Smooth hamburger animations</li>
                        <li>Mobile overlay with backdrop blur</li>
                        <li>Touch-optimized button sizes</li>
                    </ul>
                </div>
                
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #333; margin-bottom: 0.5rem;">🖱️ Interactive Features</h3>
                    <ul style="color: #666; margin: 0; padding-left: 1.5rem;">
                        <li>Hover effects with micro-animations</li>
                        <li>Active state indicators</li>
                        <li>Smooth transitions and easing</li>
                        <li>Enhanced user feedback</li>
                    </ul>
                </div>
                
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #333; margin-bottom: 0.5rem;">♿ Accessibility</h3>
                    <ul style="color: #666; margin: 0; padding-left: 1.5rem;">
                        <li>Proper focus states</li>
                        <li>ARIA labels for screen readers</li>
                        <li>Keyboard navigation support</li>
                        <li>High contrast mode support</li>
                    </ul>
                </div>
            </div>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #333; margin-bottom: 1rem;">🧪 Test Instructions</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div style="background: #e8f5e8; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #28a745;">
                    <h3 style="color: #155724; margin-bottom: 0.5rem;">🖥️ Desktop Testing</h3>
                    <ul style="color: #155724; margin: 0; padding-left: 1.5rem;">
                        <li>Hover over logo to see scale effect</li>
                        <li>Test navigation button hover animations</li>
                        <li>Check user dropdown functionality</li>
                        <li>Verify active state indicators</li>
                    </ul>
                </div>
                
                <div style="background: #cce5ff; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #007bff;">
                    <h3 style="color: #004085; margin-bottom: 0.5rem;">📱 Mobile Testing</h3>
                    <ul style="color: #004085; margin: 0; padding-left: 1.5rem;">
                        <li>Resize browser window to < 768px</li>
                        <li>Click hamburger menu to open</li>
                        <li>Test slide-out animation</li>
                        <li>Verify mobile dropdown behavior</li>
                    </ul>
                </div>
            </div>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <h2 style="color: #333; margin-bottom: 1rem;">🔧 Technical Improvements</h2>
            
            <div style="background: #fff3cd; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #ffc107; margin-bottom: 1.5rem;">
                <h3 style="color: #856404; margin-bottom: 0.5rem;">Enhanced JavaScript Functionality:</h3>
                <ul style="color: #856404; margin: 0; padding-left: 1.5rem;">
                    <li><strong>Smart Menu Handling:</strong> Automatic close on outside clicks</li>
                    <li><strong>Responsive Behavior:</strong> Adapts to window resize events</li>
                    <li><strong>Smooth Animations:</strong> CSS3 transitions with cubic-bezier easing</li>
                    <li><strong>Body Scroll Lock:</strong> Prevents background scrolling on mobile</li>
                </ul>
            </div>
            
            <div style="background: #f8d7da; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #dc3545;">
                <h3 style="color: #721c24; margin-bottom: 0.5rem;">CSS Architecture:</h3>
                <ul style="color: #721c24; margin: 0; padding-left: 1.5rem;">
                    <li><strong>Mobile-First Design:</strong> Responsive breakpoints at 768px and 480px</li>
                    <li><strong>Modern CSS:</strong> Flexbox, Grid, and CSS custom properties</li>
                    <li><strong>Performance:</strong> Hardware-accelerated transforms and transitions</li>
                    <li><strong>Cross-Browser:</strong> Vendor prefixes and fallbacks included</li>
                </ul>
            </div>
        </div>

        <?php if ($isLoggedIn): ?>
        <div style="text-align: center; margin-top: 2rem; padding: 1rem; background: #d4edda; border-radius: 8px;">
            <p style="color: #155724; margin: 0;"><strong>✅ Logged in as <?php echo htmlspecialchars($currentUser['first_name']); ?>!</strong> Test the user dropdown menu in the header.</p>
        </div>
        <?php else: ?>
        <div style="text-align: center; margin-top: 2rem; padding: 1rem; background: #cce5ff; border-radius: 8px;">
            <p style="color: #004085; margin: 0;"><strong>ℹ️ Not logged in.</strong> <a href="login.php" style="color: #004085;">Log in</a> to test the full user dropdown experience.</p>
        </div>
        <?php endif; ?>
    </div>
</main>

<style>
/* Test page specific styles */
.test-section {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.test-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.test-card {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.test-card h3 {
    color: #333;
    margin-bottom: 0.5rem;
}

.test-card ul {
    color: #666;
    margin: 0;
    padding-left: 1.5rem;
}

/* Mobile indicator */
@media (max-width: 768px) {
    .mobile-indicator {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 25px;
        font-size: 0.9rem;
        z-index: 1001;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
}
</style>

<div class="mobile-indicator" style="display: none;">📱 Mobile Mode Active</div>

<script>
// Show mobile indicator on mobile devices
function updateMobileIndicator() {
    const indicator = document.querySelector('.mobile-indicator');
    if (window.innerWidth <= 768) {
        indicator.style.display = 'block';
    } else {
        indicator.style.display = 'none';
    }
}

window.addEventListener('resize', updateMobileIndicator);
updateMobileIndicator();

// Add some test functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Enhanced Navigation System Loaded');
    console.log('📱 Mobile breakpoint: 768px');
    console.log('🖥️ Current viewport:', window.innerWidth + 'px');
});
</script>

<?php require_once 'includes/footer.php'; ?>
