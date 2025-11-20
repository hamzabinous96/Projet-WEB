<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

// Démarrer la session pour vérifier la connexion
session_start();

$projectController = new ProjectController();

$projectId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$project = $projectController->getProjectById($projectId);

if (!$project) {
    header('HTTP/1.0 404 Not Found');
    exit('Projet non trouvé');
}

// VÉRIFICATION DE LA CONNEXION
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$currentUserId = $isLoggedIn ? $_SESSION['user_id'] : null;
$currentUserName = $isLoggedIn ? ($_SESSION['user_name'] ?? 'Utilisateur') : '';

$participantsCount = $projectController->getParticipantsCount($projectId);
$projectTasks = $projectController->getTasksByProject($projectId);

// Traitement de la confirmation de participation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_participation'])) {
    // Vérifier que l'utilisateur est connecté
    if (!$isLoggedIn) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
    
    $selectedTasks = $_POST['selected_tasks'] ?? [];
    
    if (!empty($selectedTasks)) {
        // Mettre à jour le statut de chaque tâche sélectionnée avec l'ID utilisateur
        foreach ($selectedTasks as $taskId) {
            $projectController->updateTaskStatus($taskId, 'prise', $currentUserId);
        }
        
        // Recharger les tâches pour afficher les nouveaux statuts
        $projectTasks = $projectController->getTasksByProject($projectId);
        
        $message = "Votre participation a été enregistrée avec succès! Les tâches sélectionnées sont maintenant marquées comme 'prises'.";
        $message_type = "success";
    }
}

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
            <?php if ($isLoggedIn): ?>
                <span class="welcome-text">Bonjour, <?php echo htmlspecialchars($currentUserName); ?></span>
                <button class="btn-login" onclick="location.href='logout.php'">Déconnexion</button>
            <?php else: ?>
                <button class="btn-login" onclick="location.href='login.php'">Connexion</button>
                <button class="btn-primary" onclick="location.href='register.php'">S'inscrire</button>
            <?php endif; ?>
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
    
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fas fa-check-circle"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

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

            <!-- Section Tâches du Projet -->
            <?php if (!empty($projectTasks)): ?>
            <section class="project-section">
                <h2>Tâches du Projet</h2>
                <div class="tasks-preview">
                    <?php foreach($projectTasks as $index => $task): ?>
                        <?php $taskStatus = $task['status'] ?? 'en_attente'; ?>
                        <div class="task-preview-item <?php echo $taskStatus; ?>">
                            <i class="fas fa-tasks"></i>
                            <div class="task-preview-content">
                                <h4><?php echo htmlspecialchars($task['nom_tache']); ?></h4>
                                <p><?php echo htmlspecialchars($task['description'] ?? 'Aucune description'); ?></p>
                                <span class="task-status <?php echo $taskStatus; ?>">
                                    <?php 
                                    if ($taskStatus === 'prise') {
                                        echo '✅ Tâche prise en charge';
                                    } elseif ($taskStatus === 'en_attente') {
                                        echo '⏳ En attente';
                                    } else {
                                        echo htmlspecialchars($taskStatus);
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
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
                    <?php if ($isLoggedIn): ?>
                        <!-- Utilisateur connecté -->
                        <button class="btn-primary full-width" id="participateBtn">
                            <i class="fas fa-hand-holding-heart"></i>
                            Participer au projet
                        </button>
                        <button class="btn-secondary full-width" id="saveProjectBtn">
                            <i class="fas fa-heart"></i>
                            Sauvegarder le projet
                        </button>
                    <?php else: ?>
                        <!-- Utilisateur NON connecté -->
                        <button class="btn-primary full-width" onclick="location.href='login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>'">
                            <i class="fas fa-sign-in-alt"></i>
                            Se connecter pour participer
                        </button>
                        <p class="info-text">Vous devez être connecté pour participer à ce projet.</p>
                    <?php endif; ?>
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

<!-- Modal des tâches avec formulaire -->
<div class="modal" id="taskModal">
  <div class="modal-content task-modal-content">
    <button class="close-btn" id="closeTaskModal">✕</button>
    <h3>Choisissez vos tâches</h3>
    <p class="modal-subtitle">Sélectionnez les tâches que vous souhaitez accomplir dans ce projet</p>
    
    <form method="POST" action="" id="participationForm">
        <div class="tasks-list" id="tasksList">
            <?php if (!empty($projectTasks)): ?>
                <?php foreach($projectTasks as $index => $task): ?>
                    <?php 
                    $taskStatus = $task['status'] ?? 'en_attente';
                    $isTaken = $taskStatus === 'prise';
                    ?>
                    <div class="task-item <?php echo $isTaken ? 'task-taken' : ''; ?>">
                        <input type="checkbox" 
                               id="task<?php echo $task['id_tache']; ?>" 
                               class="task-checkbox" 
                               name="selected_tasks[]"
                               value="<?php echo $task['id_tache']; ?>"
                               <?php echo $isTaken ? 'disabled' : ''; ?>
                               data-task-id="<?php echo $task['id_tache']; ?>">
                        <label for="task<?php echo $task['id_tache']; ?>" class="task-label">
                            <span class="task-title">
                                <?php echo htmlspecialchars($task['nom_tache']); ?>
                            </span>
                            <span class="task-description">
                                <?php echo htmlspecialchars($task['description'] ?? 'Aucune description disponible'); ?>
                            </span>
                            <span class="task-status-indicator <?php echo $taskStatus; ?>">
                                <?php 
                                if ($isTaken) {
                                    echo '❌ Déjà prise';
                                } else {
                                    echo '✅ Disponible';
                                }
                                ?>
                            </span>
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-tasks-message">
                    <i class="fas fa-tasks"></i>
                    <p>Aucune tâche disponible pour ce projet</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="selected-tasks-summary">
            <h4>Tâches sélectionnées :</h4>
            <div class="selected-tasks" id="selectedTasks">
                <p class="no-tasks">Aucune tâche sélectionnée</p>
            </div>
        </div>
        
        <div class="task-modal-actions">
            <button type="button" class="btn-secondary" id="cancelTaskSelection">Annuler</button>
            <button type="submit" name="confirm_participation" class="btn-primary" id="confirmParticipation" disabled>
                Confirmer la participation
                <i class="fas fa-check"></i>
            </button>
        </div>
    </form>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const participateBtn = document.getElementById('participateBtn');
    const taskModal = document.getElementById('taskModal');
    const closeTaskModal = document.getElementById('closeTaskModal');
    const cancelTaskSelection = document.getElementById('cancelTaskSelection');
    const confirmParticipation = document.getElementById('confirmParticipation');
    const taskCheckboxes = document.querySelectorAll('.task-checkbox:not(:disabled)');
    const selectedTasksContainer = document.getElementById('selectedTasks');

    let selectedTasks = [];

    // Ouvrir la modal des tâches
    if (participateBtn) {
        participateBtn.addEventListener('click', function() {
            taskModal.style.display = 'flex';
            selectedTasks = [];
            updateSelectedTasksUI();
        });
    }

    // Fermer la modal des tâches
    closeTaskModal.addEventListener('click', closeTaskModalFunc);
    cancelTaskSelection.addEventListener('click', closeTaskModalFunc);

    function closeTaskModalFunc() {
        taskModal.style.display = 'none';
        // Réinitialiser les sélections
        taskCheckboxes.forEach(checkbox => {
            if (!checkbox.disabled) {
                checkbox.checked = false;
            }
        });
        selectedTasks = [];
        updateSelectedTasksUI();
    }

    // Gérer la sélection des tâches
    taskCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.disabled) return;
            
            const taskId = this.value;
            const taskLabel = this.parentElement.querySelector('.task-title').textContent;
            
            if (this.checked) {
                selectedTasks.push({
                    id: taskId,
                    name: taskLabel
                });
            } else {
                selectedTasks = selectedTasks.filter(task => task.id !== taskId);
            }
            
            updateSelectedTasksUI();
            updateConfirmButton();
        });
    });

    // Mettre à jour l'affichage des tâches sélectionnées
    function updateSelectedTasksUI() {
        if (selectedTasks.length === 0) {
            selectedTasksContainer.innerHTML = '<p class="no-tasks">Aucune tâche sélectionnée</p>';
        } else {
            selectedTasksContainer.innerHTML = selectedTasks.map(task => 
                `<div class="selected-task-item">
                    <i class="fas fa-check-circle"></i>
                    <span>${task.name}</span>
                </div>`
            ).join('');
        }
    }

    // Activer/désactiver le bouton de confirmation
    function updateConfirmButton() {
        confirmParticipation.disabled = selectedTasks.length === 0;
    }

    // Fermer les modales en cliquant à l'extérieur
    window.addEventListener('click', function(event) {
        if (event.target === taskModal) {
            closeTaskModalFunc();
        }
    });
});
</script>
</body>
</html>