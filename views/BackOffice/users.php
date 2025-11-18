<?php
$currentAdminPage = 'users';
include 'views/BackOffice/layouts/header.php';
?>

<header class="admin-topbar">
    <div>
        <p class="topbar-meta">Centre de contrôle WeConnect</p>
        <h1>Utilisateurs</h1>
    </div>
    <div class="topbar-actions">
        <label class="search-field">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="Rechercher un utilisateur">
        </label>
        <a href="index.php?action=register&type=citoyen" class="admin-btn primary">
            <i class="fas fa-user-plus"></i> Ajouter un citoyen
        </a>
    </div>
</header>

<?php if(isset($_SESSION['success'])): ?>
    <div class="admin-panel">
        <span class="stat-trend positive">
            <i class="fas fa-check-circle"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </span>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="admin-panel">
        <span class="stat-trend negative">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </span>
    </div>
<?php endif; ?>

<section class="admin-panel">
    <div class="panel-header">
        <div>
            <p class="topbar-meta">Liste complète</p>
            <h3>Utilisateurs enregistrés</h3>
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
                                <a href="index.php?action=block_user&id=<?php echo $user['id']; ?>&from=users"
                                   class="action-chip"
                                   onclick="return confirm('Bloquer cet utilisateur ?')">
                                    <i class="fas fa-user-lock"></i> Bloquer
                                </a>
                            <?php else: ?>
                                <a href="index.php?action=unblock_user&id=<?php echo $user['id']; ?>&from=users"
                                   class="action-chip"
                                   onclick="return confirm('Débloquer cet utilisateur ?')">
                                    <i class="fas fa-user-check"></i> Débloquer
                                </a>
                            <?php endif; ?>

                            <a href="index.php?action=delete_user&id=<?php echo $user['id']; ?>&from=users"
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
</section>

<?php include 'views/BackOffice/layouts/footer.php'; ?>

