<?php
class config
{   private static $pdo = null;
    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername="localhost";
            $username="root";
            $password ="";
            $dbname="projet";
            try {
                self::$pdo = new PDO("mysql:host=$servername;dbname=$dbname",
                        $username,
                        $password
                   
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
               
               
            } catch (Exception $e) {
                die('Erreur: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
// create a global $pdo variable for older code that expects it
$GLOBALS['pdo'] = config::getConnexion();

// Simple PSR-4-like autoloader for this small app. It will try to
// resolve namespaced classes like "Model\Participation" to
// "Model/Participation.php" and also fallback to Model/Or/Controller/ folders.
spl_autoload_register(function ($class) {
    $base = __DIR__;
    $class = ltrim($class, '\\');
    $file = $base . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // Fallbacks
    $try = $base . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . $class . '.php';
    if (file_exists($try)) { require_once $try; return; }

    $try = $base . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . $class . '.php';
    if (file_exists($try)) { require_once $try; return; }
});

?>


