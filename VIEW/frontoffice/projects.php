<?php
// Inclure les fichiers nécessaires
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

// Initialiser le contrôleur
$projectController = new ProjectController();

// Récupérer les paramètres
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Récupérer les projets selon les filtres
if ($category) {
    $allProjects = $projectController->getProjectsByCategory($category);
} elseif ($search) {
    $allProjects = $projectController->searchProjects($search);
} else {
    $allProjects = $projectController->getAllProjects();
}

// Filtrer par statut si spécifié
if ($status && $allProjects) {
    $allProjects = array_filter($allProjects, function($project) use ($status) {
        return $project['disponibilite'] === $status;
    });
}

// Filtrer par lieu si spécifié
if ($location && $allProjects) {
    $allProjects = array_filter($allProjects, function($project) use ($location) {
        return stripos($project['lieu'] ?? '', $location) !== false;
    });
}

// Pagination
$projectsPerPage = 6;
$totalProjects = count($allProjects);
$totalPages = ceil($totalProjects / $projectsPerPage);
$startIndex = ($page - 1) * $projectsPerPage;
$projects = array_slice($allProjects, $startIndex, $projectsPerPage);

// Récupérer les statistiques pour les filtres
$categories = $projectController->getCategories();
$statuses = $projectController->getAvailabilityStatuses();

// Définir les noms de catégories pour l'affichage
$categoryNames = [
    'Solidarité' => 'Solidarité',
    'Environement' => 'Environnement',
    'Education' => 'Éducation',
    'Sante' => 'Santé',
    'Aide' => 'Aide',
    'Culture' => 'Culture'
];

// Titre de la page selon la catégorie
$pageTitle = $category ? 'Projets de ' . ($categoryNames[$category] ?? $category) : 'Tous les Projets';

// Fonction pour construire la query string avec les filtres
function buildQueryString($updates = []) {
    $params = $_GET;
    foreach ($updates as $key => $value) {
        if ($value === '' || $value === null) {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }
    unset($params['page']); // On gère la page séparément
    return http_build_query($params);
}

// Fonction pour tronquer le texte
function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($pageTitle); ?> - WeConnect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../style/projects.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <span class="logo">We<span>Connect</span></span>
        </div>
        <div class="nav-menu">
            <a href="../index.php" class="nav-link">Accueil</a>
            <a href="categorie.php" class="nav-link">Catégories</a>
            <a href="#contact" class="nav-link">Contact</a>
        </div>
        <div class="nav-actions">
            <button class="btn-login" onclick="location.href='login.php'">Connexion</button>
            <button class="btn-primary" onclick="location.href='register.php'">S'inscrire</button>
        </div>
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<header class="hero-header">
    <div class="hero-container">
        <h1 class="fade-in">
            <?php if ($category): ?>
                🌍 <?php echo htmlspecialchars($pageTitle); ?>
            <?php else: ?>
                🌍 Tous Nos Projets de Volontariat
            <?php endif; ?>
        </h1>
        <p class="fade-in">
            <?php if ($category): ?>
                Découvrez tous nos projets dans la catégorie <?php echo htmlspecialchars($categoryNames[$category] ?? $category); ?>
            <?php else: ?>
                Participez à des actions solidaires et environnementales en Tunisie et dans le monde
            <?php endif; ?>
        </p>
        <?php if ($category): ?>
        <div class="category-breadcrumb">
            <a href="categorie.php">Catégories</a> 
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($categoryNames[$category] ?? $category); ?></span>
        </div>
        <?php endif; ?>
    </div>
</header>

<!-- Filtres et Recherche -->
<section class="filters-section">
    <div class="container">
        <div class="filters-container">
            <!-- Barre de recherche -->
            <div class="search-box">
                <form method="GET" class="search-form">
                    <div class="search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Rechercher un projet par titre, description ou association..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="search-btn">Rechercher</button>
                    </div>
                    <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <?php endif; ?>
                </form>
            </div>

            <!-- Filtres -->
            <div class="filters-row">
                <div class="filter-group">
                    <label for="category-filter"><i class="fas fa-filter"></i> Catégorie</label>
                    <select id="category-filter" class="filter-select" onchange="updateFilter('category', this.value)">
                        <option value="">Toutes les catégories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                    <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($categoryNames[$cat] ?? $cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="status-filter"><i class="fas fa-info-circle"></i> Statut</label>
                    <select id="status-filter" class="filter-select" onchange="updateFilter('status', this.value)">
                        <option value="">Tous les statuts</option>
                        <?php foreach($statuses as $stat): ?>
                            <option value="<?php echo htmlspecialchars($stat); ?>" 
                                    <?php echo $status === $stat ? 'selected' : ''; ?>>
                                <?php 
                                $statusText = [
                                    'disponible' => 'Disponible',
                                    'complet' => 'Complet',
                                    'termine' => 'Terminé'
                                ];
                                echo $statusText[$stat] ?? $stat; 
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="location-filter"><i class="fas fa-map-marker-alt"></i> Lieu</label>
                    <input type="text" id="location-filter" class="filter-input" 
                           placeholder="Ville ou région" value="<?php echo htmlspecialchars($location); ?>"
                           onchange="updateFilter('location', this.value)">
                </div>

                <?php if ($search || $category || $status || $location): ?>
                <div class="filter-actions">
                    <a href="projects.php" class="btn-clear-filters">
                        <i class="fas fa-times"></i>
                        Effacer les filtres
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Résultats de la recherche -->
            <?php if ($search || $status || $location): ?>
            <div class="search-results-info">
                <p>
                    <i class="fas fa-search"></i>
                    <?php echo $totalProjects; ?> projet(s) trouvé(s)
                    <?php if ($search): ?> pour "<?php echo htmlspecialchars($search); ?>"<?php endif; ?>
                    <?php if ($status): ?> avec le statut "<?php echo $statusText[$status] ?? $status; ?>"<?php endif; ?>
                    <?php if ($location): ?> à "<?php echo htmlspecialchars($location); ?>"<?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="container">
    <?php if (count($projects) > 0): ?>
        <div class="projects-grid">
            <?php foreach($projects as $project): ?>
                <?php
                // Formater les données du projet
                $projectId = $project['id_projet'];
                $projectTitle = htmlspecialchars($project['titre']);
                $projectCategory = htmlspecialchars($project['categorie']);
                $projectAssociation = htmlspecialchars($project['association_nom'] ?? 'Association');
                $projectLocation = htmlspecialchars($project['lieu'] ?? 'Non spécifié');
                $projectDescription = truncateText(htmlspecialchars($project['descriptionp'] ?? 'Aucune description disponible'));
                $projectStartDate = $project['date_debut'] ? date('d/m/Y', strtotime($project['date_debut'])) : 'Flexible';
                $projectEndDate = $project['date_fin'] ? date('d/m/Y', strtotime($project['date_fin'])) : 'En cours';
                $participantsCount = $projectController->getParticipantsCount($projectId);
                $availability = $project['disponibilite'] ?? 'disponible';
                
                // Définir la classe de badge selon la disponibilité
                $availabilityClass = '';
                $availabilityText = '';
                switch($availability) {
                    case 'disponible':
                        $availabilityClass = 'available';
                        $availabilityText = 'Disponible';
                        break;
                    case 'complet':
                        $availabilityClass = 'full';
                        $availabilityText = 'Complet';
                        break;
                    case 'termine':
                        $availabilityClass = 'ended';
                        $availabilityText = 'Terminé';
                        break;
                    default:
                        $availabilityClass = 'available';
                        $availabilityText = 'Disponible';
                }
                ?>
                
                <div class="project-card fade-in" data-category="<?php echo strtolower($projectCategory); ?>" data-id="<?php echo $projectId; ?>">
                    <div class="project-header">
                        <div class="project-badge <?php echo $availabilityClass; ?>">
                            <?php echo htmlspecialchars($projectCategory); ?>
                        </div>
                        <div class="availability-badge <?php echo $availabilityClass; ?>">
                            <?php echo $availabilityText; ?>
                        </div>
                    </div>
                    
                    <h2><?php echo $projectTitle; ?></h2>
                    
                    <div class="project-meta">
                        <span class="meta-item">
                            <i class="fas fa-building"></i>
                            <?php echo $projectAssociation; ?>
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo $projectLocation; ?>
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <?php echo $projectStartDate; ?> - <?php echo $projectEndDate; ?>
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-users"></i>
                            <?php echo $participantsCount; ?> participant<?php echo $participantsCount > 1 ? 's' : ''; ?>
                        </span>
                    </div>
                    
                    <div class="project-details">
                        <h3>Description</h3>
                        <p><?php echo $projectDescription; ?></p>
                        
                        <?php if ($project['date_debut'] || $project['date_fin']): ?>
                        <h3>Période</h3>
                        <p>
                            <?php if ($project['date_debut'] && $project['date_fin']): ?>
                                Du <?php echo $projectStartDate; ?> au <?php echo $projectEndDate; ?>
                            <?php elseif ($project['date_debut']): ?>
                                À partir du <?php echo $projectStartDate; ?>
                            <?php else: ?>
                                Dates flexibles
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="project-actions">
                        <a href="details.php?id=<?php echo $projectId; ?>" class="btn-primary">
                            Voir plus
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <button class="btn-secondary save-project" data-project-id="<?php echo $projectId; ?>">
                            <i class="fas fa-heart"></i>
                            Sauvegarder
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <div class="pagination-controls">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo buildQueryString(); ?>&page=<?php echo $page - 1; ?>" class="pagination-btn">
                        <i class="fas fa-chevron-left"></i> Précédent
                    </a>
                <?php endif; ?>

                <div class="pagination-numbers">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
                            <a href="?<?php echo buildQueryString(); ?>&page=<?php echo $i; ?>" 
                               class="pagination-number <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php elseif (abs($i - $page) == 3): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo buildQueryString(); ?>&page=<?php echo $page + 1; ?>" class="pagination-btn">
                        Suivant <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <div class="pagination-info">
                Page <?php echo $page; ?> sur <?php echo $totalPages; ?> - 
                <?php echo $totalProjects; ?> projet(s) au total
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="no-projects-message">
            <i class="fas fa-search"></i>
            <h3>Aucun projet trouvé</h3>
            <p>
                <?php if ($category): ?>
                    Aucun projet disponible dans la catégorie "<?php echo htmlspecialchars($categoryNames[$category] ?? $category); ?>" pour le moment.
                <?php elseif ($search || $status || $location): ?>
                    Aucun projet ne correspond à vos critères de recherche. Essayez de modifier vos filtres.
                <?php else: ?>
                    Aucun projet disponible pour le moment. Revenez bientôt !
                <?php endif; ?>
            </p>
            <div class="no-projects-actions">
                <a href="categorie.php" class="btn-primary">
                    <i class="fas fa-arrow-left"></i>
                    Retour aux catégories
                </a>
                <?php if ($search || $status || $location): ?>
                <a href="projects.php" class="btn-secondary">
                    <i class="fas fa-times"></i>
                    Effacer les filtres
                </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

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
                    <li><a href="../index.php">Accueil</a></li>
                    <li><a href="categorie.php">Catégories</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="projects.php">Tous les projets</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Catégories</h4>
                <ul>
                    <li><a href="projects.php?category=Solidarité">Solidarité</a></li>
                    <li><a href="projects.php?category=Environement">Environnement</a></li>
                    <li><a href="projects.php?category=Education">Éducation</a></li>
                    <li><a href="projects.php?category=Sante">Santé</a></li>
                    <li><a href="projects.php?category=Aide">Aide</a></li>
                    <li><a href="projects.php?category=Culture">Culture</a></li>
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

<script>
  // Animation au scroll
  const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
          if (entry.isIntersecting) {
              entry.target.style.animation = 'fadeInUp 0.8s ease forwards';
              observer.unobserve(entry.target);
          }
      });
  }, observerOptions);

  // Fonction pour mettre à jour les filtres
  function updateFilter(type, value) {
      const url = new URL(window.location.href);
      
      if (value) {
          url.searchParams.set(type, value);
      } else {
          url.searchParams.delete(type);
      }
      
      // Retirer la pagination quand on change les filtres
      url.searchParams.delete('page');
      
      window.location.href = url.toString();
  }

  // Observer les éléments avec la classe fade-in
  document.addEventListener('DOMContentLoaded', () => {
      const fadeElements = document.querySelectorAll('.fade-in');
      fadeElements.forEach(el => {
          observer.observe(el);
      });

      // Gestion du bouton sauvegarder
      document.querySelectorAll('.save-project').forEach(button => {
          button.addEventListener('click', function() {
              const projectId = this.getAttribute('data-project-id');
              this.classList.toggle('saved');
              if (this.classList.contains('saved')) {
                  this.innerHTML = '<i class="fas fa-heart"></i> Sauvegardé';
                  // Ici vous pouvez ajouter une requête AJAX pour sauvegarder le projet
                  saveProjectToFavorites(projectId);
              } else {
                  this.innerHTML = '<i class="fas fa-heart"></i> Sauvegarder';
                  removeProjectFromFavorites(projectId);
              }
          });
      });

      // Recherche en temps réel (optionnel)
      const searchInput = document.querySelector('input[name="search"]');
      let searchTimeout;
      searchInput.addEventListener('input', function() {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => {
              if (this.value.length >= 3 || this.value.length === 0) {
                  this.form.submit();
              }
          }, 800);
      });
  });

  // Fonctions pour la sauvegarde des projets (à implémenter)
  function saveProjectToFavorites(projectId) {
      // Implémenter la logique AJAX pour sauvegarder le projet
      console.log('Sauvegarde du projet:', projectId);
  }

  function removeProjectFromFavorites(projectId) {
      // Implémenter la logique AJAX pour retirer le projet des favoris
      console.log('Retrait du projet:', projectId);
  }
</script>

</body>
</html>