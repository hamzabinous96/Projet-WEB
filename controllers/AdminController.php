<?php
session_start();
require_once 'config/database.php';
require_once 'models/User.php';

class AdminController {
    private $db;
    private $user;

    public function __construct() {
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
            header("Location: index.php?action=login");
            exit();
        }

        $database = new Config();
        $this->db = $database->getConnexion();
        $this->user = new User($this->db);
    }

    public function dashboard() {
        $stmt = $this->user->readAll();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include 'views/BackOffice/dashboard.php';
    }

    public function users() {
        $stmt = $this->user->readAll();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include 'views/BackOffice/users.php';
    }

    public function blockUser() {
        if(isset($_GET['id'])) {
            $this->user->id = $_GET['id'];
            $this->user->status = 'banned';

            if($this->user->updateStatus()) {
                $_SESSION['success'] = "User blocked successfully!";
            } else {
                $_SESSION['error'] = "Failed to block user!";
            }
        }
        $redirect = (isset($_GET['from']) && $_GET['from'] === 'users') ? 'admin_users' : 'admin_dashboard';
        header("Location: index.php?action=" . $redirect);
        exit();
    }

    public function unblockUser() {
        if(isset($_GET['id'])) {
            $this->user->id = $_GET['id'];
            $this->user->status = 'active';

            if($this->user->updateStatus()) {
                $_SESSION['success'] = "User unblocked successfully!";
            } else {
                $_SESSION['error'] = "Failed to unblock user!";
            }
        }
        $redirect = (isset($_GET['from']) && $_GET['from'] === 'users') ? 'admin_users' : 'admin_dashboard';
        header("Location: index.php?action=" . $redirect);
        exit();
    }

    public function deleteUser() {
        if(isset($_GET['id'])) {
            $this->user->id = $_GET['id'];

            if($this->user->delete()) {
                $_SESSION['success'] = "User deleted successfully!";
            } else {
                $_SESSION['error'] = "Failed to delete user!";
            }
        }
        $redirect = (isset($_GET['from']) && $_GET['from'] === 'users') ? 'admin_users' : 'admin_dashboard';
        header("Location: index.php?action=" . $redirect);
        exit();
    }
}
?>