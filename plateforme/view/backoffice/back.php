<?php
// CONFIG PHP / BDD

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=weconnect;charset=utf8',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// SUPPRESSION ARTICLE (DELETE)
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM blogs WHERE id_blog = ?");
    $stmt->execute([$id]);
    header("Location: back.php?tab=articles");
    exit;
}

// GESTION DES COMMENTAIRES
// Suppression d'un commentaire
if (isset($_GET['delete_comment'])) {
    $comment_id = (int) $_GET['delete_comment'];
    $stmt = $pdo->prepare("DELETE FROM commentaires WHERE id_commentaire = ?");
    $stmt->execute([$comment_id]);
    header("Location: back.php?tab=comments");
    exit;
}

// Mettre un commentaire en brouillon/actif
if (isset($_GET['toggle_comment'])) {
    $comment_id = (int) $_GET['toggle_comment'];
    $stmt = $pdo->prepare("UPDATE commentaires SET est_approuve = NOT est_approuve WHERE id_commentaire = ?");
    $stmt->execute([$comment_id]);
    header("Location: back.php?tab=comments");
    exit;
}

// RÉCUPÉRATION ARTICLE À MODIFIER (edit)
$editing = false;
$article_edit = [
    'id_blog'        => '',
    'titre_blog'     => '',
    'contenu_blog'   => '',
    'categorie_blog' => '',
    'est_publie_blog'=> 1,
    'image_blog'     => ''
];

if (isset($_GET['edit'])) {
    $editing = true;
    $id = (int) $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id_blog = ?");
    $stmt->execute([$id]);
    $article_edit = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$article_edit) {
        $editing = false;
        $article_edit = [
            'id_blog'        => '',
            'titre_blog'     => '',
            'contenu_blog'   => '',
            'categorie_blog' => '',
            'est_publie_blog'=> 1,
            'image_blog'     => ''
        ];
    }
}

// SAUVEGARDE ARTICLE (CREATE / UPDATE)
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // VALIDATION DES CHAMPS
    $titre   = trim($_POST['titre_blog'] ?? '');
    $contenu = trim($_POST['contenu_blog'] ?? '');
    $cat     = trim($_POST['categorie_blog'] ?? '');
    
    // Validation du titre
    if (empty($titre)) {
        $errors['titre_blog'] = "Le titre est obligatoire";
    } elseif (strlen($titre) < 3) {
        $errors['titre_blog'] = "Le titre doit contenir au moins 3 caractères";
    }
    
    // Validation de la catégorie
    if (empty($cat)) {
        $errors['categorie_blog'] = "La catégorie est obligatoire";
    }
    
    // Validation du contenu
    if (empty($contenu)) {
        $errors['contenu_blog'] = "Le contenu est obligatoire";
    } elseif (strlen($contenu) < 10) {
        $errors['contenu_blog'] = "Le contenu doit contenir au moins 10 caractères";
    }
    
    // GESTION IMAGE UPLOAD
    $imageName = $article_edit['image_blog'] ?? null;

    if (!empty($_FILES['image_blog']['name']) && $_FILES['image_blog']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['image_blog']['tmp_name'];
        $name = time() . "_" . basename($_FILES['image_blog']['name']);
        $uploadDir = "../frontoffice/assets/";
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (move_uploaded_file($tmp, $uploadDir . $name)) {
            $imageName = $name;
        } else {
            $errors['image_blog'] = "Erreur lors de l'upload de l'image";
        }
    }

    $id      = !empty($_POST['id_blog']) ? (int) $_POST['id_blog'] : null;
    $publie  = isset($_POST['est_publie_blog']) ? 1 : 0;
    $cree_par  = 1;

    // Si pas d'erreurs, on sauvegarde
    if (empty($errors)) {
        try {
            if ($id) {
                // MODIFICATION
                $sql = "UPDATE blogs
                        SET titre_blog = :t,
                            contenu_blog = :c,
                            categorie_blog = :cat,
                            est_publie_blog = :p,
                            date_modification_blog = NOW()";
                
                if ($imageName !== null) {
                    $sql .= ", image_blog = :img";
                }
                
                $sql .= " WHERE id_blog = :id";
                
                $stmt = $pdo->prepare($sql);
                $params = [
                    ':t'   => $titre,
                    ':c'   => $contenu,
                    ':cat' => $cat,
                    ':p'   => $publie,
                    ':id'  => $id
                ];
                
                if ($imageName !== null) {
                    $params[':img'] = $imageName;
                }
                
                $stmt->execute($params);
                
            } else {
                // NOUVEL ARTICLE
                $sql = "INSERT INTO blogs
                        (titre_blog, contenu_blog, categorie_blog,
                         date_creation_blog, image_blog, est_publie_blog, cree_par_blog)
                        VALUES (:t, :c, :cat, NOW(), :img, :p, :cree_par)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':t'         => $titre,
                    ':c'         => $contenu,
                    ':cat'       => $cat,
                    ':img'       => $imageName,
                    ':p'         => $publie,
                    ':cree_par'  => $cree_par
                ]);
            }

            header("Location: back.php?success=1&tab=articles");
            exit;
            
        } catch (PDOException $e) {
            $errors['general'] = "Erreur lors de la sauvegarde: " . $e->getMessage();
        }
    }
}

// PAGINATION ARTICLES
$articles_par_page = 5;
$page_articles = isset($_GET['page_articles']) ? max(1, (int)$_GET['page_articles']) : 1;
$offset_articles = ($page_articles - 1) * $articles_par_page;

// Compter le nombre total d'articles
$stmt = $pdo->query("SELECT COUNT(*) as total FROM blogs");
$total_articles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages_articles = ceil($total_articles / $articles_par_page);

// LISTE DES ARTICLES avec pagination
$stmt = $pdo->prepare("SELECT * FROM blogs, categories WHERE categorie_blog = id_categorie ORDER BY date_creation_blog DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $articles_par_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset_articles, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques articles
$articles_publies = $pdo->query("SELECT COUNT(*) as count FROM blogs WHERE est_publie_blog = 1")->fetch(PDO::FETCH_ASSOC)['count'];
$articles_brouillon = $total_articles - $articles_publies;

// PAGINATION COMMENTAIRES
$commentaires_par_page = 10;
$page_comments = isset($_GET['page_comments']) ? max(1, (int)$_GET['page_comments']) : 1;
$offset_comments = ($page_comments - 1) * $commentaires_par_page;

// Compter le nombre total de commentaires
$stmt = $pdo->query("SELECT COUNT(*) as total FROM commentaires");
$total_commentaires = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages_comments = ceil($total_commentaires / $commentaires_par_page);

// Récupérer les commentaires avec informations des articles
$stmt = $pdo->prepare("
    SELECT c.*, b.titre_blog
    FROM commentaires c 
    LEFT JOIN blogs b ON c.id_blog = b.id_blog 
    ORDER BY c.date_creation_comment DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $commentaires_par_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset_comments, PDO::PARAM_INT);
$stmt->execute();
$commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques des commentaires
$commentaires_approuves = $pdo->query("SELECT COUNT(*) as count FROM commentaires WHERE est_approuve = 1")->fetch(PDO::FETCH_ASSOC)['count'];
$commentaires_en_attente = $total_commentaires - $commentaires_approuves;

// LISTE DES CATEGORIES
$stmt = $pdo->query("SELECT * FROM categories ORDER BY nom_categorie");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// État du formulaire
$form_ouvert = isset($_GET['form']) && $_GET['form'] === 'ouvert';
if ($editing) {
    $form_ouvert = true;
}

// Déterminer l'onglet actif
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'articles';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Blogs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="back.css" />
    <style>
        .form-section {
            display: <?= $form_ouvert ? 'block' : 'none' ?>;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        
        .toggle-form-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
        }
        
        .toggle-form-btn:hover {
            background: #45a049;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
            gap: 10px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background: #f5f5f5;
        }
        
        .pagination .current {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .pagination .disabled {
            color: #ccc;
            pointer-events: none;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        /* Styles pour les onglets */
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab.active {
            border-bottom-color: #007bff;
            color: #007bff;
            font-weight: bold;
        }

        .tab:hover {
            background: #f8f9fa;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Styles pour les commentaires */
        .comment-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .comment-author {
            font-weight: bold;
            color: #333;
        }

        .comment-date {
            color: #666;
            font-size: 14px;
        }

        .comment-article {
            color: #007bff;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .comment-content {
            color: #333;
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .comment-actions {
            display: flex;
            gap: 10px;
        }

        .btn-moderation {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.3s ease;
        }

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background: #218838;
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background: #c82333;
        }

        .comment-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 8px;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    
    <div class="sidebar">
        <div class="sidebar-header">
            Admin Panel
        </div>
        <nav class="sidebar-menu">
            <a href="backoffice.html" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Tableau de Bord</span>
            </a>
            <a href="#" class="menu-item ">
                <i class="fas fa-users"></i>
                <span>Participants</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-project-diagram"></i>
                <span>Projects</span>
            </a>
            <a href="#" class="menu-item active">
                <i class="fas fa-building"></i>
                <span>Blogs</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Associations</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Calendrier</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Gestion des Blogs et Commentaires</h1>
        </div>

        <!-- Messages de succès/erreur -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> Opération effectuée avec succès !
            </div>
        <?php endif; ?>

        <!-- Onglets -->
        <div class="tabs">
            <button class="tab <?= $current_tab === 'articles' ? 'active' : '' ?>" onclick="switchTab('articles')">
                <i class="fas fa-newspaper"></i> Articles (<?= $total_articles ?>)
            </button>
            <button class="tab <?= $current_tab === 'comments' ? 'active' : '' ?>" onclick="switchTab('comments')">
                <i class="fas fa-comments"></i> Commentaires (<?= $total_commentaires ?>)
            </button>
        </div>

        <!-- Contenu des onglets -->
        <div id="articles-tab" class="tab-content <?= $current_tab === 'articles' ? 'active' : '' ?>">
            <!-- Stats Cards pour les articles -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Blogs créés</div>
                    <div class="stat-value"><?= $total_articles ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Blogs publiés</div>
                    <div class="stat-value"><?= $articles_publies ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Brouillons</div>
                    <div class="stat-value"><?= $articles_brouillon ?></div>
                </div>
            </div>

            <!-- Bouton pour ouvrir/fermer le formulaire -->
            <button class="toggle-form-btn" onclick="toggleForm()">
                <i class="fas fa-<?= $form_ouvert ? 'minus' : 'plus' ?>"></i>
                <?= $form_ouvert ? 'Fermer le formulaire' : 'Nouvel article' ?>
            </button>

            <div class="form-section" id="formSection">
                <h2>
                    <i class="fas fa-<?= $editing ? 'edit' : 'plus-circle' ?>"></i> 
                    <?= $editing ? 'Modifier un article' : 'Nouvel article' ?>
                </h2>
                <form method="post" action="back.php" enctype="multipart/form-data">
                    <input type="hidden" name="id_blog" value="<?= htmlspecialchars($article_edit['id_blog']) ?>">

                    <div class="form-group">
                        <label>Titre de l'article *</label>
                        <input type="text" name="titre_blog"
                               placeholder="Entrez le titre de votre article"
                               value="<?= htmlspecialchars($_POST['titre_blog'] ?? $article_edit['titre_blog']) ?>"
                               class="<?= isset($errors['titre_blog']) ? 'input-error' : '' ?>">
                        <?php if (isset($errors['titre_blog'])): ?>
                            <span class="error-message"><?= $errors['titre_blog'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Catégorie *</label>
                        <select name="categorie_blog" class="<?= isset($errors['categorie_blog']) ? 'input-error' : '' ?>">
                            <option value="">Choisir une catégorie</option>
                            <?php foreach ($categories as $categorie): ?>
                            <option value="<?= $categorie["id_categorie"] ?>" 
                                    <?= ($categorie["id_categorie"] == ($_POST['categorie_blog'] ?? $article_edit['categorie_blog'])) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categorie["nom_categorie"]) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['categorie_blog'])): ?>
                            <span class="error-message"><?= $errors['categorie_blog'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Contenu de l'article *</label>
                        <textarea name="contenu_blog"
                                  placeholder="Rédigez le contenu de votre article..."
                                  class="<?= isset($errors['contenu_blog']) ? 'input-error' : '' ?>"><?= htmlspecialchars($_POST['contenu_blog'] ?? $article_edit['contenu_blog']) ?></textarea>
                        <?php if (isset($errors['contenu_blog'])): ?>
                            <span class="error-message"><?= $errors['contenu_blog'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="checkbox-container">
                        <input type="checkbox" name="est_publie_blog" id="publier"
                               <?= !empty($_POST['est_publie_blog'] ?? $article_edit['est_publie_blog']) ? 'checked' : '' ?>>
                        <label for="publier">Publier immédiatement cet article</label>
                    </div>

                    <div class="form-group mb-3">
                        <label>Image du blog</label>
                        <input type="file" name="image_blog" accept="image/*" class="form-control">
                        <?php if (isset($errors['image_blog'])): ?>
                            <span class="error-message"><?= $errors['image_blog'] ?></span>
                        <?php endif; ?>

                        <?php if ($editing && !empty($article_edit['image_blog'])): ?>
                            <div style="margin-top: 10px;">
                                <img src="../frontoffice/assets/<?= htmlspecialchars($article_edit['image_blog']) ?>" 
                                    style="max-width:150px; border-radius:6px;">
                                <p style="font-size: 12px; color: #666;">Image actuelle</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="button-group">
                        <button type="submit">
                            <i class="fas fa-save"></i> <?= $editing ? 'Mettre à jour' : 'Créer' ?>
                        </button>
                        <?php if ($editing): ?>
                        <a href="back.php?tab=articles" class="cancel-link">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Table Section -->
            <div class="table-section">
                <div class="table-header">
                    <h2>Liste des Blogs (Page <?= $page_articles ?> sur <?= $total_pages_articles ?>)</h2>
                </div>

                <?php if (empty($articles)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucun blog pour l'instant</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>ID</th>
                                <th>Nom du blog</th>
                                <th>Catégorie</th>
                                <th>Date création</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($articles as $a): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($a['image_blog'])): ?>
                                        <img src="../frontoffice/assets/<?= htmlspecialchars($a['image_blog']) ?>" 
                                            style="width:70px; height:50px; object-fit:cover; border-radius:6px;">
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><?= $a['id_blog'] ?></td>
                                <td><?= htmlspecialchars($a['titre_blog']) ?></td>
                                <td><?= htmlspecialchars($a['nom_categorie']) ?></td>
                                <td><?= date('d/m/Y', strtotime($a['date_creation_blog'])) ?></td>
                                <td>
                                    <span class="badge badge-<?= $a['est_publie_blog'] ? 'publié' : 'brouillon' ?>">
                                        <?= $a['est_publie_blog'] ? 'Publié' : 'Brouillon' ?>
                                    </span>
                                </td>
                                <td>
                                    <a class="btn btn-modifier" href="back.php?edit=<?= $a['id_blog'] ?>&page_articles=<?= $page_articles ?>&tab=articles">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a class="btn btn-supprimer"
                                       href="back.php?delete=<?= $a['id_blog'] ?>&page_articles=<?= $page_articles ?>&tab=articles"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($total_pages_articles > 1): ?>
                    <div class="pagination">
                        <?php if ($page_articles > 1): ?>
                            <a href="back.php?tab=articles&page_articles=<?= $page_articles - 1 ?><?= $form_ouvert ? '&form=ouvert' : '' ?>">
                                <i class="fas fa-chevron-left"></i> Précédent
                            </a>
                        <?php else: ?>
                            <span class="disabled"><i class="fas fa-chevron-left"></i> Précédent</span>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages_articles; $i++): ?>
                            <?php if ($i == $page_articles): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="back.php?tab=articles&page_articles=<?= $i ?><?= $form_ouvert ? '&form=ouvert' : '' ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page_articles < $total_pages_articles): ?>
                            <a href="back.php?tab=articles&page_articles=<?= $page_articles + 1 ?><?= $form_ouvert ? '&form=ouvert' : '' ?>">
                                Suivant <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="disabled">Suivant <i class="fas fa-chevron-right"></i></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="comments-tab" class="tab-content <?= $current_tab === 'comments' ? 'active' : '' ?>">
            <!-- Stats Cards pour les commentaires -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total commentaires</div>
                    <div class="stat-value"><?= $total_commentaires ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Commentaires approuvés</div>
                    <div class="stat-value"><?= $commentaires_approuves ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">En attente</div>
                    <div class="stat-value"><?= $commentaires_en_attente ?></div>
                </div>
            </div>

            <!-- Liste des commentaires -->
            <div class="table-section">
                <div class="table-header">
                    <h2>Gestion des Commentaires (Page <?= $page_comments ?> sur <?= $total_pages_comments ?>)</h2>
                </div>

                <?php if (empty($commentaires)): ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <p>Aucun commentaire pour l'instant</p>
                    </div>
                <?php else: ?>
                    <div class="comments-list">
                        <?php foreach ($commentaires as $comment): ?>
                            <div class="comment-item">
                                <div class="comment-header">
                                    <div class="comment-author">
                                        <?= htmlspecialchars($comment['auteur_comment'] ?? 'Anonyme') ?>
                                        <span class="comment-status <?= $comment['est_approuve'] ? 'status-approved' : 'status-pending' ?>">
                                            <?= $comment['est_approuve'] ? 'Approuvé' : 'En attente' ?>
                                        </span>
                                    </div>
                                    <div class="comment-date">
                                        <?= date('d/m/Y H:i', strtotime($comment['date_creation_comment'])) ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($comment['titre_blog'])): ?>
                                    <div class="comment-article">
                                        <i class="fas fa-file-alt"></i>
                                        Article : <?= htmlspecialchars($comment['titre_blog']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="comment-content">
                                    <?= nl2br(htmlspecialchars($comment['contenu_comment'])) ?>
                                </div>
                                
                                <div class="comment-actions">
                                    <button class="btn-moderation btn-approve" 
                                            onclick="toggleComment(<?= $comment['id_commentaire'] ?>)">
                                        <i class="fas fa-<?= $comment['est_approuve'] ? 'ban' : 'check' ?>"></i>
                                        <?= $comment['est_approuve'] ? 'Désapprouver' : 'Approuver' ?>
                                    </button>
                                    
                                    <button class="btn-moderation btn-reject" 
                                            onclick="deleteComment(<?= $comment['id_commentaire'] ?>)">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination pour les commentaires -->
                    <?php if ($total_pages_comments > 1): ?>
                    <div class="pagination">
                        <?php if ($page_comments > 1): ?>
                            <a href="back.php?tab=comments&page_comments=<?= $page_comments - 1 ?>">
                                <i class="fas fa-chevron-left"></i> Précédent
                            </a>
                        <?php else: ?>
                            <span class="disabled"><i class="fas fa-chevron-left"></i> Précédent</span>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages_comments; $i++): ?>
                            <?php if ($i == $page_comments): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="back.php?tab=comments&page_comments=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page_comments < $total_pages_comments): ?>
                            <a href="back.php?tab=comments&page_comments=<?= $page_comments + 1 ?>">
                                Suivant <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="disabled">Suivant <i class="fas fa-chevron-right"></i></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleForm() {
            const formSection = document.getElementById('formSection');
            const toggleBtn = document.querySelector('.toggle-form-btn');
            const isVisible = formSection.style.display !== 'none';
            
            if (isVisible) {
                formSection.style.display = 'none';
                toggleBtn.innerHTML = '<i class="fas fa-plus"></i> Nouvel article';
                const url = new URL(window.location);
                url.searchParams.delete('form');
                window.history.replaceState({}, document.title, url.toString());
            } else {
                formSection.style.display = 'block';
                toggleBtn.innerHTML = '<i class="fas fa-minus"></i> Fermer le formulaire';
                const url = new URL(window.location);
                url.searchParams.set('form', 'ouvert');
                window.history.replaceState({}, document.title, url.toString());
            }
        }

        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.querySelectorAll('.tab').forEach(button => {
                button.classList.remove('active');
            });
            
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
            
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, document.title, url.toString());
        }

        function toggleComment(commentId) {
            if (confirm('Voulez-vous changer le statut de ce commentaire ?')) {
                window.location.href = 'back.php?toggle_comment=' + commentId + '&tab=comments';
            }
        }

        function deleteComment(commentId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer définitivement ce commentaire ?')) {
                window.location.href = 'back.php?delete_comment=' + commentId + '&tab=comments';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const currentTab = '<?= $current_tab ?>';
            if (currentTab) {
                document.querySelectorAll('.tab').forEach(button => {
                    button.classList.remove('active');
                });
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                document.querySelector(`.tab[onclick="switchTab('${currentTab}')"]`).classList.add('active');
                document.getElementById(currentTab + '-tab').classList.add('active');
            }
            
            <?php if ($editing): ?>
                document.getElementById('formSection').style.display = 'block';
                document.querySelector('.toggle-form-btn').innerHTML = '<i class="fas fa-minus"></i> Fermer le formulaire';
            <?php endif; ?>
        });
    </script>
</body>
</html>