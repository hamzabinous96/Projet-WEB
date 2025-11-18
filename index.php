<?php
/**
 * Front Controller - Point d'entrée unique de l'application
 * Gère le routage et l'instanciation des contrôleurs
 */

// Démarrage de la session
session_start();

// Chargement de la configuration
require_once 'config/database.php';

// Chargement des modèles
require_once 'models/Utilisateur.php';
require_once 'models/Publication.php';
require_once 'models/Commentaire.php';
require_once 'models/Like.php';

// Chargement des contrôleurs
require_once 'controllers/UtilisateurController.php';
require_once 'controllers/PublicationController.php';
require_once 'controllers/CommentaireController.php';
require_once 'controllers/LikeController.php';

// Récupération des paramètres de route
$action = isset($_GET['action']) ? $_GET['action'] : 'accueil';
$controleur = isset($_GET['controleur']) ? $_GET['controleur'] : 'utilisateur';

// Routage selon le contrôleur demandé
switch ($controleur) {
    case 'utilisateur':
        $ctrl = new UtilisateurController();
        break;
    case 'publication':
        $ctrl = new PublicationController();
        break;
    case 'commentaire':
        $ctrl = new CommentaireController();
        break;
    case 'like':
        $ctrl = new LikeController();
        break;
    default:
        $ctrl = new UtilisateurController();
}

// Exécution de l'action demandée
switch ($action) {
    // --- Actions Utilisateur ---
    case 'accueil':
    case 'connexion':
        $ctrl->afficherConnexion();
        break;
    case 'traiterConnexion':
        $ctrl->traiterConnexion();
        break;
    case 'inscription':
        $ctrl->afficherInscription();
        break;
    case 'traiterInscription':
        $ctrl->traiterInscription();
        break;
    case 'deconnexion':
        $ctrl->deconnecter();
        break;
    case 'profil':
        $ctrl->afficherProfil();
        break;
    case 'modifierProfil':
        $ctrl->modifierProfil();
        break;
    
    // --- Actions Publication (FrontOffice) ---
    case 'filActualite':
        $pubCtrl = new PublicationController();
        $pubCtrl->afficherFilActualite();
        break;
    case 'creerPublication':
        $pubCtrl = new PublicationController();
        $pubCtrl->creer();
        break;
    case 'supprimerPublication':
        $pubCtrl = new PublicationController();
        $pubCtrl->supprimer();
        break;
    
    // --- Actions Commentaire ---
    case 'ajouterCommentaire':
        $commCtrl = new CommentaireController();
        $commCtrl->ajouter();
        break;
    case 'supprimerCommentaire':
        $commCtrl = new CommentaireController();
        $commCtrl->supprimer();
        break;
    
    // --- Actions Like ---
    case 'toggleLike':
        $likeCtrl = new LikeController();
        $likeCtrl->basculerLike();
        break;
    
    // --- BackOffice ---
    case 'dashboard':
        require_once 'views/backoffice/dashboard.php';
        break;
    case 'adminUtilisateurs':
        $ctrl->gestionAdmin();
        break;
    case 'adminPublications':
        $pubCtrl = new PublicationController();
        $pubCtrl->gestionAdmin();
        break;
    case 'supprimerUtilisateur':
        $ctrl->supprimerAdmin();
        break;
    
    default:
        $ctrl->afficherConnexion();
}
?>
