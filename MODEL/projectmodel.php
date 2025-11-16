<?php
// Project.php - CORRECTED VERSION according to your database structure
require_once __DIR__ . '/../config.php'; // Goes up one level to config.php
require_once __DIR__ . '/../MODEL/projectmodel.php';// Goes up one level to config.php

class Project {
    private $db;
    
    public function __construct() {
        $this->db = Config::getConnexion();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    // CORRECTION: Remove $id_projet and $participants parameters - they don't exist in your table
public function addProject(
    string $titre, 
    $association, // Accepte int ou string
    string $lieu, 
    string $date_debut, 
    string $date_fin, 
    string $disponibilite, 
    string $descriptionp, 
    string $categorie, 
    int $created_by
) {
    try {
        $sql = "INSERT INTO projets (titre, association, lieu, date_debut, date_fin, disponibilite, descriptionp, categorie, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $titre, 
            $association, // Peut être ID (int) ou nom (string)
            $lieu, 
            $date_debut, 
            $date_fin, 
            $disponibilite, 
            $descriptionp, 
            $categorie, 
            $created_by
        ]);
        
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}
    
    public function getAllProjects() {
        try {
            // CORRECTED: Use id_projet instead of ID, and join with related tables
            $sql = "SELECT p.*, u.nom as association_nom, a.nom as admin_nom 
                    FROM projets p 
                    JOIN utilisateurs u ON p.association = u.id 
                    JOIN admin a ON p.created_by = a.id 
                    ORDER BY p.id_projet DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Fetch error: " . $e->getMessage());
            return [];
        }
    }
    
    public function deleteProject($id) {
        try {
            // CORRECTED: Use id_projet instead of ID
            $sql = "DELETE FROM projets WHERE id_projet = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Delete error: " . $e->getMessage());
            return false;
        }
    }

    // CORRECTION: Remove $id_projet and $participants parameters
    public function updateProject($id, $titre, $association, $lieu, $date_debut, $date_fin, $disponibilite, $descriptionp, $categorie, $created_by) {
        try {
            $sql = "UPDATE projets 
                    SET titre = :titre, association = :association, 
                        lieu = :lieu, date_debut = :date_debut, date_fin = :date_fin, 
                        disponibilite = :disponibilite, descriptionp = :descriptionp, 
                        categorie = :categorie, created_by = :created_by 
                    WHERE id_projet = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':titre', $titre, PDO::PARAM_STR);
            $stmt->bindValue(':association', $association, PDO::PARAM_INT); // Changed to INT
            $stmt->bindValue(':lieu', $lieu, PDO::PARAM_STR);
            $stmt->bindValue(':date_debut', $date_debut, PDO::PARAM_STR);
            $stmt->bindValue(':date_fin', $date_fin, PDO::PARAM_STR);
            $stmt->bindValue(':disponibilite', $disponibilite, PDO::PARAM_STR);
            $stmt->bindValue(':descriptionp', $descriptionp, PDO::PARAM_STR);
            $stmt->bindValue(':categorie', $categorie, PDO::PARAM_STR);
            $stmt->bindValue(':created_by', $created_by, PDO::PARAM_INT); // Changed to INT
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating project: " . $e->getMessage());
            return false;
        }
    }
    
    public function getProjectById($id) {
        try {
            // CORRECTED: Use id_projet instead of ID, and join with related tables
            $sql = "SELECT p.*, u.nom as association_nom, a.nom as admin_nom 
                    FROM projets p 
                    JOIN utilisateurs u ON p.association = u.id 
                    JOIN admin a ON p.created_by = a.id 
                    WHERE p.id_projet = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching project: " . $e->getMessage());
            return null;
        }
    }

    // CORRECTION: Remove getTotalParticipants() since there's no participants field
    public function getProjectsCount() {
        try {
            $sql = "SELECT COUNT(*) as count FROM projets";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch()['count'];
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
            return $stmt->fetch()['count'];
        } catch (PDOException $e) {
            error_log("Error counting available projects: " . $e->getMessage());
            return 0;
        }
    }
}
?>