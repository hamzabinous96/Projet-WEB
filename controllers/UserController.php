<?php
class UserController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    public function registerCoach($data) {
        // Check if email already exists
        if ($this->userModel->emailExists($data['email'])) {
            return false;
        }

        $this->userModel->name = $data['name'];
        $this->userModel->email = $data['email'];
        $this->userModel->password = $data['password'];
        $this->userModel->role = 'coach';
        $this->userModel->phone = $data['phone'];
        $this->userModel->bio = $data['bio'];
        
        return $this->userModel->create();
    }
public function loginExistingTeacher($teacher_code, $teacher_name) {
    $query = "SELECT * FROM users WHERE teacher_code = ? AND name = ? AND role = 'coach'";
    $stmt = $this->userModel->conn->prepare($query);
    $stmt->bindParam(1, $teacher_code);
    $stmt->bindParam(2, $teacher_name);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        return $teacher;
    }
    return false;
}
    public function getAllUsers() {
        return $this->userModel->readAll();
    }

    public function getUserStats() {
        return $this->userModel->getUsersCount();
    }

    public function getUser($id) {
        return $this->userModel->getUserById($id);
    }

    public function getCoachesByCourse($course_id) {
        return $this->userModel->getCoachesByCourse($course_id);
    }
}
?>