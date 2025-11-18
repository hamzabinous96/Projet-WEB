<?php
/**
 * Contrôleur Like - Gestion des likes
 */
class LikeController {
    private $modeleLike;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->modeleLike = new Like();
    }
    
    /**
     * Bascule l'état du like (ajouter ou retirer)
     */
    public function basculerLike() {
        if (isset($_GET['id_publication']) && isset($_SESSION['id_utilisateur'])) {
            $idPublication = $_GET['id_publication'];
            $idUtilisateur = $_SESSION['id_utilisateur'];
            
            $this->modeleLike->idPublication = $idPublication;
            $this->modeleLike->idUtilisateur = $idUtilisateur;
            
            if ($this->modeleLike->aLike()) {
                // L'utilisateur a déjà liké, on retire le like
                if ($this->modeleLike->retirer()) {
                    $_SESSION['succes'] = 'J\'aime retiré.';
                } else {
                    $_SESSION['erreur'] = 'Erreur lors du retrait du J\'aime.';
                }
            } else {
                // L'utilisateur n'a pas liké, on ajoute le like
                if ($this->modeleLike->ajouter()) {
                    $_SESSION['succes'] = 'J\'aime ajouté.';
                } else {
                    $_SESSION['erreur'] = 'Erreur lors de l\'ajout du J\'aime.';
                }
            }
            
            // Redirection vers le fil d'actualité
            header('Location: index.php?action=filActualite');
            exit();
        }
    }
}
?>
