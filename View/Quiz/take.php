<?php
// View/Quiz/take.php
// controller must have started session and supplied $quiz and $questions (with _choices)
$pageTitle = "Passer : " . ($quiz['quiz_title'] ?? '');
require __DIR__ . '/../includes/header.php';
?>
<section style="padding:40px 0">
  <div class="hero-card card">
    <h2>Passer le quiz : <?= htmlspecialchars($quiz['quiz_title'] ?? '') ?></h2>
    <p class="small"><?= nl2br(htmlspecialchars($quiz['descriptions'] ?? '')) ?></p>

    <form method="post" action="/quiz/Controller/Quiz.php?action=submit" novalidate>
      <input type="hidden" name="quiz_id" value="<?= intval($quiz['id']) ?>">

      <?php if (empty($questions)): ?>
        <p class="small">Aucune question à afficher pour ce quiz.</p>
      <?php else: ?>
        <?php foreach ($questions as $i => $q): ?>
          <fieldset style="margin-bottom:18px;padding:14px;border-radius:12px;border:1px solid var(--pistachio-soft)">
            <legend style="font-weight:700">Q<?= ($i+1) ?> — <?= intval($q['points']) ?> pt</legend>

            <div style="margin-bottom:10px"><?= nl2br(htmlspecialchars($q['question_text'])) ?></div>

            <?php
              $choices = $q['_choices'] ?? [];
              // If choices exist -> render radio buttons, name uses question id so controller receives choice_id
            ?>
            <?php if (!empty($choices)): ?>
              <div role="radiogroup" aria-labelledby="q-label-<?= intval($q['id']) ?>">
                <?php foreach ($choices as $choice): ?>
                  <label style="display:flex;align-items:center;gap:10px;margin-bottom:8px;cursor:pointer;">
                    <input
                      type="radio"
                      name="answer[<?= intval($q['id']) ?>]"
                      value="<?= intval($choice['id']) ?>"
                      required
                    >
                    <span><?= htmlspecialchars($choice['text']) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
              <!-- provide a fallback hidden text input (optional) -->
              <input type="hidden" name="answer_text[<?= intval($q['id']) ?>]" value="">
            <?php else: ?>
              <!-- fallback: text input when no choices exist -->
              <label class="small">Réponse (texte)</label>
              <input class="input" type="text" name="answer[<?= intval($q['id']) ?>]" placeholder="Ta réponse">
            <?php endif; ?>
          </fieldset>
        <?php endforeach; ?>
      <?php endif; ?>

      <div style="display:flex;gap:12px">
        <button class="btn-primary" type="submit">Soumettre</button>
        <a class="btn-secondary" href="/quiz/Controller/Quiz.php?action=list">Annuler</a>
      </div>
    </form>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
