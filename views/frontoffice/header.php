<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeConnect - Réseau Social</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <a href="index.php?action=filActualite" class="logo">WeConnect</a>
        </div>
        <nav class="nav-menu">
            <?php if (isset($_SESSION['id_utilisateur'])): ?>
                <a href="index.php?action=filActualite" class="nav-link"><i class="fas fa-home"></i> Accueil</a>
                <a href="index.php?action=profil" class="nav-link"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['nom_utilisateur']); ?></a>
                <a href="index.php?action=deconnexion" class="nav-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                <!-- Lien vers le backoffice (simplifié) -->
                <a href="index.php?action=dashboard" class="nav-link"><i class="fas fa-cog"></i> Admin</a>
            <?php else: ?>
                <a href="index.php?action=connexion" class="nav-link"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                <a href="index.php?action=inscription" class="nav-link"><i class="fas fa-user-plus"></i> Inscription</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main style="padding-top: 80px;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        <?php 
        // Affichage des messages de session (succès/erreur)
        if (isset($_SESSION['succes'])) {
            echo '<div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background-color: #d4edda; color: #155724;">' . $_SESSION['succes'] . '</div>';
            unset($_SESSION['succes']);
        }
        if (isset($_SESSION['erreur'])) {
            echo '<div class="alert alert-danger" style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background-color: #f8d7da; color: #721c24;">' . $_SESSION['erreur'] . '</div>';
            unset($_SESSION['erreur']);
        }
        ?>
