<?php
$accountType = (isset($_GET['type']) && $_GET['type'] === 'association') ? 'association' : 'citoyen';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte - WeConnect</title>

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
                <i class="fas fa-user-plus"></i>
                <h2>Rejoignez WeConnect</h2>
                <p>Créez votre compte et commencez à faire la différence dès aujourd'hui.</p>
            </div>
        </div>

        <div class="auth-form-section">
            <div class="auth-form-container">

                <div class="form-header">
                    <h2>Créer un compte</h2>
                    <p>Rejoignez notre communauté en quelques clics.</p>
                </div>

                <div class="account-type-selector">
                    <label class="account-type-card">
                        <input type="radio" name="account_type" value="citoyen" <?php echo $accountType === 'citoyen' ? 'checked' : ''; ?>>
                        <div class="card-content">
                            <i class="fas fa-user"></i>
                            <h3>Citoyen</h3>
                            <p>Je veux aider</p>
                        </div>
                    </label>
                    <label class="account-type-card">
                        <input type="radio" name="account_type" value="association" <?php echo $accountType === 'association' ? 'checked' : ''; ?>>
                        <div class="card-content">
                            <i class="fas fa-hands-helping"></i>
                            <h3>Association</h3>
                            <p>Je représente une organisation</p>
                        </div>
                    </label>
                </div>

                <div id="message-container">
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="message message-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <form id="registerForm" class="auth-form" method="POST" action="index.php?action=register" enctype="multipart/form-data" novalidate>

                    <input type="hidden" name="user_type" id="user_type" value="<?php echo htmlspecialchars($accountType, ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">
                                <i class="fas fa-user"></i>
                                Prénom
                            </label>
                            <input type="text" id="first_name" name="first_name" placeholder="Votre prénom" autocomplete="given-name">
                            <span class="error-message" id="first-name-error"></span>
                        </div>

                        <div class="form-group">
                            <label for="last_name">
                                <i class="fas fa-user"></i>
                                Nom
                            </label>
                            <input type="text" id="last_name" name="last_name" placeholder="Votre nom" autocomplete="family-name">
                            <span class="error-message" id="last-name-error"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Adresse email
                        </label>
                        <input type="text" id="email" name="email" placeholder="exemple@email.com" autocomplete="email">
                        <span class="error-message" id="email-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="phone">
                            <i class="fas fa-phone"></i>
                            Téléphone (optionnel)
                        </label>
                        <input type="text" id="phone" name="phone" placeholder="+216 XX XXX XXX" autocomplete="tel">
                        <span class="error-message" id="phone-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="profile_picture">
                            <i class="fas fa-camera"></i>
                            Photo de profil (optionnel)
                        </label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                        <span class="error-message" id="profile-picture-error"></span>
                        <small class="form-text">Max 2MB. Formats: JPG, PNG, GIF</small>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Mot de passe
                        </label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="new-password">
                            <button type="button" class="toggle-password" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strength-fill"></div>
                            </div>
                            <span class="strength-text" id="strength-text">Entrez un mot de passe</span>
                        </div>
                        <span class="error-message" id="password-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i>
                            Confirmer le mot de passe
                        </label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" autocomplete="new-password">
                            <button type="button" class="toggle-password" data-target="confirm_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span class="error-message" id="confirm-password-error"></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        <span>Créer mon compte</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <div class="form-footer">
                        <p>Vous avez déjà un compte ? <a href="index.php?action=login">Se connecter</a></p>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

<script>
// Handle account type selection
document.addEventListener('DOMContentLoaded', function() {
    const accountTypeInputs = document.querySelectorAll('input[name="account_type"]');
    const firstNameGroup = document.querySelector('#first_name').closest('.form-group');
    const lastNameGroup = document.querySelector('#last_name').closest('.form-group');
    const firstNameLabel = firstNameGroup.querySelector('label');
    const lastNameInput = document.querySelector('#last_name');
    const userTypeHidden = document.querySelector('#user_type');
    
    function updateFormFields() {
        const selectedType = document.querySelector('input[name="account_type"]:checked').value;
        
        // Update hidden field
        userTypeHidden.value = selectedType;
        
        if (selectedType === 'association') {
            // Change label for first name
            firstNameLabel.innerHTML = '<i class="fas fa-building"></i> Nom de l\'association';
            document.querySelector('#first_name').placeholder = 'Nom de votre association';
            
            // Hide last name field
            lastNameGroup.style.display = 'none';
            
            // Set last name to "Association" automatically
            lastNameInput.value = 'Association';
            lastNameInput.removeAttribute('required');
            
            // Make the form row full width for association
            firstNameGroup.closest('.form-row').style.gridTemplateColumns = '1fr';
            
        } else {
            // Restore original label for citizen
            firstNameLabel.innerHTML = '<i class="fas fa-user"></i> Prénom';
            document.querySelector('#first_name').placeholder = 'Votre prénom';
            
            // Show last name field
            lastNameGroup.style.display = 'block';
            
            // Clear last name
            if (lastNameInput.value === 'Association') {
                lastNameInput.value = '';
            }
            lastNameInput.setAttribute('required', 'required');
            
            // Restore two-column layout
            firstNameGroup.closest('.form-row').style.gridTemplateColumns = '1fr 1fr';
        }
    }
    
    // Add event listeners to radio buttons
    accountTypeInputs.forEach(input => {
        input.addEventListener('change', updateFormFields);
    });
    
    // Initialize on page load
    updateFormFields();
});
</script>

</body>
</html>