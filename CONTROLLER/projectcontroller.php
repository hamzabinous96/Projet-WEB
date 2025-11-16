<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../MODEL/projectmodel.php';

class ProjectController {
    private $projectModel;
    
    public function __construct() {
        $this->projectModel = new Project();
    }
    
    public function getProjectsByCategory($categorie) {
        if (empty($categorie)) {
            return [];
        }
        return $this->projectModel->getProjectsByCategory($categorie);
    }
    
    public function getAllProjects() {
        return $this->projectModel->getAllProjects();
    }
    
    public function getProjectById($id) {
        if (empty($id)) {
            return null;
        }
        return $this->projectModel->getProjectById($id);
    }
    
    public function addProject($titre, $association, $lieu, $date_debut, $date_fin, $disponibilite, $descriptionp, $categorie, $created_by) {
        if (empty($titre) || empty($association) || empty($created_by)) {
            return "Erreur: Titre, association et créateur sont obligatoires";
        }
        
        if (!empty($date_debut) && !empty($date_fin) && $date_debut > $date_fin) {
            return "Erreur: La date de début ne peut pas être après la date de fin";
        }
        
        if (!$this->isValidAssociation($association)) {
            return "Erreur: L'association spécifiée n'existe pas";
        }
        
        if (!$this->isValidAdmin($created_by)) {
            return "Erreur: L'administrateur créateur n'existe pas";
        }
        
        return $this->projectModel->addProject(
            $titre, $association, $lieu, $date_debut, $date_fin, 
            $disponibilite, $descriptionp, $categorie, $created_by
        );
    }
    public function getTasksByProject($projectId) {
    try {
        $db = Config::getConnexion();
        $sql = "SELECT * FROM taches WHERE id_projet = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des tâches: " . $e->getMessage());
        return [];
    }
}
public function updateTaskStatus($taskId, $status) {
    try {
        $db = Config::getConnexion();
        $sql = "UPDATE taches SET status = ? WHERE id_tache = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$status, $taskId]);
    } catch (PDOException $e) {
        error_log("Error updating task status: " . $e->getMessage());
        return false;
    }
}
    public function updateProject($id, $titre, $association, $lieu, $date_debut, $date_fin, $disponibilite, $descriptionp, $categorie, $created_by) {
        if (empty($id) || empty($titre) || empty($association) || empty($created_by)) {
            return "Erreur: ID, titre, association et créateur sont obligatoires";
        }
        
        $existingProject = $this->projectModel->getProjectById($id);
        if (!$existingProject) {
            return "Erreur: Le projet n'existe pas";
        }
        
        if (!empty($date_debut) && !empty($date_fin) && $date_debut > $date_fin) {
            return "Erreur: La date de début ne peut pas être après la date de fin";
        }
        
        if (!$this->isValidAssociation($association)) {
            return "Erreur: L'association spécifiée n'existe pas";
        }
        
        if (!$this->isValidAdmin($created_by)) {
            return "Erreur: L'administrateur créateur n'existe pas";
        }
        
        return $this->projectModel->updateProject(
            $id, $titre, $association, $lieu, $date_debut, $date_fin, 
            $disponibilite, $descriptionp, $categorie, $created_by
        );
    }
    
    public function deleteProject($id) {
        if (empty($id)) {
            return false;
        }
        
        $existingProject = $this->projectModel->getProjectById($id);
        if (!$existingProject) {
            return false;
        }
        
        return $this->projectModel->deleteProject($id);
    }
    
    public function getAvailableProjects() {
        return $this->projectModel->getAvailableProjects();
    }
    
    public function getProjectsByAssociation($association_id) {
        if (empty($association_id)) {
            return [];
        }
        return $this->projectModel->getProjectsByAssociation($association_id);
    }
    
    public function getProjectsByStatus($status) {
        if (empty($status)) {
            return [];
        }
        return $this->projectModel->getProjectsByStatus($status);
    }
    
    public function getProjectsWithFilters($filters = []) {
        $category = $filters['category'] ?? '';
        $status = $filters['status'] ?? '';
        $location = $filters['location'] ?? '';
        $search = $filters['search'] ?? '';
        
        $projects = [];
        
        if (!empty($search)) {
            $projects = $this->searchProjects($search);
        }
        elseif (!empty($category)) {
            $projects = $this->getProjectsByCategory($category);
        }
        else {
            $projects = $this->getAllProjects();
        }
        
        if (!empty($status) && $projects) {
            $projects = array_filter($projects, function($project) use ($status) {
                return $project['disponibilite'] === $status;
            });
        }
        
        if (!empty($location) && $projects) {
            $projects = array_filter($projects, function($project) use ($location) {
                return stripos($project['lieu'] ?? '', $location) !== false;
            });
        }
        
        return array_values($projects);
    }
    
    public function getProjectsCount() {
        return $this->projectModel->getProjectsCount();
    }
    
    public function getAvailableProjectsCount() {
        return $this->projectModel->getAvailableProjectsCount();
    }
    
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
    
    public function getProjectsByAvailability() {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT disponibilite, COUNT(*) as count 
                    FROM projets 
                    WHERE disponibilite IN ('disponible', 'complet', 'termine') 
                    GROUP BY disponibilite";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stats = [];
            foreach ($results as $row) {
                $stats[$row['disponibilite']] = $row['count'];
            }
            
            $stats['disponible'] = $stats['disponible'] ?? 0;
            $stats['complet'] = $stats['complet'] ?? 0;
            $stats['termine'] = $stats['termine'] ?? 0;
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error fetching projects by availability: " . $e->getMessage());
            return [
                'disponible' => 0,
                'complet' => 0,
                'termine' => 0
            ];
        }
    }
    
    public function getProjectsByCategoryStats() {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT categorie, COUNT(*) as count 
                    FROM projets 
                    WHERE categorie IS NOT NULL AND categorie != '' 
                    GROUP BY categorie";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stats = [];
            foreach ($results as $row) {
                $stats[$row['categorie']] = $row['count'];
            }
            
            $mainCategories = ['Solidarité', 'Environement', 'Education', 'Sante', 'Aide', 'Culture'];
            foreach ($mainCategories as $category) {
                $stats[$category] = $stats[$category] ?? 0;
            }
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error fetching projects by category stats: " . $e->getMessage());
            return [
                'Solidarité' => 0,
                'Environement' => 0,
                'Education' => 0,
                'Sante' => 0,
                'Aide' => 0,
                'Culture' => 0
            ];
        }
    }
    
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
    
    public function addParticipation($user_id, $project_id, $admin_id) {
        if (empty($user_id) || empty($project_id) || empty($admin_id)) {
            return "Erreur: ID utilisateur, ID projet et ID admin sont obligatoires";
        }
        
        if (!$this->isValidUser($user_id)) {
            return "Erreur: L'utilisateur spécifié n'existe pas";
        }
        
        if (!$this->projectModel->getProjectById($project_id)) {
            return "Erreur: Le projet spécifié n'existe pas";
        }
        
        if (!$this->isValidAdmin($admin_id)) {
            return "Erreur: L'administrateur spécifié n'existe pas";
        }
        
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
    

    public function getProjectsWithPagination($page = 1, $limit = 10, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        try {
            $db = Config::getConnexion();
            
            $sql = "SELECT p.*, u.nom as association_nom, a.nom as admin_nom 
                    FROM projets p 
                    JOIN utilisateurs u ON p.association = u.id 
                    JOIN admin a ON p.created_by = a.id 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['category'])) {
                $sql .= " AND p.categorie = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND p.disponibilite = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['location'])) {
                $sql .= " AND p.lieu LIKE ?";
                $params[] = "%" . $filters['location'] . "%";
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (p.titre LIKE ? OR p.descriptionp LIKE ? OR u.nom LIKE ?)";
                $search_pattern = "%" . $filters['search'] . "%";
                $params[] = $search_pattern;
                $params[] = $search_pattern;
                $params[] = $search_pattern;
            }
            
            $sql .= " ORDER BY p.id_projet DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching projects with pagination: " . $e->getMessage());
            return [];
        }
    }

    public function countProjectsWithFilters($filters = []) {
        try {
            $db = Config::getConnexion();
            
            $sql = "SELECT COUNT(*) as total 
                    FROM projets p 
                    JOIN utilisateurs u ON p.association = u.id 
                    JOIN admin a ON p.created_by = a.id 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['category'])) {
                $sql .= " AND p.categorie = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND p.disponibilite = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['location'])) {
                $sql .= " AND p.lieu LIKE ?";
                $params[] = "%" . $filters['location'] . "%";
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (p.titre LIKE ? OR p.descriptionp LIKE ? OR u.nom LIKE ?)";
                $search_pattern = "%" . $filters['search'] . "%";
                $params[] = $search_pattern;
                $params[] = $search_pattern;
                $params[] = $search_pattern;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ? $result['total'] : 0;
        } catch (PDOException $e) {
            error_log("Error counting projects with filters: " . $e->getMessage());
            return 0;
        }
    }

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
    
    public function getUrgentProjects($days = 7) {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT p.*, u.nom as association_nom 
                    FROM projets p 
                    JOIN utilisateurs u ON p.association = u.id 
                    WHERE p.date_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                    AND p.disponibilite = 'disponible'
                    ORDER BY p.date_fin ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching urgent projects: " . $e->getMessage());
            return [];
        }
    }
    
    public function saveProjectToFavorites($user_id, $project_id) {
        if (empty($user_id) || empty($project_id)) {
            return false;
        }
        
        try {
            $db = Config::getConnexion();
            
            $check_sql = "SELECT id FROM favoris WHERE id_utilisateur = ? AND id_projet = ?";
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->execute([$user_id, $project_id]);
            
            if ($check_stmt->rowCount() > 0) {
                return "Ce projet est déjà dans vos favoris";
            }
            
            $sql = "INSERT INTO favoris (id_utilisateur, id_projet, date_ajout) VALUES (?, ?, NOW())";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$user_id, $project_id]);
            
            return $result ? true : "Erreur lors de l'ajout aux favoris";
        } catch (PDOException $e) {
            error_log("Error saving project to favorites: " . $e->getMessage());
            return "Erreur de base de données: " . $e->getMessage();
        }
    }
    
    public function removeProjectFromFavorites($user_id, $project_id) {
        if (empty($user_id) || empty($project_id)) {
            return false;
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "DELETE FROM favoris WHERE id_utilisateur = ? AND id_projet = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$user_id, $project_id]);
        } catch (PDOException $e) {
            error_log("Error removing project from favorites: " . $e->getMessage());
            return false;
        }
    }
    
    public function isProjectInFavorites($user_id, $project_id) {
        if (empty($user_id) || empty($project_id)) {
            return false;
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "SELECT id FROM favoris WHERE id_utilisateur = ? AND id_projet = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user_id, $project_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking favorites: " . $e->getMessage());
            return false;
        }
    }

    public function getSimilarProjects($project_id, $limit = 3) {
        if (empty($project_id)) {
            return [];
        }
        
        try {
            $currentProject = $this->getProjectById($project_id);
            if (!$currentProject) {
                return [];
            }
            
            $category = $currentProject['categorie'];
            
            $db = Config::getConnexion();
            $sql = "SELECT p.*, u.nom as association_nom 
                    FROM projets p 
                    JOIN utilisateurs u ON p.association = u.id 
                    WHERE p.categorie = ? AND p.id_projet != ? AND p.disponibilite = 'disponible'
                    ORDER BY p.date_debut DESC 
                    LIMIT ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$category, $project_id, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching similar projects: " . $e->getMessage());
            return [];
        }
    }

    public function getProjectParticipants($project_id) {
        if (empty($project_id)) {
            return [];
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "SELECT u.id, u.nom, u.email, u.photo, p.date_participation 
                    FROM participation p 
                    JOIN utilisateurs u ON p.id_participant = u.id 
                    WHERE p.id_projet = ? 
                    ORDER BY p.date_participation DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$project_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching project participants: " . $e->getMessage());
            return [];
        }
    }
public function addTache($nom_tache, $description, $status, $id_projet, $assignee, $created_by) {
    try {
        $db = Config::getConnexion();
        $sql = "INSERT INTO taches (nom_tache, description, status, id_projet, assignee, created_by) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$nom_tache, $description, $status, $id_projet, $assignee, $created_by]);
    } catch (PDOException $e) {
        error_log("Error adding task: " . $e->getMessage());
        return false;
    }
}



public function getLastInsertId() {
    try {
        $db = Config::getConnexion();
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error getting last insert ID: " . $e->getMessage());
        return false;
    }
}
    public function isUserParticipatingInProject($user_id, $project_id) {
        if (empty($user_id) || empty($project_id)) {
            return false;
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "SELECT id_participant FROM participation WHERE id_participant = ? AND id_projet = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user_id, $project_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking user participation: " . $e->getMessage());
            return false;
        }
    }

    public function updateProjectAvailability($project_id, $availability) {
        if (empty($project_id) || empty($availability)) {
            return false;
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "UPDATE projets SET disponibilite = ? WHERE id_projet = ?";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$availability, $project_id]);
            
            return $result ? true : "Erreur lors de la mise à jour du statut";
        } catch (PDOException $e) {
            error_log("Error updating project availability: " . $e->getMessage());
            return "Erreur de base de données: " . $e->getMessage();
        }
    }

    public function getProjectsByCreator($admin_id) {
        if (empty($admin_id)) {
            return [];
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "SELECT p.*, u.nom as association_nom 
                    FROM projets p 
                    JOIN utilisateurs u ON p.association = u.id 
                    WHERE p.created_by = ? 
                    ORDER BY p.date_debut DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$admin_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching projects by creator: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentProjects($limit = 5) {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT p.*, u.nom as association_nom 
                    FROM projets p 
                    JOIN utilisateurs u ON p.association = u.id 
                    WHERE p.disponibilite = 'disponible'
                    ORDER BY p.date_creation DESC 
                    LIMIT ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching recent projects: " . $e->getMessage());
            return [];
        }
    }

    public function advancedSearch($criteria = []) {
        try {
            $db = Config::getConnexion();
            
            $sql = "SELECT p.*, u.nom as association_nom, a.nom as admin_nom 
                    FROM projets p 
                    JOIN utilisateurs u ON p.association = u.id 
                    JOIN admin a ON p.created_by = a.id 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($criteria['search'])) {
                $sql .= " AND (p.titre LIKE ? OR p.descriptionp LIKE ? OR u.nom LIKE ?)";
                $search_pattern = "%" . $criteria['search'] . "%";
                $params[] = $search_pattern;
                $params[] = $search_pattern;
                $params[] = $search_pattern;
            }
            
            if (!empty($criteria['category'])) {
                $sql .= " AND p.categorie = ?";
                $params[] = $criteria['category'];
            }
            
            if (!empty($criteria['status'])) {
                $sql .= " AND p.disponibilite = ?";
                $params[] = $criteria['status'];
            }
            
            if (!empty($criteria['location'])) {
                $sql .= " AND p.lieu LIKE ?";
                $params[] = "%" . $criteria['location'] . "%";
            }
            
            if (!empty($criteria['start_date'])) {
                $sql .= " AND p.date_debut >= ?";
                $params[] = $criteria['start_date'];
            }
            
            if (!empty($criteria['end_date'])) {
                $sql .= " AND p.date_fin <= ?";
                $params[] = $criteria['end_date'];
            }
            
            if (!empty($criteria['association'])) {
                $sql .= " AND u.nom LIKE ?";
                $params[] = "%" . $criteria['association'] . "%";
            }
            
            $sql .= " ORDER BY p.date_debut DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in advanced search: " . $e->getMessage());
            return [];
        }
    }
}
?>