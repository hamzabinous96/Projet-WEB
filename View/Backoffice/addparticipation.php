<?php
// Correct paths for your structure
$baseDir = __DIR__ . '/../..';

// Debug: Check if files exist
$configPath = $baseDir . '/config.php';
$controllerPath = $baseDir . '/Controller/ParticipationController.php';

if (!file_exists($configPath)) {
    die("Config file not found at: " . $configPath);
}
if (!file_exists($controllerPath)) {
    die("Controller file not found at: " . $controllerPath);
}

require_once $configPath;
require_once $controllerPath;

// Initialize controller
try {
    $controller = new ParticipationController();
} catch (Error $e) {
    die("Error creating controller: " . $e->getMessage());
}

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->create();
    
    if (isset($result['success'])) {
        $message = $result['success'];
        $messageType = 'success';
        // Clear form data after successful submission
        $_POST = [];
    } else {
        $message = $result['error'];
        $messageType = 'error';
    }
}

// Define categories directly (bypass database for now)
$categories = [
    'Technology & Innovation',
    'Education & Learning',
    'Health & Wellness',
    'Arts & Culture',
    'Business & Entrepreneurship',
    'Environment & Sustainability',
    'Community Development',
    'Sports & Recreation',
    'Science & Research',
    'Social Services',
    'Youth Development',
    'Women Empowerment',
    'Volunteer Programs',
    'Professional Development'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Participation</title>
    <!-- Your CSS styles remain the same -->
    <style>
        /* Your existing CSS styles */
    </style>
</head>
<body>
    <div class="container fade-in">
        <h1>Add New Participation</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="titre">Title <span class="required">*</span></label>
                <input type="text" id="titre" name="titre" value="<?php echo isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : ''; ?>" required placeholder="Enter participation title">
            </div>

            <div class="form-group">
                <label for="auteur">Author <span class="required">*</span></label>
                <input type="text" id="auteur" name="auteur" value="<?php echo isset($_POST['auteur']) ? htmlspecialchars($_POST['auteur']) : ''; ?>" required placeholder="Enter author name">
            </div>

            <div class="form-group">
                <label for="categorie">Category <span class="required">*</span></label>
                <select id="categorie" name="categorie" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>" 
                            <?php echo (isset($_POST['categorie']) && $_POST['categorie'] === $category) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn">
                Create Participation
            </button>
        </form>

        <div class="form-footer">
            <a href="participationlist.php">← Back to Participations List</a>
        </div>
    </div>

    <script>
        // Your existing JavaScript
    </script>
</body>
</html>