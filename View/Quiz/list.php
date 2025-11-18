<?php
// View/Quiz/list.php
$pageTitle = "Quizzes — WeConnect";
$notice = $_SESSION['success'] ?? $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
require __DIR__ . '/../includes/header.php';
?>
<section style="padding:40px 0">
  <div class="section-header">
    <h2>Quizzes disponibles</h2>
    <p>Choisis un quiz et teste tes connaissances.</p>
  </div>

  <?php if ($notice): ?>
    <div class="flash success" style="margin:12px 0;padding:12px;border-radius:8px;background:var(--pistachio-soft);border:1px solid var(--pistachio-light)">
      <?= htmlspecialchars($notice) ?>
    </div>
  <?php endif; ?>

  <div class="categories-grid">
    <?php if (empty($quizzes)): ?>
      <div class="category-card">
        <h3>Aucun quiz disponible</h3>
        <p>Crée un quiz via l'administration pour commencer.</p>
      </div>
    <?php else: foreach ($quizzes as $quiz): ?>
      <div class="category-card">
        <div class="category-icon" aria-hidden="true">Q</div>

        <h3 style="margin-top:8px"><?= htmlspecialchars($quiz['quiz_title']) ?></h3>

        <p style="min-height:44px; color:var(--gray)"><?= nl2br(htmlspecialchars(substr($quiz['descriptions'] ?? '', 0, 140))) ?></p>

        <div style="margin-top:12px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
          <?php if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])): ?>
            <a class="btn-primary" href="/quiz/Controller/Quiz.php?action=take&id=<?= intval($quiz['id']) ?>">Passer</a>
          <?php endif; ?>
          <a class="btn-secondary" href="/quiz/Controller/Quiz.php?action=show&id=<?= intval($quiz['id']) ?>">Détails</a>

          <?php if (!empty($_SESSION['admin']) && !empty($_SESSION['admin']['id'])): ?>
            <a class="btn-primary" href="/quiz/Controller/Quiz.php?action=admin_delete&id=<?= intval($quiz['id']) ?>" onclick="return confirm('Supprimer ce quiz ? Cette action est irréversible. Confirmez pour continuer.');">Supprimer</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
