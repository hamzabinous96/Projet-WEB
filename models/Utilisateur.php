<?php
/**
 * Modèle Utilisateur - Gestion des utilisateurs
 */
class Utilisateur {
    // Connexion à la base de données
    private $connexion;
    private $table = 'utilisateurs';
    
    // Propriétés de l'utilisateur
    public $id;
    public $nomUtilisateur;
    public $email;
    public $motDePasse;
    public $biographie;
    public $avatar;
    public $dateCreation;
    
    /**
     * Constructeur - Initialise la connexion
     */
    public function __construct() {
        $this->connexion = Database::obtenirConnexion();
    }
    
    /**
     * CREATE - Créer un nouvel utilisateur
     * @return bool Succès de la création
     */
    public function creer() {
        $requete = "INSERT INTO " . $this->table . " 
                    (nom_utilisateur, email, mot_de_passe, date_creation) 
                    VALUES (:nom, :email, :mdp, NOW())";
        
        $stmt = $this->connexion->prepare($requete);
        
        // Nettoyage des données
        $this->nomUtilisateur = htmlspecialchars(strip_tags($this->nomUtilisateur));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->motDePasse = password_hash($this->motDePasse, PASSWORD_DEFAULT);
        
        // Liaison des paramètres
        $stmt->bindParam(':nom', $this->nomUtilisateur);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':mdp', $this->motDePasse);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    /**
     * READ - Lire tous les utilisateurs
     * @return PDOStatement Résultat de la requête
     */
    public function lireTous() {
        $requete = "SELECT * FROM " . $this->table . " ORDER BY date_creation DESC";
        $stmt = $this->connexion->prepare($requete);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * READ ONE - Lire un utilisateur par ID
     * @return bool Succès de la lecture
     */
    public function lireUn() {
        $requete = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->connexion->prepare($requete);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $ligne = $stmt->fetch();
        if ($ligne) {
            $this->nomUtilisateur = $ligne['nom_utilisateur'];
            $this->email = $ligne['email'];
            $this->biographie = $ligne['biographie'];
            $this->avatar = $ligne['avatar'];
            $this->dateCreation = $ligne['date_creation'];
            return true;
        }
        return false;
    }
    
    /**
     * UPDATE - Mettre à jour un utilisateur
     * @return bool Succès de la mise à jour
     */
    public function mettreAJour() {
        $requete = "UPDATE " . $this->table . " 
                    SET nom_utilisateur = :nom, 
                        email = :email,
                        biographie = :bio 
                    WHERE id = :id";
        
        $stmt = $this->connexion->prepare($requete);
        
        // Nettoyage des données
        $this->nomUtilisateur = htmlspecialchars(strip_tags($this->nomUtilisateur));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->biographie = htmlspecialchars(strip_tags($this->biographie));
        
        // Liaison des paramètres
        $stmt->bindParam(':nom', $this->nomUtilisateur);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':bio', $this->biographie);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    /**
     * DELETE - Supprimer un utilisateur
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
     * Authentification - Vérifier les identifiants
     * @param string $email Email de l'utilisateur
     * @param string $motDePasse Mot de passe en clair
     * @return array|false Données de l'utilisateur ou false
     */
    public function authentifier($email, $motDePasse) {
        $requete = "SELECT id, nom_utilisateur, mot_de_passe FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->connexion->prepare($requete);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $utilisateur = $stmt->fetch();
        
        if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            // Mot de passe correct
            return $utilisateur;
        }
        return false;
    }
    
    /**
     * Vérifier si l'email existe déjà
     * @param string $email Email à vérifier
     * @return bool True si l'email existe, false sinon
     */
    public function emailExiste($email) {
        $requete = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->connexion->prepare($requete);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Vérifier si le nom d'utilisateur existe déjà
     * @param string $nomUtilisateur Nom d'utilisateur à vérifier
     * @return bool True si le nom d'utilisateur existe, false sinon
     */
    public function nomUtilisateurExiste($nomUtilisateur) {
        $requete = "SELECT id FROM " . $this->table . " WHERE nom_utilisateur = :nom LIMIT 1";
        $stmt = $this->connexion->prepare($requete);
        $stmt->bindParam(':nom', $nomUtilisateur);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Compter le nombre total d'utilisateurs
     * @return int Nombre d'utilisateurs
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
