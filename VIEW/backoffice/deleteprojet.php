<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

$projectController = new ProjectController();

// Vérifier si l'ID du projet est passé en paramètre
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listerprojet.php");
    exit;
}

$id_projet = $_GET['id'];

// Récupérer les données du projet à supprimer
$projet = $projectController->getProjectById($id_projet);

if (!$projet) {
    header("Location: listerprojet.php?message=Projet non trouvé&message_type=error");
    exit;
}

// Traitement de la suppression
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirmer_suppression'])) {
    if ($projectController->deleteProject($id_projet)) {
        header("Location: listerprojet.php?message=Projet supprimé avec succès&message_type=success");
        exit;
    } else {
        header("Location: listerprojet.php?message=Erreur lors de la suppression du projet&message_type=error");
        exit;
    }
}

// Si annulation, rediriger vers la liste
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['annuler_suppression'])) {
    header("Location: listerprojet.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Panel - Supprimer Projet</title>
  <link rel="stylesheet" href="../style/deleteprojet.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1><i class="fas fa-exclamation-triangle"></i> Confirmation de suppression</h1>
      <p>Action irréversible</p>
    </div>

    <div class="confirmation-content">
      <div class="warning-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>

      <div class="confirmation-message">
        <p>Êtes-vous sûr de vouloir supprimer définitivement ce projet ?</p>
        <p><strong>Cette action ne peut pas être annulée.</strong></p>
      </div>

      <?php if ($projet): ?>
        <div class="project-info">
          <h3>Détails du projet à supprimer :</h3>
          <div class="project-detail">
            <strong>ID :</strong> <?php echo htmlspecialchars($projet['id_projet']); ?>
          </div>
          <div class="project-detail">
            <strong>Nom :</strong> <?php echo htmlspecialchars($projet['titre']); ?>
          </div>
          <div class="project-detail">
            <strong>Association :</strong> <?php echo htmlspecialchars($projet['association_nom'] ?? 'N/A'); ?>
          </div>
          <div class="project-detail">
            <strong>Lieu :</strong> <?php echo htmlspecialchars($projet['lieu'] ?? 'Non spécifié'); ?>
          </div>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="confirmation-buttons">
        <button type="submit" name="annuler_suppression" class="btn btn-secondary">
          <i class="fas fa-times"></i>
          Annuler
        </button>
        <button type="submit" name="confirmer_suppression" class="btn btn-danger">
          <i class="fas fa-trash-alt"></i>
          Oui, supprimer
        </button>
      </form>
    </div>
  </div>
</body>
</html>