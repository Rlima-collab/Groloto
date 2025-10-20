# Dockerfile pour application Symfony avec PHP-FPM
FROM php:8.2-fpm-alpine

# Prévenir les prompts interactifs
ARG DEBIAN_FRONTEND=noninteractive

# Installer les dépendances système et extensions PHP
RUN apk add --no-cache \
    git \
    unzip \
    icu-dev \
    postgresql-dev \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        intl \
        pdo \
        pdo_pgsql \
        zip \
        opcache \
    && apk del --no-cache \
        icu-dev \
        postgresql-dev \
        libzip-dev \
        oniguruma-dev

# Configuration PHP optimisée pour développement
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Installer Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de dépendances
COPY composer.json composer.lock symfony.lock ./

# Installer les dépendances PHP (sans scripts pour éviter erreurs si app pas complète)
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader || true

# Copier le reste de l'application
COPY . .

# Installer toutes les dépendances (avec scripts cette fois)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader || true

# Permissions pour www-data
RUN chown -R www-data:www-data /var/www/html/var || true

# Exposer le port PHP-FPM
EXPOSE 9000

# Lancer PHP-FPM
CMD ["php-fpm"]
