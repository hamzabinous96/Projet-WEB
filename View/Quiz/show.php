<?php
$pageTitle = $quiz['quiz_title'] ?? 'Quiz';
require __DIR__ . '/../includes/header.php';
?>

<section style="padding:40px 0">
  <div class="hero-card card">
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <div>
          <h2 style="margin:0"><?= htmlspecialchars($quiz['quiz_title'] ?? '') ?></h2>
          <?php if (!empty($quiz['creation_date'])): ?>
            <div class="small-muted" style="margin-top:6px">Créé le <?= htmlspecialchars(date('d/m/Y', strtotime($quiz['creation_date']))) ?></div>
          <?php endif; ?>
        </div>

        <div style="display:flex;gap:10px;align-items:center">
          <?php if (!empty($_SESSION['admin'])): ?>
            <a class="btn-ghost" href="/quiz/Controller/Quiz.php?action=admin_delete&id=<?= intval($quiz['id']) ?>" onclick="return confirm('Supprimer ce quiz ?')">Supprimer</a>
          <?php endif; ?>

          <a class="btn-primary" href="/quiz/Controller/Quiz.php?action=take&id=<?= intval($quiz['id']) ?>">Passer le quiz</a>
          <a class="btn-secondary" href="/quiz/Controller/Quiz.php?action=list">Retour</a>
        </div>
      </div>

      <?php if (!empty($result)): ?>
        <div class="flash success" style="margin-top:6px"><?= htmlspecialchars($result) ?></div>
      <?php endif; ?>

      <p class="small" style="margin-top:6px"><?= nl2br(htmlspecialchars($quiz['descriptions'] ?? '')) ?></p>

      <!-- User stats -->
      <?php if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])): ?>
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px">
          <div style="padding:10px 12px;border-radius:10px;border:1px solid var(--pistachio-soft);background:var(--pistachio-soft);min-width:150px">
            <div class="small-muted">Dernier essai</div>
            <?php if (!empty($userLast)): ?>
              <div style="font-weight:700"><?= intval($userLast['score']) ?> pts</div>
              <div class="mini">le <?= date('d/m/Y H:i', strtotime($userLast['attempt_date'])) ?></div>
            <?php else: ?>
              <div class="mini">Aucun essai</div>
            <?php endif; ?>
          </div>

          <div style="padding:10px 12px;border-radius:10px;border:1px solid var(--pistachio-soft);min-width:150px">
            <div class="small-muted">Meilleur score</div>
            <?php if (!empty($userBest)): ?>
              <div style="font-weight:700"><?= intval($userBest['score']) ?> pts</div>
              <div class="mini">le <?= date('d/m/Y H:i', strtotime($userBest['attempt_date'])) ?></div>
            <?php else: ?>
              <div class="mini">Aucun score</div>
            <?php endif; ?>
          </div>

          <div style="padding:10px 12px;border-radius:10px;border:1px solid var(--pistachio-soft);background:linear-gradient(90deg,var(--pistachio-soft),transparent);min-width:120px">
            <div class="small-muted">Nombre d'essais</div>
            <div style="font-weight:700"><?= intval($attemptCount) ?></div>
          </div>
        </div>
      <?php else: ?>
        <div class="small-muted" style="margin-top:12px">Connecte-toi pour voir ton historique et tes scores.</div>
      <?php endif; ?>

      <hr style="margin:18px 0;border:none;border-top:1px solid var(--pistachio-soft)">
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
