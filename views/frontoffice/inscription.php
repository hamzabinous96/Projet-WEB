<?php include 'views/frontoffice/header.php'; ?>

<div class="form-container" style="max-width: 400px; margin: 50px auto; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);">
    <h2 style="text-align: center; margin-bottom: 30px; color: var(--pistachio-dark);">Inscription</h2>
    
    <form action="index.php?action=traiterInscription" method="POST" onsubmit="return validerFormulaireInscription()">
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="nom_utilisateur" style="display: block; margin-bottom: 5px; font-weight: 600;">Nom d'utilisateur :</label>
            <input type="text" id="nom_utilisateur" name="nom_utilisateur" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="email" style="display: block; margin-bottom: 5px; font-weight: 600;">Email :</label>
            <input type="email" id="email" name="email" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="mot_de_passe" style="display: block; margin-bottom: 5px; font-weight: 600;">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
        </div>
        
        <div class="form-group" style="margin-bottom: 30px;">
            <label for="confirmation_mdp" style="display: block; margin-bottom: 5px; font-weight: 600;">Confirmer mot de passe :</label>
            <input type="password" id="confirmation_mdp" name="confirmation_mdp" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
        </div>
        
        <button type="submit" class="btn-publier" style="width: 100%; padding: 12px; font-size: 1.1rem;">S'inscrire</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        Déjà un compte ? <a href="index.php?action=connexion" style="color: var(--pistachio-dark); text-decoration: none; font-weight: 600;">Connectez-vous ici</a>
    </p>
</div>

<?php include 'views/frontoffice/footer.php'; ?>
