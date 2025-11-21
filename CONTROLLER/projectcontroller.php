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
    
    public function addProject($titre, $association, $lieu, $date_debut, $date_fin, $disponibilite, $description, $categorie, $created_by) {
        if (empty($titre) || empty($association) || empty($created_by)) {
            return "Erreur: Titre, association et créateur sont obligatoires";
        }
        
        if (!empty($date_debut) && !empty($date_fin) && $date_debut > $date_fin) {
            return "Erreur: La date de début ne peut pas être après la date de fin";
        }
        
        if (!$this->isValidAssociation($association)) {
            return "Erreur: L'association spécifiée n'existe pas";
        }
        
        if (!$this->isValidUser($created_by)) {
            return "Erreur: L'utilisateur créateur n'existe pas";
        }
        
        return $this->projectModel->addProject(
            $titre, $association, $lieu, $date_debut, $date_fin, 
            $disponibilite, $description, $categorie, $created_by
        );
    }

    public function getTasksByProject($projectId) {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT t.*, u.first_name, u.last_name 
                    FROM taches t 
                    LEFT JOIN users u ON t.assignee = u.id 
                    WHERE t.id_projet = ? 
                    ORDER BY t.id_tache ASC";
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

    public function updateProject($id, $titre, $association, $lieu, $date_debut, $date_fin, $disponibilite, $description, $categorie, $created_by) {
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
        
        if (!$this->isValidUser($created_by)) {
            return "Erreur: L'utilisateur créateur n'existe pas";
        }
        
        return $this->projectModel->updateProject(
            $id, $titre, $association, $lieu, $date_debut, $date_fin, 
            $disponibilite, $description, $categorie, $created_by
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
            $sql = "SELECT COUNT(DISTINCT assignee) as count FROM taches WHERE id_projet = ? AND assignee IS NOT NULL";
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
            $sql = "SELECT COUNT(DISTINCT assignee) as total FROM taches WHERE assignee IS NOT NULL";
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
                    WHERE disponibilite IS NOT NULL 
                    GROUP BY disponibilite";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stats = [];
            foreach ($results as $row) {
                $stats[$row['disponibilite']] = $row['count'];
            }
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error fetching projects by availability: " . $e->getMessage());
            return [];
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
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error fetching projects by category stats: " . $e->getMessage());
            return [];
        }
    }
    
    private function isValidAssociation($association_id) {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT id FROM users WHERE id = ? AND user_type = 'association'";
            $stmt = $db->prepare($sql);
            $stmt->execute([$association_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error validating association: " . $e->getMessage());
            return false;
        }
    }
    
    private function isValidUser($user_id) {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT id FROM users WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error validating user: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAssociations() {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT id, first_name, last_name, email FROM users WHERE user_type = 'association' ORDER BY first_name, last_name";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching associations: " . $e->getMessage());
            return [];
        }
    }
    
    public function getUsers() {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT id, first_name, last_name, email, user_type FROM users ORDER BY first_name, last_name";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return [];
        }
    }
    
    public function searchProjects($search_term) {
        if (empty($search_term)) {
            return [];
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    WHERE p.titre LIKE ? OR p.description LIKE ? OR p.lieu LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?
                    ORDER BY p.date_debut DESC";
            $stmt = $db->prepare($sql);
            $search_pattern = "%" . $search_term . "%";
            $stmt->execute([$search_pattern, $search_pattern, $search_pattern, $search_pattern, $search_pattern]);
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
    
    public function assignUserToTask($user_id, $task_id) {
        if (empty($user_id) || empty($task_id)) {
            return false;
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "UPDATE taches SET assignee = ? WHERE id_tache = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$user_id, $task_id]);
        } catch (PDOException $e) {
            error_log("Error assigning user to task: " . $e->getMessage());
            return false;
        }
    }
    
    public function removeUserFromTask($task_id) {
        if (empty($task_id)) {
            return false;
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "UPDATE taches SET assignee = NULL WHERE id_tache = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$task_id]);
        } catch (PDOException $e) {
            error_log("Error removing user from task: " . $e->getMessage());
            return false;
        }
    }
    
    public function isUserAssignedToProject($user_id, $project_id) {
        if (empty($user_id) || empty($project_id)) {
            return false;
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "SELECT id_tache FROM taches WHERE id_projet = ? AND assignee = ? LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$project_id, $user_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking user assignment: " . $e->getMessage());
            return false;
        }
    }
    
    public function getProjectsWithPagination($page = 1, $limit = 10, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        try {
            $db = Config::getConnexion();
            
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
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
                $sql .= " AND (p.titre LIKE ? OR p.description LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
                $search_pattern = "%" . $filters['search'] . "%";
                $params[] = $search_pattern;
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
                    JOIN users u ON p.association = u.id 
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
                $sql .= " AND (p.titre LIKE ? OR p.description LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
                $search_pattern = "%" . $filters['search'] . "%";
                $params[] = $search_pattern;
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
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
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
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
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
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
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
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
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
            $sql = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.email, u.profile_picture 
                    FROM taches t 
                    JOIN users u ON t.assignee = u.id 
                    WHERE t.id_projet = ? AND t.assignee IS NOT NULL 
                    ORDER BY u.first_name, u.last_name";
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

    public function getProjectsByCreator($user_id) {
        if (empty($user_id)) {
            return [];
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    WHERE p.created_by = ? 
                    ORDER BY p.date_debut DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching projects by creator: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentProjects($limit = 5) {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    WHERE p.disponibilite = 'disponible'
                    ORDER BY p.date_debut DESC 
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
            
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($criteria['search'])) {
                $sql .= " AND (p.titre LIKE ? OR p.description LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
                $search_pattern = "%" . $criteria['search'] . "%";
                $params[] = $search_pattern;
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
                $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ?)";
                $params[] = "%" . $criteria['association'] . "%";
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

    // MÉTHODES POUR LES DÉTAILS DES PROJETS

    public function getTachesByProject($projectId) {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT t.*, u.first_name, u.last_name 
                    FROM taches t 
                    LEFT JOIN users u ON t.assignee = u.id 
                    WHERE t.id_projet = ? 
                    ORDER BY t.id_tache ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$projectId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching tasks by project: " . $e->getMessage());
            return [];
        }
    }

    public function getProjectDetails($projectId) {
        if (empty($projectId)) {
            return null;
        }
        
        try {
            $db = Config::getConnexion();
            $sql = "SELECT p.*, u.first_name, u.last_name, u_assoc.first_name as assoc_first_name, u_assoc.last_name as assoc_last_name
                    FROM projets p 
                    JOIN users u ON p.created_by = u.id 
                    JOIN users u_assoc ON p.association = u_assoc.id 
                    WHERE p.id_projet = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$projectId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching project details: " . $e->getMessage());
            return null;
        }
    }

    public function getProjectStatistics($projectId) {
        if (empty($projectId)) {
            return [];
        }
        
        try {
            $db = Config::getConnexion();
            
            // Nombre de participants (utilisateurs assignés à des tâches)
            $participantsCount = $this->getParticipantsCount($projectId);
            
            // Nombre de tâches par statut
            $sql = "SELECT status, COUNT(*) as count 
                    FROM taches 
                    WHERE id_projet = ? 
                    GROUP BY status";
            $stmt = $db->prepare($sql);
            $stmt->execute([$projectId]);
            $tasksByStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Tâches assignées vs non assignées
            $sql = "SELECT 
                        COUNT(CASE WHEN assignee IS NOT NULL THEN 1 END) as assigned,
                        COUNT(CASE WHEN assignee IS NULL THEN 1 END) as unassigned
                    FROM taches 
                    WHERE id_projet = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$projectId]);
            $assignmentStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'participants_count' => $participantsCount,
                'tasks_by_status' => $tasksByStatus,
                'assignment_stats' => $assignmentStats
            ];
        } catch (PDOException $e) {
            error_log("Error fetching project statistics: " . $e->getMessage());
            return [];
        }
    }

    public function updateTache($taskId, $nom_tache, $description, $status, $assignee) {
        try {
            $db = Config::getConnexion();
            $sql = "UPDATE taches 
                    SET nom_tache = ?, description = ?, status = ?, assignee = ? 
                    WHERE id_tache = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$nom_tache, $description, $status, $assignee, $taskId]);
        } catch (PDOException $e) {
            error_log("Error updating task: " . $e->getMessage());
            return false;
        }
    }

    public function deleteTache($taskId) {
        try {
            $db = Config::getConnexion();
            $sql = "DELETE FROM taches WHERE id_tache = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$taskId]);
        } catch (PDOException $e) {
            error_log("Error deleting task: " . $e->getMessage());
            return false;
        }
    }

    public function getTacheById($taskId) {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT t.*, u.first_name, u.last_name 
                    FROM taches t 
                    LEFT JOIN users u ON t.assignee = u.id 
                    WHERE t.id_tache = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$taskId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching task by ID: " . $e->getMessage());
            return null;
        }
    }

    public function getUserTasks($user_id) {
        try {
            $db = Config::getConnexion();
            $sql = "SELECT t.*, p.titre as project_titre, p.lieu as project_lieu
                    FROM taches t 
                    JOIN projets p ON t.id_projet = p.id_projet 
                    WHERE t.assignee = ? 
                    ORDER BY t.id_tache DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user tasks: " . $e->getMessage());
            return [];
        }
    }

    public function getProjectProgress($project_id) {
        try {
            $db = Config::getConnexion();
            
            // Calculer le pourcentage de tâches complétées
            $sql = "SELECT 
                        COUNT(*) as total_tasks,
                        COUNT(CASE WHEN status = 'termine' THEN 1 END) as completed_tasks
                    FROM taches 
                    WHERE id_projet = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$project_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['total_tasks'] > 0) {
                $progress = ($result['completed_tasks'] / $result['total_tasks']) * 100;
                return round($progress, 2);
            }
            
            return 0;
        } catch (PDOException $e) {
            error_log("Error calculating project progress: " . $e->getMessage());
            return 0;
        }
    }
}
?>