<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

$projectController = new ProjectController();

// Variables pour les messages
$message = "";
$message_type = "";

// Récupérer les associations RÉELLES depuis la base de données
$associations = $projectController->getAssociations();
$admins = $projectController->getAdmins();
$categories = $projectController->getCategories();

// Traitement du formulaire d'ajout de projet
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_projet'])) {
    $titre = $_POST['titre'];
    $association = $_POST['association'] ?? null;
    $lieu = $_POST['lieu'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $disponibilite = $_POST['disponibilite'];
    $descriptionp = $_POST['descriptionp'];
    $categorie = $_POST['categorie'];
    $created_by = $_POST['created_by'];

    // Validation que l'association est sélectionnée
    if (empty($association)) {
        $message = "Erreur: Vous devez sélectionner une association";
        $message_type = "error";
    } else {
        // Ajouter le projet via le Controller
        $result = $projectController->addProject(
            $titre, $association, $lieu, $date_debut, $date_fin, 
            $disponibilite, $descriptionp, $categorie, $created_by
        );
        
        if ($result === true) {
            $message = "Projet ajouté avec succès!";
            $message_type = "success";
            
            // Recharger la page pour vider le formulaire et afficher les nouvelles données
            echo "<script>window.location.href = window.location.href.split('?')[0];</script>";
            exit;
        } else {
            $message = "Erreur lors de l'ajout du projet: " . $result;
            $message_type = "error";
        }
    }
}

// Traitement de la suppression de projet avec confirmation
if (isset($_GET['supprimer']) && isset($_GET['confirm']) && $_GET['confirm'] === 'oui') {
    $id = $_GET['supprimer'];
    
    if ($projectController->deleteProject($id)) {
        $message = "Projet supprimé avec succès!";
        $message_type = "success";
        
        // Recharger la page pour afficher les données mises à jour
        header("Location: " . str_replace('?supprimer=' . $id . '&confirm=oui', '', $_SERVER['REQUEST_URI']));
        exit;
    } else {
        $message = "Erreur lors de la suppression du projet";
        $message_type = "error";
    }
}

// Récupérer tous les projets
$projects = $projectController->getAllProjects();

// Calculer les statistiques
$projetsCrees = $projectController->getProjectsCount();
$projetsDisponibles = $projectController->getAvailableProjectsCount();
$participationTotale = $projectController->getTotalParticipants();

// Pour les projets supprimés
$projetsSupprimes = 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Panel - Gestion des Projets</title>
  <link rel="stylesheet" href="../style/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Styles pour le modal de formulaire */
    .modal-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }
    
    .modal-content {
      background-color: white;
      border-radius: 8px;
      width: 90%;
      max-width: 600px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }
    
    .modal-header {
      padding: 20px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .modal-header h2 {
      margin: 0;
      color: #333;
    }
    
    .close-btn {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #666;
    }
    
    .close-btn:hover {
      color: #333;
    }
    
    .modal-body {
      padding: 20px;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
    }
    
    .form-control {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      box-sizing: border-box;
    }
    
    .form-control:focus {
      outline: none;
      border-color: #4a6cf7;
      box-shadow: 0 0 0 2px rgba(74, 108, 247, 0.2);
    }
    
    .form-row {
      display: flex;
      gap: 15px;
    }
    
    .form-row .form-group {
      flex: 1;
    }
    
    textarea.form-control {
      min-height: 100px;
      resize: vertical;
    }
    
    .modal-footer {
      padding: 20px;
      border-top: 1px solid #eee;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
    
    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .btn-primary {
      background-color: #4a6cf7;
      color: white;
    }
    
    .btn-primary:hover {
      background-color: #3a5ce5;
    }
    
    .btn-secondary {
      background-color: #f0f0f0;
      color: #333;
    }
    
    .btn-secondary:hover {
      background-color: #e0e0e0;
    }
    
    .btn-danger {
      background-color: #dc3545;
      color: white;
    }
    
    .btn-danger:hover {
      background-color: #c82333;
    }
    
    .btn-success {
      background-color: #28a745;
      color: white;
    }
    
    .btn-success:hover {
      background-color: #218838;
    }
    
    .btn-warning {
      background-color: #ffc107;
      color: #212529;
    }
    
    .btn-warning:hover {
      background-color: #e0a800;
    }
    
    /* Styles pour la table */
    .table-wrap {
      overflow-x: auto;
    }
    
    .projects-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .projects-table th,
    .projects-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    
    .projects-table th {
      background-color: #f8f9fa;
      font-weight: 600;
      color: #333;
    }
    
    .projects-table tr:hover {
      background-color: #f8f9fa;
    }
    
    .center-col {
      text-align: center;
    }
    
    .badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
    }
    
    .badge.disponible {
      background-color: #e6f4ea;
      color: #137333;
    }
    
    .badge.complet {
      background-color: #fff8e6;
      color: #b86000;
    }
    
    .badge.termine {
      background-color: #f0f0f0;
      color: #666;
    }
    
    /* Styles pour les cartes de statistiques */
    .stats-overview {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .stat-card {
      background-color: white;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      text-align: center;
    }
    
    .stat-card h4 {
      margin: 0 0 10px 0;
      font-size: 14px;
      color: #666;
      font-weight: 600;
    }
    
    .stat-number {
      margin: 0;
      font-size: 32px;
      font-weight: 700;
      color: #333;
    }
    
    /* Styles pour la sidebar et le layout principal */
    .admin-wrapper {
      display: flex;
      min-height: 100vh;
    }
    
    .sidebar {
      width: 250px;
      background-color: #2c3e50;
      color: white;
    }
    
    .sidebar-header {
      padding: 20px;
      border-bottom: 1px solid #34495e;
    }
    
    .sidebar-header h2 {
      margin: 0;
      font-size: 20px;
    }
    
    .sidebar-nav ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    
    .sidebar-nav li {
      border-bottom: 1px solid #34495e;
    }
    
    .sidebar-nav a {
      display: flex;
      align-items: center;
      padding: 15px 20px;
      color: #bdc3c7;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .sidebar-nav a:hover,
    .sidebar-nav a.active {
      background-color: #34495e;
      color: white;
    }
    
    .sidebar-nav i {
      margin-right: 10px;
      width: 20px;
      text-align: center;
    }
    
    .main-content {
      flex: 1;
      background-color: #f5f7fa;
    }
    
    .topbar {
      background-color: white;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .header-inner {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .title {
      margin: 0;
      color: #333;
    }
    
    .add-btn {
      background-color: #4a6cf7;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .add-btn:hover {
      background-color: #3a5ce5;
    }
    
    .container {
      padding: 20px;
    }
    
    .card {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      overflow: hidden;
    }
    
    .card-header {
      padding: 20px;
      border-bottom: 1px solid #eee;
    }
    
    .card-header h3 {
      margin: 0;
      color: #333;
    }

    .alert {
      padding: 12px 15px;
      border-radius: 4px;
      margin-bottom: 20px;
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

    .no-associations {
      background-color: #fff3cd;
      border: 1px solid #ffeaa7;
      color: #856404;
      padding: 15px;
      border-radius: 4px;
      margin-bottom: 20px;
    }

    /* Modal de confirmation */
    .confirmation-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 2000;
      justify-content: center;
      align-items: center;
    }

    .confirmation-content {
      background-color: white;
      border-radius: 8px;
      width: 90%;
      max-width: 400px;
      padding: 20px;
      text-align: center;
    }

    .confirmation-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 20px;
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
          <li><a href="project_connect1.php" class="active"><i class="fas fa-project-diagram"></i> Projets</a></li>
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
                      echo "<button class='btn btn-primary'><i class='fas fa-edit'></i> Modifier</button>";
                      echo "<button class='btn btn-danger delete-btn' data-project-id='" . $project['id_projet'] . "' data-project-name='" . htmlspecialchars($project['titre']) . "'><i class='fas fa-trash-alt'></i> Supprimer</button>";
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

  <!-- Modal pour ajouter un projet -->
  <div class="modal-overlay" id="projectModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Ajouter un nouveau projet</h2>
        <button class="close-btn" id="closeModal">&times;</button>
      </div>
      <form method="POST" action="" id="projectForm">
        <div class="modal-body">
          <div class="form-group">
            <label for="titre">Titre *</label>
            <input type="text" id="titre" name="titre" class="form-control" required>
          </div>
          
          <div class="form-group">
            <label for="association">Association *</label>
            <?php if (!empty($associations)): ?>
              <select id="association" name="association" class="form-control" required>
                <option value="">Sélectionner une association</option>
                <?php foreach($associations as $association): ?>
                  <option value="<?php echo $association['id']; ?>">
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
          </div>
          
          <div class="form-group">
            <label for="lieu">Lieu</label>
            <input type="text" id="lieu" name="lieu" class="form-control">
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="date_debut">Date de début</label>
              <input type="date" id="date_debut" name="date_debut" class="form-control">
            </div>
            
            <div class="form-group">
              <label for="date_fin">Date de fin</label>
              <input type="date" id="date_fin" name="date_fin" class="form-control">
            </div>
          </div>
          
          <div class="form-group">
            <label for="disponibilite">Disponibilité</label>
            <select id="disponibilite" name="disponibilite" class="form-control">
              <option value="">Sélectionner</option>
              <option value="disponible">Disponible</option>
              <option value="complet">Complet</option>
              <option value="termine">Terminé</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="descriptionp">Description</label>
            <textarea id="descriptionp" name="descriptionp" class="form-control"></textarea>
          </div>
          
          <div class="form-group">
            <label for="categorie">Catégorie</label>
            <select id="categorie" name="categorie" class="form-control">
                 <option value="">Sélectionner</option>
              <option value="Solidarité">Solidarité</option>
              <option value="Environement">Environement</option>
              <option value="Education">Education</option>
              <option value="Sante">Sante</option>
              <option value="Aide">Aide</option>
              <option value="Culture">Culture</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="created_by">Créé par *</label>
            <select id="created_by" name="created_by" class="form-control" required>
              <option value="">Sélectionner un administrateur</option>
              <?php foreach($admins as $admin): ?>
                <option value="<?php echo $admin['id']; ?>">
                  <?php echo htmlspecialchars($admin['nom'] . ' (' . $admin['email'] . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="cancelBtn">Annuler</button>
          <button type="submit" name="ajouter_projet" class="btn btn-primary" <?php echo empty($associations) ? 'disabled' : ''; ?>>Ajouter le projet</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal de confirmation de suppression -->
  <div class="confirmation-modal" id="confirmationModal">
    <div class="confirmation-content">
      <h3><i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 24px;"></i> Confirmation de suppression</h3>
      <p id="confirmationMessage">Êtes-vous sûr de vouloir supprimer ce projet ?</p>
      <div class="confirmation-buttons">
        <button class="btn btn-secondary" id="cancelDeleteBtn">Non, annuler</button>
        <a href="#" class="btn btn-danger" id="confirmDeleteBtn">Oui, supprimer</a>
      </div>
    </div>
  </div>

  <script>
    // Gestion du modal d'ajout de projet
    document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('projectModal');
      const addBtn = document.getElementById('addProjectBtn');
      const closeBtn = document.getElementById('closeModal');
      const cancelBtn = document.getElementById('cancelBtn');
      
      // Modal de confirmation
      const confirmationModal = document.getElementById('confirmationModal');
      const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
      const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
      const confirmationMessage = document.getElementById('confirmationMessage');
      
      // Ouvrir le modal d'ajout
      addBtn.addEventListener('click', function() {
        modal.style.display = 'flex';
      });
      
      // Fermer le modal d'ajout
      function closeModal() {
        modal.style.display = 'none';
      }
      
      closeBtn.addEventListener('click', closeModal);
      cancelBtn.addEventListener('click', closeModal);
      
      // Fermer le modal d'ajout en cliquant à l'extérieur
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          closeModal();
        }
      });

      // Empêcher l'ouverture du modal s'il n'y a pas d'associations
      addBtn.addEventListener('click', function(e) {
        <?php if (empty($associations)): ?>
          e.preventDefault();
          alert('Aucune association disponible. Créez d\'abord des associations dans la base de données.');
        <?php endif; ?>
      });

      // Gestion des boutons de suppression
      document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          const projectId = this.getAttribute('data-project-id');
          const projectName = this.getAttribute('data-project-name');
          
          // Mettre à jour le message de confirmation
          confirmationMessage.textContent = `Êtes-vous sûr de vouloir supprimer le projet "${projectName}" ? Cette action est irréversible.`;
          
          // Mettre à jour le lien de confirmation
          confirmDeleteBtn.href = `?supprimer=${projectId}&confirm=oui`;
          
          // Afficher le modal de confirmation
          confirmationModal.style.display = 'flex';
        });
      });

      // Fermer le modal de confirmation
      function closeConfirmationModal() {
        confirmationModal.style.display = 'none';
      }

      cancelDeleteBtn.addEventListener('click', closeConfirmationModal);
      
      // Fermer le modal de confirmation en cliquant à l'extérieur
      confirmationModal.addEventListener('click', function(e) {
        if (e.target === confirmationModal) {
          closeConfirmationModal();
        }
      });

      // Confirmer la suppression (le lien fait déjà l'action)
      confirmDeleteBtn.addEventListener('click', function() {
        closeConfirmationModal();
      });
    });
  </script>
</body>
</html>