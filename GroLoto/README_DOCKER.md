# 🐳 Guide de Conteneurisation - GroLoto Application

## ✅ Ce qui a été créé pour vous

Votre application est maintenant **prête pour Docker/Podman** avec:

### Fichiers Docker
- ✅ `Dockerfile` - Image PHP-FPM 8.2 avec toutes les extensions Symfony
- ✅ `docker-compose.yaml` - Orchestration complète (PHP + Nginx + PostgreSQL)
- ✅ `docker/nginx/default.conf` - Configuration Nginx optimisée pour Symfony
- ✅ `.dockerignore` - Exclusion des fichiers inutiles du build

### Scripts d'aide
- ✅ `scripts/install_podman.sh` - Installation automatique de Podman
- ✅ `scripts/start_containers.sh` - Démarrage automatique des conteneurs

### Documentation
- ✅ `DOCKER_GUIDE.md` - Guide complet avec toutes les commandes

---

## 🚀 TÂCHES À FAIRE POUR TESTER

### Option A: Avec Podman (recommandé si sudo disponible)

**Podman** = Alternative à Docker sans daemon, plus sécurisée, 100% compatible

#### 1. Installer Podman
```bash
./scripts/install_podman.sh
```
> **Important**: Le script va demander votre mot de passe sudo **une seule fois** pour installer Podman. Après ça, tout fonctionne sans sudo.

#### 2. Recharger les alias
```bash
source ~/.bash_aliases
```

#### 3. Démarrer l'application
```bash
./scripts/start_containers.sh
```

#### 4. Ouvrir dans le navigateur
```
http://localhost:8000
```

#### 5. (Optionnel) Exécuter les migrations
```bash
podman-compose exec php php bin/console doctrine:migrations:migrate
```

---

### Option B: Si sudo n'est PAS disponible

Vous avez plusieurs alternatives **gratuites** qui ont Docker pré-installé:

#### B1. GitHub Codespaces (recommandé)
1. Poussez votre code sur GitHub
2. Sur la page du repo, cliquez "Code" → "Codespaces" → "Create codespace"
3. Dans le terminal du Codespace:
   ```bash
   docker compose up --build -d
   ```
4. Cliquez sur le lien "Ports" puis sur le port 8000

#### B2. GitPod
1. Préfixez l'URL de votre repo avec `https://gitpod.io/#`
   Exemple: `https://gitpod.io/#https://github.com/votre-repo`
2. GitPod démarre avec Docker installé
3. Lancez:
   ```bash
   docker compose up --build -d
   ```

#### B3. Play with Docker (4h gratuites)
1. Allez sur https://labs.play-with-docker.com
2. Cliquez "Login" → "Start"
3. Cliquez "+ ADD NEW INSTANCE"
4. Clonez votre repo:
   ```bash
   git clone https://votre-repo.git
   cd nom-du-repo
   docker compose up --build -d
   ```
5. Cliquez sur le badge "8000" qui apparaît en haut

---

### Option C: Machine distante avec Docker

Si vous avez accès à un serveur distant avec Docker (VPS, serveur universitaire, etc.):

```bash
# Copier le projet
scp -r . utilisateur@serveur-distant:/chemin/du/projet

# Se connecter
ssh utilisateur@serveur-distant

# Naviguer vers le projet
cd /chemin/du/projet

# Démarrer Docker
docker compose up --build -d

# Trouver l'IP du serveur
ip addr show | grep "inet "
```

Puis accédez à `http://<IP-du-serveur>:8000`

---

## 📋 Commandes Utiles (une fois que c'est lancé)

### Voir l'état des conteneurs
```bash
podman-compose ps
# ou
docker compose ps
```

### Voir les logs en temps réel
```bash
podman-compose logs -f
# ou
docker compose logs -f
```

### Arrêter les conteneurs
```bash
podman-compose down
# ou
docker compose down
```

### Redémarrer
```bash
podman-compose restart
# ou
docker compose restart
```

### Entrer dans le conteneur PHP
```bash
podman-compose exec php bash
# ou
docker compose exec php bash
```

### Exécuter des commandes Symfony
```bash
# Migrations
podman-compose exec php php bin/console doctrine:migrations:migrate

# Créer un admin
podman-compose exec php php bin/console app:create-admin

# Nettoyer le cache
podman-compose exec php php bin/console cache:clear
```

---

## 🎯 Résumé: Que faire MAINTENANT?

### Si vous avez sudo:
1. `./scripts/install_podman.sh` (entrez mot de passe une fois)
2. `source ~/.bash_aliases`
3. `./scripts/start_containers.sh`
4. Ouvrir http://localhost:8000

### Si vous n'avez PAS sudo:
1. Utilisez GitHub Codespaces (gratuit, 60h/mois)
2. OU GitPod (gratuit, 50h/mois)
3. OU Play with Docker (gratuit, 4h/session)
4. Lancez `docker compose up --build -d`

---

## ❓ FAQ

### Différence entre Docker et Podman?
- **Docker** = Standard, nécessite daemon root
- **Podman** = Alternative sans daemon, plus sécurisé, même syntaxe

### Pourquoi Podman sur cette machine?
Car vous ne pouvez pas installer Docker directement (restrictions). Podman est plus facile à installer avec droits limités.

### Ça va fonctionner pareil?
OUI. Podman est 100% compatible avec Docker (mêmes commandes, mêmes Dockerfile).

### Combien de temps ça prend?
- Installation Podman: 2-3 minutes
- Premier build: 5-10 minutes
- Builds suivants: 30 secondes (grâce au cache)

### Et si ça ne marche pas?
Consultez `DOCKER_GUIDE.md` pour le dépannage complet, ou utilisez une alternative cloud (Codespaces/GitPod).

---

## 📚 Documentation Complète

Consultez `DOCKER_GUIDE.md` pour:
- Architecture détaillée
- Toutes les commandes
- Dépannage avancé
- Configuration production
- Variables d'environnement
- Et plus encore...

---

**Prêt à tester? Choisissez Option A ou B ci-dessus et suivez les étapes! 🚀**
