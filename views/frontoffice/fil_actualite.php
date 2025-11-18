<?php include 'views/frontoffice/header.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            
            <!-- Formulaire de nouvelle publication -->
            <div class="formulaire-publication">
                <h4>Quoi de neuf ?</h4>
                <form action="index.php?action=creerPublication" method="POST" onsubmit="return validerPublication()">
                    <textarea name="contenu" id="contenu_publication" placeholder="Exprimez-vous..." onkeyup="compterCaracteres()"></textarea>
                    <div class="formulaire-actions">
                        <span id="compteur_caracteres">280 caractères restants</span>
                        <button type="submit" class="btn-publier">Publier</button>
                    </div>
                </form>
            </div>
            
            <!-- Fil d'actualité -->
            <?php
            // Affichage des messages de succès/erreur
            if (isset($_SESSION['succes'])) {
                echo '<div class="alert alert-success">' . $_SESSION['succes'] . '</div>';
                unset($_SESSION['succes']);
            }
            if (isset($_SESSION['erreur'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['erreur'] . '</div>';
                unset($_SESSION['erreur']);
            }
            
            if ($stmt->rowCount() > 0) {
                while ($publication = $stmt->fetch()) {
                    // Vérifier si l'utilisateur connecté a liké cette publication
                    $modeleLike = new Like();
                    $modeleLike->idPublication = $publication['id'];
                    $modeleLike->idUtilisateur = $_SESSION['id_utilisateur'];
                    $aLike = $modeleLike->aLike();
                    
                    // Récupérer les commentaires
                    $modeleCommentaire = new Commentaire();
                    $modeleCommentaire->idPublication = $publication['id'];
                    $stmtCommentaires = $modeleCommentaire->lireParPublication();
                    $commentaires = $stmtCommentaires->fetchAll();
                    
                    ?>
                    <div class="carte-publication">
                        <div class="entete-publication">
                            <div class="avatar-utilisateur">
                                <?php echo strtoupper(substr($publication['nom_utilisateur'], 0, 1)); ?>
                            </div>
                            <div class="info-utilisateur">
                                <h4><?php echo htmlspecialchars($publication['nom_utilisateur']); ?></h4>
                                <span><?php echo date('d/m/Y H:i', strtotime($publication['date_creation'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="contenu-publication">
                            <?php echo nl2br(htmlspecialchars($publication['contenu'])); ?>
                        </div>
                        
                        <div class="actions-publication">
                            <!-- Bouton Like -->
                            <a href="index.php?action=toggleLike&id_publication=<?php echo $publication['id']; ?>" 
                               class="action-btn <?php echo $aLike ? 'active' : ''; ?>">
                                <i class="fas fa-heart"></i> 
                                J'aime (<?php echo $publication['total_likes']; ?>)
                            </a>
                            
                            <!-- Bouton Commentaire -->
                            <button class="action-btn" onclick="basculerCommentaires(<?php echo $publication['id']; ?>)">
                                <i class="fas fa-comment"></i> 
                                Commenter (<?php echo $publication['total_commentaires']; ?>)
                            </button>
                            
                            <!-- Bouton Supprimer (si c'est sa publication) -->
                            <?php if ($publication['id_utilisateur'] == $_SESSION['id_utilisateur']): ?>
                                <a href="index.php?action=supprimerPublication&id=<?php echo $publication['id']; ?>" 
                                   class="action-btn" 
                                   onclick="return confirmerSuppression('publication')">
                                    <i class="fas fa-trash"></i> 
                                    Supprimer
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Zone de commentaires -->
                        <div class="zone-commentaires" id="commentaires_<?php echo $publication['id']; ?>" style="display: none;">
                            
                            <!-- Formulaire d'ajout de commentaire -->
                            <form action="index.php?action=ajouterCommentaire" method="POST" class="formulaire-commentaire" onsubmit="return validerCommentaire()">
                                <input type="hidden" name="id_publication" value="<?php echo $publication['id']; ?>">
                                <input type="text" name="contenu" id="contenu_commentaire" placeholder="Écrire un commentaire..." required>
                                <button type="submit">Envoyer</button>
                            </form>
                            
                            <!-- Liste des commentaires -->
                            <?php if (!empty($commentaires)): ?>
                                <?php foreach ($commentaires as $commentaire): ?>
                                    <div class="commentaire-item">
                                        <div class="commentaire-avatar">
                                            <?php echo strtoupper(substr($commentaire['nom_utilisateur'], 0, 1)); ?>
                                        </div>
                                        <div class="commentaire-contenu">
                                            <div class="commentaire-auteur">
                                                <?php echo htmlspecialchars($commentaire['nom_utilisateur']); ?>
                                                <span class="commentaire-date"> - <?php echo date('d/m/Y H:i', strtotime($commentaire['date_creation'])); ?></span>
                                            </div>
                                            <div class="commentaire-texte">
                                                <?php echo nl2br(htmlspecialchars($commentaire['contenu'])); ?>
                                            </div>
                                            <!-- Bouton Supprimer Commentaire (si c'est son commentaire ou sa publication) -->
                                            <?php if ($commentaire['id_utilisateur'] == $_SESSION['id_utilisateur'] || $publication['id_utilisateur'] == $_SESSION['id_utilisateur']): ?>
                                                <a href="index.php?action=supprimerCommentaire&id=<?php echo $commentaire['id']; ?>&id_publication=<?php echo $publication['id']; ?>" 
                                                   class="text-danger" 
                                                   style="font-size: 0.8rem;"
                                                   onclick="return confirmerSuppression('commentaire')">
                                                    Supprimer
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center text-muted">Aucun commentaire pour l'instant.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="text-center">Aucune publication pour l\'instant. Soyez le premier à publier !</p>';
            }
            ?>
            
        </div>
    </div>
</div>

<?php include 'views/frontoffice/footer.php'; ?>
