<?php
// View/includes/header.php
// NOTE: controllers must call session_start() before including views.
// Do NOT call session_start() in views.
$user  = $_SESSION['user']  ?? null;
$admin = $_SESSION['admin'] ?? null;
$pageTitle = $pageTitle ?? 'WeConnect - Quiz';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="/quiz/assets/style.css"> <!-- adjust path if necessary -->
</head>
<body>
  <nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="nav-container">
      <div class="nav-logo">
        <a class="logo" href="/quiz/Controller/Quiz.php?action=list">We<span>Connect</span></a>
      </div>

      <div class="nav-menu" id="navMenu">
        <a class="nav-link" href="/quiz/Controller/Quiz.php?action=list">Quizzes</a>
        <?php if ($admin): ?>
          <a class="nav-link" href="/quiz/Controller/Quiz.php?action=admin_create">Créer quiz</a>
        <?php endif; ?>
      </div>

      <div class="nav-actions">
        <?php if ($user): ?>
          <span class="small" style="margin-right:8px"><?= htmlspecialchars($user['nom'] ?? $user['email']) ?></span>
          <a class="btn-login" href="/quiz/Controller/Authentification.php?action=logout">Déconnexion</a>
        <?php else: ?>
          <a class="btn-login" href="/quiz/Controller/Authentification.php">Connexion</a>
        <?php endif; ?>
        <div class="hamburger" id="hamburger" aria-hidden="true" title="Menu">
          <span></span><span></span><span></span>
        </div>
      </div>
    </div>
  </nav>

  <main style="padding-top:85px"> <!-- leave space for fixed navbar -->
    <div class="container">
