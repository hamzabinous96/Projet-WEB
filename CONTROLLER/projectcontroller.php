<?php
// ProjectController.php
require_once __DIR__ . '/../config.php'; // Goes up one level to config.php
require_once __DIR__ . '/../MODEL/projectmodel.php'; // Goes up one level then to model

class ProjectController {
    private $projectModel;
    
    public function __construct() {
        $this->projectModel = new Project();
    }
    
    // Ajouter un nouveau projet
    public function addProject($titre, $association, $lieu, $date_debut, $date_fin, $disponibilite, $descriptionp, $categorie, $created_by) {
        // Validation des données
        if (empty($titre) || empty($association) || empty($created_by)) {
            return "Erreur: Titre, association et créateur sont obligatoires";
        }
        
        // Validation des dates
        if (!empty($date_debut) && !empty($date_fin) && $date_debut > $date_fin) {
            return "Erreur: La date de début ne peut pas être après la date de fin";
        }
        
        // Vérifier que l'association existe dans la table utilisateurs
        if (!$this->isValidAssociation($association)) {
            return "Erreur: L'association spécifiée n'existe pas";
        }
        
        // Vérifier que l'admin créateur existe
        if (!$this->isValidAdmin($created_by)) {
            return "Erreur: L'administrateur créateur n'existe pas";
        }
        
        return $this->projectModel->addProject(
            $titre, $association, $lieu, $date_debut, $date_fin, 
            $disponibilite, $descriptionp, $categorie, $created_by
        );
    }
    
    // Récupérer tous les projets
    public function getAllProjects() {
        return $this->projectModel->getAllProjects();
    }
    
    // Récupérer un projet par ID
    public function getProjectById($id) {
        if (empty($id)) {
            return null;
        }
        return $this->projectModel->getProjectById($id);
    }
    
    // Mettre à jour un projet
    public function updateProject($id, $titre, $association, $lieu, $date_debut, $date_fin, $disponibilite, $descriptionp, $categorie, $created_by) {
        // Validation des données
        if (empty($id) || empty($titre) || empty($association) || empty($created_by)) {
            return "Erreur: ID, titre, association et créateur sont obligatoires";
        }
        
        // Vérifier que le projet existe
        $existingProject = $this->projectModel->getProjectById($id);
        if (!$existingProject) {
            return "Erreur: Le projet n'existe pas";
        }
        
        // Validation des dates
        if (!empty($date_debut) && !empty($date_fin) && $date_debut > $date_fin) {
            return "Erreur: La date de début ne peut pas être après la date de fin";
        }
        
        // Vérifier que l'association existe
        if (!$this->isValidAssociation($association)) {
            return "Erreur: L'association spécifiée n'existe pas";
        }
        
        // Vérifier que l'admin créateur existe
        if (!$this->isValidAdmin($created_by)) {
            return "Erreur: L'administrateur créateur n'existe pas";
        }
        
        return $this->projectModel->updateProject(
            $id, $titre, $association, $lieu, $date_debut, $date_fin, 
            $disponibilite, $descriptionp, $categorie, $created_by
        );
    }
    
    // Supprimer un projet
    public function deleteProject($id) {
        if (empty($id)) {
            return false;
        }
        
        // Vérifier que le projet existe
        $existingProject = $this->projectModel->getProjectById($id);
        if (!$existingProject) {
            return false;
        }
        
        return $this->projectModel->deleteProject($id);
    }
    
    // Récupérer les projets par catégorie
    public function getProjectsByCategory($categorie) {
        if (empty($categorie)) {
            return [];
        }
        return $this->projectModel->getProjectsByCategory($categorie);
    }
    
    // Récupérer les projets disponibles
    public function getAvailableProjects() {
        return $this->projectModel->getAvailableProjects();
    }
    
    // Récupérer les projets par association
    public function getProjectsByAssociation($association_id) {
        if (empty($association_id)) {
            return [];
        }
        return $this->projectModel->getProjectsByAssociation($association_id);
    }
    
    // Récupérer les projets par statut de disponibilité
    public function getProjectsByStatus($status) {
        if (empty($status)) {
            return [];
        }
        return $this->projectModel->getProjectsByStatus($status);
    }
    
    // Statistiques
    public function getProjectsCount() {
        return $this->projectModel->getProjectsCount();
    }
    
    public function getAvailableProjectsCount() {
        return $this->projectModel->getAvailableProjectsCount();
    }
    
    // Récupérer le nombre de participants pour un projet (depuis la table participation)
    public function getParticipantsCount($project_id) {
        if (empty($project_id)) {
            return 0;
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "SELECT COUNT(*) as count FROM participation WHERE id_projet = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$project_id]);
            $result = $stmt->fetch();
            return $result ? $result['count'] : 0;
        } catch (PDOException $e) {
            error_log("Error counting participants: " . $e->getMessage());
            return 0;
        }
    }
    
    // Récupérer le total des participants pour tous les projets
    public function getTotalParticipants() {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT COUNT(*) as total FROM participation";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ? $result['total'] : 0;
        } catch (PDOException $e) {
            error_log("Error calculating total participants: " . $e->getMessage());
            return 0;
        }
    }
    
    // Méthodes de validation
    private function isValidAssociation($association_id) {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT id FROM utilisateurs WHERE id = ? AND type = 'association'";
            $stmt = $db->prepare($sql);
            $stmt->execute([$association_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error validating association: " . $e->getMessage());
            return false;
        }
    }
    
    private function isValidAdmin($admin_id) {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT id FROM admin WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$admin_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error validating admin: " . $e->getMessage());
            return false;
        }
    }
    
    // Récupérer les associations disponibles
    public function getAssociations() {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT id, nom, email FROM utilisateurs WHERE type = 'association' ORDER BY nom";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching associations: " . $e->getMessage());
            return [];
        }
    }
    
    // Récupérer les administrateurs disponibles
    public function getAdmins() {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT id, nom, email FROM admin ORDER BY nom";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching admins: " . $e->getMessage());
            return [];
        }
    }
    
    // Recherche de projets
    public function searchProjects($search_term) {
        if (empty($search_term)) {
            return [];
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "SELECT p.*, u.nom as association_nom, a.nom as admin_nom 
                    FROM projets p 
                    JOIN utilisateurs u ON p.association = u.id 
                    JOIN admin a ON p.created_by = a.id 
                    WHERE p.titre LIKE ? OR p.descriptionp LIKE ? OR p.lieu LIKE ? OR u.nom LIKE ?
                    ORDER BY p.date_debut DESC";
            $stmt = $db->prepare($sql);
            $search_pattern = "%" . $search_term . "%";
            $stmt->execute([$search_pattern, $search_pattern, $search_pattern, $search_pattern]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching projects: " . $e->getMessage());
            return [];
        }
    }
    
    // Récupérer les catégories disponibles
    public function getCategories() {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT DISTINCT categorie FROM projets WHERE categorie IS NOT NULL AND categorie != '' ORDER BY categorie";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return [];
        }
    }
    
    // Récupérer les statuts de disponibilité disponibles
    public function getAvailabilityStatuses() {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT DISTINCT disponibilite FROM projets WHERE disponibilite IS NOT NULL AND disponibilite != '' ORDER BY disponibilite";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error fetching availability statuses: " . $e->getMessage());
            return ['disponible', 'complet', 'termine'];
        }
    }
    
    // Gérer la participation d'un utilisateur à un projet
    public function addParticipation($user_id, $project_id, $admin_id) {
        if (empty($user_id) || empty($project_id) || empty($admin_id)) {
            return "Erreur: ID utilisateur, ID projet et ID admin sont obligatoires";
        }
        
        // Vérifier que l'utilisateur existe
        if (!$this->isValidUser($user_id)) {
            return "Erreur: L'utilisateur spécifié n'existe pas";
        }
        
        // Vérifier que le projet existe
        if (!$this->projectModel->getProjectById($project_id)) {
            return "Erreur: Le projet spécifié n'existe pas";
        }
        
        // Vérifier que l'admin existe
        if (!$this->isValidAdmin($admin_id)) {
            return "Erreur: L'administrateur spécifié n'existe pas";
        }
        
        // Vérifier si l'utilisateur participe déjà à ce projet
        if ($this->isUserParticipating($user_id, $project_id)) {
            return "Erreur: L'utilisateur participe déjà à ce projet";
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "INSERT INTO participation (id_participant, id_projet, created_by) VALUES (?, ?, ?)";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$user_id, $project_id, $admin_id]);
            
            return $result ? true : "Erreur lors de l'ajout de la participation";
        } catch (PDOException $e) {
            return "DATABASE_ERROR: " . $e->getMessage();
        }
    }
    
    // Supprimer une participation
    public function removeParticipation($user_id, $project_id) {
        if (empty($user_id) || empty($project_id)) {
            return false;
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "DELETE FROM participation WHERE id_participant = ? AND id_projet = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$user_id, $project_id]);
        } catch (PDOException $e) {
            error_log("Error removing participation: " . $e->getMessage());
            return false;
        }
    }
    
    // Vérifier si un utilisateur participe à un projet
    private function isUserParticipating($user_id, $project_id) {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT id_participant FROM participation WHERE id_participant = ? AND id_projet = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user_id, $project_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking participation: " . $e->getMessage());
            return false;
        }
    }
    
    // Vérifier si un utilisateur existe
    private function isValidUser($user_id) {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT id FROM utilisateurs WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error validating user: " . $e->getMessage());
            return false;
        }
    }

    // Méthode pour récupérer les projets avec pagination
    public function getProjectsWithPagination($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        try {
            $db = Config::getConnexion();
            $sql = "SELECT p.*, u.nom as association_nom, a.nom as admin_nom 
                    FROM projets p 
                    JOIN utilisateurs u ON p.association = u.id 
                    JOIN admin a ON p.created_by = a.id 
                    ORDER BY p.id_projet DESC 
                    LIMIT ? OFFSET ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching projects with pagination: " . $e->getMessage());
            return [];
        }
    }

    // Méthode pour récupérer les projets expirés
    public function getExpiredProjects() {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT p.*, u.nom as association_nom 
                    FROM projets p 
                    JOIN utilisateurs u ON p.association = u.id 
                    WHERE p.date_fin < CURDATE() 
                    ORDER BY p.date_fin DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching expired projects: " . $e->getMessage());
            return [];
        }
    }

    // Méthode pour récupérer les projets à venir
    public function getUpcomingProjects() {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT p.*, u.nom as association_nom 
                    FROM projets p 
                    JOIN utilisateurs u ON p.association = u.id 
                    WHERE p.date_debut > CURDATE() 
                    ORDER BY p.date_debut ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching upcoming projects: " . $e->getMessage());
            return [];
        }
    }
}
?>