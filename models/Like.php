<?php
/**
 * Modèle Like - Gestion des likes
 */
class Like {
    private $connexion;
    private $table = 'likes';
    
    // Propriétés du like
    public $id;
    public $idPublication;
    public $idUtilisateur;
    public $dateCreation;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->connexion = Database::obtenirConnexion();
    }
    
    /**
     * CREATE - Ajouter un like
     * @return bool Succès de l'ajout
     */
    public function ajouter() {
        $requete = "INSERT INTO " . $this->table . " 
                    (id_publication, id_utilisateur, date_creation) 
                    VALUES (:id_pub, :id_user, NOW())";
        
        $stmt = $this->connexion->prepare($requete);
        
        // Liaison des paramètres
        $stmt->bindParam(':id_pub', $this->idPublication);
        $stmt->bindParam(':id_user', $this->idUtilisateur);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    /**
     * DELETE - Retirer un like
     * @return bool Succès de la suppression
     */
    public function retirer() {
        $requete = "DELETE FROM " . $this->table . " 
                    WHERE id_publication = :id_pub AND id_utilisateur = :id_user";
        
        $stmt = $this->connexion->prepare($requete);
        $stmt->bindParam(':id_pub', $this->idPublication);
        $stmt->bindParam(':id_user', $this->idUtilisateur);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    /**
     * Vérifier si l'utilisateur a déjà liké
     * @return bool A liké ou non
     */
    public function aLike() {
        $requete = "SELECT id FROM " . $this->table . " 
                    WHERE id_publication = :id_pub AND id_utilisateur = :id_user 
                    LIMIT 1";
        
        $stmt = $this->connexion->prepare($requete);
        $stmt->bindParam(':id_pub', $this->idPublication);
        $stmt->bindParam(':id_user', $this->idUtilisateur);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}
?>
