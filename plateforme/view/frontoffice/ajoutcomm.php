<?php
header('Content-Type: application/json');

// Activer l'affichage des erreurs pour le développement
error_reporting(E_ALL);
ini_set('display_errors', 0); // Mettre à 0 en production

try {
    // Connexion à la base de données
    $pdo = new PDO(
        'mysql:host=localhost;dbname=weconnect;charset=utf8',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Vérifier que la requête est en POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }
    
    // Récupérer et valider les données
    $id_blog = isset($_POST['id_blog']) ? intval($_POST['id_blog']) : 0;
    $auteur = isset($_POST['auteur']) ? trim($_POST['auteur']) : '';
    $contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    
    // Validation
    if ($id_blog <= 0) {
        throw new Exception('ID du blog invalide');
    }
    
    if (empty($auteur)) {
        throw new Exception('Le nom est obligatoire');
    }
    
    if (strlen($auteur) > 100) {
        throw new Exception('Le nom est trop long (max 100 caractères)');
    }
    
    if (empty($contenu)) {
        throw new Exception('Le commentaire est obligatoire');
    }
    
    if (strlen($contenu) > 1000) {
        throw new Exception('Le commentaire est trop long (max 1000 caractères)');
    }
    
    // Validation de l'email si fourni
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email invalide');
    }
    
    // Vérifier que l'article existe et est publié
    $stmtCheck = $pdo->prepare("SELECT id_blog FROM blogs WHERE id_blog = ? AND est_publie_blog = 1");
    $stmtCheck->execute([$id_blog]);
    if (!$stmtCheck->fetch()) {
        throw new Exception('Article non trouvé');
    }
    
    // Préparer l'insertion du commentaire
    // Statut par défaut : 'en_attente' (nécessite modération)
    // Vous pouvez changer en 'approuvé' si vous voulez une publication directe
    $statut = 'en_attente'; // ou 'approuvé' pour publication immédiate
    
    $stmt = $pdo->prepare("
        INSERT INTO commentaire 
        (id_blog, auteur_comment, contenu_comment, statut_comment, date_creation_comment) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $id_blog,
        $auteur,
        $contenu,
        $statut
    ]);
    
    // Récupérer l'ID du commentaire inséré
    $commentaire_id = $pdo->lastInsertId();
    
    // Préparer la réponse
    $response = [
        'success' => true,
        'message' => $statut === 'approuvé' 
            ? 'Commentaire publié avec succès !' 
            : 'Votre commentaire a été soumis et est en attente de modération.',
        'commentaire_id' => $commentaire_id
    ];
    
    // Si le commentaire est directement approuvé, renvoyer ses données pour l'affichage
    if ($statut === 'approuvé') {
        $response['commentaire'] = [
            'id' => $commentaire_id,
            'auteur' => htmlspecialchars($auteur),
            'contenu' => htmlspecialchars($contenu),
            'date' => date('Y-m-d H:i:s')
        ];
    }
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    // Erreur de base de données
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'enregistrement du commentaire'
    ]);
    
    // Log l'erreur (en développement)
    error_log('Erreur DB commentaire: ' . $e->getMessage());
    
} catch (Exception $e) {
    // Autres erreurs (validation, etc.)
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>