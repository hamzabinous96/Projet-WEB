    <?php
    // Model/Quiz.php

    class QuizModel {
        public $pdo;

        public function __construct(){
            $this->pdo = new PDO(
                "mysql:host=127.0.0.1;dbname=project_weconnect1;charset=utf8mb4",
                "root", "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        }

        /* quizzes */
        public function all(){
            return $this->pdo->query("
                SELECT q.*, a.nom AS creator, q.creation_date
                FROM quiz q
                LEFT JOIN admin a ON q.creator_id = a.id
                ORDER BY q.id DESC
            ")->fetchAll();
        }

        public function find($id){
            $stmt = $this->pdo->prepare("SELECT * FROM quiz WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        }

        /* questions */
        public function questionsByQuiz($quiz_id){
            $stmt = $this->pdo->prepare("SELECT * FROM quiz_question WHERE quiz_id = ? ORDER BY id ASC");
            $stmt->execute([$quiz_id]);
            return $stmt->fetchAll();
        }

        public function createQuestion($quiz_id, $text, $type, $correct_answer, $points = 1){
            $stmt = $this->pdo->prepare("INSERT INTO quiz_question (quiz_id, question_text, question_type, correct_answer, points) VALUES (?,?,?,?,?)");
            $stmt->execute([$quiz_id, $text, $type, $correct_answer, $points]);
            return (int)$this->pdo->lastInsertId();
        }

        public function deleteQuestionsByQuiz($quiz_id){
            $stmt = $this->pdo->prepare("DELETE FROM quiz_question WHERE quiz_id = ?");
            return $stmt->execute([$quiz_id]);
        }

        public function updateQuiz($id, $title, $desc, $pass_score = 0){
            $stmt = $this->pdo->prepare("UPDATE quiz SET quiz_title = ?, descriptions = ?, pass_score = ? WHERE id = ?");
            return $stmt->execute([$title, $desc, $pass_score, $id]);
        }

        public function createQuiz($title, $desc, $creator_id, $pass_score = 0){
            $stmt = $this->pdo->prepare("INSERT INTO quiz (quiz_title, descriptions, creator_id, pass_score) VALUES (?,?,?,?)");
            $stmt->execute([$title,$desc,$creator_id,$pass_score]);
            return (int)$this->pdo->lastInsertId();
        }

        /* attempts */
        public function storeAttempt($quiz_id, $user_id, $score){
            // allow $user_id to be null when guests take quizzes
            $user_id = ($user_id === 0 || $user_id === '' || $user_id === null) ? null : $user_id;

            $stmt = $this->pdo->prepare("INSERT INTO quiz_attempt (quiz_id, user_id, score) VALUES (?,?,?)");
            $stmt->execute([$quiz_id, $user_id, $score]);
            return (int)$this->pdo->lastInsertId();
        }


        public function storeAttemptAnswer($attempt_id, $question_id, $choice_id = null, $answer_text = null, $is_correct = 0){
            // check table exists to avoid errors if schema doesn't include quiz_attempt_answer
            try {
                $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'quiz_attempt_answer'");
                $stmt->execute();
                if (!$stmt->fetchColumn()) {
                    // table not present — skip storing detail answers
                    return false;
                }

                $stmt = $this->pdo->prepare("INSERT INTO quiz_attempt_answer (attempt_id, question_id, choice_id, answer_text, is_correct) VALUES (?,?,?,?,?)");
                return $stmt->execute([$attempt_id, $question_id, $choice_id, $answer_text, $is_correct]);
            } catch (\PDOException $e) {
                return false;
            }
        }

        /* optional: get a choice from quiz_choice table if you ever create it */
        public function getChoice($choice_id){
            try {
                $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'quiz_choice'");
                $stmt->execute();
                if (!$stmt->fetchColumn()) return null;
                $stmt = $this->pdo->prepare("SELECT * FROM quiz_choice WHERE id = ?");
                $stmt->execute([$choice_id]);
                return $stmt->fetch() ?: null;
            } catch (\PDOException $e) {
                return null;
            }
        }

        /**
         * Retourne le dernier essai d'un utilisateur pour un quiz (ou null)
         */
        public function getLastAttemptByUser(int $quiz_id, int $user_id) {
            $stmt = $this->pdo->prepare("
                SELECT * FROM quiz_attempt
                WHERE quiz_id = ? AND user_id = ?
                ORDER BY attempt_date DESC
                LIMIT 1
            ");
            $stmt->execute([$quiz_id, $user_id]);
            return $stmt->fetch() ?: null;
        }

        /**
         * Retourne le meilleur essai d'un utilisateur pour un quiz (or null).
         * Si plusieurs essais ont le même score max, on prend le plus récent (ou changez ORDER BY si besoin).
         */
        public function getBestAttemptByUser(int $quiz_id, int $user_id) {
            $stmt = $this->pdo->prepare("
                SELECT * FROM quiz_attempt
                WHERE quiz_id = ? AND user_id = ?
                ORDER BY score DESC, attempt_date DESC
                LIMIT 1
            ");
            $stmt->execute([$quiz_id, $user_id]);
            return $stmt->fetch() ?: null;
        }

        /**
         * (Optionnel) Compte d'essais de l'utilisateur sur ce quiz
         */
        public function countAttemptsByUser(int $quiz_id, int $user_id) {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) AS cnt FROM quiz_attempt
                WHERE quiz_id = ? AND user_id = ?
            ");
            $stmt->execute([$quiz_id, $user_id]);
            $r = $stmt->fetch();
            return $r ? intval($r['cnt']) : 0;
        }

        public function deleteQuiz(int $quiz_id) {
            try {
                $this->pdo->beginTransaction();

                // 1) delete per-question attempt answers if table exists
                $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'quiz_attempt_answer'");
                $stmt->execute();
                $hasAttemptAnswers = (bool)$stmt->fetchColumn();
                if ($hasAttemptAnswers) {
                    // delete by attempt -> the attempt deletion below could cascade, but delete explicitly to be safe
                    $stmt = $this->pdo->prepare("
                        DELETE qaa FROM quiz_attempt_answer qaa
                        JOIN quiz_attempt qa ON qaa.attempt_id = qa.id
                        WHERE qa.quiz_id = ?
                    ");
                    $stmt->execute([$quiz_id]);
                }

                // 2) delete attempts for this quiz (if table exists)
                $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'quiz_attempt'");
                $stmt->execute();
                $hasAttempts = (bool)$stmt->fetchColumn();
                if ($hasAttempts) {
                    $stmt = $this->pdo->prepare("DELETE FROM quiz_attempt WHERE quiz_id = ?");
                    $stmt->execute([$quiz_id]);
                }

                // 3) delete choices if the quiz_choice table exists (safety)
                $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'quiz_choice'");
                $stmt->execute();
                $hasChoices = (bool)$stmt->fetchColumn();
                if ($hasChoices) {
                    // delete choices where question belongs to this quiz
                    $stmt = $this->pdo->prepare("
                        DELETE qc FROM quiz_choice qc
                        JOIN quiz_question q ON qc.question_id = q.id
                        WHERE q.quiz_id = ?
                    ");
                    $stmt->execute([$quiz_id]);
                }

                // 4) delete questions
                $stmt = $this->pdo->prepare("DELETE FROM quiz_question WHERE quiz_id = ?");
                $stmt->execute([$quiz_id]);

                // 5) delete the quiz itself
                $stmt = $this->pdo->prepare("DELETE FROM quiz WHERE id = ?");
                $stmt->execute([$quiz_id]);

                $this->pdo->commit();
                return true;
            } catch (\PDOException $e) {
                // rollback on error and return false
                if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                // optionally log $e->getMessage();
                return false;
            }
        }
    }