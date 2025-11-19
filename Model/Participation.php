<?php
namespace Model;

class Participation {
    private $db;
    
    public function __construct() {
        global $pdo;
        $this->db = $pdo;
    }
    
    public function create($titre, $auteur, $categorie) {
        try {
            // changed: use consistent lowercase table name
            $sql = "INSERT INTO participation (titre, auteur, categorie, date_creation) VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$titre, $auteur, $categorie]);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function getAll() {
        try {
            $sql = "SELECT * FROM participation ORDER BY date_creation DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function getById($id) {
        try {
            $sql = "SELECT * FROM participation WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function update($id, $titre, $auteur, $categorie) {
        try {
            $sql = "UPDATE participation SET titre = ?, auteur = ?, categorie = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$titre, $auteur, $categorie, $id]);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            $sql = "DELETE FROM participation WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
}

// provide backward-compatible aliases so controllers that expect different class names still work
namespace {
    if (!class_exists('Participation')) {
        class_alias('Model\Participation', 'Participation');
    }
    if (!class_exists('App\Model\Participation')) {
        class_alias('Model\Participation', 'App\Model\Participation');
    }
}
?>