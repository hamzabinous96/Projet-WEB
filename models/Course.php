<?php
class Course {
    private $conn;
    private $table_name = "courses";

    public $id;
    public $name;
    public $description;
    public $category;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->category = $row['category'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET name=:name, description=:description, category=:category";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category = htmlspecialchars(strip_tags($this->category));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category", $this->category);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET name=:name, description=:description, category=:category 
                WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category = htmlspecialchars(strip_tags($this->category));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getCourseCoaches($course_id) {
        $query = "SELECT u.* FROM users u 
                 INNER JOIN coach_courses cc ON u.id = cc.coach_id 
                 WHERE cc.course_id = ? AND u.role = 'coach'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $course_id);
        $stmt->execute();
        
        return $stmt;
    }
}
?>