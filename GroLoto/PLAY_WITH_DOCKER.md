# 🐳 Guide Play with Docker - GroLoto

## Qu'est-ce que Play with Docker?

**Play with Docker** est un environnement Docker gratuit dans le navigateur:
- ✅ Docker pré-installé
- ✅ 4 heures par session (renouvelable)
- ✅ Accessible via navigateur web
- ✅ Parfait pour tester sans installer Docker localement

---

## 🚀 ÉTAPES COMPLÈTES POUR TESTER VOTRE APPLICATION

### Étape 1: Préparer votre code

Vous avez 2 options pour obtenir votre code dans Play with Docker:

#### Option A: Via Git (recommandé)

Si votre projet est sur GitHub/GitLab:
```bash
# Vous utiliserez cette commande dans Play with Docker
git clone https://github.com/votre-user/votre-repo.git
cd votre-repo
```

#### Option B: Via archive ZIP

Si pas de Git, créez une archive de votre projet:
```bash
# Sur votre machine actuelle, créez un ZIP
cd /home/iut45/Etudiants/o22301253/Bureau/SAE/Groloto
tar -czf GroLoto.tar.gz GroLoto/

# Uploadez ce fichier sur un service (Google Drive, Dropbox, etc.)
# et récupérez le lien de téléchargement
```

---

### Étape 2: Accéder à Play with Docker

1. **Ouvrez votre navigateur** et allez sur:
   ```
   https://labs.play-with-docker.com
   ```

2. **Connectez-vous** avec votre compte Docker Hub:
   - Si vous n'avez pas de compte, créez-en un sur https://hub.docker.com (gratuit)
   - Cliquez sur "Login" puis "Docker"

3. **Démarrez une session**:
   - Cliquez sur "Start"
   - Vous avez maintenant 4 heures

4. **Créez une instance**:
   - Cliquez sur "+ ADD NEW INSTANCE"
   - Un terminal Linux avec Docker apparaît

---

### Étape 3: Récupérer votre projet

**Option A: Si vous avez Git**
```bash
# Dans le terminal Play with Docker
git clone https://github.com/VOTRE-USER/VOTRE-REPO.git
cd VOTRE-REPO
```

**Option B: Si vous avez uploadé un ZIP**
```bash
# Télécharger l'archive (remplacez l'URL par la vôtre)
wget "VOTRE-URL-DE-TELECHARGEMENT" -O GroLoto.tar.gz

# Extraire
tar -xzf GroLoto.tar.gz
cd GroLoto
```

**Option C: Créer les fichiers manuellement** (si petits fichiers seulement)
```bash
mkdir GroLoto
cd GroLoto

# Créer chaque fichier avec 'cat' (voir section ci-dessous)
```

---

### Étape 4: Vérifier les fichiers Docker

```bash
# Vérifier que les fichiers sont présents
ls -la

# Vous devez voir:
# - Dockerfile
# - docker-compose.yaml
# - docker/nginx/default.conf
```

Si les fichiers ne sont pas là, utilisez la **Section Copie Rapide** en bas de ce guide.

---

### Étape 5: Démarrer l'application

```bash
# Construire et démarrer les conteneurs
docker compose up --build -d

# Vérifier l'état
docker compose ps

# Voir les logs (optionnel)
docker compose logs -f
```

⏳ **Le premier build prend 5-10 minutes** (installation des dépendances PHP, Composer, etc.)

---

### Étape 6: Accéder à l'application

**Play with Docker crée automatiquement un lien public!**

1. Cherchez en haut de l'écran le badge **"8000"** qui apparaît
2. Cliquez dessus
3. Votre application GroLoto s'ouvre dans un nouvel onglet! 🎉

**Ou** utilisez l'URL affichée (format: `http://ip172-18-0-X-XXXX.direct.labs.play-with-docker.com`)

---

### Étape 7: Exécuter les migrations (si nécessaire)

```bash
# Entrer dans le conteneur PHP
docker compose exec php bash

# Lancer les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Créer un admin (optionnel)
php bin/console app:create-admin

# Sortir du conteneur
exit
```

---

## 📋 Commandes Utiles dans Play with Docker

### Voir l'état des conteneurs
```bash
docker compose ps
```

### Voir les logs
```bash
# Tous les logs
docker compose logs -f

# Logs d'un service spécifique
docker compose logs -f nginx
docker compose logs -f php
docker compose logs -f database
```

### Redémarrer un service
```bash
docker compose restart php
```

### Arrêter les conteneurs
```bash
docker compose down
```

### Reconstruire après modification
```bash
docker compose up --build -d
```

### Nettoyer tout
```bash
docker compose down -v
docker system prune -a -f
```

---

## 🔧 Section Copie Rapide (si vous devez créer les fichiers manuellement)

### Créer le Dockerfile
```bash
cat > Dockerfile << 'EOF'
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    git unzip icu-dev postgresql-dev libzip-dev oniguruma-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl pdo pdo_pgsql zip opcache \
    && apk del --no-cache icu-dev postgresql-dev libzip-dev oniguruma-dev

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader || true
RUN chown -R www-data:www-data /var/www/html/var || true

EXPOSE 9000
CMD ["php-fpm"]
EOF
```

### Créer le docker-compose.yaml
```bash
cat > docker-compose.yaml << 'EOF'
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
EOF
```

### Créer la configuration Nginx
```bash
mkdir -p docker/nginx

cat > docker/nginx/default.conf << 'EOF'
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
EOF
```

### Créer .dockerignore
```bash
cat > .dockerignore << 'EOF'
vendor/
node_modules/
var/cache/
var/log/
.git/
.env
.env.local
EOF
```

---

## 🎯 Checklist Complète

- [ ] Accéder à https://labs.play-with-docker.com
- [ ] Se connecter avec Docker Hub
- [ ] Cliquer "Start" puis "+ ADD NEW INSTANCE"
- [ ] Récupérer le code (git clone OU wget OU copie manuelle)
- [ ] Vérifier `ls -la` (Dockerfile, docker-compose.yaml présents)
- [ ] Lancer `docker compose up --build -d`
- [ ] Attendre 5-10 minutes (premier build)
- [ ] Cliquer sur le badge "8000" en haut
- [ ] Tester l'application dans le navigateur
- [ ] (Optionnel) Lancer les migrations avec `docker compose exec php bash`

---

## ❓ FAQ Play with Docker

### Combien de temps ai-je?
**4 heures par session**. Après, vous pouvez recommencer une nouvelle session.

### Est-ce vraiment gratuit?
**Oui, 100% gratuit**. Pas de carte bancaire requise.

### Puis-je sauvegarder mon travail?
Non, tout est supprimé après 4h. Mais vous pouvez:
- Exporter la base de données: `docker compose exec database pg_dump -U app app > backup.sql`
- Télécharger via le bouton de téléchargement de fichiers

### Que faire si le port 8000 ne s'affiche pas?
1. Vérifiez que les conteneurs tournent: `docker compose ps`
2. Vérifiez les logs: `docker compose logs nginx`
3. Attendez 30 secondes puis rafraîchissez la page

### Puis-je utiliser un autre port?
Oui, modifiez dans `docker-compose.yaml`:
```yaml
ports:
  - "80:80"  # au lieu de "8000:80"
```
Puis le badge sera "80"

---

## 🚨 Dépannage

### "Build failed with composer errors"
```bash
# Ignorer composer dans le build
docker compose build --build-arg SKIP_COMPOSER=true
```

### "Database connection refused"
```bash
# Attendre que la DB soit prête
docker compose logs database

# Redémarrer PHP après 30 secondes
docker compose restart php
```

### "502 Bad Gateway"
```bash
# Vérifier PHP-FPM
docker compose logs php

# Redémarrer
docker compose restart php nginx
```

---

## 📸 À quoi ça ressemble?

Voici ce que vous verrez dans Play with Docker:

1. **Terminal Linux** au centre
2. **Badge "8000"** en haut (après `docker compose up`)
3. **Boutons** pour ajouter d'autres instances
4. **Timer** en haut à droite (compte à rebours 4h)

---

## 🎉 Résumé Ultra-Rapide

```bash
# 1. Aller sur
https://labs.play-with-docker.com

# 2. Login → Start → + ADD NEW INSTANCE

# 3. Dans le terminal
git clone VOTRE-REPO
cd VOTRE-REPO

# 4. Lancer
docker compose up --build -d

# 5. Cliquer sur le badge "8000"

# 6. Profiter de votre app! 🚀
```

---

**Besoin d'aide? Suivez ce guide étape par étape et tout devrait fonctionner! 💪**
