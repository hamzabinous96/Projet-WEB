<?php
require_once __DIR__ . '/../config.php';

class Project {
    private $db;
    
    public function __construct() {
        $this->db = Config::getConnexion();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function addProject(
        string $titre, 
        $association,
        string $lieu, 
        string $date_debut, 
        string $date_fin, 
        string $disponibilite, 
        string $description, 
        string $categorie, 
        int $created_by
    ) {
        try {
            $sql = "INSERT INTO projets (titre, association, lieu, date_debut, date_fin, disponibilite, description, categorie, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $titre, 
                $association,
                $lieu, 
                $date_debut, 
                $date_fin, 
                $disponibilite, 
                $description, 
                $categorie, 
                $created_by
            ]);
            
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    
    public function getAllProjects() {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    ORDER BY p.id_projet DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Fetch error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getProjectsByCategory($categorie) {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    WHERE p.categorie = ? 
                    ORDER BY p.date_debut DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$categorie]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching projects by category: " . $e->getMessage());
            return [];
        }
    }
    
    public function deleteProject($id) {
        try {
            // Supprimer d'abord les tâches associées
            $sql_tasks = "DELETE FROM taches WHERE id_projet = ?";
            $stmt_tasks = $this->db->prepare($sql_tasks);
            $stmt_tasks->execute([$id]);
            
            // Puis supprimer le projet
            $sql = "DELETE FROM projets WHERE id_projet = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Delete error: " . $e->getMessage());
            return false;
        }
    }

    public function updateProject($id, $titre, $association, $lieu, $date_debut, $date_fin, $disponibilite, $description, $categorie, $created_by) {
        try {
            $sql = "UPDATE projets 
                    SET titre = :titre, association = :association, 
                        lieu = :lieu, date_debut = :date_debut, date_fin = :date_fin, 
                        disponibilite = :disponibilite, description = :description, 
                        categorie = :categorie, created_by = :created_by 
                    WHERE id_projet = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':titre', $titre, PDO::PARAM_STR);
            $stmt->bindValue(':association', $association, PDO::PARAM_INT);
            $stmt->bindValue(':lieu', $lieu, PDO::PARAM_STR);
            $stmt->bindValue(':date_debut', $date_debut, PDO::PARAM_STR);
            $stmt->bindValue(':date_fin', $date_fin, PDO::PARAM_STR);
            $stmt->bindValue(':disponibilite', $disponibilite, PDO::PARAM_STR);
            $stmt->bindValue(':description', $description, PDO::PARAM_STR);
            $stmt->bindValue(':categorie', $categorie, PDO::PARAM_STR);
            $stmt->bindValue(':created_by', $created_by, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating project: " . $e->getMessage());
            return false;
        }
    }
    
    public function getProjectById($id) {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name, 
                           creator.first_name as creator_first_name, creator.last_name as creator_last_name
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    JOIN users creator ON p.created_by = creator.id 
                    WHERE p.id_projet = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching project: " . $e->getMessage());
            return null;
        }
    }

    public function getProjectsCount() {
        try {
            $sql = "SELECT COUNT(*) as count FROM projets";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ? $result['count'] : 0;
        } catch (PDOException $e) {
            error_log("Error counting projects: " . $e->getMessage());
            return 0;
        }
    }

    public function getAvailableProjectsCount() {
        try {
            $sql = "SELECT COUNT(*) as count FROM projets WHERE disponibilite = 'disponible'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ? $result['count'] : 0;
        } catch (PDOException $e) {
            error_log("Error counting available projects: " . $e->getMessage());
            return 0;
        }
    }

    public function getAvailableProjects() {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    WHERE p.disponibilite = 'disponible' 
                    ORDER BY p.date_debut DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching available projects: " . $e->getMessage());
            return [];
        }
    }

    public function getProjectsByAssociation($association_id) {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    WHERE p.association = ? 
                    ORDER BY p.date_debut DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$association_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching projects by association: " . $e->getMessage());
            return [];
        }
    }

    public function getProjectsByStatus($status) {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    WHERE p.disponibilite = ? 
                    ORDER BY p.date_debut DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching projects by status: " . $e->getMessage());
            return [];
        }
    }

    // NOUVELLES MÉTHODES AJOUTÉES

    public function getProjectsWithDetails() {
        try {
            $sql = "SELECT p.*, 
                           u.first_name as assoc_first_name, u.last_name as assoc_last_name,
                           creator.first_name as creator_first_name, creator.last_name as creator_last_name,
                           COUNT(t.id_tache) as task_count,
                           COUNT(DISTINCT t.assignee) as participant_count
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    JOIN users creator ON p.created_by = creator.id 
                    LEFT JOIN taches t ON p.id_projet = t.id_projet 
                    GROUP BY p.id_projet 
                    ORDER BY p.id_projet DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching projects with details: " . $e->getMessage());
            return [];
        }
    }

    public function getProjectsByDateRange($start_date, $end_date) {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    WHERE p.date_debut >= ? AND p.date_fin <= ? 
                    ORDER BY p.date_debut ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$start_date, $end_date]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching projects by date range: " . $e->getMessage());
            return [];
        }
    }

    public function getActiveProjects() {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    WHERE p.disponibilite = 'disponible' 
                    AND (p.date_fin IS NULL OR p.date_fin >= CURDATE())
                    ORDER BY p.date_debut DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching active projects: " . $e->getMessage());
            return [];
        }
    }

    public function getCompletedProjects() {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    WHERE p.disponibilite = 'termine' 
                    OR (p.date_fin IS NOT NULL AND p.date_fin < CURDATE())
                    ORDER BY p.date_fin DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching completed projects: " . $e->getMessage());
            return [];
        }
    }

    public function searchProjects($search_term) {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    WHERE p.titre LIKE ? 
                    OR p.description LIKE ? 
                    OR p.lieu LIKE ? 
                    OR p.categorie LIKE ? 
                    OR u.first_name LIKE ? 
                    OR u.last_name LIKE ? 
                    ORDER BY p.date_debut DESC";
            $stmt = $this->db->prepare($sql);
            $search_pattern = "%" . $search_term . "%";
            $stmt->execute([
                $search_pattern, 
                $search_pattern, 
                $search_pattern, 
                $search_pattern, 
                $search_pattern, 
                $search_pattern
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching projects: " . $e->getMessage());
            return [];
        }
    }

    public function getProjectStats() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_projects,
                        COUNT(CASE WHEN disponibilite = 'disponible' THEN 1 END) as available_projects,
                        COUNT(CASE WHEN disponibilite = 'complet' THEN 1 END) as complete_projects,
                        COUNT(CASE WHEN disponibilite = 'termine' THEN 1 END) as finished_projects,
                        COUNT(CASE WHEN date_debut > CURDATE() THEN 1 END) as upcoming_projects,
                        COUNT(CASE WHEN date_fin < CURDATE() THEN 1 END) as expired_projects
                    FROM projets";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching project stats: " . $e->getMessage());
            return [];
        }
    }

    public function getProjectsByCreator($created_by) {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    WHERE p.created_by = ? 
                    ORDER BY p.date_debut DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$created_by]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching projects by creator: " . $e->getMessage());
            return [];
        }
    }

    public function updateProjectStatus($project_id, $status) {
        try {
            $sql = "UPDATE projets SET disponibilite = ? WHERE id_projet = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$status, $project_id]);
        } catch (PDOException $e) {
            error_log("Error updating project status: " . $e->getMessage());
            return false;
        }
    }

    public function getProjectsWithTaskCount() {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name,
                           COUNT(t.id_tache) as task_count,
                           COUNT(CASE WHEN t.status = 'termine' THEN 1 END) as completed_tasks
                    FROM projets p 
                    JOIN users u ON p.association = u.id 
                    LEFT JOIN taches t ON p.id_projet = t.id_projet 
                    GROUP BY p.id_projet 
                    ORDER BY p.id_projet DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching projects with task count: " . $e->getMessage());
            return [];
        }
    }
}
?>