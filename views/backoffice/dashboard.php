<?php 
$pageTitle = 'Tableau de bord';
include 'views/backoffice/header.php'; 

// Les modèles sont déjà chargés par index.php
$modeleUtilisateur = new Utilisateur();
$modelePublication = new Publication();
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Utilisateurs inscrits</h3>
        <p class="stat-number"><?php echo $modeleUtilisateur->compter(); ?></p>
        <i class="fas fa-users icon"></i>
    </div>
    <div class="stat-card">
        <h3>Publications totales</h3>
        <p class="stat-number"><?php echo $modelePublication->compter(); ?></p>
        <i class="fas fa-file-alt icon"></i>
    </div>
    <div class="stat-card">
        <h3>Nouveaux utilisateurs (7j)</h3>
        <p class="stat-number">N/A</p>
        <i class="fas fa-user-plus icon"></i>
    </div>
    <div class="stat-card">
        <h3>Publications (24h)</h3>
        <p class="stat-number">N/A</p>
        <i class="fas fa-chart-line icon"></i>
    </div>
</div>

<div class="recent-activity">
    <h2>Activité récente</h2>
    <p>Cette section afficherait les dernières inscriptions et publications.</p>
</div>

<?php include 'views/backoffice/footer.php'; ?>
