</div><!-- .main-content -->
    
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <span class="brand-icon">&#9733;</span>
                <span>Uganda Martyrs University</span>
            </div>
            <div class="footer-links">
                <a href="<?php echo APP_URL; ?>/index.php">Home</a>
                <a href="<?php echo APP_URL; ?>/events.php">Events</a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo APP_URL; ?>/dashboard.php">Dashboard</a>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>/login.php">Login</a>
                    <a href="<?php echo APP_URL; ?>/register.php">Register</a>
                <?php endif; ?>
            </div>
            <div class="footer-copy">
                &copy; <?php echo date('Y'); ?> Uganda Martyrs University. All rights reserved.
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="<?php echo APP_URL; ?>/js/main.js"></script>

</body>
</html>
