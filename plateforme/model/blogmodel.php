<?php
// récupérer les données nécessaires au blog//
require_once 'config/database.php';

class BlogModel {
    private $db;
    private $table_blogs = 'blogs';
    private $table_comments = 'commentaires';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getArticles($limit = 10) {
        $query = "SELECT * FROM " . $this->table_blogs . " 
                  WHERE est_public_blog = 1 
                  ORDER BY date_creation_blog DESC 
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArticleById($id) {
        $query = "SELECT * FROM " . $this->table_blogs . " WHERE id_blog = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCommentsByArticle($article_id) {
        $query = "SELECT * FROM " . $this->table_comments . " 
                  WHERE id_blog = :article_id 
                  AND statut_comment = 'approuve'
                  ORDER BY date_creation_comment DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countComments($article_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_comments . " 
                  WHERE id_blog = :article_id 
                  AND statut_comment = 'approuve'";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getRecentArticles($limit = 3) {
        return $this->getArticles($limit);
    }
}
?>