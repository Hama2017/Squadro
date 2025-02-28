# Squadro - Application Web de Jeu de Stratégie


## 🎲 Présentation du Projet

Squadro est un jeu de stratégie innovant développé dans le cadre d'un projet universitaire de Licence 3 Informatique. Ce projet démontre une approche complète du développement d'une application web multijoueur en utilisant des pratiques modernes de développement PHP.

### 🌟 Fonctionnalités Principales

- Jeu de plateau interactif sur navigateur
- Jeu de stratégie pour deux joueurs
- Gestion persistante de l'état du jeu
- Interface web responsive
- Sessions de jeu sauvegardées en base de données
- Support de jeu multijoueur asynchrone

## 🚀 Environnement Technologique

### Backend
- PHP 8.0+
- PDO pour interactions base de données
- MySQL/PostgreSQL
- Gestion de sessions

### Frontend
- HTML5
- CSS3
- Design responsive
- Interactions par formulaires

### Outils de Développement
- Git pour le contrôle de version
- Claude 3.5 Haiku (Assistant de développement IA)
- PHPUnit pour les tests
- Environnements de développement : VS Code / PhpStorm

## 📦 Architecture du Projet

### Composants Principaux

1. **Classes de Logique de Jeu**
   - `PieceSquadro` : Représente les pièces et cases du plateau
   - `PlateauSquadro` : Gère l'état du plateau de jeu
   - `ActionSquadro` : Implémente les règles et mouvements du jeu
   - `ArrayPieceSquadro` : Gère les collections de pièces

2. **Interface Utilisateur**
   - `PieceSquadroUI` : Génère les représentations HTML des pièces
   - `SquadroUIGenerator` : Crée les pages de l'interface de jeu

3. **Persistance**
   - `PDOSquadro` : Couche d'interaction avec la base de données
   - `JoueurSquadro` : Gestion des entités joueurs
   - `PartieSquadro` : Gestion des sessions de jeu

## 🔧 Étapes de Développement

### Étape 1 : Modélisation des Mécaniques de Jeu
- Création des classes de logique de base
- Implémentation de la validation des mouvements
- Développement des règles de déplacement des pièces
- Création de la structure initiale des classes

### Étape 2 : Développement de l'Interface Web
- Conception de la représentation HTML du plateau
- Création de boutons de pièces interactifs
- Implémentation de composants d'interface responsive
- Développement de la génération de pages selon les états

### Étape 3 : Gestion des Sessions de Jeu
- Implémentation de l'état du jeu via les sessions PHP
- Création d'une machine à états pour le flux de jeu
- Développement de scripts de gestion des actions
- Implémentation de la logique de jeu au tour par tour

### Étape 4 : Multijoueur et Persistance
- Ajout de l'authentification utilisateur
- Implémentation du stockage des jeux en base de données
- Création d'un système de lobby et de matching de parties
- Développement du support de jeu asynchrone

## 🛠 Installation

### Prérequis
- PHP 7.4+
- MySQL/MariaDB
- Composer
- Serveur web (Apache/Nginx)

### Étapes de Configuration

1. Cloner le dépôt
   ```bash
   git clone https://www-apps.univ-lehavre.fr/forge/super_equipe/squadro.git
   cd squadro
   ```

2. Configurer la Base de Données
   ```bash
   mysql -u root -p < SQL/squadro.sql
   ```

3. Configuration de l'Environnement
   ```php
   // Éditer env/db.php
   $_ENV = [
       'sgbd' => 'mysql',
       'host' => 'localhost',
       'database' => 'squadro',
       'user' => 'votre_utilisateur',
       'password' => 'votre_mot_de_passe'
   ];
   ```

4. Installer les Dépendances
   ```bash
   composer install
   ```

5. Lancer l'Application
    - Configurer votre serveur web
    - Naviguer vers `login.php`

## 🧪 Tests

### Exécution des Tests
```bash
php vendor/bin/phpunit tests/
```

### Couverture des Tests
- Tests unitaires des classes principales
- Tests d'intégration de la logique de jeu
- Tests des composants d'interface

## 🤝 Contribution

1. Forker le dépôt
2. Créer une branche de fonctionnalité
3. Commiter les modifications
4. Pousser la branche
5. Ouvrir une Pull Request

### Directives de Contribution
- Suivre les standards de codage PSR-12
- Écrire des tests complets
- Documenter le code avec PHPDoc
- Maintenir un code propre et lisible

## 📝 Notes sur le Développement IA

Ce projet a utilisé Claude 3.5 Haiku pour :
- La génération initiale de code
- Les conseils architecturaux
- La revue et l'optimisation du code
- L'assistance à la documentation

## 👥 Équipe du Projet

**Étudiants :**
- TACKO NDIAYE
- HAMADOU BA

**Encadré par :**
- Yoann Pigné
- Dominique Fournier

## 📄 Licence

[Choisir une licence open-source appropriée, par exemple MIT, GPL]

## 🏆 Remerciements

- Département Informatique de l'Université [Nom]
- Claude IA par Anthropic
- Communauté Open-Source
```

## 🔧 Étapes de Développement

### Étape 1 : Modélisation des Mécaniques de Jeu

#### 📋 Prompt IA Détaillé

```
Bonjour Claude, je souhaite développer un jeu de stratégie appelé Squadro en PHP.
Je vais vous détailler précisément les besoins de modélisation pour les classes métier.

**Contexte du Jeu :**
- Jeu de stratégie pour 2 joueurs
- Plateau de 7x7 cases
- Deux ensembles de 5 pièces (blanches et noires)
- Déplacements différenciés selon la couleur et la direction

**Spécifications des Classes à Développer :**

1. Classe PieceSquadro
- Représente une pièce ou un emplacement sur le plateau
- Doit gérer 4 états de couleur : BLANC, NOIR, VIDE, NEUTRE
- Doit gérer 4 directions : NORD, SUD, EST, OUEST
- Constructeur privé
- Méthodes statiques d'initialisation
- Méthode inverseDirection()
- Méthodes toJson() et fromJson()

Contraintes techniques :
- Utiliser PHP 7.4+
- Typage strict
- Documentation PHPDoc complète
- Gestion des constantes de classe
- Méthodes immuables si possible

Pouvez-vous générer l'implémentation complète en respectant ces principes architecturaux ?
```

### Étape 2 : Développement de l'Interface Web

#### 📋 Prompt IA Détaillé

```
Bonjour Claude, je veux développer l'interface web pour le jeu Squadro.

**Objectifs de l'Interface :**
- Représenter graphiquement un plateau de jeu interactif
- Générer des boutons HTML pour chaque type de pièce
- Gérer différents états de pièces (jouable/non jouable)
- Créer une expérience utilisateur intuitive

**Classes à Développer :**

1. PieceSquadroUI
- Méthodes de génération HTML pour :
    * Cases vides
    * Cases neutres
    * Pièces blanches (directions Est/Ouest)
    * Pièces noires (directions Nord/Sud)
- Gestion des états actifs/inactifs
- Support des coordonnées pour transmission de formulaire

2. SquadroUIGenerator
- Générer pages de jeu :
    * Choix de pièce
    * Confirmation de déplacement
    * Victoire
    * Gestion d'erreurs
- Design responsive
- Utilisation de classes CSS modulaires

Contraintes techniques :
- PHP 7.4+
- HTML5 sémantique
- CSS moderne
- Accessibilité
- Séparation claire des préoccupations

Pouvez-vous proposer une implémentation complète et élégante ?
```

### Étape 3 : Gestion des Sessions de Jeu

#### 📋 Prompt IA Détaillé

```
Bonjour Claude, je souhaite implémenter la logique de gestion de session pour Squadro.

**Architecture Requise :**
- Système de gestion d'état basé sur les sessions PHP
- Machine à états pour le flux de jeu
- Gestion des transitions entre états
- Validation des actions de jeu

**Scripts à Développer :**

1. index.php
- Gestion centralisée des états de jeu
- Affichage dynamique selon l'état courant
- Utilisation extensive des sessions PHP
- Sécurisation des transitions

2. traiteActionSquadro.php
- Traitement des actions de jeu
- Validation des mouvements
- Mise à jour de l'état de session
- Gestion des conditions de victoire
- Gestion des erreurs

**États de Jeu :**
- ChoixPièce
- ConfirmationPiece
- Victoire
- Erreur

**Règles de Transition :**
- ChoisirPièce : Mémorisation et validation
- ConfirmerChoix : Déplacement, changement de joueur
- AnnulerChoix : Retour à l'état précédent
- Rejouer : Réinitialisation complète

Contraintes :
- Sécurité des sessions
- Validation stricte des données
- Gestion des erreurs
- Maintenabilité du code

Pouvez-vous proposer une architecture robuste et élégante ?
```

### Étape 4 : Multijoueur et Persistance

#### 📋 Prompt IA Détaillé

```
Bonjour Claude, je veux implémenter la couche de persistance et le système multijoueur pour Squadro.

**Architecture Système :**
- Base de données pour stocker parties et joueurs
- Authentification des utilisateurs
- Système de lobby
- Parties asynchrones

**Composants à Développer :**

1. Base de Données (MySQL)
- Table JoueurSquadro
    * ID
    * Nom
    * Informations de connexion

- Table PartieSquadro
    * ID Partie
    * Joueurs
    * État de la partie
    * JSON de l'état du jeu
    * Horodatage

2. PDOSquadro (Couche d'Accès aux Données)
- Méthodes CRUD complètes
- Gestion des connexions
- Requêtes paramétrées
- Gestion des erreurs

3. Scripts Additionnels
- login.php : Authentification
- home.php : Lobby et gestion des parties
- Gestion des parties en attente/en cours

**Fonctionnalités Requises :**
- Création de compte
- Connexion
- Liste des parties
- Création/Rejoindre une partie
- Reprise de partie
- Déconnexion

Contraintes :
- Sécurité (mots de passe hashés)
- Requêtes préparées
- Gestion des exceptions
- Performance
- Scalabilité

