<?php
$pageTitle = 'Dropdown Test';
require_once 'includes/header.php';
?>

<main style="padding: 2rem 0;">
    <div class="container">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h1 style="color: #333; margin-bottom: 1rem;">User Dropdown Fix Test</h1>
            <p style="color: #666; font-size: 1.2rem;">Testing the Maria Profile dropdown menu</p>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #28a745; margin-bottom: 1rem;">✅ Dropdown Issues Fixed</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #333; margin-bottom: 0.5rem;">🖱️ Desktop Dropdown</h3>
                    <p style="color: #666;">Hover over "Maria" in the header to see the improved dropdown menu with proper spacing and styling</p>
                </div>
                
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #333; margin-bottom: 0.5rem;">📱 Mobile Dropdown</h3>
                    <p style="color: #666;">On mobile devices, the dropdown displays as a vertical list within the hamburger menu</p>
                </div>
                
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #333; margin-bottom: 0.5rem;">🎨 Visual Improvements</h3>
                    <p style="color: #666;">Added hover effects, proper spacing, icons, and a dropdown arrow for better UX</p>
                </div>
                
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #333; margin-bottom: 0.5rem;">🔧 CSS Fixes</h3>
                    <p style="color: #666;">Resolved CSS conflicts between desktop and mobile dropdown styles</p>
                </div>
            </div>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <h2 style="color: #333; margin-bottom: 1rem;">🧪 Test the Dropdown</h2>
            
            <div style="background: #e8f5e8; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #28a745; margin-bottom: 1.5rem;">
                <h3 style="color: #155724; margin-bottom: 0.5rem;">Desktop Test:</h3>
                <p style="color: #155724; margin: 0;">Hover over "👤 Maria" in the top navigation bar. You should see a clean dropdown menu with:</p>
                <ul style="color: #155724; margin: 0.5rem 0 0 1.5rem;">
                    <li>Profile option with user icon</li>
                    <li>Messages option with chat icon</li>
                    <li>Wishlist option with heart icon</li>
                    <li>Logout option with sign-out icon</li>
                </ul>
            </div>
            
            <div style="background: #cce5ff; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #007bff; margin-bottom: 1.5rem;">
                <h3 style="color: #004085; margin-bottom: 0.5rem;">Mobile Test:</h3>
                <p style="color: #004085; margin: 0;">On mobile (or resize browser < 768px), click the hamburger menu (☰) and you should see the user options displayed vertically within the mobile menu.</p>
            </div>
            
            <div style="background: #fff3cd; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #ffc107;">
                <h3 style="color: #856404; margin-bottom: 0.5rem;">Expected Behavior:</h3>
                <ul style="color: #856404; margin: 0.5rem 0 0 1.5rem;">
                    <li>Smooth hover animations</li>
                    <li>Proper spacing between menu items</li>
                    <li>Icons aligned correctly</li>
                    <li>No overlapping text</li>
                    <li>Clean visual separation</li>
                </ul>
            </div>
        </div>

        <?php if ($isLoggedIn): ?>
        <div style="text-align: center; margin-top: 2rem; padding: 1rem; background: #d4edda; border-radius: 8px;">
            <p style="color: #155724; margin: 0;"><strong>✅ You are logged in as <?php echo htmlspecialchars($currentUser['first_name']); ?>!</strong> The dropdown should be visible in the header.</p>
        </div>
        <?php else: ?>
        <div style="text-align: center; margin-top: 2rem; padding: 1rem; background: #f8d7da; border-radius: 8px;">
            <p style="color: #721c24; margin: 0;"><strong>⚠️ You are not logged in.</strong> <a href="login.php" style="color: #721c24;">Please log in</a> to see the user dropdown menu.</p>
        </div>
        <?php endif; ?>
    </div>
</main>

<style>
/* Additional test styles */
.test-highlight {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
    margin: 0.25rem;
}

.status-good {
    color: #28a745;
    font-weight: 600;
}

.status-warning {
    color: #ffc107;
    font-weight: 600;
}
</style>

<?php require_once 'includes/footer.php'; ?>
