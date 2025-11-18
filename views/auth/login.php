<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - WeConnect</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="assets/js/validation.js" defer></script>
</head>
<body>

<header class="auth-header">
    <div class="container">
        <div class="auth-nav">
            <a href="index.php" class="logo-link">
                <h1>WeConnect</h1>
                <span class="tagline">Paix & Inclusion</span>
            </a>
            <a href="index.php" class="back-home">
                <i class="fas fa-arrow-left"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</header>

<div class="auth-container">
    <div class="auth-wrapper">

        <div class="auth-illustration">
            <div class="illustration-content">
                <i class="fas fa-hands-helping"></i>
                <h2>Bienvenue sur WeConnect</h2>
                <p>Connectez-vous pour continuer votre mission de solidarité.</p>
            </div>
        </div>

        <div class="auth-form-section">
            <div class="auth-form-container">

                <div class="form-header">
                    <h2>Connexion</h2>
                    <p>Ravi de vous revoir !</p>
                </div>

                <div id="message-container">
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="message message-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['success'])): ?>
                        <div class="message message-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <form id="loginForm" class="auth-form" method="POST" action="index.php?action=login" novalidate>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Adresse email
                        </label>
                        <input type="text" id="email" name="email" placeholder="exemple@email.com" autocomplete="email">
                        <span class="error-message" id="email-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Mot de passe
                        </label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password">
                            <button type="button" class="toggle-password" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span class="error-message" id="password-error"></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        <span>Se connecter</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <div class="form-footer">
                        <p>Vous n'avez pas de compte ? <a href="index.php?action=register">Créer un compte</a></p>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

</body>
</html>