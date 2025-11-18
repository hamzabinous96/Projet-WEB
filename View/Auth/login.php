<?php
// View/Auth/login.php
// controller should have started session
$errors = $_SESSION['errors'] ?? []; unset($_SESSION['errors']);
$pageTitle = "Connexion — WeConnect";
require __DIR__ . '/../includes/header.php';
?>
<section class="hero">
  <div class="hero-container">
    <div>
      <div class="hero-badge">Connexion</div>
      <h1 class="hero-title">Bienvenue sur <span class="highlight">WeConnect</span></h1>
      <p class="hero-description">Connecte-toi pour créer et gérer des quizzes ou pour tester tes connaissances.</p>
    </div>
    <div class="hero-visual">
      <div class="hero-card card">
        <?php if ($errors): ?>
          <div class="flash error">
            <ul style="margin:0;padding-left:18px"><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
          </div>
        <?php endif; ?>

        <form method="post" action="/quiz/Controller/Authentification.php" novalidate>
          <div class="form-row">
            <label>Email</label>
            <input class="input" type="text" name="email" placeholder="adresse@exemple.com" required>
          </div>
          <div class="form-row">
            <label>Mot de passe</label>
            <input class="input" type="password" name="password" placeholder="●●●●●●" required>
          </div>
          <div style="display:flex;gap:10px;align-items:center">
            <button class="btn-primary" type="submit">Se connecter</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
