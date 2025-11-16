<?php
class config {
    private static $pdo = null;

    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username = "root";
            $password = ""; // ou "password" si c’est ton mot de passe MySQL
            $dbname = "project_weconnect1"; // remplace par le nom réel de ta base

            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname;charset=utf8",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );

                
            } catch (Exception $e) {
                die("❌ Erreur de connexion : " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}

// Test de connexion
config::getConnexion();
?>
