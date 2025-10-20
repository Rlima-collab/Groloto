# 🎯 MÉTHODE COPIER-COLLER POUR PLAY WITH DOCKER

## Vous N'AVEZ PAS besoin de Git ou d'uploader de fichier !

Cette méthode utilise uniquement le **copier-coller** dans le terminal de Play with Docker.

---

## 🚀 ÉTAPES COMPLÈTES (15 minutes)

### ÉTAPE 1: Aller sur Play with Docker

1. Ouvrez votre navigateur
2. Allez sur: **https://labs.play-with-docker.com**
3. Cliquez **"Login"** avec Docker Hub
   - Pas de compte? Créez-en un sur https://hub.docker.com (2 minutes)
4. Cliquez **"Start"**
5. Cliquez **"+ ADD NEW INSTANCE"**

➡️ Un terminal Linux avec Docker apparaît

---

### ÉTAPE 2: Créer le dossier du projet

Dans le terminal Play with Docker, copiez-collez:

```bash
mkdir -p GroLoto/docker/nginx
cd GroLoto
```

---

### ÉTAPE 3: Créer le Dockerfile

Copiez-collez cette commande complète:

```bash
cat > Dockerfile << 'EOFFILE'
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    git unzip icu-dev postgresql-dev libzip-dev oniguruma-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl pdo pdo_pgsql zip opcache \
    && apk del --no-cache icu-dev postgresql-dev libzip-dev oniguruma-dev

RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader || true
RUN chown -R www-data:www-data /var/www/html/var || true

EXPOSE 9000
CMD ["php-fpm"]
EOFFILE
```

✅ Appuyez sur **Entrée**

---

### ÉTAPE 4: Créer le docker-compose.yaml

Copiez-collez:

```bash
cat > docker-compose.yaml << 'EOFFILE'
version: '3.8'

services:
  database:
    image: postgres:16-alpine
    container_name: groloto_db
    environment:
      POSTGRES_DB: app
      POSTGRES_USER: app
      POSTGRES_PASSWORD: '!ChangeMe!'
    healthcheck:
      test: ["CMD", "pg_isready", "-d", "app", "-U", "app"]
      interval: 10s
      timeout: 5s
      retries: 5
    volumes:
      - db_data:/var/lib/postgresql/data
    networks:
      - groloto_network

  php:
    build: .
    container_name: groloto_php
    depends_on:
      database:
        condition: service_healthy
    environment:
      DATABASE_URL: "postgresql://app:!ChangeMe!@database:5432/app?serverVersion=16&charset=utf8"
      APP_ENV: dev
      APP_DEBUG: 1
    volumes:
      - .:/var/www/html:cached
    networks:
      - groloto_network

  nginx:
    image: nginx:1.25-alpine
    container_name: groloto_nginx
    depends_on:
      - php
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html:ro
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
    networks:
      - groloto_network

volumes:
  db_data:

networks:
  groloto_network:
EOFFILE
```

✅ Appuyez sur **Entrée**

---

### ÉTAPE 5: Créer la configuration Nginx

Copiez-collez:

```bash
cat > docker/nginx/default.conf << 'EOFFILE'
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass php:9000;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }
}
EOFFILE
```

✅ Appuyez sur **Entrée**

---

### ÉTAPE 6: Créer .dockerignore

Copiez-collez:

```bash
cat > .dockerignore << 'EOFFILE'
vendor/
node_modules/
var/cache/
var/log/
.git/
.env
.env.local
EOFFILE
```

---

### ÉTAPE 7: Créer les fichiers Symfony essentiels

#### A. Créer composer.json minimal

```bash
cat > composer.json << 'EOFFILE'
{
    "name": "groloto/app",
    "type": "project",
    "require": {
        "php": ">=8.1",
        "symfony/console": "^6.0",
        "symfony/dotenv": "^6.0",
        "symfony/flex": "^2.0",
        "symfony/framework-bundle": "^6.0",
        "symfony/runtime": "^6.0",
        "symfony/yaml": "^6.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
EOFFILE
```

#### B. Créer la structure de base

```bash
mkdir -p public src config var/cache var/log
```

#### C. Créer public/index.php

```bash
cat > public/index.php << 'EOFFILE'
<?php
echo "<!DOCTYPE html>";
echo "<html><head><title>GroLoto - Docker Test</title>";
echo "<style>body{font-family:Arial;text-align:center;padding:50px;background:#f0f0f0}";
echo "h1{color:#006909}p{font-size:18px}</style></head><body>";
echo "<h1>🎉 GroLoto Application</h1>";
echo "<p>✅ Docker fonctionne correctement!</p>";
echo "<p>Version PHP: " . PHP_VERSION . "</p>";
echo "<p>Extensions chargées: " . implode(", ", get_loaded_extensions()) . "</p>";
echo "</body></html>";
EOFFILE
```

---

### ÉTAPE 8: Vérifier que tous les fichiers sont créés

```bash
ls -la
ls -la docker/nginx/
```

Vous devez voir:
- `Dockerfile`
- `docker-compose.yaml`
- `.dockerignore`
- `composer.json`
- `public/index.php`
- `docker/nginx/default.conf`

---

### ÉTAPE 9: Démarrer Docker

```bash
docker compose up --build -d
```

⏳ **Attendez 3-5 minutes** pour le premier build

Pour suivre la progression:
```bash
docker compose logs -f
```

---

### ÉTAPE 10: Accéder à l'application

1. **Regardez en haut de l'écran** Play with Docker
2. Un badge **"8000"** apparaît
3. **Cliquez dessus**
4. **Votre application s'affiche!** 🎉

---

## 🎯 SI VOUS VOULEZ AJOUTER VOTRE CODE SYMFONY COMPLET

### Option A: Copier-coller vos fichiers un par un

Pour chaque fichier important (controllers, templates, etc.):

```bash
cat > src/Controller/HomeController.php << 'EOFFILE'
# Collez ici le contenu de votre fichier
EOFFILE
```

### Option B: Utiliser un éditeur dans Play with Docker

Play with Docker a un éditeur intégré:
1. Cliquez sur "Editor" en haut
2. Créez/éditez vos fichiers directement
3. Sauvegardez

---

## 📋 COMMANDES UTILES

### Voir l'état des conteneurs
```bash
docker compose ps
```

### Voir les logs
```bash
docker compose logs -f
docker compose logs -f php
docker compose logs -f nginx
```

### Redémarrer
```bash
docker compose restart
```

### Arrêter
```bash
docker compose down
```

### Reconstruire après modification
```bash
docker compose down
docker compose up --build -d
```

---

## ❓ FAQ

**Q: Puis-je copier mes fichiers PHP/Symfony existants?**
A: Oui, utilisez la méthode `cat > fichier << 'EOFFILE'` pour chaque fichier, ou l'éditeur intégré.

**Q: Comment copier plusieurs fichiers rapidement?**
A: Créez un script qui génère tous vos fichiers avec des commandes `cat`, ou utilisez l'éditeur.

**Q: Le badge 8000 n'apparaît pas?**
A: Vérifiez `docker compose ps` - attendez que tous les services soient "Up" et "healthy".

**Q: Erreur "502 Bad Gateway"?**
A: Attendez 30 secondes puis `docker compose restart php nginx`

**Q: Comment sauvegarder mon travail?**
A: Vous ne pouvez pas (session de 4h). Copiez vos modifications importantes avant la fin.

---

## 🆘 DÉPANNAGE

### Les fichiers ne se créent pas
Vérifiez que vous copiez TOUTE la commande `cat > ... << 'EOFFILE' ... EOFFILE`

### Build échoue
```bash
docker compose logs php
```

### Port 8000 déjà utilisé
Changez le port dans docker-compose.yaml:
```yaml
ports:
  - "80:80"  # au lieu de "8000:80"
```

---

## ✅ CHECKLIST

- [ ] Play with Docker ouvert
- [ ] Instance créée
- [ ] Dossier GroLoto créé
- [ ] Dockerfile copié
- [ ] docker-compose.yaml copié
- [ ] Configuration Nginx copiée
- [ ] Fichiers Symfony de base créés
- [ ] `docker compose up --build -d` exécuté
- [ ] Badge "8000" apparu
- [ ] Application testée

---

**C'est tout! Pas besoin de Git, pas besoin d'upload. Juste du copier-coller! 🚀**
