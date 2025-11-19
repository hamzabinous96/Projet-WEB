<?php
class Chat {
    private $conn;
    private $table_name = "messages";

    public $id;
    public $sender_id;
    public $receiver_id;
    public $course_id;
    public $message;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET sender_id=:sender_id, receiver_id=:receiver_id, 
                    course_id=:course_id, message=:message";
        
        $stmt = $this->conn->prepare($query);
        
        $this->message = htmlspecialchars(strip_tags($this->message));
        
        $stmt->bindParam(":sender_id", $this->sender_id);
        $stmt->bindParam(":receiver_id", $this->receiver_id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":message", $this->message);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getConversation($user1_id, $user2_id, $course_id) {
        $query = "SELECT m.*, u.name as sender_name 
                 FROM " . $this->table_name . " m
                 LEFT JOIN users u ON m.sender_id = u.id
                 WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR 
                        (m.sender_id = ? AND m.receiver_id = ?)) 
                 AND m.course_id = ?
                 ORDER BY m.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user1_id);
        $stmt->bindParam(2, $user2_id);
        $stmt->bindParam(3, $user2_id);
        $stmt->bindParam(4, $user1_id);
        $stmt->bindParam(5, $course_id);
        $stmt->execute();
        
        return $stmt;
    }

    public function getUserConversations($user_id) {
        $query = "SELECT DISTINCT 
                    CASE 
                        WHEN m.sender_id = ? THEN m.receiver_id 
                        ELSE m.sender_id 
                    END as other_user_id,
                    u.name as other_user_name,
                    c.name as course_name,
                    MAX(m.created_at) as last_message_time
                 FROM " . $this->table_name . " m
                 LEFT JOIN users u ON (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) = u.id
                 LEFT JOIN courses c ON m.course_id = c.id
                 WHERE m.sender_id = ? OR m.receiver_id = ?
                 GROUP BY other_user_id, course_name
                 ORDER BY last_message_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $user_id);
        $stmt->bindParam(3, $user_id);
        $stmt->bindParam(4, $user_id);
        $stmt->execute();
        
        return $stmt;
    }
}
?>