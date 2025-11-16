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
            <input type="text" id="titre" name="titre" class="form-control" value="<?php echo htmlspecialchars($projet['titre'] ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <label for="association">Association</label>
            <select id="association" name="association" class="form-control">
              <option value="">Sélectionner une association</option>
              <?php foreach($associations as $association): ?>
                <option value="<?php echo $association['id']; ?>" 
                  <?php echo (($projet['id_association'] ?? '') == $association['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($association['nom'] . ' (' . $association['email'] . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="lieu">Lieu</label>
            <input type="text" id="lieu" name="lieu" class="form-control" value="<?php echo htmlspecialchars($projet['lieu'] ?? ''); ?>">
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="date_debut">Date de début</label>
              <input type="date" id="date_debut" name="date_debut" class="form-control" value="<?php echo $projet['date_debut'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
              <label for="date_fin">Date de fin</label>
              <input type="date" id="date_fin" name="date_fin" class="form-control" value="<?php echo $projet['date_fin'] ?? ''; ?>">
            </div>
          </div>
          
          <div class="form-group">
            <label for="disponibilite">Disponibilité</label>
            <select id="disponibilite" name="disponibilite" class="form-control">
              <option value="">Sélectionner</option>
              <option value="disponible" <?php echo (($projet['disponibilite'] ?? '') == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
              <option value="complet" <?php echo (($projet['disponibilite'] ?? '') == 'complet') ? 'selected' : ''; ?>>Complet</option>
              <option value="termine" <?php echo (($projet['disponibilite'] ?? '') == 'termine') ? 'selected' : ''; ?>>Terminé</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="descriptionp">Description</label>
            <textarea id="descriptionp" name="descriptionp" class="form-control"><?php echo htmlspecialchars($projet['descriptionp'] ?? ''); ?></textarea>
          </div>
          
          <div class="form-group">
            <label for="categorie">Catégorie</label>
            <select id="categorie" name="categorie" class="form-control">
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
            <select id="created_by" name="created_by" class="form-control">
              <option value="">Sélectionner un administrateur</option>
              <?php foreach($admins as $admin): ?>
                <option value="<?php echo $admin['id']; ?>" 
                  <?php echo (($projet['created_by'] ?? '') == $admin['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($admin['nom'] . ' (' . $admin['email'] . ')'); ?>
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

  <!-- Script pour debug (à retirer en production) -->
  <script>
    <?php if ($projet): ?>
    console.log('Données du projet:', <?php echo json_encode($projet); ?>);
    <?php endif; ?>
  </script>
</body>
</html>