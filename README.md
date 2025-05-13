# Flip-Gen - Générateur de comptes en ligne.
Flip-Gen est une plateforme Web permettant aux utilisateurs de générer automatiquement des comptes premium depuis un stock, avec différents niveaux d'accès (membre, VIP, fournisseur, admin). 
Le projet propose un design moderne en dark mode, responsive, et est entièrement configurable via une interface d'administration.

## Fonctionnalités principales

### 🔌 Système de générateurs de comptes

* Ajout/modification/suppression de générateurs depuis le panel admin
* Restock de comptes facilement accessible via le panel admin.
* Attribution dynamique d'un compte disponible lors d'une génération
* Système de logs : Chaque génération est enregistré en base de données.

### ⚡ Interface utilisateur

* Affichage des générateurs disponibles
* Stock affiché en badge dynamique sur chaque carte
* Système de cooldown affichant un bouton avec un timer si génération trop récente.
* Popup publicitaire pour les non-VIP avec lien ShrinkMe.io
* Statistiques en direct (dernières générations, classement etc..)

### 🔧 Interface Admin

* Tableau de bord avec stats dynamiques (nombre d'utilisateurs, VIP, générations, etc.)
* Gestion des utilisateurs (suppression, permissions)
* Gestion des générateurs (CRUD complet)
* Restock des générateurs avec embed discord intégré.
* Configuration globale de la plateforme

### 🚀 Premium/VIP

* Page d'abonnement Premium.
* Expiration automatique du rôle
* Avantages : pas de publicité, cooldown réduit, limites journalières augmentées

### 🌐 Système de permissions (roles)

* `admin` : accès total
* `fournisseur` : accès restreint au restock
* `vip` : accès prioritaire, pas de pubs, limites augmentées
* `membre` : accès standard avec restrictions

### 🌟 Fonctions utilisateur

* Page profil avec dernières générations effectuées
* Affichage du rôle, image de profil, email, statistiques

## Autres caractéristiques

* Dark mode par défaut, responsive mobile/tablette
* Support de la langue française
* Utilisation de Font Awesome, Google Fonts (Nunito Sans)
* Compatible PHP 8.2+
* Fonctionne avec MySQL/MariaDB

## Installation rapide

1. Configurez `config.php` avec vos identifiants PDO, constantes, etc.
2. Importez le fichier `flipgen.sql` dans votre base de données.
3. Créez un compte puis mettez vous la permission 'admin' via la base de données.
4. le reste du site est configurable en ligne dans `tondomaine.fr/admin/configuration`
---

## Contributions

Les contributions sont les bienvenues. N'hésitez pas à ouvrir une *issue* ou une *pull request* pour proposer des améliorations ou corriger des bugs.

---

Créé avec ❤️ par Celwo.

> Pour toute question ou problème, contactez-moi sur Discord > `celwo`.
