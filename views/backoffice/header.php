<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeConnect - Backoffice</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/backoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="backoffice-wrapper">
    <aside class="sidebar">
        <div class="logo">
            <a href="index.php?action=dashboard">WeConnect Admin</a>
        </div>
        <nav class="nav-menu">
            <a href="index.php?action=dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="index.php?action=adminUtilisateurs" class="nav-link"><i class="fas fa-users"></i> Utilisateurs</a>
            <a href="index.php?action=adminPublications" class="nav-link"><i class="fas fa-file-alt"></i> Publications</a>
            <a href="index.php?action=statistiques" class="nav-link"><i class="fas fa-chart-line"></i> Statistiques</a>
            <hr>
            <a href="index.php?action=filActualite" class="nav-link"><i class="fas fa-arrow-left"></i> Retour au site</a>
            <a href="index.php?action=deconnexion" class="nav-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>
    
    <main class="content">
        <header class="topbar">
            <h1><?php echo $pageTitle ?? 'Tableau de bord'; ?></h1>
        </header>
        
        <div class="main-content">
            <?php 
            // Affichage des messages de session (succès/erreur)
            if (isset($_SESSION['succes'])) {
                echo '<div class="alert alert-success">' . $_SESSION['succes'] . '</div>';
                unset($_SESSION['succes']);
            }
            if (isset($_SESSION['erreur'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['erreur'] . '</div>';
                unset($_SESSION['erreur']);
            }
            ?>
