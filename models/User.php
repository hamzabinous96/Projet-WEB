<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $phone;
    public $bio;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET name=:name, email=:email, password=:password, role=:role, phone=:phone, bio=:bio";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->bio = htmlspecialchars(strip_tags($this->bio));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":bio", $this->bio);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getCoachesByCourse($course_id) {
        $query = "SELECT u.* FROM users u 
                 INNER JOIN coach_courses cc ON u.id = cc.coach_id 
                 WHERE cc.course_id = ? AND u.role = 'coach'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $course_id);
        $stmt->execute();
        
        return $stmt;
    }

    public function getUsersCount() {
        $query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>