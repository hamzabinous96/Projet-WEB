<?php
/**
 * Contrôleur Utilisateur - Gestion des utilisateurs (connexion, inscription, profil)
 */
class UtilisateurController {
    private $modeleUtilisateur;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->modeleUtilisateur = new Utilisateur();
    }
    
    /**
     * Affiche la page de connexion
     */
    public function afficherConnexion() {
        include 'views/frontoffice/connexion.php';
    }
    
    /**
     * Traite la tentative de connexion
     */
    public function traiterConnexion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $motDePasse = $_POST['mot_de_passe'] ?? '';
            
            $utilisateur = $this->modeleUtilisateur->authentifier($email, $motDePasse);
            
            if ($utilisateur) {
                // Connexion réussie
                $_SESSION['id_utilisateur'] = $utilisateur['id'];
                $_SESSION['nom_utilisateur'] = $utilisateur['nom_utilisateur'];
                $_SESSION['succes'] = 'Connexion réussie. Bienvenue !';
                header('Location: index.php?action=filActualite');
                exit();
            } else {
                // Échec de la connexion
                $_SESSION['erreur'] = 'Email ou mot de passe incorrect.';
                header('Location: index.php?action=connexion');
                exit();
            }
        }
        header('Location: index.php?action=connexion');
        exit();
    }
    
    /**
     * Affiche la page d'inscription
     */
    public function afficherInscription() {
        include 'views/frontoffice/inscription.php';
    }
    
    /**
     * Traite la tentative d'inscription
     */
    public function traiterInscription() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nomUtilisateur = $_POST['nom_utilisateur'] ?? '';
            $email = $_POST['email'] ?? '';
            $motDePasse = $_POST['mot_de_passe'] ?? '';
            $confirmationMdp = $_POST['confirmation_mdp'] ?? '';
            
            // Validation simple (la validation JS est aussi présente)
            if (empty($nomUtilisateur) || empty($email) || empty($motDePasse) || $motDePasse !== $confirmationMdp) {
                $_SESSION['erreur'] = 'Veuillez remplir tous les champs correctement.';
                header('Location: index.php?action=inscription');
                exit();
            }
            
            if ($this->modeleUtilisateur->emailExiste($email)) {
                $_SESSION['erreur'] = 'Cet email est déjà utilisé.';
                header('Location: index.php?action=inscription');
                exit();
            }
            
            if ($this->modeleUtilisateur->nomUtilisateurExiste($nomUtilisateur)) {
                $_SESSION['erreur'] = 'Ce nom d\'utilisateur est déjà pris.';
                header('Location: index.php?action=inscription');
                exit();
            }
            
            // Création de l'utilisateur
            $this->modeleUtilisateur->nomUtilisateur = $nomUtilisateur;
            $this->modeleUtilisateur->email = $email;
            $this->modeleUtilisateur->motDePasse = $motDePasse;
            
            if ($this->modeleUtilisateur->creer()) {
                $_SESSION['succes'] = 'Inscription réussie. Vous pouvez maintenant vous connecter.';
                header('Location: index.php?action=connexion');
                exit();
            } else {
                $_SESSION['erreur'] = 'Erreur lors de l\'inscription.';
                header('Location: index.php?action=inscription');
                exit();
            }
        }
        header('Location: index.php?action=inscription');
        exit();
    }
    
    /**
     * Déconnecte l'utilisateur
     */
    public function deconnecter() {
        session_destroy();
        header('Location: index.php?action=connexion');
        exit();
    }
    
    /**
     * Affiche le profil de l'utilisateur connecté
     */
    public function afficherProfil() {
        if (!isset($_SESSION['id_utilisateur'])) {
            header('Location: index.php?action=connexion');
            exit();
        }
        
        $this->modeleUtilisateur->id = $_SESSION['id_utilisateur'];
        if ($this->modeleUtilisateur->lireUn()) {
            $utilisateur = [
                'id' => $this->modeleUtilisateur->id,
                'nomUtilisateur' => $this->modeleUtilisateur->nomUtilisateur,
                'email' => $this->modeleUtilisateur->email,
                'biographie' => $this->modeleUtilisateur->biographie,
                'avatar' => $this->modeleUtilisateur->avatar,
                'dateCreation' => $this->modeleUtilisateur->dateCreation
            ];
            include 'views/frontoffice/profil.php';
        } else {
            $_SESSION['erreur'] = 'Profil introuvable.';
            header('Location: index.php?action=filActualite');
            exit();
        }
    }
    
    /**
     * Traite la modification du profil
     */
    public function modifierProfil() {
        if (!isset($_SESSION['id_utilisateur'])) {
            header('Location: index.php?action=connexion');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->modeleUtilisateur->id = $_SESSION['id_utilisateur'];
            $this->modeleUtilisateur->nomUtilisateur = $_POST['nom_utilisateur'] ?? '';
            $this->modeleUtilisateur->email = $_POST['email'] ?? '';
            $this->modeleUtilisateur->biographie = $_POST['biographie'] ?? '';
            
            if ($this->modeleUtilisateur->mettreAJour()) {
                $_SESSION['nom_utilisateur'] = $this->modeleUtilisateur->nomUtilisateur; // Mise à jour de la session
                $_SESSION['succes'] = 'Profil mis à jour avec succès.';
            } else {
                $_SESSION['erreur'] = 'Erreur lors de la mise à jour du profil.';
            }
        }
        header('Location: index.php?action=profil');
        exit();
    }
    
    /**
     * Gestion admin des utilisateurs
     */
    public function gestionAdmin() {
        // Vérification des droits admin (simplifiée ici)
        if (!isset($_SESSION['id_utilisateur'])) {
            header('Location: index.php?action=connexion');
            exit();
        }
        
        $stmt = $this->modeleUtilisateur->lireTous();
        include 'views/backoffice/gestion_utilisateurs.php';
    }
    
    /**
     * Supprime un utilisateur (action admin)
     */
    public function supprimerAdmin() {
        // Vérification des droits admin (simplifiée ici)
        if (!isset($_SESSION['id_utilisateur']) || !isset($_GET['id'])) {
            header('Location: index.php?action=connexion');
            exit();
        }
        
        $this->modeleUtilisateur->id = $_GET['id'];
        if ($this->modeleUtilisateur->supprimer()) {
            $_SESSION['succes'] = 'Utilisateur supprimé avec succès.';
        } else {
            $_SESSION['erreur'] = 'Erreur lors de la suppression de l\'utilisateur.';
        }
        
        header('Location: index.php?action=adminUtilisateurs');
        exit();
    }
}
?>
