<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

$projectController = new ProjectController();

$message = "";
$message_type = "";

$associations = $projectController->getAssociations();
$users = $projectController->getUsers(); // Remplacé getAdmins() par getUsers()

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_projet'])) {
    // Nettoyage et validation des données
    $titre = trim($_POST['titre'] ?? '');
    $association = $_POST['association'] ?? null;
    $lieu = trim($_POST['lieu'] ?? '');
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';
    $disponibilite = $_POST['disponibilite'] ?? '';
    $description = trim($_POST['description'] ?? ''); // Changé descriptionp en description
    $categorie = $_POST['categorie'] ?? '';
    $created_by = $_POST['created_by'] ?? '';
    $taches = $_POST['taches'] ?? [];

    // Validation des champs obligatoires
    $errors = [];

    if (empty($titre)) {
        $errors[] = "Le titre est obligatoire";
    } elseif (strlen($titre) > 255) {
        $errors[] = "Le titre ne doit pas dépasser 255 caractères";
    }

    if (empty($association)) {
        $errors[] = "Vous devez sélectionner une association";
    }

    if (empty($lieu)) {
        $errors[] = "Le lieu est obligatoire";
    } elseif (strlen($lieu) > 255) {
        $errors[] = "Le lieu ne doit pas dépasser 255 caractères";
    }

    if (empty($date_debut)) {
        $errors[] = "La date de début est obligatoire";
    } else {
        $date_debut = date('Y-m-d', strtotime($date_debut));
        if (!$date_debut || $date_debut == '1970-01-01') {
            $errors[] = "La date de début n'est pas valide";
        }
    }

    if (!empty($date_fin)) {
        $date_fin = date('Y-m-d', strtotime($date_fin));
        if (!$date_fin || $date_fin == '1970-01-01') {
            $errors[] = "La date de fin n'est pas valide";
        } elseif ($date_fin < $date_debut) {
            $errors[] = "La date de fin ne peut pas être avant la date de début";
        }
    } else {
        $date_fin = null;
    }

    if (empty($disponibilite)) {
        $errors[] = "La disponibilité est obligatoire";
    } elseif (!in_array($disponibilite, ['disponible', 'complet', 'termine'])) {
        $errors[] = "La disponibilité sélectionnée n'est pas valide";
    }

    if (empty($description)) {
        $errors[] = "La description est obligatoire";
    } elseif (strlen($description) > 1000) {
        $errors[] = "La description ne doit pas dépasser 1000 caractères";
    }

    if (empty($categorie)) {
        $errors[] = "La catégorie est obligatoire";
    } elseif (!in_array($categorie, ['Solidarité', 'Environement', 'Education', 'Sante', 'Aide', 'Culture'])) {
        $errors[] = "La catégorie sélectionnée n'est pas valide";
    }

    if (empty($created_by)) {
        $errors[] = "Vous devez sélectionner un utilisateur";
    }

    // Validation des tâches
    $valid_taches = [];
    if (!empty($taches)) {
        foreach ($taches as $index => $tache) {
            $tache_nom = trim($tache['nom'] ?? '');
            $tache_description = trim($tache['description'] ?? '');
            $tache_assignee = $tache['assignee'] ?? null;
            
            // Ne garder que les tâches avec un nom
            if (!empty($tache_nom)) {
                if (strlen($tache_nom) > 255) {
                    $errors[] = "Le nom de la tâche #" . ($index + 1) . " ne doit pas dépasser 255 caractères";
                }
                
                if (strlen($tache_description) > 500) {
                    $errors[] = "La description de la tâche #" . ($index + 1) . " ne doit pas dépasser 500 caractères";
                }
                
                $valid_taches[] = [
                    'nom' => $tache_nom,
                    'description' => $tache_description,
                    'assignee' => !empty($tache_assignee) ? $tache_assignee : null
                ];
            }
        }
    }

    if (empty($errors)) {
        $result = $projectController->addProject(
            $titre, $association, $lieu, $date_debut, $date_fin, 
            $disponibilite, $description, $categorie, $created_by
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
            header("Location: listerprojet.php?message=success&action=added");
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
  <link rel="stylesheet" href="../style/addprojet.css" />
  <style>
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
    
    .form-group {
      margin-bottom: 1rem;
    }
    
    .character-count {
      font-size: 0.75rem;
      color: #6c757d;
      text-align: right;
      margin-top: 0.25rem;
    }
    
    .character-count.warning {
      color: #ffc107;
    }
    
    .character-count.error {
      color: #dc3545;
    }

    .no-associations {
      background: #fff3cd;
      border: 1px solid #ffeaa7;
      color: #856404;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .no-associations i {
      color: #ffc107;
    }

    .tache-item {
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
    }

    .tache-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .tache-title {
      font-weight: 600;
      color: #495057;
    }

    .btn-remove-tache {
      background: #dc3545;
      color: white;
      border: none;
      padding: 5px 10px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.875rem;
    }

    .btn-remove-tache:hover {
      background: #c82333;
    }

    .btn-add-tache {
      background: #28a745;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 6px;
      cursor: pointer;
      margin-bottom: 15px;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    .btn-add-tache:hover {
      background: #218838;
    }

    .alert {
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 25px;
      font-weight: 500;
      display: flex;
      align-items: center;
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
      margin-right: 10px;
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
          <form method="POST" action="" id="projectForm" novalidate>
            <div class="form-group">
              <label for="titre">Titre *</label>
              <input type="text" id="titre" name="titre" class="form-control" 
                     value="<?php echo isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : ''; ?>"
                     maxlength="255" required>
              <div class="character-count" id="titre-count">0/255</div>
              <span class="error-message" id="titre-error"></span>
            </div>
            
            <div class="form-group">
              <label for="association">Association *</label>
              <?php if (!empty($associations)): ?>
                <select id="association" name="association" class="form-control" required>
                  <option value="">Sélectionner une association</option>
                  <?php foreach($associations as $association): ?>
                    <option value="<?php echo $association['id']; ?>" 
                      <?php echo (isset($_POST['association']) && $_POST['association'] == $association['id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($association['first_name'] . ' ' . $association['last_name'] . ' (' . $association['email'] . ')'); ?>
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
              <label for="lieu">Lieu *</label>
              <input type="text" id="lieu" name="lieu" class="form-control"
                     value="<?php echo isset($_POST['lieu']) ? htmlspecialchars($_POST['lieu']) : ''; ?>"
                     maxlength="255" required>
              <div class="character-count" id="lieu-count">0/255</div>
              <span class="error-message" id="lieu-error"></span>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="date_debut">Date de début *</label>
                <input type="date" id="date_debut" name="date_debut" class="form-control"
                       value="<?php echo isset($_POST['date_debut']) ? htmlspecialchars($_POST['date_debut']) : $date_debut_default; ?>"
                       required>
                <span class="error-message" id="date_debut-error"></span>
              </div>
              
              <div class="form-group">
                <label for="date_fin">Date de fin</label>
                <input type="date" id="date_fin" name="date_fin" class="form-control"
                       value="<?php echo isset($_POST['date_fin']) ? htmlspecialchars($_POST['date_fin']) : $date_fin_default; ?>">
                <span class="error-message" id="date_fin-error"></span>
              </div>
            </div>
            
            <div class="form-group">
              <label for="disponibilite">Disponibilité *</label>
              <select id="disponibilite" name="disponibilite" class="form-control" required>
                <option value="">Sélectionner</option>
                <option value="disponible" <?php echo (isset($_POST['disponibilite']) && $_POST['disponibilite'] == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                <option value="complet" <?php echo (isset($_POST['disponibilite']) && $_POST['disponibilite'] == 'complet') ? 'selected' : ''; ?>>Complet</option>
                <option value="termine" <?php echo (isset($_POST['disponibilite']) && $_POST['disponibilite'] == 'termine') ? 'selected' : ''; ?>>Terminé</option>
              </select>
              <span class="error-message" id="disponibilite-error"></span>
            </div>
            
            <div class="form-group">
              <label for="description">Description *</label>
              <textarea id="description" name="description" class="form-control" maxlength="1000" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
              <div class="character-count" id="description-count">0/1000</div>
              <span class="error-message" id="description-error"></span>
            </div>
            
            <div class="form-group">
              <label for="categorie">Catégorie *</label>
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
              <label for="created_by">Créé par *</label>
              <select id="created_by" name="created_by" class="form-control" required>
                <option value="">Sélectionner un utilisateur</option>
                <?php foreach($users as $user): ?>
                  <option value="<?php echo $user['id']; ?>"
                    <?php echo (isset($_POST['created_by']) && $_POST['created_by'] == $user['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <span class="error-message" id="created_by-error"></span>
            </div>

            <div class="taches-section">
              <h3>Tâches du Projet</h3>
              <button type="button" class="btn-add-tache" id="addTache">
                <i class="fas fa-plus"></i> Ajouter une tâche
              </button>
              
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
                          <label>Nom de la tâche</label>
                          <input type="text" name="taches[<?php echo $index; ?>][nom]" class="form-control tache-nom" 
                                 value="<?php echo htmlspecialchars($tache['nom'] ?? ''); ?>" maxlength="255">
                          <div class="character-count tache-nom-count"><?php echo strlen($tache['nom'] ?? ''); ?>/255</div>
                        </div>
                        <div class="form-group">
                          <label>Description</label>
                          <textarea name="taches[<?php echo $index; ?>][description]" class="form-control tache-description" maxlength="500"><?php echo htmlspecialchars($tache['description'] ?? ''); ?></textarea>
                          <div class="character-count tache-desc-count"><?php echo strlen($tache['description'] ?? ''); ?>/500</div>
                        </div>
                        <div class="form-group">
                          <label>Assigné à</label>
                          <select name="taches[<?php echo $index; ?>][assignee]" class="form-control">
                            <option value="">Non assigné</option>
                            <?php foreach($users as $user): ?>
                              <option value="<?php echo $user['id']; ?>"
                                <?php echo (isset($tache['assignee']) && $tache['assignee'] == $user['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
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
    let tacheCount = <?php echo isset($_POST['taches']) ? count(array_filter($_POST['taches'], function($t) { return !empty(trim($t['nom'])); })) : 0; ?>;
    
    // Fonctions pour le comptage de caractères
    function updateCharacterCount(element, counterId, maxLength) {
      const count = element.value.length;
      const counter = document.getElementById(counterId);
      if (counter) {
        counter.textContent = `${count}/${maxLength}`;
        
        if (count > maxLength * 0.9) {
          counter.className = 'character-count error';
        } else if (count > maxLength * 0.7) {
          counter.className = 'character-count warning';
        } else {
          counter.className = 'character-count';
        }
      }
    }
    
    function validateField(field, errorId, rules) {
      const value = field.value.trim();
      const errorElement = document.getElementById(errorId);
      let isValid = true;
      let errorMessage = '';
      
      if (rules.required && !value) {
        isValid = false;
        errorMessage = rules.required;
      } else if (rules.maxLength && value.length > rules.maxLength) {
        isValid = false;
        errorMessage = `Ne doit pas dépasser ${rules.maxLength} caractères`;
      }
      
      if (isValid) {
        field.classList.remove('error-field');
        if (errorElement) errorElement.textContent = '';
      } else {
        field.classList.add('error-field');
        if (errorElement) errorElement.textContent = errorMessage;
      }
      
      return isValid;
    }
    
    // Configuration de validation pour chaque champ
    const validationRules = {
      titre: { required: 'Le titre est obligatoire', maxLength: 255 },
      association: { required: 'Une association est obligatoire' },
      lieu: { required: 'Le lieu est obligatoire', maxLength: 255 },
      date_debut: { required: 'La date de début est obligatoire' },
      disponibilite: { required: 'La disponibilité est obligatoire' },
      description: { required: 'La description est obligatoire', maxLength: 1000 },
      categorie: { required: 'La catégorie est obligatoire' },
      created_by: { required: 'Un utilisateur est obligatoire' }
    };
    
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
          <input type="text" name="taches[${tacheCount}][nom]" class="form-control tache-nom" maxlength="255">
          <div class="character-count">0/255</div>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="taches[${tacheCount}][description]" class="form-control tache-description" maxlength="500"></textarea>
          <div class="character-count">0/500</div>
        </div>
        <div class="form-group">
          <label>Assigné à</label>
          <select name="taches[${tacheCount}][assignee]" class="form-control">
            <option value="">Non assigné</option>
            <?php foreach($users as $user): ?>
              <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      `;
      
      container.appendChild(tacheItem);
      
      // Ajouter les écouteurs d'événements pour les nouvelles tâches
      const nomInput = tacheItem.querySelector('.tache-nom');
      const descTextarea = tacheItem.querySelector('.tache-description');
      const nomCounter = tacheItem.querySelector('.character-count:nth-of-type(1)');
      const descCounter = tacheItem.querySelector('.character-count:nth-of-type(2)');
      
      nomInput.addEventListener('input', function() {
        const count = this.value.length;
        nomCounter.textContent = `${count}/255`;
        
        if (count > 255 * 0.9) {
          nomCounter.className = 'character-count error';
        } else if (count > 255 * 0.7) {
          nomCounter.className = 'character-count warning';
        } else {
          nomCounter.className = 'character-count';
        }
      });
      
      descTextarea.addEventListener('input', function() {
        const count = this.value.length;
        descCounter.textContent = `${count}/500`;
        
        if (count > 500 * 0.9) {
          descCounter.className = 'character-count error';
        } else if (count > 500 * 0.7) {
          descCounter.className = 'character-count warning';
        } else {
          descCounter.className = 'character-count';
        }
      });
      
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
    
    // Validation du formulaire avant soumission
    function validateForm() {
      let isValid = true;
      
      for (const [fieldName, rules] of Object.entries(validationRules)) {
        const field = document.getElementById(fieldName);
        if (field) {
          if (!validateField(field, `${fieldName}-error`, rules)) {
            isValid = false;
          }
        }
      }
      
      // Validation des dates
      const dateDebut = document.getElementById('date_debut');
      const dateFin = document.getElementById('date_fin');
      
      if (dateDebut.value && dateFin.value && dateDebut.value > dateFin.value) {
        isValid = false;
        const errorElement = document.getElementById('date_fin-error');
        if (errorElement) {
          errorElement.textContent = 'La date de fin ne peut pas être avant la date de début';
        }
        dateFin.classList.add('error-field');
      }
      
      return isValid;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
      // Initialiser les compteurs de caractères
      const titre = document.getElementById('titre');
      const lieu = document.getElementById('lieu');
      const description = document.getElementById('description');
      
      if (titre) {
        updateCharacterCount(titre, 'titre-count', 255);
        titre.addEventListener('input', function() {
          updateCharacterCount(this, 'titre-count', 255);
          validateField(this, 'titre-error', validationRules.titre);
        });
      }
      
      if (lieu) {
        updateCharacterCount(lieu, 'lieu-count', 255);
        lieu.addEventListener('input', function() {
          updateCharacterCount(this, 'lieu-count', 255);
          validateField(this, 'lieu-error', validationRules.lieu);
        });
      }
      
      if (description) {
        updateCharacterCount(description, 'description-count', 1000);
        description.addEventListener('input', function() {
          updateCharacterCount(this, 'description-count', 1000);
          validateField(this, 'description-error', validationRules.description);
        });
      }
      
      // Ajouter les écouteurs pour la validation en temps réel
      Object.keys(validationRules).forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
          field.addEventListener('blur', function() {
            validateField(this, `${fieldName}-error`, validationRules[fieldName]);
          });
          
          field.addEventListener('input', function() {
            if (this.classList.contains('error-field')) {
              validateField(this, `${fieldName}-error`, validationRules[fieldName]);
            }
          });
        }
      });
      
      // Validation des dates
      const dateDebut = document.getElementById('date_debut');
      const dateFin = document.getElementById('date_fin');
      
      if (dateDebut && dateFin) {
        dateDebut.addEventListener('change', function() {
          validateField(this, 'date_debut-error', validationRules.date_debut);
          if (dateFin.value && this.value > dateFin.value) {
            dateFin.value = this.value;
          }
        });
        
        dateFin.addEventListener('change', function() {
          if (dateDebut.value && this.value < dateDebut.value) {
            const errorElement = document.getElementById('date_fin-error');
            if (errorElement) {
              errorElement.textContent = 'La date de fin ne peut pas être avant la date de début';
            }
            this.classList.add('error-field');
          } else {
            const errorElement = document.getElementById('date_fin-error');
            if (errorElement) {
              errorElement.textContent = '';
            }
            this.classList.remove('error-field');
          }
        });
      }
      
      // Compteurs de caractères pour les tâches existantes
      document.querySelectorAll('.tache-nom').forEach(input => {
        input.addEventListener('input', function() {
          const count = this.value.length;
          const counter = this.nextElementSibling;
          if (counter && counter.classList.contains('character-count')) {
            counter.textContent = `${count}/255`;
            
            if (count > 255 * 0.9) {
              counter.className = 'character-count error';
            } else if (count > 255 * 0.7) {
              counter.className = 'character-count warning';
            } else {
              counter.className = 'character-count';
            }
          }
        });
      });
      
      document.querySelectorAll('.tache-description').forEach(textarea => {
        textarea.addEventListener('input', function() {
          const count = this.value.length;
          const counter = this.nextElementSibling;
          if (counter && counter.classList.contains('character-count')) {
            counter.textContent = `${count}/500`;
            
            if (count > 500 * 0.9) {
              counter.className = 'character-count error';
            } else if (count > 500 * 0.7) {
              counter.className = 'character-count warning';
            } else {
              counter.className = 'character-count';
            }
          }
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
    });
  </script>
</body>
</html>