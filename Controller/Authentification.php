<?php
session_start();
require_once __DIR__ . '/../Model/Utilisateur.php';
require_once __DIR__ . '/../Model/Admin.php';

$action = $_GET['action'] ?? null;

// ----------------------------------------------------
// LOGOUT
// ----------------------------------------------------
if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: ../View/auth/login.php');
    exit;
}

// ----------------------------------------------------
// LOGIN (POST)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $_SESSION['errors'] = ["Email et mot de passe requis."];
        header('Location: ../View/auth/login.php');
        exit;
    }

    // Try UTILISATEUR first
    $uModel = new Utilisateur();
    $user = $uModel->findByEmail($email);

    if ($user && $password === $user['mot_de_passe']) {
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'nom'   => $user['nom'],
            'email' => $user['email'],
            'type'  => $user['type'] // participant / association
        ];
        unset($_SESSION['admin']);
        header('Location: Quiz.php?action=list');
        exit;
    }

    // Try ADMIN next
    $aModel = new Admin();
    $admin = $aModel->findByEmail($email);

    if ($admin && $password === $admin['mot_de_passe']) {
        $_SESSION['admin'] = [
            'id'    => $admin['id'],
            'nom'   => $admin['nom'],
            'email' => $admin['email']
        ];
        $_SESSION['user'] = [
            'id'    => $admin['id'],
            'nom'   => $admin['nom'],
            'email' => $admin['email'],
            'type'  => 'admin'
        ];
        header('Location: Quiz.php?action=list');
        exit;
    }

    $_SESSION['errors'] = ["Identifiants incorrects."];
    header('Location: ../View/auth/login.php');
    exit;
}

// ----------------------------------------------------
// DEFAULT: SHOW LOGIN
// ----------------------------------------------------
header('Location: ../View/auth/login.php');
exit;
