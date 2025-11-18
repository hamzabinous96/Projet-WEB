<?php 
$pageTitle = 'Gestion des Utilisateurs';
include 'views/backoffice/header.php'; 
?>

<h2>Liste des Utilisateurs</h2>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom d'utilisateur</th>
            <th>Email</th>
            <th>Date d'inscription</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($stmt->rowCount() > 0): ?>
            <?php while ($utilisateur = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo $utilisateur['id']; ?></td>
                    <td><?php echo htmlspecialchars($utilisateur['nom_utilisateur']); ?></td>
                    <td><?php echo htmlspecialchars($utilisateur['email']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($utilisateur['date_creation'])); ?></td>
                    <td>
                        <a href="index.php?action=supprimerUtilisateur&id=<?php echo $utilisateur['id']; ?>" 
                           class="btn-action delete" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                            <i class="fas fa-trash"></i> Supprimer
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Aucun utilisateur trouvé.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'views/backoffice/footer.php'; ?>
