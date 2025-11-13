<footer class="bg-dark text-white pt-5 pb-4">
  <div class="container">
    <div class="row text-center text-md-start">

      <!-- Brand Info -->
     <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-duration="1200">
  <div class="d-flex align-items-center mb-2">
    <h5 class="text-warning fs-4 mb-0 text-center text-md-start w-100">
      Elegant Salon
    </h5>
  </div>
  <p class="small">Style. Beauty. Confidence.<br>Your go-to place for elegance and care.</p>
  <p>
    Your trusted destination for expert styling, relaxing treatments, and a touch of luxury — where elegance meets you.
  </p>
    </div>

      <!-- Quick Links -->
      <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-duration="1200">
        <h5 class="text-warning">Quick Links</h5>
        <ul class="list-unstyled">
          <li><a href="index.php" class="text-white text-decoration-none footer-link">🏠 Home</a></li>
          <li><a href="about.php" class="text-white text-decoration-none footer-link">📖 About</a></li>
          <li><a href="gallery.php" class="text-white text-decoration-none footer-link">🖼️ Gallery</a></li>
          <li><a href="services.php" class="text-white text-decoration-none footer-link">💇 Services</a></li>
          <li><a href="contact.php" class="text-white text-decoration-none footer-link">📬 Contact</a></li>
          <?php if (isset($_SESSION['user_id'])): ?>
  <li><a href="logout.php" class="text-white text-decoration-none footer-link">🚪 Logout</a></li>
<?php else: ?>
  <li><a href="login.php" class="text-white text-decoration-none footer-link">🔐 Login</a></li>
<?php endif; ?>

        </ul>
      </div>

      <!-- Contact & Social -->
      <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-duration="1200">
        <h5 class="text-warning">Connect With Us</h5>
        <div class="mb-2">
          <a href="https://www.facebook.com" target="_blank" class="text-warning me-3 fs-5"><i class="bi bi-facebook"></i></a>
          <a href="https://www.instagram.com" target="_blank" class="text-warning me-3 fs-5"><i class="bi bi-instagram"></i></a>
          <a href="#" target="_blank" class="text-warning fs-5"><i class="bi bi-whatsapp"></i></a>
        </div>
        <p class="small mb-0"><i class="bi bi-envelope-fill text-warning me-2"></i>info@elegantsalon.com</p>
        <p class="small"><i class="bi bi-telephone-fill text-warning me-2"></i>+92-XXX-XXXXXXX</p>
      </div>

    </div>

    <hr class="border-warning" />
    <p class="text-center mb-0 small">© 2025 <span class="text-warning">Elegant Salon</span>. All rights reserved.</p>
  </div>
</footer>
