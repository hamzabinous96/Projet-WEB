<?php
session_start();
require_once 'config/database.php';
require_once 'models/User.php';

class UserController {
    private $db;
    private $user;

    public function __construct() {
        if(!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $database = new Config();
        $this->db = $database->getConnexion();
        $this->user = new User($this->db);
    }

    public function profile() {
        $this->user->id = $_SESSION['user_id'];
        $this->user->readOne();
        include 'views/FrontOffice/profile.php';
    }

    public function editProfile() {
        $this->user->id = $_SESSION['user_id'];
        $this->user->readOne();

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->user->first_name = $_POST['first_name'];
            $this->user->last_name = $_POST['last_name'];
            $this->user->email = $_POST['email'];
            $this->user->phone = $_POST['phone'];

            // Keep existing profile picture
            $old_picture = $this->user->profile_picture;

            // Handle new profile picture upload
            if(isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_picture']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if(in_array(strtolower($filetype), $allowed)) {
                    $newname = uniqid() . '.' . $filetype;
                    $upload_path = 'uploads/profile_pictures/' . $newname;
                    
                    if(move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                        // Delete old picture if exists
                        if($old_picture && file_exists($old_picture)) {
                            unlink($old_picture);
                        }
                        $this->user->profile_picture = $upload_path;
                    }
                }
            } else {
                $this->user->profile_picture = $old_picture;
            }

            if($this->user->update()) {
                $_SESSION['success'] = "Profile updated successfully!";
                $_SESSION['user_name'] = $this->user->first_name . ' ' . $this->user->last_name;
                header("Location: index.php?action=profile");
                exit();
            } else {
                $_SESSION['error'] = "Failed to update profile!";
            }
        }

        include 'views/FrontOffice/edit_profile.php';
    }

    public function changePassword() {
        $this->user->id = $_SESSION['user_id'];

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            // Verify current password
            if(!$this->user->verifyPassword($current_password)) {
                $_SESSION['error'] = "Current password is incorrect!";
                header("Location: index.php?action=change_password");
                exit();
            }

            // Check if new passwords match
            if($new_password !== $confirm_password) {
                $_SESSION['error'] = "New passwords do not match!";
                header("Location: index.php?action=change_password");
                exit();
            }

            // Check password length
            if(strlen($new_password) < 6) {
                $_SESSION['error'] = "Password must be at least 6 characters!";
                header("Location: index.php?action=change_password");
                exit();
            }

            $this->user->password = $new_password;
            if($this->user->changePassword()) {
                $_SESSION['success'] = "Password changed successfully!";
                header("Location: index.php?action=profile");
                exit();
            } else {
                $_SESSION['error'] = "Failed to change password!";
                header("Location: index.php?action=change_password");
                exit();
            }
        }

        include 'views/FrontOffice/change_password.php';
    }
}
?>