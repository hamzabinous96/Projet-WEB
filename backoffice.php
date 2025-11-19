<?php
session_start();
require_once 'models/Database.php';
require_once 'models/User.php';
require_once 'models/Course.php';
require_once 'controllers/UserController.php';
require_once 'controllers/CourseController.php';

$database = new DatabaseConnection();
$db = $database->conn;

$userController = new UserController($db);
$courseController = new CourseController($db);

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Handle course operations
if($_POST) {
    if(isset($_POST['add_course'])) {
        if($courseController->createCourse($_POST)) {
            header("Location: backoffice.php?page=courses&message=Course added successfully");
            exit;
        }
    }
    
    if(isset($_POST['edit_course'])) {
        if($courseController->updateCourse($_POST['id'], $_POST)) {
            header("Location: backoffice.php?page=courses&message=Course updated successfully");
            exit;
        }
    }
}

if(isset($_GET['delete_course'])) {
    if($courseController->deleteCourse($_GET['delete_course'])) {
        header("Location: backoffice.php?page=courses&message=Course deleted successfully");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office - Training Platform</title>
    <link rel="stylesheet" href="assets/css/backoffice.css">
</head>
<body>
    <div class="backoffice-container">
        <?php include 'views/backoffice/sidebar.php'; ?>
        
        <main class="main-content">
            <?php
            switch($page) {
                case 'dashboard':
                    include 'views/backoffice/dashboard.php';
                    break;
                case 'courses':
                    include 'views/backoffice/courses_list.php';
                    break;
                case 'add_course':
                    include 'views/backoffice/add_course.php';
                    break;
                case 'edit_course':
                    include 'views/backoffice/edit_course.php';
                    break;
                case 'users':
                    include 'views/backoffice/users.php';
                    break;
                case 'statistics':
                    include 'views/backoffice/statistics.php';
                    break;
                default:
                    include 'views/backoffice/dashboard.php';
            }
            ?>
        </main>
    </div>
    
    <script src="assets/js/backoffice.js"></script>
</body>
</html>