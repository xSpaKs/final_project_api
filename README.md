# Planetary API - Backend (Laravel)

## Description
Planetary API est l'API backend de l'application Planetary, développée avec Laravel. Elle fournit une API RESTful pour la gestion de l'authentification des utilisateurs, des paiements par Stripe, l'envoi d'emails de contact et plus encore.

## Fonctionnalités

- Gestion des utilisateurs : Création, modification et suppression des comptes utilisateurs.
- Authentification : Connexion, déconnexion et inscription des utilisateurs avec Sanctum pour une authentification par token.
- Intégration Stripe : Gestion des paiements, création de clients et gestion des abonnements via Stripe.
- Gestion des emails : Envoi d'emails de contact à l'administrateur.
- Actualités : Récupération et affichage des articles de news.
- Paiements : Historique des paiements des utilisateurs.

## Endpoints

### Gestion des utilisateurs

- POST /user - Récupérer les informations de l'utilisateur authentifié (nécessite une authentification)
- POST /modify-user - Modifier les informations de l'utilisateur (nécessite une authentification)
- POST /delete-account - Supprimer le compte utilisateur (nécessite une authentification)


### Authentification

- POST /register - Inscrire un nouvel utilisateur
- POST /login - Connexion d'un utilisateur
- POST /logout - Déconnexion d'un utilisateur (nécessite une authentification)

### Email de contact

- POST /mail-contact - Envoyer un email de contact à l'administrateur

### Intégration Stripe

- POST /stripe/checkout - Créer une session de paiement Stripe (nécessite une authentification)
- POST /stripe/customer - Créer un client Stripe (nécessite une authentification)
- POST /stripe/webhook - Webhook Stripe pour traiter les événements de paiement
- GET /stripe/subscriptions - Récupérer toutes les souscriptions
- GET /stripe/subscriptions/{id} - Récupérer une souscription spécifique par ID

### Gestion des actualités

- GET /news - Récupérer tous les articles de news
- GET /news/{id} - Récupérer un article de news spécifique par ID

### Paiements

- POST /payments-from-user - Récupérer l'historique des paiements pour l'utilisateur authentifié (nécessite une authentification)

## Installation

### Prérequis
PHP 8.x
Composer
Laravel 9.x ou supérieur

### Cloner le dépôt :

- git clone https://github.com/xSpaKs/final_project_api

### Installer les dépendances :

Allez dans le répertoire du projet et installez les dépendances via Composer :

- cd final_project_api
- composer install
- Configurer les variables d'environnement dans le .env avec vos informations

### Générer la clé de l'application :

- php artisan key:generate

### Exécuter les migrations :

- php artisan migrate

### Lancer le serveur API :

- php artisan serve
- L'API sera disponible à l'adresse http://localhost:8000.

## Authentification
Cette API utilise Sanctum pour l'authentification. Après la connexion, les utilisateurs recevront un token qui doit être passé dans l'en-tête Authorization sous forme de token Bearer pour les requêtes nécessitant une authentification.

## Intégration Stripe
Cette API utilise Stripe pour la gestion des abonnements et des paiements. Vous devez configurer vos clés API Stripe dans le fichier .env.

## Webhook Stripe
Stripe enverra des événements à votre URL de webhook pour les différents événements de paiement. Assurez-vous que votre point de terminaison webhook (/stripe/webhook) est bien configuré dans votre tableau de bord Stripe.
