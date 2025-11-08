<?php
$pageTitle = 'Header Test';
require_once 'includes/header.php';
?>

<main style="padding: 2rem 0;">
    <div class="container">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h1 style="color: #333; margin-bottom: 1rem;">Header Functionality Test</h1>
            <p style="color: #666; font-size: 1.2rem;">Test the navigation buttons and mobile responsiveness</p>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #28a745; margin-bottom: 1rem;">✅ Header Features Fixed</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #333; margin-bottom: 0.5rem;">🍔 Hamburger Menu</h3>
                    <p style="color: #666;">Animated hamburger menu for mobile devices with smooth transitions</p>
                </div>
                
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #333; margin-bottom: 0.5rem;">📱 Mobile Responsive</h3>
                    <p style="color: #666;">Optimized navigation for mobile and tablet devices</p>
                </div>
                
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #333; margin-bottom: 0.5rem;">🎨 Button Styling</h3>
                    <p style="color: #666;">Improved button hover effects and visual feedback</p>
                </div>
                
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #333; margin-bottom: 0.5rem;">📋 Dropdown Menu</h3>
                    <p style="color: #666;">Enhanced user profile dropdown with better positioning</p>
                </div>
            </div>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <h2 style="color: #333; margin-bottom: 1rem;">🧪 Test Instructions</h2>
            
            <ol style="color: #666; line-height: 1.8; padding-left: 1.5rem;">
                <li><strong>Desktop Test:</strong> Hover over navigation buttons to see smooth hover effects</li>
                <li><strong>Mobile Test:</strong> Resize browser window or use mobile device to test hamburger menu</li>
                <li><strong>Dropdown Test:</strong> Hover over user profile (if logged in) to test dropdown functionality</li>
                <li><strong>Navigation Test:</strong> Click on different navigation items to ensure proper active states</li>
                <li><strong>Responsive Test:</strong> Test on different screen sizes (desktop, tablet, mobile)</li>
            </ol>
            
            <div style="margin-top: 2rem; padding: 1rem; background: #e8f5e8; border-radius: 8px; border-left: 4px solid #28a745;">
                <p style="color: #155724; margin: 0;"><strong>Note:</strong> The hamburger menu will appear on screens smaller than 768px width. Try resizing your browser window to test it!</p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="dashboard.php" class="btn btn-primary" style="margin-right: 1rem;">Go to Dashboard</a>
            <a href="marketplace.php" class="btn btn-secondary">Visit Marketplace</a>
        </div>
    </div>
</main>

<style>
/* Additional test page styles */
.test-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin: 2rem 0;
}

.test-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}

.test-card h3 {
    color: #28a745;
    margin-bottom: 1rem;
}

.test-card p {
    color: #666;
    line-height: 1.6;
}

/* Mobile test indicator */
@media (max-width: 768px) {
    .mobile-indicator {
        position: fixed;
        top: 90px;
        right: 10px;
        background: #28a745;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        z-index: 1001;
    }
}
</style>

<div class="mobile-indicator" style="display: none;">📱 Mobile View Active</div>

<script>
// Show mobile indicator on mobile devices
function checkMobileView() {
    const indicator = document.querySelector('.mobile-indicator');
    if (window.innerWidth <= 768) {
        indicator.style.display = 'block';
    } else {
        indicator.style.display = 'none';
    }
}

window.addEventListener('resize', checkMobileView);
checkMobileView();
</script>

<?php require_once 'includes/footer.php'; ?>
