<?php
// ===========================================
// CONNEXION BDD
// ===========================================
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=weconnect;charset=utf8',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// ===========================================
// RÉCUPÉRATION DES ARTICLES PUBLIÉS
// ===========================================
$stmt = $pdo->query("
    SELECT b.*, c.nom_categorie 
    FROM blogs b
    INNER JOIN categories c ON b.categorie_blog = c.id_categorie
    WHERE b.est_publie_blog = 1
    ORDER BY b.date_creation_blog DESC
");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===========================================
// RÉCUPÉRATION DES CATÉGORIES
// ===========================================
$stmt_cat = $pdo->query("SELECT * FROM categories ORDER BY nom_categorie");
$categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

// ===========================================
// ARTICLES RÉCENTS (3 derniers)
// ===========================================
$stmt_recent = $pdo->query("
    SELECT b.*, c.nom_categorie 
    FROM blogs b
    INNER JOIN categories c ON b.categorie_blog = c.id_categorie
    WHERE b.est_publie_blog = 1
    ORDER BY b.date_creation_blog DESC
    LIMIT 3
");
$articles_recents = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour tronquer le texte
function tronquer($texte, $longueur = 100) {
    if (strlen($texte) > $longueur) {
        return substr($texte, 0, $longueur) . '...';
    }
    return $texte;
}

// Fonction pour formater la date en français
function dateEnFrancais($date) {
    $mois = [
        1 => 'jan.', 2 => 'fév.', 3 => 'mars', 4 => 'avr.',
        5 => 'mai', 6 => 'juin', 7 => 'juil.', 8 => 'août',
        9 => 'sept.', 10 => 'oct.', 11 => 'nov.', 12 => 'déc.'
    ];
    $timestamp = strtotime($date);
    $jour = date('d', $timestamp);
    $moisNum = date('n', $timestamp);
    $annee = date('Y', $timestamp);
    return $jour . ' ' . $mois[$moisNum] . ' ' . $annee;
}

// Couleurs des badges par catégorie
$badge_colors = [
    'primary' => 'bg-primary',
    'success' => 'bg-success',
    'warning' => 'bg-warning',
    'info' => 'bg-info',
    'danger' => 'bg-danger',
    'secondary' => 'bg-secondary'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog de solidarité - WeConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Bouton admin flottant */
        .admin-fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #93C572 0%, #7AA959 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 10px 40px rgba(147, 197, 114, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
            border: none;
            text-decoration: none;
        }

        .admin-fab:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 20px 60px rgba(147, 197, 114, 0.6);
            background: linear-gradient(135deg, #7AA959 0%, #93C572 100%);
        }

        .admin-fab i {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        /* Badge admin dans la navbar */
        .admin-badge-nav {
            background: linear-gradient(135deg, #93C572 0%, #7AA959 100%);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .admin-badge-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(147, 197, 114, 0.4);
            color: white;
        }

        .blog-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
        }

        .blog-card img {
            height: 200px;
            object-fit: cover;
        }

        .recent-post img {
            border-radius: 8px;
            object-fit: cover;
            height: 60px;
        }

        .recent-post h5 {
            font-size: 14px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-hands-helping me-1"></i>WeConnect</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Projets</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Forum</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="blog.php" data-bs-toggle="dropdown">Blog</a>
                    <ul class="dropdown-menu">
                        <?php foreach ($categories as $cat): ?>
                        <li><a class="dropdown-item" href="blog.php?categorie=<?= $cat['id_categorie'] ?>"><?= htmlspecialchars($cat['nom_categorie']) ?></a></li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="blog.php">Tous les articles</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="#">Événements</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
            </ul>
            <div class="ms-lg-3 mt-3 mt-lg-0 d-flex gap-2">
                <button class="btn btn-primary" id="joinBtn">Rejoindre notre communauté</button>
                <!-- Bouton Admin dans la navbar -->
                <a href="/categorie/plateforme/view/backoffice/back.php" class="admin-badge-nav">
                    <i class="fas fa-shield-alt"></i>
                    Admin
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Bouton Admin Flottant -->
<a href="back.php" class="admin-fab" title="Accéder à l'administration">
    <i class="fas fa-cog"></i>
</a>

<!-- Header -->
<header class="mt-5 pt-5 text-center bg-light py-5">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Blog de solidarité</h1>
        <p class="lead mb-0">Découvrez les initiatives de paix et d'inclusion de notre communauté 🌍</p>
    </div>
</header>

<!-- Main Content -->
<main class="py-5">
    <div class="container">
        <div class="row">
            <!-- Articles -->
            <div class="col-lg-8">
                <?php if (empty($articles)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        Aucun article publié pour le moment. Revenez bientôt !
                    </div>
                <?php else: ?>
                <div class="row g-4">
                    <?php 
                    $colors = array_values($badge_colors);
                    foreach ($articles as $index => $article): 
                        $color_class = $colors[$index % count($colors)];
                    ?>
                    <div class="col-md-6">
                        <div class="blog-card border rounded shadow-sm h-100">
                            <?php if (!empty($article['image_blog'])): ?>
                            <img src="<?= htmlspecialchars($article['image_blog']) ?>" alt="<?= htmlspecialchars($article['titre_blog']) ?>" class="img-fluid rounded-top">
                            <?php else: ?>
                            <img src="https://via.placeholder.com/400x200?text=<?= urlencode($article['titre_blog']) ?>" alt="<?= htmlspecialchars($article['titre_blog']) ?>" class="img-fluid rounded-top">
                            <?php endif; ?>
                            <div class="p-3">
                                <span class="badge <?= $color_class ?> mb-2"><?= htmlspecialchars($article['nom_categorie']) ?></span>
                                <h4 class="mb-2"><?= htmlspecialchars($article['titre_blog']) ?></h4>
                                <p class="text-muted"><?= tronquer(strip_tags($article['contenu_blog']), 120) ?></p>
                                <small class="text-muted"><?= dateEnFrancais($article['date_creation_blog']) ?></small>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <span><i class="far fa-comment"></i> 0</span>
                                        <span class="ms-2"><i class="far fa-heart"></i> 0</span>
                                    </div>
                                    <a href="article.php?id=<?= $article['id_blog'] ?>" class="btn btn-primary btn-sm">Lire la suite</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- À propos -->
                <div class="sidebar-widget mb-4 p-3 border rounded shadow-sm">
                    <h4>À propos de WeConnect</h4>
                    <p>WeConnect met en relation bénévoles et associations pour créer un impact positif.</p>
                    <button class="btn btn-outline-primary w-100" id="sidebarJoinBtn">Rejoindre notre communauté</button>
                </div>

                <!-- Articles récents -->
                <div class="sidebar-widget mb-4 p-3 border rounded shadow-sm">
                    <h4>Articles récents</h4>
                    <?php foreach ($articles_recents as $recent): ?>
                    <div class="recent-post d-flex mb-3">
                        <?php if (!empty($recent['image_blog'])): ?>
                        <img src="<?= htmlspecialchars($recent['image_blog']) ?>" alt="<?= htmlspecialchars($recent['titre_blog']) ?>" class="me-2 img-fluid" style="width:60px;">
                        <?php else: ?>
                        <img src="https://via.placeholder.com/60?text=Blog" alt="<?= htmlspecialchars($recent['titre_blog']) ?>" class="me-2 img-fluid" style="width:60px;">
                        <?php endif; ?>
                        <div>
                            <a href="article.php?id=<?= $recent['id_blog'] ?>" class="text-decoration-none text-dark">
                                <h5 class="mb-0"><?= htmlspecialchars(tronquer($recent['titre_blog'], 50)) ?></h5>
                            </a>
                            <small class="text-muted"><?= dateEnFrancais($recent['date_creation_blog']) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Catégories -->
                <div class="sidebar-widget mb-4 p-3 border rounded shadow-sm">
                    <h4>Catégories</h4>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($categories as $cat): ?>
                        <a href="blog.php?categorie=<?= $cat['id_categorie'] ?>" class="tag btn btn-light btn-sm text-decoration-none">
                            <?= htmlspecialchars($cat['nom_categorie']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Événements -->
                <div class="sidebar-widget mb-4 p-3 border rounded shadow-sm">
                    <h4>Événements à venir</h4>
                    <div class="mb-3 border-bottom pb-2">
                        <h5 class="mb-1">Collecte de vêtements d'hiver</h5>
                        <small><i class="far fa-calendar me-1"></i>15 janvier 2026</small><br>
                        <small><i class="fas fa-map-marker-alt me-1"></i>Centre communautaire WeConnect</small>
                    </div>
                    <div class="mb-3 border-bottom pb-2">
                        <h5 class="mb-1">Atelier jardinage urbain</h5>
                        <small><i class="far fa-calendar me-1"></i>22 novembre 2025</small><br>
                        <small><i class="fas fa-map-marker-alt me-1"></i>Jardin partagé du quartier</small>
                    </div>
                    <div>
                        <h5 class="mb-1">Forum de l'emploi solidaire</h5>
                        <small><i class="far fa-calendar me-1"></i>5 décembre 2025</small><br>
                        <small><i class="fas fa-map-marker-alt me-1"></i>Espace WeConnect</small>
                    </div>
                </div>

                <!-- Newsletter -->
                <div class="sidebar-widget p-3 border rounded shadow-sm">
                    <h4>Restez informé</h4>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Votre email" id="newsletterEmail">
                        <button class="btn btn-primary" id="subscribeBtn">S'abonner</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-3">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h5><i class="fas fa-hands-helping me-2"></i>WeConnect</h5>
                <p>Plateforme de solidarité mettant en relation bénévoles et associations pour un impact positif.</p>
            </div>
            <div class="col-lg-2 col-md-6 mb-3">
                <h5>Liens rapides</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white text-decoration-none">Accueil</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Projets</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Forum</a></li>
                    <li><a href="blog.php" class="text-white text-decoration-none">Blog</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Événements</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <h5>Catégories</h5>
                <ul class="list-unstyled">
                    <?php foreach ($categories as $cat): ?>
                    <li><a href="blog.php?categorie=<?= $cat['id_categorie'] ?>" class="text-white text-decoration-none"><?= htmlspecialchars($cat['nom_categorie']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5>Contact</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-map-marker-alt me-1"></i>123 Rue de la Solidarité, Tunis</li>
                    <li><i class="fas fa-phone me-1"></i>+216 12 345 678</li>
                    <li><i class="fas fa-envelope me-1"></i>contact@weconnect.tn</li>
                    <li><i class="fas fa-clock me-1"></i>Lun - Ven: 9h - 18h</li>
                </ul>
            </div>
        </div>
        <div class="text-center mt-3">
            &copy; 2025 WeConnect. Tous droits réservés.
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script bouton -->
<script>
document.getElementById('joinBtn').addEventListener('click', ()=>window.location.href='inscription.html');
document.getElementById('sidebarJoinBtn').addEventListener('click', ()=>window.location.href='inscription.html');
document.getElementById('subscribeBtn').addEventListener('click', ()=>{
    const email = document.getElementById('newsletterEmail').value;
    if(email) alert(`Merci ! Vous êtes maintenant abonné avec l'email: ${email}`);
});
</script>

</body>
</html>