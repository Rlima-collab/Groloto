# 🎰 GroLoto - Application de Gestion de Lotos

Application Symfony 7.3 pour la gestion complète d'événements de loto : bénévoles, mécènes, stocks, tâches et planning.

## 📋 Table des matières

- [Prérequis](#prérequis)
- [Installation avec Docker (Recommandé)](#installation-avec-docker-recommandé)
- [Installation Manuelle](#installation-manuelle)
- [Utilisation](#utilisation)
- [Architecture](#architecture)
- [Développement](#développement)

---

## 🔧 Prérequis

### Pour Docker (Recommandé)
- **Docker** (version 20.10 ou supérieure)
- **Docker Compose** (version 2.0 ou supérieure)

### Pour l'installation manuelle
- **PHP** 8.2 ou supérieur
- **Composer** 2.x
- **SQLite3**
- Extensions PHP : `pdo_sqlite`, `intl`, `zip`, `xml`, `mbstring`, `curl`

---

## 🐳 Installation avec Docker (Recommandé)

### 1️⃣ Cloner le projet

```bash
git clone https://github.com/Rlima-collab/Groloto.git
cd Groloto/GroLoto
```

### 2️⃣ Vérifier la présence des fichiers Docker

Assurez-vous que les fichiers suivants existent :
- `Dockerfile` - Configuration du conteneur
- `compose.yaml` - Orchestration des services
- `docker/apache/000-default.conf` - Configuration Apache

### 3️⃣ Construire et démarrer les conteneurs

```bash
# Construction de l'image Docker et démarrage des services
docker compose up --build -d
```

**Explication des options :**
- `--build` : Reconstruit l'image si nécessaire
- `-d` : Mode détaché (en arrière-plan)

### 4️⃣ Vérifier le statut des conteneurs

```bash
docker compose ps
```

Vous devriez voir :
```
NAME                IMAGE              STATUS         PORTS
groloto_app         groloto-app        Up             0.0.0.0:8080->80/tcp
groloto_mailer      axllent/mailpit    Up             0.0.0.0:1025->1025/tcp, 0.0.0.0:8025->8025/tcp
```

### 5️⃣ Accéder à l'application

- **Application principale** : [http://localhost:8080](http://localhost:8080)
- **Interface MailPit** (emails) : [http://localhost:8025](http://localhost:8025)

### 6️⃣ Commandes Docker utiles

```bash
# Voir les logs de l'application
docker compose logs -f app

# Voir les logs du mailer
docker compose logs -f mailer

# Arrêter les conteneurs
docker compose stop

# Redémarrer les conteneurs
docker compose restart

# Arrêter et supprimer les conteneurs
docker compose down

# Arrêter et supprimer les conteneurs + volumes
docker compose down -v

# Accéder au shell du conteneur
docker compose exec app bash

# Exécuter des commandes Symfony dans le conteneur
docker compose exec app php bin/console cache:clear
docker compose exec app php bin/console debug:router
```

### 7️⃣ Réinitialiser la base de données

Si vous devez réinitialiser la base de données :

```bash
# Supprimer la base existante
rm -f var/data.db

# Recréer depuis le fichier SQL
docker compose exec app sqlite3 var/data.db < sql/groLoto.sql

# Ou reconstruire complètement
docker compose down -v
docker compose up --build -d
```

---

## 💻 Installation Manuelle

### 1️⃣ Cloner le projet

```bash
git clone https://github.com/Rlima-collab/Groloto.git
cd Groloto/GroLoto
```

### 2️⃣ Installer PHP 8.2 (si nécessaire)

**Sur Ubuntu/Debian :**
```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-sqlite3 php8.2-xml \
    php8.2-mbstring php8.2-intl php8.2-curl php8.2-zip
```

### 3️⃣ Installer les dépendances

```bash
composer install
```

### 4️⃣ Créer la base de données

```bash
# Créer le dossier var s'il n'existe pas
mkdir -p var

# Importer le fichier SQL
sqlite3 var/data.db < sql/groLoto.sql

# Vérifier la création
sqlite3 var/data.db ".tables"
```

### 5️⃣ Configurer l'environnement

Copiez le fichier `.env` et adaptez-le :
```bash
cp .env .env.local
```

### 6️⃣ Vider le cache

```bash
php bin/console cache:clear
```

### 7️⃣ Démarrer le serveur

**Avec Symfony CLI :**
```bash
symfony server:start
```

**Avec PHP intégré :**
```bash
php -S localhost:8000 -t public/
```

L'application sera accessible sur : [http://localhost:8000](http://localhost:8000)

---

## 🚀 Utilisation

### Fonctionnalités principales

1. **Gestion des utilisateurs**
   - Inscription et authentification
   - Gestion des rôles (admin, bénévole, mécène)

2. **Gestion des événements**
   - Création et planification d'événements
   - Suivi des lotos

3. **Gestion des bénévoles**
   - Inscription des bénévoles
   - Affectation aux tâches
   - Gestion des disponibilités

4. **Gestion des mécènes**
   - Inscription des entreprises mécènes
   - Suivi des conventions
   - Génération de PDF

5. **Gestion des stocks**
   - Suivi des lots
   - Historique des mouvements

6. **Communication**
   - Envoi d'emails
   - Formulaire de contact

### Comptes par défaut

**Note :** Consultez la base de données ou le code source pour les comptes de test.

---

## 🏗️ Architecture

### Structure du projet

```
GroLoto/
├── assets/              # Assets JavaScript (Stimulus)
├── bin/                 # Scripts exécutables (console)
├── config/              # Configuration Symfony
├── docker/              # Configuration Docker
│   └── apache/          # Config Apache
├── migrations/          # Migrations Doctrine
├── public/              # Point d'entrée web
│   ├── css/            # Feuilles de style
│   ├── images/         # Images
│   └── index.php       # Front controller
├── sql/                 # Scripts SQL
│   └── groLoto.sql     # Schéma de base
├── src/                 # Code source PHP
│   ├── Command/        # Commandes console
│   ├── Controller/     # Contrôleurs
│   ├── Entity/         # Entités Doctrine
│   ├── Form/           # Formulaires
│   └── Repository/     # Repositories
├── templates/           # Templates Twig
├── var/                 # Cache, logs, DB
│   └── data.db         # Base SQLite
├── vendor/              # Dépendances PHP
├── Dockerfile           # Image Docker
├── compose.yaml         # Docker Compose
└── composer.json        # Dépendances PHP
```

### Services Docker

1. **app** (groloto_app)
   - Conteneur principal avec PHP 8.2 + Apache
   - Port : 8080 → 80
   - Base : php:8.2-apache

2. **mailer** (groloto_mailer)
   - Serveur SMTP pour le développement
   - Port SMTP : 1025
   - Interface web : 8025
   - Base : axllent/mailpit

### Base de données

L'application utilise **SQLite** avec les tables suivantes :
- ROLE, UTILISATEUR, BENEVOLE, MECENE
- EVENEMENT, TACHE, AFFECTATION_TACHE
- STOCK, HISTORIQUE_STOCK, LOT
- CONVENTION, INSCRIPTION_MECENE
- DISPONIBILITE_BENEVOLE, COMMUNICATION
- HELLOASSO, PARAMETRE, HISTORIQUE_EVENEMENT

---

## 🛠️ Développement

### Commandes Symfony utiles

```bash
# Lister les routes
php bin/console debug:router

# Créer un contrôleur
php bin/console make:controller

# Créer une entité
php bin/console make:entity

# Créer un formulaire
php bin/console make:form

# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Vider le cache
php bin/console cache:clear
```

### Mode développement avec Docker

Pour développer avec Docker et voir les modifications en temps réel :

```bash
# Utiliser les volumes montés (déjà configuré dans compose.yaml)
docker compose up -d

# Les fichiers dans public/ et var/ sont synchronisés automatiquement
```

### Debugging

**Voir les logs Apache dans Docker :**
```bash
docker compose exec app tail -f /var/log/apache2/error.log
```

**Voir les logs Symfony :**
```bash
docker compose exec app tail -f var/log/dev.log
```

### Tests

```bash
# Exécuter les tests
php bin/phpunit

# Avec Docker
docker compose exec app php bin/phpunit
```

---

## 📝 Variables d'environnement

### Fichier `.env`

```env
APP_ENV=dev                    # Environnement (dev/prod)
APP_SECRET=your-secret-key     # Clé secrète Symfony
DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db
MAILER_DSN=smtp://localhost:1025
CONTACT_EMAIL=contact@example.com
```

### Avec Docker

Les variables sont définies dans `compose.yaml` et `.env.docker`.

---

## 🔒 Sécurité

### En production

1. **Changer le APP_SECRET**
   ```env
   APP_SECRET=VotreCleSuperSecreteEtLongue123!@#
   ```

2. **Configurer HTTPS**
   - Utiliser un reverse proxy (nginx, Traefik)
   - Obtenir un certificat SSL (Let's Encrypt)

3. **Sauvegarder la base de données**
   ```bash
   # Backup
   cp var/data.db var/data.db.backup
   
   # Avec Docker
   docker compose cp app:/var/www/html/var/data.db ./backup/
   ```

4. **Permissions des fichiers**
   ```bash
   chmod 644 var/data.db
   chown www-data:www-data var/data.db
   ```

---

## 🐛 Dépannage

### L'application ne démarre pas

```bash
# Vérifier les logs
docker compose logs app

# Reconstruire l'image
docker compose build --no-cache
docker compose up -d
```

### Erreur de permissions

```bash
docker compose exec app chown -R www-data:www-data /var/www/html/var
docker compose exec app chmod -R 755 /var/www/html/var
```

### Base de données corrompue

```bash
rm var/data.db
docker compose exec app sqlite3 var/data.db < sql/groLoto.sql
```

### Port déjà utilisé

Modifiez les ports dans `compose.yaml` :
```yaml
ports:
  - "8081:80"  # Au lieu de 8080
```

---

## 📞 Support

Pour toute question ou problème :
1. Vérifiez la documentation ci-dessus
2. Consultez les logs : `docker compose logs`
3. Ouvrez une issue sur GitHub

---

## 📄 Licence

Ce projet est sous licence propriétaire.

---

## 👥 Contributeurs

- Équipe GroLoto

---

**Bon développement ! 🚀**
