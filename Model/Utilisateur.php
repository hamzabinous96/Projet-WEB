<?php
class Utilisateur {
    private $pdo;

    public function __construct(){
        $this->pdo = new PDO(
            "mysql:host=127.0.0.1;dbname=project_weconnect1;charset=utf8mb4",
            "root","",
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
        );
    }

    public function findByEmail($email){
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}