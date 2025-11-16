<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

$projectController = new ProjectController();

$projectId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$project = $projectController->getProjectById($projectId);

if (!$project) {
    header('HTTP/1.0 404 Not Found');
    exit('Projet non trouvé');
}

$participantsCount = $projectController->getParticipantsCount($projectId);

$projectTitle = htmlspecialchars($project['titre']);
$projectCategory = htmlspecialchars($project['categorie']);
$projectAssociation = htmlspecialchars($project['association_nom'] ?? 'Association');
$projectLocation = htmlspecialchars($project['lieu'] ?? 'Non spécifié');
$projectDescription = htmlspecialchars($project['descriptionp'] ?? 'Aucune description disponible');
$projectStartDate = $project['date_debut'] ? date('d/m/Y', strtotime($project['date_debut'])) : 'Flexible';
$projectEndDate = $project['date_fin'] ? date('d/m/Y', strtotime($project['date_fin'])) : 'En cours';
$availability = $project['disponibilite'] ?? 'disponible';

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

$categoryNames = [
    'Solidarité' => 'Solidarité',
    'Environement' => 'Environnement',
    'Education' => 'Éducation',
    'Sante' => 'Santé',
    'Aide' => 'Aide',
    'Culture' => 'Culture'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?php echo $projectTitle; ?> - WeConnect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../style/details.css">
  <link rel="stylesheet" href="../style/projects.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <span class="logo">We<span>Connect</span></span>
        </div>
        <div class="nav-menu">
            <a href="../index.php" class="nav-link">Accueil</a>
            <a href="categorie.php" class="nav-link">Catégories</a>
            <a href="projects.php" class="nav-link">Projets</a>
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

<header class="hero-header">
    <div class="hero-container">
        <div class="breadcrumb">
            <a href="projects.php">Projets</a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo $projectTitle; ?></span>
        </div>
        <h1 class="fade-in"><?php echo $projectTitle; ?></h1>
        <p class="fade-in"><?php echo $projectAssociation; ?> - <?php echo $projectLocation; ?></p>
    </div>
</header>

<div class="project-details-container fade-in" 
     data-project-id="<?php echo $projectId; ?>"
     data-project-title="<?php echo $projectTitle; ?>"
     data-project-association="<?php echo $projectAssociation; ?>"
     data-project-location="<?php echo $projectLocation; ?>"
     data-project-availability="<?php echo $availability; ?>"
     data-project-category="<?php echo $projectCategory; ?>"
     data-participants-count="<?php echo $participantsCount; ?>">
    <div class="project-header">
        <div class="project-meta">
            <div class="meta-badges">
                <span class="category-badge <?php echo $availabilityClass; ?>">
                    <?php echo $categoryNames[$projectCategory] ?? $projectCategory; ?>
                </span>
                <span class="availability-badge <?php echo $availabilityClass; ?>">
                    <?php echo $availabilityText; ?>
                </span>
            </div>
            <div class="project-stats">
                <div class="stat">
                    <i class="fas fa-users"></i>
                    <span><?php echo $participantsCount; ?> participant<?php echo $participantsCount > 1 ? 's' : ''; ?></span>
                </div>
                <div class="stat">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo $projectStartDate; ?> - <?php echo $projectEndDate; ?></span>
                </div>
                <div class="stat">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?php echo $projectLocation; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="project-content">
        <div class="project-main">
            <section class="project-section">
                <h2>Description du Projet</h2>
                <div class="description-content">
                    <?php echo nl2br($projectDescription); ?>
                </div>
            </section>

            <section class="project-section">
                <h2>Détails du Projet</h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <i class="fas fa-building"></i>
                        <div>
                            <h4>Association</h4>
                            <p><?php echo $projectAssociation; ?></p>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Lieu</h4>
                            <p><?php echo $projectLocation; ?></p>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-calendar"></i>
                        <div>
                            <h4>Période</h4>
                            <p>
                                <?php if ($project['date_debut'] && $project['date_fin']): ?>
                                    Du <?php echo $projectStartDate; ?> au <?php echo $projectEndDate; ?>
                                <?php elseif ($project['date_debut']): ?>
                                    À partir du <?php echo $projectStartDate; ?>
                                <?php else: ?>
                                    Dates flexibles
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-tag"></i>
                        <div>
                            <h4>Catégorie</h4>
                            <p><?php echo $categoryNames[$projectCategory] ?? $projectCategory; ?></p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="project-sidebar">
            <div class="action-card">
                <h3>Participer au Projet</h3>
                <div class="project-status">
                    <span class="status-badge <?php echo $availabilityClass; ?>">
                        <?php echo $availabilityText; ?>
                    </span>
                    <p><?php echo $participantsCount; ?> personne(s) participent déjà</p>
                </div>
                
                <?php if ($availability === 'disponible'): ?>
                    <button class="btn-primary full-width" id="participateBtn">
                        <i class="fas fa-hand-holding-heart"></i>
                        Participer au projet
                    </button>
                    <button class="btn-secondary full-width" id="saveProjectBtn">
                        <i class="fas fa-heart"></i>
                        Sauvegarder le projet
                    </button>
                <?php elseif ($availability === 'complet'): ?>
                    <button class="btn-secondary full-width" disabled>
                        <i class="fas fa-users"></i>
                        Projet complet
                    </button>
                    <p class="info-text">Ce projet a atteint son nombre maximum de participants.</p>
                <?php else: ?>
                    <button class="btn-secondary full-width" disabled>
                        <i class="fas fa-flag-checkered"></i>
                        Projet terminé
                    </button>
                    <p class="info-text">Ce projet est déjà terminé.</p>
                <?php endif; ?>
            </div>

            <div class="info-card">
                <h4>À propos de l'association</h4>
                <div class="association-info">
                    <div class="association-avatar">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="association-details">
                        <h5><?php echo $projectAssociation; ?></h5>
                        <p>Organisation à but non lucratif</p>
                    </div>
                </div>
                <button class="btn-outline full-width" onclick="location.href='association-profile.php?id=<?php echo $project['association']; ?>'">
                    <i class="fas fa-user"></i>
                    Voir le profil
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="taskModal">
  <div class="modal-content task-modal-content">
    <button class="close-btn" id="closeTaskModal">✕</button>
    <h3>Choisissez vos tâches</h3>
    <p class="modal-subtitle">Sélectionnez les tâches que vous souhaitez accomplir dans ce projet</p>
    
    <div class="tasks-list" id="tasksList">
        <div class="task-item">
            <input type="checkbox" id="task1" class="task-checkbox">
            <label for="task1" class="task-label">
                <span class="task-title">Distribution de nourriture</span>
                <span class="task-description">Aider à la distribution de repas aux personnes dans le besoin</span>
            </label>
        </div>
        <div class="task-item">
            <input type="checkbox" id="task2" class="task-checkbox">
            <label for="task2" class="task-label">
                <span class="task-title">Animation d'ateliers</span>
                <span class="task-description">Participer à l'animation des activités éducatives</span>
            </label>
        </div>
        <div class="task-item">
            <input type="checkbox" id="task3" class="task-checkbox">
            <label for="task3" class="task-label">
                <span class="task-title">Logistique</span>
                <span class="task-description">Aide à l'organisation et la préparation des événements</span>
            </label>
        </div>
    </div>
    
    <div class="selected-tasks-summary">
      <h4>Tâches sélectionnées :</h4>
      <div class="selected-tasks" id="selectedTasks">
        <p class="no-tasks">Aucune tâche sélectionnée</p>
      </div>
    </div>
    
    <div class="task-modal-actions">
      <button class="btn-secondary" id="cancelTaskSelection">Annuler</button>
      <button class="btn-primary" id="confirmParticipation" disabled>
        Confirmer la participation
        <i class="fas fa-check"></i>
      </button>
    </div>
  </div>
</div>

<div class="modal" id="successModal">
  <div class="modal-content success-modal-content">
    <div class="success-icon">
      <i class="fas fa-check-circle"></i>
    </div>
    <h3>Participation confirmée !</h3>
    <p>Votre participation au projet "<span id="successProjectTitle"><?php echo $projectTitle; ?></span>" a été enregistrée avec succès.</p>
    <div class="success-details">
        <div class="success-item">
            <i class="fas fa-project-diagram"></i>
            <span id="successProjectName"><?php echo $projectTitle; ?></span>
        </div>
        <div class="success-item">
            <i class="fas fa-building"></i>
            <span id="successAssociation"><?php echo $projectAssociation; ?></span>
        </div>
        <div class="success-item">
            <i class="fas fa-map-marker-alt"></i>
            <span id="successLocation"><?php echo $projectLocation; ?></span>
        </div>
    </div>
    <div class="success-actions">
        <button class="btn-secondary" onclick="location.href='projects.php'">
            <i class="fas fa-arrow-left"></i>
            Retour aux projets
        </button>
        <button class="btn-primary" id="closeSuccessModal">
            Continuer
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
  </div>
</div>

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
                    <li><a href="projects.php">Projets</a></li>
                    <li><a href="#contact">Contact</a></li>
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

<script src="../js/details.js"></script>
</body>
</html>