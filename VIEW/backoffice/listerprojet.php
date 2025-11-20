<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

$projectController = new ProjectController();

$message = "";
$message_type = "";

$associations = $projectController->getAssociations();
$admins = $projectController->getAdmins();
$categories = $projectController->getCategories();

// Initialiser ou récupérer le compteur de projets supprimés depuis la session
session_start();
if (!isset($_SESSION['projets_supprimes'])) {
    $_SESSION['projets_supprimes'] = 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_projet'])) {
    // Récupération et nettoyage des données
    $titre = trim($_POST['titre'] ?? '');
    $association = $_POST['association'] ?? null;
    $lieu = trim($_POST['lieu'] ?? '');
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';
    $disponibilite = $_POST['disponibilite'] ?? '';
    $descriptionp = trim($_POST['descriptionp'] ?? '');
    $categorie = $_POST['categorie'] ?? '';
    $created_by = $_POST['created_by'] ?? '';
    $taches = $_POST['taches'] ?? [];

    // Validation de tous les champs obligatoires
    $errors = [];

    if (empty($titre)) {
        $errors[] = "Le titre est obligatoire";
    }

    if (empty($association)) {
        $errors[] = "L'association est obligatoire";
    }

    if (empty($lieu)) {
        $errors[] = "Le lieu est obligatoire";
    }

    if (empty($date_debut)) {
        $errors[] = "La date de début est obligatoire";
    }

    if (empty($date_fin)) {
        $errors[] = "La date de fin est obligatoire";
    }

    if (empty($disponibilite)) {
        $errors[] = "La disponibilité est obligatoire";
    }

    if (empty($descriptionp)) {
        $errors[] = "La description est obligatoire";
    }

    if (empty($categorie)) {
        $errors[] = "La catégorie est obligatoire";
    }

    if (empty($created_by)) {
        $errors[] = "Le créateur est obligatoire";
    }

    // Validation des dates
    if (!empty($date_debut) && !empty($date_fin)) {
        if (strtotime($date_debut) > strtotime($date_fin)) {
            $errors[] = "La date de début ne peut pas être après la date de fin";
        }
    }

    // Validation des tâches - au moins une tâche requise
    $valid_taches = [];
    if (!empty($taches)) {
        foreach ($taches as $index => $tache) {
            $tache_nom = trim($tache['nom'] ?? '');
            $tache_description = trim($tache['description'] ?? '');
            $tache_assignee = $tache['assignee'] ?? null;
            
            if (!empty($tache_nom)) {
                $valid_taches[] = [
                    'nom' => $tache_nom,
                    'description' => $tache_description,
                    'assignee' => !empty($tache_assignee) ? $tache_assignee : null
                ];
            }
        }
    }

    if (empty($valid_taches)) {
        $errors[] = "Au moins une tâche est obligatoire";
    }

    if (empty($errors)) {
        $result = $projectController->addProject(
            $titre, $association, $lieu, $date_debut, $date_fin, 
            $disponibilite, $descriptionp, $categorie, $created_by
        );
        
        if ($result === true) {
            $lastProjectId = $projectController->getLastInsertId();
            
            if ($lastProjectId && !empty($valid_taches)) {
                foreach ($valid_taches as $tache) {
                    $projectController->addTache(
                        $tache['nom'],
                        $tache['description'],
                        'en_attente',
                        $lastProjectId,
                        $tache['assignee'],
                        $created_by
                    );
                }
            }
            
            $message = "Projet et tâches ajoutés avec succès!";
            $message_type = "success";
            echo "<script>window.location.href = window.location.href.split('?')[0];</script>";
            exit;
        } else {
            $message = "Erreur lors de l'ajout du projet: " . $result;
            $message_type = "error";
        }
    } else {
        $message = "Erreurs de validation:<br>" . implode("<br>", $errors);
        $message_type = "error";
    }
}

if (isset($_GET['supprimer']) && isset($_GET['confirm']) && $_GET['confirm'] === 'oui') {
    $id = $_GET['supprimer'];
    
    if ($projectController->deleteProject($id)) {
        // Incrémenter le compteur de projets supprimés
        $_SESSION['projets_supprimes']++;
        
        $message = "Projet supprimé avec succès!";
        $message_type = "success";
        header("Location: " . str_replace('?supprimer=' . $id . '&confirm=oui', '', $_SERVER['REQUEST_URI']));
        exit;
    } else {
        $message = "Erreur lors de la suppression du projet";
        $message_type = "error";
    }
}

$projects = $projectController->getAllProjects();

$projetsCrees = $projectController->getProjectsCount();
$projetsDisponibles = $projectController->getAvailableProjectsCount();
$participationTotale = $projectController->getTotalParticipants();

// Utiliser le compteur de la session pour les projets supprimés
$projetsSupprimes = $_SESSION['projets_supprimes'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Panel - Gestion des Projets</title>
  <link rel="stylesheet" href="../style/listerprojet.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #E8F4E0 0%, transparent 50%, #FFFFFF 100%);
      min-height: 100vh;
    }

    .error-field {
      border: 2px solid #dc3545 !important;
      background-color: #fff5f5;
    }
    
    .error-message {
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: 0.25rem;
      display: block;
    }
    
    .required-field::after {
      content: " *";
      color: #dc3545;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .no-taches-error {
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: 0.5rem;
      display: none;
    }

    .action-buttons {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      justify-content: center;
    }

    .btn-info {
      background: linear-gradient(135deg, #7AA959, #93C572);
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 12px;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(122, 169, 89, 0.3);
    }

    .btn-info:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(122, 169, 89, 0.4);
    }

    /* Styles modernes pour le modal de détails */
    .details-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.6);
      backdrop-filter: blur(5px);
      z-index: 1000;
      justify-content: center;
      align-items: center;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .details-modal.active {
      opacity: 1;
    }

    .details-content {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      padding: 0;
      border-radius: 20px;
      max-width: 900px;
      width: 95%;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 60px rgba(0,0,0,0.15);
      border: 1px solid #E8F4E0;
      transform: scale(0.9) translateY(20px);
      opacity: 0;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      position: relative;
    }

    .details-modal.active .details-content {
      transform: scale(1) translateY(0);
      opacity: 1;
    }

    .details-header {
      background: linear-gradient(135deg, #7AA959, #93C572);
      padding: 25px 30px;
      border-radius: 20px 20px 0 0;
      color: white;
      position: relative;
      overflow: hidden;
    }

    .details-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
      background-size: cover;
    }

    .details-header h2 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: 700;
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .details-header h2 i {
      font-size: 1.3rem;
    }

    .close-details {
      background: rgba(255,255,255,0.2);
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      font-size: 20px;
      cursor: pointer;
      color: white;
      transition: all 0.3s ease;
      position: absolute;
      top: 20px;
      right: 20px;
      z-index: 2;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .close-details:hover {
      background: rgba(255,255,255,0.3);
      transform: rotate(90deg);
    }

    .details-body {
      padding: 30px;
      background: white;
    }

    .details-section {
      margin-bottom: 30px;
      animation: slideInUp 0.6s ease;
      animation-fill-mode: both;
    }

    .details-section:nth-child(1) { animation-delay: 0.1s; }
    .details-section:nth-child(2) { animation-delay: 0.2s; }
    .details-section:nth-child(3) { animation-delay: 0.3s; }

    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .details-section h3 {
      color: #7AA959;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 2px solid #E8F4E0;
      font-size: 1.2rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .details-section h3 i {
      color: #93C572;
    }

    .project-info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }

    .info-card {
      background: white;
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(122, 169, 89, 0.08);
      border: 1px solid #E8F4E0;
      border-left: 4px solid #93C572;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .info-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(122, 169, 89, 0.15);
    }

    .info-label {
      font-weight: 600;
      color: #7AA959;
      font-size: 0.9rem;
      margin-bottom: 5px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .info-label i {
      color: #93C572;
      font-size: 0.8rem;
    }

    .info-value {
      color: #2d3748;
      font-size: 1rem;
      font-weight: 500;
    }

    .description-card {
      background: white;
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(122, 169, 89, 0.08);
      border: 1px solid #E8F4E0;
      border-left: 4px solid #48bb78;
      margin-top: 15px;
    }

    .taches-grid, .participants-grid {
      display: grid;
      gap: 15px;
    }

    .tache-card, .participant-card {
      background: white;
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(122, 169, 89, 0.08);
      border: 1px solid #E8F4E0;
      border-left: 4px solid #93C572;
      transition: transform 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .tache-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: linear-gradient(135deg, #7AA959, #93C572);
    }

    .participant-card {
      border-left-color: #7AA959;
    }

    .tache-card:hover, .participant-card:hover {
      transform: translateX(5px);
      box-shadow: 0 6px 25px rgba(122, 169, 89, 0.15);
    }

    .tache-header, .participant-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 12px;
    }

    .tache-title {
      font-weight: 600;
      color: #7AA959;
      font-size: 1.1rem;
    }

    /* Styles modernes pour les statuts des tâches */
    .tache-status {
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }

    .status-en_attente { 
      background: linear-gradient(135deg, #FFF3CD, #FFEAA7);
      color: #856404;
      border: 1px solid #FFEAA7;
    }
    
    .status-en_attente:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(255, 234, 167, 0.3);
    }

    .status-en_cours { 
      background: linear-gradient(135deg, #CCE7FF, #A5D8FF);
      color: #004085;
      border: 1px solid #A5D8FF;
    }
    
    .status-en_cours:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(165, 216, 255, 0.3);
    }

    .status-termine { 
      background: linear-gradient(135deg, #D4EDDA, #C3E6CB);
      color: #155724;
      border: 1px solid #C3E6CB;
    }
    
    .status-termine:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(195, 230, 203, 0.3);
    }

    .status-disponible {
      background: linear-gradient(135deg, #E8F4E0, #D4EDC9);
      color: #7AA959;
      border: 1px solid #D4EDC9;
    }
    
    .status-disponible:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(212, 237, 201, 0.3);
    }

    .status-pris {
      background: linear-gradient(135deg, #E2E3E5, #D1D3D8);
      color: #383d41;
      border: 1px solid #D1D3D8;
    }
    
    .status-pris:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(209, 211, 216, 0.3);
    }

    .tache-description {
      color: #718096;
      line-height: 1.5;
      margin-bottom: 10px;
    }

    .tache-assignee {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #7AA959;
      font-size: 0.9rem;
      font-weight: 500;
    }

    .tache-assignee i {
      color: #93C572;
    }

    .tache-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid #E8F4E0;
    }

    .tache-date {
      color: #a0aec0;
      font-size: 0.8rem;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .no-data {
      text-align: center;
      color: #a0aec0;
      font-style: italic;
      padding: 40px 20px;
      background: #f7fafc;
      border-radius: 16px;
      border: 2px dashed #E8F4E0;
    }

    .no-data i {
      font-size: 2rem;
      margin-bottom: 10px;
      color: #E8F4E0;
    }

    .badge {
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .badge.disponible {
      background: linear-gradient(135deg, #E8F4E0, #D4EDC9);
      color: #7AA959;
      border: 1px solid #D4EDC9;
    }

    .badge.complet {
      background: linear-gradient(135deg, #F8D7DA, #F5C6CB);
      color: #721c24;
      border: 1px solid #F5C6CB;
    }

    .badge.termine {
      background: linear-gradient(135deg, #E2E3E5, #D6D8DB);
      color: #383d41;
      border: 1px solid #D6D8DB;
    }

    .loading-spinner {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid #93C572;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-right: 10px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Alert styles */
    .alert {
      padding: 15px 20px;
      border-radius: 12px;
      margin-bottom: 25px;
      font-weight: 500;
      display: flex;
      align-items: center;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .alert-success {
      background-color: #E8F4E0;
      color: #7AA959;
      border-left: 4px solid #93C572;
    }

    .alert-error {
      background-color: rgba(220, 53, 69, 0.1);
      color: #dc3545;
      border-left: 4px solid #dc3545;
    }

    .alert i {
      margin-right: 12px;
      font-size: 1.2rem;
    }

    @media (max-width: 768px) {
      .project-info-grid {
        grid-template-columns: 1fr;
      }
      
      .action-buttons {
        flex-direction: column;
      }
      
      .action-buttons a, .action-buttons button {
        width: 100%;
        text-align: center;
      }
      
      .details-content {
        width: 98%;
        margin: 10px;
      }
      
      .details-body {
        padding: 20px;
      }
      
      .tache-header {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
      }
      
      .tache-meta {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>
  <div class="admin-wrapper">
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>Admin Panel</h2>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li><a href="backoffice.html"><i class="fas fa-chart-line"></i> Dashboard</a></li>
          <li><a href="listerprojet.php" class="active"><i class="fas fa-project-diagram"></i> Projets</a></li>
          <li><a href="#"><i class="fas fa-users"></i> Utilisateurs</a></li>
          <li><a href="#"><i class="fas fa-cog"></i> Paramètres</a></li>
        </ul>
      </nav>
    </aside>

    <div class="main-content">
      <header class="topbar">
        <div class="header-inner">
          <h1 class="title">Gestion des Projets</h1>
          <button class="btn add-btn" id="addProjectBtn"><i class="fas fa-plus"></i> Ajouter un projet</button>
        </div>
      </header>

      <section class="stats-overview container">
        <div class="stat-card">
          <h4>Projets créés</h4>
          <p class="stat-number" id="projetsCrees"><?php echo $projetsCrees; ?></p>
        </div>
        <div class="stat-card">
          <h4>Projets supprimés</h4>
          <p class="stat-number" id="projetsSupprimes"><?php echo $projetsSupprimes; ?></p>
        </div>
        <div class="stat-card">
          <h4>Projets disponibles</h4>
          <p class="stat-number" id="projetsPublies"><?php echo $projetsDisponibles; ?></p>
        </div>
        <div class="stat-card">
          <h4>Participation totale</h4>
          <p class="stat-number" id="participationTotale"><?php echo $participationTotale; ?></p>
        </div>
      </section>

      <main class="container">
        <?php if ($message): ?>
          <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'error'; ?>">
            <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
            <?php echo $message; ?>
          </div>
        <?php endif; ?>

        <?php if (empty($associations)): ?>
          <div class="no-associations">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Aucune association trouvée.</strong> Vous devez d'abord créer des associations dans la base de données avant de pouvoir ajouter des projets.
          </div>
        <?php endif; ?>

        <div class="card projects-card">
          <div class="card-header">
            <h3>Liste des Projets</h3>
          </div>
          <div class="table-wrap">
            <table class="projects-table" role="table" aria-label="Liste des projets">
              <tr class="table-header">
                <th>ID</th>
                <th>Nom du projet</th>
                <th>Description</th>
                <th>Association</th>
                <th>Lieu</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th>Disponibilité</th>
                <th>Participants</th>
                <th>Actions</th>
              </tr>
              <?php
              if (count($projects) > 0) {
                  foreach($projects as $project) {
                      $badgeClass = $project['disponibilite'];
                      $badgeText = ucfirst($project['disponibilite']);
                      $participantsCount = $projectController->getParticipantsCount($project['id_projet']);
                      
                      echo "<tr>";
                      echo "<td>" . htmlspecialchars($project['id_projet']) . "</td>";
                      echo "<td>" . htmlspecialchars($project['titre']) . "</td>";
                      echo "<td>" . htmlspecialchars(substr($project['descriptionp'] ?? '', 0, 50)) . "...</td>";
                      echo "<td>" . htmlspecialchars($project['association_nom'] ?? 'N/A') . "</td>";
                      echo "<td>" . htmlspecialchars($project['lieu'] ?? '') . "</td>";
                      echo "<td>" . htmlspecialchars($project['date_debut'] ?? '') . "</td>";
                      echo "<td>" . htmlspecialchars($project['date_fin'] ?? '') . "</td>";
                      echo "<td class='center-col'><span class='badge $badgeClass'>$badgeText</span></td>";
                      echo "<td class='center-col'>" . $participantsCount . "</td>";
                      echo "<td class='center-col'>";
                      echo "<div class='action-buttons'>";
                      echo "<button class='btn btn-info details-btn' 
                              data-project-id='" . $project['id_projet'] . "'
                              data-project-title='" . htmlspecialchars($project['titre']) . "'
                              data-project-description='" . htmlspecialchars($project['descriptionp'] ?? '') . "'
                              data-project-association='" . htmlspecialchars($project['association_nom'] ?? 'N/A') . "'
                              data-project-lieu='" . htmlspecialchars($project['lieu'] ?? '') . "'
                              data-project-date-debut='" . htmlspecialchars($project['date_debut'] ?? '') . "'
                              data-project-date-fin='" . htmlspecialchars($project['date_fin'] ?? '') . "'
                              data-project-disponibilite='" . htmlspecialchars($project['disponibilite'] ?? '') . "'
                              data-project-categorie='" . htmlspecialchars($project['categorie'] ?? '') . "'>
                              <i class='fas fa-eye'></i> Détails
                            </button>";
                      echo "<a href='updateprojet.php?id=" . $project['id_projet'] . "' class='btn btn-warning'><i class='fas fa-edit'></i> Modifier</a>";
                      echo "<button class='btn btn-danger delete-btn' data-project-id='" . $project['id_projet'] . "' data-project-name='" . htmlspecialchars($project['titre']) . "'><i class='fas fa-trash-alt'></i> Supprimer</button>";
                      echo "</div>";
                      echo "</td>";
                      echo "</tr>";
                  }
              } else {
                  echo "<tr><td colspan='10' class='center-col'>Aucun projet trouvé</td></tr>";
              }
              ?>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Modal d'ajout de projet -->
  <div class="modal-overlay" id="projectModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Ajouter un nouveau projet</h2>
        <button class="close-btn" id="closeModal">&times;</button>
      </div>
      <form method="POST" action="" id="projectForm" novalidate>
        <div class="modal-body">
          <div class="form-group">
            <label for="titre" class="required-field">Titre</label>
            <input type="text" id="titre" name="titre" class="form-control" required
                   value="<?php echo isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : ''; ?>">
            <span class="error-message" id="titre-error"></span>
          </div>
          
          <div class="form-group">
            <label for="association" class="required-field">Association</label>
            <?php if (!empty($associations)): ?>
              <select id="association" name="association" class="form-control" required>
                <option value="">Sélectionner une association</option>
                <?php foreach($associations as $association): ?>
                  <option value="<?php echo $association['id']; ?>"
                    <?php echo (isset($_POST['association']) && $_POST['association'] == $association['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($association['nom'] . ' (' . $association['email'] . ')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            <?php else: ?>
              <div class="no-associations" style="margin: 0;">
                <i class="fas fa-exclamation-triangle"></i>
                Aucune association disponible. Créez d'abord des associations.
              </div>
            <?php endif; ?>
            <span class="error-message" id="association-error"></span>
          </div>
          
          <div class="form-group">
            <label for="lieu" class="required-field">Lieu</label>
            <input type="text" id="lieu" name="lieu" class="form-control" required
                   value="<?php echo isset($_POST['lieu']) ? htmlspecialchars($_POST['lieu']) : ''; ?>">
            <span class="error-message" id="lieu-error"></span>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="date_debut" class="required-field">Date de début</label>
              <input type="date" id="date_debut" name="date_debut" class="form-control" required
                     value="<?php echo isset($_POST['date_debut']) ? htmlspecialchars($_POST['date_debut']) : ''; ?>">
              <span class="error-message" id="date_debut-error"></span>
            </div>
            
            <div class="form-group">
              <label for="date_fin" class="required-field">Date de fin</label>
              <input type="date" id="date_fin" name="date_fin" class="form-control" required
                     value="<?php echo isset($_POST['date_fin']) ? htmlspecialchars($_POST['date_fin']) : ''; ?>">
              <span class="error-message" id="date_fin-error"></span>
            </div>
          </div>
          
          <div class="form-group">
            <label for="disponibilite" class="required-field">Disponibilité</label>
            <select id="disponibilite" name="disponibilite" class="form-control" required>
              <option value="">Sélectionner</option>
              <option value="disponible" <?php echo (isset($_POST['disponibilite']) && $_POST['disponibilite'] == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
              <option value="complet" <?php echo (isset($_POST['disponibilite']) && $_POST['disponibilite'] == 'complet') ? 'selected' : ''; ?>>Complet</option>
              <option value="termine" <?php echo (isset($_POST['disponibilite']) && $_POST['disponibilite'] == 'termine') ? 'selected' : ''; ?>>Terminé</option>
            </select>
            <span class="error-message" id="disponibilite-error"></span>
          </div>
          
          <div class="form-group">
            <label for="descriptionp" class="required-field">Description</label>
            <textarea id="descriptionp" name="descriptionp" class="form-control" required><?php echo isset($_POST['descriptionp']) ? htmlspecialchars($_POST['descriptionp']) : ''; ?></textarea>
            <span class="error-message" id="descriptionp-error"></span>
          </div>
          
          <div class="form-group">
            <label for="categorie" class="required-field">Catégorie</label>
            <select id="categorie" name="categorie" class="form-control" required>
              <option value="">Sélectionner</option>
              <option value="Solidarité" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == 'Solidarité') ? 'selected' : ''; ?>>Solidarité</option>
              <option value="Environement" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == 'Environement') ? 'selected' : ''; ?>>Environement</option>
              <option value="Education" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == 'Education') ? 'selected' : ''; ?>>Education</option>
              <option value="Sante" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == 'Sante') ? 'selected' : ''; ?>>Sante</option>
              <option value="Aide" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == 'Aide') ? 'selected' : ''; ?>>Aide</option>
              <option value="Culture" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == 'Culture') ? 'selected' : ''; ?>>Culture</option>
            </select>
            <span class="error-message" id="categorie-error"></span>
          </div>
          
          <div class="form-group">
            <label for="created_by" class="required-field">Créé par</label>
            <select id="created_by" name="created_by" class="form-control" required>
              <option value="">Sélectionner un administrateur</option>
              <?php foreach($admins as $admin): ?>
                <option value="<?php echo $admin['id']; ?>"
                  <?php echo (isset($_POST['created_by']) && $_POST['created_by'] == $admin['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($admin['nom'] . ' (' . $admin['email'] . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <span class="error-message" id="created_by-error"></span>
          </div>

          <div class="taches-section">
            <h3>Tâches du Projet <span class="required-field">(Au moins une tâche requise)</span></h3>
            <button type="button" class="btn-add-tache" id="addTache">
              <i class="fas fa-plus"></i> Ajouter une tâche
            </button>
            <div class="no-taches-error" id="taches-error">Au moins une tâche est obligatoire</div>
            
            <div id="taches-container">
              <?php if (isset($_POST['taches']) && is_array($_POST['taches'])): ?>
                <?php foreach($_POST['taches'] as $index => $tache): ?>
                  <?php if (!empty(trim($tache['nom']))): ?>
                    <div class="tache-item" data-index="<?php echo $index; ?>">
                      <div class="tache-header">
                        <span class="tache-title">Tâche #<?php echo $index + 1; ?></span>
                        <button type="button" class="btn-remove-tache" onclick="removeTache(this)">
                          <i class="fas fa-times"></i> Supprimer
                        </button>
                      </div>
                      <div class="form-group">
                        <label class="required-field">Nom de la tâche</label>
                        <input type="text" name="taches[<?php echo $index; ?>][nom]" class="form-control tache-nom" 
                               value="<?php echo htmlspecialchars($tache['nom'] ?? ''); ?>" required>
                        <span class="error-message">Le nom de la tâche est obligatoire</span>
                      </div>
                      <div class="form-group">
                        <label>Description</label>
                        <textarea name="taches[<?php echo $index; ?>][description]" class="form-control"><?php echo htmlspecialchars($tache['description'] ?? ''); ?></textarea>
                      </div>
                      <div class="form-group">
                        <label>Assigné à</label>
                        <select name="taches[<?php echo $index; ?>][assignee]" class="form-control">
                          <option value="">Non assigné</option>
                          <?php foreach($associations as $assoc): ?>
                            <option value="<?php echo $assoc['id']; ?>"
                              <?php echo (isset($tache['assignee']) && $tache['assignee'] == $assoc['id']) ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($assoc['nom']); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                  <?php endif; ?>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="cancelBtn">Annuler</button>
          <button type="submit" name="ajouter_projet" class="btn btn-primary" <?php echo empty($associations) ? 'disabled' : ''; ?>>Ajouter le projet</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal de détails du projet - Version moderne et animée -->
  <div class="details-modal" id="detailsModal">
    <div class="details-content">
      <div class="details-header">
        <h2><i class="fas fa-project-diagram"></i> <span id="detailsTitle">Détails du Projet</span></h2>
        <button class="close-details" id="closeDetails">&times;</button>
      </div>
      
      <div class="details-body">
        <div class="details-section">
          <h3><i class="fas fa-info-circle"></i> Informations du Projet</h3>
          <div class="project-info-grid">
            <div class="info-card">
              <div class="info-label"><i class="fas fa-heading"></i> Titre:</div>
              <div class="info-value" id="detailsTitre"></div>
            </div>
            <div class="info-card">
              <div class="info-label"><i class="fas fa-building"></i> Association:</div>
              <div class="info-value" id="detailsAssociation"></div>
            </div>
            <div class="info-card">
              <div class="info-label"><i class="fas fa-map-marker-alt"></i> Lieu:</div>
              <div class="info-value" id="detailsLieu"></div>
            </div>
            <div class="info-card">
              <div class="info-label"><i class="fas fa-tag"></i> Catégorie:</div>
              <div class="info-value" id="detailsCategorie"></div>
            </div>
            <div class="info-card">
              <div class="info-label"><i class="fas fa-calendar-alt"></i> Date de début:</div>
              <div class="info-value" id="detailsDateDebut"></div>
            </div>
            <div class="info-card">
              <div class="info-label"><i class="fas fa-calendar-check"></i> Date de fin:</div>
              <div class="info-value" id="detailsDateFin"></div>
            </div>
            <div class="info-card">
              <div class="info-label"><i class="fas fa-toggle-on"></i> Disponibilité:</div>
              <div class="info-value" id="detailsDisponibilite"></div>
            </div>
          </div>
          <div class="description-card">
            <div class="info-label"><i class="fas fa-align-left"></i> Description:</div>
            <div class="info-value" id="detailsDescription" style="margin-top: 10px; line-height: 1.6;"></div>
          </div>
        </div>

        <div class="details-section">
          <h3><i class="fas fa-tasks"></i> Tâches du Projet</h3>
          <div id="detailsTaches">
            <div class="no-data">
              <i class="fas fa-spinner loading-spinner"></i>
              <div>Chargement des tâches...</div>
            </div>
          </div>
        </div>

        <div class="details-section">
          <h3><i class="fas fa-users"></i> Participants</h3>
          <div id="detailsParticipants">
            <div class="no-data">
              <i class="fas fa-spinner loading-spinner"></i>
              <div>Chargement des participants...</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de confirmation de suppression -->
  <div class="confirmation-modal" id="confirmationModal">
    <div class="confirmation-content">
      <div class="confirmation-header">
        <h3>Confirmation de suppression</h3>
      </div>
      <div class="confirmation-body">
        <p id="confirmationMessage">Êtes-vous sûr de vouloir supprimer ce projet ?</p>
      </div>
      <div class="confirmation-footer">
        <button class="confirmation-btn confirmation-btn-cancel" id="cancelDeleteBtn">Annuler</button>
        <a href="#" class="confirmation-btn confirmation-btn-delete" id="confirmDeleteBtn">Oui, supprimer</a>
      </div>
    </div>
  </div>

  <script>
    let tacheCount = <?php echo isset($_POST['taches']) ? count(array_filter($_POST['taches'], function($t) { return !empty(trim($t['nom'])); })) : 0; ?>;
    
    function validateField(field, errorId) {
      const value = field.value.trim();
      const errorElement = document.getElementById(errorId);
      
      if (!value) {
        field.classList.add('error-field');
        errorElement.textContent = 'Ce champ est obligatoire';
        return false;
      } else {
        field.classList.remove('error-field');
        errorElement.textContent = '';
        return true;
      }
    }
    
    function validateTaches() {
      const tacheItems = document.querySelectorAll('.tache-item');
      const tacheError = document.getElementById('taches-error');
      
      if (tacheItems.length === 0) {
        tacheError.style.display = 'block';
        return false;
      } else {
        tacheError.style.display = 'none';
        
        // Valider chaque tâche
        let allValid = true;
        tacheItems.forEach(item => {
          const nomInput = item.querySelector('.tache-nom');
          if (!validateTacheField(nomInput)) {
            allValid = false;
          }
        });
        
        return allValid;
      }
    }
    
    function validateTacheField(field) {
      const value = field.value.trim();
      
      if (!value) {
        field.classList.add('error-field');
        return false;
      } else {
        field.classList.remove('error-field');
        return true;
      }
    }
    
    function validateForm() {
      let isValid = true;
      
      // Valider tous les champs principaux
      const requiredFields = [
        'titre', 'association', 'lieu', 'date_debut', 'date_fin', 
        'disponibilite', 'descriptionp', 'categorie', 'created_by'
      ];
      
      requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field && !validateField(field, `${fieldName}-error`)) {
          isValid = false;
        }
      });
      
      // Valider les dates
      const dateDebut = document.getElementById('date_debut');
      const dateFin = document.getElementById('date_fin');
      
      if (dateDebut.value && dateFin.value && dateDebut.value > dateFin.value) {
        isValid = false;
        document.getElementById('date_fin-error').textContent = 'La date de fin ne peut pas être avant la date de début';
        dateFin.classList.add('error-field');
      }
      
      // Valider les tâches
      if (!validateTaches()) {
        isValid = false;
      }
      
      return isValid;
    }

    function loadProjectDetails(projectId) {
      // Charger les tâches
      const tachesContainer = document.getElementById('detailsTaches');
      tachesContainer.innerHTML = `
        <div class="no-data">
          <i class="fas fa-spinner loading-spinner"></i>
          <div>Chargement des tâches...</div>
        </div>
      `;

      fetch(`get_taches.php?project_id=${projectId}`)
        .then(response => response.text())
        .then(html => {
          setTimeout(() => {
            tachesContainer.innerHTML = html;
            // Ajouter des animations aux cartes de tâches
            const tacheCards = tachesContainer.querySelectorAll('.tache-card');
            tacheCards.forEach((card, index) => {
              card.style.animationDelay = `${index * 0.1}s`;
            });
          }, 300);
        })
        .catch(error => {
          console.error('Erreur lors du chargement des tâches:', error);
          tachesContainer.innerHTML = `
            <div class="no-data">
              <i class="fas fa-exclamation-triangle"></i>
              <div>Erreur lors du chargement des tâches</div>
            </div>
          `;
        });

      // Charger les participants
      const participantsContainer = document.getElementById('detailsParticipants');
      participantsContainer.innerHTML = `
        <div class="no-data">
          <i class="fas fa-spinner loading-spinner"></i>
          <div>Chargement des participants...</div>
        </div>
      `;

      fetch(`get_participants.php?project_id=${projectId}`)
        .then(response => response.text())
        .then(html => {
          setTimeout(() => {
            participantsContainer.innerHTML = html;
            // Ajouter des animations aux cartes de participants
            const participantCards = participantsContainer.querySelectorAll('.participant-card');
            participantCards.forEach((card, index) => {
              card.style.animationDelay = `${index * 0.1}s`;
            });
          }, 500);
        })
        .catch(error => {
          console.error('Erreur lors du chargement des participants:', error);
          participantsContainer.innerHTML = `
            <div class="no-data">
              <i class="fas fa-exclamation-triangle"></i>
              <div>Erreur lors du chargement des participants</div>
            </div>
          `;
        });
    }
    
    document.getElementById('addTache').addEventListener('click', function() {
      const container = document.getElementById('taches-container');
      const tacheItem = document.createElement('div');
      tacheItem.className = 'tache-item';
      tacheItem.setAttribute('data-index', tacheCount);
      
      tacheItem.innerHTML = `
        <div class="tache-header">
          <span class="tache-title">Tâche #${tacheCount + 1}</span>
          <button type="button" class="btn-remove-tache" onclick="removeTache(this)">
            <i class="fas fa-times"></i> Supprimer
          </button>
        </div>
        <div class="form-group">
          <label class="required-field">Nom de la tâche</label>
          <input type="text" name="taches[${tacheCount}][nom]" class="form-control tache-nom" required>
          <span class="error-message">Le nom de la tâche est obligatoire</span>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="taches[${tacheCount}][description]" class="form-control"></textarea>
        </div>
        <div class="form-group">
          <label>Assigné à</label>
          <select name="taches[${tacheCount}][assignee]" class="form-control">
            <option value="">Non assigné</option>
            <?php foreach($associations as $assoc): ?>
              <option value="<?php echo $assoc['id']; ?>"><?php echo htmlspecialchars($assoc['nom']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      `;
      
      container.appendChild(tacheItem);
      
      // Ajouter la validation en temps réel pour la nouvelle tâche
      const nomInput = tacheItem.querySelector('.tache-nom');
      nomInput.addEventListener('blur', function() {
        validateTacheField(this);
      });
      
      tacheCount++;
      validateTaches(); // Mettre à jour la validation des tâches
    });
    
    function removeTache(button) {
      const tacheItem = button.closest('.tache-item');
      tacheItem.style.transform = 'translateX(-100%)';
      tacheItem.style.opacity = '0';
      setTimeout(() => {
        tacheItem.remove();
        updateTacheNumbers();
        validateTaches();
      }, 300);
    }
    
    function updateTacheNumbers() {
      const tacheItems = document.querySelectorAll('.tache-item');
      tacheItems.forEach((item, index) => {
        const title = item.querySelector('.tache-title');
        title.textContent = `Tâche #${index + 1}`;
        
        const inputs = item.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
          const name = input.getAttribute('name');
          if (name) {
            input.setAttribute('name', name.replace(/taches\[\d+\]/, `taches[${index}]`));
          }
        });
      });
      tacheCount = tacheItems.length;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('projectModal');
      const addBtn = document.getElementById('addProjectBtn');
      const closeBtn = document.getElementById('closeModal');
      const cancelBtn = document.getElementById('cancelBtn');
      
      const detailsModal = document.getElementById('detailsModal');
      const closeDetails = document.getElementById('closeDetails');
      
      const confirmationModal = document.getElementById('confirmationModal');
      const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
      const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
      const confirmationMessage = document.getElementById('confirmationMessage');
      
      // Gestion du modal d'ajout
      addBtn.addEventListener('click', function() {
        modal.style.display = 'flex';
      });
      
      function closeModal() {
        modal.style.display = 'none';
      }
      
      closeBtn.addEventListener('click', closeModal);
      cancelBtn.addEventListener('click', closeModal);
      
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          closeModal();
        }
      });

      // Gestion du modal de détails
      function openDetailsModal(projectId, projectData) {
        document.getElementById('detailsTitle').textContent = projectData.title;
        document.getElementById('detailsTitre').textContent = projectData.title;
        document.getElementById('detailsAssociation').textContent = projectData.association;
        document.getElementById('detailsLieu').textContent = projectData.lieu;
        document.getElementById('detailsCategorie').textContent = projectData.categorie;
        document.getElementById('detailsDateDebut').textContent = projectData.dateDebut;
        document.getElementById('detailsDateFin').textContent = projectData.dateFin;
        
        // Formater la disponibilité avec un badge
        const disponibiliteMap = {
          'disponible': '<span class="badge disponible">Disponible</span>',
          'complet': '<span class="badge complet">Complet</span>',
          'termine': '<span class="badge termine">Terminé</span>'
        };
        document.getElementById('detailsDisponibilite').innerHTML = disponibiliteMap[projectData.disponibilite] || projectData.disponibilite;
        
        document.getElementById('detailsDescription').textContent = projectData.description;
        
        // Animation d'ouverture
        detailsModal.style.display = 'flex';
        setTimeout(() => {
          detailsModal.classList.add('active');
        }, 10);
        
        loadProjectDetails(projectId);
      }
      
      function closeDetailsModal() {
        detailsModal.classList.remove('active');
        setTimeout(() => {
          detailsModal.style.display = 'none';
        }, 300);
      }
      
      closeDetails.addEventListener('click', closeDetailsModal);
      
      detailsModal.addEventListener('click', function(e) {
        if (e.target === detailsModal) {
          closeDetailsModal();
        }
      });

      // Gestion des boutons détails
      document.querySelectorAll('.details-btn').forEach(button => {
        button.addEventListener('click', function() {
          const projectId = this.getAttribute('data-project-id');
          const projectData = {
            title: this.getAttribute('data-project-title'),
            association: this.getAttribute('data-project-association'),
            lieu: this.getAttribute('data-project-lieu'),
            categorie: this.getAttribute('data-project-categorie'),
            dateDebut: this.getAttribute('data-project-date-debut'),
            dateFin: this.getAttribute('data-project-date-fin'),
            disponibilite: this.getAttribute('data-project-disponibilite'),
            description: this.getAttribute('data-project-description')
          };
          openDetailsModal(projectId, projectData);
        });
      });

      // Ajouter la validation en temps réel pour tous les champs
      const requiredFields = [
        'titre', 'association', 'lieu', 'date_debut', 'date_fin', 
        'disponibilite', 'descriptionp', 'categorie', 'created_by'
      ];
      
      requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
          field.addEventListener('blur', function() {
            validateField(this, `${fieldName}-error`);
          });
        }
      });

      // Validation des dates
      const dateDebut = document.getElementById('date_debut');
      const dateFin = document.getElementById('date_fin');
      
      if (dateDebut && dateFin) {
        dateDebut.addEventListener('change', function() {
          validateField(this, 'date_debut-error');
          if (dateFin.value && this.value > dateFin.value) {
            dateFin.value = this.value;
          }
        });
        
        dateFin.addEventListener('change', function() {
          validateField(this, 'date_fin-error');
          if (dateDebut.value && this.value < dateDebut.value) {
            document.getElementById('date_fin-error').textContent = 'La date de fin ne peut pas être avant la date de début';
            this.classList.add('error-field');
          }
        });
      }

      // Validation des tâches existantes
      document.querySelectorAll('.tache-nom').forEach(input => {
        input.addEventListener('blur', function() {
          validateTacheField(this);
        });
      });

      const form = document.getElementById('projectForm');
      form.addEventListener('submit', function(e) {
        <?php if (empty($associations)): ?>
          e.preventDefault();
          alert('Aucune association disponible. Créez d\'abord des associations dans la base de données.');
          return false;
        <?php endif; ?>
        
        if (!validateForm()) {
          e.preventDefault();
          // Faire défiler jusqu'au premier champ en erreur
          const firstError = document.querySelector('.error-field');
          if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
          }
          return false;
        }
      });

      // Gestion de la suppression
      document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          const projectId = this.getAttribute('data-project-id');
          const projectName = this.getAttribute('data-project-name');
          
          confirmationMessage.textContent = `Êtes-vous sûr de vouloir supprimer le projet "${projectName}" ? Cette action est irréversible.`;
          confirmDeleteBtn.href = `?supprimer=${projectId}&confirm=oui`;
          confirmationModal.style.display = 'flex';
        });
      });

      function closeConfirmationModal() {
        confirmationModal.style.display = 'none';
      }

      cancelDeleteBtn.addEventListener('click', closeConfirmationModal);
      
      confirmationModal.addEventListener('click', function(e) {
        if (e.target === confirmationModal) {
          closeConfirmationModal();
        }
      });

      confirmDeleteBtn.addEventListener('click', function() {
        closeConfirmationModal();
      });

      // Initialiser la validation des tâches
      validateTaches();
    });
  </script>
</body>
</html>