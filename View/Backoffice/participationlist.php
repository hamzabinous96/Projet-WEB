<?php
// Correct paths for your structure
$baseDir = __DIR__ . '/../..'; // Go up two levels from Backoffice to reach tasnim folder

require_once $baseDir . '/config.php';
require_once $baseDir . '/Controller/ParticipationController.php';

// Initialize controller
$controller = new ParticipationController();

// Get all participations
$participations = $controller->index();
if (isset($participations['error'])) {
    $participations = [];
}

// Handle delete action
if (isset($_POST['delete_id'])) {
    $deleteResult = $controller->delete($_POST['delete_id']);
    if (isset($deleteResult['success'])) {
        header('Location: index.php?message=' . urlencode($deleteResult['success']) . '&type=success');
        exit();
    } else {
        header('Location: index.php?message=' . urlencode($deleteResult['error']) . '&type=error');
        exit();
    }
}

// Check for messages from redirects
$message = '';
$messageType = '';
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $messageType = $_GET['type'] ?? 'success';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participations List</title>
    <style>
        /* -----------------------------------
           PALETTE & VARIABLES MODERNE VERT PISTACHE
        ----------------------------------- */
        :root {
            --pistachio: #93C572;
            --pistachio-light: #B8D8A6;
            --pistachio-dark: #7AA959;
            --pistachio-soft: #E8F4E0;
            --pistachio-transparent: rgba(147, 197, 114, 0.1);
            --dark: #1A1A1A;
            --gray-dark: #333333;
            --gray: #666666;
            --gray-light: #F8F9FA;
            --white: #FFFFFF;
            --shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 20px 60px rgba(147, 197, 114, 0.15);
        }

        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--pistachio-soft) 0%, transparent 50%, var(--white) 100%);
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: var(--pistachio-light);
            opacity: 0.1;
            border-radius: 50%;
            animation: pulse 8s ease-in-out infinite;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .header {
            text-align: center;
            margin-bottom: 50px;
            padding-top: 30px;
        }

        h1 {
            color: var(--pistachio-dark);
            font-size: 3em;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--pistachio), var(--pistachio-light));
            border-radius: 2px;
        }

        .subtitle {
            color: var(--gray);
            font-size: 1.2em;
            margin-bottom: 30px;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .btn {
            background: linear-gradient(135deg, var(--pistachio), var(--pistachio-dark));
            color: var(--white);
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .btn-secondary {
            background: transparent;
            border: 2px solid var(--pistachio);
            color: var(--pistachio);
        }

        .btn-secondary:hover {
            background: var(--pistachio);
            color: var(--white);
        }

        .btn-danger {
            background: transparent;
            border: 2px solid #E74C3C;
            color: #E74C3C;
            padding: 8px 16px;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-danger:hover {
            background: #E74C3C;
            color: var(--white);
        }

        .btn-edit {
            background: transparent;
            border: 2px solid var(--pistachio);
            color: var(--pistachio);
            padding: 8px 16px;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit:hover {
            background: var(--pistachio);
            color: var(--white);
        }

        .search-filter {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .search-box, .filter-select {
            padding: 12px 20px;
            border: 2px solid var(--pistachio-soft);
            border-radius: 12px;
            font-size: 16px;
            background-color: var(--white);
            transition: all 0.3s ease;
        }

        .search-box:focus, .filter-select:focus {
            outline: none;
            border-color: var(--pistachio);
            box-shadow: 0 0 0 4px var(--pistachio-transparent);
        }

        .message {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            border: 2px solid transparent;
            animation: fadeInUp 0.5s ease;
        }

        .message.success {
            background-color: var(--pistachio-soft);
            color: var(--pistachio-dark);
            border-color: var(--pistachio-light);
        }

        .message.error {
            background-color: #FEE;
            color: #C33;
            border-color: #FCC;
        }

        .participations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .participation-card {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow);
            border: 1px solid var(--pistachio-soft);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .participation-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--pistachio), var(--pistachio-light));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .participation-card:hover::before {
            transform: scaleX(1);
        }

        .participation-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .card-title {
            color: var(--pistachio-dark);
            font-size: 1.5em;
            font-weight: 600;
            margin-bottom: 10px;
            flex: 1;
        }

        .card-category {
            background: var(--pistachio);
            color: var(--white);
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 600;
            white-space: nowrap;
        }

        .card-author {
            color: var(--gray);
            font-size: 1em;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-date {
            color: var(--gray);
            font-size: 0.9em;
            margin-bottom: 20px;
        }

        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-end;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .empty-state h3 {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: var(--pistachio-dark);
        }

        /* Delete confirmation modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-hover);
            max-width: 500px;
            width: 90%;
            text-align: center;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn-cancel {
            background: transparent;
            border: 2px solid var(--gray);
            color: var(--gray);
            padding: 12px 25px;
        }

        .btn-cancel:hover {
            background: var(--gray);
            color: var(--white);
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.7;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.9;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeInUp 0.8s ease forwards;
        }

        @media (max-width: 768px) {
            .participations-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .search-filter {
                width: 100%;
            }

            h1 {
                font-size: 2.2em;
            }

            .participation-card {
                padding: 20px;
            }

            .modal-buttons {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            h1 {
                font-size: 1.8em;
            }

            .btn {
                padding: 12px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header fade-in">
            <h1>Participations</h1>
            <p class="subtitle">Manage and view all participations</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="actions fade-in">
            <a href="addparticipation.php" class="btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Add New Participation
            </a>
            
            <div class="search-filter">
                <input type="text" class="search-box" placeholder="Search participations..." id="searchInput">
                <select class="filter-select" id="categoryFilter">
                    <option value="">All Categories</option>
                    <?php
                    $allCategories = [];
                    foreach ($participations as $participation) {
                        if (!empty($participation['categorie'])) {
                            $allCategories[] = $participation['categorie'];
                        }
                    }
                    $allCategories = array_unique($allCategories);
                    foreach ($allCategories as $category): 
                    ?>
                        <option value="<?php echo htmlspecialchars($category); ?>">
                            <?php echo htmlspecialchars($category); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="participations-grid">
            <?php if (empty($participations)): ?>
                <div class="empty-state fade-in">
                    <h3>No Participations Found</h3>
                    <p>Get started by creating your first participation!</p>
                    <a href="addparticipation.php" class="btn" style="margin-top: 20px;">
                        Create First Participation
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($participations as $participation): ?>
                    <div class="participation-card fade-in" data-category="<?php echo htmlspecialchars($participation['categorie']); ?>">
                        <div class="card-header">
                            <h3 class="card-title"><?php echo htmlspecialchars($participation['titre']); ?></h3>
                            <span class="card-category"><?php echo htmlspecialchars($participation['categorie']); ?></span>
                        </div>
                        
                        <div class="card-author">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <?php echo htmlspecialchars($participation['auteur']); ?>
                        </div>
                        
                        <div class="card-date">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            Created: <?php echo date('M j, Y', strtotime($participation['date_creation'])); ?>
                        </div>
                        
                        <div class="card-actions">
                            <a href="updateparticipation.php?id=<?php echo $participation['id']; ?>" class="btn-edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Edit
                            </a>
                            <button class="btn-danger" onclick="showDeleteModal(<?php echo $participation['id']; ?>, '<?php echo htmlspecialchars($participation['titre']); ?>')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <h3 style="color: var(--pistachio-dark); margin-bottom: 15px;">Confirm Deletion</h3>
            <p style="color: var(--gray-dark); margin-bottom: 10px;">Are you sure you want to delete the participation:</p>
            <p style="color: var(--dark); font-weight: 600; margin-bottom: 20px;" id="participationTitle"></p>
            <p style="color: #E74C3C; font-size: 0.9em;">This action cannot be undone.</p>
            
            <form method="POST" action="" id="deleteForm">
                <input type="hidden" name="delete_id" id="deleteId">
                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" onclick="hideDeleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const participationCards = document.querySelectorAll('.participation-card');

            function filterParticipations() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedCategory = categoryFilter.value;

                participationCards.forEach(card => {
                    const title = card.querySelector('.card-title').textContent.toLowerCase();
                    const author = card.querySelector('.card-author').textContent.toLowerCase();
                    const category = card.getAttribute('data-category');

                    const matchesSearch = title.includes(searchTerm) || author.includes(searchTerm);
                    const matchesCategory = !selectedCategory || category === selectedCategory;

                    if (matchesSearch && matchesCategory) {
                        card.style.display = 'block';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 50);
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });
            }

            searchInput.addEventListener('input', filterParticipations);
            categoryFilter.addEventListener('change', filterParticipations);

            // Clear success message after 5 seconds
            const successMessage = document.querySelector('.message.success');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.opacity = '0';
                    successMessage.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => successMessage.remove(), 500);
                }, 5000);
            }
        });

        // Delete modal functions
        function showDeleteModal(id, title) {
            document.getElementById('deleteId').value = id;
            document.getElementById('participationTitle').textContent = title;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function hideDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideDeleteModal();
            }
        });
    </script>
</body>
</html>