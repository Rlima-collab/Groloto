# 📋 Résumé de la conteneurisation - GroLoto

## ✅ Fichiers créés/modifiés

### Nouveaux fichiers

1. **Dockerfile** - Configuration du conteneur PHP/Apache
   - Base PHP 8.2 avec Apache
   - Installation des extensions nécessaires
   - Configuration Apache
   - Installation des dépendances Composer
   - Création de la base de données SQLite

2. **docker/apache/000-default.conf** - Configuration Apache pour Symfony
   - DocumentRoot pointant vers /public
   - Activation de la réécriture d'URL
   - Configuration optimisée pour Symfony

3. **.dockerignore** - Exclusions pour le build Docker
   - Ignore les fichiers inutiles pour optimiser la taille de l'image

4. **.env.docker** - Variables d'environnement pour Docker

5. **README.md** - Documentation complète
   - Guide d'installation avec Docker
   - Guide d'installation manuelle
   - Commandes utiles
   - Architecture du projet
   - Dépannage

6. **DOCKER.md** - Guide spécifique Docker
   - Architecture détaillée
   - Commandes avancées
   - Configuration pour la production
   - Monitoring et maintenance
   - Sécurité

7. **start.sh** - Script de démarrage rapide
   - Construction automatique des images
   - Démarrage des conteneurs
   - Affichage des URL d'accès

8. **stop.sh** - Script d'arrêt
   - Arrêt propre des conteneurs

### Fichiers modifiés

1. **compose.yaml** - Configuration Docker Compose
   - Service `app` : Application Symfony
   - Service `mailer` : MailPit pour les emails
   - Réseau `groloto_network`
   - Volumes pour la persistance des données

2. **compose.override.yaml** - Surcharges pour le développement
   - Variables d'environnement pour le mode dev

## 🏗️ Architecture Docker

```
┌─────────────────────────────────────────┐
│           Docker Compose                │
│                                         │
│  ┌─────────────┐      ┌──────────────┐ │
│  │   app       │      │   mailer     │ │
│  │ (Symfony)   │─────▶│  (MailPit)   │ │
│  │ PHP 8.2     │      │   SMTP       │ │
│  │ Apache      │      │              │ │
│  │ SQLite      │      │              │ │
│  └─────────────┘      └──────────────┘ │
│       │                     │          │
│    Port 8080            Port 8025      │
│    Port 1025                           │
└─────────────────────────────────────────┘
           │
           ▼
    ┌──────────────┐
    │   Volumes    │
    │  - var/      │
    │  - public/   │
    └──────────────┘
```

## 🚀 Utilisation

### Démarrage

```bash
# Méthode 1 : Script automatique
./start.sh

# Méthode 2 : Commandes Docker Compose
docker compose build
docker compose up -d
```

### Accès

- **Application** : http://localhost:8080
- **MailPit (emails)** : http://localhost:8025

### Arrêt

```bash
# Méthode 1 : Script
./stop.sh

# Méthode 2 : Commande Docker Compose
docker compose stop

# Arrêt et suppression
docker compose down
```

## ✨ Avantages de la conteneurisation

1. **Portabilité** : Fonctionne sur n'importe quel système avec Docker
2. **Isolation** : Pas de conflit avec d'autres installations PHP
3. **Reproductibilité** : Environnement identique pour tous les développeurs
4. **Facilité** : Démarrage en une seule commande
5. **Production-ready** : Configuration adaptable pour la production

## 🔧 Configuration

### Ports par défaut

- Application : 8080 → 80 (conteneur)
- MailPit SMTP : 1025 → 1025 (conteneur)
- MailPit Web : 8025 → 8025 (conteneur)

### Base de données

- **Type** : SQLite
- **Localisation** : `var/data.db`
- **Persistance** : Via volume Docker monté

### Variables d'environnement

Définies dans `compose.yaml` :
- `APP_ENV` : dev/prod
- `APP_SECRET` : Clé secrète Symfony
- `DATABASE_URL` : URL de la base SQLite

## 📦 Services

### 1. app (groloto_app)

**Image** : Construite depuis le Dockerfile
**Rôle** : Application Symfony principale
**Contient** :
- PHP 8.2 avec extensions (SQLite, intl, zip, gd, opcache)
- Apache 2.4
- Composer
- Code source de l'application
- Base de données SQLite

### 2. mailer (groloto_mailer)

**Image** : axllent/mailpit
**Rôle** : Serveur SMTP de développement
**Fonctionnalités** :
- Capture tous les emails envoyés
- Interface web pour visualiser les emails
- Pas d'envoi réel (développement)

## 🧪 Tests effectués

✅ Construction de l'image Docker
✅ Démarrage des conteneurs
✅ Accès à l'application (HTTP 200)
✅ Vérification de la base de données (16 tables)
✅ Accès à l'interface MailPit
✅ Vérification des logs Apache
✅ Persistance des données via volumes

## 📝 Commandes utiles

```bash
# Voir les logs
docker compose logs -f

# Accéder au conteneur
docker compose exec app bash

# Exécuter des commandes Symfony
docker compose exec app php bin/console cache:clear
docker compose exec app php bin/console debug:router

# Gérer la base de données
docker compose exec app sqlite3 var/data.db ".tables"

# Sauvegarder la base
docker compose cp app:/var/www/html/var/data.db ./backup/

# Reconstruire
docker compose build --no-cache
docker compose up -d
```

## 🔐 Sécurité pour la production

**À faire avant le déploiement :**

1. Changer `APP_SECRET` dans compose.yaml
2. Définir `APP_ENV=prod`
3. Configurer HTTPS avec un reverse proxy
4. Désactiver le service mailer (utiliser un vrai SMTP)
5. Mettre en place des sauvegardes automatiques
6. Limiter les ressources des conteneurs
7. Utiliser des secrets Docker pour les données sensibles

## 📚 Documentation

- **README.md** : Guide complet d'utilisation
- **DOCKER.md** : Guide spécifique Docker avec commandes avancées
- **Symfony Docs** : https://symfony.com/doc/current/index.html
- **Docker Docs** : https://docs.docker.com/

## 🎯 Prochaines étapes

1. ✅ Conteneurisation terminée
2. ⏳ Tests en environnement de production
3. ⏳ Configuration CI/CD
4. ⏳ Orchestration avec Kubernetes (optionnel)
5. ⏳ Monitoring (Prometheus/Grafana)

---

**Date de création** : 24 octobre 2025
**Branche** : feature/docker-containerization
**Statut** : ✅ Testé et fonctionnel
