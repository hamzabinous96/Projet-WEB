<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeConnect - Connecting Communities</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="assets/js/validation.js" defer></script>
</head>
<body>
<nav class="navbar">
    <div class="container navbar-content">
        <a href="index.php" class="nav-brand">WeConnect</a>

        <div class="nav-actions">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="nav-user">Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                <a href="index.php?action=profile" class="nav-link">Profile</a>
                <a href="index.php?action=logout" class="btn btn-secondary btn-sm">Logout</a>
            <?php else: ?>
                <a href="index.php?action=login" class="nav-link">Login</a>
                <a href="index.php?action=register" class="btn btn-primary btn-sm">Create Account</a>
            <?php endif; ?>
        </div>
    </div>
</nav>