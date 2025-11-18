<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

$projectController = new ProjectController();

$message = "";
$message_type = "";

$associations = $projectController->getAssociations();
$admins = $projectController->getAdmins();

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
    $taches = $_POST['taches'] ?? [];

    // Valider et formater les dates
    if (!empty($date_debut)) {
        $date_debut = date('Y-m-d', strtotime($date_debut));
    } else {
        $date_debut = null;
    }
    
    if (!empty($date_fin)) {
        $date_fin = date('Y-m-d', strtotime($date_fin));
    } else {
        $date_fin = null;
    }

    if (empty($association)) {
        $message = "Erreur: Vous devez sélectionner une association";
        $message_type = "error";
    } else {
        $result = $projectController->addProject(
            $titre, $association, $lieu, $date_debut, $date_fin, 
            $disponibilite, $descriptionp, $categorie, $created_by
        );
        
        if ($result === true) {
            $lastProjectId = $projectController->getLastInsertId();
            
            if ($lastProjectId && !empty($taches)) {
                foreach ($taches as $tache) {
                    if (!empty(trim($tache['nom']))) {
                        $projectController->addTache(
                            $tache['nom'],
                            $tache['description'] ?? '',
                            'en_attente',
                            $lastProjectId,
                            $tache['assignee'] ?? null,
                            $created_by
                        );
                    }
                }
            }
            
            $message = "Projet et tâches ajoutés avec succès!";
            $message_type = "success";
            header("Location: listerprojet.php?message=success&action=added");
            exit;
        } else {
            $message = "Erreur lors de l'ajout du projet: " . $result;
            $message_type = "error";
        }
    }
}

// Définir les dates par défaut pour le formulaire
$date_debut_default = date('Y-m-d');
$date_fin_default = date('Y-m-d', strtotime('+1 month'));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Panel - Ajouter un Projet</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f5f5;
      color: #333;
    }

    .admin-wrapper {
      display: flex;
      min-height: 100vh;
    }

    .sidebar {
      width: 250px;
      background: #2c3e50;
      color: white;
      padding: 20px 0;
    }

    .sidebar-header {
      padding: 0 20px 20px;
      border-bottom: 1px solid #34495e;
    }

    .sidebar-header h2 {
      font-size: 1.5rem;
      font-weight: 600;
    }

    .sidebar-nav ul {
      list-style: none;
      padding: 20px 0;
    }

    .sidebar-nav li {
      margin-bottom: 5px;
    }

    .sidebar-nav a {
      display: flex;
      align-items: center;
      padding: 12px 20px;
      color: #bdc3c7;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .sidebar-nav a:hover,
    .sidebar-nav a.active {
      background: #34495e;
      color: white;
      border-left: 4px solid #3498db;
    }

    .sidebar-nav i {
      margin-right: 10px;
      width: 20px;
      text-align: center;
    }

    .main-content {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .topbar {
      background: white;
      padding: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .header-inner {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .title {
      font-size: 1.8rem;
      font-weight: 700;
      color: #2c3e50;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      text-decoration: none;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn i {
      margin-right: 8px;
    }

    .btn-secondary {
      background: #95a5a6;
      color: white;
    }

    .btn-secondary:hover {
      background: #7f8c8d;
    }

    .btn-primary {
      background: #3498db;
      color: white;
    }

    .btn-primary:hover {
      background: #2980b9;
    }

    .btn-primary:disabled {
      background: #bdc3c7;
      cursor: not-allowed;
    }

    .alert {
      padding: 15px 20px;
      margin: 20px;
      border-radius: 5px;
      display: flex;
      align-items: center;
    }

    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .alert i {
      margin-right: 10px;
    }

    .no-associations {
      background: #fff3cd;
      color: #856404;
      padding: 15px 20px;
      margin: 20px;
      border-radius: 5px;
      border: 1px solid #ffeaa7;
      display: flex;
      align-items: center;
    }

    .no-associations i {
      margin-right: 10px;
    }

    .card {
      background: white;
      margin: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    .card-header {
      padding: 20px;
      background: #f8f9fa;
      border-bottom: 1px solid #e9ecef;
    }

    .card-header h3 {
      font-size: 1.4rem;
      font-weight: 600;
      color: #2c3e50;
    }

    .card-body {
      padding: 20px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #2c3e50;
    }

    .form-control {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 14px;
      transition: border-color 0.3s ease;
    }

    .form-control:focus {
      outline: none;
      border-color: #3498db;
      box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }

    textarea.form-control {
      min-height: 100px;
      resize: vertical;
    }

    .taches-section {
      margin-top: 30px;
      padding: 20px;
      background: #f8f9fa;
      border-radius: 5px;
    }

    .taches-section h3 {
      margin-bottom: 15px;
      color: #2c3e50;
    }

    .btn-add-tache {
      background: #27ae60;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 5px;
      cursor: pointer;
      margin-bottom: 20px;
    }

    .btn-add-tache:hover {
      background: #219a52;
    }

    .tache-item {
      background: white;
      padding: 15px;
      margin-bottom: 15px;
      border-radius: 5px;
      border: 1px solid #e9ecef;
    }

    .tache-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #e9ecef;
    }

    .tache-title {
      font-weight: 600;
      color: #2c3e50;
    }

    .btn-remove-tache {
      background: #e74c3c;
      color: white;
      border: none;
      padding: 5px 10px;
      border-radius: 3px;
      cursor: pointer;
      font-size: 12px;
    }

    .btn-remove-tache:hover {
      background: #c0392b;
    }

    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 15px;
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px solid #e9ecef;
    }

    @media (max-width: 768px) {
      .admin-wrapper {
        flex-direction: column;
      }
      
      .sidebar {
        width: 100%;
      }
      
      .form-row {
        grid-template-columns: 1fr;
      }
      
      .header-inner {
        flex-direction: column;
        gap: 15px;
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
          <li><a href="listerprojet.php"><i class="fas fa-project-diagram"></i> Projets</a></li>
          <li><a href="addprojet.php" class="active"><i class="fas fa-plus"></i> Ajouter Projet</a></li>
          <li><a href="#"><i class="fas fa-users"></i> Utilisateurs</a></li>
          <li><a href="#"><i class="fas fa-cog"></i> Paramètres</a></li>
        </ul>
      </nav>
    </aside>

    <div class="main-content">
      <header class="topbar">
        <div class="header-inner">
          <h1 class="title">Ajouter un Nouveau Projet</h1>
          <a href="listerprojet.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
          </a>
        </div>
      </header>

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

      <div class="card">
        <div class="card-header">
          <h3>Informations du Projet</h3>
        </div>
        <div class="card-body">
          <form method="POST" action="" id="projectForm">
            <div class="form-group">
              <label for="titre">Titre</label>
              <input type="text" id="titre" name="titre" class="form-control" 
                     value="<?php echo isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : ''; ?>">
            </div>
            
            <div class="form-group">
              <label for="association">Association</label>
              <?php if (!empty($associations)): ?>
                <select id="association" name="association" class="form-control">
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
            </div>
            
            <div class="form-group">
              <label for="lieu">Lieu</label>
              <input type="text" id="lieu" name="lieu" class="form-control"
                     value="<?php echo isset($_POST['lieu']) ? htmlspecialchars($_POST['lieu']) : ''; ?>">
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="date_debut">Date de début</label>
                <input type="date" id="date_debut" name="date_debut" class="form-control"
                       value="<?php echo isset($_POST['date_debut']) ? htmlspecialchars($_POST['date_debut']) : $date_debut_default; ?>">
              </div>
              
              <div class="form-group">
                <label for="date_fin">Date de fin</label>
                <input type="date" id="date_fin" name="date_fin" class="form-control"
                       value="<?php echo isset($_POST['date_fin']) ? htmlspecialchars($_POST['date_fin']) : $date_fin_default; ?>">
              </div>
            </div>
            
            <div class="form-group">
              <label for="disponibilite">Disponibilité</label>
              <select id="disponibilite" name="disponibilite" class="form-control">
                <option value="">Sélectionner</option>
                <option value="disponible" <?php echo (isset($_POST['disponibilite']) && $_POST['disponibilite'] == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                <option value="complet" <?php echo (isset($_POST['disponibilite']) && $_POST['disponibilite'] == 'complet') ? 'selected' : ''; ?>>Complet</option>
                <option value="termine" <?php echo (isset($_POST['disponibilite']) && $_POST['disponibilite'] == 'termine') ? 'selected' : ''; ?>>Terminé</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="descriptionp">Description</label>
              <textarea id="descriptionp" name="descriptionp" class="form-control"><?php echo isset($_POST['descriptionp']) ? htmlspecialchars($_POST['descriptionp']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
              <label for="categorie">Catégorie</label>
              <select id="categorie" name="categorie" class="form-control">
                <option value="">Sélectionner</option>
                <option value="Solidarité" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == 'Solidarité') ? 'selected' : ''; ?>>Solidarité</option>
                <option value="Environement" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == 'Environement') ? 'selected' : ''; ?>>Environement</option>
                <option value="Education" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == 'Education') ? 'selected' : ''; ?>>Education</option>
                <option value="Sante" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == 'Sante') ? 'selected' : ''; ?>>Sante</option>
                <option value="Aide" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == 'Aide') ? 'selected' : ''; ?>>Aide</option>
                <option value="Culture" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == 'Culture') ? 'selected' : ''; ?>>Culture</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="created_by">Créé par</label>
              <select id="created_by" name="created_by" class="form-control">
                <option value="">Sélectionner un administrateur</option>
                <?php foreach($admins as $admin): ?>
                  <option value="<?php echo $admin['id']; ?>"
                    <?php echo (isset($_POST['created_by']) && $_POST['created_by'] == $admin['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($admin['nom'] . ' (' . $admin['email'] . ')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="taches-section">
              <h3>Tâches du Projet</h3>
              <button type="button" class="btn-add-tache" id="addTache">
                <i class="fas fa-plus"></i> Ajouter une tâche
              </button>
              
              <div id="taches-container">
                <?php if (isset($_POST['taches']) && is_array($_POST['taches'])): ?>
                  <?php foreach($_POST['taches'] as $index => $tache): ?>
                    <div class="tache-item" data-index="<?php echo $index; ?>">
                      <div class="tache-header">
                        <span class="tache-title">Tâche #<?php echo $index + 1; ?></span>
                        <button type="button" class="btn-remove-tache" onclick="removeTache(this)">
                          <i class="fas fa-times"></i> Supprimer
                        </button>
                      </div>
                      <div class="form-group">
                        <label>Nom de la tâche</label>
                        <input type="text" name="taches[<?php echo $index; ?>][nom]" class="form-control" 
                               value="<?php echo htmlspecialchars($tache['nom'] ?? ''); ?>">
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
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
            
            <div class="form-actions">
              <a href="listerprojet.php" class="btn btn-secondary">Annuler</a>
              <button type="submit" name="ajouter_projet" class="btn btn-primary" <?php echo empty($associations) ? 'disabled' : ''; ?>>
                <i class="fas fa-plus"></i> Ajouter le projet
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    let tacheCount = <?php echo isset($_POST['taches']) ? count($_POST['taches']) : 0; ?>;
    
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
          <label>Nom de la tâche</label>
          <input type="text" name="taches[${tacheCount}][nom]" class="form-control">
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
      tacheCount++;
    });
    
    function removeTache(button) {
      const tacheItem = button.closest('.tache-item');
      tacheItem.remove();
      updateTacheNumbers();
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
      const dateDebut = document.getElementById('date_debut');
      const dateFin = document.getElementById('date_fin');
      
      // Définir des dates par défaut si vides
      const aujourdhui = new Date().toISOString().split('T')[0];
      const dansUnMois = new Date();
      dansUnMois.setMonth(dansUnMois.getMonth() + 1);
      const dansUnMoisStr = dansUnMois.toISOString().split('T')[0];
      
      if (dateDebut && !dateDebut.value) {
        dateDebut.value = aujourdhui;
      }
      if (dateFin && !dateFin.value) {
        dateFin.value = dansUnMoisStr;
      }
      
      if (dateDebut && dateFin) {
        dateDebut.addEventListener('change', function() {
          if (dateFin.value && this.value > dateFin.value) {
            alert('La date de début ne peut pas être après la date de fin');
            dateFin.value = this.value;
          }
        });
        
        dateFin.addEventListener('change', function() {
          if (dateDebut.value && this.value < dateDebut.value) {
            alert('La date de fin ne peut pas être avant la date de début');
            dateDebut.value = this.value;
          }
        });
      }
      
      const form = document.getElementById('projectForm');
      form.addEventListener('submit', function(e) {
        <?php if (empty($associations)): ?>
          e.preventDefault();
          alert('Aucune association disponible. Créez d\'abord des associations dans la base de données.');
        <?php endif; ?>
        
        // Validation supplémentaire des dates
        if (dateDebut.value && dateFin.value && dateDebut.value > dateFin.value) {
          e.preventDefault();
          alert('Erreur : La date de début ne peut pas être après la date de fin');
          return false;
        }
      });
    });
  </script>
</body>
</html>