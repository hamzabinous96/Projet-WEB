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
        // Mettre à jour chaque tâche sélectionnée avec l'ID utilisateur
        foreach ($selectedTasks as $taskId) {
            $projectController->updateTache($taskId, 
                $projectController->getTacheById($taskId)['nom_tache'],
                $projectController->getTacheById($taskId)['description'],
                'prise', // Statut changé à "prise"
                $currentUserId // Assigner l'utilisateur
            );
        }
        
        // Recharger les tâches pour afficher les nouveaux statuts
        $projectTasks = $projectController->getTasksByProject($projectId);
        
        $message = "Votre participation a été enregistrée avec succès! Les tâches sélectionnées sont maintenant marquées comme 'prises'.";
        $message_type = "success";
    }
}

// Récupérer le nom de l'association
$associationName = '';
$associations = $projectController->getAssociations();
foreach($associations as $assoc) {
    if ($assoc['id'] == $project['association']) {
        $associationName = $assoc['first_name'] . ' ' . $assoc['last_name'];
        break;
    }
}

$projectTitle = htmlspecialchars($project['titre']);
$projectCategory = htmlspecialchars($project['categorie']);
$projectAssociation = htmlspecialchars($associationName);
$projectLocation = htmlspecialchars($project['lieu'] ?? 'Non spécifié');
$projectDescription = htmlspecialchars($project['description'] ?? 'Aucune description disponible');
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
  <style>
    /* Styles pour les alertes */
        .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert i {
        font-size: 1.2rem;
    }

    /* Styles pour les badges de statut */
    .available { background: #e8f5e8; color: #2e7d32; }
    .full { background: #ffebee; color: #c62828; }
    .ended { background: #f5f5f5; color: #616161; }

    .category-badge, .availability-badge, .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }

    /* Styles pour les tâches */
    .task-taken {
        opacity: 0.6;
        background: #f8f9fa;
    }

    .task-status-indicator {
        font-size: 0.8rem;
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 12px;
        display: inline-block;
        margin-top: 5px;
    }

    .task-status-indicator.en_attente { background: #fff3cd; color: #856404; }
    .task-status-indicator.prise { background: #d4edda; color: #155724; }

    /* Styles pour la modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .task-modal-content {
        background: white;
        padding: 30px;
        border-radius: 12px;
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
    }

    .close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #666;
    }

    .task-item {
        display: flex;
        align-items: flex-start;
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .task-item:hover {
        border-color: #93C572;
    }

    .task-checkbox {
        margin-right: 15px;
        margin-top: 5px;
    }

    .task-label {
        flex: 1;
        cursor: pointer;
    }

    .task-title {
        font-weight: 600;
        display: block;
        margin-bottom: 5px;
    }

    .task-description {
        color: #666;
        font-size: 0.9rem;
        display: block;
        margin-bottom: 5px;
    }

    .selected-tasks-summary {
        margin: 20px 0;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .selected-task-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px;
        background: white;
        border-radius: 6px;
        margin: 5px 0;
    }

    .task-modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    /* STYLES DES BOUTONS - Style pistache comme demandé */
    .btn-primary, .btn-secondary, .btn-outline, .btn-login, .btn-register {
        padding: 12px 24px;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        text-decoration: none;
        text-align: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    /* Style pour btn-primary (bouton principal) */
    .btn-primary {
        padding: 12px 30px;
        background: linear-gradient(135deg, #93C572, #7AA959);
        border: none;
        color: white;
        box-shadow: 0 2px 4px rgba(147, 197, 114, 0.3);
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, 
            transparent, 
            rgba(255, 255, 255, 0.2), 
            transparent);
        transition: left 0.6s ease;
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 20px 60px rgba(147, 197, 114, 0.15);
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    .btn-primary i {
        transition: transform 0.3s ease;
    }

    .btn-primary:hover i {
        transform: translateX(3px);
    }

    /* Style pour btn-secondary */
    .btn-secondary {
        padding: 12px 30px;
        background: transparent;
        border: 2px solid #93C572;
        color: #93C572;
    }

    .btn-secondary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, 
            transparent, 
            rgba(147, 197, 114, 0.1), 
            transparent);
        transition: left 0.6s ease;
    }

    .btn-secondary:hover::before {
        left: 100%;
    }

    .btn-secondary:hover:not(:disabled) {
        background: #93C572;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 20px 60px rgba(147, 197, 114, 0.15);
    }

    .btn-secondary.saved {
        background: #93C572;
        color: white;
        border-color: #93C572;
    }

    .btn-secondary.saved:hover {
        background: #7AA959;
        border-color: #7AA959;
    }

    /* Style pour btn-outline (identique au style du bouton Connexion) */
    .btn-outline, .btn-login, .btn-register {
        padding: 10px 20px;
        background: transparent;
        border: 2px solid #93C572;
        color: #93C572;
    }

    .btn-outline::before, .btn-login::before, .btn-register::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, 
            transparent, 
            rgba(147, 197, 114, 0.1), 
            transparent);
        transition: left 0.6s ease;
    }

    .btn-outline:hover::before, .btn-login:hover::before, .btn-register:hover::before {
        left: 100%;
    }

    .btn-outline:hover, .btn-login:hover, .btn-register:hover {
        background: #93C572;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 20px 60px rgba(147, 197, 114, 0.15);
    }

    .btn-outline:active, .btn-login:active, .btn-register:active {
        transform: translateY(0);
    }

    .btn-outline i, .btn-login i, .btn-register i {
        transition: transform 0.3s ease;
    }

    .btn-outline:hover i, .btn-login:hover i, .btn-register:hover i {
        transform: translateX(3px);
    }

    /* États désactivés */
    .btn-primary:disabled, .btn-secondary:disabled, .btn-outline:disabled {
        background: #6c757d;
        border-color: #6c757d;
        color: white;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-outline:disabled {
        background: transparent;
        color: #6c757d;
    }

    .btn-primary:disabled:hover, .btn-secondary:disabled:hover, .btn-outline:disabled:hover {
        transform: none;
        box-shadow: none;
    }

    .btn-primary:disabled:hover::before, 
    .btn-secondary:disabled:hover::before, 
    .btn-outline:disabled:hover::before {
        left: -100%;
    }

    .full-width {
        width: 100%;
        justify-content: center;
    }

    .info-text {
        font-size: 0.9rem;
        color: #666;
        text-align: center;
        margin-top: 10px;
    }
  </style>
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
                        <?php 
                        $taskStatus = $task['status'] ?? 'en_attente';
                        $isTaken = $taskStatus === 'prise';
                        $assigneeName = '';
                        
                        // Récupérer le nom de l'assigné si la tâche est prise
                        if ($isTaken && !empty($task['assignee'])) {
                            $users = $projectController->getUsers();
                            foreach($users as $user) {
                                if ($user['id'] == $task['assignee']) {
                                    $assigneeName = $user['first_name'] . ' ' . $user['last_name'];
                                    break;
                                }
                            }
                        }
                        ?>
                        <div class="task-preview-item <?php echo $taskStatus; ?>">
                            <i class="fas fa-tasks"></i>
                            <div class="task-preview-content">
                                <h4><?php echo htmlspecialchars($task['nom_tache']); ?></h4>
                                <p><?php echo htmlspecialchars($task['description'] ?? 'Aucune description'); ?></p>
                                <span class="task-status <?php echo $taskStatus; ?>">
                                    <?php 
                                    if ($taskStatus === 'prise') {
                                        echo '✅ Tâche prise par ' . htmlspecialchars($assigneeName);
                                    } elseif ($taskStatus === 'en_attente') {
                                        echo '⏳ En attente de participation';
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
                <button class="btn-outline full-width">
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