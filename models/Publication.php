<?php
/**
 * Modèle Publication - Gestion des publications (posts)
 */
class Publication {
    private $connexion;
    private $table = 'publications';
    
    // Propriétés de la publication
    public $id;
    public $idUtilisateur;
    public $contenu;
    public $image;
    public $dateCreation;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->connexion = Database::obtenirConnexion();
    }
    
    /**
     * CREATE - Créer une nouvelle publication
     * @return bool Succès de la création
     */
    public function creer() {
        $requete = "INSERT INTO " . $this->table . " 
                    (id_utilisateur, contenu, image, date_creation) 
                    VALUES (:id_user, :contenu, :image, NOW())";
        
        $stmt = $this->connexion->prepare($requete);
        
        // Nettoyage des données
        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        $this->image = htmlspecialchars(strip_tags($this->image));
        
        // Liaison des paramètres
        $stmt->bindParam(':id_user', $this->idUtilisateur);
        $stmt->bindParam(':contenu', $this->contenu);
        $stmt->bindParam(':image', $this->image);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    /**
     * READ - Lire toutes les publications avec les infos utilisateur
     * @return PDOStatement Résultat de la requête
     */
    public function lireToutesAvecUtilisateur() {
        $requete = "SELECT 
                        p.*, 
                        u.nom_utilisateur, 
                        u.avatar,
                        (SELECT COUNT(*) FROM likes l WHERE l.id_publication = p.id) as total_likes,
                        (SELECT COUNT(*) FROM commentaires c WHERE c.id_publication = p.id) as total_commentaires
                    FROM " . $this->table . " p
                    LEFT JOIN utilisateurs u ON p.id_utilisateur = u.id
                    ORDER BY p.date_creation DESC";
        
        $stmt = $this->connexion->prepare($requete);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * READ ONE - Lire une publication par ID
     * @return bool Succès de la lecture
     */
    public function lireUn() {
        $requete = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->connexion->prepare($requete);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $ligne = $stmt->fetch();
        if ($ligne) {
            $this->idUtilisateur = $ligne['id_utilisateur'];
            $this->contenu = $ligne['contenu'];
            $this->image = $ligne['image'];
            $this->dateCreation = $ligne['date_creation'];
            return true;
        }
        return false;
    }
    
    /**
     * DELETE - Supprimer une publication
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
    
    /**
     * Compter le nombre total de publications
     * @return int Nombre de publications
     */
    public function compter() {
        $requete = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->connexion->prepare($requete);
        $stmt->execute();
        $ligne = $stmt->fetch();
        return $ligne['total'];
    }
}
?>
