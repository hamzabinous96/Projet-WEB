<?php
// Correct paths for your structure
$baseDir = __DIR__ . '/../..'; // Go up two levels from Backoffice to reach tasnim folder

require_once $baseDir . '/config.php';
require_once $baseDir . '/Controller/ParticipationController.php';

// Initialize controller
$controller = new ParticipationController();

// Handle delete action
if (isset($_GET['id'])) {
    $result = $controller->delete($_GET['id']);
    
    if (isset($result['success'])) {
        header('Location: index.php?message=' . urlencode($result['success']) . '&type=success');
    } else {
        header('Location: index.php?message=' . urlencode($result['error']) . '&type=error');
    }
    exit();
} else {
    // If no ID provided, redirect back
    header('Location: index.php?message=' . urlencode('No participation ID provided') . '&type=error');
    exit();
}
?>