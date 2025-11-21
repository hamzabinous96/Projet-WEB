<?php
// ===========================================
// CONNEXION BDD ET RÉCUPÉRATION DE L'ARTICLE
// ===========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Récupérer l'ID de l'article depuis l'URL
$article_id = $_GET['id'] ?? 0;

if (!$article_id) {
    header("Location: blog.php");
    exit;
}

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=weconnect;charset=utf8',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Récupérer l'article avec sa catégorie
    $stmt = $pdo->prepare("
        SELECT b.*, c.nom_categorie 
        FROM blogs b
        INNER JOIN categories c ON b.categorie_blog = c.id_categorie
        WHERE b.id_blog = ? AND b.est_publie_blog = 1
    ");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$article) {
        die("Article non trouvé ou non publié");
    }
    
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}

// ===========================================
// TRAITEMENT DU FORMULAIRE DE COMMENTAIRE
// ===========================================if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ===========================================
// TRAITEMENT DU FORMULAIRE DE COMMENTAIRE
// ===========================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Commentaire principal
    if (isset($_POST['submit_comment'])) {
        $nom = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $comment_text = htmlspecialchars(trim($_POST['comment']));
        
        // Validation des champs obligatoires
        if (empty($nom) || empty($comment_text)) {
            $error_message = "Erreur : Le nom et le commentaire sont obligatoires.";
        } 
        // Validation de l'email si fourni
        else if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Erreur : Format d'email invalide.";
        }
        else {
            try {
                // Préparation de la requête d'insertion avec modération
                $sql = "INSERT INTO commentaires (
                    id_blog, 
                    auteur_comment, 
                    contenu_comment, 
                    date_creation_comment,
                    est_approuve
                ) VALUES (
                    :id_blog, 
                    :auteur, 
                    :contenu, 
                    NOW(),
                    1
                )";
                
                $stmt = $pdo->prepare($sql);
                
                // Liaison des paramètres
                $stmt->bindParam(':id_blog', $article_id, PDO::PARAM_INT);
                $stmt->bindParam(':auteur', $nom, PDO::PARAM_STR);
                $stmt->bindParam(':contenu', $comment_text, PDO::PARAM_STR);
                
                // Exécution de la requête
                if ($stmt->execute()) {
                    // REDIRECTION POUR ÉVITER LA RE-SOUMISSION
                    header("Location: ?id=" . $article_id . "&success=1");
                    exit;
                } else {
                    $error_message = "Erreur lors de la publication du commentaire.";
                }
                
            } catch(PDOException $e) {
                $error_message = "Erreur d'insertion : " . $e->getMessage();
            }
        }
    }
    
    // Réponse à un commentaire
    if (isset($_POST['submit_reply'])) {
        $reply_nom = htmlspecialchars(trim($_POST['reply_name']));
        $reply_comment_text = htmlspecialchars(trim($_POST['reply_comment']));
        $parent_id = intval($_POST['parent_id']);
        
        if (empty($reply_nom) || empty($reply_comment_text)) {
            $error_message = "Erreur : Le nom et la réponse sont obligatoires.";
        } else {
            try {
                $sql = "INSERT INTO commentaires (
                    id_blog, 
                    auteur_comment, 
                    contenu_comment, 
                    date_creation_comment,
                    parent_id,
                    est_approuve
                ) VALUES (
                    :id_blog, 
                    :auteur, 
                    :contenu, 
                    NOW(),
                    :parent_id,
                    1
                )";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id_blog', $article_id, PDO::PARAM_INT);
                $stmt->bindParam(':auteur', $reply_nom, PDO::PARAM_STR);
                $stmt->bindParam(':contenu', $reply_comment_text, PDO::PARAM_STR);
                $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    // REDIRECTION POUR ÉVITER LA RE-SOUMISSION
                    header("Location: ?id=" . $article_id . "&success=1");
                    exit;
                }
                
            } catch(PDOException $e) {
                $error_message = "Erreur d'insertion de la réponse : " . $e->getMessage();
            }
        }
    }
}

// ===========================================
// AFFICHAGE DU MESSAGE DE SUCCÈS APRÈS REDIRECTION
// ===========================================
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "Votre commentaire a été publié avec succès !";
}


// ===========================================
// PAGINATION ET RÉCUPÉRATION DES COMMENTAIRES - VERSION CORRIGÉE
// ===========================================
$comments_per_page = 5;
$current_page = $_GET['page'] ?? 1;
$offset = ($current_page - 1) * $comments_per_page;

try {
    // VÉRIFIER SI LES NOUVEAUX CHAMPS EXISTENT
    $test_columns = $pdo->query("SHOW COLUMNS FROM commentaires LIKE 'est_approuve'")->fetch();
    
    if ($test_columns) {
        // AVEC les nouveaux champs
        $count_sql = "SELECT COUNT(*) as total FROM commentaires WHERE id_blog = ? AND (parent_id IS NULL OR parent_id = 0) AND est_approuve = 1";
        $select_sql = "SELECT id_commentaire, auteur_comment, contenu_comment, date_creation_comment FROM commentaires WHERE id_blog = ? AND (parent_id IS NULL OR parent_id = 0) AND est_approuve = 1 ORDER BY date_creation_comment DESC LIMIT ? OFFSET ?";
    } else {
        // SANS les nouveaux champs (version simple)
        $count_sql = "SELECT COUNT(*) as total FROM commentaires WHERE id_blog = ?";
        $select_sql = "SELECT id_commentaire, auteur_comment, contenu_comment, date_creation_comment FROM commentaires WHERE id_blog = ? ORDER BY date_creation_comment DESC LIMIT ? OFFSET ?";
    }
    
    // Compter le nombre total de commentaires
    $stmt_count = $pdo->prepare($count_sql);
    $stmt_count->execute([$article_id]);
    $total_comments = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_comments / $comments_per_page);
    
    // Corriger la page actuelle si elle dépasse le total
    if ($current_page > $total_pages && $total_pages > 0) {
        $current_page = 1;
        $offset = 0;
    }
    
    // Récupérer les commentaires parents AVEC PAGINATION
    $stmt_parents = $pdo->prepare($select_sql);
    $stmt_parents->bindValue(1, $article_id, PDO::PARAM_INT);
    $stmt_parents->bindValue(2, $comments_per_page, PDO::PARAM_INT);
    $stmt_parents->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt_parents->execute();
    
    $commentaires_parents = $stmt_parents->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les réponses (version simplifiée)
    $replies_by_parent = [];
    
} catch(PDOException $e) {
    $commentaires_parents = [];
    $replies_by_parent = [];
    $total_pages = 1;
    $total_comments = 0;
}

// Fonction pour formater la date en français
function dateEnFrancais($date) {
    $mois = [
        1 => 'janv.', 2 => 'fév.', 3 => 'mars', 4 => 'avr.',
        5 => 'mai', 6 => 'juin', 7 => 'juil.', 8 => 'août',
        9 => 'sept.', 10 => 'oct.', 11 => 'nov.', 12 => 'déc.'
    ];
    $timestamp = strtotime($date);
    $jour = date('d', $timestamp);
    $moisNum = date('n', $timestamp);
    $annee = date('Y', $timestamp);
    return $jour . ' ' . $mois[$moisNum] . ' ' . $annee;
}

// Fonction pour formater la date relative (il y a...)
function dateRelative($date) {
    $now = new DateTime();
    $dateComment = new DateTime($date);
    $interval = $now->diff($dateComment);
    
    if ($interval->y > 0) {
        return "Il y a " . $interval->y . " an" . ($interval->y > 1 ? "s" : "");
    } elseif ($interval->m > 0) {
        return "Il y a " . $interval->m . " mois";
    } elseif ($interval->d > 0) {
        return "Il y a " . $interval->d . " jour" . ($interval->d > 1 ? "s" : "");
    } elseif ($interval->h > 0) {
        return "Il y a " . $interval->h . " heure" . ($interval->h > 1 ? "s" : "");
    } elseif ($interval->i > 0) {
        return "Il y a " . $interval->i . " minute" . ($interval->i > 1 ? "s" : "");
    } else {
        return "À l'instant";
    }
}

// Fonction récursive pour afficher les commentaires et réponses
function displayComment($comment, $replies_by_parent, $level = 0) {
    $margin = $level * 30;
    $border_color = $level == 0 ? 'var(--primary-color)' : '#dee2e6';
    ?>
    <div class="comment-item mb-3 p-3 bg-light rounded" style="margin-left: <?= $margin ?>px; border-left: 3px solid <?= $border_color ?>">
        <div class="d-flex justify-content-between mb-2">
            <strong><?= htmlspecialchars($comment['auteur_comment']) ?></strong>
            <small class="text-muted"><?= dateRelative($comment['date_creation_comment']) ?></small>
        </div>
        <p class="mb-2"><?= nl2br(htmlspecialchars($comment['contenu_comment'])) ?></p>
        
        <!-- Bouton Répondre -->
        <button class="btn btn-sm btn-outline-primary reply-btn" 
                data-comment-id="<?= $comment['id_commentaire'] ?>" 
                data-comment-author="<?= htmlspecialchars($comment['auteur_comment']) ?>">
            <i class="fas fa-reply me-1"></i>Répondre
        </button>
        
        <!-- Formulaire de réponse (caché par défaut) -->
        <div class="reply-form mt-3" id="reply-form-<?= $comment['id_commentaire'] ?>" style="display: none;">
            <form method="POST" class="row g-2">
                <input type="hidden" name="parent_id" value="<?= $comment['id_commentaire'] ?>">
                <div class="col-md-6">
                    <input type="text" name="reply_name" class="form-control form-control-sm" placeholder="Votre nom" required>
                </div>
                <div class="col-12">
                    <textarea name="reply_comment" class="form-control form-control-sm" rows="2" placeholder="Votre réponse..." required></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" name="submit_reply" class="btn btn-primary btn-sm me-2">
                        <i class="fas fa-paper-plane me-1"></i>Publier la réponse
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm cancel-reply">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Afficher les réponses -->
        <?php if (isset($replies_by_parent[$comment['id_commentaire']])): ?>
            <div class="replies mt-3">
                <?php foreach ($replies_by_parent[$comment['id_commentaire']] as $reply): ?>
                    <?php displayComment($reply, $replies_by_parent, $level + 1); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['titre_blog']) ?> - WeConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{
            --primary-color: #93C572;
            --secondary-color: #B8D8A6;
        }
        body { 
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial; 
            background:#f8f9fa; 
        }
        header { 
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); 
            color: white; 
            padding: 100px 0 70px; 
            text-align: center;
            position: relative;
        }
        header h1 { 
            font-size: 2.5rem; 
            margin-bottom: 1rem; 
        }
        header .lead { 
            font-size: 1.2rem; 
            opacity: 0.9; 
        }
        .article-card { 
            background:#fff; 
            border-radius:12px; 
            box-shadow:0 6px 18px rgba(22,28,45,0.06); 
            overflow:hidden; 
            margin-bottom:40px;
        }
        .article-image { 
            width:100%; 
            max-height:500px; 
            object-fit:cover; 
        }
        .category-badge { 
            background:rgba(16, 185, 129, 0.1); 
            color:var(--primary-color); 
            font-weight:600; 
            padding:6px 10px; 
            border-radius:999px; 
            font-size:0.85rem; 
        }
        .tag { 
            display:inline-block; 
            padding:6px 10px; 
            background:#eef4ff; 
            color:var(--primary-color); 
            border-radius:8px; 
            margin-right:6px; 
            font-size:0.85rem; 
        }
        .author-avatar { 
            width:48px; 
            height:48px; 
            border-radius:50%; 
            object-fit:cover; 
        }
        footer.site-footer { 
            background:#0b1723; 
            color:#dbe9ff; 
            padding:40px 0; 
            text-align:center; 
        }
        .back-btn { 
            position: absolute; 
            top: 30px; 
            left: 30px; 
            background: rgba(255,255,255,0.2); 
            color: white; 
            border: 1px solid rgba(255,255,255,0.3);
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            transform: translateX(-5px);
        }
        .article-content { 
            font-size: 1.1rem; 
            line-height: 1.8; 
            color: #333;
            margin-bottom: 2rem;
        }
        .article-content p {
            margin-bottom: 1.5rem;
        }
        .comment-item {
            transition: all 0.3s ease;
        }
        .comment-item:hover {
            background: #f8f9fa !important;
        }
        .reply-form {
            background: rgba(147, 197, 114, 0.05);
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid var(--secondary-color);
        }
        .pagination .page-link {
            color: var(--primary-color);
        }
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>

<!-- =========================================== -->
<!-- HEADER AVEC BOUTON RETOUR -->
<!-- =========================================== -->
<header>
    <div class="container position-relative">
        <a href="blog.php" class="back-btn">
            <i class="fas fa-arrow-left me-2"></i>Retour au blog
        </a>
        
        <h1><?= htmlspecialchars($article['titre_blog']) ?></h1>
        <p class="lead">Découvrez cet article de notre communauté solidaire</p>
    </div>
</header>

<!-- =========================================== -->
<!-- CONTENU PRINCIPAL -->
<!-- =========================================== -->
<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 article-card p-0">
            
           <?php if (!empty($article['image_blog'])): ?>
    <img src="assets/<?= htmlspecialchars($article['image_blog']) ?>" 
         alt="<?= htmlspecialchars($article['titre_blog']) ?>" 
         class="article-image">
<?php else: ?>
    <img src="https://via.placeholder.com/800x400?text=WeConnect+Blog" 
         alt="<?= htmlspecialchars($article['titre_blog']) ?>" 
         class="article-image">
<?php endif; ?>

            
            <div class="p-5">



                <!-- En-tête de l'article -->
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <span class="category-badge">
                            <?= htmlspecialchars($article['nom_categorie']) ?>
                        </span>
                    </div>
                    <div class="text-end text-muted small">
                        Publié le <strong><?= dateEnFrancais($article['date_creation_blog']) ?></strong> • 
                        par <strong>WeConnect</strong>
                    </div>
                </div>

                <!-- Contenu complet de l'article -->
                <div class="article-content">
                    <?= nl2br(htmlspecialchars($article['contenu_blog'])) ?>
                </div>

                <!-- Tags -->
                <div class="mt-4">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="tag">#solidarité</span>
                        <span class="tag">#communauté</span>
                        <span class="tag">#weconnect</span>
                        <span class="tag">#<?= htmlspecialchars(strtolower($article['nom_categorie'])) ?></span>
                    </div>
                </div>

                <!-- Auteur -->
                <div class="d-flex align-items-center mt-4 border-top pt-4">
                    <img src="assets/halo.jpeg" 
                         alt="Équipe WeConnect" 
                         class="author-avatar me-3">
                    <div>
                        <div class="fw-bold">Équipe WeConnect</div>
                        <div class="text-muted small">Plateforme de solidarité</div>
                    </div>
                </div>

                <!-- =========================================== -->
                <!-- SECTION COMMENTAIRES AVANCÉE -->
                <!-- =========================================== -->
                <section class="mt-5">
                    <h4 class="h5 fw-bold mb-3">Laissez un commentaire</h4>
                    <p class="text-muted small mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        Ton retour nous intéresse — reste poli et constructif.
                    </p>

                    <!-- Messages d'alerte -->
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= $success_message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= $error_message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Formulaire principal de commentaire -->
                    <form id="commentForm" method="POST" class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom *</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   placeholder="Votre nom" value="<?= isset($nom) ? htmlspecialchars($nom) : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email (optionnel)</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   placeholder="Votre email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                        </div>
                        <div class="col-12">
                            <label for="comment" class="form-label">Commentaire *</label>
                            <textarea id="comment" name="comment" class="form-control" rows="4" 
                                      placeholder="Partagez vos pensées..." required><?= isset($comment_text) ? htmlspecialchars($comment_text) : '' ?></textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                En soumettant, vous acceptez nos 
                                <a href="#" class="text-decoration-none">conditions d'utilisation</a>.
                            </small>
                            <button type="submit" name="submit_comment" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>Publier
                            </button>
                        </div>
                    </form>

                    <!-- Liste des commentaires avec pagination -->
                    <div id="commentsList" class="mt-4">
                        <?php if (!empty($commentaires_parents)): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><?= $total_comments ?> commentaire(s)</h5>
                                <?php if ($total_pages > 1): ?>
                                    <small class="text-muted">Page <?= $current_page ?> sur <?= $total_pages ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <?php foreach ($commentaires_parents as $comment): ?>
                                <?php displayComment($comment, $replies_by_parent); ?>
                            <?php endforeach; ?>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Pagination des commentaires">
                                    <ul class="pagination justify-content-center mt-4">
                                        <?php if ($current_page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?id=<?= $article_id ?>&page=<?= $current_page - 1 ?>">
                                                    <i class="fas fa-chevron-left me-1"></i>Précédent
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                                <a class="page-link" href="?id=<?= $article_id ?>&page=<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($current_page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?id=<?= $article_id ?>&page=<?= $current_page + 1 ?>">
                                                    Suivant<i class="fas fa-chevron-right ms-1"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-comments fa-2x mb-2"></i>
                                <p>Aucun commentaire pour le moment. Soyez le premier à commenter !</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

            </div>
        </div>
    </div>
</main>

<!-- =========================================== -->
<!-- FOOTER -->
<!-- =========================================== -->
<footer class="site-footer mt-5">
    <div class="container">
        <p class="mb-2">© 2025 WeConnect. Tous droits réservés.</p>
        <p class="mb-0">
            Suivez-nous sur 
            <a href="#" class="text-decoration-none text-light mx-2">
                <i class="fab fa-facebook-f"></i>
            </a> 
            <a href="#" class="text-decoration-none text-light mx-2">
                <i class="fab fa-twitter"></i>
            </a> 
            <a href="#" class="text-decoration-none text-light mx-2">
                <i class="fab fa-instagram"></i>
            </a>
        </p>
    </div>
</footer>

<!-- =========================================== -->
<!-- SCRIPTS -->
<!-- =========================================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Animation d'apparition du contenu
document.addEventListener('DOMContentLoaded', function() {
    const content = document.querySelector('.article-content');
    if (content) {
        content.style.opacity = '0';
        content.style.transform = 'translateY(20px)';
        content.style.transition = 'all 0.6s ease';
        
        setTimeout(() => {
            content.style.opacity = '1';
            content.style.transform = 'translateY(0)';
        }, 300);
    }
    
    // Gestion des boutons "Répondre"
    document.querySelectorAll('.reply-btn').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const replyForm = document.getElementById('reply-form-' + commentId);
            
            // Masquer tous les autres formulaires de réponse
            document.querySelectorAll('.reply-form').forEach(form => {
                if (form.id !== 'reply-form-' + commentId) {
                    form.style.display = 'none';
                }
            });
            
            // Afficher/masquer le formulaire actuel
            if (replyForm.style.display === 'none') {
                replyForm.style.display = 'block';
                // Pré-remplir le nom si possible
                const replyNameInput = replyForm.querySelector('input[name="reply_name"]');
                if (replyNameInput && !replyNameInput.value) {
                    const mainNameInput = document.getElementById('name');
                    if (mainNameInput && mainNameInput.value) {
                        replyNameInput.value = mainNameInput.value;
                    }
                }
            } else {
                replyForm.style.display = 'none';
            }
        });
    });
    
    // Gestion des boutons "Annuler"
    document.querySelectorAll('.cancel-reply').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.reply-form').style.display = 'none';
        });
    });
});
</script>

</body>
</html>