# Dockerfile pour l'application Symfony GroLoto
FROM php:8.2-apache

# Installation des dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    sqlite3 \
    libsqlite3-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_sqlite \
    pdo_pgsql \
    pgsql \
    intl \
    zip \
    gd \
    opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration d'Apache
RUN a2enmod rewrite headers

# Copie de la configuration Apache personnalisée
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . /var/www/html

# Créer les répertoires nécessaires avec les bonnes permissions
RUN mkdir -p var/cache var/log var && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Installation des dépendances PHP (avec dev pour éviter les erreurs de bundles)
RUN composer install --optimize-autoloader --no-interaction --ignore-platform-reqs

# Créer la base de données SQLite si elle n'existe pas
RUN if [ ! -f var/data.db ]; then \
        sqlite3 var/data.db < sql/groLoto.sql && \
        chown www-data:www-data var/data.db && \
        chmod 664 var/data.db; \
    fi

# Vider le cache Symfony
RUN php bin/console cache:clear --env=prod && \
    chown -R www-data:www-data var/

# Exposer le port 80
EXPOSE 80

# Commande de démarrage
CMD ["apache2-foreground"]
