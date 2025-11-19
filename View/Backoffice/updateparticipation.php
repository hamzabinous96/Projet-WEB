<?php
// Correct paths for your structure
$baseDir = __DIR__ . '/../..'; // Go up two levels from Backoffice to reach tasnim folder

require_once $baseDir . '/config.php';
require_once $baseDir . '/Controller/ParticipationController.php';

// Initialize controller
$controller = new ParticipationController();
$message = '';
$messageType = '';

// Get participation data for editing
$participation = null;
if (isset($_GET['id'])) {
    $participation = $controller->show($_GET['id']);
    if (isset($participation['error'])) {
        $message = $participation['error'];
        $messageType = 'error';
        $participation = null;
    }
}

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $result = $controller->update($_POST['id']);
    
    if (isset($result['success'])) {
        $message = $result['success'];
        $messageType = 'success';
        // Refresh participation data
        $participation = $controller->show($_POST['id']);
    } else {
        $message = $result['error'];
        $messageType = 'error';
    }
}

// Get categories for dropdown
$categories = [
    'Technology & Innovation',
    'Education & Learning',
    'Health & Wellness',
    'Arts & Culture',
    'Business & Entrepreneurship',
    'Environment & Sustainability',
    'Community Development',
    'Sports & Recreation',
    'Science & Research',
    'Social Services',
    'Youth Development',
    'Women Empowerment',
    'Volunteer Programs',
    'Professional Development'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Participation</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
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
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 50px;
            width: 100%;
            max-width: 600px;
            position: relative;
            z-index: 2;
            border: 1px solid var(--pistachio-soft);
            transition: all 0.3s ease;
        }

        .container:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-5px);
        }

        h1 {
            color: var(--pistachio-dark);
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5em;
            font-weight: 700;
            position: relative;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--pistachio), var(--pistachio-light));
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 12px;
            color: var(--gray-dark);
            font-weight: 600;
            font-size: 15px;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--pistachio-soft);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: var(--gray-light);
            color: var(--dark);
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--pistachio);
            background-color: var(--white);
            box-shadow: 0 0 0 4px var(--pistachio-transparent);
            transform: translateY(-2px);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }

        .btn {
            background: linear-gradient(135deg, var(--pistachio), var(--pistachio-dark));
            color: var(--white);
            border: none;
            padding: 18px 40px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .btn-secondary {
            background: transparent;
            border: 2px solid var(--pistachio);
            color: var(--pistachio);
            margin-top: 15px;
        }

        .btn-secondary:hover {
            background: var(--pistachio);
            color: var(--white);
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

        .form-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid var(--pistachio-soft);
        }

        .form-footer a {
            color: var(--pistachio);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .form-footer a:hover {
            color: var(--pistachio-dark);
            transform: translateX(-5px);
        }

        .required {
            color: #E74C3C;
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

        /* Custom select arrow */
        select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2393C572' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 16px;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .container {
                padding: 30px 25px;
                margin: 10px;
            }

            h1 {
                font-size: 2em;
                margin-bottom: 30px;
            }

            .form-group {
                margin-bottom: 25px;
            }

            input[type="text"],
            select,
            textarea {
                padding: 14px 18px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 25px 20px;
            }

            h1 {
                font-size: 1.8em;
            }

            .btn {
                padding: 16px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="container fade-in">
        <h1>Edit Participation</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($participation): ?>
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo $participation['id']; ?>">
                
                <div class="form-group">
                    <label for="titre">Title <span class="required">*</span></label>
                    <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($participation['titre']); ?>" required placeholder="Enter participation title">
                </div>

                <div class="form-group">
                    <label for="auteur">Author <span class="required">*</span></label>
                    <input type="text" id="auteur" name="auteur" value="<?php echo htmlspecialchars($participation['auteur']); ?>" required placeholder="Enter author name">
                </div>

                <div class="form-group">
                    <label for="categorie">Category <span class="required">*</span></label>
                    <select id="categorie" name="categorie" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" 
                                <?php echo ($participation['categorie'] === $category) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn">
                    Update Participation
                </button>
            </form>
        <?php else: ?>
            <div class="message error">
                Participation not found or cannot be edited.
            </div>
        <?php endif; ?>

        <div class="form-footer">
            <a href="index.php">← Back to Participations List</a>
        </div>
    </div>

    <script>
        // Add some interactive features
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                const inputs = form.querySelectorAll('input[required], select[required]');
                
                // Add real-time validation
                inputs.forEach(input => {
                    input.addEventListener('blur', function() {
                        if (!this.value) {
                            this.style.borderColor = '#E74C3C';
                            this.style.boxShadow = '0 0 0 4px rgba(231, 76, 60, 0.1)';
                        } else {
                            this.style.borderColor = 'var(--pistachio)';
                            this.style.boxShadow = '0 0 0 4px var(--pistachio-transparent)';
                        }
                    });
                    
                    input.addEventListener('input', function() {
                        if (this.value) {
                            this.style.borderColor = 'var(--pistachio)';
                            this.style.boxShadow = '0 0 0 4px var(--pistachio-transparent)';
                        }
                    });
                });

                // Add focus effects
                inputs.forEach(input => {
                    input.addEventListener('focus', function() {
                        this.style.transform = 'translateY(-2px)';
                    });
                    
                    input.addEventListener('blur', function() {
                        this.style.transform = 'translateY(0)';
                    });
                });
            }

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
    </script>
</body>
</html>