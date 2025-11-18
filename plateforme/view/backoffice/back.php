<?php
// ===========================================
// CONFIG PHP / BDD
// ===========================================
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

// ===========================================
// SUPPRESSION (DELETE)
// ===========================================
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM blogs WHERE id_blog = ?");
    $stmt->execute([$id]);
    header("Location: back.php");
    exit;
}

// ===========================================
// RÉCUPÉRATION ARTICLE À MODIFIER (edit)
// ===========================================
$editing = false;
$article_edit = [
    'id_blog'        => '',
    'titre_blog'     => '',
    'contenu_blog'   => '',
    'categorie_blog'     => '',
    'est_publie_blog'=> 1
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
            'categorie_blog'     => '',
            'est_publie_blog'=> 1
        ];
    }
}

// ===========================================
// SAUVEGARDE (CREATE / UPDATE)
// ===========================================
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // -----------------------------
    // VALIDATION DES CHAMPS
    // -----------------------------
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
    
    // -----------------------------
    // GESTION IMAGE UPLOAD
    // -----------------------------
    $imageName = $article_edit['image_blog'] ?? null;

    if (!empty($_FILES['image_blog']['name'])) {
        $tmp = $_FILES['image_blog']['tmp_name'];
        $name = time() . "_" . basename($_FILES['image_blog']['name']);
        move_uploaded_file($tmp, "../frontoffice/assets/" . $name);
        $imageName = $name;
    }

    $id      = !empty($_POST['id_blog']) ? (int) $_POST['id_blog'] : null;
    $publie  = isset($_POST['est_publie_blog']) ? 1 : 0;
    $cree_par  = 1;

    // Si pas d'erreurs, on sauvegarde
    if (empty($errors)) {
        if ($id) {
            $sql = "UPDATE blogs
                    SET titre_blog = :t,
                        contenu_blog = :c,
                        categorie_blog = :cat,
                        est_publie_blog = :p,
                        date_modification_blog = NOW(),
                        image_blog = :img
                    WHERE id_blog = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':t'         => $titre,
                ':c'         => $contenu,
                ':cat'       => $cat,
                ':p'         => $publie,
                ':id'        => $id,
                ':img'       => $imageName
            ]);
        } else {
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

        header("Location: back.php");
        exit;
    }
}

// ===========================================
// LISTE DES ARTICLES
// ===========================================
$stmt = $pdo->query("SELECT * FROM blogs, categories WHERE categorie_blog = id_categorie ORDER BY date_creation_blog DESC");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$total_articles = count($articles);
$articles_publies = count(array_filter($articles, function($a) { return $a['est_publie_blog'] == 1; }));
$articles_brouillon = $total_articles - $articles_publies;

// ===========================================
// LISTE DES CATEGORIES
// ===========================================
$stmt = $pdo->query("SELECT * FROM categories ORDER BY nom_categorie");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Blogs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7f5;
            display: flex;
            min-height: 100vh;
        }

        /* ============ SIDEBAR ============ */
        .sidebar {
            width: 200px;
            background: white;
            position: fixed;
            height: 100vh;
            padding: 20px 0;
            box-shadow: 2px 0 4px rgba(0,0,0,0.05);
        }

        .sidebar-header {
            padding: 0 20px 30px;
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }

        .sidebar-menu {
            list-style: none;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.2s;
            gap: 12px;
            font-size: 14px;
        }

        .menu-item:hover {
            background: #f8f9fa;
            color: #93c572;
        }

        .menu-item.active {
            background: #e8f4e0;
            color: #93c572;
            border-right: 3px solid #93c572;
            font-weight: 500;
        }

        .menu-item i {
            width: 18px;
            text-align: center;
        }

        /* ============ MAIN CONTENT ============ */
        .main-content {
            margin-left: 200px;
            flex: 1;
            padding: 40px;
            background: #f5f7f5;
        }

        /* ============ HEADER ============ */
        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 32px;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .add-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #93c572;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .add-btn:hover {
            background: #7aa959;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(147, 197, 114, 0.3);
        }

        /* ============ STATS CARDS ============ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .stat-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 600;
            color: #93c572;
        }

        /* ============ FORM SECTION ============ */
        .form-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .form-section h2 {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #495057;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #93c572;
            box-shadow: 0 0 0 3px rgba(147, 197, 114, 0.1);
        }

        select {
            cursor: pointer;
            background: white;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 16px;
        }

        .checkbox-container input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-container label {
            margin: 0;
            cursor: pointer;
            font-weight: normal;
        }

        .button-group {
            margin-top: 24px;
            display: flex;
            gap: 12px;
        }

        button[type="submit"] {
            background: #93c572;
            color: white;
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        button[type="submit"]:hover {
            background: #7aa959;
            transform: translateY(-1px);
        }

        .cancel-link {
            color: #dc3545;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            transition: color 0.2s;
        }

        .cancel-link:hover {
            color: #c82333;
        }

        /* ============ ERROR MESSAGES ============ */
        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .input-error {
            border-color: #dc3545 !important;
        }

        /* ============ TABLE SECTION ============ */
        .table-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #e8f4e0;
            margin-bottom: 20px;
        }

        .table-header h2 {
            font-size: 18px;
            color: #93c572;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        thead th {
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
        }

        tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }

        tbody tr:hover {
            background: #f8fdf5;
        }

        tbody td {
            padding: 16px;
            color: #495057;
            font-size: 14px;
        }

        /* ============ BADGES ============ */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-publié {
            background: #93c572;
            color: white;
        }

        .badge-brouillon {
            background: #ffc107;
            color: #856404;
        }

        /* ============ ACTION BUTTONS ============ */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            margin-right: 6px;
        }

        .btn-modifier {
            background: #93c572;
            color: white;
        }

        .btn-modifier:hover {
            background: #7aa959;
        }

        .btn-supprimer {
            background: #dc3545;
            color: white;
        }

        .btn-supprimer:hover {
            background: #c82333;
        }

        /* ============ EMPTY STATE ============ */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
            color: #93c572;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }

            .sidebar-header,
            .menu-item span {
                display: none;
            }

            .menu-item {
                justify-content: center;
                padding: 12px;
            }

            .main-content {
                margin-left: 60px;
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 12px;
            }

            thead th,
            tbody td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- ============ SIDEBAR ============ -->
    <div class="sidebar">
        <div class="sidebar-header">
            Admin Panel
        </div>
        <nav class="sidebar-menu">
            <a href="backoffice.html" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="menu-item active">
                <i class="fas fa-project-diagram"></i>
                <span>blogs</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>
              <a href="#" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Projets</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
        </nav>
    </div>

    <!-- ============ MAIN CONTENT ============ -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Gestion des Blogs</h1>
        </div>

        <!-- Stats Cards -->
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

        <!-- Form Section - TOUJOURS VISIBLE -->
        <div class="form-section">
            <h2>
                <i class="fas fa-plus-circle"></i> 
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
                        <?php foreach ($categories as $a): ?>
                        <option value="<?= $a["id_categorie"] ?>" 
                                <?= ($a["id_categorie"] == ($_POST['categorie_blog'] ?? $article_edit['categorie_blog'])) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a["nom_categorie"]) ?>
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

                    <?php if ($editing && !empty($article_edit['image_blog'])): ?>
                        <img src="assets/<?= htmlspecialchars($article_edit['image_blog']) ?>" 
                            style="max-width:150px; margin-top:10px; border-radius:6px;">
                    <?php endif; ?>
                </div>

                <div class="button-group">
                    <button type="submit">
                        <i class="fas fa-save"></i> <?= $editing ? 'Mettre à jour' : 'Créer' ?>
                    </button>
                    <?php if ($editing): ?>
                    <a href="back.php" class="cancel-link">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="table-header">
                <h2>Liste des Blogs</h2>
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
                            <th>Date début</th>
                            <th>Disponibilité</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($articles as $a): ?>
                        <tr>
                            <td>
                                <?php if (!empty($a['image_blog'])): ?>
                                    <img src="../frontoffice/assets/<?= htmlspecialchars($a['image_blog']) ?>" 
                                        style="width:70px; border-radius:6px;">
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?= $a['id_blog'] ?></td>
                            <td><?= htmlspecialchars($a['titre_blog']) ?></td>
                            <td><?= htmlspecialchars($a['nom_categorie']) ?></td>
                       <td><?= date('Y-m-d', strtotime($a['date_creation_blog'])) ?></td>                            <td>
                                <span class="badge badge-<?= $a['est_publie_blog'] ? 'publié' : 'brouillon' ?>">
                                    <?= $a['est_publie_blog'] ? 'Publié' : 'Brouillon' ?>
                                </span>
                            </td>
                            <td>
                                <a class="btn btn-modifier" href="back.php?edit=<?= $a['id_blog'] ?>">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a class="btn btn-supprimer"
                                   href="back.php?delete=<?= $a['id_blog'] ?>"
                                   onclick="return confirm('Êtes-vous sûr ?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>