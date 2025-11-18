<?php include 'views/frontoffice/header.php'; ?>

<div class="profile-container" style="max-width: 600px; margin: 50px auto; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);">
    <h2 style="text-align: center; margin-bottom: 30px; color: var(--pistachio-dark);">Mon Profil</h2>
    
    <div style="text-align: center; margin-bottom: 30px;">
        <div class="avatar-utilisateur" style="width: 100px; height: 100px; font-size: 2rem; margin: 0 auto 15px;">
            <?php echo strtoupper(substr($utilisateur['nomUtilisateur'], 0, 1)); ?>
        </div>
        <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($utilisateur['nomUtilisateur']); ?></h3>
        <p style="color: #666;">Membre depuis le <?php echo date('d/m/Y', strtotime($utilisateur['dateCreation'])); ?></p>
    </div>
    
    <form action="index.php?action=modifierProfil" method="POST">
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="nom_utilisateur" style="display: block; margin-bottom: 5px; font-weight: 600;">Nom d'utilisateur :</label>
            <input type="text" id="nom_utilisateur" name="nom_utilisateur" value="<?php echo htmlspecialchars($utilisateur['nomUtilisateur']); ?>" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="email" style="display: block; margin-bottom: 5px; font-weight: 600;">Email :</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($utilisateur['email']); ?>" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
        </div>
        
        <div class="form-group" style="margin-bottom: 30px;">
            <label for="biographie" style="display: block; margin-bottom: 5px; font-weight: 600;">Biographie :</label>
            <textarea id="biographie" name="biographie" rows="4" 
                      style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; resize: vertical;"><?php echo htmlspecialchars($utilisateur['biographie']); ?></textarea>
        </div>
        
        <button type="submit" class="btn-publier" style="width: 100%; padding: 12px; font-size: 1.1rem;">Enregistrer les modifications</button>
    </form>
</div>

<?php include 'views/frontoffice/footer.php'; ?>
