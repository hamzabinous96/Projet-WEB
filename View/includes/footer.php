<?php
// View/includes/footer.php
?>
    </div> <!-- .container -->
  </main>

  <footer class="footer" role="contentinfo" aria-label="Footer">
    <div class="footer-container">
      <div class="footer-content">
        <div class="footer-logo">
          <span class="logo">WeConnect</span>
          <p class="footer-section">Plateforme d’apprentissage et de quiz pour la communauté.</p>
        </div>

        <div class="footer-section">
          <h4>Ressources</h4>
          <ul>
            <li><a href="#">Aide</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">FAQ</a></li>
          </ul>
        </div>

        <div class="footer-section">
          <h4>Entreprise</h4>
          <ul>
            <li><a href="#">À propos</a></li>
            <li><a href="#">Carrières</a></li>
          </ul>
        </div>

        <div class="footer-section">
          <h4>Suivez-nous</h4>
          <div class="social-links">
            <a class="social-link" href="#" aria-label="Twitter">T</a>
            <a class="social-link" href="#" aria-label="Facebook">f</a>
            <a class="social-link" href="#" aria-label="LinkedIn">in</a>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <div>© <?= date('Y') ?> WeConnect</div>
        <div class="footer-links">
          <a href="#">Mentions</a>
          <a href="#">Confidentialité</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- tiny JS for hamburger menu (optional) -->
  <script>
    (function(){
      const burger = document.getElementById('hamburger');
      const menu   = document.getElementById('navMenu');
      if (!burger || !menu) return;
      burger.addEventListener('click', function(){
        menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
      });
      // Responsive initial state
      function adapt(){
        if (window.innerWidth <= 768) {
          menu.style.display = 'none';
        } else {
          menu.style.display = 'flex';
        }
      }
      adapt();
      window.addEventListener('resize', adapt);
    })();
  </script>
</body>
</html>
