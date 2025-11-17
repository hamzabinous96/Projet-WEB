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
            border-left: 3px solid var(--primary-color);
            padding-left: 15px;
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
                <!-- SECTION COMMENTAIRES -->
                <!-- =========================================== -->
                <section class="mt-5">
                    <h4 class="h5 fw-bold mb-3">Laissez un commentaire</h4>
                    <p class="text-muted small mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        Ton retour nous intéresse — reste poli et constructif.
                    </p>
                    
                    <form id="commentForm" class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom *</label>
                            <input type="text" id="name" class="form-control" placeholder="Votre nom" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email (optionnel)</label>
                            <input type="email" id="email" class="form-control" placeholder="Votre email">
                        </div>
                        <div class="col-12">
                            <label for="comment" class="form-label">Commentaire *</label>
                            <textarea id="comment" class="form-control" rows="4" 
                                      placeholder="Partagez vos pensées..." required></textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                En soumettant, vous acceptez nos 
                                <a href="#" class="text-decoration-none">conditions d'utilisation</a>.
                            </small>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>Publier
                            </button>
                        </div>
                    </form>

                    <!-- Liste des commentaires -->
                    <div id="commentsList" class="mt-4">
                        <div class="comment-item mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <strong>Marie L.</strong>
                                <small class="text-muted">Il y a 2 jours</small>
                            </div>
                            <p class="mb-0">Très bel article ! Merci pour ce partage inspirant.</p>
                        </div>
                        
                        <div class="comment-item mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <strong>Jean D.</strong>
                                <small class="text-muted">Il y a 1 semaine</small>
                            </div>
                            <p class="mb-0">Je partage totalement cette vision de la solidarité numérique.</p>
                        </div>
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
// Gestion des commentaires
document.getElementById('commentForm').addEventListener('submit', function(e){
    e.preventDefault();
    
    const name = document.getElementById('name').value.trim();
    const comment = document.getElementById('comment').value.trim();
    
    if(!name || !comment) {
        alert('Veuillez remplir tous les champs obligatoires.');
        return;
    }

    // Créer le nouveau commentaire
    const container = document.createElement('div');
    container.className = 'comment-item mb-3 p-3 bg-light rounded';
    container.innerHTML = `
        <div class="d-flex justify-content-between mb-2">
            <strong>${escapeHtml(name)}</strong>
            <small class="text-muted">À l'instant</small>
        </div>
        <p class="mb-0">${escapeHtml(comment)}</p>
    `;
    
    // Ajouter en haut de la liste
    document.getElementById('commentsList').prepend(container);
    
    // Réinitialiser le formulaire
    document.getElementById('commentForm').reset();
    
    // Message de confirmation
    showNotification('Commentaire publié avec succès !');
});

// Fonction d'échappement HTML
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Fonction de notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3';
    notification.style.zIndex = '1060';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

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
});
</script>

</body>
</html>