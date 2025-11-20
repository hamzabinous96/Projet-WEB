<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

if (isset($_GET['project_id'])) {
    $projectController = new ProjectController();
    $participants = $projectController->getParticipantsByProject($_GET['project_id']);
    
    if (count($participants) > 0) {
        echo '<ul class="participants-list">';
        foreach ($participants as $participant) {
            echo '<li class="participant-item">';
            echo '<div class="participant-header">';
            echo '<span class="participant-name">' . htmlspecialchars($participant['nom']) . '</span>';
            echo '<span class="participant-email">' . htmlspecialchars($participant['email']) . '</span>';
            echo '</div>';
            echo '<div class="participant-info">';
            echo 'Type: ' . htmlspecialchars($participant['type']) . ' | ';
            echo 'Date d\'inscription: ' . htmlspecialchars($participant['date_inscription']);
            echo '</div>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<div class="no-data">Aucun participant trouvé</div>';
    }
} else {
    echo '<div class="no-data">ID de projet non spécifié</div>';
}
?>