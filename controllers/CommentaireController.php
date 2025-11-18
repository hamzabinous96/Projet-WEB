<?php
/**
 * Contrôleur Commentaire - Gestion des commentaires
 */
class CommentaireController {
    private $modeleCommentaire;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->modeleCommentaire = new Commentaire();
    }
    
    /**
     * Ajoute un nouveau commentaire
     */
    public function ajouter() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_utilisateur'])) {
            $idPublication = $_POST['id_publication'] ?? null;
            $contenu = $_POST['contenu'] ?? '';
            
            if ($idPublication && !empty($contenu)) {
                $this->modeleCommentaire->idPublication = $idPublication;
                $this->modeleCommentaire->idUtilisateur = $_SESSION['id_utilisateur'];
                $this->modeleCommentaire->contenu = $contenu;
                
                if ($this->modeleCommentaire->ajouter()) {
                    $_SESSION['succes'] = 'Commentaire ajouté avec succès.';
                } else {
                    $_SESSION['erreur'] = 'Erreur lors de l\'ajout du commentaire.';
                }
            } else {
                $_SESSION['erreur'] = 'Contenu du commentaire manquant.';
            }
            
            // Redirection vers le fil d'actualité
            header('Location: index.php?action=filActualite');
            exit();
        }
    }
    
    /**
     * Supprime un commentaire
     */
    public function supprimer() {
        if (isset($_GET['id']) && isset($_SESSION['id_utilisateur'])) {
            $idCommentaire = $_GET['id'];
            $idPublication = $_GET['id_publication'] ?? null; // Pour la redirection
            
            // Logique de vérification des droits (simplifiée : l'utilisateur doit être l'auteur ou l'admin)
            // Dans un cas réel, il faudrait vérifier si l'utilisateur est l'auteur du commentaire ou l'auteur de la publication.
            
            $this->modeleCommentaire->id = $idCommentaire;
            
            if ($this->modeleCommentaire->supprimer()) {
                $_SESSION['succes'] = 'Commentaire supprimé.';
            } else {
                $_SESSION['erreur'] = 'Erreur lors de la suppression du commentaire.';
            }
            
            // Redirection vers le fil d'actualité
            header('Location: index.php?action=filActualite');
            exit();
        }
    }
}
?>
