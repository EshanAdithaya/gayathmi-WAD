<?php
session_start();
include_once 'navbar.php';
?>


    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="hero-content">
            <h1 class="hero-title">Experience Modern Asian Cuisine</h1>
            <p class="hero-subtitle">
                Discover the perfect blend of traditional flavors and contemporary dining at Chan's Food. 
                Fresh ingredients, authentic recipes, and a warm atmosphere await you.
            </p>
            <div class="cta-buttons">
                <a href="reservations.php" class="primary-btn">Book a Table</a>
                <a href="menu.php" class="secondary-btn">View Menu</a>
            </div>

            <!-- Key Features -->
            <div class="features">
                <div class="feature-card">
                    <i class="fa fa-clock-o feature-icon"></i>
                    <h3>Fast Delivery</h3>
                    <p>30 minutes or free</p>
                </div>
                <div class="feature-card">
                    <i class="fa fa-leaf feature-icon"></i>
                    <h3>Fresh & Healthy</h3>
                    <p>Quality ingredients</p>
                </div>
                <div class="feature-card">
                    <i class="fa fa-star feature-icon"></i>
                    <h3>Best Rated</h3>
                    <p>4.8/5 customer rating</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Action Buttons -->
    <div class="quick-actions">
        <a href="tel:+1234567890" class="action-btn" title="Call Us">
            <i class="fa fa-phone"></i>
        </a>
        <a href="order.php" class="action-btn" title="Order Now">
            <i class="fa fa-shopping-cart"></i>
        </a>
        <a href="#home" class="action-btn" title="Back to Top">
            <i class="fa fa-arrow-up"></i>
        </a>
    </div>

    <!-- Social Links -->
    <div class="social-container">
        <ul class="social-icons">
            <li><a href="#" aria-label="Facebook"><i class="fa fa-facebook-f"></i></a></li>
            <li><a href="#" aria-label="Instagram"><i class="fa fa-instagram"></i></a></li>
            <li><a href="#" aria-label="Twitter"><i class="fa fa-twitter"></i></a></li>
        </ul>
    </div>

    <script>
        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Navbar Animation
        const navbar = document.querySelector('.navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            } else {
                navbar.style.background = 'transparent';
                navbar.style.boxShadow = 'none';
            }
        });
    </script>
</body>
</html>