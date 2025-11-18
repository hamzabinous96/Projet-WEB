<?php
/**
 * Classe Database - Gestion de la connexion à la base de données
 * Pattern Singleton pour une instance unique
 */
class Database {
    // Paramètres de connexion
    private static $hote = 'localhost';
    private static $nomBase = 'weconnect';
    private static $nomUtilisateur = 'root';
    private static $motDePasse = '';
    private static $instance = null;
    
    /**
     * Obtenir la connexion à la base de données
     * @return PDO Instance PDO
     */
    public static function obtenirConnexion() {
        // Si l'instance n'existe pas, la créer
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    'mysql:host=' . self::$hote . ';dbname=' . self::$nomBase . ';charset=utf8',
                    self::$nomUtilisateur,
                    self::$motDePasse,
                    array(
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    )
                );
            } catch (PDOException $e) {
                die('Erreur de connexion : ' . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
?>
