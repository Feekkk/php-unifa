<?php
/**
 * Footer Component
 * Usage: include this file and call renderFooter($basePath)
 * $basePath should be relative path to root (e.g., '../' for pages/, '../../' for pages/student/)
 */

function renderFooter($basePath = '../', $isIndexPage = false) {
    // Ensure basePath ends with / if not empty, or set to empty string for root
    if ($basePath === '') {
        $assetPath = '';
        if ($isIndexPage) {
            $homeLink = '#home';
            $aboutLink = '#about';
            $appsLink = '#applications';
            $contactLink = '#contact';
        } else {
            $homeLink = 'index.php';
            $aboutLink = 'index.php#about';
            $appsLink = 'index.php#applications';
            $contactLink = 'index.php#contact';
        }
    } else {
        if (substr($basePath, -1) !== '/') {
            $basePath .= '/';
        }
        $assetPath = $basePath;
        $homeLink = $basePath . 'index.php';
        $aboutLink = $basePath . 'index.php#about';
        $appsLink = $basePath . 'index.php#applications';
        $contactLink = $basePath . 'index.php#contact';
    }
    ?>
    <!-- Footer -->
    <footer class="site-footer" role="contentinfo">
        <div class="container footer-grid">
            <div class="footer-col">
                <img src="<?php echo $assetPath; ?>public/rcmp-white.png" alt="UniKL RCMP logo" class="logo" />
                <p>
                    UniKL RCMP Financial Aid System dedicated to supporting student wellbeing and success.
                </p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $homeLink; ?>">Home</a></li>
                    <li><a href="<?php echo $aboutLink; ?>">About</a></li>
                    <li><a href="<?php echo $appsLink; ?>">Applications</a></li>
                    <li><a href="<?php echo $contactLink; ?>">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <ul class="footer-info">
                    <li>UniKL RCMP, Ipoh, Perak</li>
                    <li>+60 5-806 2000</li>
                    <li><a href="mailto:swf@unikl.edu.my">swf@unikl.edu.my</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Office & Social</h4>
                <ul class="footer-info">
                    <li>Mon–Fri: 9:00 AM – 5:00 PM</li>
                    <br>
                    <li class="socials">
                        <a href="#" aria-label="Facebook">FB</a>
                        <a href="#" aria-label="Instagram">IG</a>
                        <a href="#" aria-label="Twitter">Tw</a>
                        <a href="#" aria-label="LinkedIN">Li</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>© <?php echo date('Y'); ?> UniKL RCMP. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <?php
}
?>
