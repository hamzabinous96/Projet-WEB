<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../CONTROLLER/ProjectController.php');

if (isset($_GET['project_id'])) {
    $projectController = new ProjectController();
    $projectId = $_GET['project_id'];
    
    // Utiliser la méthode qui existe pour récupérer les participants
    $participants = $projectController->getProjectParticipants($projectId);
    
    if (empty($participants)) {
        // Si getProjectParticipants ne retourne rien, utiliser getTasksByProject
        $tasks = $projectController->getTasksByProject($projectId);
        $participants = [];
        
        foreach ($tasks as $task) {
            if (!empty($task['assignee']) && !empty($task['first_name'])) {
                $participants[] = [
                    'first_name' => $task['first_name'],
                    'last_name' => $task['last_name'],
                    'email' => $task['email'] ?? 'Non spécifié'
                ];
            }
        }
    }
    
    if (count($participants) > 0) {
        foreach ($participants as $participant) {
            echo '<div class="participant-card">';
            echo '<div class="participant-info">';
            echo '<strong>' . htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']) . '</strong>';
            echo '<br><small>' . htmlspecialchars($participant['email']) . '</small>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="no-data">Aucun participant trouvé</div>';
    }
} else {
    echo '<div class="no-data">ID de projet non spécifié</div>';
}
?>