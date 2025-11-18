<?php
/**
 * Contrôleur Publication - Gestion des publications
 */
class PublicationController {
    private $modelePublication;
    private $modeleCommentaire;
    private $modeleLike;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->modelePublication = new Publication();
        $this->modeleCommentaire = new Commentaire();
        $this->modeleLike = new Like();
    }
    
    /**
     * Afficher le fil d'actualité (feed principal)
     */
    public function afficherFilActualite() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['id_utilisateur'])) {
            header('Location: index.php?action=connexion');
            exit();
        }
        
        // Récupérer toutes les publications
        $stmt = $this->modelePublication->lireToutesAvecUtilisateur();
        
        // Inclure la vue du fil d'actualité
        include 'views/frontoffice/fil_actualite.php';
    }
    
    /**
     * Créer une nouvelle publication
     */
    public function creer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_utilisateur'])) {
            // Récupération des données
            $this->modelePublication->idUtilisateur = $_SESSION['id_utilisateur'];
            $this->modelePublication->contenu = $_POST['contenu'];
            $this->modelePublication->image = ''; // À gérer pour l'upload d'images
            
            // Création de la publication
            if ($this->modelePublication->creer()) {
                $_SESSION['succes'] = 'Publication créée avec succès';
            } else {
                $_SESSION['erreur'] = 'Erreur lors de la création';
            }
            
            header('Location: index.php?action=filActualite');
            exit();
        }
    }
    
    /**
     * Supprimer une publication
     */
    public function supprimer() {
        if (isset($_GET['id']) && isset($_SESSION['id_utilisateur'])) {
            $this->modelePublication->id = $_GET['id'];
            
            if ($this->modelePublication->supprimer()) {
                $_SESSION['succes'] = 'Publication supprimée';
            } else {
                $_SESSION['erreur'] = 'Erreur lors de la suppression';
            }
            
            header('Location: index.php?action=filActualite');
            exit();
        }
    }
    
    /**
     * Gestion admin des publications
     */
    public function gestionAdmin() {
        $stmt = $this->modelePublication->lireToutesAvecUtilisateur();
        include 'views/backoffice/gestion_publications.php';
    }
}
?>
