# 🐳 Guide Docker - GroLoto

Ce guide explique comment utiliser Docker pour déployer et gérer l'application GroLoto.

## 📋 Table des matières

- [Architecture Docker](#architecture-docker)
- [Démarrage rapide](#démarrage-rapide)
- [Configuration](#configuration)
- [Commandes avancées](#commandes-avancées)
- [Développement](#développement)
- [Production](#production)
- [Dépannage](#dépannage)

---

## 🏗️ Architecture Docker

### Services

L'application utilise 2 conteneurs Docker :

#### 1. **app** (Application Symfony)
- **Base**: `php:8.2-apache`
- **Port**: `8080:80`
- **Volumes**:
  - `./var:/var/www/html/var` - Données et cache
  - `./public:/var/www/html/public` - Assets publics
- **Dépendances**: PHP 8.2, Apache, SQLite, extensions PHP

#### 2. **mailer** (MailPit)
- **Base**: `axllent/mailpit`
- **Ports**:
  - `1025` - SMTP
  - `8025` - Interface web
- **Usage**: Capture et affichage des emails en développement

### Réseau

Les services communiquent via le réseau Docker `groloto_network`.

---

## 🚀 Démarrage rapide

### Méthode 1: Script automatique (recommandé)

```bash
./start.sh
```

### Méthode 2: Commandes manuelles

```bash
# 1. Construire les images
docker compose build

# 2. Démarrer les conteneurs
docker compose up -d

# 3. Vérifier le statut
docker compose ps
```

### Accès

- **Application**: [http://localhost:8080](http://localhost:8080)
- **MailPit**: [http://localhost:8025](http://localhost:8025)

---

## ⚙️ Configuration

### Variables d'environnement

Les variables sont définies dans `compose.yaml` :

```yaml
environment:
  APP_ENV: prod
  APP_SECRET: SomeRandomSecret123
  DATABASE_URL: "sqlite:///%kernel.project_dir%/var/data.db"
```

Pour modifier ces valeurs, éditez directement `compose.yaml` ou créez un fichier `.env`.

### Personnaliser les ports

Si les ports par défaut sont déjà utilisés, modifiez-les dans `compose.yaml` :

```yaml
services:
  app:
    ports:
      - "8081:80"  # Au lieu de 8080
  
  mailer:
    ports:
      - "1026:1025"  # SMTP
      - "8026:8025"  # Interface web
```

---

## 🛠️ Commandes avancées

### Gestion des conteneurs

```bash
# Voir les logs en temps réel
docker compose logs -f

# Logs d'un service spécifique
docker compose logs -f app
docker compose logs -f mailer

# Redémarrer les services
docker compose restart

# Arrêter sans supprimer
docker compose stop

# Redémarrer après un stop
docker compose start

# Arrêter et supprimer les conteneurs
docker compose down

# Arrêter et supprimer conteneurs + volumes
docker compose down -v
```

### Accès au conteneur

```bash
# Shell dans le conteneur app
docker compose exec app bash

# Exécuter une commande Symfony
docker compose exec app php bin/console debug:router
docker compose exec app php bin/console cache:clear

# Accéder à la base de données (Docker utilise PostgreSQL par défaut)
# Via psql dans le conteneur 'db' :
# docker compose exec db psql -U postgres -d groloto -c "SELECT * FROM utilisateur;"

# Ou via Symfony Doctrine depuis le conteneur app :
# docker compose exec app php bin/console doctrine:query:sql "SELECT * FROM utilisateur;"
```

### Gestion de la base de données

Docker utilise **PostgreSQL** en développement (plus fiable sur systèmes de fichiers partagés).

```bash
# Appliquer les migrations (crée les tables si nécessaire)
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

# Sauvegarder la base (Postgres)
docker compose exec db pg_dump -U postgres -d groloto -f /tmp/groloto.sql
# Récupérer la sauvegarde sur l'hôte
docker compose exec db cat /tmp/groloto.sql > ./backup/groloto_$(date +%Y%m%d_%H%M%S).sql

# Réinitialiser en supprimant le volume Postgres (attention: données perdues) et en relançant
docker compose down -v
docker compose up --build -d
```

### Reconstruction

```bash
# Reconstruire sans cache
docker compose build --no-cache

# Reconstruire et redémarrer
docker compose up --build -d

# Reconstruire un service spécifique
docker compose build app
```

---

## 💻 Développement

### Mode développement

Pour activer le mode développement, modifiez `compose.override.yaml` :

```yaml
services:
  app:
    environment:
      APP_ENV: dev
      APP_DEBUG: 0
```

Puis redémarrez :

```bash
docker compose down
docker compose up -d
```

### Hot reload

Les volumes montés permettent de voir les changements en temps réel :

- `./var` - Cache et logs
- `./public` - Assets CSS/JS/images

### Installer de nouvelles dépendances

```bash
# PHP (Composer)
docker compose exec app composer require nom/package

# Mettre à jour les dépendances
docker compose exec app composer update

# Reconstruire après ajout de dépendances système
docker compose build --no-cache
```

### Debugging

```bash
# Voir les erreurs Apache
docker compose exec app tail -f /var/log/apache2/error.log

# Voir les logs Symfony
docker compose exec app tail -f var/log/dev.log

# Inspecter le conteneur
docker compose exec app php -v
docker compose exec app php -m  # Extensions installées
docker compose exec app apache2 -v
```

---

## 🚀 Production

### Optimisations pour la production

1. **Modifier APP_ENV**

Éditez `compose.yaml` :

```yaml
environment:
  APP_ENV: prod
  APP_DEBUG: 0
```

2. **Changer le secret**

```yaml
environment:
  APP_SECRET: VotreCleSuperSecreteEtLongue123!@#$%
```

3. **Désactiver le montage des volumes**

Commentez les volumes dans `compose.yaml` pour utiliser les fichiers embarqués dans l'image :

```yaml
# volumes:
#   - ./var:/var/www/html/var
#   - ./public:/var/www/html/public
```

4. **Utiliser un reverse proxy**

Ajoutez nginx ou Traefik devant Apache pour HTTPS :

```yaml
services:
  nginx:
    image: nginx:alpine
    ports:
      - "443:443"
      - "80:80"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - ./ssl:/etc/nginx/ssl
    depends_on:
      - app
```

5. **Sauvegardes automatiques**

Créez un script de sauvegarde quotidien :

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
docker compose cp app:/var/www/html/var/data.db ./backups/data_${DATE}.db
# Garder seulement les 7 dernières sauvegardes
ls -t ./backups/data_*.db | tail -n +8 | xargs rm -f
```

---

## 🐛 Dépannage

### Le conteneur ne démarre pas

```bash
# Voir les logs détaillés
docker compose logs app

# Vérifier si le port est utilisé
sudo lsof -i :8080

# Reconstruire sans cache
docker compose build --no-cache
docker compose up -d
```

### Erreur de permissions

```bash
# Corriger les permissions dans le conteneur
docker compose exec app chown -R www-data:www-data /var/www/html/var
docker compose exec app chmod -R 755 /var/www/html/var
```

### Base de données corrompue

```bash
# Supprimer et recréer
docker compose exec app rm var/data.db
docker compose exec app sqlite3 var/data.db < sql/groLoto.sql
```

### Erreur "no configuration file provided"

Assurez-vous d'être dans le bon répertoire :

```bash
cd /chemin/vers/GroLoto
ls compose.yaml  # Doit exister
```

Ou spécifiez le chemin du fichier :

```bash
docker compose -f /chemin/complet/compose.yaml up -d
```

### Le cache ne se vide pas

```bash
# Vider manuellement
docker compose exec app rm -rf var/cache/*
docker compose exec app php bin/console cache:clear
```

### Problèmes réseau entre conteneurs

```bash
# Recréer le réseau
docker compose down
docker network prune
docker compose up -d
```

### L'application affiche une erreur 500

```bash
# Vérifier les logs Symfony
docker compose exec app tail -100 var/log/prod.log

# Vérifier les logs Apache
docker compose exec app tail -100 /var/log/apache2/error.log

# Vider le cache
docker compose exec app php bin/console cache:clear --env=prod
```

---

## 📊 Monitoring

### Ressources utilisées

```bash
# Voir la consommation CPU/RAM
docker stats

# Voir l'espace disque utilisé
docker system df

# Nettoyer les ressources inutilisées
docker system prune -a
```

### Healthcheck

Le service mailer a un healthcheck intégré. Pour ajouter un healthcheck à l'app :

```yaml
services:
  app:
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/"]
      interval: 30s
      timeout: 3s
      retries: 3
      start_period: 40s
```

---

## 📝 Maintenance

### Mises à jour

```bash
# Mettre à jour les images de base
docker compose pull

# Reconstruire avec les nouvelles images
docker compose build --no-cache
docker compose up -d
```

### Sauvegarde complète

```bash
# Créer un dossier de sauvegarde
mkdir -p backup/$(date +%Y%m%d)

# Sauvegarder la base
docker compose cp app:/var/www/html/var/data.db backup/$(date +%Y%m%d)/

# Sauvegarder les uploads (si applicable)
docker compose cp app:/var/www/html/public/uploads backup/$(date +%Y%m%d)/

# Archiver
tar -czf backup_$(date +%Y%m%d).tar.gz backup/$(date +%Y%m%d)
```

---

## 🔐 Sécurité

### Bonnes pratiques

1. ✅ Toujours changer `APP_SECRET` en production
2. ✅ Utiliser HTTPS avec un certificat SSL
3. ✅ Ne pas exposer les ports de debug en production
4. ✅ Sauvegarder régulièrement la base de données
5. ✅ Mettre à jour les images Docker régulièrement
6. ✅ Utiliser des volumes pour les données sensibles
7. ✅ Limiter les ressources des conteneurs

### Limiter les ressources

```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '1.0'
          memory: 512M
        reservations:
          cpus: '0.5'
          memory: 256M
```

---

## 📞 Support

Si vous rencontrez des problèmes non couverts ici :

1. Vérifiez les logs : `docker compose logs`
2. Consultez le README principal
3. Vérifiez la documentation Docker officielle
4. Ouvrez une issue sur GitHub

---

**Bon développement avec Docker ! 🐳**
