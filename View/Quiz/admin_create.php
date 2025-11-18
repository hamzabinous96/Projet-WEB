<?php
// View/Quiz/admin_create.php
// NOTE: controller must call session_start() before rendering this view.
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
$pageTitle = "Créer un quiz — Admin";
require __DIR__ . '/../includes/header.php';
?>
<section style="padding:40px 0">
  <div class="hero-card card">
    <h2>Créer un quiz</h2>
    <p class="small">Remplis le titre, la description et ajoute des questions (texte ou QCM).</p>

    <?php if ($errors): ?>
      <div class="flash error" style="margin-top:12px;">
        <ul style="margin:0;padding-left:18px;">
          <?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form id="quizForm" method="post" action="/quiz/Controller/Quiz.php?action=admin_store" style="margin-top:18px;">
      <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <div style="flex:1">
          <label class="small">Titre</label>
          <input class="input" type="text" name="title" placeholder="Titre du quiz" required>
        </div>
        <div style="width:160px">
          <label class="small">Score de passage (optionnel)</label>
          <input class="input" type="number" name="pass_score" min="0" value="0">
        </div>
      </div>

      <div style="margin-top:12px;">
        <label class="small">Description</label>
        <textarea class="input" name="descriptions" placeholder="Description courte..."></textarea>
      </div>

      <hr style="margin:18px 0;border:none;border-top:1px solid var(--pistachio-soft)">

      <div id="questionsWrap">
        <!-- Question blocks will be inserted here -->
      </div>

      <div style="margin-top:12px;display:flex;gap:12px;align-items:center">
        <button type="button" id="addQuestionBtn" class="btn-secondary">+ Ajouter une question</button>
        <button type="submit" class="btn-primary">Créer le quiz</button>
        <a class="btn-secondary" href="/quiz/Controller/Quiz.php?action=list" style="margin-left:8px">Annuler</a>
      </div>
    </form>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- Minimal JS to add/remove questions and MCQ choices -->
<script>
(function(){
  const questionsWrap = document.getElementById('questionsWrap');
  const addQuestionBtn = document.getElementById('addQuestionBtn');

  function createQuestionBlock(index, existing = {}) {
    const qIdx = Date.now() + Math.floor(Math.random()*1000); // unique id
    const container = document.createElement('div');
    container.className = 'card';
    container.style.marginBottom = '14px';
    container.innerHTML = `
      <div style="display:flex;justify-content:space-between;align-items:center">
        <h4 style="margin:0">Question</h4>
        <div style="display:flex;gap:8px">
          <button type="button" class="btn-secondary btn-remove-question">Supprimer</button>
        </div>
      </div>

      <div style="margin-top:8px;">
        <label class="small">Texte de la question</label>
        <input class="input" name="question[]" value="${existing.question_text ? htmlspecialchars(existing.question_text) : ''}" required>
      </div>

      <div style="display:flex;gap:12px;margin-top:8px;align-items:center">
        <div style="flex:1">
          <label class="small">Type</label>
          <select class="input q-type" name="type[]">
            <option value="text" ${existing.question_type === 'text' ? 'selected' : ''}>Texte (réponse libre)</option>
            <option value="mcq" ${existing.question_type === 'mcq' ? 'selected' : ''}>QCM (choix multiples)</option>
          </select>
        </div>

        <div style="width:120px">
          <label class="small">Points</label>
          <input class="input" type="number" name="points[]" min="1" value="${existing.points ? parseInt(existing.points) : 1}">
        </div>
      </div>

      <div class="q-choices" style="margin-top:12px; display:none;">
        <label class="small">Choix (pour QCM)</label>
        <div class="choices-list"></div>
        <div style="margin-top:8px">
          <button type="button" class="btn-secondary btn-add-choice">+ Ajouter choix</button>
        </div>
      </div>

      <div class="q-correct-text" style="margin-top:12px; display:none;">
        <label class="small">Réponse correcte (texte)</label>
        <input class="input" name="correct[]" value="${existing.correct_answer ? htmlspecialchars(existing.correct_answer) : ''}">
      </div>

      <!-- hidden fields for correct_choice (index-based), will be set dynamically -->
      <input type="hidden" name="correct_choice[]" value="">
      <div style="margin-top:10px;"><small class="small">Astuce: sélectionne "QCM" pour ajouter des choix.</small></div>
    `;

    // helper for escaping values
    function unescapeHtml(s) { return (s || '').replace(/"/g, '&quot;').replace(/'/g, '&#039;'); }
    function htmlspecialchars(s) { return s ? s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : ''; }

    // attach events
    const typeSelect = container.querySelector('.q-type');
    const choicesBox = container.querySelector('.q-choices');
    const choicesList = container.querySelector('.choices-list');
    const addChoiceBtn = container.querySelector('.btn-add-choice');
    const correctChoiceInput = container.querySelector('input[name="correct_choice[]"]');
    const correctTextBox = container.querySelector('.q-correct-text');

    function showTypeUI() {
      if (typeSelect.value === 'mcq') {
        choicesBox.style.display = 'block';
        correctTextBox.style.display = 'none';
      } else {
        choicesBox.style.display = 'none';
        correctTextBox.style.display = 'block';
      }
    }

    typeSelect.addEventListener('change', showTypeUI);

    // add default two choices for MCQ
    function addChoice(text = '', isCorrect = false) {
      const idx = choicesList.children.length;
      const row = document.createElement('div');
      row.style.display = 'flex';
      row.style.gap = '8px';
      row.style.marginTop = '8px';
      row.innerHTML = `
        <input class="input" name="choices[${qIdx}][]" value="${htmlspecialchars(text)}" placeholder="Texte du choix" style="flex:1">
        <label style="display:flex;align-items:center;gap:6px">
          <input type="radio" name="correct_radio_${qIdx}" ${isCorrect ? 'checked' : ''}>
          <span class="small">Correct</span>
        </label>
        <button type="button" class="btn-secondary btn-remove-choice">×</button>
      `;
      // choose radio event -> set hidden correct_choice input index
      const radio = row.querySelector('input[type="radio"]');
      radio.addEventListener('change', function(){
        // find index of this child
        const children = Array.from(choicesList.children);
        const index = children.indexOf(row);
        correctChoiceInput.value = index;
      });
      // remove choice
      row.querySelector('.btn-remove-choice').addEventListener('click', function(){
        const children = Array.from(choicesList.children);
        const idx = children.indexOf(row);
        row.remove();
        // if removed choice was marked correct, clear correct_choice
        if (correctChoiceInput.value == idx) correctChoiceInput.value = '';
        // reassign radios' names and update correct_choice mapping if needed
        Array.from(choicesList.children).forEach((c, i) => {
          const r = c.querySelector('input[type="radio"]');
          if (r) r.name = 'correct_radio_' + qIdx;
        });
      });
      choicesList.appendChild(row);
      // if it's the first choice and marked correct, set correct_choiceInput
      if (isCorrect) {
        const children = Array.from(choicesList.children);
        const index = children.indexOf(row);
        correctChoiceInput.value = index;
      }
    }

    addChoiceBtn.addEventListener('click', function(){ addChoice('',''); });

    // remove question
    container.querySelector('.btn-remove-question').addEventListener('click', function(){
      container.remove();
    });

    // if existing data provided (server-side) - populate choices & set UI
    if (existing._choices && Array.isArray(existing._choices) && existing._choices.length) {
      existing._choices.forEach(function(c, j){
        addChoice(c.text, c.is_correct == 1);
      });
    } else {
      // default: create two blank choices to start
      addChoice('', false);
      addChoice('', false);
    }

    // initial show/hide
    showTypeUI();

    return container;
  }

  addQuestionBtn.addEventListener('click', function(){
    const block = createQuestionBlock();
    questionsWrap.appendChild(block);
  });

  // init with one empty question
  addQuestionBtn.click();

})();
</script>
