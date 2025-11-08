    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><i class="fas fa-graduation-cap"></i> CampusMart</h4>
                    <p>Exclusive JH Cerilles State College Student Marketplace</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/marketplace.php">Marketplace</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/services.php">Services</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="<?php echo SITE_URL; ?>/post-item.php">Post Item</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/dashboard.php">Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo SITE_URL; ?>/register.php">Register</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/login.php">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/help.php">Help Center</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact.php">Contact Us</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/policies.php">Policies</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> CampusMart - JH Cerilles State College Student Marketplace. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>/js/script.js"></script>
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo SITE_URL . '/' . $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
