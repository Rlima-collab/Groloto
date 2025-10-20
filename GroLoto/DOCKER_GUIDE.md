# Conteneurisation avec Docker/Podman - Guide Complet

## Contexte

Vous souhaitez utiliser Docker pour conteneuriser l'application, mais Docker ne peut pas être installé directement sur ce PC (restrictions système/réseau). Ce guide propose une solution avec **Podman**, une alternative à Docker qui fonctionne sans privilèges root.

## Qu'est-ce que Podman?

**Podman** est une alternative à Docker qui:
- Fonctionne sans daemon (plus sécurisé)
- Ne nécessite pas de privilèges root (mode rootless)
- Est 100% compatible avec Docker (mêmes commandes, mêmes Dockerfile)
- Peut utiliser `docker-compose` (via podman-compose)

## Architecture de l'application conteneurisée

```
┌─────────────────────────────────────────┐
│         Nginx (Port 8000)               │
│  Serveur web + reverse proxy            │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│      PHP-FPM 8.2 (Port 9000)            │
│  Application Symfony + Extensions       │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│     PostgreSQL 16 (Port 5432)           │
│  Base de données persistante            │
└─────────────────────────────────────────┘
```

## Installation et Configuration

### Étape 1: Installer Podman

```bash
# Rendre le script exécutable
chmod +x scripts/install_podman.sh

# Lancer l'installation
./scripts/install_podman.sh
```

Le script va:
1. Installer Podman via apt (nécessite sudo une seule fois)
2. Configurer Podman en mode rootless
3. Créer des alias `docker` et `docker-compose` pour compatibilité
4. Installer `podman-compose`

**Note**: Si vous n'avez pas les droits sudo, passez à la section "Alternatives sans Podman".

### Étape 2: Recharger les alias

```bash
source ~/.bash_aliases
```

### Étape 3: Démarrer l'application

```bash
# Méthode automatique (recommandée)
./scripts/start_containers.sh

# OU manuellement
podman-compose up --build -d
# ou avec l'alias Docker:
docker compose up --build -d
```

### Étape 4: Accéder à l'application

Ouvrez votre navigateur: **http://localhost:8000**

## Commandes Utiles

### Gestion des conteneurs

```bash
# Démarrer les conteneurs
podman-compose up -d

# Arrêter les conteneurs
podman-compose down

# Voir l'état des conteneurs
podman-compose ps

# Voir les logs
podman-compose logs -f

# Redémarrer un service
podman-compose restart php
```

### Commandes Symfony dans le conteneur

```bash
# Entrer dans le conteneur PHP
podman-compose exec php bash

# Exécuter des migrations
podman-compose exec php php bin/console doctrine:migrations:migrate

# Nettoyer le cache
podman-compose exec php php bin/console cache:clear

# Créer un admin
podman-compose exec php php bin/console app:create-admin
```

### Débug et Maintenance

```bash
# Reconstruire les images
podman-compose build --no-cache

# Voir les logs d'un service spécifique
podman-compose logs -f nginx
podman-compose logs -f php
podman-compose logs -f database

# Supprimer tous les conteneurs et volumes
podman-compose down -v

# Nettoyer les images inutilisées
podman system prune -a
```

## Structure des Fichiers Docker

```
GroLoto/
├── Dockerfile                    # Image PHP-FPM avec Symfony
├── docker-compose.yaml           # Orchestration des services
├── .dockerignore                 # Fichiers exclus du build
├── docker/
│   └── nginx/
│       └── default.conf          # Configuration Nginx
└── scripts/
    ├── install_podman.sh         # Installation Podman
    └── start_containers.sh       # Démarrage automatique
```

## Configuration des Services

### Service PHP-FPM
- **Image**: PHP 8.2 FPM Alpine
- **Extensions**: intl, pdo, pdo_pgsql, zip, opcache
- **Volume**: Code source monté en temps réel
- **Port**: 9000 (interne)

### Service Nginx
- **Image**: Nginx 1.25 Alpine
- **Configuration**: Reverse proxy vers PHP-FPM
- **Port**: 8000 (externe) → 80 (interne)

### Service PostgreSQL
- **Image**: PostgreSQL 16 Alpine
- **Volume**: Données persistantes
- **Port**: 5432 (interne)
- **Healthcheck**: Vérifie la disponibilité avant de démarrer PHP

## Variables d'Environnement

Créez un fichier `.env.local` (ignoré par Git) pour personnaliser:

```env
# Base de données
POSTGRES_DB=groloto_db
POSTGRES_USER=groloto_user
POSTGRES_PASSWORD=VotreMotDePasseSecurise

# Symfony
APP_ENV=dev
APP_DEBUG=1
```

## Alternatives sans Podman

Si vous ne pouvez vraiment pas installer Podman (restrictions trop fortes):

### Option 1: GitHub Codespaces
1. Push votre projet sur GitHub
2. Créez un Codespace depuis votre repo
3. Docker est préinstallé et prêt à l'emploi
4. Lancez `docker compose up --build`

### Option 2: GitPod
1. Push votre projet sur GitLab/GitHub/Bitbucket
2. Préfixez l'URL avec `https://gitpod.io/#`
3. GitPod démarre un environnement avec Docker
4. Lancez `docker compose up --build`

### Option 3: Play with Docker
1. Allez sur https://labs.play-with-docker.com
2. Créez une instance (4h gratuites)
3. Clonez votre repo
4. Lancez `docker compose up --build`

### Option 4: Machine distante avec SSH
Si vous avez accès à un serveur distant avec Docker:

```bash
# Copier les fichiers
scp -r . user@serveur:/path/to/projet

# Se connecter
ssh user@serveur

# Démarrer Docker
cd /path/to/projet
docker compose up --build -d
```

## Différences Podman vs Docker

| Aspect | Docker | Podman |
|--------|--------|--------|
| Daemon | Oui (dockerd) | Non (daemonless) |
| Privilèges root | Requis | Optionnel (rootless) |
| Compatibilité | Standard | 100% compatible |
| Commandes | `docker`, `docker-compose` | `podman`, `podman-compose` |
| Sécurité | Bon | Meilleur (pas de daemon root) |

## Dépannage

### "Permission denied" avec Podman

```bash
# Vérifier les permissions
podman system migrate

# Redémarrer la session
logout
# puis reconnectez-vous
```

### "Port 8000 already in use"

```bash
# Trouver le processus
sudo lsof -i :8000

# Tuer le processus
sudo kill -9 <PID>

# Ou changer le port dans docker-compose.yaml
# ports:
#   - "8080:80"  # au lieu de 8000:80
```

### "Database connection failed"

```bash
# Vérifier que la DB est healthy
podman-compose ps

# Voir les logs de la DB
podman-compose logs database

# Attendre 30 secondes puis redémarrer PHP
podman-compose restart php
```

### "Build fails with composer errors"

```bash
# Nettoyer le cache composer
rm -rf vendor/
rm composer.lock

# Rebuild
podman-compose build --no-cache
```

## Performance et Optimisation

### Cache des layers Docker
Les builds suivants seront plus rapides grâce au cache des layers.

### Volumes cached (macOS/Windows)
```yaml
volumes:
  - .:/var/www/html:cached  # Améliore les perfs
```

### Opcache activé
Configuration PHP optimisée pour Symfony avec opcache.

## Migration vers Production

Pour déployer en production, modifiez:

1. **Dockerfile**: Utilisez `--no-dev` pour Composer
2. **docker-compose.yaml**: Retirez les volumes de code source
3. **Variables d'env**: Utilisez `.env.prod` avec valeurs sécurisées
4. **Nginx**: Configurez HTTPS et domaine

## Support

Si vous rencontrez des problèmes:

1. Vérifiez les logs: `podman-compose logs -f`
2. Vérifiez l'état: `podman-compose ps`
3. Reconstruisez: `podman-compose build --no-cache`
4. Nettoyez: `podman-compose down -v && podman system prune -a`

---

**Résumé des commandes pour tester:**

```bash
# Installation
./scripts/install_podman.sh
source ~/.bash_aliases

# Démarrage
./scripts/start_containers.sh

# Accès
# → http://localhost:8000

# Arrêt
podman-compose down
```
