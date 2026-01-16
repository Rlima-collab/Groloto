# Architecture du Projet GroLoto

## Vue d'ensemble

GroLoto est une application web Symfony pour la gestion d'une tombola caritative. Elle permet de gérer les bénévoles, les mécènes, les événements, les stocks et la messagerie interne.

## Structure des dossiers

```
GroLoto/
├── config/              # Configuration Symfony
│   ├── packages/        # Configuration des bundles
│   └── routes/          # Routes de l'application
├── docker/              # Configuration Docker
│   └── apache/          # Configuration Apache
├── docs/                # Documentation du projet
├── migrations/          # Migrations Doctrine
├── public/              # Fichiers publics (point d'entrée)
│   ├── css/             # Feuilles de style
│   ├── js/              # Scripts JavaScript
│   ├── images/          # Images statiques
│   └── uploads/         # Fichiers uploadés par les utilisateurs
├── sql/                 # Scripts SQL initiaux
├── src/                 # Code source PHP
│   ├── Command/         # Commandes console
│   ├── Controller/      # Contrôleurs
│   ├── Dto/             # Data Transfer Objects
│   ├── Entity/          # Entités Doctrine
│   ├── Form/            # Types de formulaires
│   ├── Repository/      # Repositories Doctrine
│   ├── Service/         # Services métier
│   └── Validator/       # Validateurs personnalisés
├── templates/           # Templates Twig
│   ├── admin/           # Templates administration
│   ├── auth/            # Templates authentification
│   ├── benevole/        # Templates espace bénévole
│   ├── mecene/          # Templates espace mécène
│   └── ...              # Autres templates
├── tests/               # Tests PHPUnit
├── translations/        # Fichiers de traduction
└── var/                 # Cache et logs
```

## Organisation des assets CSS

Les fichiers CSS sont organisés par domaine fonctionnel :

| Fichier | Description |
|---------|-------------|
| `admin.css` | Styles pour les pages d'administration |
| `benevoles.css` | Styles pour les pages bénévoles |
| `evenements.css` | Styles pour les pages événements |
| `mecenes.css` | Styles pour les pages mécènes |
| `stocks.css` | Styles pour la gestion des stocks |
| `taches.css` | Styles pour la gestion des tâches |
| `weekends.css` | Styles pour les weekends/festivals |
| `shared/messaging.css` | Styles partagés pour la messagerie |

## Organisation des assets JavaScript

| Fichier | Description |
|---------|-------------|
| `notifications.js` | Gestion des notifications temps réel |
| `register.js` | Validation des formulaires d'inscription |
| `sidebar.js` | Comportement de la sidebar |
| `shared/messaging.js` | Fonctions partagées de messagerie |
| `admin/messaging.js` | Fonctions admin de messagerie |

## Rôles utilisateurs

- **ROLE_ADMIN** : Accès complet à l'administration
- **ROLE_BENEVOLE** : Gestion des disponibilités et tâches
- **ROLE_MECENE** : Inscription aux événements et dons

## Base de données

L'application utilise SQLite en développement. Les principales tables :

- `UTILISATEUR` : Utilisateurs du système
- `BENEVOLE` : Informations spécifiques aux bénévoles
- `MECENE` : Informations spécifiques aux mécènes
- `WEEKEND` : Festivals/événements
- `TACHE` : Tâches assignables aux bénévoles
- `STOCK` : Gestion des stocks
- `CONTACT_MESSAGE` : Messagerie interne

## Lancement de l'application

```bash
# Avec Docker
docker-compose up -d

# Ou avec le serveur Symfony
symfony serve
```

## Tests

```bash
# Exécuter tous les tests
php bin/phpunit

# Exécuter un test spécifique
php bin/phpunit tests/Controller/MonControllerTest.php
```
