<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

$projectController = new ProjectController();

$message = "";
$message_type = "";

$associations = $projectController->getAssociations();
$admins = $projectController->getAdmins();
$categories = $projectController->getCategories();

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
            echo "<script>window.location.href = window.location.href.split('?')[0];</script>";
            exit;
        } else {
            $message = "Erreur lors de l'ajout du projet: " . $result;
            $message_type = "error";
        }
    }
}

if (isset($_GET['supprimer']) && isset($_GET['confirm']) && $_GET['confirm'] === 'oui') {
    $id = $_GET['supprimer'];
    
    if ($projectController->deleteProject($id)) {
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

$projetsSupprimes = 0;
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

  <div class="modal-overlay" id="projectModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Ajouter un nouveau projet</h2>
        <button class="close-btn" id="closeModal">&times;</button>
      </div>
      <form method="POST" action="" id="projectForm">
        <div class="modal-body">
          <div class="form-group">
            <label for="titre">Titre</label>
            <input type="text" id="titre" name="titre" class="form-control">
          </div>
          
          <div class="form-group">
            <label for="association">Association</label>
            <?php if (!empty($associations)): ?>
              <select id="association" name="association" class="form-control">
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
            <label for="created_by">Créé par</label>
            <select id="created_by" name="created_by" class="form-control">
              <option value="">Sélectionner un administrateur</option>
              <?php foreach($admins as $admin): ?>
                <option value="<?php echo $admin['id']; ?>">
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
            
            <div id="taches-container"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="cancelBtn">Annuler</button>
          <button type="submit" name="ajouter_projet" class="btn btn-primary" <?php echo empty($associations) ? 'disabled' : ''; ?>>Ajouter le projet</button>
        </div>
      </form>
    </div>
  </div>

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
    let tacheCount = 0;
    
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
      const modal = document.getElementById('projectModal');
      const addBtn = document.getElementById('addProjectBtn');
      const closeBtn = document.getElementById('closeModal');
      const cancelBtn = document.getElementById('cancelBtn');
      
      const confirmationModal = document.getElementById('confirmationModal');
      const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
      const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
      const confirmationMessage = document.getElementById('confirmationMessage');
      
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

      addBtn.addEventListener('click', function(e) {
        <?php if (empty($associations)): ?>
          e.preventDefault();
          alert('Aucune association disponible. Créez d\'abord des associations dans la base de données.');
        <?php endif; ?>
      });

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

      // Solution améliorée pour la validation des dates
      const dateDebut = document.getElementById('date_debut');
      const dateFin = document.getElementById('date_fin');
      
      if (dateDebut && dateFin) {
        // Utiliser l'événement 'input' au lieu de 'change' pour une meilleure réactivité
        dateDebut.addEventListener('input', function() {
          if (dateFin.value && this.value > dateFin.value) {
            // Ne pas réinitialiser, mais ajuster automatiquement la date de fin
            dateFin.value = this.value;
          }
        });
        
        dateFin.addEventListener('input', function() {
          if (dateDebut.value && this.value < dateDebut.value) {
            // Ne pas réinitialiser, mais ajuster automatiquement la date de début
            dateDebut.value = this.value;
          }
        });

        // Solution alternative : validation seulement à la soumission du formulaire
        const form = document.getElementById('projectForm');
        form.addEventListener('submit', function(e) {
          if (dateDebut.value && dateFin.value && dateDebut.value > dateFin.value) {
            e.preventDefault();
            // Afficher un message d'erreur élégant
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-error';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> La date de début ne peut pas être après la date de fin.';
            
            // Insérer le message d'erreur après le form-row des dates
            const dateRow = document.querySelector('.form-row');
            dateRow.parentNode.insertBefore(errorDiv, dateRow.nextSibling);
            
            // Supprimer le message après 5 secondes
            setTimeout(() => {
              errorDiv.remove();
            }, 5000);
          }
        });
      }
      
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