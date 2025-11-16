<?php
// ------------------------------------------------------
// DEBUG (à laisser activé en développement)
// ------------------------------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ------------------------------------------------------
// 1. CONNEXION A LA BASE weconnect
// ------------------------------------------------------
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=weconnect;charset=utf8',
        'root', // user XAMPP par défaut
        '',     // mot de passe XAMPP par défaut
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// ------------------------------------------------------
// 2. SUPPRESSION D'UN ARTICLE (DELETE)
// ------------------------------------------------------
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = $pdo->prepare('DELETE FROM blogs WHERE id_blog = ?');
    $stmt->execute([$id]);

    header('Location: back.php');
    exit;
}

// ------------------------------------------------------
// 3. SAUVEGARDE (CREATE ou UPDATE) quand le formulaire est soumis
// ------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_blog       = !empty($_POST['id_blog']) ? (int)$_POST['id_blog'] : null;
    $titre_blog    = trim($_POST['titre_blog']);
    $contenu_blog  = trim($_POST['contenu_blog']);
    $auteur_blog   = trim($_POST['auteur_blog']);
    $categories    = trim($_POST['categories']);
    $est_publie    = isset($_POST['est_publie_blog']) ? (int)$_POST['est_publie_blog'] : 0;

    if ($auteur_blog === '') {
        $auteur_blog = 'WeConnect';
    }

    if ($id_blog) {
        // UPDATE
        $sql = "UPDATE blogs
                SET titre_blog = :titre,
                    contenu_blog = :contenu,
                    auteur_blog = :auteur,
                    categories = :categorie,
                    est_publie_blog = :publie,
                    date_modification_blog = NOW()
                WHERE id_blog = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titre'    => $titre_blog,
            ':contenu'  => $contenu_blog,
            ':auteur'   => $auteur_blog,
            ':categorie'=> $categories,
            ':publie'   => $est_publie,
            ':id'       => $id_blog
        ]);
    } else {
        // CREATE
        $sql = "INSERT INTO blogs 
                (titre_blog, contenu_blog, auteur_blog, categories, date_creation_blog, est_publie_blog, created_by)
                VALUES (:titre, :contenu, :auteur, :categorie, NOW(), :publie, :created_by)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titre'      => $titre_blog,
            ':contenu'    => $contenu_blog,
            ':auteur'     => $auteur_blog,
            ':categorie'  => $categories,
            ':publie'     => $est_publie,
            ':created_by' => $auteur_blog
        ]);
    }

    header('Location: back.php');
    exit;
}

// ------------------------------------------------------
// 4. LECTURE : récupérer tous les articles pour la liste
// ------------------------------------------------------
$stmt = $pdo->query('SELECT * FROM blogs ORDER BY date_creation_blog DESC');
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office - Gestion Blog & Commentaires | WeConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="style1.css" rel="stylesheet">
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-hands-helping"></i> WeConnect</h3>
            <p style="font-size: 0.85rem; color: #94a3b8; margin: 5px 0 0;">Back Office</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" class="active"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li><a href="#"><i class="fas fa-newspaper"></i> Articles</a></li>
            <li><a href="#"><i class="fas fa-comments"></i> Commentaires</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Utilisateurs</a></li>
            <li><a href="#"><i class="fas fa-folder"></i> Catégories</a></li>
            <li><a href="#"><i class="fas fa-tags"></i> Tags</a></li>
            <li><a href="#"><i class="fas fa-calendar"></i> Événements</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> Paramètres</a></li>
            <li><a href="#"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </div>

    <!-- CONTENU PRINCIPAL -->
    <div class="main-content">
        <!-- Top bar -->
        <div class="top-nav">
            <div class="search-bar">
                <i class="fas fa-search" style="color: #9ca3af;"></i>
                <input type="text" placeholder="Rechercher...">
            </div>
            <div class="user-profile">
                <div class="notification-icon">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">5</span>
                </div>
                <div class="user-avatar">AD</div>
                <div>
                    <div style="font-weight: 600;">Admin</div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Administrateur</div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #dbeafe; color: #1e40af;">
                    <i class="fas fa-newspaper"></i>
                </div>
                <h3>6</h3>
                <p>Articles publiés</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #d1fae5; color: #065f46;">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>4</h3>
                <p>Commentaires</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fef3c7; color: #92400e;">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>23</h3>
                <p>En attente</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fee2e2; color: #991b1b;">
                    <i class="fas fa-eye"></i>
                </div>
                <h3>45.2K</h3>
                <p>Vues totales</p>
            </div>
        </div>

        <!-- Contenu -->
        <div class="content-section">
            <div class="custom-tabs">
                <button class="tab-btn active" onclick="switchTab(event,'articles')">
                    <i class="fas fa-newspaper"></i> Gestion des Articles
                </button>
                <button class="tab-btn" onclick="switchTab(event,'comments')">
                    <i class="fas fa-comments"></i> Gestion des Commentaires
                </button>
            </div>

            <!-- ONGLET ARTICLES -->
            <div id="articles" class="tab-content active">
                <div class="section-header">
                    <h2>Liste des Articles</h2>
                    <button class="btn-primary" type="button" onclick="newArticle()">
                        <i class="fas fa-plus"></i> Nouvel Article
                    </button>
                </div>

                <div class="filters">
                    <select class="filter-select">
                        <option>Tous les statuts</option>
                        <option>Publié</option>
                        <option>Brouillon</option>
                    </select>
                    <select class="filter-select">
                        <option>Toutes les catégories</option>
                        <option>Solidarité</option>
                        <option>Éducation</option>
                        <option>Santé</option>
                    </select>
                    <input type="date" class="filter-select">
                </div>

                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Auteur</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Vues</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($articles)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:20px;">
                                Aucun article pour l’instant. Cliquez sur <strong>“Nouvel Article”</strong> pour en créer un.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($articles as $article): ?>
                            <tr
                                data-id="<?= $article['id_blog']; ?>"
                                data-titre="<?= htmlspecialchars($article['titre_blog']); ?>"
                                data-contenu="<?= htmlspecialchars($article['contenu_blog']); ?>"
                                data-categorie="<?= htmlspecialchars($article['categories']); ?>"
                                data-auteur="<?= htmlspecialchars($article['auteur_blog']); ?>"
                                data-est-publie="<?= (int)$article['est_publie_blog']; ?>"
                            >
                                <td><img src="hy.png" alt="Article" class="article-thumbnail"></td>

                                <td><strong><?= htmlspecialchars($article['titre_blog']); ?></strong></td>

                                <td><?= htmlspecialchars($article['categories']); ?></td>

                                <td><?= htmlspecialchars($article['auteur_blog']); ?></td>

                                <td>
                                    <?= $article['date_creation_blog']
                                        ? date('d M Y', strtotime($article['date_creation_blog']))
                                        : '-'; ?>
                                </td>

                                <td>
                                    <?php if ($article['est_publie_blog']): ?>
                                        <span class="status-badge status-published">Publié</span>
                                    <?php else: ?>
                                        <span class="status-badge status-draft">Brouillon</span>
                                    <?php endif; ?>
                                </td>

                                <td>-</td>

                                <td>
                                    <!-- BOUTON MODIFIER -->
                                    <button class="action-btn btn-edit" type="button" onclick="editArticle(this)">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <!-- BOUTON SUPPRIMER -->
                                    <a href="back.php?delete=<?= $article['id_blog']; ?>"
                                       class="action-btn btn-delete"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ONGLET COMMENTAIRES (toujours statique pour l’instant) -->
            <div id="comments" class="tab-content">
                <div class="section-header">
                    <h2>Liste des Commentaires</h2>
                    <div class="filters">
                        <select class="filter-select">
                            <option>Tous les statuts</option>
                            <option>Approuvé</option>
                            <option>En attente</option>
                            <option>Spam</option>
                        </select>
                    </div>
                </div>

                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Auteur</th>
                            <th>Commentaire</th>
                            <th>Article</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Démo statique -->
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="user-avatar" style="width: 35px; height: 35px; font-size: 0.9rem;">MA</div>
                                    <div>
                                        <strong>Marie Dubois</strong>
                                        <div style="font-size: 0.85rem; color: #6b7280;">marie@email.com</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="comment-preview">
                                    Excellent article ! WeConnect change vraiment la donne en matière de solidarité...
                                </div>
                            </td>
                            <td>Une plateforme qui unit...</td>
                            <td>13 Nov 2025</td>
                            <td><span class="status-badge status-approved">Approuvé</span></td>
                            <td>
                                <button class="action-btn btn-edit" type="button" onclick="openModal('commentModal')">
                                    <i class="fas fa-eye"></i> Voir
                                </button>
                                <button class="action-btn btn-delete" type="button" onclick="confirmDelete('commentaire')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODALE ARTICLE -->
    <div id="articleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-newspaper"></i> <span id="modalArticleTitle">Nouvel Article</span></h3>
                <button class="close-btn" type="button" onclick="closeModal('articleModal')">&times;</button>
            </div>
            <form id="articleForm" method="post" action="">
                <input type="hidden" id="articleId" name="id_blog">

                <div class="form-group">
                    <label>Titre de l'article *</label>
                    <input type="text" id="articleTitre" name="titre_blog" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Catégorie *</label>
                    <select id="articleCategorie" name="categories" class="form-control" required>
                        <option value="">Sélectionner une catégorie</option>
                        <option value="Solidarité">Solidarité</option>
                        <option value="Éducation">Éducation</option>
                        <option value="Santé">Santé</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Contenu *</label>
                    <textarea id="articleContenu" name="contenu_blog" class="form-control" rows="10" required></textarea>
                </div>

                <div class="form-group">
                    <label>Auteur</label>
                    <input type="text" id="articleAuteur" name="auteur_blog" class="form-control" value="WeConnect">
                </div>

                <div class="form-group">
                    <label>Statut</label>
                    <select id="articleStatut" name="est_publie_blog" class="form-control">
                        <option value="1">Publié</option>
                        <option value="0">Brouillon</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" onclick="closeModal('articleModal')">
                        Annuler
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODALE COMMENTAIRE (démo simple) -->
    <div id="commentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-comment"></i> Détails du Commentaire</h3>
                <button class="close-btn" type="button" onclick="closeModal('commentModal')">&times;</button>
            </div>
            <div style="margin-top:20px;">
                <p>Exemple de contenu de modale commentaire.</p>
            </div>
        </div>
    </div>

    <script>
        // Changement d'onglet
        function switchTab(evt, tabName) {
            const tabs = document.querySelectorAll('.tab-content');
            const buttons = document.querySelectorAll('.tab-btn');
            tabs.forEach(t => t.classList.remove('active'));
            buttons.forEach(b => b.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            evt.target.classList.add('active');
        }

        // Ouvrir / fermer modale
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Nouvel article => vider le formulaire
        function newArticle() {
            document.getElementById('modalArticleTitle').textContent = 'Nouvel Article';
            document.getElementById('articleId').value = '';
            document.getElementById('articleTitre').value = '';
            document.getElementById('articleCategorie').value = '';
            document.getElementById('articleContenu').value = '';
            document.getElementById('articleAuteur').value = 'WeConnect';
            document.getElementById('articleStatut').value = '1';
            openModal('articleModal');
        }

        // Modifier article => remplir le formulaire à partir de la ligne
        function editArticle(button) {
            const row = button.closest('tr');

            document.getElementById('modalArticleTitle').textContent = "Modifier l'article";
            document.getElementById('articleId').value        = row.dataset.id;
            document.getElementById('articleTitre').value     = row.dataset.titre;
            document.getElementById('articleCategorie').value = row.dataset.categorie;
            document.getElementById('articleContenu').value   = row.dataset.contenu;
            document.getElementById('articleAuteur').value    = row.dataset.auteur;
            document.getElementById('articleStatut').value    = row.dataset.estPublie;

            openModal('articleModal');
        }

        // Confirmation suppression de commentaire (démo)
        function confirmDelete(type) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer cet ${type} ?`)) {
                alert(`${type.charAt(0).toUpperCase() + type.slice(1)} supprimé avec succès !`);
                closeModal('commentModal');
            }
        }

        // Fermer modale en cliquant à l'extérieur
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>