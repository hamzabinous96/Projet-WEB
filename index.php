<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'acceuil';

switch($action) {
    case 'acceuil':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->acceuil();
        break;

    case 'register':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->register();
        break;

    case 'login':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;

    case 'logout':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    case 'admin_dashboard':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->dashboard();
        break;

    case 'admin_users':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->users();
        break;

    case 'block_user':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->blockUser();
        break;

    case 'unblock_user':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->unblockUser();
        break;

    case 'delete_user':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->deleteUser();
        break;

    case 'profile':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        $controller->profile();
        break;

    case 'edit_profile':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        $controller->editProfile();
        break;

    case 'change_password':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        $controller->changePassword();
        break;

    default:
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->acceuil();
        break;
}
?>