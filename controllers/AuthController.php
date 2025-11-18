<?php
session_start();
require_once 'config/database.php';
require_once 'models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Config();
        $this->db = $database->getConnexion();
        $this->user = new User($this->db);
    }

    public function acceuil() {
        include 'views/FrontOffice/acceuil.php';
    }
    
    public function register() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->user->user_type = $_POST['user_type'];
            $this->user->first_name = $_POST['first_name'];
            
            // Handle last_name based on user_type
            if($_POST['user_type'] === 'association') {
                $this->user->last_name = 'Association';
            } else {
                $this->user->last_name = $_POST['last_name'];
            }
            
            $this->user->email = $_POST['email'];
            $this->user->phone = $_POST['phone'];
            $this->user->password = $_POST['password'];
            $this->user->profile_picture = null;
            $this->user->status = 'active';

            // Handle profile picture upload
            if(isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_picture']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if(in_array(strtolower($filetype), $allowed)) {
                    $newname = uniqid() . '.' . $filetype;
                    $upload_path = 'uploads/profile_pictures/' . $newname;
                    
                    if(move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                        $this->user->profile_picture = $upload_path;
                    }
                }
            }

            // Check if email already exists
            if($this->user->emailExists()) {
                $_SESSION['error'] = "Email already exists!";
                header("Location: index.php?action=register");
                exit();
            }

            if($this->user->create()) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: index.php?action=login");
                exit();
            } else {
                $_SESSION['error'] = "Registration failed!";
                header("Location: index.php?action=register");
                exit();
            }
        } else {
            include 'views/auth/register.php';
        }
    }


    public function login() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->user->email = $_POST['email'];
            $this->user->password = $_POST['password'];

            if($this->user->login()) {
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['user_type'] = $this->user->user_type;
                $_SESSION['user_name'] = $this->user->first_name . ' ' . $this->user->last_name;

                if($this->user->user_type == 'admin') {
                    header("Location: index.php?action=admin_dashboard");
                } else {
                    header("Location: index.php?action=acceuil");
                }
                exit();
            } else {
                $_SESSION['error'] = "Invalid credentials or account banned!";
                header("Location: index.php?action=login");
                exit();
            }
        } else {
            include 'views/auth/login.php';
        }
    }

    public function logout() {
        session_destroy();
        header("Location: index.php?action=login");
        exit();
    }
}
?>