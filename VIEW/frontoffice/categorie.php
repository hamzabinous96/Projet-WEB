<?php
// Inclure les fichiers nécessaires
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

// Initialiser le contrôleur
$projectController = new ProjectController();

// Récupérer les statistiques par catégorie depuis la base de données
$statsCategories = $projectController->getProjectsByCategoryStats();

// Définir les catégories avec leurs icônes et descriptions
$categories = [
    'Solidarité' => [
        'icon' => 'fas fa-hand-holding-heart',
        'description' => 'Aide aux personnes défavorisées et actions sociales communautaires',
        'url' => 'projects.php?category=Solidarité'
    ],
    'Environement' => [
        'icon' => 'fas fa-leaf',
        'description' => 'Protection de la nature et initiatives de développement durable',
        'url' => 'projects.php?category=Environement'
    ],
    'Education' => [
        'icon' => 'fas fa-graduation-cap',
        'description' => 'Soutien scolaire et programmes éducatifs pour tous les âges',
        'url' => 'projects.php?category=Education'
    ],
    'Sante' => [
        'icon' => 'fas fa-heartbeat',
        'description' => 'Sensibilisation et actions pour la santé physique et mentale',
        'url' => 'projects.php?category=Sante'
    ],
    'Aide' => [
        'icon' => 'fas fa-utensils',
        'description' => 'Distribution de nourriture et lutte contre la précarité alimentaire',
        'url' => 'projects.php?category=Aide'
    ],
    'Culture' => [
        'icon' => 'fas fa-palette',
        'description' => 'Promotion des arts et préservation du patrimoine culturel',
        'url' => 'projects.php?category=Culture'
    ]
];

// Calculer le total des projets
$totalProjects = array_sum($statsCategories);
$totalCategories = count($categories); // Maintenant on compte toutes les catégories définies
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeConnect - Communauté Solidaire</title>
    <link rel="stylesheet" href="../style/categorie.css">
    <link rel="stylesheet" href="../style/projects.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body onload="window.scrollTo(0,0)"> 

<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <span class="logo">We<span>Connect</span></span>
        </div>
        <div class="nav-menu">
            <a href="#hero-section" class="nav-link">Accueil</a>
            <a href="#categories-section" class="nav-link">À propos</a>
            <a href="#contact" class="nav-link">Contact</a>
        </div>
        <div class="nav-actions">
            <button class="btn-login" onclick="location.href='login.php'">Connexion</button>
            <button class="btn-primary"onclick="location.href='register.php'">S'inscrire</button>
        </div>
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero" id="hero-section">
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-badge">
                <span>🌍 Explorez les différentes catégories</span>
            </div>
            <h1 class="hero-title">
                Découvrez nos 
                <span class="highlight">catégories de projets</span>
            </h1>
            <p class="hero-description">
                Parcourez nos différentes catégories pour trouver des projets qui vous passionnent et auxquels vous pouvez contribuer. 
                Que ce soit la solidarité, l'environnement, l'éducation ou la santé, chaque action compte pour construire une société plus inclusive et solidaire.
            </p>
            <div class="hero-actions">
                <a href="#categories-section" class="btn-primary btn-large">
                    Voir toutes les catégories
                    <i class="fas fa-arrow-right"></i>
                </a>
                <button class="btn-secondary btn-large">
                    <i class="fas fa-play"></i>
                    Comment ça marche
                </button>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <h3><?php echo $totalCategories; ?>+</h3>
                    <p>Catégories</p>
                </div>
                <div class="stat">
                    <h3><?php echo $totalProjects; ?>+</h3>
                    <p>Projets actifs</p>
                </div>
                <div class="stat">
                    <h3><?php echo $projectController->getTotalParticipants(); ?>+</h3>
                    <p>Volontaires</p>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-card">
                <div class="card-image">
                    <div class="image-placeholder">
                        <img src="../assets/categorie.png" alt="Catégories WeConnect">
                    </div>
                </div>
                <div class="card-content">
                    <h4>Projets de Solidarité</h4>
                    <p>Rejoignez des actions sociales qui font la différence</p>
                    <div class="card-meta">
                        <span class="participants">
                            <i class="fas fa-users"></i>
                            <?php echo $statsCategories['Solidarité'] ?? 0; ?> projets
                        </span>
                        <span class="rating">
                            <i class="fas fa-star"></i>
                            4.8/5
                        </span>
                    </div>
                </div>
            </div>
            <div class="floating-element el-1">
                <i class="fas fa-hands-helping"></i>
            </div>
            <div class="floating-element el-2">
                <i class="fas fa-seedling"></i>
            </div>
            <div class="floating-element el-3">
                <i class="fas fa-heart"></i>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section id="categories-section">
    <div class="container">
        <div class="section-header">
            <h2>Catégories Disponibles</h2>
            <p>Choisissez votre domaine d'action préféré</p>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $categoryName => $categoryData): ?>
                <?php 
                $projectCount = $statsCategories[$categoryName] ?? 0;
                ?>
                <div class="category-card" onclick="location.href='<?php echo $categoryData['url']; ?>'">
                    <div class="category-icon">
                        <i class="<?php echo $categoryData['icon']; ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($categoryName); ?></h3>
                    <p><?php echo htmlspecialchars($categoryData['description']); ?></p>
                    <span class="project-count"><?php echo $projectCount; ?> projet<?php echo $projectCount > 1 ? 's' : ''; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($totalProjects === 0): ?>
        <div class="no-projects-message">
            <i class="fas fa-info-circle"></i>
            <h3>Aucun projet disponible pour le moment</h3>
            <p>Revenez bientôt pour découvrir nos nouvelles initiatives !</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2>Prêt à vous engager ?</h2>
            <p>Rejoignez notre communauté de volontaires et commencez à faire la différence dès aujourd'hui</p>
            <div class="cta-actions">
                <button class="btn-primary btn-large">
                    Rejoindre maintenant
                    <i class="fas fa-arrow-right"></i>
                </button>
                <button class="btn-secondary btn-large">
                    Découvrir plus
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer id="contact">
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <span class="logo">We<span>Connect</span></span>
                </div>
                <p>Plateforme de volontariat qui connecte les passionnés avec des projets qui changent le monde.</p>
                <div class="social-links">
                    <a href="https://www.facebook.com" target="_blank" class="social-link">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com" target="_blank" class="social-link">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.tiktok.com" target="_blank" class="social-link">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Navigation</h4>
                <ul>
                    <li><a href="#hero-section">Accueil</a></li>
                    <li><a href="#categories-section">À propos</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="#">Projets</a></li>
                    <li><a href="#">Catégories</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Catégories</h4>
                <ul>
                    <?php foreach ($categories as $categoryName => $categoryData): ?>
                        <li><a href="<?php echo $categoryData['url']; ?>"><?php echo htmlspecialchars($categoryName); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contact</h4>
                <ul>
                    <li><i class="fas fa-envelope"></i> contact@weconnect.tn</li>
                    <li><i class="fas fa-phone"></i> +216 12 345 678</li>
                    <li><i class="fas fa-map-marker-alt"></i> Tunis, Tunisia</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 WeConnect. Tous droits réservés.</p>
            <div class="footer-links">
                <a href="#">Politique de confidentialité</a>
                <a href="#">Conditions d'utilisation</a>
            </div>
        </div>
    </div>
</footer>

<script src="../script/categorie.js"></script>

</body>
</html>