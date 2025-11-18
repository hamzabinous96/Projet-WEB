<?php
// Controller/Quiz.php
session_start();
require_once __DIR__ . '/../Model/Quiz.php';

$qm = new QuizModel();
$action = $_GET['action'] ?? 'list';

// helper: admin guard
function require_admin() {
    if (empty($_SESSION['admin'])) {
        $_SESSION['error'] = "Accès admin requis.";
        header("Location: Quiz.php?action=list");
        exit;
    }
}

// ---------------------- LIST ----------------------
if ($action === 'list') {
    $quizzes = $qm->all();
    require __DIR__ . '/../View/Quiz/list.php';
    exit;
}

// ---------------------- SHOW ----------------------
if ($action === 'show') {
    $id = intval($_GET['id'] ?? 0);
    $quiz = $qm->find($id);
    if (!$quiz) {
        $_SESSION['error'] = "Quiz introuvable.";
        header("Location: Quiz.php?action=list");
        exit;
    }

    // questions + attach choices if MCQ stored as JSON (same logic as elsewhere)
    $questions = $qm->questionsByQuiz($id);
    foreach ($questions as &$q) {
        $q['_choices'] = [];
        $q['_correct_index'] = null;
        if (($q['question_type'] ?? '') === 'mcq' && !empty($q['correct_answer'])) {
            $decoded = json_decode($q['correct_answer'], true);
            if (is_array($decoded) && isset($decoded['choices']) && is_array($decoded['choices'])) {
                $arr = [];
                foreach ($decoded['choices'] as $idx => $txt) {
                    $arr[] = ['id' => $idx, 'text' => $txt, 'is_correct' => ($decoded['correct'] == $idx ? 1 : 0)];
                }
                $q['_choices'] = $arr;
                $q['_correct_index'] = intval($decoded['correct']);
            }
        }
    }
    unset($q);

    // prepare user stats (if logged in)
    $userLast = null;
    $userBest = null;
    $attemptCount = 0;
    if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
        $uid = intval($_SESSION['user']['id']);
        $userLast = $qm->getLastAttemptByUser($id, $uid);
        $userBest = $qm->getBestAttemptByUser($id, $uid);
        $attemptCount = $qm->countAttemptsByUser($id, $uid);
    }

    $result = $_SESSION['result'] ?? null;
    unset($_SESSION['result']);

    // pass $userLast, $userBest, $attemptCount to the view
    require __DIR__ . '/../View/Quiz/show.php';
    exit;
}


// ---------------------- TAKE ----------------------
if ($action === 'take') {
    $id = intval($_GET['id'] ?? 0);
    $quiz = $qm->find($id);

    if (!$quiz) {
        $_SESSION['error'] = "Quiz introuvable.";
        header("Location: Quiz.php?action=list");
        exit;
    }

    // fetch questions
    $questions = $qm->questionsByQuiz($id);

    // attach parsed choices if present (from JSON in correct_answer)
    foreach ($questions as &$q) {
        $q['_choices'] = [];
        $q['_correct_index'] = null;
        if (($q['question_type'] ?? '') === 'mcq' && !empty($q['correct_answer'])) {
            $decoded = json_decode($q['correct_answer'], true);
            if (is_array($decoded) && isset($decoded['choices']) && is_array($decoded['choices'])) {
                $arr = [];
                foreach ($decoded['choices'] as $idx => $txt) {
                    $arr[] = ['id' => $idx, 'text' => $txt, 'is_correct' => ($decoded['correct'] == $idx ? 1 : 0)];
                }
                $q['_choices'] = $arr;
                $q['_correct_index'] = intval($decoded['correct']);
            }
        }
    }
    unset($q);

    require __DIR__ . '/../View/Quiz/take.php';
    exit;
}

// ---------------------- SUBMIT ----------------------
if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $quiz_id = intval($_POST['quiz_id'] ?? 0);
    $answers = $_POST['answer'] ?? []; // answers keyed by question id -> either index (json-mode) or choice id (db-mode) or text (fallback)
    $questions = $qm->questionsByQuiz($quiz_id);

    // attach parsed choices for scoring logic (same as in take)
    foreach ($questions as &$q) {
        $q['_choices'] = [];
        $q['_correct_index'] = null;
        if (($q['question_type'] ?? '') === 'mcq' && !empty($q['correct_answer'])) {
            $decoded = json_decode($q['correct_answer'], true);
            if (is_array($decoded) && isset($decoded['choices']) && is_array($decoded['choices'])) {
                $arr = [];
                foreach ($decoded['choices'] as $idx => $txt) {
                    $arr[] = ['id' => $idx, 'text' => $txt, 'is_correct' => ($decoded['correct'] == $idx ? 1 : 0)];
                }
                $q['_choices'] = $arr;
                $q['_correct_index'] = intval($decoded['correct']);
            }
        }
    }
    unset($q);

    $score = 0;
    $total = 0;

    foreach ($questions as $q) {
        $qid = $q['id'];
        $total += intval($q['points']);

        $choices = $q['_choices'] ?? [];

        if (!empty($choices)) {
            // json-mode: choices have id = index (0,1,2)
            $answer_val = $answers[$qid] ?? null;
            if ($answer_val === null || $answer_val === '') {
                // unanswered => score += 0
            } else {
                // treat as integer index
                if (isset($q['_correct_index'])) {
                    if (intval($answer_val) === intval($q['_correct_index'])) {
                        $score += intval($q['points']);
                    }
                } else {
                    // fallback: if somehow choices present but no correct_index, do nothing
                }
            }
        } else {
            // text fallback
            $given = isset($answers[$qid]) ? trim(strtolower($answers[$qid])) : '';
            $correct = trim(strtolower($q['correct_answer']));
            if ($given !== '' && $given === $correct) {
                $score += intval($q['points']);
            }
        }
    }

    // store attempt and per-question answers
    $user_id = $_SESSION['user']['id'] ?? 0;
    $attempt_id = $qm->storeAttempt($quiz_id, $user_id, $score);

    // store per-question answers if table exists
    foreach ($questions as $q) {
        $qid = $q['id'];
        $choices = $q['_choices'] ?? [];
        if (!empty($choices)) {
            $answer_val = $answers[$qid] ?? null;
            if ($answer_val === null || $answer_val === '') {
                // unanswered
                $qm->storeAttemptAnswer($attempt_id, $qid, null, null, 0);
            } else {
                $is_correct = (isset($q['_correct_index']) && intval($answer_val) === intval($q['_correct_index'])) ? 1 : 0;
                // store choice_id as index (int), answer_text null
                $qm->storeAttemptAnswer($attempt_id, $qid, intval($answer_val), null, $is_correct);
            }
        } else {
            $given = isset($answers[$qid]) ? $answers[$qid] : null;
            $is_correct = ($given !== null && trim(strtolower($given)) === trim(strtolower($q['correct_answer']))) ? 1 : 0;
            $qm->storeAttemptAnswer($attempt_id, $qid, null, $given, $is_correct);
        }
    }

    $_SESSION['result'] = "Votre score : $score / $total.";
    header("Location: Quiz.php?action=show&id=$quiz_id");
    exit;
}

// ---------------------- ADMIN: CREATE FORM ----------------------
if ($action === 'admin_create') {
    require_admin();
    require __DIR__ . '/../View/Quiz/admin_create.php';
    exit;
}

// ---------------------- ADMIN: STORE (POST) ----------------------
if ($action === 'admin_store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();

    // pull posted arrays
    $title = trim($_POST['title'] ?? '');
    $desc  = trim($_POST['descriptions'] ?? '');
    $questions = $_POST['question'] ?? [];   // numeric array of texts
    $types     = $_POST['type'] ?? [];       // numeric array of types
    $points    = $_POST['points'] ?? [];     // numeric array of points
    $corrects  = $_POST['correct'] ?? [];    // numeric array for text questions
    $choices_raw = $_POST['choices'] ?? [];  // possibly associative array: choices[qkey] = array(...)
    $correct_choice = $_POST['correct_choice'] ?? []; // numeric array aligned with questions

    // normalize choices: produce a numeric-indexed array parallel to $questions
    // array_values preserves insertion order of posted choices blocks
    $choices_list = array_values($choices_raw); // each element may be an array of texts

    $errs = [];
    if ($title === '') $errs[] = "Titre requis.";
    $hasQ = false;
    foreach ($questions as $qtxt) { if (trim($qtxt) !== '') { $hasQ = true; break; } }
    if (!$hasQ) $errs[] = "Au moins une question requise.";

    if ($errs) {
        $_SESSION['errors'] = $errs;
        header("Location: Quiz.php?action=admin_create");
        exit;
    }

    // create quiz
    $creator_id = $_SESSION['admin']['id'];
    $quiz_id = $qm->createQuiz($title, $desc, $creator_id, intval($_POST['pass_score'] ?? 0));

    // insert questions (keep order)
    foreach ($questions as $i => $qtext) {
        $qtext = trim($qtext);
        if ($qtext === '') continue;
        $type = $types[$i] ?? 'text';
        $pt   = intval($points[$i] ?? 1);

        if ($type === 'mcq') {
            // get corresponding choices from choices_list by index
            $ch = $choices_list[$i] ?? [];
            // clean empty choices
            $ch_clean = array_values(array_filter($ch, function($c){ return trim($c) !== ''; }));
            $correct_index = isset($correct_choice[$i]) ? intval($correct_choice[$i]) : -1;
            // ensure correct_index is valid relative to cleaned choices
            if ($correct_index < 0 || $correct_index >= count($ch_clean)) {
                // if invalid, set to -1 (no correct)
                $correct_index = -1;
            }
            // store JSON payload in correct_answer
            $payload = json_encode(['choices' => $ch_clean, 'correct' => $correct_index], JSON_UNESCAPED_UNICODE);
            $qm->createQuestion($quiz_id, $qtext, 'mcq', $payload, $pt);
        } else {
            $corr = $corrects[$i] ?? '';
            $qm->createQuestion($quiz_id, $qtext, 'text', $corr, $pt);
        }
    }

    $_SESSION['success'] = "Quiz créé.";
    header("Location: Quiz.php?action=list");
    exit;
}

// ---------------------- ADMIN: DELETE ----------------------
if ($action === 'admin_delete' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_admin();
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) { $_SESSION['error'] = "ID invalide."; header("Location: Quiz.php?action=list"); exit; }
    $qm->deleteQuiz($id);
    $_SESSION['success'] = "Quiz supprimé.";
    header("Location: Quiz.php?action=list");
    exit;
}

// default fallback
header("Location: Quiz.php?action=list");
exit;
