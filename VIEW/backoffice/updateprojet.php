<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

$projectController = new ProjectController();

// Variables pour les messages
$message = "";
$message_type = "";

// Récupérer les données nécessaires
$associations = $projectController->getAssociations();
$admins = $projectController->getAdmins();

// Récupérer l'ID du projet à modifier depuis l'URL
$id_projet = $_GET['id'] ?? null;

// Si aucun ID n'est spécifié, rediriger vers la liste
if (!$id_projet) {
    header("Location: listerprojet.php");
    exit;
}

// Récupérer les données du projet à modifier
$projet = $projectController->getProjectById($id_projet);

// Si le projet n'existe pas, rediriger
if (!$projet) {
    header("Location: listerprojet.php");
    exit;
}

// Traitement du formulaire de modification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_projet'])) {
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
        // Modifier le projet via le Controller
        $result = $projectController->updateProject(
            $id_projet, $titre, $association, $lieu, $date_debut, $date_fin, 
            $disponibilite, $descriptionp, $categorie, $created_by
        );
        
        if ($result === true) {
            $message = "Projet modifié avec succès!";
            $message_type = "success";
            
            // Rediriger vers la liste après 2 secondes
            header("Refresh: 2; URL=listerprojet.php");
        } else {
            $message = "Erreur lors de la modification du projet: " . $result;
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Panel - Modifier un Projet</title>
  <link rel="stylesheet" href="../style/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
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
      padding: 20px;
    }
    
    .topbar {
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      margin-bottom: 20px;
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
    
    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      text-align: center;
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
    
    .btn-warning {
      background-color: #ffc107;
      color: #212529;
    }
    
    .btn-warning:hover {
      background-color: #e0a800;
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
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .card-header h3 {
      margin: 0;
      color: #333;
    }
    
    .project-id {
      background-color: #f8f9fa;
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 14px;
      color: #666;
    }
    
    .card-body {
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
    
    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px solid #eee;
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
    
    .required {
      color: #dc3545;
    }
    
    .info-box {
      background-color: #e7f3ff;
      border: 1px solid #b3d9ff;
      border-radius: 4px;
      padding: 15px;
      margin-bottom: 20px;
    }
    
    .info-box p {
      margin: 0;
      color: #0066cc;
      font-size: 14px;
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
          <li><a href="listerprojet.php"><i class="fas fa-project-diagram"></i> Projets</a></li>
          <li><a href="addprojet.php"><i class="fas fa-plus"></i> Ajouter Projet</a></li>
          <li><a href="updateprojet.php?id=<?php echo $id_projet; ?>" class="active"><i class="fas fa-edit"></i> Modifier Projet</a></li>
          <li><a href="#"><i class="fas fa-users"></i> Utilisateurs</a></li>
          <li><a href="#"><i class="fas fa-cog"></i> Paramètres</a></li>
        </ul>
      </nav>
    </aside>

    <div class="main-content">
      <header class="topbar">
        <div class="header-inner">
          <h1 class="title">Modifier un Projet</h1>
          <a href="listerprojet.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
          </a>
        </div>
      </header>

      <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'error'; ?>">
          <?php echo $message; ?>
          <?php if ($message_type == 'success'): ?>
            <p>Redirection vers la liste des projets dans 2 secondes...</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (empty($associations)): ?>
        <div class="no-associations">
          <i class="fas fa-exclamation-triangle"></i>
          <strong>Aucune association trouvée.</strong> Vous devez d'abord créer des associations dans la base de données avant de pouvoir modifier des projets.
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">
          <h3>Modification du Projet</h3>
          <div class="project-id">ID: <?php echo htmlspecialchars($projet['id_projet']); ?></div>
        </div>
        <div class="card-body">
          <div class="info-box">
            <p><i class="fas fa-info-circle"></i> Vous modifiez le projet "<?php echo htmlspecialchars($projet['titre']); ?>"</p>
          </div>
          
          <form method="POST" action="" id="projectForm">
            <div class="form-group">
              <label for="titre">Titre <span class="required">*</span></label>
              <input type="text" id="titre" name="titre" class="form-control" required 
                     value="<?php echo isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : htmlspecialchars($projet['titre']); ?>">
            </div>
            
            <div class="form-group">
              <label for="association">Association <span class="required">*</span></label>
              <?php if (!empty($associations)): ?>
                <select id="association" name="association" class="form-control" required>
                  <option value="">Sélectionner une association</option>
                  <?php foreach($associations as $association): ?>
                    <option value="<?php echo $association['id']; ?>" 
                      <?php 
                        $selected_association = isset($_POST['association']) ? $_POST['association'] : $projet['association'];
                        echo ($selected_association == $association['id']) ? 'selected' : '';
                      ?>>
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
              <input type="text" id="lieu" name="lieu" class="form-control"
                     value="<?php echo isset($_POST['lieu']) ? htmlspecialchars($_POST['lieu']) : htmlspecialchars($projet['lieu'] ?? ''); ?>">
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="date_debut">Date de début</label>
                <input type="date" id="date_debut" name="date_debut" class="form-control"
                       value="<?php echo isset($_POST['date_debut']) ? htmlspecialchars($_POST['date_debut']) : htmlspecialchars($projet['date_debut'] ?? ''); ?>">
              </div>
              
              <div class="form-group">
                <label for="date_fin">Date de fin</label>
                <input type="date" id="date_fin" name="date_fin" class="form-control"
                       value="<?php echo isset($_POST['date_fin']) ? htmlspecialchars($_POST['date_fin']) : htmlspecialchars($projet['date_fin'] ?? ''); ?>">
              </div>
            </div>
            
            <div class="form-group">
              <label for="disponibilite">Disponibilité</label>
              <select id="disponibilite" name="disponibilite" class="form-control">
                <option value="">Sélectionner</option>
                <option value="disponible" <?php 
                  $selected_dispo = isset($_POST['disponibilite']) ? $_POST['disponibilite'] : $projet['disponibilite'];
                  echo ($selected_dispo == 'disponible') ? 'selected' : '';
                ?>>Disponible</option>
                <option value="complet" <?php echo ($selected_dispo == 'complet') ? 'selected' : ''; ?>>Complet</option>
                <option value="termine" <?php echo ($selected_dispo == 'termine') ? 'selected' : ''; ?>>Terminé</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="descriptionp">Description</label>
              <textarea id="descriptionp" name="descriptionp" class="form-control"><?php echo isset($_POST['descriptionp']) ? htmlspecialchars($_POST['descriptionp']) : htmlspecialchars($projet['descriptionp'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
              <label for="categorie">Catégorie</label>
              <select id="categorie" name="categorie" class="form-control">
                <option value="">Sélectionner</option>
                <option value="Solidarité" <?php 
                  $selected_cat = isset($_POST['categorie']) ? $_POST['categorie'] : $projet['categorie'];
                  echo ($selected_cat == 'Solidarité') ? 'selected' : '';
                ?>>Solidarité</option>
                <option value="Environement" <?php echo ($selected_cat == 'Environement') ? 'selected' : ''; ?>>Environement</option>
                <option value="Education" <?php echo ($selected_cat == 'Education') ? 'selected' : ''; ?>>Education</option>
                <option value="Sante" <?php echo ($selected_cat == 'Sante') ? 'selected' : ''; ?>>Sante</option>
                <option value="Aide" <?php echo ($selected_cat == 'Aide') ? 'selected' : ''; ?>>Aide</option>
                <option value="Culture" <?php echo ($selected_cat == 'Culture') ? 'selected' : ''; ?>>Culture</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="created_by">Créé par <span class="required">*</span></label>
              <select id="created_by" name="created_by" class="form-control" required>
                <option value="">Sélectionner un administrateur</option>
                <?php foreach($admins as $admin): ?>
                  <option value="<?php echo $admin['id']; ?>"
                    <?php 
                      $selected_admin = isset($_POST['created_by']) ? $_POST['created_by'] : $projet['created_by'];
                      echo ($selected_admin == $admin['id']) ? 'selected' : '';
                    ?>>
                    <?php echo htmlspecialchars($admin['nom'] . ' (' . $admin['email'] . ')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="form-actions">
              <a href="listerprojet.php" class="btn btn-secondary">Annuler</a>
              <button type="submit" name="modifier_projet" class="btn btn-warning" <?php echo empty($associations) ? 'disabled' : ''; ?>>
                <i class="fas fa-edit"></i> Modifier le projet
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Validation des dates
      const dateDebut = document.getElementById('date_debut');
      const dateFin = document.getElementById('date_fin');
      
      if (dateDebut && dateFin) {
        dateDebut.addEventListener('change', function() {
          if (dateFin.value && this.value > dateFin.value) {
            alert('La date de début ne peut pas être après la date de fin');
            this.value = '';
          }
        });
        
        dateFin.addEventListener('change', function() {
          if (dateDebut.value && this.value < dateDebut.value) {
            alert('La date de fin ne peut pas être avant la date de début');
            this.value = '';
          }
        });
      }
      
      // Empêcher la soumission si pas d'associations
      const form = document.getElementById('projectForm');
      form.addEventListener('submit', function(e) {
        <?php if (empty($associations)): ?>
          e.preventDefault();
          alert('Aucune association disponible. Créez d\'abord des associations dans la base de données.');
        <?php endif; ?>
      });
    });
  </script>
</body>
</html>