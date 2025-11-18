<?php
$totalUsers = count($users);
$activeUsers = 0;
$blockedUsers = 0;
$associations = 0;
$joinedToday = 0;
$today = date('Y-m-d');

foreach ($users as $user) {
    if ($user['user_type'] === 'BackOffice') {
        continue;
    }

    if (strtolower($user['status']) === 'active') {
        $activeUsers++;
    } else {
        $blockedUsers++;
    }

    if (strtolower($user['user_type']) === 'association') {
        $associations++;
    }

    if (date('Y-m-d', strtotime($user['created_at'])) === $today) {
        $joinedToday++;
    }
}
?>

<?php
$currentAdminPage = 'dashboard';
include 'views/BackOffice/layouts/header.php';
?>

    <header class="admin-topbar">
        <div>
            <p class="topbar-meta">Centre de contrôle WeConnect</p>
            <h1>Tableau de bord</h1>
        </div>
        <div class="topbar-actions">
            <label class="search-field">
                <i class="fas fa-search"></i>
                <input type="search" placeholder="Rechercher un utilisateur">
            </label>
        </div>
    </header>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="admin-panel" id="flash-success">
            <span class="stat-trend positive">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </span>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="admin-panel" id="flash-error">
            <span class="stat-trend negative">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </span>
        </div>
    <?php endif; ?>

    <section class="admin-stats-grid" id="stats-panel">
        <article class="admin-stat-card">
            <span>Total utilisateurs</span>
            <strong><?php echo $totalUsers; ?></strong>
            <div class="stat-trend positive">
                <i class="fas fa-arrow-up"></i> +8% vs mois dernier
            </div>
        </article>
        <article class="admin-stat-card">
            <span>Citoyens actifs</span>
            <strong><?php echo $activeUsers; ?></strong>
            <div class="stat-trend positive">
                <i class="fas fa-user-check"></i> <?php echo $joinedToday; ?> aujourd'hui
            </div>
        </article>
        <article class="admin-stat-card">
            <span>Comptes à surveiller</span>
            <strong><?php echo $blockedUsers; ?></strong>
            <div class="stat-trend neutral">
                <i class="fas fa-shield-alt"></i> Modérations en attente
            </div>
        </article>
        <article class="admin-stat-card">
            <span>Associations</span>
            <strong><?php echo $associations; ?></strong>
            <div class="stat-trend positive">
                <i class="fas fa-people-group"></i> Nouveaux partenariats
            </div>
        </article>
    </section>

    <section class="admin-panels">
        <article class="admin-panel" id="users-panel">
            <div class="panel-header">
                <div>
                    <p class="topbar-meta">Gestion unifiée</p>
                    <h3>Utilisateurs</h3>
                </div>
                <div class="panel-actions">
                    <a href="#filters" class="admin-btn ghost">
                        <i class="fas fa-filter"></i> Filtrer
                    </a>
                    <a href="index.php?action=register&type=citoyen" class="admin-btn primary">
                        <i class="fas fa-user-plus"></i> Ajouter
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Profil</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($users as $user): ?>
                        <?php if($user['user_type'] != 'BackOffice'): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td>
                                    <strong><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></strong><br>
                                    <small><?php echo ucfirst($user['user_type']); ?></small>
                                </td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['phone'] ?: '—'; ?></td>
                                <td>
                                    <?php $statusClass = strtolower($user['status']) === 'active' ? 'active' : 'blocked'; ?>
                                    <span class="status-pill <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if($user['status'] == 'active'): ?>
                                        <a href="index.php?action=block_user&id=<?php echo $user['id']; ?>"
                                           class="action-chip"
                                           onclick="return confirm('Bloquer cet utilisateur ?')">
                                            <i class="fas fa-user-lock"></i> Bloquer
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?action=unblock_user&id=<?php echo $user['id']; ?>"
                                           class="action-chip"
                                           onclick="return confirm('Débloquer cet utilisateur ?')">
                                            <i class="fas fa-user-check"></i> Débloquer
                                        </a>
                                    <?php endif; ?>

                                    <a href="index.php?action=delete_user&id=<?php echo $user['id']; ?>"
                                       class="action-chip"
                                       onclick="return confirm('Supprimer définitivement cet utilisateur ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>

    </section>

<?php include 'views/BackOffice/layouts/footer.php'; ?>