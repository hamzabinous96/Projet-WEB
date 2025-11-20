<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

if (isset($_GET['project_id'])) {
    $projectController = new ProjectController();
    $taches = $projectController->getTachesByProject($_GET['project_id']);
    
    if (count($taches) > 0) {
        echo '<ul class="taches-list">';
        foreach ($taches as $tache) {
            $statusClass = 'status-' . $tache['status'];
            $assignee = $tache['assignee_nom'] ?? 'Non assigné';
            $description = $tache['description'] ?: 'Aucune description';
            
            echo '<li class="tache-item-details">';
            echo '<div class="tache-header">';
            echo '<span class="tache-title">' . htmlspecialchars($tache['nom_tache']) . '</span>';
            echo '<span class="tache-status ' . $statusClass . '">' . htmlspecialchars($tache['status']) . '</span>';
            echo '</div>';
            echo '<div class="tache-description">' . htmlspecialchars($description) . '</div>';
            echo '<div class="tache-assignee">Assigné à: ' . htmlspecialchars($assignee) . '</div>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<div class="no-data">Aucune tâche trouvée</div>';
    }
} else {
    echo '<div class="no-data">ID de projet non spécifié</div>';
}
?>