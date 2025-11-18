<?php 
$pageTitle = 'Gestion des Publications';
include 'views/backoffice/header.php'; 
?>

<h2>Liste des Publications</h2>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Auteur</th>
            <th>Contenu</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($stmt->rowCount() > 0): ?>
            <?php while ($publication = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo $publication['id']; ?></td>
                    <td><?php echo htmlspecialchars($publication['nom_utilisateur']); ?></td>
                    <td><?php echo substr(htmlspecialchars($publication['contenu']), 0, 50) . '...'; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($publication['date_creation'])); ?></td>
                    <td>
                        <a href="index.php?action=supprimerPublication&id=<?php echo $publication['id']; ?>" 
                           class="btn-action delete" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')">
                            <i class="fas fa-trash"></i> Supprimer
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Aucune publication trouvée.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'views/backoffice/footer.php'; ?>
