<?php $currentAdminPage = $currentAdminPage ?? ''; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeConnect • Centre d'administration</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <span>WeConnect</span>
            <small>Admin Center</small>
        </div>

        <nav class="sidebar-nav">
            <a href="index.php?action=admin_dashboard" class="sidebar-link <?php echo $currentAdminPage === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> Tableau de bord
            </a>
            <a href="index.php?action=admin_users" class="sidebar-link <?php echo $currentAdminPage === 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Utilisateurs
            </a>
            <a href="index.php?action=admin_dashboard#shortcuts" class="sidebar-link">
                <i class="fas fa-rocket"></i> Actions rapides
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="index.php?action=logout" class="sidebar-link logout">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </aside>

    <div class="admin-main">

