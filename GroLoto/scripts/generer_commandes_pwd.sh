#!/usr/bin/env bash
set -euo pipefail

# Script qui génère toutes les commandes à copier-coller dans Play with Docker

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

OUTPUT_FILE="COMMANDES_COMPLETES_PLAY_WITH_DOCKER.sh"

echo "=========================================="
echo "  Génération des commandes pour"
echo "  Play with Docker (copier-coller)"
echo "=========================================="
echo ""

cat > "$OUTPUT_FILE" << 'EOFSCRIPT'
#!/bin/bash
# TOUTES LES COMMANDES À COPIER-COLLER DANS PLAY WITH DOCKER
# Copiez ce fichier ENTIER et collez-le dans le terminal Play with Docker

echo "========================================"
echo "  Configuration GroLoto avec Docker"
echo "========================================"
echo ""

# Créer la structure
mkdir -p GroLoto/docker/nginx
cd GroLoto

# Créer Dockerfile
cat > Dockerfile << 'DOCKERFILE_EOF'
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
DOCKERFILE_EOF

echo "✓ Dockerfile créé"

# Créer docker-compose.yaml
cat > docker-compose.yaml << 'COMPOSE_EOF'
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
COMPOSE_EOF

echo "✓ docker-compose.yaml créé"

# Créer configuration Nginx
cat > docker/nginx/default.conf << 'NGINX_EOF'
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
NGINX_EOF

echo "✓ Configuration Nginx créée"

# Créer .dockerignore
cat > .dockerignore << 'DOCKERIGNORE_EOF'
vendor/
node_modules/
var/cache/
var/log/
.git/
.env
.env.local
DOCKERIGNORE_EOF

echo "✓ .dockerignore créé"

# Créer structure Symfony minimale
mkdir -p public src config var/cache var/log

# Créer composer.json
cat > composer.json << 'COMPOSER_EOF'
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
COMPOSER_EOF

echo "✓ composer.json créé"

# Créer index.php de test
cat > public/index.php << 'INDEX_EOF'
<?php
echo "<!DOCTYPE html>";
echo "<html><head><title>GroLoto - Docker Test</title>";
echo "<style>body{font-family:Arial;text-align:center;padding:50px;background:#f0f0f0}";
echo "h1{color:#006909}p{font-size:18px}.success{color:green}.info{color:#0066cc}</style></head><body>";
echo "<h1>🎉 GroLoto Application</h1>";
echo "<p class='success'>✅ Docker fonctionne correctement!</p>";
echo "<p class='info'>Version PHP: " . PHP_VERSION . "</p>";
echo "<hr><h2>Extensions PHP chargées:</h2>";
echo "<p style='font-size:14px'>" . implode(", ", get_loaded_extensions()) . "</p>";
echo "<hr><p>Conteneurisé avec Docker + Nginx + PHP-FPM + PostgreSQL</p>";
echo "</body></html>";
INDEX_EOF

echo "✓ public/index.php créé"

# Vérifier les fichiers
echo ""
echo "========================================"
echo "  Vérification des fichiers"
echo "========================================"
ls -la
echo ""
echo "Configuration Nginx:"
ls -la docker/nginx/
echo ""

echo "========================================"
echo "  Démarrage de Docker"
echo "========================================"
echo ""
echo "⏳ Build en cours (3-5 minutes)..."
echo ""

# Démarrer Docker
docker compose up --build -d

echo ""
echo "========================================"
echo "  État des conteneurs"
echo "========================================"
docker compose ps

echo ""
echo "========================================"
echo "  ✅ TERMINÉ!"
echo "========================================"
echo ""
echo "Pour accéder à l'application:"
echo "  → Cliquez sur le badge '8000' en haut de l'écran"
echo ""
echo "Commandes utiles:"
echo "  docker compose logs -f       # Voir les logs"
echo "  docker compose ps            # État des conteneurs"
echo "  docker compose restart php   # Redémarrer PHP"
echo "  docker compose down          # Arrêter tout"
echo ""
EOFSCRIPT

chmod +x "$OUTPUT_FILE"

echo "✓ Fichier généré: $OUTPUT_FILE"
echo ""
echo "=========================================="
echo "  MODE D'EMPLOI"
echo "=========================================="
echo ""
echo "1. Ouvrez le fichier:"
echo "   cat $OUTPUT_FILE"
echo ""
echo "2. Sélectionnez TOUT le contenu (Ctrl+A)"
echo ""
echo "3. Copiez (Ctrl+C)"
echo ""
echo "4. Allez sur Play with Docker:"
echo "   https://labs.play-with-docker.com"
echo ""
echo "5. Login → Start → + ADD NEW INSTANCE"
echo ""
echo "6. Collez dans le terminal (Clic droit → Paste)"
echo ""
echo "7. Appuyez sur Entrée"
echo ""
echo "8. Attendez 3-5 minutes"
echo ""
echo "9. Cliquez sur le badge '8000' qui apparaît"
echo ""
echo "10. Votre application est en ligne! 🚀"
echo ""
