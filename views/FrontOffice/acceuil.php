<?php include 'layouts/header.php'; ?>

<main class="landing">
    <section class="landing-hero" id="accueil">
        <div class="hero-overlay"></div>
        <div class="container hero-grid">
            <div class="hero-content">
                <p class="eyebrow">Solidarité & Inclusion</p>
                <h1>Ensemble, construisons un monde de paix</h1>
                <p class="hero-subtitle">
                    WeConnect rapproche citoyens, associations et bénévoles pour créer
                    des projets concrets partout en Tunisie.
                </p>
                <div class="hero-buttons">
                    <a href="#how-it-works" class="btn btn-large btn-primary">
                        Découvrir la plateforme
                    </a>
                    <a href="index.php?action=register" class="btn btn-large btn-outline">
                        Rejoindre la communauté
                    </a>
                </div>
                <div class="hero-pills">
                    <a href="#mission">Notre mission</a>
                    <a href="#projects">Projets</a>
                    <a href="#testimonials">Témoignages</a>
                </div>
            </div>

            <div class="hero-stats">
                <div class="stat-card">
                    <span class="stat-label">Citoyens actifs</span>
                    <strong class="stat-number">1 250+</strong>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Associations partenaires</span>
                    <strong class="stat-number">85+</strong>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Projets réalisés</span>
                    <strong class="stat-number">500+</strong>
                </div>
            </div>
        </div>
    </section>

    <section id="mission" class="landing-section mission-section">
        <div class="container mission-grid">
            <div class="mission-text">
                <div class="section-eyebrow">Notre engagement</div>
                <h2>Un pont entre ceux qui ont besoin et ceux qui peuvent aider</h2>
                <p>
                    Dans un contexte social en mutation, WeConnect facilite la rencontre
                    entre les bonnes volontés et les besoins concrets des territoires.
                    La plateforme offre un espace simple pour proposer son aide, trouver
                    des bénévoles et partager les réussites.
                </p>
                <p>
                    Chaque membre bénéficie d’outils intuitifs pour piloter ses
                    initiatives et mesurer son impact, tout en restant aligné sur nos
                    valeurs communes.
                </p>

                <div class="mission-values">
                    <div class="value-item">
                        <i class="fas fa-hand-holding-heart"></i>
                        <div>
                            <h4>Solidarité</h4>
                            <p>Créer des liens d’entraide durables.</p>
                        </div>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-peace"></i>
                        <div>
                            <h4>Paix</h4>
                            <p>Encourager le dialogue et la compréhension.</p>
                        </div>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-users"></i>
                        <div>
                            <h4>Inclusion</h4>
                            <p>Donner une voix à chacun, sans distinction.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mission-visual">
                <div class="visual-pill">+32 projets ouverts</div>
                <div class="visual-card">
                    <h3>Impact 2025</h3>
                    <ul>
                        <li><i class="fas fa-check"></i> Programmes éducatifs</li>
                        <li><i class="fas fa-check"></i> Actions environnementales</li>
                        <li><i class="fas fa-check"></i> Soutien aux familles</li>
                    </ul>
                </div>
                <div class="visual-highlight">
                    <span>94%</span>
                    <p>des membres recommandent WeConnect</p>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="landing-section soft-bg">
        <div class="container">
            <div class="section-header">
                <p class="section-eyebrow">Trois profils, une communauté</p>
                <h2>Comment ça marche ?</h2>
                <p>Choisissez votre rôle et laissez-vous guider.</p>
            </div>

            <div class="roles-grid">
                <article class="role-card">
                    <div class="role-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Citoyens</h3>
                    <p>Proposez votre temps, vos talents et participez à des missions locales.</p>
                    <ul class="role-features">
                        <li><i class="fas fa-check"></i> Trouver un projet</li>
                        <li><i class="fas fa-check"></i> Rejoindre une équipe</li>
                        <li><i class="fas fa-check"></i> Suivre votre impact</li>
                    </ul>
                    <a href="index.php?action=register&type=citoyen" class="btn btn-secondary">
                        Devenir citoyen actif
                    </a>
                </article>

                <article class="role-card featured">
                    <div class="role-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h3>Associations</h3>
                    <p>Publiez vos besoins, recrutez des bénévoles et partagez vos résultats.</p>
                    <ul class="role-features">
                        <li><i class="fas fa-check"></i> Publier des missions</li>
                        <li><i class="fas fa-check"></i> Gérer vos bénévoles</li>
                        <li><i class="fas fa-check"></i> Obtenir de la visibilité</li>
                    </ul>
                    <a href="index.php?action=register&type=association" class="btn btn-primary">
                        Inscrire mon association
                    </a>
                </article>

                <article class="role-card">
                    <div class="role-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Administrateurs</h3>
                    <p>Veillez au bon fonctionnement de la plateforme et mesurez la performance.</p>
                    <ul class="role-features">
                        <li><i class="fas fa-check"></i> Valider les actions</li>
                        <li><i class="fas fa-check"></i> Gérer les utilisateurs</li>
                        <li><i class="fas fa-check"></i> Consolider les statistiques</li>
                    </ul>
                    <a href="index.php?action=login" class="btn btn-secondary">
                        Accéder à l’espace admin
                    </a>
                </article>
            </div>
        </div>
    </section>

    <section id="projects" class="landing-section">
        <div class="container">
            <div class="section-header">
                <p class="section-eyebrow">Actions en lumière</p>
                <h2>Projets en cours</h2>
                <p>Des initiatives concrètes qui changent des vies.</p>
            </div>

            <div class="projects-grid">
                <article class="project-card">
                    <div class="project-image education">
                        <span class="project-badge">Éducation</span>
                    </div>
                    <div class="project-content">
                        <h3>Soutien scolaire solidaire</h3>
                        <p>Aide aux devoirs et mentorat pour des enfants en difficulté.</p>
                        <div class="project-meta">
                            <span><i class="fas fa-users"></i> 25 bénévoles</span>
                            <span><i class="fas fa-map-marker-alt"></i> Tunis</span>
                        </div>
                        <div class="project-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:70%"></div>
                            </div>
                            <span class="progress-text">70% complété</span>
                        </div>
                        <a href="index.php?action=login" class="btn btn-small btn-primary">Participer</a>
                    </div>
                </article>

                <article class="project-card">
                    <div class="project-image environment">
                        <span class="project-badge">Environnement</span>
                    </div>
                    <div class="project-content">
                        <h3>Nettoyage des plages</h3>
                        <p>Actions collectives pour préserver notre littoral.</p>
                        <div class="project-meta">
                            <span><i class="fas fa-users"></i> 40 bénévoles</span>
                            <span><i class="fas fa-map-marker-alt"></i> La Marsa</span>
                        </div>
                        <div class="project-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:45%"></div>
                            </div>
                            <span class="progress-text">45% complété</span>
                        </div>
                        <a href="index.php?action=login" class="btn btn-small btn-primary">Participer</a>
                    </div>
                </article>

                <article class="project-card">
                    <div class="project-image health">
                        <span class="project-badge">Santé</span>
                    </div>
                    <div class="project-content">
                        <h3>Accompagnement des seniors</h3>
                        <p>Visites à domicile et activités pour rompre l’isolement.</p>
                        <div class="project-meta">
                            <span><i class="fas fa-users"></i> 18 bénévoles</span>
                            <span><i class="fas fa-map-marker-alt"></i> Ariana</span>
                        </div>
                        <div class="project-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:90%"></div>
                            </div>
                            <span class="progress-text">90% complété</span>
                        </div>
                        <a href="index.php?action=login" class="btn btn-small btn-primary">Participer</a>
                    </div>
                </article>
            </div>

            <div class="projects-cta">
                <a href="index.php?action=register" class="btn btn-outline">Voir tous les projets</a>
            </div>
        </div>
    </section>

    <section class="landing-section soft-bg causes-section">
        <div class="container">
            <div class="section-header">
                <p class="section-eyebrow">Nos combats</p>
                <h2>Des valeurs au cœur de notre action</h2>
            </div>

            <div class="causes-grid">
                <div class="cause-item">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>Éducation pour tous</h3>
                    <p>Offrir des opportunités d’apprentissage à chaque enfant.</p>
                </div>
                <div class="cause-item">
                    <i class="fas fa-leaf"></i>
                    <h3>Protection de l’environnement</h3>
                    <p>Préserver nos ressources et sensibiliser les citoyens.</p>
                </div>
                <div class="cause-item">
                    <i class="fas fa-balance-scale"></i>
                    <h3>Justice sociale</h3>
                    <p>Combattre les inégalités et défendre les droits fondamentaux.</p>
                </div>
                <div class="cause-item">
                    <i class="fas fa-handshake"></i>
                    <h3>Dialogue interculturel</h3>
                    <p>Encourager la rencontre et le respect mutuel.</p>
                </div>
                <div class="cause-item">
                    <i class="fas fa-home"></i>
                    <h3>Lutte contre la pauvreté</h3>
                    <p>Accompagner les familles vers plus d’autonomie.</p>
                </div>
                <div class="cause-item">
                    <i class="fas fa-medkit"></i>
                    <h3>Accès aux soins</h3>
                    <p>Soutenir les initiatives de santé de proximité.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="landing-section">
        <div class="container">
            <div class="section-header">
                <p class="section-eyebrow">Ils témoignent</p>
                <h2>Les voix de notre communauté</h2>
            </div>

            <div class="testimonials-grid">
                <article class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"WeConnect m’a permis de trouver la mission qui me correspond et de rencontrer une équipe formidable."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">AB</div>
                        <div>
                            <h4>Amina B.</h4>
                            <span>Citoyenne bénévole</span>
                        </div>
                    </div>
                </article>

                <article class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"Notre association a doublé son nombre de bénévoles en quelques semaines grâce à la plateforme."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">MK</div>
                        <div>
                            <h4>Mohamed K.</h4>
                            <span>Association Espoir</span>
                        </div>
                    </div>
                </article>

                <article class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"Une interface claire, un accompagnement réactif et un réseau très qualifié : WeConnect coche toutes les cases."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">SM</div>
                        <div>
                            <h4>Sarah M.</h4>
                            <span>Coordinatrice ONG</span>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <p class="section-eyebrow">Passez à l’action</p>
                <h2>Prêt à faire la différence ?</h2>
                <p>Créez votre compte en quelques minutes ou connectez-vous pour retrouver vos projets.</p>
                <div class="cta-buttons">
                    <a href="index.php?action=register" class="btn btn-large btn-primary">Créer mon compte</a>
                    <a href="index.php?action=login" class="btn btn-large btn-outline">Se connecter</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'layouts/footer.php'; ?>

