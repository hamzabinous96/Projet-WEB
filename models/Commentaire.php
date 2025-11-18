<?php
/**
 * Modèle Commentaire - Gestion des commentaires
 */
class Commentaire {
    private $connexion;
    private $table = 'commentaires';
    
    // Propriétés du commentaire
    public $id;
    public $idPublication;
    public $idUtilisateur;
    public $contenu;
    public $dateCreation;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->connexion = Database::obtenirConnexion();
    }
    
    /**
     * CREATE - Ajouter un commentaire
     * @return bool Succès de l'ajout
     */
    public function ajouter() {
        $requete = "INSERT INTO " . $this->table . " 
                    (id_publication, id_utilisateur, contenu, date_creation) 
                    VALUES (:id_pub, :id_user, :contenu, NOW())";
        
        $stmt = $this->connexion->prepare($requete);
        
        // Nettoyage du contenu
        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        
        // Liaison des paramètres
        $stmt->bindParam(':id_pub', $this->idPublication);
        $stmt->bindParam(':id_user', $this->idUtilisateur);
        $stmt->bindParam(':contenu', $this->contenu);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    /**
     * READ - Lire les commentaires d'une publication
     * @return PDOStatement Résultat de la requête
     */
    public function lireParPublication() {
        $requete = "SELECT 
                        c.*,
                        u.nom_utilisateur,
                        u.avatar
                    FROM " . $this->table . " c
                    LEFT JOIN utilisateurs u ON c.id_utilisateur = u.id
                    WHERE c.id_publication = :id_pub
                    ORDER BY c.date_creation ASC";
        
        $stmt = $this->connexion->prepare($requete);
        $stmt->bindParam(':id_pub', $this->idPublication);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * DELETE - Supprimer un commentaire
     * @return bool Succès de la suppression
     */
    public function supprimer() {
        $requete = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->connexion->prepare($requete);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
