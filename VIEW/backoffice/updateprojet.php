<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

$projectController = new ProjectController();

// Variables pour les messages
$message = "";
$message_type = "";

// Récupérer les données nécessaires
$associations = $projectController->getAssociations();
$users = $projectController->getUsers(); // Remplacé getAdmins() par getUsers()

// Vérifier si l'ID du projet est passé en paramètre
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listerprojet.php");
    exit;
}

$id_projet = $_GET['id'];

// Récupérer les données du projet à modifier
$projet = $projectController->getProjectById($id_projet);

if (!$projet) {
    $message = "Projet non trouvé!";
    $message_type = "error";
}

// Traitement du formulaire de modification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_projet'])) {
    $titre = $_POST['titre'];
    $association = $_POST['association'] ?? null;
    $lieu = $_POST['lieu'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $disponibilite = $_POST['disponibilite'];
    $description = $_POST['description']; // Changé descriptionp en description
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
            $disponibilite, $description, $categorie, $created_by
        );
        
        if ($result === true) {
            $message = "Projet modifié avec succès!";
            $message_type = "success";
            
            // Rediriger vers la liste des projets après 2 secondes
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'listerprojet.php';
                }, 2000);
            </script>";
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
  <title>Admin Panel - Modifier Projet</title>
  <link rel="stylesheet" href="../style/updateprojet.css" />
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
      padding: 20px;
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: #7AA959;
      text-decoration: none;
      font-weight: 500;
      margin-bottom: 30px;
      padding: 10px 15px;
      border-radius: 8px;
      background: white;
      box-shadow: 0 2px 10px rgba(122, 169, 89, 0.1);
      transition: all 0.3s ease;
    }

    .back-link:hover {
      background: #E8F4E0;
      transform: translateX(-5px);
    }

    .header {
      text-align: center;
      margin-bottom: 40px;
    }

    .header h1 {
      color: #2d3748;
      font-size: 2.5rem;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
    }

    .header p {
      color: #718096;
      font-size: 1.1rem;
    }

    .form-container {
      background: white;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      border: 1px solid #E8F4E0;
    }

    .form-group {
      margin-bottom: 25px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #2d3748;
    }

    .form-control {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #E8F4E0;
      border-radius: 12px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: #f8fafc;
    }

    .form-control:focus {
      outline: none;
      border-color: #7AA959;
      background: white;
      box-shadow: 0 0 0 3px rgba(122, 169, 89, 0.1);
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    textarea.form-control {
      min-height: 120px;
      resize: vertical;
    }

    .form-actions {
      display: flex;
      gap: 15px;
      justify-content: flex-end;
      margin-top: 40px;
      padding-top: 30px;
      border-top: 2px solid #E8F4E0;
    }

    .btn {
      padding: 12px 30px;
      border: none;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
    }

    .btn-primary {
      background: linear-gradient(135deg, #7AA959, #93C572);
      color: white;
      box-shadow: 0 4px 15px rgba(122, 169, 89, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(122, 169, 89, 0.4);
    }

    .btn-secondary {
      background: #718096;
      color: white;
    }

    .btn-secondary:hover {
      background: #4a5568;
      transform: translateY(-2px);
    }

    .alert {
      padding: 15px 20px;
      border-radius: 12px;
      margin-bottom: 25px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 12px;
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
      font-size: 1.2rem;
    }

    @media (max-width: 768px) {
      .form-row {
        grid-template-columns: 1fr;
      }
      
      .form-actions {
        flex-direction: column;
      }
      
      .btn {
        width: 100%;
        justify-content: center;
      }
      
      .form-container {
        padding: 25px;
      }
      
      .header h1 {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="listerprojet.php" class="back-link">
      <i class="fas fa-arrow-left"></i>
      Retour à la liste des projets
    </a>

    <div class="header">
      <h1><i class="fas fa-edit"></i> Modifier le Projet</h1>
      <p>Mettez à jour les informations du projet</p>
    </div>

    <div class="form-container">
      <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'error'; ?>">
          <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <?php if ($projet): ?>
        <form method="POST" action="">
          <div class="form-group">
            <label for="titre">Titre</label>
            <input type="text" id="titre" name="titre" class="form-control" value="<?php echo htmlspecialchars($projet['titre'] ?? ''); ?>" required>
          </div>
          
          <div class="form-group">
            <label for="association">Association</label>
            <select id="association" name="association" class="form-control" required>
              <option value="">Sélectionner une association</option>
              <?php foreach($associations as $association): ?>
                <option value="<?php echo $association['id']; ?>" 
                  <?php echo (($projet['association'] ?? '') == $association['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($association['first_name'] . ' ' . $association['last_name'] . ' (' . $association['email'] . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="lieu">Lieu</label>
            <input type="text" id="lieu" name="lieu" class="form-control" value="<?php echo htmlspecialchars($projet['lieu'] ?? ''); ?>" required>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="date_debut">Date de début</label>
              <input type="date" id="date_debut" name="date_debut" class="form-control" value="<?php echo $projet['date_debut'] ?? ''; ?>" required>
            </div>
            
            <div class="form-group">
              <label for="date_fin">Date de fin</label>
              <input type="date" id="date_fin" name="date_fin" class="form-control" value="<?php echo $projet['date_fin'] ?? ''; ?>">
            </div>
          </div>
          
          <div class="form-group">
            <label for="disponibilite">Disponibilité</label>
            <select id="disponibilite" name="disponibilite" class="form-control" required>
              <option value="">Sélectionner</option>
              <option value="disponible" <?php echo (($projet['disponibilite'] ?? '') == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
              <option value="complet" <?php echo (($projet['disponibilite'] ?? '') == 'complet') ? 'selected' : ''; ?>>Complet</option>
              <option value="termine" <?php echo (($projet['disponibilite'] ?? '') == 'termine') ? 'selected' : ''; ?>>Terminé</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($projet['description'] ?? ''); ?></textarea>
          </div>
          
          <div class="form-group">
            <label for="categorie">Catégorie</label>
            <select id="categorie" name="categorie" class="form-control" required>
              <option value="">Sélectionner</option>
              <option value="Solidarité" <?php echo (($projet['categorie'] ?? '') == 'Solidarité') ? 'selected' : ''; ?>>Solidarité</option>
              <option value="Environement" <?php echo (($projet['categorie'] ?? '') == 'Environement') ? 'selected' : ''; ?>>Environement</option>
              <option value="Education" <?php echo (($projet['categorie'] ?? '') == 'Education') ? 'selected' : ''; ?>>Education</option>
              <option value="Sante" <?php echo (($projet['categorie'] ?? '') == 'Sante') ? 'selected' : ''; ?>>Sante</option>
              <option value="Aide" <?php echo (($projet['categorie'] ?? '') == 'Aide') ? 'selected' : ''; ?>>Aide</option>
              <option value="Culture" <?php echo (($projet['categorie'] ?? '') == 'Culture') ? 'selected' : ''; ?>>Culture</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="created_by">Créé par</label>
            <select id="created_by" name="created_by" class="form-control" required>
              <option value="">Sélectionner un utilisateur</option>
              <?php foreach($users as $user): ?>
                <option value="<?php echo $user['id']; ?>" 
                  <?php echo (($projet['created_by'] ?? '') == $user['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-actions">
            <a href="listerprojet.php" class="btn btn-secondary">
              <i class="fas fa-times"></i>
              Annuler
            </a>
            <button type="submit" name="modifier_projet" class="btn btn-primary">
              <i class="fas fa-save"></i>
              Modifier le projet
            </button>
          </div>
        </form>
      <?php else: ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-triangle"></i>
          Projet non trouvé. Veuillez vérifier l'identifiant du projet.
        </div>
      <?php endif; ?>
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
            dateFin.value = this.value;
          }
        });
        
        dateFin.addEventListener('change', function() {
          if (dateDebut.value && this.value < dateDebut.value) {
            alert('La date de fin ne peut pas être avant la date de début');
            this.value = dateDebut.value;
          }
        });
      }

      // Validation du formulaire
      const form = document.querySelector('form');
      form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
          if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = '#dc3545';
          } else {
            field.style.borderColor = '#E8F4E0';
          }
        });
        
        if (!isValid) {
          e.preventDefault();
          alert('Veuillez remplir tous les champs obligatoires');
        }
      });
    });
  </script>
</body>
</html>