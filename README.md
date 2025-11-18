# WeConnect - Application Type Twitter (MVC)

Ceci est une application de type réseau social simplifiée, inspirée de Twitter, implémentée en PHP avec le pattern MVC (Modèle-Vue-Contrôleur).

## 🚀 Démarrage rapide

1.  **Base de données**: Importez le fichier `weconnect_db.sql` dans votre gestionnaire de base de données (ex: phpMyAdmin, Adminer).
    *   Nom de la base de données: `weconnect`
    *   Utilisateur: `root` (à modifier dans `config/database.php` si nécessaire)
    *   Mot de passe: (vide)
2.  **Configuration**: Assurez-vous que les chemins et les informations de connexion à la base de données dans `config/database.php` sont corrects.
3.  **Lancement**: Placez tous les fichiers dans le répertoire racine de votre serveur web (ex: `htdocs` ou `www`). L'application est accessible via `http://localhost/weconnect/`.

## 📁 Structure du projet

```
weconnect/
├── index.php                    # Front controller principal
├── config/
│   └── database.php            # Configuration base de données
├── models/
│   ├── Utilisateur.php         # Modèle Utilisateur
│   ├── Publication.php         # Modèle Publication (posts)
│   ├── Commentaire.php         # Modèle Commentaire
│   └── Like.php                # Modèle Like
├── controllers/
│   ├── UtilisateurController.php
│   ├── PublicationController.php
│   ├── CommentaireController.php
│   └── LikeController.php
├── views/
│   ├── frontoffice/
│   │   ├── header.php
│   │   ├── footer.php
│   │   ├── connexion.php
│   │   ├── inscription.php
│   │   ├── fil_actualite.php        # Page principale (feed)
│   │   └── profil.php
│   └── backoffice/
│       ├── header.php
│       ├── footer.php
│       ├── dashboard.php
│       ├── gestion_utilisateurs.php
│       ├── gestion_publications.php
│       └── statistiques.php
├── assets/
│   ├── css/
│   │   ├── style.css           # Style général (vert pistache)
│   │   └── backoffice.css
│   └── js/
│       └── validation.js       # Validation JavaScript
├── weconnect_db.sql             # Fichier SQL pour la création des tables
└── README.md
```

## 🔑 Fonctionnalités principales

*   **Connexion/Inscription**: Gestion des utilisateurs avec hachage de mot de passe.
*   **Fil d'actualité**: Affichage des publications par ordre chronologique.
*   **Publications**: Création et suppression de publications (limite de 280 caractères).
*   **Interactions**: Système de "J'aime" (Like) et de commentaires.
*   **Validation**: Validation des formulaires côté client en JavaScript.
*   **Design**: Style simple et moderne avec un thème "vert pistache".

## 🛠️ Note technique

Le code fourni est une base solide pour une application MVC simple en PHP. Les contrôleurs `UtilisateurController.php`, `CommentaireController.php`, et `LikeController.php`, ainsi que les vues restantes, doivent être complétés pour que l'application soit entièrement fonctionnelle.

**Ce qui a été inclus dans ce package:**
*   Le Front Controller (`index.php`)
*   La configuration de la base de données (`config/database.php`)
*   Tous les Modèles (`models/*.php`)
*   Le Contrôleur de Publication (`controllers/PublicationController.php`)
*   Le script SQL de création des tables (`weconnect_db.sql`)
*   Le CSS de base (`assets/css/style.css`)
*   Le JavaScript de validation (`assets/js/validation.js`)
*   La vue principale du fil d'actualité (`views/frontoffice/fil_actualite.php`)
*   Ce fichier README.md

**Ce qui est manquant (à compléter par l'utilisateur):**
*   `controllers/UtilisateurController.php`
*   `controllers/CommentaireController.php`
*   `controllers/LikeController.php`
*   Toutes les vues restantes dans `views/frontoffice/` et `views/backoffice/` (sauf `fil_actualite.php`)
*   Le fichier `assets/css/backoffice.css`

Bon développement !
