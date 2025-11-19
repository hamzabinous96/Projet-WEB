<?php
class Material {
    private $conn;
    private $table_name = "course_materials";

    public $id;
    public $coach_id;
    public $course_id;
    public $title;
    public $material_type;
    public $content;
    public $description;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET coach_id=:coach_id, course_id=:course_id, title=:title, 
                    material_type=:material_type, content=:content, description=:description";
        
        $stmt = $this->conn->prepare($query);
        
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->material_type = htmlspecialchars(strip_tags($this->material_type));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->description = htmlspecialchars(strip_tags($this->description));
        
        $stmt->bindParam(":coach_id", $this->coach_id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":material_type", $this->material_type);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":description", $this->description);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readByCourse($course_id) {
        $query = "SELECT m.*, u.name as coach_name, c.name as course_name 
                 FROM " . $this->table_name . " m
                 LEFT JOIN users u ON m.coach_id = u.id
                 LEFT JOIN courses c ON m.course_id = c.id
                 WHERE m.course_id = ? 
                 ORDER BY m.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $course_id);
        $stmt->execute();
        
        return $stmt;
    }

    public function readByCoach($coach_id) {
        $query = "SELECT m.*, c.name as course_name 
                 FROM " . $this->table_name . " m
                 LEFT JOIN courses c ON m.course_id = c.id
                 WHERE m.coach_id = ? 
                 ORDER BY m.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $coach_id);
        $stmt->execute();
        
        return $stmt;
    }
}
?>