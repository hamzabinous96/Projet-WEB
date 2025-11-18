/**
 * Validation des formulaires - Pas de HTML5
 * Toutes les validations en JavaScript pur
 */

/**
 * Valider le formulaire de connexion
 * @returns {boolean} Formulaire valide ou non
 */
function validerFormulaireConnexion() {
    var nomUtilisateur = document.getElementById('nom_utilisateur').value;
    var motDePasse = document.getElementById('mot_de_passe').value;
    var erreurs = [];
    
    // Validation du nom d'utilisateur
    if (nomUtilisateur === '') {
        erreurs.push('Le nom d\'utilisateur est requis');
    } else if (nomUtilisateur.length < 3) {
        erreurs.push('Le nom d\'utilisateur doit contenir au moins 3 caractères');
    }
    
    // Validation du mot de passe
    if (motDePasse === '') {
        erreurs.push('Le mot de passe est requis');
    } else if (motDePasse.length < 6) {
        erreurs.push('Le mot de passe doit contenir au moins 6 caractères');
    }
    
    // Affichage des erreurs
    if (erreurs.length > 0) {
        alert('Erreurs de validation :\n\n' + erreurs.join('\n'));
        return false;
    }
    return true;
}

/**
 * Valider le formulaire d'inscription
 * @returns {boolean} Formulaire valide ou non
 */
function validerFormulaireInscription() {
    var nomUtilisateur = document.getElementById('nom_utilisateur').value;
    var email = document.getElementById('email').value;
    var motDePasse = document.getElementById('mot_de_passe').value;
    var confirmationMdp = document.getElementById('confirmation_mdp').value;
    var erreurs = [];
    
    // Validation du nom d'utilisateur
    if (nomUtilisateur === '') {
        erreurs.push('Le nom d\'utilisateur est requis');
    } else if (nomUtilisateur.length < 3) {
        erreurs.push('Le nom d\'utilisateur doit contenir au moins 3 caractères');
    } else if (nomUtilisateur.length > 50) {
        erreurs.push('Le nom d\'utilisateur ne doit pas dépasser 50 caractères');
    } else if (!/^[a-zA-Z0-9_]+$/.test(nomUtilisateur)) {
        erreurs.push('Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et underscores');
    }
    
    // Validation de l'email
    if (email === '') {
        erreurs.push('L\'email est requis');
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        erreurs.push('L\'email n\'est pas valide');
    }
    
    // Validation du mot de passe
    if (motDePasse === '') {
        erreurs.push('Le mot de passe est requis');
    } else if (motDePasse.length < 6) {
        erreurs.push('Le mot de passe doit contenir au moins 6 caractères');
    } else if (!/[A-Z]/.test(motDePasse)) {
        erreurs.push('Le mot de passe doit contenir au moins une majuscule');
    } else if (!/[0-9]/.test(motDePasse)) {
        erreurs.push('Le mot de passe doit contenir au moins un chiffre');
    }
    
    // Validation de la confirmation
    if (motDePasse !== confirmationMdp) {
        erreurs.push('Les mots de passe ne correspondent pas');
    }
    
    // Affichage des erreurs
    if (erreurs.length > 0) {
        alert('Erreurs de validation :\n\n' + erreurs.join('\n'));
        return false;
    }
    return true;
}

/**
 * Valider le contenu d'une publication
 * @returns {boolean} Contenu valide ou non
 */
function validerPublication() {
    var contenu = document.getElementById('contenu_publication').value;
    
    // Vérifier que le contenu n'est pas vide
    if (contenu === '' || contenu.trim().length === 0) {
        alert('Le contenu de la publication ne peut pas être vide');
        return false;
    }
    
    // Vérifier la longueur maximale (280 caractères comme Twitter)
    if (contenu.length > 280) {
        alert('La publication ne peut pas dépasser 280 caractères\nCaractères actuels: ' + contenu.length);
        return false;
    }
    
    return true;
}

/**
 * Valider un commentaire
 * @returns {boolean} Commentaire valide ou non
 */
function validerCommentaire() {
    var contenu = document.getElementById('contenu_commentaire').value;
    
    // Vérifier que le commentaire n'est pas vide
    if (contenu === '' || contenu.trim().length === 0) {
        alert('Le commentaire ne peut pas être vide');
        return false;
    }
    
    // Vérifier la longueur maximale
    if (contenu.length > 200) {
        alert('Le commentaire ne peut pas dépasser 200 caractères');
        return false;
    }
    
    return true;
}

/**
 * Confirmation de suppression
 * @param {string} typeElement Type de l'élément (publication, commentaire, etc.)
 * @returns {boolean} Confirmation ou annulation
 */
function confirmerSuppression(typeElement) {
    return confirm('Êtes-vous sûr de vouloir supprimer cette ' + typeElement + ' ?');
}

/**
 * Compter les caractères restants pour une publication
 */
function compterCaracteres() {
    var contenu = document.getElementById('contenu_publication').value;
    var compteur = document.getElementById('compteur_caracteres');
    var caracteresRestants = 280 - contenu.length;
    
    if (compteur) {
        compteur.textContent = caracteresRestants + ' caractères restants';
        
        // Changer la couleur si approche de la limite
        if (caracteresRestants < 20) {
            compteur.style.color = '#ff6b6b';
        } else if (caracteresRestants < 50) {
            compteur.style.color = '#ffa500';
        } else {
            compteur.style.color = '#93C572';
        }
    }
}

/**
 * Basculer l'affichage des commentaires
 * @param {number} idPublication ID de la publication
 */
function basculerCommentaires(idPublication) {
    var zoneCommentaires = document.getElementById('commentaires_' + idPublication);
    
    if (zoneCommentaires) {
        if (zoneCommentaires.style.display === 'none' || zoneCommentaires.style.display === '') {
            zoneCommentaires.style.display = 'block';
        } else {
            zoneCommentaires.style.display = 'none';
        }
    }
}
