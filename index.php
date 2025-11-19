<?php
session_start();
require_once 'models/Database.php';
require_once 'models/User.php';
require_once 'models/Course.php';
require_once 'models/Material.php';
require_once 'models/Chat.php';
require_once 'controllers/UserController.php';
require_once 'controllers/CourseController.php';
require_once 'controllers/MaterialController.php';
require_once 'controllers/ChatController.php';

$database = new DatabaseConnection();
$db = $database->conn;

$userController = new UserController($db);
$courseController = new CourseController($db);

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['register_coach'])) {
        if ($userController->registerCoach($_POST)) {
            $_SESSION['user_id'] = $db->lastInsertId();
            $_SESSION['user_role'] = 'coach';
            $_SESSION['user_name'] = $_POST['name'];
            header("Location: index.php?page=coach_profile");
            exit;
        } else {
            header("Location: index.php?page=coach_registration&error=email_exists");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeConnect - Learning Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'views/frontoffice/header.php'; ?>
    
    <main>
        <?php
        // FIXED: Use the correct variable in the switch statement
        switch($page) {
            case 'home':
                include 'views/frontoffice/home.php';
                break;
            case 'role_selection':
                include 'views/frontoffice/role_selection.php';
                break;
            case 'courses':
                include 'views/frontoffice/courses.php';
                break;
            case 'course_coaches':
                include 'views/frontoffice/course_coaches.php';
                break;
            case 'coach_registration':
                include 'views/frontoffice/coach_registration.php';
                break;
            case 'coach_profile':
                include 'views/frontoffice/coach_profile.php';
                break;
            case 'materials':
                include 'views/frontoffice/materials.php';
                break;
            case 'chat':
                include 'views/frontoffice/chat.php';
                break;
            case 'add_material':
                include 'views/frontoffice/add_material.php';
                break;
            case 'our_coaches':
    include 'views/frontoffice/our_coaches.php';
    break;
case 'teacher_assignment':
    include 'views/frontoffice/teacher_assignment.php';
    break;
            default:
                include 'views/frontoffice/home.php';
        }
        ?>
    </main>
    
    <?php include 'views/frontoffice/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>